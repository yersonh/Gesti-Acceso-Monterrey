# Módulo de Visitas

## Descripción General

El **Módulo de Visitas** es una aplicación web diseñada para la gestión integral de citas programadas y visitas espontáneas en dependencias gubernamentales, específicamente para la Alcaldía Municipal de Monterrey, Casanare. El sistema permite a los ciudadanos agendar citas con funcionarios específicos, gestionar el flujo de trabajo administrativo y recopilar feedback post-servicio a través de encuestas automatizadas.

### Problema que Resuelve
- Optimiza la atención al ciudadano mediante un sistema de citas organizado
- Reduce tiempos de espera y mejora la eficiencia administrativa
- Proporciona trazabilidad completa de todas las interacciones
- Genera reportes estadísticos para toma de decisiones
- Implementa auditoría integral para cumplimiento normativo

### Usuarios Objetivo
- **Ciudadanos**: Residentes que requieren servicios municipales
- **Funcionarios**: Empleados administrativos que atienden citas
- **Superadministradores**: Personal de TI que gestiona usuarios y configuraciones
- **Administradores**: Supervisores con permisos extendidos

---

## Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | 8.3 | Lenguaje de backend |
| PostgreSQL | [PENDIENTE] | Base de datos relacional |
| Composer | [PENDIENTE] | Gestión de dependencias |
| PhpSpreadsheet | ^5.6 | Generación de reportes Excel |
| Railway | N/A | Plataforma de despliegue |
| Nixpacks | N/A | Constructor de contenedores |

---

## Requisitos Previos

### Sistema Operativo
- Windows 10/11, Linux o macOS

### Software Requerido
- **PHP**: Versión 8.3 o superior
- **PostgreSQL**: Versión 12 o superior
- **Composer**: Última versión estable
- **Git**: Para control de versiones

### Extensiones PHP Requeridas
```bash
php8.3-pdo
php8.3-pdo-pgsql
php8.3-pgsql
php8.3-curl
php8.3-gd
php8.3-mbstring
php8.3-xml
php8.3-zip
```

### Variables de Entorno
```bash
BREVO_API_KEY=your_brevo_api_key
SMTP_FROM=your_email@domain.com
SMTP_FROM_NAME=Alcaldía de Monterrey
DATABASE_PUBLIC_URL=postgresql://user:pass@host:port/dbname
```

---

## Instalación

### 1. Clonar el Repositorio
```bash
git clone [URL_DEL_REPOSITORIO]
cd modulo-de-visitas
```

### 2. Instalar Dependencias
```bash
composer install
```

### 3. Configurar Base de Datos
```bash
# Crear base de datos PostgreSQL
createdb modulo_visitas

# Ejecutar esquema inicial
psql -d modulo_visitas -f init.sql
```

### 4. Configurar Variables de Entorno
Crear archivo `.env` en la raíz del proyecto:
```bash
cp .env.example .env
# Editar .env con las credenciales reales
```

### 5. Configurar Servidor Web
Para desarrollo local con PHP built-in server:
```bash
cd public
php -S localhost:8000
```

Acceder en: `http://localhost:8000`

---

## Estructura del Proyecto

| Directorio/Archivo | Descripción |
|-------------------|-------------|
| `ajax/` | Endpoints AJAX para operaciones asíncronas |
| `config/` | Archivos de configuración (base de datos, correo) |
| `controllers/` | Controladores MVC para lógica de negocio |
| `models/` | Modelos de datos y lógica de acceso a BD |
| `public/` | Punto de entrada público y assets estáticos |
| `vendor/` | Dependencias instaladas por Composer |
| `views/` | Plantillas de vista por rol de usuario |
| `composer.json` | Configuración de dependencias PHP |
| `init.sql` | Esquema inicial de base de datos |
| `railway.json` | Configuración de despliegue en Railway |
| `nixpacks.toml` | Configuración del constructor Nixpacks |

---

## Arquitectura de Controladores

| Controlador | Propósito | Acciones Principales |
|-------------|-----------|---------------------|
| `AuthController` | Gestión de autenticación y registro | login, logout, registro, recuperación de contraseña |
| `CitaController` | Panel del ciudadano | agendar, dashboard, cancelar citas, responder reprogramaciones |
| `FuncionarioController` | Flujo de trabajo administrativo | dashboard, aprobar/rechazar citas, proponer reprogramaciones, bloquear horarios |
| `ReportesController` | Generación de estadísticas | dashboard, API de datos, exportación Excel |
| `SuperAdminController` | Administración de usuarios | gestión de usuarios, activar/desactivar, reset de contraseñas |
| `ValoracionController` | Encuestas post-servicio | mostrar formulario, procesar respuestas |

---

## Roles de Usuario

### Ciudadano
- **Acceso**: Portal web público
- **Funcionalidades**:
  - Registro y autenticación
  - Agendamiento de citas con funcionarios específicos
  - Visualización y cancelación de citas propias
  - Respuesta a propuestas de reprogramación
  - Participación en encuestas de satisfacción

### Funcionario
- **Acceso**: Sistema de escritorio administrativo
- **Funcionalidades**:
  - Gestión del flujo de citas (aprobar, rechazar, reprogramar)
  - Bloqueo de horarios personales
  - Registro de salida de atención
  - Envío de notificaciones por correo

### Superadmin
- **Acceso**: Panel de administración completo
- **Funcionalidades**:
  - Gestión completa de usuarios del sistema
  - Activación/desactivación de cuentas
  - Reset de contraseñas
  - Acceso a reportes avanzados

### Administrador
- **Acceso**: Similar a Superadmin con permisos limitados
- **Funcionalidades**: Subconjunto de permisos del Superadmin

---

## Sistema de Notificaciones

### Correo Electrónico
Utiliza la API de Brevo (Sendinblue) para envío de notificaciones:
- **Confirmación de registro**: Bienvenida al sistema
- **Recuperación de contraseña**: Enlace seguro con token
- **Confirmación de solicitud**: Acuse de cita agendada
- **Aprobación/Rechazo de cita**: Notificación con detalles
- **Propuesta de reprogramación**: Nueva fecha/hora sugerida
- **Aceptación/Rechazo de contrapropuesta**: Respuesta del ciudadano

### WhatsApp
- **Envío de enlace de valoración**: Token único para encuesta post-servicio
- Integración manual mediante API externa

---

## Auditoría

El sistema registra automáticamente todas las operaciones importantes en la tabla `auditoria_logs`:

### Acciones Auditadas
- `AGENDAR_CITA`: Creación de nueva cita
- `CANCELAR_CITA`: Cancelación por ciudadano
- `APROBAR_CITA`: Aprobación por funcionario
- `RECHAZAR_CITA`: Rechazo con motivo
- `PROPONER_REPROGRAMACION`: Sugerencia de nueva fecha/hora
- `ACEPTAR_REPROGRAMACION`: Aceptación de reprogramación
- `RECHAZAR_REPROGRAMACION`: Rechazo de reprogramación
- `CONTRAPROPUESTA_CIUDADANO`: Propuesta alternativa del ciudadano
- `BLOQUEAR_HORARIO`: Reserva de horario personal
- `ELIMINAR_BLOQUEO`: Liberación de horario
- `REGISTRAR_SALIDA`: Finalización de atención
- `ENVIAR_VALORACION`: Envío de enlace de encuesta
- `TOGGLE_ACTIVO`: Activación/desactivación de usuario
- `RESET_PASSWORD`: Cambio de contraseña por admin
- `CREAR_USUARIO`: Registro de nuevo usuario

### Información Registrada
- ID y nombre del usuario
- Rol del usuario
- Acción realizada
- Descripción detallada
- Tabla y registro afectados
- Dirección IP del cliente
- Timestamp automático

---

## API Endpoints AJAX

### `horarios_disponibles.php`
- **Método**: GET
- **Parámetros**: `funcionario_id`, `fecha`
- **Respuesta**: JSON con horarios disponibles para agendamiento

### `check_nuevas_citas.php`
- **Método**: GET
- **Propósito**: Notificaciones en tiempo real de nuevas citas
- **Respuesta**: JSON con estado de citas pendientes

### `get_citas.php`
- **Método**: GET
- **Parámetros**: Filtros opcionales (estado, fecha, etc.)
- **Respuesta**: JSON con lista de citas

### `superadmin_usuarios.php`
- **Método**: GET/POST
- **Funciones**: CRUD completo de usuarios
- **Respuesta**: JSON con resultados de operaciones

### `verificar_identificacion.php`
- **Método**: POST
- **Parámetros**: `tipo_id`, `numero_id`
- **Respuesta**: JSON con validación de documento

---

## Reportes Disponibles

### Datos Estadísticos
- **Ocupación por dependencia**: Distribución de citas por área
- **Horas pico de atención**: Análisis temporal de demanda
- **Tiempo de espera promedio**: Métricas de eficiencia
- **Duración de visitas**: Estadísticas de tiempo de atención
- **Ranking de motivos**: Frecuencia de tipos de solicitud
- **Inasistencias (no-show)**: Tasa de ausencias
- **Trazabilidad de citas**: Historial completo por cita

### Formatos de Exportación
- **Excel profesional**: Con logo institucional, formato tabular, metadatos
- **JSON estructurado**: Para integraciones y análisis avanzado

---

## Configuración de Despliegue

### Railway (Plataforma Recomendada)
1. **Conectar repositorio**: Vincular cuenta GitHub/GitLab
2. **Variables de entorno**: Configurar credenciales de BD y API
3. **Base de datos**: PostgreSQL managed por Railway
4. **Dominio**: Configurar dominio personalizado opcional

### Archivo `railway.json`
```json
{
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "echo 'Skipping build'"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t public public/index.php",
    "healthcheckPath": "/",
    "restartPolicyType": "ON_FAILURE"
  }
}
```

### Archivo `nixpacks.toml`
```toml
name = "sgp-app"

[phases.setup]
nixPkgs = [
  "php83",
  "php83Extensions.pdo",
  "php83Extensions.pdo_pgsql",
  "php83Extensions.pgsql",
  "php83Extensions.curl",
  "php83Extensions.gd",
  "php83Extensions.mbstring",
  "php83Extensions.xml",
  "php83Extensions.zip",
  "php83Packages.composer"
]

[phases.install]
cmds = [
  "composer install --no-dev --optimize-autoloader",
  "mkdir -p storage/cache",
  "mkdir -p storage/logs"
]

[start]
cmd = "php -S 0.0.0.0:$PORT -t public public/index.php"
```

---

## Guía de Uso Básica por Rol

### Para Ciudadanos
1. **Registro**: Crear cuenta con documento de identidad
2. **Inicio de sesión**: Acceder al portal ciudadano
3. **Agendar cita**: Seleccionar dependencia, funcionario y horario disponible
4. **Gestionar citas**: Ver estado, cancelar si es necesario
5. **Responder reprogramaciones**: Aceptar o proponer nueva fecha

### Para Funcionarios
1. **Inicio de sesión**: Acceder al sistema administrativo
2. **Revisar pendientes**: Ver citas solicitadas
3. **Gestionar citas**: Aprobar, rechazar o reprogramar
4. **Bloquear horarios**: Reservar tiempo personal
5. **Registrar salidas**: Finalizar atención y activar encuesta

### Para Superadministradores
1. **Inicio de sesión**: Acceder al panel de administración
2. **Gestionar usuarios**: Crear, activar/desactivar cuentas
3. **Reset de contraseñas**: Ayudar a usuarios con problemas de acceso
4. **Revisar reportes**: Analizar estadísticas del sistema

---

## Contribución

### Estándares de Código
- Seguir PSR-4 para autoloading
- Usar nombres descriptivos en inglés para variables y funciones
- Comentar código complejo en español
- Mantener consistencia en el estilo de código

### Proceso de Contribución
1. Crear rama feature desde `main`
2. Implementar cambios con commits descriptivos
3. Ejecutar pruebas locales
4. Crear Pull Request con descripción detallada
5. Esperar revisión y aprobación

### Configuración de Desarrollo
```bash
# Instalar dependencias de desarrollo
composer install

# Ejecutar linter (si configurado)
# Configurar base de datos de desarrollo
```

---

## Licencia

[PENDIENTE] - Especificar tipo de licencia (MIT, GPL, etc.)

---

*Desarrollado para la Alcaldía Municipal de Monterrey, Casanare*</content>