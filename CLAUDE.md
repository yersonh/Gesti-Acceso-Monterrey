# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de Gestión de Visitas (SCV) for the Alcaldía Municipal de Monterrey, Casanare. A PHP 8.3 municipal visitor and appointment management web application deployed on Railway.

## Development Commands

```bash
# Install dependencies
composer install

# Run local dev server (from project root)
php -S localhost:8000 -t public public/index.php

# Production start (Railway)
php -S 0.0.0.0:$PORT -t public public/index.php
```

There are no build, lint, or test scripts — this is a traditional PHP application.

## Environment Variables

Required in Railway (do not hardcode):
- `DATABASE_URL` — PostgreSQL connection string (`postgresql://user:pass@host:port/db`)
- `BREVO_API_KEY`, `SMTP_FROM`, `SMTP_FROM_NAME` — email via Brevo API

## Architecture

**Custom MVC pattern** with no framework. All HTTP requests are routed through `public/index.php` via a URL-based switch statement.

### Startup sequence in `public/index.php`

The order matters — do not reorder:

```
1. HTTP security headers (X-Frame-Options, X-Content-Type-Options, etc.)
2. config/database.php        — PDO singleton (reads DATABASE_URL)
3. config/session_handler.php — DatabaseSessionHandler (sessions in PostgreSQL)
4. session_start()
5. Delete legacy tab_id cookie
6. config/csrf.php            — csrf_token(), csrf_field(), csrf_verify()
7. config/auth.php            — auth_load(), auth_save(), auth_clear(), redirect(), tab_id_field()
8. config/rate_limiter.php    — RateLimiter class (login brute-force protection)
9. auth_load()                — loads current tab's auth data into $_SESSION
10. register_shutdown_function('auth_save')
```

### Request Flow

```
Browser → public/index.php (router) → controllers/*.php → models/*.php → views/*.php
                                ↓ (async)
                           ajax/*.php endpoints (all routed through index.php)
```

### Controllers

| Controller | Routes |
|---|---|
| `AuthController` | login, registro, recuperar, restablecer, restablecerEscritorio, logout |
| `CitaController` | dashboard (ciudadano), agendar, cita/responder (reprogramación) |
| `FuncionarioController` | funcionario/dashboard (aprobar/rechazar/reprogramar citas) |
| `SuperAdminController` | superadmin/usuarios + ajax/superadmin_usuarios |
| `ReportesController` | superadmin/reportes, ajax/reportes, reportes/excel |
| `ValoracionController` | valorar (encuesta post-visita, acceso sin auth por token) |

### AJAX Endpoints

All files in `ajax/` are routed through `index.php`, inheriting `auth_load()` and all config.

| Endpoint | Purpose |
|---|---|
| `ajax/get_citas` | Paginated ciudadano appointments (returns HTML + JSON paginación) |
| `ajax/horarios_disponibles` | Available time slots for a funcionario on a date |
| `ajax/verificar_identificacion` | Check if ID number already exists (during registro) |
| `ajax/superadmin_usuarios` | CRUD for user management |
| `ajax/check_nuevas_citas` | Polling endpoint used by funcionario dashboard |

Exception messages must never be sent to the client — use `error_log()` and return a generic message.

### Key Config Files

- `config/database.php` — PDO singleton; reads `DATABASE_URL` env var
- `config/session_handler.php` — `DatabaseSessionHandler` implementing `SessionHandlerInterface`; requires `php_sessions` table
- `config/csrf.php` — `csrf_token()`, `csrf_field()`, `csrf_verify()`
- `config/auth.php` — multi-tab session system + `redirect()` helper
- `config/rate_limiter.php` — `RateLimiter::check()`, `registrarFallo()`, `limpiar()`; requires `login_attempts` table
- `config/mail.php` — Brevo API; all user-supplied variables are escaped with `self::h()` before heredoc interpolation

### Multi-Tab Session System (`config/auth.php`)

Each browser tab generates a unique `tab_id` via `crypto.randomUUID()` stored in `sessionStorage` (per-tab, not shared). Auth data lives at `$_SESSION['_tabs'][$tabId][...]`.

**Critical rules:**
- **Never `session_destroy()`** — destroys all tabs' sessions. Use `auth_clear()` for logout.
- **Always `redirect('/path')`** — never `header('Location: /path') + exit`. The helper appends `?tab_id=xxx` to preserve tab identity across server-side redirects.
- `auth_tab_id()` reads from `$_POST['tab_id']` then `$_GET['tab_id']` — **never from cookies** (cookies are domain-wide, break tab isolation).
- All JavaScript `fetch()` GET calls must append `&tab_id=${window.TAB_ID}` to the URL. POST calls must include `tab_id` in the request body/FormData.
- `window.TAB_ID` is set by the inline script in every view's `<head>`.
- Flash messages (`flash_mensaje`, `flash_error`) are in `AUTH_KEYS` — they are tab-isolated. Set them before `redirect()`, read and unset them at the top of the target controller action.

### CSRF Protection (`config/csrf.php`)

- Every POST handler: call `csrf_verify()` as the first line.
- Every HTML form: include `<?= csrf_field() ?>` and `<?= tab_id_field() ?>`.
- For AJAX POST (FormData): append `body.append('csrf_token', CSRF_TOKEN)` and `body.append('tab_id', TAB_ID)`.

### Rate Limiting (`config/rate_limiter.php`)

Protects the login form: 5 failed attempts per IP within 15 minutes triggers a block. Stored in PostgreSQL (`login_attempts` table) — survives container restarts. Call `RateLimiter::limpiar()` on successful login.

### Email (`config/mail.php`)

All template methods escape user-supplied data **before** the heredoc using `self::h($var)` (wraps `htmlspecialchars`). Never interpolate raw user data inside a heredoc template.

### User Roles

Role values stored in `usuarios.rol` and `$_SESSION['usuario_rol']` are **title-case**:
`Superadmin` → `Administrador` → `Recepcionista` → `Funcionario` → `Ciudadano`

Access control via `$_SESSION['usuario_rol']` in controller `require*()` guard methods.

### Database

PostgreSQL via PDO. Full schema in `esquema.sql`.

**All tables:**
`usuarios`, `ciudadanos`, `funcionarios`, `personal`, `dependencias`, `funcionario_dependencia`,
`citas`, `visitas_espontaneas`, `horarios_bloqueados`, `dias_festivos`,
`configuracion_sistema`, `valoraciones`, `password_resets`, `auditoria_logs`,
`php_sessions`, `login_attempts`

**DB Views (used by reports):**
- `vista_registro_visitas` — today's `citas` + `visitas_espontaneas` unified (used by funcionario dashboard)
- `vista_reportes_visitas` — all-dates union for SuperAdmin reports

#### Critical schema facts

**`funcionarios` has NO `dependencia_id` column.** The many-to-many relationship is in `funcionario_dependencia (id, funcionario_id, dependencia_id, created_at)`. Always join through that table.

**`citas.hora_fin` is auto-calculated** by the DB trigger `trg_calcular_hora_fin` using `hora_inicio + intervalo_min` from `configuracion_sistema`. You can pass `hora_fin` in an INSERT but the trigger will overwrite it.

**`citas.estado` valid values** (enforced by CHECK constraint):
`pendiente`, `confirmada`, `cancelada`, `completada`, `no_asistio`, `en_curso`, `finalizada`, `propuesta_reprogramacion`, `contrapropuesta_ciudadano`

**`citas` has a unique partial index** preventing double-booking:
`UNIQUE (funcionario_id, fecha, hora_inicio) WHERE estado <> 'cancelada'`

**`configuracion_sistema`** is a single-row table (always `LIMIT 1`). Controls office hours (`manana_inicio/fin`, `tarde_inicio/fin`), appointment interval (`intervalo_min` minutes), and working days (`lunes`…`domingo` booleans).

**`dias_festivos`** — holidays that block scheduling. `recurrente = true` means the month/day repeats every year regardless of the `fecha` year.

**`visitas_espontaneas`** — walk-in visits (no prior appointment). Managed from the recepcionista/funcionario interface. Different from `citas` (pre-scheduled).

**`valoraciones`** — post-visit satisfaction survey. Token-based access (no auth). `tipo_visita` = `'cita'` or `'espontanea'`, `visita_id` references the corresponding table. `respondido` is set true once the ciudadano submits; tokens expire via `expires_at`.

Tables that must exist (run once if missing from Railway):
```sql
CREATE TABLE IF NOT EXISTS php_sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL DEFAULT '',
    expires_at TIMESTAMPTZ NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_php_sessions_expires ON php_sessions (expires_at);

CREATE TABLE IF NOT EXISTS login_attempts (
    id SERIAL PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts (ip, created_at);
```

### Adding a New Controller Action (checklist)

1. `csrf_verify()` as first line of every POST block
2. `redirect('/path')` — never `header('Location:') + exit`
3. Add route to the `switch` in `public/index.php`
4. New form: `<?= csrf_field() ?>` + `<?= tab_id_field() ?>` inside `<form>`
5. New view: copy the tab_id `<script>` block from any existing view's `<head>`
6. New AJAX GET fetch: append `&tab_id=${window.TAB_ID}`
7. New AJAX POST fetch: include `tab_id` and `csrf_token` in body

### Reports

`ReportesController` + `ReporteModel` generate Excel via PHPOffice/PhpSpreadsheet. `ReportesController.php` has `declare(strict_types=1)` — must be first statement after `<?php`, no blank lines, no BOM.

### Favicon

`public/imagenes/favicon.png` — circular logo with golden border. All views reference it as `<link rel="icon" type="image/png" href="/imagenes/favicon.png">`.

## Deployment

Railway + Nixpacks (PHP 8.3). Config in `railway.json` and `nixpacks.toml`.

**BOM warning:** PowerShell's `[System.Text.Encoding]::UTF8` adds a BOM that breaks `declare(strict_types=1)`. Use `[System.IO.File]::WriteAllBytes` to write without BOM, or run the BOM-removal script if needed.
