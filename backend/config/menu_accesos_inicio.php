<?php
/**
 * Lista plana de ítems del menú para Accesos rápidos (misma lógica que View::getMenu).
 * También usada para autorización: ruta -> módulos requeridos (index.php).
 */
if (!function_exists('getMenuItemsConfig')) {
    function getMenuItemsConfig()
    {
        return [
            ['url' => '/estadocuenta/consulta', 'label' => 'Estados de Cuenta', 'icon' => 'fa-solid fa-sack-dollar', 'bg' => 'bg-yellow', 'modulos' => [1]],
            ['url' => '/estadocuenta/guatemala', 'label' => 'Estados de Cuenta - Guatemala', 'icon' => 'fa-solid fa-flag', 'bg' => 'bg-success', 'modulos' => [1]],
            ['url' => '/estadocuenta/documentacion', 'label' => 'Documentación', 'icon' => 'fa-solid fa-sack-dollar', 'bg' => 'bg-yellow', 'modulos' => [2]],
            ['url' => '/gestiones/seguimiento', 'label' => 'Histórico Gestiones', 'icon' => 'fa-solid fa-screwdriver-wrench', 'bg' => 'bg-green', 'modulos' => [3]],
            ['url' => '/indicadores/kpiTotal', 'label' => 'KPI Total', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [40]],
            ['url' => '/indicadores/gestiones1A7', 'label' => 'Gestión 1-7', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [24]],
            ['url' => '/indicadores/eficiencia1A7', 'label' => 'Eficiencia 1-7', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [25]],
            ['url' => '/indicadores/gestiones8A21', 'label' => 'Gestión 8-21', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [26]],
            ['url' => '/indicadores/eficiencia8A21', 'label' => 'Eficiencia 8-21', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [27]],
            ['url' => '/indicadores/seguimientoIntensidad', 'label' => 'Intensidad', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [29]],
            ['url' => '/indicadores/detalleClientes', 'label' => 'Detalle Clientes', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [30]],
            ['url' => '/indicadores/detalleEficiencia', 'label' => 'Detalle Eficiencia', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [31]],
            ['url' => '/indicadores/carteraInicioSem', 'label' => 'Cartera Inicial', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [32]],
            ['url' => '/indicadores/seguimientoPromesasPago', 'label' => 'Promesas Pago', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [33]],
            ['url' => '/indicadores/espartanos', 'label' => 'Espartanos', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [34]],
            ['url' => '/indicadores/matrizBuckets', 'label' => 'Matriz Buckets', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [35]],
            ['url' => '/indicadores/matrizBucketsMas1', 'label' => 'Buckets +1', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [36]],
            ['url' => '/indicadores/auditoria', 'label' => 'Auditoría', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [37]],
            ['url' => '/indicadores/auditoria2', 'label' => 'Auditoría 2', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [38]],
            ['url' => '/indicadores/seguimiento', 'label' => 'Seguimiento', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [39]],
            ['url' => '/caphum/gestion', 'label' => 'Capital Humano - Gestión', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [4]],
            ['url' => '/caphum/candidatos', 'label' => 'Candidatos', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [42]],
            ['url' => '/caphum/bajas', 'label' => 'Bajas', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [13]],
            ['url' => '/caphum/organigrama', 'label' => 'Organigrama', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [5]],
            ['url' => '/reporteria/resumencallcenter', 'label' => 'Resumen Call Center', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [6]],
            ['url' => '/reporteria/layoutlegacy', 'label' => 'Layout Legacy', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [7]],
            ['url' => '/estadocuenta/reporteDictamen', 'label' => 'Dictamen de Llamadas', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [14]],
            ['url' => '/reporteria/reporteCapitalHumano', 'label' => 'Reporte CH', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [21]],
            ['url' => '/condonaciones/historial', 'label' => 'Historial Condonaciones', 'icon' => 'fa-solid fa-hand-holding-dollar', 'bg' => 'bg-red', 'modulos' => [15]],
            ['url' => '/sabueso/ticket', 'label' => 'Sabueso - Ticket', 'icon' => 'fa-solid fa-dog', 'bg' => 'bg-teal', 'modulos' => [18]],
            ['url' => '/sabueso/paneladmin', 'label' => 'Panel Admin', 'icon' => 'fa-solid fa-dog', 'bg' => 'bg-teal', 'modulos' => [19]],
            ['url' => '/sabueso/cerradoEliminado', 'label' => 'Cerrado/Eliminado', 'icon' => 'fa-solid fa-dog', 'bg' => 'bg-teal', 'modulos' => [19]],
            ['url' => '/Despachos/AsignacionCreditosDespacho', 'label' => 'Despachos', 'icon' => 'fa-solid fa-building-columns', 'bg' => 'bg-yellow', 'modulos' => [20]],
            ['url' => '/paises/consulta', 'label' => 'Países', 'icon' => 'fa-solid fa-globe', 'bg' => 'bg-blue', 'modulos' => [41]],
            ['url' => '/onboarding/index', 'label' => 'Curso Onboarding', 'icon' => 'fa-solid fa-graduation-cap', 'bg' => 'bg-blue', 'modulos' => [44]],
            ['url' => '/departamentos/consulta/', 'label' => 'Departamentos', 'icon' => 'fa-solid fa-cog', 'bg' => 'bg-blue', 'modulos' => [10]],
            ['url' => '/equivalencias/consulta', 'label' => 'Equivalencia puestos', 'icon' => 'fa-solid fa-cog', 'bg' => 'bg-blue', 'modulos' => [17]],
            ['url' => '/segundometro/shell', 'label' => 'Shell Segundómetro', 'icon' => 'fa-solid fa-cog', 'bg' => 'bg-blue', 'modulos' => [16]],
        ];
    }
}

if (!function_exists('getAccesosRapidosDesdeModulos')) {
    function getAccesosRapidosDesdeModulos()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        $items = [];
        foreach (getMenuItemsConfig() as $row) {
            if (!empty($row['modulos']) && !array_intersect($row['modulos'], $modulos)) {
                continue;
            }
            // No mostrar Indicadores en accesos rápidos (son muchos y recargan la página)
            $path = trim(parse_url($row['url'], PHP_URL_PATH), '/');
            if (stripos($path, 'indicadores/') === 0) {
                continue;
            }
            $items[] = ['url' => $row['url'], 'label' => $row['label'], 'icon' => $row['icon'], 'bg' => $row['bg'] ?? 'bg-blue'];
        }
        return $items;
    }
}

/**
 * Devuelve mapa path_normalizado => [módulos]. Usado para autorización en index.php.
 */
if (!function_exists('getRutasModulos')) {
    function getRutasModulos()
    {
        $rutas = [];
        foreach (getMenuItemsConfig() as $row) {
            if (empty($row['modulos'])) {
                continue;
            }
            $path = trim(parse_url($row['url'], PHP_URL_PATH), '/');
            $path = strtolower(preg_replace('#/+#', '/', $path));
            if ($path !== '') {
                $rutas[$path] = $row['modulos'];
            }
        }
        return $rutas;
    }
}

/**
 * Módulos requeridos por controlador completo (todas las acciones).
 * Si la ruta concreta no está en getRutasModulos(), se usa esto.
 */
if (!function_exists('getControladoresModulos')) {
    function getControladoresModulos()
    {
        return [
            'segundometro' => [16],
            'paises' => [41],
        ];
    }
}

/**
 * Devuelve array de módulos requeridos para controller/metodo, o null si no hay restricción.
 */
if (!function_exists('getModulosRequeridos')) {
    function getModulosRequeridos($controller, $metodo)
    {
        $path = strtolower(trim($controller)) . '/' . strtolower(trim($metodo));
        $rutas = getRutasModulos();
        if (isset($rutas[$path])) {
            return $rutas[$path];
        }
        $controlador = strtolower(trim($controller));
        $porControlador = getControladoresModulos();
        if (isset($porControlador[$controlador])) {
            return $porControlador[$controlador];
        }
        return null;
    }
}
