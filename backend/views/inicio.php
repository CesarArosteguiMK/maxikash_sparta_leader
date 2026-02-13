<div class="container h-100 d-flex flex-column justify-content-center align-items-center text-center">
    <img src="https://__SPARTA_SECRET_REDACTED__.mx/cdn/shop/files/Logotipo-Maxikash-Outline.png?v=1749328460" alt="Logo de la empresa" class="mb-10 w-50">
</div>

<!-- Botón flotante: diagnóstico de conexiones (BD direcciones alternas, SSL, permisos) -->
<button id="btnDiagnosticoConexiones" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    z-index: 9999;
    transition: all 0.3s ease;
" title="Diagnóstico de conexiones (BD direcciones alternas, SSL, permisos)">
    🔌
</button>

<style>
#btnDiagnosticoConexiones:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(13, 148, 136, 0.6);
}
#btnDiagnosticoConexiones:active {
    transform: scale(0.95);
}
</style>

<script>
document.getElementById('btnDiagnosticoConexiones').addEventListener('click', function() {
    var w = 900, h = 700, left = (screen.width - w) / 2, top = (screen.height - h) / 2;
    window.open('/inicio/diagnosticoConexiones', 'DiagnosticoConexiones',
        'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes');
});
</script>