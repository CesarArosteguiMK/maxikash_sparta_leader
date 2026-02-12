<div class="container h-100 d-flex flex-column justify-content-center align-items-center text-center">
    <img src="https://__SPARTA_SECRET_REDACTED__.mx/cdn/shop/files/Logotipo-Maxikash-Outline.png?v=1749328460" alt="Logo de la empresa" class="mb-10 w-50">
</div>

<!-- Botón flotante diagnóstico Segundómetro -->
<button id="btnDiagnosticoSegundometro" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
" title="Diagnóstico Shell Segundómetro">
    🔍
</button>

<style>
#btnDiagnosticoSegundometro:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

#btnDiagnosticoSegundometro:active {
    transform: scale(0.95);
}
</style>

<script>
document.getElementById('btnDiagnosticoSegundometro').addEventListener('click', function() {
    // Abrir diagnóstico en nueva ventana
    const width = 1200;
    const height = 800;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    window.open(
        '/inicio/diagnosticoSegundometro',
        'DiagnosticoSegundometro',
        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
    );
});
</script>