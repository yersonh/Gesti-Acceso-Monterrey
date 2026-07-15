<?php
// Componente: Modal informativo de NexGovIA
// Requiere Font Awesome 6+ en la vista padre
// Trigger: abrirModalNexGov() | Cierra: cerrarModalNexGov()
?>
<style>
    /* ── Trigger ─────────────────────────────────────────── */
    .btn-nexgov-trigger {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: none;
        border: none;
        padding: 2px 7px;
        font: inherit;
        font-weight: 700;
        font-size: inherit;
        color: #c9a84c;
        cursor: pointer;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.2s;
        vertical-align: baseline;
    }
    .btn-nexgov-trigger:hover {
        color: #e8c97a;
        background: rgba(201,168,76,0.12);
    }
    .btn-nexgov-trigger::after {
        content: '\f05a';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 0.7em;
        opacity: 0.7;
    }

    /* ── Overlay ─────────────────────────────────────────── */
    .nexgov-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 12px 16px;
        background: rgba(10,28,18,0.75);
        backdrop-filter: blur(4px);
    }
    .nexgov-overlay.open { display: flex; }

    @keyframes nexgovEntrar {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── Modal container ─────────────────────────────────── */
    .nexgov-modal {
        width: 100%;
        max-width: 500px;
        background: #f8f4ed;
        border: 1px solid rgba(201,168,76,0.25);
        border-radius: 20px;
        box-shadow: 0 30px 70px rgba(0,0,0,0.4), 0 0 0 1px rgba(201,168,76,0.15);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 92vh;
        animation: nexgovEntrar 0.35s ease both;
    }

    /* ── Header verde institucional ──────────────────────── */
    .nexgov-header {
        position: relative;
        overflow: hidden;
        background: linear-gradient(160deg, #061a0d 0%, #0d3320 40%, #1a5c38 100%);
        min-height: 8.5rem;
        padding: 22px 52px 22px 22px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        border-bottom: 1px solid rgba(201,168,76,0.3);
    }
    /* Patrón de puntos */
    .nexgov-dots {
        position: absolute;
        inset: 0;
        opacity: 0.08;
        pointer-events: none;
    }
    /* Resplandor dorado */
    .nexgov-glow {
        position: absolute;
        left: -30px; top: -30px;
        width: 150px; height: 150px;
        background: rgba(201,168,76,0.15);
        border-radius: 50%;
        filter: blur(40px);
        pointer-events: none;
    }
    /* Línea dorada derecha (decorativa, como panel-izquierdo) */
    .nexgov-header::after {
        content: '';
        position: absolute;
        right: 0; top: 0;
        width: 1px; height: 100%;
        background: linear-gradient(to bottom,
            transparent, rgba(201,168,76,0.4) 30%,
            rgba(201,168,76,0.4) 70%, transparent);
    }

    .nexgov-close {
        position: absolute;
        top: 14px; right: 14px;
        padding: 7px 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(201,168,76,0.25);
        color: rgba(255,255,255,0.55);
        cursor: pointer;
        z-index: 10;
        line-height: 1;
        transition: all 0.2s;
        font-size: 0.9rem;
    }
    .nexgov-close:hover {
        background: rgba(201,168,76,0.15);
        color: #e8c97a;
        border-color: rgba(201,168,76,0.5);
    }

    .nexgov-header-inner {
        display: flex;
        align-items: center;
        gap: 18px;
        width: 100%;
        position: relative;
        z-index: 10;
    }
    .nexgov-logo-circle {
        width: 76px; height: 76px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 9px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        flex-shrink: 0;
        border: 3px solid rgba(201,168,76,0.5);
        transition: transform 0.3s, border-color 0.3s;
    }
    .nexgov-logo-circle:hover {
        transform: scale(1.05);
        border-color: #c9a84c;
    }
    .nexgov-logo-circle img { width: 100%; height: 100%; object-fit: contain; }

    .nexgov-header-txt { line-height: 1.5; }
    .nexgov-by {
        color: rgba(255,255,255,0.88);
        font-size: 0.8rem;
        font-weight: 500;
    }
    .nexgov-brand { color: #e8c97a; font-weight: 700; }
    .nexgov-sub {
        color: rgba(255,255,255,0.5);
        font-size: 0.72rem;
        margin-top: 4px;
    }
    .nexgov-sub-accent { color: #a8d5b5; font-weight: 600; }

    /* ── Cuerpo ──────────────────────────────────────────── */
    .nexgov-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        background: #f8f4ed;
    }
    .nexgov-body::-webkit-scrollbar { width: 5px; }
    .nexgov-body::-webkit-scrollbar-track { background: transparent; }
    .nexgov-body::-webkit-scrollbar-thumb {
        background: rgba(26,92,56,0.2);
        border-radius: 9999px;
    }
    .nexgov-body::-webkit-scrollbar-thumb:hover {
        background: rgba(26,92,56,0.4);
    }

    .nexgov-desc {
        color: #3a3a3a;
        font-size: 0.87rem;
        line-height: 1.7;
        margin-bottom: 22px;
    }
    .nexgov-desc p + p { margin-top: 11px; }

    /* ── Cards de features ───────────────────────────────── */
    .nexgov-features {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 22px;
    }
    .nexgov-feat {
        padding: 13px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(201,168,76,0.18);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .nexgov-feat:hover {
        border-color: rgba(201,168,76,0.4);
        box-shadow: 0 3px 10px rgba(26,92,56,0.08);
    }
    .nexgov-feat-icon { font-size: 1.05rem; margin-bottom: 7px; }
    .nexgov-feat-title {
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 3px;
    }
    .nexgov-feat-desc { font-size: 0.7rem; color: #6b6b6b; line-height: 1.4; }

    .nexgov-feat:nth-child(1) .nexgov-feat-icon { color: #1a5c38; }
    .nexgov-feat:nth-child(1) .nexgov-feat-title { color: #1a5c38; }
    .nexgov-feat:nth-child(2) .nexgov-feat-icon { color: #2d7a4f; }
    .nexgov-feat:nth-child(2) .nexgov-feat-title { color: #2d7a4f; }
    .nexgov-feat:nth-child(3) .nexgov-feat-icon { color: #c9a84c; }
    .nexgov-feat:nth-child(3) .nexgov-feat-title { color: #a07830; }

    /* ── Website link ────────────────────────────────────── */
    .nexgov-website {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .nexgov-website p { font-size: 0.82rem; color: #6b6b6b; font-weight: 400; }
    .nexgov-website a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        background: rgba(26,92,56,0.07);
        color: #1a5c38;
        font-weight: 700;
        font-size: 0.78rem;
        border: 1px solid rgba(26,92,56,0.2);
        text-decoration: none;
        transition: all 0.25s;
    }
    .nexgov-website a:hover {
        background: rgba(26,92,56,0.14);
        border-color: rgba(26,92,56,0.4);
    }

    /* ── Botón CTA ───────────────────────────────────────── */
    .nexgov-cta {
        width: 100%;
        padding: 13px;
        border-radius: 10px;
        background: #1a5c38;
        color: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        margin-bottom: 12px;
        transition: all 0.25s;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.04em;
    }
    .nexgov-cta::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.07), transparent);
    }
    .nexgov-cta:hover {
        background: #2d7a4f;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26,92,56,0.3);
    }
    .nexgov-cta:active { transform: translateY(0); }

    .nexgov-copy {
        text-align: center;
        font-size: 0.65rem;
        color: #a09890;
    }

    /* ── Mobile ──────────────────────────────────────────── */
    @media (max-width: 480px) {
        .nexgov-features { grid-template-columns: 1fr; }
        .nexgov-logo-circle { width: 62px; height: 62px; }
    }
</style>

<div class="nexgov-overlay" id="nexgovModal">
    <div class="nexgov-modal">
        <div class="nexgov-header">
            <div class="nexgov-dots">
                <svg width="100%" height="100%">
                    <pattern id="ng-dots" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="white"/>
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#ng-dots)"/>
                </svg>
            </div>
            <div class="nexgov-glow"></div>
            <button class="nexgov-close" onclick="cerrarModalNexGov()"><i class="fas fa-xmark"></i></button>
            <div class="nexgov-header-inner">
                <div class="nexgov-logo-circle">
                    <img src="/imagenes/logoEmpresa.png" alt="Logo NexGovIA">
                </div>
                <div class="nexgov-header-txt">
                    <p class="nexgov-by">Plataforma desarrollada por <span class="nexgov-brand">NexGovIA S.A.S.®</span></p>
                    <p class="nexgov-sub">Asesores <span class="nexgov-sub-accent">e-Governance Solutions</span> para Entidades Públicas.</p>
                </div>
            </div>
        </div>

        <div class="nexgov-body">
            <div class="nexgov-desc">
                <p>NexGovIA es una firma líder en consultoría y desarrollo tecnológico, especializada en la implementación de soluciones de Inteligencia Artificial para la administración pública, mediante la automatización de procesos de sus actividades administrativas incursas en cumplimientos normativos.</p>
                <p>Diseñamos ecosistemas digitales que permiten a las organizaciones gubernamentales operar con mayor transparencia, agilidad y eficiencia, conectando mejor con las necesidades del ciudadano moderno.</p>
            </div>

            <div class="nexgov-features">
                <div class="nexgov-feat">
                    <div class="nexgov-feat-icon"><i class="fas fa-globe"></i></div>
                    <div class="nexgov-feat-title">Alcance</div>
                    <div class="nexgov-feat-desc">Sistemas escalables a nivel nacional.</div>
                </div>
                <div class="nexgov-feat">
                    <div class="nexgov-feat-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="nexgov-feat-title">Seguridad</div>
                    <div class="nexgov-feat-desc">Protección de datos de alto nivel.</div>
                </div>
                <div class="nexgov-feat">
                    <div class="nexgov-feat-icon"><i class="fas fa-bolt"></i></div>
                    <div class="nexgov-feat-title">IA</div>
                    <div class="nexgov-feat-desc">Automatización inteligente de procesos.</div>
                </div>
            </div>

            <div class="nexgov-website">
                <p>Conoce más sobre nosotros en:</p>
                <a href="https://nexgovia.com" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-globe"></i> nexgovia.com
                </a>
            </div>

            <button class="nexgov-cta" onclick="cerrarModalNexGov()">Entendido</button>
            <p class="nexgov-copy">© 2026 NexGovIA · Transformando el futuro de la gestión pública</p>
        </div>
    </div>
</div>

<script>
    function abrirModalNexGov() {
        document.getElementById('nexgovModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function cerrarModalNexGov() {
        document.getElementById('nexgovModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('nexgovModal').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalNexGov();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarModalNexGov();
    });
</script>
