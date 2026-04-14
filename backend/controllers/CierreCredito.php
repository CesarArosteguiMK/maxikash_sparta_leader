<?php

namespace Controllers;

use Core\Controller;
use Models\CierreCredito as CierreCreditoDAO;

class CierreCredito extends Controller
{
    /**
     * modulos_web — pestaña «Permisos especiales» (Cierre de crédito).
     * Deben coincidir con los registros asignables en CapHum / BD.
     */
    private const CC_PESTANA_PERM_CONVENIOS = 52;

    private const CC_PESTANA_PERM_VALIDACION = 53;

    private const CC_PESTANA_PERM_EN_PROCESO = 54;

    private const CC_PESTANA_PERM_HISTORIAL = 55;

    private function cierreTieneModuloPermisoSesion(int $moduloId): bool
    {
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));

        return in_array($moduloId, $mods, true);
    }

    /**
     * @param list<int> $moduloIds
     */
    private function cierreTieneAlgunoDeModulos(array $moduloIds): bool
    {
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        foreach ($moduloIds as $id) {
            if (in_array((int) $id, $mods, true)) {
                return true;
            }
        }

        return false;
    }

    private function cierreRequiereModuloPermiso(int $moduloId): void
    {
        if ($this->cierreTieneModuloPermisoSesion($moduloId)) {
            return;
        }
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'No tiene permiso para realizar esta acción.',
        ]);
    }

    /**
     * @param list<int> $moduloIds
     */
    private function cierreRequiereAlgunoDeModulos(array $moduloIds): void
    {
        if ($this->cierreTieneAlgunoDeModulos($moduloIds)) {
            return;
        }
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'No tiene permiso para realizar esta acción.',
        ]);
    }

    // ─────────────────────────────────────────────
    // VISTA PRINCIPAL
    // ─────────────────────────────────────────────

    public function consulta()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', 'Cierre de crédito | ' . $emp);

        $ccPermConvenios = $this->cierreTieneModuloPermisoSesion(self::CC_PESTANA_PERM_CONVENIOS);
        $ccPermValidacion = $this->cierreTieneModuloPermisoSesion(self::CC_PESTANA_PERM_VALIDACION);
        $ccPermEnProceso = $this->cierreTieneModuloPermisoSesion(self::CC_PESTANA_PERM_EN_PROCESO);
        $ccPermHistorial = $this->cierreTieneModuloPermisoSesion(self::CC_PESTANA_PERM_HISTORIAL);
        $ccPermAlguno = $ccPermConvenios || $ccPermValidacion || $ccPermEnProceso || $ccPermHistorial;

        $ccDefaultTab = null;
        foreach (
            [
                'convenios' => $ccPermConvenios,
                'validacion' => $ccPermValidacion,
                'en_proceso' => $ccPermEnProceso,
                'historial' => $ccPermHistorial,
            ] as $clave => $ok
        ) {
            if ($ok) {
                $ccDefaultTab = $clave;
                break;
            }
        }

        $this->set('cc_perm_convenios', $ccPermConvenios);
        $this->set('cc_perm_validacion', $ccPermValidacion);
        $this->set('cc_perm_en_proceso', $ccPermEnProceso);
        $this->set('cc_perm_historial', $ccPermHistorial);
        $this->set('cc_perm_alguno', $ccPermAlguno);
        $this->set('cc_default_tab', $ccDefaultTab);

        $this->render('cierre_credito_consulta');
    }

    // ─────────────────────────────────────────────
    // API: LISTADO EN PROCESO
    // ─────────────────────────────────────────────

    public function getEnProceso()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_EN_PROCESO);
        $r = CierreCreditoDAO::getEnProceso();
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: LISTADO ENVIADO FINALIZADO
    // ─────────────────────────────────────────────

    public function getEnviadoFinalizado()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_VALIDACION);
        $r = CierreCreditoDAO::getEnviadoFinalizado();
        // Añadir el usuario de sesión para mostrarlo en la UI de validación
        $r['validador'] = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'Sin sesión';
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: CREAR REGISTRO
    // ─────────────────────────────────────────────

    public function crear()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_VALIDACION);
        $campos = ['id_credito', 'nombre_cliente', 'estatus'];

        $datos = [];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                self::respuestaJSON(self::respuesta(false, "Campo requerido faltante: $campo"));
            }
            $datos[$campo] = trim($_POST[$campo]);
        }

        $datos['usuario_alta'] = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

        $r = CierreCreditoDAO::crear($datos);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: CAMBIAR ESTATUS
    // ─────────────────────────────────────────────

    public function cambiarEstatus()
    {
        $this->cierreRequiereAlgunoDeModulos([
            self::CC_PESTANA_PERM_VALIDACION,
            self::CC_PESTANA_PERM_EN_PROCESO,
        ]);
        $id      = isset($_POST['id'])      ? (int) $_POST['id']            : 0;
        $estatus = isset($_POST['estatus']) ? trim($_POST['estatus'])        : '';

        if ($id <= 0 || $estatus === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetros inválidos.'));
        }

        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

        $r = CierreCreditoDAO::cambiarEstatus($id, $estatus, $usuario);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: ENVIAR A CARTERA
    // ─────────────────────────────────────────────

    public function enviarACartera()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_EN_PROCESO);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }

        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';
        $r = CierreCreditoDAO::enviarACartera($id, $usuario);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: MARCAR LISTO PARA REENVÍO (en_cola → listo_envio)
    // ─────────────────────────────────────────────

    public function marcarListoEnvio()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_HISTORIAL);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }
        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';
        $r = CierreCreditoDAO::marcarListoEnvio($id, $usuario);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: REENVIAR A CARTERA (listo_envio → enviado_cartera / en_cola)
    // ─────────────────────────────────────────────

    public function reenviarACartera()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_HISTORIAL);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }
        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';
        $r = CierreCreditoDAO::enviarACartera($id, $usuario, 'listo_envio');
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: TODOS LOS CONVENIOS (pestaña Convenios)
    // ─────────────────────────────────────────────

    public function getAllConvenios()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_CONVENIOS);
        $r = CierreCreditoDAO::getAllConvenios();
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: DETALLE DE CONVENIO (por convenio_cliente.id)
    // ─────────────────────────────────────────────

    public function getDetalleConvenio()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_CONVENIOS);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }
        $r = CierreCreditoDAO::getDetalleConvenio($id);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: CATÁLOGO DE MOTIVOS DE DESCARTE
    // ─────────────────────────────────────────────

    public function getCatalogoDescarte()
    {
        $r = CierreCreditoDAO::getCatalogoDescarte();
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: DESCARTAR (regresa a Enviados Finalizados)
    // ─────────────────────────────────────────────

    public function descartar()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_EN_PROCESO);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }

        // ID del motivo del catálogo
        $motivoId = isset($_POST['motivo_id']) ? (int) $_POST['motivo_id'] : 0;

        // Comentario libre — sanitizado contra inyecciones
        $comentario = trim(strip_tags($_POST['comentario'] ?? ''));
        $comentario = mb_substr($comentario, 0, 150);

        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';
        $r = CierreCreditoDAO::descartar($id, $usuario, $motivoId, $comentario);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: DETALLE (acordeón)
    // ─────────────────────────────────────────────

    public function getDetalleCierre()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_EN_PROCESO);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID inválido.'));
            return;
        }
        $r = CierreCreditoDAO::getDetalleCierre($id);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // DESCARGA EXCEL CIERRE EN PROCESO
    // ─────────────────────────────────────────────

    public function descargarExcelCierre()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_EN_PROCESO);
        while (ob_get_level()) { ob_end_clean(); }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); die('ID inválido.'); }

        $r = CierreCreditoDAO::getDetalleCierre($id);
        if (!$r['success']) { http_response_code(404); die($r['mensaje']); }

        $cierre   = $r['datos']['cierre'];
        $convenio = $r['datos']['convenio'];
        $amort    = $r['datos']['amortizacion'];

        require_once LIBRERIAS . '/PhpSpreadsheet/PhpSpreadsheet.php';

        // ── Hoja 1: Resumen ──────────────────────
        $colResumen = [
            \PHPSpreadsheet::ColumnaExcel('campo', 'Campo'),
            \PHPSpreadsheet::ColumnaExcel('valor', 'Valor'),
        ];
        $fmtMXN = fn($v) => '$' . number_format((float)$v, 2);
        $dataResumen = [
            ['campo' => 'Crédito #',            'valor' => $cierre['id_credito']],
            ['campo' => 'Cliente',               'valor' => $cierre['nombre_cliente']],
            ['campo' => 'Producto',              'valor' => $convenio['nombre_producto'] ?? '—'],
            ['campo' => 'Adeudo original',       'valor' => $fmtMXN($convenio['adeudo_total_original'] ?? 0)],
            ['campo' => 'Descuento aplicado',    'valor' => ($convenio['porcentaje_descuento'] ?? 0) . '%'],
            ['campo' => 'Monto descuento',       'valor' => $fmtMXN($convenio['descuento_monto'] ?? 0)],
            ['campo' => 'Total a pagar',         'valor' => $fmtMXN($convenio['total_a_pagar'] ?? 0)],
            ['campo' => 'Pago inicial',          'valor' => $fmtMXN($convenio['pago_inicial_monto'] ?? 0)],
            ['campo' => 'Pago semanal',          'valor' => $fmtMXN($convenio['pago_semanal'] ?? 0)],
            ['campo' => 'Número de semanas',     'valor' => $convenio['numero_semanas'] ?? '—'],
            ['campo' => 'Fecha de acuerdo',      'valor' => $convenio['fecha_acuerdo'] ?? '—'],
            ['campo' => 'Fecha primer pago',     'valor' => $convenio['fecha_primer_pago'] ?? '—'],
            ['campo' => 'Fecha último pago',     'valor' => $convenio['fecha_ultimo_pago'] ?? '—'],
            ['campo' => 'Registrado por',        'valor' => $cierre['usuario_alta']],
            ['campo' => 'Fecha alta',            'valor' => $cierre['fecha_alta']],
        ];

        // ── Hoja 2: Tabla de amortización ───────
        $colAmort = [
            \PHPSpreadsheet::ColumnaExcel('semana',       'Semana',        ['estilo' => \PHPSpreadsheet::GetEstilosExcel('centrado')]),
            \PHPSpreadsheet::ColumnaExcel('fecha_pago',   'Fecha pago',    ['estilo' => \PHPSpreadsheet::GetEstilosExcel('centrado')]),
            \PHPSpreadsheet::ColumnaExcel('pago_semanal', 'Pago semanal',  ['estilo' => \PHPSpreadsheet::GetEstilosExcel('moneda'), 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('capital',      'Capital',       ['estilo' => \PHPSpreadsheet::GetEstilosExcel('moneda'), 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('saldo',        'Saldo restante',['estilo' => \PHPSpreadsheet::GetEstilosExcel('moneda')]),
            \PHPSpreadsheet::ColumnaExcel('estatus',      'Estatus',       ['estilo' => \PHPSpreadsheet::GetEstilosExcel('centrado')]),
            \PHPSpreadsheet::ColumnaExcel('fecha_real',   'Fecha pago real',['estilo' => \PHPSpreadsheet::GetEstilosExcel('centrado')]),
            \PHPSpreadsheet::ColumnaExcel('comprobante',  'Comprobante',   ['estilo' => \PHPSpreadsheet::GetEstilosExcel('centrado')]),
        ];

        $dataAmort = array_map(fn($a) => [
            'semana'       => $a['numero_semana'],
            'fecha_pago'   => $a['fecha_pago'],
            'pago_semanal' => $a['pago_semanal'],
            'capital'      => $a['capital'],
            'saldo'        => $a['saldo_restante'],
            'estatus'      => match($a['estatus_pago'] ?? '') {
                'pagado'   => 'Pagado',
                'pendiente'=> 'Pendiente',
                default    => $a['estatus_pago'] ?? '—',
            },
            'fecha_real'   => $a['fecha_pago_real'] ?? '',
            'comprobante'  => (!empty($a['comprobante_path'])) ? 'Sí' : 'No',
        ], $amort);

        // Genera con dos hojas
        $titulo = "Cierre de Crédito #{$cierre['id_credito']} — {$cierre['nombre_cliente']}";
        $libro  = \PHPSpreadsheet::GeneraExcel('Resumen', $titulo, $colResumen, $dataResumen);

        $libroAmort = \PHPSpreadsheet::GeneraExcel('Amortización',
            "Tabla de amortización — Crédito #{$cierre['id_credito']}", $colAmort, $dataAmort);
        $libro->addExternalSheet($libroAmort->getActiveSheet());

        $nombre = 'CierreCredito_' . $cierre['id_credito'] . '_' . date('Ymd');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────
    // API: HISTORIAL
    // ─────────────────────────────────────────────

    public function getHistorial()
    {
        $this->cierreRequiereModuloPermiso(self::CC_PESTANA_PERM_HISTORIAL);
        $r = CierreCreditoDAO::getHistorial();
        self::respuestaJSON($r);
    }
}
