<?php
$error_mensaje = $error_mensaje ?? '';
$token = $token ?? '';
$nombre_candidato = $nombre_candidato ?? '';
$id_candidato = (int)($id_candidato ?? 0);
$documentos_subidos = $documentos_subidos ?? [];
$expediente_completo = $expediente_completo ?? false;
$api_verificacion_base = $api_verificacion_base ?? '/CapHum/docVerificacionProxy';
$documentos = [
    1  => 'SOLICITUD INTERNA',
    2  => 'CV O SOLICITUD DE TRABAJO',
    3  => 'ACTA DE NACIMIENTO CERTIFICADA',
    4  => 'CURP',
    5  => 'IDENTIFICACIÓN OFICIAL',
    6  => 'COMPROBANTE DE DOMICILIO',
    7  => 'CONSTANCIA DE SITUACION FISCAL',
    8  => 'NÚMERO DE SEGURIDAD SOCIAL',
    9  => 'HOJA DE RETENCION FONACOT O INFONAVIT',
    10 => 'ESTADO DE CUENTA',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir documentos | Maxikash</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico2.svg">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css">
    <link rel="stylesheet" href="/assets/vendor/css/core.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Public Sans', sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            max-width: 780px;
            width: 100%;
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, #2c5282 0%, #3182ce 100%);
            color: #fff;
            padding: 1.75rem 2rem;
            text-align: center;
        }
        .form-card-header img {
            height: 48px;
            width: auto;
            margin-bottom: 0.75rem;
        }
        .form-card-header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
        }
        .form-card-header p {
            margin: 0.5rem 0 0;
            font-size: 0.9rem;
            opacity: 0.95;
        }
        .form-card-body {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        .candidato-name {
            background: #edf2f7;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #2d3748;
        }
        .expediente-completo {
            text-align: center;
            padding: 2rem 1.5rem;
        }
        .expediente-completo-icon {
            font-size: 4rem;
            color: #38a169;
            margin-bottom: 1.25rem;
            line-height: 1;
        }
        .expediente-completo-icon i { font-size: inherit; }
        .expediente-completo-title {
            margin: 0 0 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
        }
        .expediente-completo-texto {
            margin: 0 0 1rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #4a5568;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        .expediente-completo-agradecimiento {
            margin: 0;
            font-size: 0.95rem;
            color: #718096;
            font-style: italic;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.35rem;
            font-size: 0.85rem;
        }
        .form-group .form-control-file {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .form-group .form-control-file:hover {
            border-color: #3182ce;
            background: #ebf8ff;
        }
        .btn-submit {
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(135deg, #2b6cb0 0%, #3182ce 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        #mensajeResultado {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
            max-height: 280px;
            overflow-y: auto;
        }
        #mensajeResultado .msg-verificando,
        #mensajeResultado .msg-result-item {
            padding: 10px 12px;
            border-radius: 10px;
            flex-shrink: 0;
        }
        #mensajeResultado .msg-verificando {
            background: rgba(45,212,191,0.08);
            border: 1px solid rgba(45,212,191,0.2);
            color: #0F9080;
        }
        #mensajeResultado .msg-result-item {
            transition: opacity 0.4s ease;
        }
        .small-text {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
        .btn-descarga {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            background: #bee3f8;
            color: #2b6cb0;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-descarga:hover {
            background: #90cdf4;
            color: #2c5282;
        }
        .btn-descarga.btn-llenar {
            background: #9ae6b4;
            color: #276749;
        }
        .btn-descarga.btn-llenar:hover {
            background: #68d391;
            color: #22543d;
        }
        .descarga-doc .btn-descarga + .btn-descarga {
            margin-left: 0.5rem;
        }
        .carta-parrafo-mano details[open] summary .fa-chevron-right {
            transform: rotate(90deg);
        }
        .btn-tomar-foto {
            white-space: nowrap;
        }
        /* Modal cámara */
        .modal-camara-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-camara-overlay.activo {
            display: flex;
        }
        .modal-camara {
            background: #fff;
            border-radius: 16px;
            max-width: 100%;
            width: 560px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-camara .modal-camara-header {
            padding: 1rem 1.25rem;
            background: #2c5282;
            color: #fff;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-camara .modal-camara-body {
            padding: 1rem;
            position: relative;
            background: #000;
        }
        .modal-camara video {
            width: 100%;
            display: block;
            max-height: 70vh;
        }
        .modal-camara canvas {
            display: none;
        }
        .modal-camara .modal-camara-actions {
            padding: 1rem 1.25rem;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        .modal-camara .modal-camara-actions button {
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
        }
        .modal-camara .btn-cancelar-camara {
            background: #e2e8f0;
            color: #4a5568;
        }
        .modal-camara .btn-capturar {
            background: #3182ce;
            color: #fff;
        }
        .modal-camara .btn-capturar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .modal-camara .msg-error-camara {
            color: #fff;
            padding: 1rem;
            text-align: center;
        }
        /* Responsive: card y contenido en móvil */
        @media (max-width: 768px) {
            body { padding: 0.75rem; align-items: flex-start; }
            .form-card { max-width: 100%; border-radius: 12px; margin: 0; }
            .form-card-header { padding: 1.25rem 1rem; }
            .form-card-header h1 { font-size: 1.15rem; }
            .form-card-header p { font-size: 0.8rem; }
            .form-card-body { padding: 1.25rem 1rem; max-height: none; }
            .candidato-name { font-size: 0.9rem; padding: 0.6rem 0.75rem; }
            .form-group .form-control-file { font-size: 0.8rem; padding: 0.5rem; }
            .btn-descarga { font-size: 0.8rem; padding: 0.35rem 0.6rem; }
            .descarga-doc .btn-descarga + .btn-descarga { margin-left: 0; margin-top: 0.35rem; }
            .d-flex.flex-wrap { flex-direction: column; align-items: stretch; }
            .btn-tomar-foto { align-self: flex-start; }
            .btn-submit { padding: 0.8rem 1rem; font-size: 0.95rem; }
            .small-text { font-size: 0.75rem; }
        }
        @media (max-width: 480px) {
            body { padding: 0.5rem; }
            .form-card-header img { height: 36px; }
            .form-card-header h1 { font-size: 1rem; }
        }
    </style>
    <style id="captura-id-styles">
        /* ===== SHARED OVERLAY DESIGN SYSTEM ===== */
        #capturaIdOverlay, #comprobanteOverlay { position: fixed; inset: 0; z-index: 10000; font-family: 'DM Sans',sans-serif; overflow: hidden; background: #0B0F14; color: #E8E4DC; }
        #capturaIdOverlay *, #comprobanteOverlay * { box-sizing: border-box; }
        .ov-intro { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; }
        .ov-panel { width: 100%; max-width: 400px; background: linear-gradient(170deg, rgba(45,212,191,0.04) 0%, transparent 40%); border: 1px solid rgba(45,212,191,0.1); border-radius: 18px; padding: 32px 24px 28px; display: flex; flex-direction: column; align-items: center; }
        .ov-icon { width: 50px; height: 50px; margin-bottom: 16px; border: 1.5px solid rgba(45,212,191,0.35); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .ov-badge { display: inline-block; font-size: 8.5px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: #2DD4BF; background: rgba(45,212,191,0.1); border: 1px solid rgba(45,212,191,0.18); padding: 3px 10px; border-radius: 20px; margin-bottom: 10px; }
        .ov-title { font-size: 20px; font-weight: 600; color: #E8E4DC; margin-bottom: 8px; line-height: 1.25; text-align: center; }
        .ov-title em { font-style: normal; color: #5EEAD4; }
        .ov-desc { font-size: 12px; color: rgba(232,228,220,0.42); line-height: 1.5; max-width: 320px; margin-bottom: 32px; text-align: center; }
        .ov-section { font-size: 8.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(232,228,220,0.3); margin-bottom: 10px; align-self: flex-start; }
        .ov-tips { display: flex; flex-direction: column; gap: 8px; margin-bottom: 32px; width: 100%; }
        .ov-tip { display: flex; align-items: center; gap: 10px; background: rgba(45,212,191,0.03); border: 1px solid rgba(45,212,191,0.07); border-radius: 10px; padding: 10px 14px; }
        .ov-tip-ic { width: 28px; height: 28px; flex-shrink: 0; background: rgba(45,212,191,0.08); border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .ov-tip b { font-size: 11.5px; font-weight: 500; display: block; color: #E8E4DC; }
        .ov-tip small { font-size: 10.5px; color: rgba(232,228,220,0.3); }
        .ov-btns { display: flex; flex-direction: column; gap: 16px; width: 100%; }
        .ov-btn-gold { padding: 14px 20px; background: linear-gradient(135deg, #2DD4BF 0%, #0F9080 100%); border: none; border-radius: 11px; color: #0B0F14; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
        .ov-btn-gold:hover { opacity: 0.88; }
        .ov-btn-line { padding: 13px 20px; background: transparent; border: 1.5px solid rgba(45,212,191,0.25); border-radius: 11px; color: #5EEAD4; font-family: inherit; font-size: 12.5px; font-weight: 500; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
        .ov-btn-line:hover { border-color: rgba(45,212,191,0.45); background: rgba(45,212,191,0.05); }
        .ov-btn-close { padding: 10px; background: none; border: none; color: rgba(232,228,220,0.28); font-size: 11.5px; font-family: inherit; cursor: pointer; transition: color 0.2s; align-self: center; }
        .ov-btn-close:hover { color: rgba(232,228,220,0.55); }
        .ov-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 28px; width: 100%; }
        .ov-gi { display: flex; align-items: center; gap: 8px; background: rgba(45,212,191,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 9px; padding: 8px 10px; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
        .ov-gi:hover { border-color: rgba(45,212,191,0.18); background: rgba(45,212,191,0.04); }
        .ov-gi.sel { border-color: rgba(45,212,191,0.35); background: rgba(45,212,191,0.05); border-left: 3px solid #2DD4BF; }
        .ov-gi .gi-em { font-size: 14px; flex-shrink: 0; }
        .ov-gi b { font-size: 10.5px; font-weight: 500; display: block; color: #E8E4DC; }
        .ov-gi small { font-size: 9.5px; color: rgba(232,228,220,0.3); }
        .ov-warn { padding: 8px 12px; font-size: 10.5px; background: rgba(45,212,191,0.05); border: 1px solid rgba(45,212,191,0.12); border-radius: 9px; color: rgba(94,234,212,0.8); margin-bottom: 24px; width: 100%; }
        @media (max-width: 480px) {
            .ov-intro { padding: 12px; }
            .ov-panel { padding: 22px 16px 18px; border-radius: 14px; max-width: 100%; }
            .ov-icon { width: 42px; height: 42px; font-size: 1.1rem; }
            .ov-title { font-size: 18px; }
            .ov-desc { font-size: 11.5px; margin-bottom: 16px; }
            .ov-tip { padding: 6px 10px; }
            .ov-tip-ic { width: 24px; height: 24px; font-size: 10px; }
            .ov-gi { padding: 6px 8px; }
        }
        /* ===== ID CAMERA STYLES ===== */
        .captura-id-screen { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        #capturaId-result.captura-id-screen { justify-content: flex-start; align-items: stretch; padding: 0; overflow-y: auto; flex-direction: column; display: flex; }
        #capturaId-camera { position: relative; background: #000; padding: 0; justify-content: flex-end; }
        #capturaId-video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .captura-id-overlay-cam { position: absolute; inset: 0; pointer-events: none; z-index: 10; }
        .captura-id-shadow-mask { position: absolute; inset: 0; background: radial-gradient(ellipse 70% 50% at 50% 50%, transparent 55%, rgba(0,0,0,0.7) 100%); }
        .captura-id-frame { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: min(88vw, 340px); aspect-ratio: 85.6/54; border-radius: 10px; z-index: 20; border: 1px solid rgba(45,212,191,0.3); }
        .captura-id-corner { position: absolute; width: 22px; height: 22px; }
        .captura-id-corner::before, .captura-id-corner::after { content: ''; position: absolute; background: #2DD4BF; border-radius: 2px; }
        .captura-id-corner::before { width: 100%; height: 2.5px; }
        .captura-id-corner::after { width: 2.5px; height: 100%; }
        .captura-id-corner.tl { top: 0; left: 0; }
        .captura-id-corner.tr { top: 0; right: 0; }
        .captura-id-corner.tr::before { right: 0; } .captura-id-corner.tr::after { right: 0; }
        .captura-id-corner.bl { bottom: 0; left: 0; }
        .captura-id-corner.bl::before { bottom: 0; } .captura-id-corner.bl::after { bottom: 0; }
        .captura-id-corner.br { bottom: 0; right: 0; }
        .captura-id-corner.br::before { bottom: 0; right: 0; } .captura-id-corner.br::after { bottom: 0; right: 0; }
        @keyframes capturaId-scan { 0% { top: 8%; opacity: 1; } 50% { top: 92%; } 51% { top: 8%; opacity: 0; } 100% { top: 92%; opacity: 1; } }
        .captura-id-scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #2DD4BF, transparent); animation: capturaId-scan 2.4s ease-in-out infinite; }
        .captura-id-instruction { position: absolute; z-index: 30; left: 0; right: 0; text-align: center; font-size: 13px; color: rgba(255,255,255,0.7); }
        .captura-id-align-badge { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; background: rgba(0,0,0,0.55); border-radius: 30px; border: 1px solid rgba(255,255,255,0.1); font-size: 12.5px; }
        .captura-id-align-dot { width: 7px; height: 7px; border-radius: 50%; background: #2DD4BF; }
        .captura-id-align-dot.ok { background: #4ADE80; }
        .captura-id-hud-top { position: absolute; top: 0; left: 0; right: 0; z-index: 30; padding: max(env(safe-area-inset-top), 16px) 20px; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent); }
        .captura-id-hud-back { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; border: none; cursor: pointer; color: #F4F2EE; font-size: 18px; }
        .captura-id-hud-label { font-size: 14px; font-weight: 500; }
        .captura-id-side-indicator { font-size: 11px; background: rgba(45,212,191,0.18); border: 1px solid rgba(45,212,191,0.35); color: #5EEAD4; padding: 5px 10px; border-radius: 20px; }
        .captura-id-hud-bottom { position: absolute; bottom: 0; left: 0; right: 0; z-index: 30; padding: 20px 32px max(env(safe-area-inset-bottom), 28px); background: linear-gradient(to top, rgba(0,0,0,0.75), transparent); display: flex; align-items: center; justify-content: space-between; }
        .captura-id-btn-flip, .captura-id-btn-torch { width: 48px; height: 48px; background: rgba(255,255,255,0.1); border-radius: 50%; border: none; cursor: pointer; color: #F4F2EE; font-size: 18px; }
        .captura-id-shutter-wrap { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .captura-id-shutter { width: 72px; height: 72px; border-radius: 50%; background: transparent; border: 3px solid #2DD4BF; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .captura-id-shutter-inner { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #5EEAD4, #2DD4BF); }
        .captura-id-shutter-label { font-size: 10.5px; color: rgba(244,242,238,0.38); }
        @keyframes capturaId-flash { 0% { opacity: 0; } 10% { opacity: 1; } 100% { opacity: 0; } }
        .captura-id-flash-fx { position: absolute; inset: 0; z-index: 99; background: #fff; opacity: 0; pointer-events: none; }
        .captura-id-flash-fx.go { animation: capturaId-flash 0.35s ease-out forwards; }
        .captura-id-result-header { background: #111118; border-bottom: 1px solid rgba(255,255,255,0.06); padding: 20px 24px; display: flex; align-items: center; gap: 14px; }
        .captura-id-result-header button { background: none; border: none; cursor: pointer; color: rgba(244,242,238,0.38); font-size: 14px; font-family: inherit; }
        .captura-id-result-header h2 { font-size: 17px; font-weight: 600; }
        .captura-id-result-body { padding: 24px; flex: 1; overflow-y: auto; }
        .captura-id-captured-card { background: #1A1A26; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 20px; }
        #capturaId-captured-img { width: 100%; display: block; aspect-ratio: 85.6/54; object-fit: cover; }
        .captura-id-captured-meta { padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); }
        .captura-id-meta-label { font-size: 11px; color: rgba(244,242,238,0.38); text-transform: uppercase; }
        .captura-id-meta-side { font-size: 12px; color: #5EEAD4; background: rgba(45,212,191,0.15); padding: 4px 10px; border-radius: 20px; }
        .captura-id-quality-bar { background: #1A1A26; border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 16px 18px; margin-bottom: 20px; }
        .captura-id-quality-title { font-size: 11.5px; font-weight: 600; color: rgba(244,242,238,0.38); margin-bottom: 12px; text-transform: uppercase; }
        .captura-id-quality-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .captura-id-quality-status.ok { color: #4ADE80; }
        .captura-id-quality-status.warn { color: #2DD4BF; }
        .captura-id-result-actions { display: flex; flex-direction: column; gap: 10px; }
        .captura-id-btn-use { padding: 15px; background: linear-gradient(135deg, #2DD4BF, #0F9080); border: none; border-radius: 13px; color: #0A0A0F; font-family: inherit; font-size: 15px; font-weight: 600; cursor: pointer; }
        .captura-id-btn-retake { padding: 14px; background: transparent; border: 1px solid rgba(255,255,255,0.12); border-radius: 13px; color: rgba(244,242,238,0.38); font-family: inherit; font-size: 14px; cursor: pointer; }
        .captura-id-btn-retake:hover { border-color: rgba(255,255,255,0.22); color: #F4F2EE; }
    </style>
    <style id="comprobante-styles">
        @keyframes comp-scan { 0% { top: 4%; opacity: 1; } 50% { top: 96%; opacity: 0.3; } 51% { top: 4%; opacity: 0; } 100% { top: 96%; opacity: 1; } }
        #comp-flash-fx.go { animation: capturaId-flash 0.32s ease-out forwards; }
        #comprobanteOverlay [id^="comp-q-"][id$="-bar"] { height: 100%; border-radius: 2px; background: #4ADE80; transition: width 0.6s ease; }
        #comprobanteOverlay [id^="comp-q-"][id$="-bar"].warn { background: #FBBF24; }
        #comprobanteOverlay [id^="comp-q-"][id$="-val"].warn { color: #FBBF24; }
    </style>
</head>
<body>
    <div class="form-card">
        <div class="form-card-header">
            <img src="/assets/img/logo_ico2.svg" alt="Maxikash" onerror="this.style.display='none'">
            <?php if ($expediente_completo): ?>
                <h1>Documentación recibida</h1>
                <p>Su expediente está completo</p>
            <?php else: ?>
                <h1>Subir documentos</h1>
                <p>Completa tu postulación adjuntando tus documentos</p>
            <?php endif; ?>
        </div>
        <div class="form-card-body">
            <?php if ($error_mensaje !== ''): ?>
                <div class="alert-error"><?= htmlspecialchars($error_mensaje) ?></div>
            <?php elseif ($expediente_completo): ?>
                <div class="expediente-completo">
                    <div class="expediente-completo-icon" aria-hidden="true"><i class="fa fa-check-circle"></i></div>
                    <h2 class="expediente-completo-title">Documentación completa</h2>
                    <p class="expediente-completo-texto">Ha subido correctamente todos los documentos requeridos. El equipo de <strong>Capital Humano</strong> revisará su expediente y se pondrá en contacto con usted a la brevedad.</p>
                    <p class="expediente-completo-agradecimiento">Gracias por su interés en formar parte de nuestro equipo.</p>
                </div>
            <?php else: ?>
                <div class="candidato-name"><?= htmlspecialchars($nombre_candidato) ?></div>
                <form id="formSubirDocumentos" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <?php
                    $urlBaseDescarga = '/CapHum/descargarDocumentoCandidato/' . urlencode($token);
                    // Sin botón "Tomar foto": identificación oficial (5) y comprobante (6) solo por archivo.
                    $documentosConFoto = [];
                    $docsSoloPdf = [3, 4, 5, 6, 7, 8, 10]; // acta, CURP, identificación, comprobante, constancia fiscal, NSS, estado de cuenta
                    foreach ($documentos as $num => $nombreDoc):
                        $esSolicitud = ($num === 1);
                        $esCartaAdeudo = ($num === 9);
                        $permiteFoto = in_array($num, $documentosConFoto, true);
                        $soloPdf = in_array($num, $docsSoloPdf, true);
                        $yaSubido = isset($documentos_subidos[$num]);
                    ?>
                    <div class="form-group" data-doc-num="<?= $num ?>">
                        <label for="archivo_<?= $num ?>"><?= $num ?>. <?= htmlspecialchars($nombreDoc) ?><?= $num === 5 ? ' <span class="text-muted" style="font-weight:400;">(un solo archivo PDF con frente y reverso)</span>' : '' ?></label>
                        <?php if ($yaSubido): ?>
                        <div class="doc-ya-subido py-2 px-3 rounded" style="background:#e8f5e9;color:#2e7d32;">
                            <i class="fa fa-check-circle me-1"></i> Ya subido: <?= htmlspecialchars($documentos_subidos[$num]['nombre_archivo'] ?? 'documento') ?>
                        </div>
                        <input type="hidden" name="archivo_<?= $num ?>_ya_subido" value="1">
                        <?php else: ?>
                        <?php if ($esSolicitud): ?>
                        <div class="descarga-doc mb-2">
                            <a href="<?= htmlspecialchars($urlBaseDescarga) ?>/solicitud_interna" class="btn-descarga" target="_blank" rel="noopener"><i class="fa fa-download me-1"></i> Descargar solicitud</a>
                            <a href="/CapHum/llenarSolicitudEnLinea/<?= htmlspecialchars($token) ?>" class="btn-descarga btn-llenar"><i class="fa fa-edit me-1"></i> Llenar solicitud en línea</a>
                            <span class="d-block small-text mt-1">Descarga la plantilla en blanco para llenarla a mano o en computadora, o usa el formulario en línea. Luego guárdalo, fírmalo y súbelo aquí.</span>
                        </div>
                        <?php elseif ($esCartaAdeudo): ?>
                        <div class="descarga-doc mb-2">
                            <a href="<?= htmlspecialchars($urlBaseDescarga) ?>/carta_no_adeudo" class="btn-descarga" target="_blank" rel="noopener"><i class="fa fa-download me-1"></i> Descargar carta de no adeudo</a>
                            <p class="small-text mt-2 mb-1">Si tienes crédito INFONAVIT o FONACOT y no tienes la hoja de retención: descarga la carta, llénala, fírmala y copia <strong>a mano</strong> en ella el texto que se indica abajo. Luego súbela aquí.</p>
                            <details class="carta-parrafo-mano mt-2" style="border:1px solid #dee2e6;border-radius:8px;background:#fafafa;">
                                <summary style="padding:0.5rem 0.75rem;cursor:pointer;font-size:0.9rem;list-style:none;display:flex;align-items:center;gap:0.35rem;">
                                    <i class="fa fa-chevron-right" style="transition:transform 0.2s;"></i>
                                    <span><strong>Ver texto que debes copiar a mano en la carta</strong></span>
                                </summary>
                                <div class="px-3 pb-3 pt-1" style="font-size:0.88rem;line-height:1.5;">
                                    <p class="mb-2">Yo ___________ declaro tener activo el crédito (INFONAVIT o FONACOT) con número _________________ en el cual tengo una cuota fija de $__________________ y será ajustado en mi salario acorde a la normativa vigente, inclusive si hay un acumulado pendiente.</p>
                                    <p class="text-muted small mb-0">Primer espacio: nombre completo. Segundo: número de crédito. Tercero: monto de la cuota. <strong>Debe estar escrito a mano por usted.</strong></p>
                                </div>
                            </details>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="file" class="form-control-file" id="archivo_<?= $num ?>" name="archivo_<?= $num ?>" accept="<?= $soloPdf ? '.pdf' : '.pdf,.doc,.docx,.jpg,.jpeg,.png' ?>">
                            <?php if ($num === 4): ?>
                            <span id="curp-verificado" class="doc-check-inline" style="display:none;color:#2e7d32;font-weight:600;"><i class="fa fa-check-circle me-1"></i> CURP verificado</span>
                            <?php endif; ?>
                            <?php if ($num === 5): ?>
                            <span id="id-verificado-frente" class="doc-check-inline" style="display:none;color:#2e7d32;font-weight:600;"><i class="fa fa-check-circle me-1"></i> Identificación verificada</span>
                            <?php endif; ?>
                            <?php if ($num === 6): ?>
                            <span id="comp-verificado" class="doc-check-inline" style="display:none;color:#2e7d32;font-weight:600;"><i class="fa fa-check-circle me-1"></i> Comprobante verificado</span>
                            <?php endif; ?>
                            <?php if ($num === 7): ?>
                            <span id="fiscal-verificado" class="doc-check-inline" style="display:none;color:#2e7d32;font-weight:600;"><i class="fa fa-check-circle me-1"></i> Constancia fiscal verificada</span>
                            <?php endif; ?>
                            <?php if ($num === 8): ?>
                            <span id="nss-verificado" class="doc-check-inline" style="display:none;color:#2e7d32;font-weight:600;"><i class="fa fa-check-circle me-1"></i> NSS verificado</span>
                            <?php endif; ?>
                            <?php if ($permiteFoto): ?>
                            <input type="file" id="archivo_<?= $num ?>_foto" name="archivo_<?= $num ?>_foto" accept="image/*" capture="environment" class="d-none">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-tomar-foto <?= $num === 5 ? 'btn-captura-identificacion' : '' ?> <?= $num === 6 ? 'btn-captura-comprobante' : '' ?>" data-target="<?= $num ?>" <?= $num === 5 ? 'data-es-identificacion="1"' : '' ?> <?= $num === 6 ? 'data-es-comprobante="1"' : '' ?> title="Abrir cámara para tomar foto">
                                <i class="fa fa-camera me-1"></i> Tomar foto
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <p class="small-text">Puedes subir los documentos por partes: envía los que tengas ahora y el resto después. Los que ya enviaste no se pueden cambiar. Formatos: PDF, JPG, PNG.</p>
                    <button type="submit" class="btn-submit" id="btnEnviar">Subir documentos</button>
                </form>
                <div id="mensajeResultado"></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Modal cámara para tomar foto -->
    <div id="modalCamara" class="modal-camara-overlay" aria-hidden="true">
        <div class="modal-camara">
            <div class="modal-camara-header">
                <span id="modalCamaraTitulo">Tomar foto</span>
                <button type="button" class="btn-cancelar-camara" id="btnCerrarModalCamara" style="background:transparent;color:inherit;border:none;cursor:pointer;padding:0.25rem;"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-camara-body">
                <video id="videoCamara" autoplay playsinline muted></video>
                <canvas id="canvasCamara"></canvas>
                <p id="msgErrorCamara" class="msg-error-camara" style="display:none;"></p>
            </div>
            <div class="modal-camara-actions">
                <button type="button" class="btn-cancelar-camara" id="btnCancelarCamara">Cancelar</button>
                <button type="button" class="btn-capturar" id="btnCapturarFoto">Capturar</button>
            </div>
        </div>
    </div>
    <!-- Overlay captura de identificación (flujo mejorado solo para doc 5) -->
    <div id="capturaIdOverlay" class="captura-id-overlay" style="display:none;" aria-hidden="true">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
        <div id="capturaId-intro" class="ov-intro">
            <div class="ov-panel">
                <div class="ov-icon">&#128203;</div>
                <div class="ov-badge">Identificación oficial</div>
                <h2 class="ov-title">Captura de <em>Identificación</em></h2>
                <p class="ov-desc">Necesitamos una foto clara de tu INE, pasaporte u otra identificación válida.</p>
                <div class="ov-section">Recomendaciones</div>
                <div class="ov-tips">
                    <div class="ov-tip"><span class="ov-tip-ic">&#128161;</span><div><b>Buena iluminación</b><small>Evita sombras o reflejos sobre tu ID</small></div></div>
                    <div class="ov-tip"><span class="ov-tip-ic">&#127919;</span><div><b>Ajusta el encuadre</b><small>Centra tu ID dentro del marco</small></div></div>
                    <div class="ov-tip"><span class="ov-tip-ic">&#9995;</span><div><b>Mano firme</b><small>Mantén el teléfono estable al tomar la foto</small></div></div>
                </div>
                <div class="ov-btns">
                    <button type="button" class="ov-btn-gold" id="capturaId-btn-frente">Capturar Frente del ID</button>
                    <button type="button" class="ov-btn-line" id="capturaId-btn-reverso">Capturar Reverso</button>
                    <button type="button" class="ov-btn-close" id="capturaId-btn-cerrar-intro">Cerrar</button>
                </div>
            </div>
        </div>
        <div id="capturaId-camera" class="captura-id-screen" style="display:none;">
            <video id="capturaId-video" autoplay playsinline muted></video>
            <canvas id="capturaId-canvas" style="display:none;"></canvas>
            <div class="captura-id-overlay-cam"><div class="captura-id-shadow-mask"></div></div>
            <div class="captura-id-frame" id="capturaId-id-frame">
                <div class="captura-id-corner tl"></div><div class="captura-id-corner tr"></div>
                <div class="captura-id-corner bl"></div><div class="captura-id-corner br"></div>
                <div class="captura-id-scan-line"></div>
            </div>
            <div class="captura-id-instruction" id="capturaId-instruction">
                <div class="captura-id-align-badge"><span class="captura-id-align-dot" id="capturaId-align-dot"></span><span id="capturaId-align-text">Centrando identificación…</span></div>
            </div>
            <div class="captura-id-hud-top">
                <button type="button" class="captura-id-hud-back" id="capturaId-btn-back"><span aria-hidden="true">&#8592;</span></button>
                <span class="captura-id-hud-label">Identificación Oficial</span>
                <span class="captura-id-side-indicator" id="capturaId-side-indicator">FRENTE</span>
            </div>
            <div class="captura-id-hud-bottom">
                <button type="button" class="captura-id-btn-flip" id="capturaId-btn-flip" title="Cambiar cámara">&#8634;</button>
                <div class="captura-id-shutter-wrap">
                    <button type="button" class="captura-id-shutter" id="capturaId-shutter"><span class="captura-id-shutter-inner"></span></button>
                    <span class="captura-id-shutter-label">CAPTURAR</span>
                </div>
                <button type="button" class="captura-id-btn-torch" id="capturaId-btn-torch" title="Linterna">&#128294;</button>
            </div>
            <div id="capturaId-flash-fx" class="captura-id-flash-fx"></div>
        </div>
        <div id="capturaId-result" class="captura-id-screen" style="display:none;">
            <div class="captura-id-result-header">
                <button type="button" class="captura-id-result-back" id="capturaId-btn-retomar">Retomar</button>
                <h2>Vista Previa</h2>
            </div>
            <div class="captura-id-result-body">
                <div class="captura-id-captured-card">
                    <img id="capturaId-captured-img" src="" alt="ID Capturado"/>
                    <div class="captura-id-captured-meta"><span class="captura-id-meta-label">Identificación capturada</span><span class="captura-id-meta-side" id="capturaId-result-side">FRENTE</span></div>
                </div>
                <div class="captura-id-quality-bar">
                    <div class="captura-id-quality-title">Verificación de Calidad</div>
                    <div class="captura-id-quality-item"><span class="captura-id-quality-icon">&#128262;</span><span class="captura-id-quality-text">Iluminación</span><span class="captura-id-quality-status ok" id="capturaId-q-light">Correcta</span></div>
                    <div class="captura-id-quality-item"><span class="captura-id-quality-icon">&#128203;</span><span class="captura-id-quality-text">Encuadre</span><span class="captura-id-quality-status ok" id="capturaId-q-frame">Centrado</span></div>
                    <div class="captura-id-quality-item"><span class="captura-id-quality-icon">&#128269;</span><span class="captura-id-quality-text">Nitidez</span><span class="captura-id-quality-status ok" id="capturaId-q-blur">Clara</span></div>
                </div>
                <div class="captura-id-result-actions">
                    <button type="button" class="captura-id-btn-use" id="capturaId-btn-use">Usar esta Foto</button>
                    <button type="button" class="captura-id-btn-retake" id="capturaId-btn-retake">Tomar Otra Foto</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Overlay captura Comprobante de domicilio (doc 6) -->
    <div id="comprobanteOverlay" style="display:none;" aria-hidden="true">
        <div id="comp-intro" class="ov-intro">
            <div class="ov-panel">
                <div class="ov-icon">&#127968;</div>
                <div class="ov-badge">Comprobante de domicilio</div>
                <h2 class="ov-title">Comprobante de <em>Domicilio</em></h2>
                <p class="ov-desc">Necesitamos un documento que acredite tu dirección actual, con antigüedad no mayor a 3 meses.</p>
                <div class="ov-section">Documentos aceptados</div>
                <div class="ov-grid" id="comp-doc-types">
                    <div class="ov-gi sel" data-doc="Recibo de Luz"><span class="gi-em">&#9889;</span><div><b>CFE / Luz</b><small>Recibo eléctrico</small></div></div>
                    <div class="ov-gi" data-doc="Recibo de Agua"><span class="gi-em">&#128166;</span><div><b>Agua</b><small>SIAPA / SACMEX</small></div></div>
                    <div class="ov-gi" data-doc="Recibo de Gas"><span class="gi-em">&#128293;</span><div><b>Gas</b><small>Natural o LP</small></div></div>
                    <div class="ov-gi" data-doc="Estado de Cuenta"><span class="gi-em">&#128176;</span><div><b>Banco</b><small>Estado de cuenta</small></div></div>
                    <div class="ov-gi" data-doc="Recibo de Teléfono"><span class="gi-em">&#128225;</span><div><b>Teléfono / Internet</b><small>Recibo de servicio</small></div></div>
                    <div class="ov-gi" data-doc="Predial"><span class="gi-em">&#127968;</span><div><b>Predial</b><small>Boleta catastral</small></div></div>
                </div>
                <div class="ov-section">Recomendaciones</div>
                <div class="ov-tips">
                    <div class="ov-tip"><span class="ov-tip-ic">&#128196;</span><div><b>Documento extendido</b><small>Superficie plana y sin arrugas</small></div></div>
                    <div class="ov-tip"><span class="ov-tip-ic">&#128262;</span><div><b>Sin reflejos</b><small>Evita luz directa sobre el papel</small></div></div>
                    <div class="ov-tip"><span class="ov-tip-ic">&#128203;</span><div><b>Encuadre completo</b><small>Incluye los 4 bordes en el marco</small></div></div>
                </div>
                <div class="ov-warn">El documento debe tener fecha de emisión de <strong>no más de 3 meses</strong></div>
                <div class="ov-btns">
                    <button type="button" class="ov-btn-gold" id="comp-btn-start">Tomar Foto del Comprobante</button>
                    <button type="button" class="ov-btn-close" id="comp-btn-cerrar-intro">Cerrar</button>
                </div>
            </div>
        </div>
        <div id="comp-camera" class="comp-screen" style="display:none; position:relative; width:100%; height:100%; background:#000;">
            <video id="comp-video" autoplay playsinline muted style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;"></video>
            <canvas id="comp-canvas" style="display:none;"></canvas>
            <div class="comp-shadow-mask" style="position:absolute; inset:0; background:rgba(0,0,0,0.45); pointer-events:none; z-index:8;"></div>
            <div class="comp-doc-frame" id="comp-doc-frame" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:min(72vw, 280px); aspect-ratio:210/297; z-index:20; border:1px solid rgba(45,212,191,0.35); border-radius:4px;">
                <div class="comp-corner tl" style="position:absolute; top:0; left:0; width:24px; height:24px;"><span style="position:absolute; top:0; left:0; width:100%; height:2.5px; background:#2DD4BF; border-radius:2px;"></span><span style="position:absolute; top:0; left:0; width:2.5px; height:100%; background:#2DD4BF; border-radius:2px;"></span></div>
                <div class="comp-corner tr" style="position:absolute; top:0; right:0; width:24px; height:24px;"><span style="position:absolute; top:0; right:0; width:100%; height:2.5px; background:#2DD4BF; border-radius:2px;"></span><span style="position:absolute; top:0; right:0; width:2.5px; height:100%; background:#2DD4BF; border-radius:2px;"></span></div>
                <div class="comp-corner bl" style="position:absolute; bottom:0; left:0; width:24px; height:24px;"><span style="position:absolute; bottom:0; left:0; width:100%; height:2.5px; background:#2DD4BF; border-radius:2px;"></span><span style="position:absolute; bottom:0; left:0; width:2.5px; height:100%; background:#2DD4BF; border-radius:2px;"></span></div>
                <div class="comp-corner br" style="position:absolute; bottom:0; right:0; width:24px; height:24px;"><span style="position:absolute; bottom:0; right:0; width:100%; height:2.5px; background:#2DD4BF; border-radius:2px;"></span><span style="position:absolute; bottom:0; right:0; width:2.5px; height:100%; background:#2DD4BF; border-radius:2px;"></span></div>
                <div class="comp-scan-line" style="position:absolute; left:0; right:0; height:1.5px; background:linear-gradient(90deg, transparent, #2DD4BF, transparent); animation:comp-scan 2.8s ease-in-out infinite;"></div>
            </div>
            <div class="comp-instruction" id="comp-instruction" style="position:absolute; z-index:30; left:0; right:0; text-align:center;">
                <div class="comp-align-badge" style="display:inline-flex; align-items:center; gap:7px; padding:7px 14px; background:rgba(0,0,0,0.6); border-radius:30px; border:1px solid rgba(255,255,255,0.08); font-size:12.5px;">
                    <span class="comp-align-dot" id="comp-align-dot" style="width:7px; height:7px; border-radius:50%; background:#2DD4BF;"></span>
                    <span id="comp-align-text">Coloca el comprobante dentro del marco</span>
                </div>
            </div>
            <div class="comp-hud-top" style="position:absolute; top:0; left:0; right:0; z-index:30; padding:max(env(safe-area-inset-top),14px) 18px 14px; background:linear-gradient(to bottom, rgba(0,0,0,0.72), transparent); display:flex; align-items:center; justify-content:space-between;">
                <button type="button" class="comp-hud-back" id="comp-btn-back" style="width:38px; height:38px; background:rgba(255,255,255,0.1); border-radius:50%; border:none; cursor:pointer; color:#EEF4F6; font-size:17px;">&#8592;</button>
                <div style="text-align:center;"><div style="font-size:13.5px; font-weight:600;">Comprobante</div><div id="comp-hud-doc-type" style="font-size:10.5px; color:#5EEAD4; background:rgba(45,212,191,0.15); border:1px solid rgba(45,212,191,0.3); padding:3px 10px; border-radius:20px; display:inline-block; margin-top:3px;">Recibo de Luz</div></div>
                <span style="font-size:10.5px; background:rgba(45,212,191,0.12); border:1px solid rgba(45,212,191,0.25); color:#5EEAD4; padding:5px 10px; border-radius:20px;">DOCUMENTO</span>
            </div>
            <div class="comp-hud-bottom" style="position:absolute; bottom:0; left:0; right:0; z-index:30; padding:16px 32px max(env(safe-area-inset-bottom),24px); background:linear-gradient(to top, rgba(0,0,0,0.78), transparent); display:flex; align-items:center; justify-content:space-between;">
                <button type="button" class="comp-btn-flip" id="comp-btn-flip" style="width:46px; height:46px; background:rgba(255,255,255,0.1); border-radius:50%; border:none; cursor:pointer; color:#EEF4F6; font-size:19px;">&#8634;</button>
                <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                    <button type="button" class="comp-shutter" id="comp-shutter" style="width:70px; height:70px; border-radius:50%; border:3px solid #2DD4BF; background:transparent; display:flex; align-items:center; justify-content:center; cursor:pointer;"><span style="width:54px; height:54px; border-radius:50%; background:linear-gradient(135deg, #5EEAD4, #2DD4BF);"></span></button>
                    <span style="font-size:10.5px; color:rgba(238,244,246,0.38);">CAPTURAR</span>
                </div>
                <button type="button" class="comp-btn-torch" id="comp-btn-torch" style="width:46px; height:46px; background:rgba(255,255,255,0.1); border-radius:50%; border:none; cursor:pointer; color:#EEF4F6;" title="Linterna">&#128294;</button>
            </div>
            <div id="comp-flash-fx" style="position:absolute; inset:0; z-index:99; background:#fff; opacity:0; pointer-events:none;"></div>
        </div>
        <div id="comp-result" class="comp-screen" style="display:none; width:100%; height:100%; flex-direction:column; background:#080E12; overflow-y:auto;">
            <div class="comp-result-header" style="background:#0D1519; border-bottom:1px solid rgba(255,255,255,0.06); padding:18px 22px; display:flex; align-items:center; gap:12px;">
                <button type="button" id="comp-btn-retomar" style="background:none; border:none; cursor:pointer; color:rgba(238,244,246,0.38); font-size:14px;">Retomar</button>
                <h2 style="font-size:17px; font-weight:600;">Vista Previa</h2>
            </div>
            <div class="comp-result-body" style="padding:22px; flex:1;">
                <div class="comp-captured-card" style="background:#131E24; border:1px solid rgba(255,255,255,0.08); border-radius:16px; overflow:hidden; margin-bottom:18px;">
                    <img id="comp-captured-img" src="" alt="Comprobante" style="width:100%; display:block; max-height:52vw; object-fit:cover; object-position:top;"/>
                    <div style="padding:12px 16px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid rgba(255,255,255,0.06);">
                        <div><div style="font-size:11px; color:rgba(238,244,246,0.38); text-transform:uppercase;">Documento capturado</div><div id="comp-result-doc-name" style="font-size:13px; font-weight:500; margin-top:2px;">Recibo de Luz</div></div>
                        <span style="font-size:11px; color:#5EEAD4; background:rgba(45,212,191,0.12); padding:4px 10px; border-radius:20px;">CAPTURADO</span>
                    </div>
                </div>
                <div class="comp-quality-card" style="background:#131E24; border:1px solid rgba(255,255,255,0.06); border-radius:14px; padding:16px 18px; margin-bottom:18px;">
                    <div style="font-size:11px; font-weight:700; color:rgba(238,244,246,0.38); letter-spacing:0.08em; text-transform:uppercase; margin-bottom:14px;">Verificación de Calidad</div>
                    <div class="comp-quality-item" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;"><span style="font-size:15px;">&#128262;</span><span style="font-size:13px; flex:1;">Iluminación</span><div style="width:80px; height:4px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden;"><div id="comp-q-light-bar" style="height:100%; width:0%; background:#4ADE80; border-radius:2px;"></div></div><span id="comp-q-light-val" style="font-size:11.5px; font-weight:600; color:#4ADE80;">—</span></div>
                    <div class="comp-quality-item" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;"><span style="font-size:15px;">&#127912;</span><span style="font-size:13px; flex:1;">Contraste</span><div style="width:80px; height:4px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden;"><div id="comp-q-contrast-bar" style="height:100%; width:0%; background:#4ADE80; border-radius:2px;"></div></div><span id="comp-q-contrast-val" style="font-size:11.5px; font-weight:600; color:#4ADE80;">—</span></div>
                    <div class="comp-quality-item" style="display:flex; align-items:center; gap:10px;"><span style="font-size:15px;">&#128203;</span><span style="font-size:13px; flex:1;">Encuadre</span><div style="width:80px; height:4px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden;"><div id="comp-q-frame-bar" style="height:100%; width:0%; background:#4ADE80; border-radius:2px;"></div></div><span id="comp-q-frame-val" style="font-size:11.5px; font-weight:600; color:#4ADE80;">—</span></div>
                </div>
                <div class="comp-reminder" style="background:rgba(251,191,36,0.07); border:1px solid rgba(251,191,36,0.18); border-radius:12px; padding:13px 15px; margin-bottom:18px; font-size:12.5px; color:rgba(251,191,36,0.8);">Verifica que la <strong>fecha de emisión</strong> sea visible y no tenga más de 3 meses.</div>
                <div class="comp-result-actions" style="display:flex; flex-direction:column; gap:10px;">
                    <button type="button" class="comp-btn-use" id="comp-btn-use" style="padding:15px; background:linear-gradient(135deg, #2DD4BF, #0F9080); border:none; border-radius:13px; color:#080E12; font-family:inherit; font-size:15px; font-weight:600; cursor:pointer;">Usar este Comprobante</button>
                    <button type="button" class="comp-btn-retake" id="comp-btn-retake" style="padding:14px; background:transparent; border:1px solid rgba(255,255,255,0.1); border-radius:13px; color:rgba(238,244,246,0.38); font-family:inherit; font-size:14px; cursor:pointer;">Tomar Otra Foto</button>
                </div>
            </div>
        </div>
    </div>
    <?php if ($error_mensaje === '' && $token !== ''): ?>
    <script>
        (function() {
            var modal = document.getElementById('modalCamara');
            var video = document.getElementById('videoCamara');
            var canvas = document.getElementById('canvasCamara');
            var ctx = canvas.getContext('2d');
            var msgError = document.getElementById('msgErrorCamara');
            var btnCapturar = document.getElementById('btnCapturarFoto');
            var streamActual = null;
            var targetNum = null;

            var API_BASE = <?= json_encode($api_verificacion_base) ?>;
            var API_KEY = 'sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key';
            var idVerificado = { front: false, back: false };
            var VERIFICACION_TIMEOUT_MS = 30000;
            var VERIFICACION_IDENTIFICACION_TIMEOUT_MS = 120000; // 2 min: OCR + analizadores pueden tardar
            var VERIFICACION_CALIDAD_TIMEOUT_MS = 20000; // 20 s para revisión ligera (solo calidad de imagen)

            function fetchWithTimeout(url, options, timeoutMs) {
                timeoutMs = timeoutMs || VERIFICACION_TIMEOUT_MS;
                var ctrl = new AbortController();
                var id = setTimeout(function() { ctrl.abort(); }, timeoutMs);
                var opts = Object.assign({}, options, { signal: ctrl.signal });
                return fetch(url, opts).then(function(r) {
                    clearTimeout(id);
                    return r;
                }, function(err) {
                    clearTimeout(id);
                    throw err;
                });
            }

            function verificarDocumentoAPI(blob, tipoDoc, side) {
                return new Promise(function(resolve, reject) {
                    var formData = new FormData();
                    formData.append('imagen', blob, side + '.jpg');
                    var url = API_BASE + '/verificar?tipo_documento=' + encodeURIComponent(tipoDoc);
                    fetchWithTimeout(url, { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_IDENTIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(resolve)
                    .catch(function(err) {
                        if (err && err.name === 'AbortError') {
                            reject(new Error('Verificación tardó demasiado. Revisa tu conexión o que la API esté encendida e intenta de nuevo.'));
                        } else {
                            reject(err);
                        }
                    });
                });
            }

            /** Revisión ligera (calidad + opcional frente/reverso). Si ladoEsperado es 'frente' o 'reverso', la API valida que la imagen sea de ese lado. */
            function verificarCalidadDocumentoAPI(blob, ladoEsperado) {
                return new Promise(function(resolve, reject) {
                    var formData = new FormData();
                    formData.append('imagen', blob, 'documento.jpg');
                    var url = API_BASE + '/verificar-calidad';
                    if (ladoEsperado === 'frente' || ladoEsperado === 'reverso') {
                        url += '?lado_esperado=' + encodeURIComponent(ladoEsperado);
                    }
                    fetchWithTimeout(url, { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_CALIDAD_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) {
                            return r.json().catch(function() { return {}; }).then(function(body) {
                                throw new Error((body && body.mensaje) ? body.mensaje : 'La API respondió con error. Intenta de nuevo.');
                            });
                        }
                        return r.json();
                    })
                    .then(resolve)
                    .catch(function(err) {
                        if (err && err.name === 'AbortError') {
                            reject(new Error('Verificación tardó demasiado. Revisa tu conexión o que la API esté encendida e intenta de nuevo.'));
                        } else {
                            reject(err);
                        }
                    });
                });
            }

            function verificarComprobanteAPI(blob, filename) {
                return new Promise(function(resolve, reject) {
                    var formData = new FormData();
                    formData.append('documento', blob, filename || 'comprobante.jpg');
                    fetchWithTimeout(API_BASE + '/verificar-comprobante', { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(resolve)
                    .catch(function(err) {
                        if (err && err.name === 'AbortError') {
                            reject(new Error('Verificación tardó demasiado. Revisa tu conexión o que la API esté encendida e intenta de nuevo.'));
                        } else {
                            reject(err);
                        }
                    });
                });
            }

            function actualizarCheckmark(docNum, aprobado) {
                var label = document.querySelector('label[for="archivo_' + docNum + '"]');
                if (!label) return;
                var existing = label.querySelector('.doc-check');
                if (existing) existing.remove();
                if (aprobado) {
                    var check = document.createElement('span');
                    check.className = 'doc-check';
                    check.style.cssText = 'color:#2e7d32;margin-left:8px;font-weight:700;';
                    check.innerHTML = '&#10003; Verificado';
                    label.appendChild(check);
                }
            }

            var MSG_AUTO_OCULTAR_MS = 10000;
            var verificacionesPendientes = 0;

            function actualizarBotonEnviar() {
                var btn = document.getElementById('btnEnviar');
                if (btn) {
                    btn.disabled = verificacionesPendientes > 0;
                    btn.textContent = verificacionesPendientes > 0 ? 'Verificando... (no se puede subir aún)' : 'Subir documentos';
                    btn.setAttribute('title', verificacionesPendientes > 0 ? 'Espera a que termine la verificación' : '');
                }
            }

            function showVerificando(cont, texto) {
                if (!cont) return null;
                verificacionesPendientes++;
                actualizarBotonEnviar();
                var div = document.createElement('div');
                div.className = 'msg-verificando';
                div.textContent = texto;
                cont.appendChild(div);
                return div;
            }
            function showResultado(cont, verificandoDiv, html, esError) {
                if (!cont) return;
                verificacionesPendientes--;
                actualizarBotonEnviar();
                if (verificandoDiv && verificandoDiv.parentNode) verificandoDiv.remove();
                var div = document.createElement('div');
                div.className = 'msg-result-item ' + (esError ? 'alert-error' : 'alert-success');
                div.innerHTML = html;
                if (esError) { div.style.background = '#fef2f2'; div.style.border = '1px solid #fecaca'; div.style.color = '#b91c1c'; }
                else { div.style.background = '#f0fdf4'; div.style.border = '1px solid #bbf7d0'; div.style.color = '#166534'; }
                cont.appendChild(div);
                setTimeout(function() { if (div.parentNode) div.remove(); }, MSG_AUTO_OCULTAR_MS);
            }

            window.verificarDocumentoAPI = verificarDocumentoAPI;
            window.verificarCalidadDocumentoAPI = verificarCalidadDocumentoAPI;
            window.verificarComprobanteAPI = verificarComprobanteAPI;
            window.actualizarCheckmark = actualizarCheckmark;
            window.idVerificado = idVerificado;
            window.API_BASE = API_BASE;
            window.API_KEY = API_KEY;
            window.VERIFICACION_TIMEOUT_MS = VERIFICACION_TIMEOUT_MS;
            window.fetchWithTimeout = fetchWithTimeout;
            window.showResultado = showResultado;
            window.verificacionPendienteInicio = function() { verificacionesPendientes++; actualizarBotonEnviar(); };
            window.verificacionPendienteFin = function() { verificacionesPendientes--; actualizarBotonEnviar(); };
            window.puedeEnviarDocumentos = function() { return verificacionesPendientes === 0; };

            function cerrarModal() {
                if (streamActual) {
                    streamActual.getTracks().forEach(function(t) { t.stop(); });
                    streamActual = null;
                }
                video.srcObject = null;
                modal.classList.remove('activo');
                modal.setAttribute('aria-hidden', 'true');
                msgError.style.display = 'none';
                video.style.display = '';
                targetNum = null;
            }

            function asignarArchivoDesdeBlob(num, blob) {
                var inputPrincipal = document.getElementById('archivo_' + num);
                var inputFoto = document.getElementById('archivo_' + num + '_foto');
                var file = new File([blob], 'foto_' + num + '.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                if (inputPrincipal) inputPrincipal.files = dt.files;
                if (inputFoto) {
                    var dt2 = new DataTransfer();
                    dt2.items.add(file);
                    inputFoto.files = dt2.files;
                }
            }
            window.asignarArchivoIdentificacion = function(blob) {
                asignarArchivoDesdeBlob(5, blob);
            };
            window.asignarArchivoComprobante = function(blob) {
                asignarArchivoDesdeBlob(6, blob);
            };
            window.asignarArchivoReverso = function(blob) {
                var inputReverso = document.getElementById('archivo_5_reverso');
                if (!inputReverso) return;
                var file = new File([blob], 'identificacion_reverso.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                inputReverso.files = dt.files;
            };

            var inputComprobante = document.getElementById('archivo_6');
            if (inputComprobante) {
                inputComprobante.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    var ext = file.name.split('.').pop().toLowerCase();
                    if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(ext) === -1) return;
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Verificando comprobante...');
                    var formData = new FormData();
                    formData.append('documento', file, file.name);
                    fetchWithTimeout(API_BASE + '/verificar-comprobante', { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.resultado === 'RECHAZADO') {
                            var razon = (res.recomendacion && res.recomendacion.trim()) ? res.recomendacion : (res.alertas && res.alertas.length ? res.alertas.join('. ') : 'No se pudo verificar.');
                            var textoRechazo = (razon.indexOf('Comprobante rechazado') === 0) ? razon : ('Comprobante rechazado: ' + razon);
                            showResultado(msg, verificandoDiv, textoRechazo, true);
                            inputComprobante.value = '';
                            actualizarCheckmark(6, false);
                            var el = document.getElementById('comp-verificado');
                            if (el) el.style.display = 'none';
                        } else {
                            var info = res.empresa ? ' (' + res.empresa + ')' : '';
                            showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> Comprobante verificado' + info + '. Score: ' + (res.score_validacion || '—') + '/100.', false);
                            actualizarCheckmark(6, true);
                            var el = document.getElementById('comp-verificado');
                            if (el) el.style.display = 'inline';
                        }
                    })
                    .catch(function(err) {
                        var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o intenta de nuevo.';
                        showResultado(msg, verificandoDiv, texto, true);
                        var el = document.getElementById('comp-verificado');
                        if (el) el.style.display = 'none';
                    });
                });
            }

            var inputCURP = document.getElementById('archivo_4');
            if (inputCURP) {
                inputCURP.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    if (file.name.split('.').pop().toLowerCase() !== 'pdf') {
                        showResultado(document.getElementById('mensajeResultado'), null, 'Solo se acepta constancia de CURP en PDF (descargada del RENAPO). No uses constancia fiscal ni otros documentos.', true);
                        inputCURP.value = '';
                        return;
                    }
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Verificando CURP...');
                    var formData = new FormData();
                    formData.append('documento', file, file.name);
                    fetchWithTimeout(API_BASE + '/verificar-curp-documento', { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(function(res) {
                        var el = document.getElementById('curp-verificado');
                        if (res.valido !== true) {
                            showResultado(msg, verificandoDiv, 'CURP rechazado: ' + (res.mensaje || 'No se encontró un CURP válido') + '.', true);
                            inputCURP.value = '';
                            if (el) el.style.display = 'none';
                            actualizarCheckmark(4, false);
                            return;
                        }
                        if (res.es_reciente === false && res.meses_antiguedad != null) {
                            showResultado(msg, verificandoDiv, 'La constancia CURP tiene más de 3 meses (' + res.meses_antiguedad + ' meses). Descarga una constancia reciente desde la página del RENAPO.', true);
                            inputCURP.value = '';
                            if (el) el.style.display = 'none';
                            actualizarCheckmark(4, false);
                            return;
                        }
                        var vigencia = res.es_reciente === true ? ' Vigente.' : (res.fecha_emision ? ' Emisión: ' + res.fecha_emision + '.' : '');
                        showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> CURP verificado: ' + (res.curp_extraido || '') + '.' + vigencia, false);
                        if (el) el.style.display = 'inline';
                        actualizarCheckmark(4, true);
                    })
                    .catch(function(err) {
                        var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o intenta de nuevo.';
                        showResultado(msg, verificandoDiv, texto, true);
                        var el = document.getElementById('curp-verificado');
                        if (el) el.style.display = 'none';
                        actualizarCheckmark(4, false);
                    });
                });
            }

            var inputFrente = document.getElementById('archivo_5');
            if (inputFrente) {
                inputFrente.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    var ext = (file.name.split('.').pop() || '').toLowerCase();
                    if (ext !== 'pdf') {
                        showResultado(document.getElementById('mensajeResultado'), null, 'Solo se acepta un archivo PDF para la identificación oficial (con frente y reverso).', true);
                        inputFrente.value = '';
                        return;
                    }
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Validando formato del PDF...');
                    idVerificado.front = true;
                    idVerificado.back = true;
                    actualizarCheckmark(5, true);
                    var el = document.getElementById('id-verificado-frente');
                    if (el) el.style.display = 'inline';
                    showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> PDF de identificación cargado. La revisión profunda se realizará automáticamente después de subir.', false);
                });
            }
            var inputReversoId = document.getElementById('archivo_5_reverso');
            if (inputReversoId) {
                inputReversoId.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    var ext = (file.name.split('.').pop() || '').toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'webp'].indexOf(ext) === -1) {
                        showResultado(document.getElementById('mensajeResultado'), null, 'Solo se aceptan imágenes (JPG, PNG) para el reverso. Debe ser la parte de atrás de la identificación.', true);
                        inputReversoId.value = '';
                        return;
                    }
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Verificando identificación (reverso)...');
                    var reader = new FileReader();
                    reader.onload = function() {
                        var arr = reader.result.split(',');
                        var mime = (arr[0].match(/:(.*?);/) || [])[1] || 'image/jpeg';
                        var bstr = atob(arr[1] || '');
                        var u8 = new Uint8Array(bstr.length);
                        for (var i = 0; i < bstr.length; i++) u8[i] = bstr.charCodeAt(i);
                        var blob = new Blob([u8], { type: mime });
                        verificarCalidadDocumentoAPI(blob, 'reverso').then(function(res) {
                            if (!res.ok) {
                                var textoAlerta = (res.mensaje && res.mensaje.trim()) ? res.mensaje : 'Identificación (reverso) rechazada. Sube una imagen clara del reverso de tu INE o residencia.';
                                showResultado(msg, verificandoDiv, textoAlerta, true);
                                inputReversoId.value = '';
                                actualizarCheckmark(5, false);
                                var el = document.getElementById('id-verificado-reverso');
                                if (el) el.style.display = 'none';
                                idVerificado.back = false;
                                return;
                            }
                            idVerificado.back = true;
                            showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> Reverso aceptado. La verificación completa se hará al subir.', false);
                            actualizarCheckmark(5, idVerificado.front && idVerificado.back);
                            var el = document.getElementById('id-verificado-reverso');
                            if (el) el.style.display = 'inline';
                        }).catch(function(err) {
                            var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o que la API esté encendida.';
                            showResultado(msg, verificandoDiv, texto, true);
                            inputReversoId.value = '';
                            actualizarCheckmark(5, false);
                            var el = document.getElementById('id-verificado-reverso');
                            if (el) el.style.display = 'none';
                            idVerificado.back = false;
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            var inputFiscal = document.getElementById('archivo_7');
            if (inputFiscal) {
                inputFiscal.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    if (file.name.split('.').pop().toLowerCase() !== 'pdf') {
                        showResultado(document.getElementById('mensajeResultado'), null, 'Solo se acepta constancia de situación fiscal en PDF (descargada del SAT).', true);
                        inputFiscal.value = '';
                        return;
                    }
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Verificando constancia fiscal...');
                    var formData = new FormData();
                    formData.append('documento', file, file.name);
                    fetchWithTimeout(API_BASE + '/verificar-constancia-fiscal-documento', { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(function(res) {
                        var el = document.getElementById('fiscal-verificado');
                        if (res.valido !== true) {
                            showResultado(msg, verificandoDiv, 'Constancia fiscal rechazada: ' + (res.mensaje || 'El documento no es una constancia de situación fiscal del SAT.') + '.', true);
                            inputFiscal.value = '';
                            if (el) el.style.display = 'none';
                            actualizarCheckmark(7, false);
                            return;
                        }
                        showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> Constancia de situación fiscal verificada.', false);
                        if (el) el.style.display = 'inline';
                        actualizarCheckmark(7, true);
                    })
                    .catch(function(err) {
                        var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o intenta de nuevo.';
                        showResultado(msg, verificandoDiv, texto, true);
                        inputFiscal.value = '';
                        var elF = document.getElementById('fiscal-verificado');
                        if (elF) elF.style.display = 'none';
                    });
                });
            }

            var inputNSS = document.getElementById('archivo_8');
            if (inputNSS) {
                inputNSS.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    if (file.name.split('.').pop().toLowerCase() !== 'pdf') {
                        showResultado(document.getElementById('mensajeResultado'), null, 'Solo se acepta constancia de NSS en PDF (descargada del IMSS).', true);
                        inputNSS.value = '';
                        return;
                    }
                    var msg = document.getElementById('mensajeResultado');
                    var verificandoDiv = showVerificando(msg, 'Verificando número de seguro social...');
                    var formData = new FormData();
                    formData.append('documento', file, file.name);
                    fetchWithTimeout(API_BASE + '/verificar-nss-documento', { method: 'POST', headers: { 'X-API-Key': API_KEY }, body: formData }, VERIFICACION_TIMEOUT_MS)
                    .then(function(r) {
                        if (!r.ok) throw new Error('La API respondió con error. Intenta de nuevo.');
                        return r.json();
                    })
                    .then(function(res) {
                        var el = document.getElementById('nss-verificado');
                        if (res.valido !== true) {
                            showResultado(msg, verificandoDiv, 'NSS rechazado: ' + (res.mensaje || 'No se encontró un NSS válido en el documento. Sube la constancia del IMSS.') + '.', true);
                            inputNSS.value = '';
                            if (el) el.style.display = 'none';
                            actualizarCheckmark(8, false);
                            return;
                        }
                        showResultado(msg, verificandoDiv, '<i class="fa fa-check-circle me-1"></i> NSS verificado: ' + (res.nss_extraido || '') + '.', false);
                        if (el) el.style.display = 'inline';
                        actualizarCheckmark(8, true);
                    })
                    .catch(function(err) {
                        var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o intenta de nuevo.';
                        showResultado(msg, verificandoDiv, texto, true);
                        inputNSS.value = '';
                        var el = document.getElementById('nss-verificado');
                        if (el) el.style.display = 'none';
                    });
                });
            }

            document.getElementById('btnCerrarModalCamara').addEventListener('click', cerrarModal);
            document.getElementById('btnCancelarCamara').addEventListener('click', cerrarModal);
            btnCapturar.addEventListener('click', function() {
                if (!targetNum || !streamActual || !video.videoWidth) return;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);
                canvas.toBlob(function(blob) {
                    if (blob) asignarArchivoDesdeBlob(targetNum, blob);
                    cerrarModal();
                }, 'image/jpeg', 0.92);
            });

            document.querySelectorAll('.btn-tomar-foto').forEach(function(btn) {
                var num = btn.getAttribute('data-target');
                var inputPrincipal = document.getElementById('archivo_' + num);
                if (!inputPrincipal) return;
                btn.addEventListener('click', function() {
                    if (btn.getAttribute('data-es-identificacion') === '1') {
                        document.getElementById('capturaIdOverlay').style.display = 'flex';
                        document.getElementById('capturaIdOverlay').setAttribute('aria-hidden', 'false');
                        document.getElementById('capturaId-intro').style.display = 'flex';
                        document.getElementById('capturaId-camera').style.display = 'none';
                        document.getElementById('capturaId-result').style.display = 'none';
                        return;
                    }
                    if (btn.getAttribute('data-es-comprobante') === '1') {
                        document.getElementById('comprobanteOverlay').style.display = 'flex';
                        document.getElementById('comprobanteOverlay').setAttribute('aria-hidden', 'false');
                        document.getElementById('comp-intro').style.display = 'flex';
                        document.getElementById('comp-camera').style.display = 'none';
                        document.getElementById('comp-result').style.display = 'none';
                        return;
                    }
                    targetNum = num;
                    document.getElementById('modalCamaraTitulo').textContent = 'Tomar foto — Documento ' + num;
                    msgError.style.display = 'none';
                    video.style.display = 'block';
                    btnCapturar.disabled = true;
                    modal.classList.add('activo');
                    modal.setAttribute('aria-hidden', 'false');
                    var opts = { video: true };

                    function mostrarError(txt) {
                        msgError.textContent = txt;
                        msgError.style.display = 'block';
                        video.style.display = 'none';
                        btnCapturar.disabled = false;
                    }

                    if (!window.isSecureContext) {
                        mostrarError('Para usar la cámara debes abrir esta página con HTTPS (dirección que empiece por https://). En redes locales o desde el celular suele ser necesario usar HTTPS.');
                        return;
                    }
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        mostrarError('Tu navegador no soporta acceso a la cámara. Prueba con Chrome o Safari actualizado, o sube la foto desde "Elegir archivo".');
                        return;
                    }
                    navigator.mediaDevices.getUserMedia(opts).then(function(stream) {
                        streamActual = stream;
                        video.srcObject = stream;
                        btnCapturar.disabled = false;
                    }).catch(function(err) {
                        var nombre = err && err.name;
                        if (nombre === 'NotAllowedError' || nombre === 'PermissionDeniedError') {
                            mostrarError('Permiso de cámara denegado. Activa el permiso en ajustes del navegador o del sitio y vuelve a intentar.');
                        } else if (nombre === 'NotFoundError') {
                            mostrarError('No se encontró ninguna cámara. Conecta una o sube la foto con "Elegir archivo".');
                        } else if (nombre === 'SecurityError' || nombre === 'NotSupportedError') {
                            mostrarError('La cámara no está disponible en esta página. Prueba abriendo con HTTPS o sube la foto con "Elegir archivo".');
                        } else {
                            mostrarError('No se pudo acceder a la cámara. Revisa los permisos del navegador o sube la foto con "Elegir archivo".');
                        }
                    });
                });
            });
        })();
        (function() {
            var DOC_ID_NUM = 5;
            var overlay = document.getElementById('capturaIdOverlay');
            var intro = document.getElementById('capturaId-intro');
            var camera = document.getElementById('capturaId-camera');
            var result = document.getElementById('capturaId-result');
            var video = document.getElementById('capturaId-video');
            var canvas = document.getElementById('capturaId-canvas');
            var idFrame = document.getElementById('capturaId-id-frame');
            var streamCaptura = null;
            var facingMode = 'environment';
            var currentSide = 'front';
            var capturedDataURL = null;
            var alignTimer = null;

            function dataURLtoBlob(dataURL) {
                var arr = dataURL.split(',');
                var mime = arr[0].match(/:(.*?);/)[1];
                var bstr = atob(arr[1]);
                var n = bstr.length;
                var u8 = new Uint8Array(n);
                while (n--) u8[n] = bstr.charCodeAt(n);
                return new Blob([u8], { type: mime });
            }

            function positionInstruction() {
                var instr = document.getElementById('capturaId-instruction');
                if (!idFrame || !instr) return;
                var rect = idFrame.getBoundingClientRect();
                instr.style.top = (rect.bottom + 16) + 'px';
            }

            function startAlignSimulation() {
                var dot = document.getElementById('capturaId-align-dot');
                var txt = document.getElementById('capturaId-align-text');
                var msgs = [{ text: 'Apunta al ID…', ok: false }, { text: 'Ajustando encuadre…', ok: false }, { text: 'Centrando…', ok: false }, { text: 'Cuando el ID esté centrado, toma la foto', ok: true }];
                var i = 0;
                clearInterval(alignTimer);
                if (dot) dot.classList.remove('ok');
                alignTimer = setInterval(function() {
                    if (i >= msgs.length) { clearInterval(alignTimer); return; }
                    if (txt) txt.textContent = msgs[i].text;
                    if (dot && msgs[i].ok) dot.classList.add('ok');
                    i++;
                }, 1100);
            }

            function goIntro() {
                clearInterval(alignTimer);
                if (streamCaptura) { streamCaptura.getTracks().forEach(function(t) { t.stop(); }); streamCaptura = null; }
                if (video) video.srcObject = null;
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
                intro.style.display = 'flex';
                camera.style.display = 'none';
                result.style.display = 'none';
            }

            function openStream() {
                if (streamCaptura) streamCaptura.getTracks().forEach(function(t) { t.stop(); });
                var opts = { video: { facingMode: facingMode, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false };
                navigator.mediaDevices.getUserMedia(opts).then(function(s) {
                    streamCaptura = s;
                    video.srcObject = s;
                }).catch(function(e) {
                    alert('No se pudo acceder a la cámara: ' + (e.message || e.name));
                });
            }

            function startCamera(side) {
                currentSide = side;
                document.getElementById('capturaId-side-indicator').textContent = side === 'front' ? 'FRENTE' : 'REVERSO';
                if (!window.isSecureContext) {
                    alert('Para usar la cámara abre esta página con HTTPS.');
                    return;
                }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Tu navegador no soporta la cámara. Usa "Elegir archivo" para subir la foto.');
                    return;
                }
                intro.style.display = 'none';
                result.style.display = 'none';
                camera.style.display = 'flex';
                openStream();
                setTimeout(positionInstruction, 100);
                window.addEventListener('resize', positionInstruction);
                startAlignSimulation();
            }

            function capture() {
                var vRect = video.getBoundingClientRect();
                var fRect = idFrame.getBoundingClientRect();
                var scaleX = video.videoWidth / vRect.width;
                var scaleY = video.videoHeight / vRect.height;
                var cropX = (fRect.left - vRect.left) * scaleX;
                var cropY = (fRect.top - vRect.top) * scaleY;
                var cropW = fRect.width * scaleX;
                var cropH = fRect.height * scaleY;
                canvas.width = cropW;
                canvas.height = cropH;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(video, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);
                capturedDataURL = canvas.toDataURL('image/jpeg', 0.92);
                var flash = document.getElementById('capturaId-flash-fx');
                if (flash) { flash.classList.remove('go'); void flash.offsetWidth; flash.classList.add('go'); }
                setTimeout(showResult, 250);
            }

            function showResult() {
                clearInterval(alignTimer);
                if (streamCaptura) { streamCaptura.getTracks().forEach(function(t) { t.stop(); }); streamCaptura = null; }
                video.srcObject = null;
                camera.style.display = 'none';
                result.style.display = 'flex';
                document.getElementById('capturaId-captured-img').src = capturedDataURL;
                document.getElementById('capturaId-result-side').textContent = currentSide === 'front' ? 'FRENTE' : 'REVERSO';
                var img = new Image();
                img.onload = function() {
                    var c = document.createElement('canvas');
                    c.width = img.width;
                    c.height = img.height;
                    var ctx = c.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    var data = ctx.getImageData(0, 0, c.width, c.height).data;
                    var brightness = 0;
                    for (var i = 0; i < data.length; i += 4) brightness += (data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
                    brightness /= (data.length / 4);
                    var lightEl = document.getElementById('capturaId-q-light');
                    if (lightEl) {
                        if (brightness < 40) { lightEl.textContent = 'Muy oscura'; lightEl.className = 'captura-id-quality-status warn'; }
                        else if (brightness > 230) { lightEl.textContent = 'Sobreexpuesta'; lightEl.className = 'captura-id-quality-status warn'; }
                        else { lightEl.textContent = 'Correcta'; lightEl.className = 'captura-id-quality-status ok'; }
                    }
                    var frameEl = document.getElementById('capturaId-q-frame');
                    if (frameEl) { frameEl.textContent = img.width < 400 ? 'Resolución baja' : 'Centrado'; frameEl.className = 'captura-id-quality-status ' + (img.width < 400 ? 'warn' : 'ok'); }
                };
                img.src = capturedDataURL;
            }

            var verificarDocumentoAPI = window.verificarDocumentoAPI;
            var verificarComprobanteAPI = window.verificarComprobanteAPI;
            var actualizarCheckmark = window.actualizarCheckmark;
            var idVerificado = window.idVerificado;

            function usePhoto() {
                var blob = dataURLtoBlob(capturedDataURL);
                var side = currentSide;
                var btnUse = document.getElementById('capturaId-btn-use');
                var statusEl = document.getElementById('capturaId-result-side');
                if (btnUse) { btnUse.disabled = true; btnUse.textContent = 'Verificando...'; }
                if (window.verificacionPendienteInicio) window.verificacionPendienteInicio();

                verificarCalidadDocumentoAPI(blob, side === 'front' ? 'frente' : 'reverso').then(function(res) {
                    if (window.verificacionPendienteFin) window.verificacionPendienteFin();
                    if (btnUse) { btnUse.disabled = false; btnUse.textContent = 'Usar esta foto'; }
                    if (!res.ok) {
                        var alertas = (res.mensaje && res.mensaje.trim()) ? res.mensaje : 'Calidad de imagen insuficiente.';
                        if (res.alertas && res.alertas.length) alertas = res.alertas.join('. ');
                        alert('Foto rechazada: ' + alertas + '\n\nPor favor retoma la foto.');
                        return;
                    }
                    idVerificado[side] = true;
                    if (side === 'front') {
                        if (window.asignarArchivoIdentificacion) window.asignarArchivoIdentificacion(blob);
                        var el = document.getElementById('id-verificado-frente');
                        if (el) el.style.display = 'inline';
                    } else {
                        if (window.asignarArchivoReverso) window.asignarArchivoReverso(blob);
                        var el = document.getElementById('id-verificado-reverso');
                        if (el) el.style.display = 'inline';
                    }
                    if (idVerificado.front && idVerificado.back) {
                        actualizarCheckmark(5, true);
                    }
                    goIntro();
                    var msg = document.getElementById('mensajeResultado');
                    var falta = '';
                    var rev = document.getElementById('archivo_5_reverso');
                    var frente = document.getElementById('archivo_5');
                    if (msg) {
                        if (side === 'front') {
                            falta = (!rev || !rev.files || !rev.files.length) && !idVerificado.back ? ' Recuerda capturar también el reverso.' : '';
                            (window.showResultado || function(){})(msg, null, 'Foto del frente aceptada &#10003;. La verificación completa se hará al subir.' + falta, false);
                        } else {
                            falta = (!frente || !frente.files || !frente.files.length) && !idVerificado.front ? ' Recuerda capturar también el frente.' : '';
                            (window.showResultado || function(){})(msg, null, 'Foto del reverso aceptada &#10003;. La verificación completa se hará al subir.' + falta, false);
                        }
                    }
                }).catch(function(err) {
                    if (window.verificacionPendienteFin) window.verificacionPendienteFin();
                    if (btnUse) { btnUse.disabled = false; btnUse.textContent = 'Usar esta foto'; }
                    if (side === 'front') {
                        if (window.asignarArchivoIdentificacion) window.asignarArchivoIdentificacion(blob);
                    } else {
                        if (window.asignarArchivoReverso) window.asignarArchivoReverso(blob);
                    }
                    goIntro();
                    var msg = document.getElementById('mensajeResultado');
                    var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o que la API esté encendida.';
                    if (msg) (window.showResultado || function(){})(msg, null, texto, true);
                });
            }

            function goCamera() {
                result.style.display = 'none';
                camera.style.display = 'flex';
                openStream();
                startAlignSimulation();
                setTimeout(positionInstruction, 50);
            }

            document.getElementById('capturaId-btn-cerrar-intro').addEventListener('click', goIntro);
            document.getElementById('capturaId-btn-frente').addEventListener('click', function() { startCamera('front'); });
            document.getElementById('capturaId-btn-reverso').addEventListener('click', function() { startCamera('back'); });
            document.getElementById('capturaId-btn-back').addEventListener('click', goIntro);
            document.getElementById('capturaId-btn-flip').addEventListener('click', function() {
                facingMode = facingMode === 'environment' ? 'user' : 'environment';
                openStream();
            });
            document.getElementById('capturaId-shutter').addEventListener('click', capture);
            document.getElementById('capturaId-btn-use').addEventListener('click', usePhoto);
            document.getElementById('capturaId-btn-retake').addEventListener('click', goCamera);
            document.getElementById('capturaId-btn-retomar').addEventListener('click', goCamera);
            document.getElementById('capturaId-btn-torch').addEventListener('click', function() {
                if (!streamCaptura) return;
                var track = streamCaptura.getVideoTracks()[0];
                if (!track || !track.applyConstraints) return;
                var btn = document.getElementById('capturaId-btn-torch');
                var next = !btn.classList.contains('on');
                track.applyConstraints({ advanced: [{ torch: next }] }).then(function() { btn.classList.toggle('on', next); btn.title = 'Linterna'; }).catch(function() {
                    btn.classList.remove('on');
                    btn.title = 'Linterna no disponible en este dispositivo o navegador';
                });
            });
        })();
        (function() {
            var compOverlay = document.getElementById('comprobanteOverlay');
            var compIntro = document.getElementById('comp-intro');
            var compCamera = document.getElementById('comp-camera');
            var compResult = document.getElementById('comp-result');
            var compVideo = document.getElementById('comp-video');
            var compCanvas = document.getElementById('comp-canvas');
            var compDocFrame = document.getElementById('comp-doc-frame');
            var streamComp = null;
            var facingModeComp = 'environment';
            var capturedCompDataURL = null;
            var selectedDocComp = 'Recibo de Luz';
            var alignTimerComp = null;

            function compDataURLtoBlob(dataURL) {
                var arr = dataURL.split(',');
                var mime = arr[0].match(/:(.*?);/)[1];
                var bstr = atob(arr[1]);
                var n = bstr.length;
                var u8 = new Uint8Array(n);
                while (n--) u8[n] = bstr.charCodeAt(n);
                return new Blob([u8], { type: mime });
            }

            function compPositionInstruction() {
                var instr = document.getElementById('comp-instruction');
                if (!compDocFrame || !instr) return;
                var rect = compDocFrame.getBoundingClientRect();
                instr.style.top = (rect.bottom + 14) + 'px';
            }

            function compStartAlignSimulation() {
                var dot = document.getElementById('comp-align-dot');
                var txt = document.getElementById('comp-align-text');
                var msgs = [{ text: 'Coloca el comprobante en el marco…', ok: false }, { text: 'Ajusta para incluir todos los bordes…', ok: false }, { text: 'Mantén la cámara estable…', ok: false }, { text: 'Documento listo — toma la foto', ok: true }];
                var i = 0;
                clearInterval(alignTimerComp);
                if (dot) dot.classList.remove('ok');
                alignTimerComp = setInterval(function() {
                    if (i >= msgs.length) { clearInterval(alignTimerComp); return; }
                    if (txt) txt.textContent = msgs[i].text;
                    if (dot && msgs[i].ok) dot.classList.add('ok');
                    i++;
                }, 1200);
            }

            function compGoIntro() {
                clearInterval(alignTimerComp);
                if (streamComp) { streamComp.getTracks().forEach(function(t) { t.stop(); }); streamComp = null; }
                if (compVideo) compVideo.srcObject = null;
                compOverlay.style.display = 'none';
                compOverlay.setAttribute('aria-hidden', 'true');
                compIntro.style.display = 'flex';
                compCamera.style.display = 'none';
                compResult.style.display = 'none';
            }

            function compOpenStream() {
                if (streamComp) streamComp.getTracks().forEach(function(t) { t.stop(); });
                var opts = { video: { facingMode: facingModeComp, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false };
                navigator.mediaDevices.getUserMedia(opts).then(function(s) {
                    streamComp = s;
                    compVideo.srcObject = s;
                }).catch(function(e) {
                    alert('No se pudo acceder a la cámara: ' + (e.message || e.name));
                });
            }

            function compStartCamera() {
                if (!window.isSecureContext) { alert('Para usar la cámara abre esta página con HTTPS.'); return; }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { alert('Tu navegador no soporta la cámara. Usa "Elegir archivo" para subir la foto.'); return; }
                document.getElementById('comp-hud-doc-type').textContent = selectedDocComp;
                compIntro.style.display = 'none';
                compResult.style.display = 'none';
                compCamera.style.display = 'flex';
                compOpenStream();
                setTimeout(compPositionInstruction, 120);
                window.addEventListener('resize', compPositionInstruction);
                compStartAlignSimulation();
            }

            function compCapture() {
                var vRect = compVideo.getBoundingClientRect();
                var fRect = compDocFrame.getBoundingClientRect();
                var scaleX = compVideo.videoWidth / vRect.width;
                var scaleY = compVideo.videoHeight / vRect.height;
                var cX = (fRect.left - vRect.left) * scaleX;
                var cY = (fRect.top - vRect.top) * scaleY;
                var cW = fRect.width * scaleX;
                var cH = fRect.height * scaleY;
                compCanvas.width = Math.max(cW, 1);
                compCanvas.height = Math.max(cH, 1);
                compCanvas.getContext('2d').drawImage(compVideo, cX, cY, cW, cH, 0, 0, cW, cH);
                capturedCompDataURL = compCanvas.toDataURL('image/jpeg', 0.92);
                var flash = document.getElementById('comp-flash-fx');
                if (flash) { flash.classList.remove('go'); void flash.offsetWidth; flash.classList.add('go'); }
                setTimeout(compShowResult, 270);
            }

            function compSetQuality(prefix, pct, isWarn) {
                var bar = document.getElementById('comp-' + prefix + '-bar');
                var val = document.getElementById('comp-' + prefix + '-val');
                if (bar) { bar.style.width = pct + '%'; bar.classList.toggle('warn', isWarn); }
                if (val) { val.textContent = pct + '%'; val.classList.toggle('warn', isWarn); }
            }

            function compAnalyzeQuality(dataURL) {
                var img = new Image();
                img.onload = function() {
                    var c = document.createElement('canvas');
                    c.width = img.width;
                    c.height = img.height;
                    var ctx = c.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    var data = ctx.getImageData(0, 0, c.width, c.height).data;
                    var brightness = 0, minB = 255, maxB = 0;
                    var step = Math.max(1, Math.floor((data.length / 4) / 50000));
                    for (var i = 0; i < data.length; i += step * 4) {
                        var b = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
                        brightness += b;
                        if (b < minB) minB = b;
                        if (b > maxB) maxB = b;
                    }
                    brightness /= (data.length / (step * 4));
                    var contrast = maxB - minB;
                    var lightPct = Math.round(Math.min(brightness / 200 * 100, 100));
                    compSetQuality('q-light', lightPct, brightness < 50 || brightness > 220);
                    var contrastPct = Math.round(Math.min(contrast / 200 * 100, 100));
                    compSetQuality('q-contrast', contrastPct, contrast < 80);
                    var framePct = img.width >= 600 ? 95 : Math.round(img.width / 600 * 95);
                    compSetQuality('q-frame', framePct, img.width < 400);
                };
                img.src = dataURL;
            }

            function compShowResult() {
                clearInterval(alignTimerComp);
                if (streamComp) { streamComp.getTracks().forEach(function(t) { t.stop(); }); streamComp = null; }
                compVideo.srcObject = null;
                compCamera.style.display = 'none';
                compResult.style.display = 'flex';
                document.getElementById('comp-captured-img').src = capturedCompDataURL;
                document.getElementById('comp-result-doc-name').textContent = selectedDocComp;
                compAnalyzeQuality(capturedCompDataURL);
            }

            function compUsePhoto() {
                var blob = compDataURLtoBlob(capturedCompDataURL);
                var btnUse = document.getElementById('comp-btn-use');
                if (btnUse) { btnUse.disabled = true; btnUse.textContent = 'Verificando...'; }
                if (window.verificacionPendienteInicio) window.verificacionPendienteInicio();

                verificarComprobanteAPI(blob, 'comprobante.jpg').then(function(res) {
                    if (window.verificacionPendienteFin) window.verificacionPendienteFin();
                    if (btnUse) { btnUse.disabled = false; btnUse.textContent = 'Usar esta foto'; }
                    if (res.resultado === 'RECHAZADO') {
                        var razon = (res.recomendacion && res.recomendacion.trim()) ? res.recomendacion : (res.alertas && res.alertas.length ? res.alertas.join('. ') : 'No se pudo verificar el comprobante.');
                        if (razon.indexOf('Comprobante rechazado') !== 0) razon = 'Comprobante rechazado: ' + razon;
                        alert(razon + '\n\nPor favor retoma la foto o sube un documento más reciente.');
                        return;
                    }
                    if (window.asignarArchivoComprobante) window.asignarArchivoComprobante(blob);
                    actualizarCheckmark(6, true);
                    var el = document.getElementById('comp-verificado');
                    if (el) el.style.display = 'inline';
                    compGoIntro();
                    var msg = document.getElementById('mensajeResultado');
                    var info = res.empresa ? ' (' + res.empresa + ')' : '';
                    if (msg) (window.showResultado || function(){})(msg, null, '<i class="fa fa-check-circle me-1"></i> Comprobante verificado' + info + '. Score: ' + (res.score_validacion || '—') + '/100.', false);
                }).catch(function(err) {
                    if (window.verificacionPendienteFin) window.verificacionPendienteFin();
                    if (btnUse) { btnUse.disabled = false; btnUse.textContent = 'Usar esta foto'; }
                    if (window.asignarArchivoComprobante) window.asignarArchivoComprobante(blob);
                    compGoIntro();
                    var msg = document.getElementById('mensajeResultado');
                    var texto = (err && err.message) ? err.message : 'Verificación no disponible. Revisa tu conexión o que la API esté encendida.';
                    if (msg) (window.showResultado || function(){})(msg, null, texto, true);
                });
            }

            function compGoCamera() {
                compResult.style.display = 'none';
                compCamera.style.display = 'flex';
                compOpenStream();
                compStartAlignSimulation();
                setTimeout(compPositionInstruction, 60);
            }

            document.getElementById('comp-btn-cerrar-intro').addEventListener('click', compGoIntro);
            document.getElementById('comp-btn-start').addEventListener('click', compStartCamera);
            document.getElementById('comp-btn-back').addEventListener('click', compGoIntro);
            document.getElementById('comp-btn-flip').addEventListener('click', function() {
                facingModeComp = facingModeComp === 'environment' ? 'user' : 'environment';
                compOpenStream();
            });
            document.getElementById('comp-shutter').addEventListener('click', compCapture);
            document.getElementById('comp-btn-use').addEventListener('click', compUsePhoto);
            document.getElementById('comp-btn-retake').addEventListener('click', compGoCamera);
            document.getElementById('comp-btn-retomar').addEventListener('click', compGoCamera);
            document.getElementById('comp-btn-torch').addEventListener('click', function() {
                if (!streamComp) return;
                var track = streamComp.getVideoTracks()[0];
                if (!track || !track.applyConstraints) return;
                var btn = document.getElementById('comp-btn-torch');
                var next = !btn.classList.contains('on');
                track.applyConstraints({ advanced: [{ torch: next }] }).then(function() { btn.classList.toggle('on', next); btn.title = 'Linterna'; }).catch(function() {
                    btn.classList.remove('on');
                    btn.title = 'Linterna no disponible en este dispositivo o navegador';
                });
            });
            document.querySelectorAll('#comp-doc-types .ov-gi').forEach(function(el) {
                el.addEventListener('click', function() {
                    document.querySelectorAll('#comp-doc-types .ov-gi').forEach(function(d) { d.classList.remove('sel'); });
                    el.classList.add('sel');
                    selectedDocComp = el.getAttribute('data-doc');
                });
            });
        })();
        var formSubir = document.getElementById('formSubirDocumentos');
        if (formSubir) formSubir.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnEnviar');
            var msg = document.getElementById('mensajeResultado');
            var form = document.getElementById('formSubirDocumentos');
            if (typeof window.puedeEnviarDocumentos === 'function' && !window.puedeEnviarDocumentos()) {
                if (msg) (window.showResultado || function(){})(msg, null, 'Espera a que termine la verificación de los documentos antes de subir.', true);
                return;
            }
            function docRequiereVerificacion(inputId, spanId) {
                var input = document.getElementById(inputId);
                var span = document.getElementById(spanId);
                if (!input || !span) return false;
                if (!input.files || input.files.length === 0) return false;
                return span.style.display !== 'inline' && span.style.display !== 'inline-block';
            }
            if (docRequiereVerificacion('archivo_4', 'curp-verificado')) {
                if (msg) (window.showResultado || function(){})(msg, null, 'La constancia de CURP debe ser verificada antes de enviar. Solo se acepta constancia CURP del RENAPO.', true);
                return;
            }
            var input5 = document.getElementById('archivo_5');
            var esPdfId = input5 && input5.files && input5.files.length > 0 && (input5.files[0].name || '').toLowerCase().endsWith('.pdf');
            if (!esPdfId && docRequiereVerificacion('archivo_5', 'id-verificado-frente')) {
                if (msg) (window.showResultado || function(){})(msg, null, 'La identificación debe ser verificada antes de enviar. Sube un PDF con frente y reverso de tu INE o residencia.', true);
                return;
            }
            if (docRequiereVerificacion('archivo_7', 'fiscal-verificado')) {
                if (msg) (window.showResultado || function(){})(msg, null, 'La constancia de situación fiscal debe ser verificada antes de enviar. Solo se acepta constancia del SAT.', true);
                return;
            }
            if (docRequiereVerificacion('archivo_8', 'nss-verificado')) {
                if (msg) (window.showResultado || function(){})(msg, null, 'La constancia de NSS debe ser verificada antes de enviar. Solo se acepta constancia del IMSS.', true);
                return;
            }
            // Envío parcial: solo exigir que haya al menos un archivo seleccionado
            var tieneAlgunArchivo = false;
            for (var i = 1; i <= 10; i++) {
                var input = document.getElementById('archivo_' + i);
                if (input && input.files && input.files.length > 0) { tieneAlgunArchivo = true; break; }
                var foto = document.getElementById('archivo_' + i + '_foto');
                if (foto && foto.files && foto.files.length > 0) { tieneAlgunArchivo = true; break; }
            }
            if (!tieneAlgunArchivo) {
                var rev = document.getElementById('archivo_5_reverso');
                if (rev && rev.files && rev.files.length > 0) tieneAlgunArchivo = true;
            }
            if (!tieneAlgunArchivo) {
                msg.innerHTML = '';
                showResultado(msg, null, 'Selecciona al menos un documento para subir. Puedes enviar el resto más adelante.', true);
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Subiendo...';
            msg.innerHTML = '';
            var formData = new FormData(form);
            var ctrl = new AbortController();
            var timeoutId = setTimeout(function() { ctrl.abort(); }, 90000);
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                signal: ctrl.signal
            }).then(function(r) { clearTimeout(timeoutId); return r.json(); }).then(function(res) {
                btn.disabled = false;
                btn.textContent = 'Subir documentos';
                msg.innerHTML = '';
                if (res.success) {
                    showResultado(msg, null, '<i class="fa fa-check-circle me-1"></i> ' + (res.mensaje || 'Documentos subidos correctamente.'), false);
                    form.reset();
                    var datos = res.datos || {};
                    var docsSubidos = datos.documentos_subidos;
                    if (docsSubidos && typeof docsSubidos === 'object') {
                        function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
                        var yaSubidoHtml = function(nombre) {
                            return '<div class="doc-ya-subido py-2 px-3 rounded" style="background:#e8f5e9;color:#2e7d32;"><i class="fa fa-check-circle me-1"></i> Ya subido: ' + esc(nombre || 'documento') + '</div>';
                        };
                        for (var key in docsSubidos) {
                            if (!docsSubidos.hasOwnProperty(key)) continue;
                            var info = docsSubidos[key];
                            var nombre = (info && info.nombre_archivo) ? info.nombre_archivo : 'documento';
                            var el;
                            if (key === '5_reverso') {
                                el = document.getElementById('formGroupReverso') || document.querySelector('[data-doc="5_reverso"]');
                            } else {
                                el = document.querySelector('.form-group[data-doc-num="' + key + '"]');
                            }
                            if (el) {
                                var label = el.querySelector('label');
                                var labelText = label ? label.innerHTML : '';
                                el.innerHTML = (label ? '<label>' + labelText + '</label>' : '') + yaSubidoHtml(nombre);
                            }
                        }
                    }
                    if (datos.expediente_completo) {
                        var cardBody = document.querySelector('.form-card-body');
                        var header = document.querySelector('.form-card-header');
                        if (header) {
                            var h1 = header.querySelector('h1');
                            var p = header.querySelector('p');
                            if (h1) h1.textContent = 'Documentación recibida';
                            if (p) p.textContent = 'Su expediente está completo';
                        }
                        if (cardBody) {
                            cardBody.innerHTML = '<div class="expediente-completo">' +
                                '<div class="expediente-completo-icon" aria-hidden="true"><i class="fa fa-check-circle"></i></div>' +
                                '<h2 class="expediente-completo-title">Documentación completa</h2>' +
                                '<p class="expediente-completo-texto">Ha subido correctamente todos los documentos requeridos. El equipo de <strong>Capital Humano</strong> revisará su expediente y se pondrá en contacto con usted a la brevedad.</p>' +
                                '<p class="expediente-completo-agradecimiento">Gracias por su interés en formar parte de nuestro equipo.</p></div>';
                        }
                    }
                } else {
                    showResultado(msg, null, (res.mensaje || 'Error al subir.'), true);
                }
            }).catch(function(err) {
                clearTimeout(timeoutId);
                btn.disabled = false;
                btn.textContent = 'Subir documentos';
                if (err && err.name === 'AbortError') {
                    showResultado(msg, null, 'La subida tardó demasiado. Comprueba tu conexión e intenta de nuevo con menos archivos.', true);
                } else {
                    showResultado(msg, null, 'Error de conexión. Intenta de nuevo.', true);
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
