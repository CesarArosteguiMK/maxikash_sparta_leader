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
            ['url' => '/caphum/gestion', 'label' => 'Capital Humano - Gestión', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [4]],
            ['url' => '/caphum/accesosCapitalHumano', 'label' => 'Accesos', 'icon' => 'fa-solid fa-user-shield', 'bg' => 'bg-purple', 'modulos' => [140]],
            ['url' => '/caphum/documentosColaborador', 'label' => 'Mis documentos', 'icon' => 'fa-solid fa-folder-open', 'bg' => 'bg-purple', 'modulos' => [141]],
            ['url' => '/caphum/vacaciones', 'label' => 'Vacaciones', 'icon' => 'fa-solid fa-umbrella-beach', 'bg' => 'bg-purple', 'modulos' => [147]],
            ['url' => '/caphum/vacacionesAdmin', 'label' => 'Panel vacaciones', 'icon' => 'fa-solid fa-clipboard-check', 'bg' => 'bg-purple', 'modulos' => [4]],
            ['url' => '/caphum/documentosRrhh', 'label' => 'Expedientes RR.HH.', 'icon' => 'fa-solid fa-folder-tree', 'bg' => 'bg-purple', 'modulos' => [93]],
            ['url' => '/caphum/actualizacionesInfo', 'label' => 'Revisión RR.HH.', 'icon' => 'fa-solid fa-user-check', 'bg' => 'bg-purple', 'modulos' => [83]],
            ['url' => '/caphum/candidatos', 'label' => 'Selección de Personal', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [42]],
            ['url' => '/caphum/bajas', 'label' => 'Control de Bajas', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [13]],
            ['url' => '/caphum/organigrama', 'label' => 'Organigrama Cobranza', 'icon' => 'fa-solid fa-users', 'bg' => 'bg-purple', 'modulos' => [5]],
            ['url' => '/analitica/callcenter', 'label' => 'Call Center', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [6]],
            ['url' => '/condonaciones/historial', 'label' => 'Historial condonaciones', 'icon' => 'fa-solid fa-file-invoice-dollar', 'bg' => 'bg-orange', 'modulos' => [15, 39]],
            ['url' => '/gastoscobranza/estadisticagc', 'label' => 'Estadísticas Gastos Cobranza', 'icon' => 'fa-solid fa-chart-column', 'bg' => 'bg-orange', 'modulos' => [40]],
            ['url' => '/analitica/primerospagos', 'label' => 'Primeros pagos', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [49]],
            ['url' => '/analitica/layoutlegacy', 'label' => 'Layout Legacy', 'icon' => 'fa-solid fa-file', 'bg' => 'bg-orange', 'modulos' => [7]],
            ['url' => '/analitica/reporteCapitalHumano', 'label' => 'Reportes de Personal', 'icon' => 'fa-solid fa-file-lines', 'bg' => 'bg-purple', 'modulos' => [34]],
            ['url' => '/caphum/estadisticas', 'label' => 'Estadísticas Capital Humano', 'icon' => 'fa-solid fa-chart-pie', 'bg' => 'bg-purple', 'modulos' => [38]],
            ['url' => '/caphum/perfilesPuestos', 'label' => 'Perfiles de puesto', 'icon' => 'fa-solid fa-id-card-clip', 'bg' => 'bg-purple', 'modulos' => [91]],
            ['url' => '/ReporteriaBI/FlujoCobranza', 'label' => 'Flujo cobranza', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [50]],
            ['url' => '/sabueso/ticket', 'label' => 'Sabueso - Ticket', 'icon' => 'fa-solid fa-dog', 'bg' => 'bg-teal', 'modulos' => [18]],
            ['url' => '/sabueso/panelAdminInicio', 'label' => 'Panel Admin', 'icon' => 'fa-solid fa-table-cells', 'bg' => 'bg-teal', 'modulos' => [25, 27]],
            ['url' => '/sabueso/cerradoEliminado', 'label' => 'Cerrado/Eliminado', 'icon' => 'fa-solid fa-dog', 'bg' => 'bg-teal', 'modulos' => [48]],
            ['url' => '/sabueso/estadisticas', 'label' => 'Analítica sabueso', 'icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-orange', 'modulos' => [47]],
            ['url' => '/analitica/comparativas', 'label' => 'Comparativas', 'icon' => 'fa-solid fa-chart-column', 'bg' => 'bg-orange', 'modulos' => [60, 81]],
            ['url' => '/analitica/asignacion', 'label' => 'Direcciones', 'icon' => 'fa-solid fa-user-check', 'bg' => 'bg-orange', 'modulos' => [84]],
            ['url' => '/analitica/asignacionDirecciones', 'label' => 'Direcciones', 'icon' => 'fa-solid fa-map-location-dot', 'bg' => 'bg-orange', 'modulos' => [84]],
            ['url' => '/Despachos/AsignacionCreditosDespacho', 'label' => 'Despachos', 'icon' => 'fa-solid fa-building-columns', 'bg' => 'bg-yellow', 'modulos' => [20]],
            ['url' => '/Despachos/MiGestion', 'label' => 'Mi Gestión', 'icon' => 'fa-solid fa-chart-gantt', 'bg' => 'bg-yellow', 'modulos' => [20]],
            ['url' => '/convenios/consulta', 'label' => 'Convenios', 'icon' => 'fa-solid fa-handshake', 'bg' => 'bg-purple', 'modulos' => [46]],
            ['url' => '/CierreCredito/consulta', 'label' => 'Cierre de Crédito', 'icon' => 'fa-solid fa-file-circle-check', 'bg' => 'bg-blue', 'modulos' => [51]],
            ['url' => '/paises/consulta', 'label' => 'Países', 'icon' => 'fa-solid fa-globe', 'bg' => 'bg-blue', 'modulos' => [41]],
            ['url' => '/onboarding/index', 'label' => 'Curso Onboarding', 'icon' => 'fa-solid fa-graduation-cap', 'bg' => 'bg-blue', 'modulos' => [44]],
            ['url' => '/departamentos/consulta/', 'label' => 'Áreas', 'icon' => 'fa-solid fa-cog', 'bg' => 'bg-blue', 'modulos' => [10]],
            ['url' => '/equivalencias/consulta', 'label' => 'Equivalencia puestos', 'icon' => 'fa-solid fa-cog', 'bg' => 'bg-blue', 'modulos' => [17]],
            ['url' => '/configticketpuesto/consulta', 'label' => 'Asignación por puestos', 'icon' => 'fa-solid fa-ticket', 'bg' => 'bg-blue', 'modulos' => [26]],
            ['url' => '/segundometro/shell', 'label' => 'Segundometro', 'icon' => 'fa-solid fa-laptop', 'bg' => 'bg-secondary', 'modulos' => [16]],
            ['url' => '/gastoscobranza/shell', 'label' => 'Gastos Cobranza', 'icon' => 'fa-solid fa-laptop', 'bg' => 'bg-secondary', 'modulos' => [31]],
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
        // Rastreo (antes Never paid): sin ítem de menú; acceso desde Estado de cuenta (iframe) o URL directa.
        $modsRastreoSinTicket = [18, 27, 29];
        $rutas['reporteria/consultaidcredito'] = $modsRastreoSinTicket;
        $rutas['reporteria/consultacreditorastreo'] = $modsRastreoSinTicket;
        $rutas['analitica/consultaidcredito'] = $modsRastreoSinTicket;
        $rutas['analitica/consultacreditorastreo'] = $modsRastreoSinTicket;
        $rutas['caphum/obtenerdatosactualizacioninfopersona'] = [82];
        $rutas['caphum/obtenerdatosactualizacioninfopersonas'] = [82];
        $rutas['caphum/guardaractualizacioninfopersona'] = [82];
        $rutas['caphum/getconfiguracionsincronizalegacy'] = [89];
        $rutas['caphum/guardarconfiguracionsincronizalegacy'] = [89];
        $rutas['reporteria/getasignaciondireccionescredito'] = [84];
        $rutas['reporteria/postasignaciondireccion'] = [84];
        $rutas['reporteria/postasignaciondireccionesorden'] = [84];
        $rutas['reporteria/postasignaciondireccionessync'] = [84];
        $rutas['analitica/getasignaciondireccionescredito'] = [84];
        $rutas['analitica/postasignaciondireccion'] = [84];
        $rutas['analitica/postasignaciondireccionesorden'] = [84];
        $rutas['analitica/postasignaciondireccionessync'] = [84];
        $rutas['atlas/accesosatlas'] = [137];
        $rutas['atlas/creditosoperacion'] = [139];
        $rutas['atlas/riesgosoperativos'] = [148];
        $rutas['atlas/abanderamiento30'] = [149];
        $rutas['atlas/getriesgosoperativos'] = [148];
        $rutas['atlas/eliminardivision'] = [133];
        $rutas['atlas/fusionardivisiones'] = [133];
        $rutas['atlas/getaccesosatlas'] = [137];
        $rutas['atlas/descargarplantillaaccesosatlas'] = [137];
        $rutas['atlas/importarplantillaaccesosatlas'] = [137];
        $rutas['atlas/sincronizaraccesosatlas'] = [137];
        $rutas['atlas/actualizarexclusionaccesosatlas'] = [137];
        $rutas['atlas/getaccesoatlasdetalle'] = [137];
        $rutas['atlas/guardarpermisosaccesoatlas'] = [137];
        $rutas['atlas/restablecerpasswordaccesoatlas'] = [137];
        $rutas['gastoscobranza/getdashboardestadistica'] = [40];
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
        if (str_starts_with($path, 'analitica/')) {
            $equiv = 'reporteria/' . substr($path, strlen('analitica/'));
            if (isset($rutas[$equiv])) {
                return $rutas[$equiv];
            }
        }
        $controlador = strtolower(trim($controller));
        $porControlador = getControladoresModulos();
        if (isset($porControlador[$controlador])) {
            return $porControlador[$controlador];
        }
        return null;
    }
}
