<?php
/**
 * Depuración: escribe en la consola del navegador qué fuente (Legacy / Sky / Segundómetro) falló.
 * Requiere que el controlador haya hecho set('gestionesDbFallosConsole', GestionesDAO::getHistoricoDbFallos()).
 */
$__gestionesDbFallos = $gestionesDbFallosConsole ?? null;
if (empty($__gestionesDbFallos) || !is_array($__gestionesDbFallos)) {
    return;
}
$__gestionesDbFallosJson = json_encode($__gestionesDbFallos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
if ($__gestionesDbFallosJson === false) {
    return;
}
$__gestionesParcial = !empty($gestionesDbFallosModoParcial);
?>
<script>
(function () {
    var fallos = <?= $__gestionesDbFallosJson ?>;
    var parcial = <?= $__gestionesParcial ? 'true' : 'false' ?>;
    if (parcial) {
        console.group('%c[Histórico Gestiones] Aviso: una base no conectó (sí hay datos en pantalla)', 'color:#0d6efd;font-weight:bold');
        console.info('Lo que ves viene de las fuentes que sí respondieron (p. ej. Sky Logic o Segundómetro). Lo siguiente es solo para redes/firewall si quieren arreglar Legacy u otra BD.');
    } else {
        console.group('%c[Histórico Gestiones] Fallos de base de datos', 'color:#c0392b;font-weight:bold');
        console.info('Si ves "Connection refused" o timeout: el servidor web suele no tener salida al puerto 3306 hacia el host MySQL, o la IP del servidor no está autorizada en el firewall de la BD.');
    }
    var logFn = parcial ? console.info.bind(console) : console.warn.bind(console);
    fallos.forEach(function (row, idx) {
        var titulo = '[' + (idx + 1) + '] ' + (row.fuente || 'fuente');
        var cuerpo = (row.mensaje || '') + (row.tipo ? '\nClase: ' + row.tipo : '');
        logFn(titulo + '\n' + cuerpo);
    });
    console.groupEnd();
})();
</script>
