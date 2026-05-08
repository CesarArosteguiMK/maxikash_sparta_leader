<?php

namespace Controllers;

use Core\Controller;
use Core\EstadoCuentaTimingLog;
use Models\Empresa as EmpresasDAO;
use Models\EstadoCuenta as EstadoCuentaDAO;
use Models\Login as LoginDAO;


class EstadoCuenta extends Controller
{
    private const EC_PERMISO_DICTAMINAR_LLAMADA = 35;
    private const EC_PERMISO_CONDONAR_GASTOS_COBRANZA = 36;
    private const EC_PERMISO_NOTAS_CLIENTE = 37;

    // ---------------- SAFE FLOAT ----------------
    private function safe_float($value, $default = 0.0) {
        if ($value === null || $value === "") return $default;
        if (is_numeric($value)) return floatval($value);
        return $default;
    }

    // ---------------- SAFE INT ----------------
    private function safe_int($value, $default = 0) {
        if ($value === null || $value === "") return $default;
        if (is_numeric($value)) return intval($value);
        return $default;
    }

    /**
     * Meta del último pago efectivo (Segundómetro) para el modal de Aclaraciones GC en vistas de estado de cuenta.
     */
    private function setEcAclaracionesUltimoPagoMetaParaVista(int $idCredito): void
    {
        $metaRow = $idCredito > 0 ? EstadoCuentaDAO::obtenerUltimoPagoEfectivoSegundometroParaCredito($idCredito) : null;
        $tzEc = new \DateTimeZone('America/Mexico_City');
        $hoyEc = new \DateTimeImmutable('today', $tzEc);
        $dowEc = (int) $hoyEc->format('N');
        $minGuardarEc = ($dowEc === 1) ? $hoyEc : $hoyEc->modify('-1 day');
        $tieneUltimoPago = false;
        $ventanaOk = false;
        $ventanaFaltaMensaje = '';
        $ventanaFaltaLunesFinSemana = false;
        if ($metaRow !== null && !empty($metaRow['ymd'])) {
            $tieneUltimoPago = true;
            $v = EstadoCuentaDAO::validarUltimoPagoEfectivoVentanaAclaracionGc($metaRow['ymd']);
            $ventanaOk = !empty($v['ok']);
            if (!$ventanaOk && !empty($v['mensaje'])) {
                $ventanaFaltaMensaje = (string) $v['mensaje'];
                $ventanaFaltaLunesFinSemana = !empty($v['lunes_fin_semana']);
            }
        }
        $ymd = is_array($metaRow) ? ($metaRow['ymd'] ?? null) : null;
        $labelDisplay = 'Sin registro en Segundómetro';
        if ($ymd !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            try {
                $dLab = \DateTimeImmutable::createFromFormat('!Y-m-d', $ymd, $tzEc);
                if ($dLab instanceof \DateTimeImmutable) {
                    $labelDisplay = $dLab->format('d/m/Y');
                }
            } catch (\Throwable $e) {
                $labelDisplay = $ymd;
            }
        }
        self::set('ecAclaracionesUltimoPagoMeta', [
            'ymd' => $ymd,
            'min_guardar_ymd' => $minGuardarEc->format('Y-m-d'),
            'max_guardar_ymd' => $hoyEc->format('Y-m-d'),
            'tiene_ultimo_pago' => $tieneUltimoPago,
            'ventana_ok' => $ventanaOk,
            'permite_guardar' => $tieneUltimoPago,
            'label_display' => $labelDisplay,
            'ventana_falta_mensaje' => $ventanaFaltaMensaje,
            'ventana_falta_lunes_fin_semana' => $ventanaFaltaLunesFinSemana,
        ]);
    }

    /** Permisos especiales (modulos_web en $_SESSION['modulos']). Usuario id 1: mismo bypass que rastreo (29) y aclaraciones GC (30). */
    private function tienePermisoModuloSesion(int $moduloId): bool
    {
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario === 1) {
            return true;
        }
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        return in_array($moduloId, $mods, true);
    }

    private function requierePermisoEstadoCuentaModulo(int $moduloId): void
    {
        if ($this->tienePermisoModuloSesion($moduloId)) {
            return;
        }
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'No tiene permiso para realizar esta acción.',
        ]);
    }

    /**
     * Anexa el JS del panel Sabueso (solo consulta ID) al footer para abrir el modal de rastreo en la misma página.
     */
    private function appendRastreoSabuesoScriptSiAplica(string $scriptHtml, bool $tienePermiso): string
    {
        if (!$tienePermiso) {
            return $scriptHtml;
        }
        try {
            $extra = (new Sabueso())->getPaneladminScriptSoloConsultaParaEstadoCuenta();
            if ($extra !== '') {
                return $scriptHtml . $extra;
            }
        } catch (\Throwable $e) {
            error_log('EstadoCuenta appendRastreoSabuesoScript: ' . $e->getMessage());
        }

        return $scriptHtml;
    }

    /**
     * Consultar documento genérico por tipo (FACTURA, VALIDACIONES, etc.) en oferta_documentos.
     * Usado por la 3ª forma de búsqueda de documentos en el script de estado de cuenta.
     */
    private function consultarDocumentoGenerico($idCredito, $tipoBD)
    {
        return EstadoCuentaDAO::obtenerDocumentoPorTipo($idCredito, $tipoBD);
    }

    // ---------------- PARSEAR CUOTAS ----------------
    private function parse_cuotas_field($value) {
        if ($value === null || $value === '') return [];

        if (is_array($value)) {
            $out = [];
            foreach ($value as $v) {
                $n = (int) $v;
                if ($n > 0) {
                    $out[] = $n;
                }
            }
            return array_values(array_unique($out));
        }

        $value = is_string($value) ? trim($value) : trim((string) $value);
        if ($value === '') return [];

        if (strpos($value, ",") !== false) {
            return array_map('intval', explode(",", $value));
        }

        if (strpos($value, "-") !== false) {
            list($start, $end) = explode("-", $value);
            return range(intval($start), intval($end));
        }

        return [intval($value)];
    }

    /**
     * Motor de conciliacion: por defecto v2 en todos los contextos (web, cron, CLI).
     * Rollback explicito (solo emergencia): EC_ESTADO_CUENTA_ENGINE_V2=0 o ?ecv2=0
     * El flujo normal de estado de cuenta es siempre motor v2 (preparación + FIFO GC + notas crédito).
     */
    private function estadoCuentaEngineOverrideFromRequest(): ?bool {
        $raw = $_GET['ecv2'] ?? $_POST['ecv2'] ?? null;
        if ($raw === null) {
            return null;
        }
        $v = strtolower(trim((string) $raw));
        if ($v === '1' || $v === 'true' || $v === 'on' || $v === 'v2') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'off' || $v === 'legacy') {
            return false;
        }
        return null;
    }

    /** Producción: true salvo rollback por env o ?ecv2=0 (motor único v2 + FIFO GC + notas crédito). */
    private function shouldUseEstadoCuentaEngineV2(): bool {
        $override = $this->estadoCuentaEngineOverrideFromRequest();
        if ($override !== null) {
            return $override;
        }
        $v = getenv('EC_ESTADO_CUENTA_ENGINE_V2');
        if ($v === false || $v === null) {
            return true;
        }
        $v = strtolower(trim((string) $v));
        if ($v === '' || $v === '1' || $v === 'true' || $v === 'on' || $v === 'v2') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'off' || $v === 'legacy') {
            return false;
        }
        return true;
    }

    /**
     * Modo sombra para comparar motor legacy vs motor v2 sin cambiar UI.
     */
    private function shouldShadowCompareEstadoCuentaEngineV2(): bool {
        $raw = $_GET['ecv2shadow'] ?? $_POST['ecv2shadow'] ?? null;
        if ($raw !== null) {
            $vReq = strtolower(trim((string) $raw));
            if ($vReq === '1' || $vReq === 'true' || $vReq === 'on') {
                return true;
            }
            if ($vReq === '0' || $vReq === 'false' || $vReq === 'off') {
                return false;
            }
        }
        $v = getenv('EC_ESTADO_CUENTA_ENGINE_V2_SHADOW');
        return is_string($v) && trim($v) === '1';
    }

    private function logEstadoCuentaEngineModo(int $idCredito, bool $usarV2, bool $shadow): void {
        $baseDir = dirname(__DIR__);
        // Mismo árbol que LoginRateLimit, timing, etc.; carpeta en .gitignore (no subir .log).
        $logDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . '__SPARTA_SECRET_REDACTED___engine.log';
        $line = sprintf(
            "[%s] credito=%d engine=%s shadow=%s ecv2_param=%s ecv2shadow_param=%s env_v2=%s env_shadow=%s%s",
            date('Y-m-d H:i:s'),
            $idCredito,
            $usarV2 ? 'v2' : 'legacy',
            $shadow ? '1' : '0',
            isset($_GET['ecv2']) || isset($_POST['ecv2']) ? (string) ($_GET['ecv2'] ?? $_POST['ecv2']) : '-',
            isset($_GET['ecv2shadow']) || isset($_POST['ecv2shadow']) ? (string) ($_GET['ecv2shadow'] ?? $_POST['ecv2shadow']) : '-',
            (string) (getenv('EC_ESTADO_CUENTA_ENGINE_V2') ?: '-'),
            (string) (getenv('EC_ESTADO_CUENTA_ENGINE_V2_SHADOW') ?: '-'),
            PHP_EOL
        );
        @file_put_contents($logFile, $line, FILE_APPEND);
    }

    private function ordenarPagosDeterministicamente(array $pagos): array {
        usort($pagos, function ($a, $b) {
            $fa = (string) ($a["fechaDeposito"] ?? ($a["fechaRegistro"] ?? ($a["fechaValor"] ?? "")));
            $fb = (string) ($b["fechaDeposito"] ?? ($b["fechaRegistro"] ?? ($b["fechaValor"] ?? "")));
            $ta = $this->timestampOrdenFecha($fa);
            $tb = $this->timestampOrdenFecha($fb);
            if ($ta !== $tb) {
                return $ta <=> $tb;
            }
            $ida = (int) ($a["idPago"] ?? 0);
            $idb = (int) ($b["idPago"] ?? 0);
            return $ida <=> $idb;
        });
        return $pagos;
    }

    /**
     * Preparación de pagos para estado de cuenta.
     *
     * Producción usa siempre motor v2 ($legacyMode = false): orden determinista, destinos API completos,
     * expansiones acotadas, y mapa $gcPorIdPago (conciliarExtPagosConNotasGC).
     * $legacyMode = true solo para rollback (EC_ESTADO_CUENTA_ENGINE_V2=0 / ?ecv2=0) y comparación shadow.
     */
    private function prepararPagosListEstadoCuenta(
        array $pagos,
        array $cargos,
        array $listaNotasCargosParaSoloGc,
        int $maxIdCargoEnCargos,
        array $gcPorIdPago = [],
        bool $legacyMode = false
    ): array {
        $isV2 = !$legacyMode;
        $pagosBase = $isV2 ? $this->ordenarPagosDeterministicamente($pagos) : $pagos;

        $montoPorIdCargo = [];
        $esAnticipoPorIdCargo = [];
        foreach ($cargos as $cRep) {
            $idcM = $this->safe_int($cRep["idCargo"] ?? 0);
            if ($idcM <= 0) {
                continue;
            }
            $montoPorIdCargo[$idcM] = round($this->safe_float($cRep["monto"] ?? 0), 2);
            $esAnticipoPorIdCargo[$idcM] = $this->esCargoAnticipoCapital($cRep);
        }

        $pagos_list = [];
        foreach ($pagosBase as $p) {
            $montoPago = $this->safe_float($p["montoPago"] ?? 0);
            $extemporaneos = $this->safe_float($p["extemporaneos"] ?? 0);
            $monto_real = max($montoPago - $extemporaneos, 0);

            $idsCuotaApi = $this->parse_cuotas_field($p["numeroCuotaSemanal"] ?? null);
            if (!$isV2) {
                $idsCuotaApi = (count($idsCuotaApi) > 1) ? [$idsCuotaApi[0]] : $idsCuotaApi;
            }

            $primerIdCargoApi = !empty($idsCuotaApi) ? $this->safe_int($idsCuotaApi[0], 0) : 0;
            // En v2 el mapa FIFO de GC tiene prioridad; si el idPago ya fue conciliado contra una nota
            // GC no hace falta la comprobación por fecha exacta de esPagoSoloGastoCobranza.
            $idPagoKey = ($p['idPago'] ?? null) !== null
                ? (is_numeric($p['idPago']) ? (string) (int) $p['idPago'] : (string) $p['idPago'])
                : '';
            $soloGcDeposito = ($idPagoKey !== '' && isset($gcPorIdPago[$idPagoKey]))
                ? true
                : $this->esPagoSoloGastoCobranza($p, $listaNotasCargosParaSoloGc);
            $soloExtInyectar = ($monto_real <= 0.02 && $extemporaneos > 0.009 && $primerIdCargoApi > 0);

            if (count($idsCuotaApi) > 1 && $monto_real > 0.02) {
                if ($isV2) {
                    // v2: no adelgazar destinos por capacidad; conservar todos los ids
                    // declarados por API y dejar la distribución real al bucle de aplicación.
                    $cuotas = [];
                    $seen = [];
                    foreach ($idsCuotaApi as $idcRaw) {
                        $idcCur = (int) $idcRaw;
                        if ($idcCur <= 0 || isset($seen[$idcCur])) {
                            continue;
                        }
                        $cuotas[] = $idcCur;
                        $seen[$idcCur] = true;
                    }
                    $cuotas = $this->clasificarCuotasMultiIdDeslizanteV2($cuotas, $monto_real, $montoPorIdCargo);
                } else {
                    $disponible = round($monto_real, 2);
                    $cuotas = [];
                    foreach ($idsCuotaApi as $idcRaw) {
                        $idcCur = (int) $idcRaw;
                        if ($disponible <= 0.009) {
                            break;
                        }
                        $necesita = round((float) ($montoPorIdCargo[$idcCur] ?? 0), 2);
                        if ($necesita <= 0.009) {
                            continue;
                        }
                        $aplicarSeq = min($disponible, $necesita);
                        if ($aplicarSeq > 0.009) {
                            $cuotas[] = $idcCur;
                            $disponible = round($disponible - $aplicarSeq, 2);
                        }
                    }
                }
            } else {
                $cuotas = $idsCuotaApi;
            }

            $debeExpandirTrasAnticipo = !$isV2;
            if ($isV2) {
                foreach ($cuotas as $idTmp) {
                    if (!empty($esAnticipoPorIdCargo[(int) $idTmp])) {
                        $debeExpandirTrasAnticipo = true;
                        break;
                    }
                }
            }
            if ($debeExpandirTrasAnticipo) {
                $cuotas = $this->expandIdCargosTrasAnticipos($cuotas, $cargos);
            }

            if ($maxIdCargoEnCargos > 0 && !empty($cuotas)) {
                $maxEnPago = max($cuotas);
                if ($maxEnPago > $maxIdCargoEnCargos && !in_array($maxIdCargoEnCargos, $cuotas, true)) {
                    $cuotas[] = $maxIdCargoEnCargos;
                }
            }

            $debeExpandirPosteriores = ($monto_real > 0.02 && !$soloGcDeposito && !$soloExtInyectar);
            if ($isV2 && $debeExpandirPosteriores) {
                $sumaCuotasOriginales = 0.0;
                foreach ($cuotas as $idTmp) {
                    $sumaCuotasOriginales += (float) ($montoPorIdCargo[(int) $idTmp] ?? 0);
                }
                // En v2 solo expandir si el monto real rebasa claramente lo que cubren
                // las cuotas objetivo originales.
                $debeExpandirPosteriores = ($monto_real - round($sumaCuotasOriginales, 2)) > 0.02;
            }
            if ($debeExpandirPosteriores) {
                $cuotas = $this->expandCuotasSemanalesPosterioresParaSobrante($cuotas, $cargos);
            }

            $pagos_list[] = [
                "idPago"              => $p["idPago"] ?? null,
                "remaining"           => round($monto_real, 2),
                "cuotas"              => $cuotas,
                "fechaValor"          => $p["fechaValor"] ?? null,
                "fechaRegistro"       => $p["fechaDeposito"] ?? ($p["fechaRegistro"] ?? null),
                "montoPagoOriginal"   => $montoPago,
                "extemporaneos"       => $extemporaneos,
                "_extOrig"            => $extemporaneos,
                "_extemporaneo_aplicado" => false,
                "es_pago_solo_gasto_cobranza" => $soloGcDeposito,
                "es_pago_solo_extemporaneo_inyectar" => $soloExtInyectar,
                "primer_id_cargo_api" => $primerIdCargoApi,
            ];
        }

        return $pagos_list;
    }

    private function registrarDiferenciasPagosListMotores(int $idCredito, array $legacy, array $v2): void {
        $mapLegacy = [];
        foreach ($legacy as $pl) {
            $id = (string) ($pl["idPago"] ?? '');
            if ($id !== '') {
                $mapLegacy[$id] = $pl;
            }
        }
        $mapV2 = [];
        foreach ($v2 as $pl) {
            $id = (string) ($pl["idPago"] ?? '');
            if ($id !== '') {
                $mapV2[$id] = $pl;
            }
        }

        $ids = array_unique(array_merge(array_keys($mapLegacy), array_keys($mapV2)));
        $diffs = [];
        foreach ($ids as $idPago) {
            $a = $mapLegacy[$idPago] ?? null;
            $b = $mapV2[$idPago] ?? null;
            if ($a === null || $b === null) {
                $diffs[] = "idPago={$idPago}: presente solo en " . ($a === null ? 'v2' : 'legacy');
                continue;
            }
            $cuotasA = implode(',', array_map('strval', (array) ($a["cuotas"] ?? [])));
            $cuotasB = implode(',', array_map('strval', (array) ($b["cuotas"] ?? [])));
            if ($cuotasA !== $cuotasB) {
                $diffs[] = "idPago={$idPago}: cuotas legacy=[{$cuotasA}] vs v2=[{$cuotasB}]";
            }
            $remA = round((float) ($a["remaining"] ?? 0), 2);
            $remB = round((float) ($b["remaining"] ?? 0), 2);
            if (abs($remA - $remB) > 0.009) {
                $diffs[] = "idPago={$idPago}: remaining legacy={$remA} vs v2={$remB}";
            }
        }
        if (!empty($diffs)) {
            $max = min(count($diffs), 20);
            for ($i = 0; $i < $max; $i++) {
                error_log("[EstadoCuenta v2-shadow][credito={$idCredito}] " . $diffs[$i]);
            }
            if (count($diffs) > $max) {
                error_log("[EstadoCuenta v2-shadow][credito={$idCredito}] ...diffs truncados: " . (count($diffs) - $max));
            }
        }
    }

    private function etiquetaNotaCreditoDesdeConcepto(string $concepto): string {
        $u = mb_strtoupper(trim($concepto));
        if (strpos($u, 'CAPITAL') !== false) {
            return 'nota_credito_capital';
        }
        if (strpos($u, 'INTER') !== false) {
            return 'nota_credito_interes';
        }
        return 'nota_credito_otro';
    }

    /**
     * Integra notas de credito (QUITA/condonacion) al flujo v2.
     * Se agregan como aplicados validos que si cuentan para cierre de cuota.
     */
    private function aplicarNotasCreditoEnTablaV2(array &$tabla, array $notasCreditos): void {
        if (empty($tabla) || empty($notasCreditos)) {
            return;
        }
        $idxPorCargo = [];
        foreach ($tabla as $i => $fila) {
            $idC = (int) ($fila['idCargo'] ?? 0);
            if ($idC > 0) {
                $idxPorCargo[$idC] = $i;
            }
        }
        foreach ($notasCreditos as $nota) {
            $idCargo = (int) ($nota['idCargo'] ?? 0);
            if ($idCargo <= 0 || !isset($idxPorCargo[$idCargo])) {
                continue;
            }
            $monto = round($this->safe_float($nota['monto'] ?? 0), 2);
            if ($monto <= 0.009) {
                continue;
            }
            $concepto = (string) ($nota['concepto'] ?? 'NOTA DE CRÉDITO');
            $fecha = $nota['fechaValor'] ?? null;
            $tabla[$idxPorCargo[$idCargo]]['aplicados'][] = [
                'tipo' => 'nota_credito',
                'subtipo' => $this->etiquetaNotaCreditoDesdeConcepto($concepto),
                'idNota' => $nota['idNota'] ?? null,
                'idPago' => null,
                'montoPago' => $monto,
                'aplicado' => $monto,
                'aplicadoTotalPago' => $monto,
                'fechaRegistro' => $fecha,
                'fechaPago' => null,
                'diasMora' => null,
                'extemporaneos' => 0,
                'es_sobrante' => false,
                'gasto_cobranza' => false,
                'concepto' => $concepto,
                '_sortDate' => $fecha,
                // Nota de credito SI cuenta para cerrar pendiente en cuota.
                'no_cuenta_para_total_cuota' => false,
            ];
        }
    }

    private function esCargoAnticipoCapital(array $cargo): bool {
        return mb_strtoupper(trim((string) ($cargo["concepto"] ?? ""))) === 'ANTICIPO A CAPITAL';
    }

    /**
     * Nota de cargo compuesta de crédito (NCCC): la API desglosa una misma operación en varias filas
     * (capital, interés normal, comisión, resguardo) con la misma fecha. No confundir con gastos de
     * cobranza, sobrecargo textual ni líneas CONTRACARGO/REEMBOLSO: va al mismo bucket por fecha
     * que esas últimas para emparejar pago y recalcular cuotas.
     */
    private function esNotaCargoCompuestaCredito(string $conceptoUpper): bool
    {
        static $conceptosNccc = null;
        if ($conceptosNccc === null) {
            $conceptosNccc = [
                'NOTA DE CARGO CONTRA CAPITAL',
                'NOTA DE CARGO CONTRA INTERES NORMAL',
                'NOTA DE CARGO CONTRA INTERÉS NORMAL',
                'NOTA DE CARGO A COMISIÓN',
                'NOTA DE CARGO A COMISION',
                'NOTA DE CARGO POR RESGUARDO',
            ];
        }

        return in_array(trim($conceptoUpper), $conceptosNccc, true);
    }

    /**
     * Tras un idCargo de ANTICIPO en numeroCuotaSemanal, el sobrante del depósito debe poder
     * liquidar la siguiente CUOTA SEMANAL en datosCargos aunque el API no la liste.
     */
    private function siguienteIdCargoCuotaSemanalDespuesDe(int $afterIdCargo, array $ordenIds, array $porId): ?int {
        $found = false;
        foreach ($ordenIds as $idc) {
            if (!$found) {
                if ($idc === $afterIdCargo) {
                    $found = true;
                }
                continue;
            }
            $c = $porId[$idc] ?? null;
            if ($c === null) {
                continue;
            }
            if ($this->esCargoAnticipoCapital($c)) {
                continue;
            }
            if (strpos(mb_strtoupper((string) ($c["concepto"] ?? "")), 'CUOTA SEMANAL') !== false) {
                return $idc;
            }
        }
        return null;
    }

    private function expandIdCargosTrasAnticipos(array $idCargos, array $cargosSorted): array {
        if (empty($idCargos) || empty($cargosSorted)) {
            return $idCargos;
        }
        $porId = [];
        $ordenIds = [];
        foreach ($cargosSorted as $c) {
            $idc = $this->safe_int($c["idCargo"] ?? 0);
            if ($idc <= 0) {
                continue;
            }
            $porId[$idc] = $c;
            $ordenIds[] = $idc;
        }
        $out = [];
        $outSet = [];
        foreach ($idCargos as $idc) {
            $idc = (int) $idc;
            if (!isset($outSet[$idc])) {
                $out[] = $idc;
                $outSet[$idc] = true;
            }
            $cargo = $porId[$idc] ?? null;
            if ($cargo !== null && $this->esCargoAnticipoCapital($cargo)) {
                $sig = $this->siguienteIdCargoCuotaSemanalDespuesDe($idc, $ordenIds, $porId);
                if ($sig !== null && !isset($outSet[$sig])) {
                    $out[] = $sig;
                    $outSet[$sig] = true;
                }
            }
        }
        return $out;
    }

    /**
     * Añade a cuotas todos los idCargo de CUOTA SEMANAL posteriores al mayor id ya listado.
     * Así el remanente (sobrante interno en pagos_list[].remaining) puede aplicarse en cargos
     * siguientes aunque la distribución secuencial por API solo haya enlazado los primeros id.
     */
    private function expandCuotasSemanalesPosterioresParaSobrante(array $idCargos, array $cargosSorted): array {
        if (empty($idCargos) || empty($cargosSorted)) {
            return $idCargos;
        }
        $porId    = [];
        $ordenIds = [];
        foreach ($cargosSorted as $c) {
            $idc = $this->safe_int($c["idCargo"] ?? 0);
            if ($idc <= 0) {
                continue;
            }
            $porId[$idc] = $c;
            $ordenIds[]  = $idc;
        }
        $maxListed = 0;
        foreach ($idCargos as $idc) {
            $maxListed = max($maxListed, (int) $idc);
        }
        if ($maxListed <= 0) {
            return $idCargos;
        }
        $anchorIdx = null;
        for ($i = 0; $i < count($ordenIds); $i++) {
            if ($ordenIds[$i] === $maxListed) {
                $anchorIdx = $i;
                break;
            }
        }
        if ($anchorIdx === null) {
            for ($i = count($ordenIds) - 1; $i >= 0; $i--) {
                if ($ordenIds[$i] <= $maxListed) {
                    $anchorIdx = $i;
                    break;
                }
            }
        }
        if ($anchorIdx === null) {
            return $idCargos;
        }
        $set = [];
        $out = [];
        foreach ($idCargos as $idc) {
            $idc = (int) $idc;
            if (!isset($set[$idc])) {
                $out[]     = $idc;
                $set[$idc] = true;
            }
        }
        for ($i = $anchorIdx + 1; $i < count($ordenIds); $i++) {
            $idc   = $ordenIds[$i];
            $cargo = $porId[$idc] ?? null;
            if ($cargo === null) {
                continue;
            }
            if ($this->esCargoAnticipoCapital($cargo)) {
                continue;
            }
            if (strpos(mb_strtoupper((string) ($cargo["concepto"] ?? "")), 'CUOTA SEMANAL') === false) {
                continue;
            }
            if (!isset($set[$idc])) {
                $out[]     = $idc;
                $set[$idc] = true;
            }
        }
        return $out;
    }

    /**
     * Depósito cuyo monto va a extemporáneos y corresponde a nota de GASTOS DE COBRANZA (misma fecha).
     * Puede ser el monto total de la nota o un abono parcial (p. ej. 763 de 1500 el mismo día).
     * No es pago de cuota; no debe mezclarse con aplicación a cargo salvo para mostrarlo en UI.
     */
    private function esPagoSoloGastoCobranza(array $pagoApi, array $listaNotasCargos): bool {
        if (empty($listaNotasCargos) || !is_array($listaNotasCargos)) {
            return false;
        }
        $montoPago = $this->safe_float($pagoApi["montoPago"] ?? 0);
        $ext = $this->safe_float($pagoApi["extemporaneos"] ?? 0);
        if ($montoPago <= 0.009 || $ext <= 0.009) {
            return false;
        }
        $montoReal = max($montoPago - $ext, 0);
        if ($montoReal > 0.02) {
            return false;
        }
        $fechaP = $pagoApi["fechaDeposito"] ?? $pagoApi["fechaValor"] ?? $pagoApi["fechaRegistro"] ?? "";
        if ($fechaP === "" || strtotime((string) $fechaP) === false) {
            return false;
        }
        $fn = date("Y-m-d", strtotime((string) $fechaP));

        foreach ($listaNotasCargos as $nota) {
            $conceptoUpper = mb_strtoupper((string) ($nota["concepto"] ?? ""));
            if (strpos($conceptoUpper, "GASTO") === false || strpos($conceptoUpper, "COBRANZA") === false) {
                continue;
            }
            if (strpos($conceptoUpper, "EXTEMPORANEO") !== false || strpos($conceptoUpper, "EXTEMPORÁNEO") !== false) {
                continue;
            }
            $fechaNota = $nota["fechaMovimiento"] ?? $nota["fechaVencimiento"] ?? "";
            if ($fechaNota === "" || strtotime((string) $fechaNota) === false) {
                continue;
            }
            $fnNota = date("Y-m-d", strtotime((string) $fechaNota));
            if ($fnNota !== $fn) {
                continue;
            }
            $mNota = $this->safe_float($nota["monto"] ?? 0);
            if (abs($mNota - $ext) <= 1.0 || abs($mNota - $montoPago) <= 1.0) {
                return true;
            }
            // Nota mayor que el depósito (misma fecha, 100% ext): abono parcial a GC — sin tope de delta
            // (p. ej. 200+remanente vs 208; 763 vs nota 1500).
            if ($mNota + 0.01 >= $montoPago && $mNota - $montoPago > 0.02) {
                return true;
            }
        }
        return false;
    }

    /**
     * v2: concilia pagos 100% extemporáneos contra notas de GASTOS DE COBRANZA mediante FIFO.
     *
     * La API puede enviar varios depósitos (en fechas distintas) que en conjunto liquidan una sola
     * nota GC. esPagoSoloGastoCobranza exige coincidencia de fecha exacta, por lo que los depósitos
     * posteriores a la nota (p. ej. nota del 23/03, depósito del 29/03) quedan sin clasificar.
     * Este método corrige eso: ordena notas y pagos por fecha y asigna FIFO cada depósito 100% ext
     * a la nota GC más antigua que aún tenga saldo, siempre que la nota sea anterior o igual al pago.
     *
     * El resultado se pasa como $gcPorIdPago solo cuando prepararPagosListEstadoCuenta corre en v2 (legacyMode false).
     *
     * @return array<string,true>  mapa idPago (string) => true
     */
    private function conciliarExtPagosConNotasGC(array $pagos, array $listaNotasCargos): array
    {
        // 1. Extraer notas GC (excluir extemporáneos textuales)
        $notasGc = [];
        foreach ($listaNotasCargos as $nota) {
            $conceptoUpper = mb_strtoupper(trim((string) ($nota['concepto'] ?? '')));
            if (strpos($conceptoUpper, 'GASTO') === false || strpos($conceptoUpper, 'COBRANZA') === false) {
                continue;
            }
            if (strpos($conceptoUpper, 'EXTEMPORANEO') !== false || strpos($conceptoUpper, 'EXTEMPORÁNEO') !== false) {
                continue;
            }
            $fechaRaw = $nota['fechaMovimiento'] ?? $nota['fechaVencimiento'] ?? '';
            if ($fechaRaw === '' || strtotime((string) $fechaRaw) === false) {
                continue;
            }
            $monto = $this->safe_float($nota['monto'] ?? 0);
            if ($monto <= 0.009) {
                continue;
            }
            $notasGc[] = [
                'fecha'    => date('Y-m-d', strtotime((string) $fechaRaw)),
                'restante' => $monto,
            ];
        }
        if (empty($notasGc)) {
            return [];
        }
        // Ordenar notas por fecha ASC para FIFO
        usort($notasGc, fn($a, $b) => $a['fecha'] <=> $b['fecha']);

        // 2. Recopilar pagos 100% extemporáneos ordenados por fecha ASC
        $extPagos = [];
        foreach ($pagos as $p) {
            $montoPago = $this->safe_float($p['montoPago'] ?? 0);
            $ext       = $this->safe_float($p['extemporaneos'] ?? 0);
            if ($montoPago <= 0.009 || $ext <= 0.009) {
                continue;
            }
            if (max($montoPago - $ext, 0) > 0.02) {
                continue;  // pago mixto: parte a cuota, parte ext — no entra aquí
            }
            $fechaRaw = $p['fechaDeposito'] ?? $p['fechaValor'] ?? $p['fechaRegistro'] ?? '';
            if ($fechaRaw === '' || strtotime((string) $fechaRaw) === false) {
                continue;
            }
            $extPagos[] = [
                'idPago' => $p['idPago'] ?? null,
                'fecha'  => date('Y-m-d', strtotime((string) $fechaRaw)),
                'ext'    => $ext,
            ];
        }
        if (empty($extPagos)) {
            return [];
        }
        usort($extPagos, fn($a, $b) => $a['fecha'] <=> $b['fecha']);

        // 3. FIFO: asignar cada pago ext a la nota GC más antigua con saldo y fecha ≤ fecha del pago
        $resultado = [];
        foreach ($extPagos as $ep) {
            if ($ep['idPago'] === null) {
                continue;
            }
            foreach ($notasGc as &$nota) {
                if ($nota['restante'] <= 0.009) {
                    continue;  // nota ya saldada
                }
                if ($nota['fecha'] > $ep['fecha']) {
                    continue;  // nota creada después del depósito — no aplica
                }
                // El depósito encaja dentro del saldo restante (tolerancia $1 por redondeo)
                if ($ep['ext'] <= round($nota['restante'], 2) + 1.0) {
                    $nota['restante'] = round($nota['restante'] - $ep['ext'], 2);
                    $key = is_numeric($ep['idPago']) ? (string) (int) $ep['idPago'] : (string) $ep['idPago'];
                    $resultado[$key] = true;
                    break;
                }
            }
            unset($nota);
        }
        return $resultado;
    }

    /**
     * v2: la API a veces devuelve dos idCargo consecutivos como "ventana deslizante" aunque el monto
     * real corresponde a una sola cuota. Si el monto encaja con UNA cuota y no con la suma de ambas,
     * se usa solo ese idCargo para enlazar el pago.
     *
     * @param array<int,int> $idsCuotaOrdenApi
     * @param array<int,float> $montoPorIdCargo
     * @return array<int,int>
     */
    private function clasificarCuotasMultiIdDeslizanteV2(array $idsCuotaOrdenApi, float $montoReal, array $montoPorIdCargo): array {
        $seen = [];
        $ids = [];
        foreach ($idsCuotaOrdenApi as $idcRaw) {
            $idc = (int) $idcRaw;
            if ($idc <= 0 || isset($seen[$idc])) {
                continue;
            }
            $seen[$idc] = true;
            $ids[] = $idc;
        }
        if (count($ids) !== 2) {
            return $ids;
        }
        $a = min($ids[0], $ids[1]);
        $b = max($ids[0], $ids[1]);
        if ($b !== $a + 1) {
            return $ids;
        }
        $ma = round((float) ($montoPorIdCargo[$a] ?? 0), 2);
        $mb = round((float) ($montoPorIdCargo[$b] ?? 0), 2);
        if ($ma <= 0.009 || $mb <= 0.009) {
            return $ids;
        }
        $mr = round($montoReal, 2);
        $sum = round($ma + $mb, 2);
        $tol = 1.0;
        if (abs($mr - $sum) <= $tol) {
            return $ids;
        }
        $closeA = abs($mr - $ma) <= $tol;
        $closeB = abs($mr - $mb) <= $tol;
        if ($closeA && !$closeB) {
            return [$a];
        }
        if ($closeB && !$closeA) {
            return [$b];
        }
        return $ids;
    }

    /**
     * Inserta depósitos 100% extemporáneos (sin monto a cuota).
     * Con nota GC: una línea por movimiento en la fila del idCargo del API.
     * Sin nota: reparte N depósitos en N filas de cuota consecutivas terminando en ese idCargo (como pagos/sobrantes); si no hay filas suficientes, un resumen.
     * No suma al total_pagado del cargo.
     */
    private function inyectarDepositosSoloGastoCobranza(array &$tabla, array $pagos_list): void {
        if (empty($tabla) || empty($pagos_list)) {
            return;
        }
        $indicesCuotas = [];
        for ($ti = 0; $ti < count($tabla); $ti++) {
            if (($tabla[$ti]["tipo"] ?? "") === "anticipo") {
                continue;
            }
            $indicesCuotas[] = $ti;
        }
        $porAncla = [];
        foreach ($pagos_list as $pl) {
            if (empty($pl["es_pago_solo_extemporaneo_inyectar"])) {
                continue;
            }
            $idRef = (int) ($pl["primer_id_cargo_api"] ?? 0);
            if ($idRef <= 0) {
                continue;
            }
            $tablaIdx = null;
            for ($ti = 0; $ti < count($tabla); $ti++) {
                if ((int) ($tabla[$ti]["idCargo"] ?? 0) === $idRef) {
                    $tablaIdx = $ti;
                    break;
                }
            }
            if ($tablaIdx === null) {
                continue;
            }
            // Misma fila que el cargo del API (p. ej. id 56 → cuota 56); si esa fila es anticipo, usar la siguiente fila de cuota.
            $injectIdx = $tablaIdx;
            while ($injectIdx < count($tabla) && (($tabla[$injectIdx]["tipo"] ?? "") === "anticipo")) {
                $injectIdx++;
            }
            if ($injectIdx >= count($tabla)) {
                $injectIdx = $tablaIdx;
            }
            if (!isset($porAncla[$injectIdx])) {
                $porAncla[$injectIdx] = ["gc" => [], "ext" => []];
            }
            if (!empty($pl["es_pago_solo_gasto_cobranza"])) {
                $porAncla[$injectIdx]["gc"][] = $pl;
            } else {
                $porAncla[$injectIdx]["ext"][] = $pl;
            }
        }
        foreach ($porAncla as $injectIdx => $grupos) {
            foreach ($grupos["gc"] as $pl) {
                $mp = round((float) ($pl["montoPagoOriginal"] ?? 0), 2);
                $ext = round((float) ($pl["_extOrig"] ?? $pl["extemporaneos"] ?? 0), 2);
                $tabla[$injectIdx]["aplicados"][] = [
                    "tipo"                    => "gasto_cobranza_deposito",
                    "idPago"                  => $pl["idPago"] ?? null,
                    "montoPago"               => $mp,
                    "aplicado"                => $ext,
                    "aplicadoTotalPago"       => $mp,
                    "fechaRegistro"           => $pl["fechaRegistro"] ?? null,
                    "fechaPago"               => null,
                    "diasMora"                => null,
                    "extemporaneos"           => 0,
                    "es_sobrante"             => false,
                    "gasto_cobranza"          => true,
                    "no_cuenta_para_total_cuota" => true,
                ];
            }
            $exts = $grupos["ext"];
            if (empty($exts)) {
                continue;
            }
            usort($exts, function ($a, $b) {
                $fa = strtotime((string) ($a["fechaRegistro"] ?? ""));
                $fb = strtotime((string) ($b["fechaRegistro"] ?? ""));
                if ($fa === false) {
                    $fa = 0;
                }
                if ($fb === false) {
                    $fb = 0;
                }
                return $fa <=> $fb;
            });
            $idRefExt = (int) ($exts[0]["primer_id_cargo_api"] ?? 0);
            $posAnchor = null;
            for ($ip = 0; $ip < count($indicesCuotas); $ip++) {
                $ri = $indicesCuotas[$ip];
                if ((int) ($tabla[$ri]["idCargo"] ?? 0) === $idRefExt) {
                    $posAnchor = $ip;
                    break;
                }
            }
            $n = count($exts);
            if ($posAnchor === null || $posAnchor < $n - 1) {
                $sum = 0.0;
                foreach ($exts as $e) {
                    $sum += (float) ($e["montoPagoOriginal"] ?? 0);
                }
                $sum = round($sum, 2);
                $fechaDesde = $exts[0]["fechaRegistro"] ?? null;
                $fechaHasta = $exts[$n - 1]["fechaRegistro"] ?? null;
                $ids = [];
                foreach ($exts as $e) {
                    if (!empty($e["idPago"])) {
                        $ids[] = $e["idPago"];
                    }
                }
                $tabla[$injectIdx]["aplicados"][] = [
                    "tipo"                       => "extemporaneos_resumen",
                    "cantidad"                   => $n,
                    "montoPago"                  => $sum,
                    "aplicado"                   => $sum,
                    "fechaRegistro"              => $fechaHasta,
                    "fechaDesde"                 => $fechaDesde,
                    "fechaHasta"                 => $fechaHasta,
                    "idPagos"                    => $ids,
                    "no_cuenta_para_total_cuota" => true,
                    "gasto_cobranza"             => false,
                    "_sortDate"                  => $fechaHasta,
                ];
                continue;
            }
            $startPos = $posAnchor - ($n - 1);
            for ($i = 0; $i < $n; $i++) {
                $pl = $exts[$i];
                $rowIdx = $indicesCuotas[$startPos + $i];
                $mp = round((float) ($pl["montoPagoOriginal"] ?? 0), 2);
                $ext = round((float) ($pl["_extOrig"] ?? $pl["extemporaneos"] ?? 0), 2);
                $fr = $pl["fechaRegistro"] ?? null;
                $tabla[$rowIdx]["aplicados"][] = [
                    "tipo"                       => "extemporaneo_deposito",
                    "idPago"                     => $pl["idPago"] ?? null,
                    "montoPago"                  => $mp,
                    "aplicado"                   => $ext,
                    "aplicadoTotalPago"          => $mp,
                    "fechaRegistro"              => $fr,
                    "fechaPago"                  => null,
                    "diasMora"                   => null,
                    "extemporaneos"              => 0,
                    "es_sobrante"                => false,
                    "gasto_cobranza"             => false,
                    "no_cuenta_para_total_cuota" => true,
                    "solo_ext_api"               => true,
                    "_sortDate"                  => $fr,
                ];
            }
        }
    }

    /**
     * Une en una sola línea informativa los gastos de cobranza del mismo día en la misma cuota:
     * depósitos 100% ext (inyectados como gasto_cobranza_deposito) + la parte ext de pagos legítimos
     * que la vista mostraba aparte ("Gasto cobranza: $…"). Reduce extemporaneos en esos pagos para
     * no duplicar. El tope es la nota GC del día (datosNotasCargos) cuando existe.
     */
    private function consolidarGastosCobranzaVisualizacionEnTabla(array &$tabla, array $gastoCobranzaPorFecha): void {
        foreach ($tabla as &$fila) {
            $aps = $fila["aplicados"] ?? [];
            if (count($aps) < 2) {
                continue;
            }
            $porFecha = [];
            foreach ($aps as $i => $ap) {
                if (($ap["tipo"] ?? "") !== "gasto_cobranza_deposito") {
                    continue;
                }
                $fn = $this->fechaNormalizadaAplicadoParaCuota($ap);
                if ($fn === null) {
                    continue;
                }
                if (!isset($porFecha[$fn])) {
                    $porFecha[$fn] = ["gc" => [], "legIdx" => []];
                }
                $porFecha[$fn]["gc"][] = [
                    "idx"     => $i,
                    "aplicado" => round((float) ($ap["aplicado"] ?? 0), 2),
                    "ap"      => $ap,
                ];
            }
            foreach ($aps as $i => $ap) {
                if (!$this->esAplicadoLegitimoConExtParaConsolidacionGc($ap)) {
                    continue;
                }
                $ext = round((float) ($ap["extemporaneos"] ?? 0), 2);
                if ($ext <= 0.009) {
                    continue;
                }
                $fn = $this->fechaNormalizadaAplicadoParaCuota($ap);
                if ($fn === null) {
                    continue;
                }
                if (empty($porFecha[$fn]["gc"])) {
                    continue;
                }
                if (!isset($porFecha[$fn]["legIdx"])) {
                    $porFecha[$fn]["legIdx"] = [];
                }
                $porFecha[$fn]["legIdx"][$i] = true;
            }
            $fusionar = [];
            foreach ($porFecha as $fn => $info) {
                if (empty($info["gc"])) {
                    continue;
                }
                $ngc = count($info["gc"]);
                $notaG = round((float) ($gastoCobranzaPorFecha[$fn] ?? 0), 2);
                $legIdx = array_keys($info["legIdx"] ?? []);
                sort($legIdx);
                $depositSumPrev = 0.0;
                foreach ($info["gc"] as $g) {
                    $depositSumPrev += $g["aplicado"];
                }
                $depositSumPrev = round($depositSumPrev, 2);
                // Lo que falta de la nota GC tras los depósitos 100% ext (p. ej. 208 − 200 = 8 para remanentes).
                $restanteNotaTrasDepositos = $notaG > 0.009
                    ? max(0.0, round($notaG - $depositSumPrev, 2))
                    : 0.0;
                $legExt = [];
                if ($notaG > 0.009 && !empty($legIdx) && $restanteNotaTrasDepositos > 0.009) {
                    $restanteLeg = $restanteNotaTrasDepositos;
                    foreach ($legIdx as $idx) {
                        if (!isset($aps[$idx])) {
                            continue;
                        }
                        $ext = round((float) ($aps[$idx]["extemporaneos"] ?? 0), 2);
                        if ($ext <= 0.009) {
                            continue;
                        }
                        $parte = min($ext, max(0.0, $restanteLeg));
                        if ($parte <= 0.009) {
                            continue;
                        }
                        $legExt[] = ["idx" => $idx, "parte" => round($parte, 2)];
                        $restanteLeg = round($restanteLeg - $parte, 2);
                    }
                }
                if ($ngc >= 2) {
                    $fusionar[$fn] = ["gc" => $info["gc"], "legExt" => ($notaG > 0.009 ? $legExt : [])];
                    continue;
                }
                if ($ngc >= 1 && count($legExt) >= 1 && $notaG > 0.009) {
                    $fusionar[$fn] = ["gc" => $info["gc"], "legExt" => $legExt];
                }
            }
            if (empty($fusionar)) {
                continue;
            }
            $nuevos = [];
            foreach ($aps as $i => $ap) {
                $t = $ap["tipo"] ?? "";
                if ($t === "gasto_cobranza_deposito") {
                    $fn = $this->fechaNormalizadaAplicadoParaCuota($ap);
                    if ($fn !== null && isset($fusionar[$fn])) {
                        continue;
                    }
                }
                if ($this->esAplicadoLegitimoConExtParaConsolidacionGc($ap)) {
                    $fn = $this->fechaNormalizadaAplicadoParaCuota($ap);
                    if ($fn !== null && isset($fusionar[$fn])) {
                        foreach ($fusionar[$fn]["legExt"] as $le) {
                            if ($le["idx"] === $i) {
                                $ext = round((float) ($ap["extemporaneos"] ?? 0), 2);
                                $ap["extemporaneos"] = round(max(0.0, $ext - $le["parte"]), 2);
                                break;
                            }
                        }
                    }
                }
                $nuevos[] = $ap;
            }
            foreach ($fusionar as $fn => $bloque) {
                $depositSum = 0.0;
                $fechaRef = null;
                $idPagoRef = null;
                foreach ($bloque["gc"] as $g) {
                    $depositSum += $g["aplicado"];
                    if ($fechaRef === null) {
                        $fechaRef = $g["ap"]["fechaRegistro"] ?? null;
                    }
                    if ($idPagoRef === null && !empty($g["ap"]["idPago"])) {
                        $idPagoRef = $g["ap"]["idPago"];
                    }
                }
                $extSum = 0.0;
                foreach ($bloque["legExt"] as $le) {
                    $extSum += $le["parte"];
                }
                $notaG = round((float) ($gastoCobranzaPorFecha[$fn] ?? 0), 2);
                $total = round($depositSum + $extSum, 2);
                if ($notaG > 0.009) {
                    $total = min($total, $notaG);
                }
                if ($total <= 0.009) {
                    continue;
                }
                if ($fechaRef === null) {
                    $fechaRef = $fn . " 12:00:00";
                }
                $nuevos[] = [
                    "tipo"                       => "gasto_cobranza_deposito",
                    "idPago"                     => $idPagoRef,
                    "montoPago"                  => $total,
                    "aplicado"                   => $total,
                    "aplicadoTotalPago"          => $total,
                    "fechaRegistro"              => $fechaRef,
                    "fechaPago"                  => null,
                    "diasMora"                   => null,
                    "extemporaneos"              => 0,
                    "es_sobrante"                => false,
                    "gasto_cobranza"             => true,
                    "no_cuenta_para_total_cuota" => true,
                    "gc_consolidado"             => true,
                    "_sortDate"                  => $fechaRef,
                ];
            }
            $fila["aplicados"] = $nuevos;
        }
        unset($fila);
    }

    /**
     * Garantiza visibilidad de GC por fecha cuando el flujo de contracargo
     * reconstruye pagos legítimos y deja extemporaneos=0 en esas líneas.
     * Solo inyecta la diferencia faltante de la nota por fecha y nunca afecta
     * total_pagado de la cuota (línea informativa no contabilizable).
     */
    private function inyectarLineasGastoCobranzaFaltantesPorFecha(array &$tabla, array $gastoCobranzaPorFecha): void {
        if (empty($tabla) || empty($gastoCobranzaPorFecha)) {
            return;
        }

        $gcExistentePorFecha = [];
        $hayExtVisiblePorFecha = [];
        $filaCandidataPorFecha = [];
        $metaPagoPorFecha = [];

        foreach ($tabla as $idxFila => $fila) {
            foreach ($fila["aplicados"] ?? [] as $ap) {
                $fn = $this->fechaNormalizadaAplicadoParaCuota($ap);
                if ($fn === null) {
                    continue;
                }

                $tipo = (string) ($ap["tipo"] ?? "");
                if ($tipo === "gasto_cobranza_deposito") {
                    if (!isset($gcExistentePorFecha[$fn])) {
                        $gcExistentePorFecha[$fn] = 0.0;
                    }
                    $gcExistentePorFecha[$fn] += round((float) ($ap["aplicado"] ?? 0), 2);
                    continue;
                }

                if (!$this->aplicadoEsPagoLegitimoReubicable($ap)) {
                    continue;
                }

                if (!isset($filaCandidataPorFecha[$fn])) {
                    $filaCandidataPorFecha[$fn] = $idxFila;
                    $metaPagoPorFecha[$fn] = [
                        "idPago" => $ap["idPago"] ?? null,
                        "fechaRegistro" => $ap["fechaRegistro"] ?? ($fn . " 12:00:00"),
                    ];
                }

                $ext = round((float) ($ap["extemporaneos"] ?? 0), 2);
                if ($ext > 0.009) {
                    $hayExtVisiblePorFecha[$fn] = true;
                }
            }
        }

        foreach ($gastoCobranzaPorFecha as $fn => $montoNotaRaw) {
            $montoNota = round((float) $montoNotaRaw, 2);
            if ($montoNota <= 0.009) {
                continue;
            }

            // Si la fecha ya tiene GC visible por extemporaneos o ya tiene línea GC suficiente, no duplicar.
            if (!empty($hayExtVisiblePorFecha[$fn])) {
                continue;
            }

            $gcExistente = round((float) ($gcExistentePorFecha[$fn] ?? 0), 2);
            if ($gcExistente >= $montoNota - 0.009) {
                continue;
            }

            if (!isset($filaCandidataPorFecha[$fn])) {
                continue;
            }

            $faltante = round(max(0.0, $montoNota - $gcExistente), 2);
            if ($faltante <= 0.009) {
                continue;
            }

            $idxFila = $filaCandidataPorFecha[$fn];
            $metaPago = $metaPagoPorFecha[$fn] ?? ["idPago" => null, "fechaRegistro" => ($fn . " 12:00:00")];
            $fechaRef = $metaPago["fechaRegistro"] ?? ($fn . " 12:00:00");

            $tabla[$idxFila]["aplicados"][] = [
                "tipo"                       => "gasto_cobranza_deposito",
                "idPago"                     => $metaPago["idPago"] ?? null,
                "montoPago"                  => $faltante,
                "aplicado"                   => $faltante,
                "aplicadoTotalPago"          => $faltante,
                "fechaRegistro"              => $fechaRef,
                "fechaPago"                  => null,
                "diasMora"                   => null,
                "extemporaneos"              => 0,
                "es_sobrante"                => false,
                "gasto_cobranza"             => true,
                "no_cuenta_para_total_cuota" => true,
                "gc_consolidado"             => true,
                "_sortDate"                  => $fechaRef,
            ];
        }
    }

    private function fechaNormalizadaAplicadoParaCuota(array $ap): ?string {
        $fr = $ap["fechaRegistro"] ?? $ap["fechaPago"] ?? null;
        if ($fr === null || $fr === "") {
            return null;
        }
        $ts = strtotime((string) $fr);
        if ($ts === false) {
            return null;
        }
        return date("Y-m-d", $ts);
    }

    private function esAplicadoLegitimoConExtParaConsolidacionGc(array $ap): bool {
        $t = $ap["tipo"] ?? "";
        if (
            $t === "gasto_cobranza_deposito"
            || $t === "contracargo"
            || $t === "extemporaneo_deposito"
            || $t === "extemporaneos_resumen"
            || $t === "nota_credito"
        ) {
            return false;
        }
        if (!empty($ap["gasto_cobranza"])) {
            return false;
        }
        return round((float) ($ap["extemporaneos"] ?? 0), 2) > 0.009;
    }

    /**
     * Indica si la fila tiene línea informativa de depósito extemporáneo / GC (no suma a capital en la cuota).
     */
    private function filaTieneLineaExtInformativa(array $fila): bool {
        foreach ($fila["aplicados"] ?? [] as $ap) {
            $t = $ap["tipo"] ?? "";
            if ($t === "extemporaneo_deposito" || $t === "extemporaneos_resumen" || $t === "gasto_cobranza_deposito") {
                return true;
            }
        }
        return false;
    }

    /**
     * Pago/sobrante que sí cuenta para capital y puede moverse a otra cuota (no contracargo, no fantasma API).
     */
    private function aplicadoEsPagoLegitimoReubicable(array $ap): bool {
        if (!empty($ap["cc_invalido"])) {
            return false;
        }
        if (isset($ap["tipo"]) && $ap["tipo"] === "contracargo") {
            return false;
        }
        if (!empty($ap["no_cuenta_para_total_cuota"])) {
            return false;
        }
        if (empty($ap["idPago"])) {
            return false;
        }
        return true;
    }

    /**
     * Suma aplicada a capital en la fila (misma regla que recalcularTotalesTablaEstadoCuenta).
     */
    private function sumaAplicadosQueCuentanCapital(array $fila): float {
        $total = 0.0;
        foreach ($fila["aplicados"] ?? [] as $ap) {
            if (!empty($ap["cc_invalido"])) {
                continue;
            }
            if (isset($ap["tipo"]) && $ap["tipo"] === "contracargo") {
                continue;
            }
            if (!empty($ap["no_cuenta_para_total_cuota"])) {
                continue;
            }
            $total += (float) ($ap["aplicado"] ?? 0);
        }
        return round($total, 2);
    }

    /**
     * Indica si la fila tiene depósito extemporáneo real (no gasto de cobranza) que justifica reubicar
     * el pago legítimo que coincide en la misma fila.
     * gasto_cobranza_deposito es una línea informativa de un cargo externo: el pago real de esa cuota
     * SÍ pertenece ahí y no debe moverse.
     */
    private function filaTieneExtRealParaReubicacion(array $fila): bool {
        foreach ($fila["aplicados"] ?? [] as $ap) {
            $t = $ap["tipo"] ?? "";
            if ($t === "extemporaneo_deposito" || $t === "extemporaneos_resumen") {
                return true;
            }
        }
        return false;
    }

    /**
     * Indica si el pago (según pagos_list / API) declara el idCargo de la fila entre sus destinos.
     * Evita reubicar aplicaciones que S2 asignó explícitamente a esa cuota aunque la fila también tenga Dep. ext.
     */
    private function pagoLegitimoDeclaraIdCargoEnCuotas($idPago, int $idCargoFila, array $pagosListByIdPago): bool {
        if ($idCargoFila <= 0 || $idPago === null || $idPago === '') {
            return false;
        }
        $key = is_numeric($idPago) ? (string) (int) $idPago : (string) $idPago;
        $pl = $pagosListByIdPago[$key] ?? null;
        if ($pl === null) {
            return false;
        }
        foreach ((array) ($pl['cuotas'] ?? []) as $idc) {
            if ((int) $idc === $idCargoFila) {
                return true;
            }
        }
        return false;
    }

    /**
     * Las líneas Dep. ext. quedan en las cuotas donde se inyectan; los pagos reales que compartían fila
     * se reubican en las primeras cuotas vacías (sin capital aplicado) posteriores a la última fila con Dep. ext.
     * Ej.: ext hasta cuota 56 → pagos reales desde 57.
     * NOTA: gasto_cobranza_deposito NO activa la reubicación; el pago legítimo de esa cuota pertenece ahí.
     *
     * Invariante: no se saca de la fila un aplicado legítimo si el mismo idPago, según pagos_list (destinos API),
     * incluye el idCargo de esa fila — mismo caso que cuota + depósitos 100% ext al mismo numeroCuotaSemanal.
     *
     * @param array<string,array> $pagosListByIdPago mapa idPago (string) => fila de pagos_list
     */
    private function reubicarPagosRealesFueraDeFilasConExtemporaneo(array &$tabla, array $pagosListByIdPago = []): void {
        if (empty($tabla)) {
            return;
        }
        $lastExt = -1;
        foreach ($tabla as $i => $fila) {
            if ($this->filaTieneExtRealParaReubicacion($fila)) {
                $lastExt = $i;
            }
        }
        if ($lastExt < 0) {
            return;
        }
        $payloads = [];
        for ($i = 0; $i <= $lastExt; $i++) {
            if (!$this->filaTieneExtRealParaReubicacion($tabla[$i])) {
                continue;
            }
            $idCargoFila = (int) ($tabla[$i]['idCargo'] ?? 0);
            $nuevos = [];
            foreach ($tabla[$i]["aplicados"] ?? [] as $ap) {
                if ($this->aplicadoEsPagoLegitimoReubicable($ap)) {
                    if (
                        !empty($pagosListByIdPago)
                        && $this->pagoLegitimoDeclaraIdCargoEnCuotas($ap['idPago'] ?? null, $idCargoFila, $pagosListByIdPago)
                    ) {
                        $nuevos[] = $ap;
                    } else {
                        $payloads[] = $ap;
                    }
                } else {
                    $nuevos[] = $ap;
                }
            }
            $tabla[$i]["aplicados"] = $nuevos;
        }
        if (empty($payloads)) {
            return;
        }
        $j = $lastExt + 1;
        foreach ($payloads as $ap) {
            while ($j < count($tabla)) {
                if (($tabla[$j]["tipo"] ?? "") === "anticipo") {
                    $j++;
                    continue;
                }
                $montoCargoJ = (float) ($tabla[$j]["monto_cargo"] ?? 0);
                $aplicadoJ = $this->sumaAplicadosQueCuentanCapital($tabla[$j]);
                $deficitJ = round($montoCargoJ - $aplicadoJ, 2);
                if ($deficitJ > 0.009) {
                    break;
                }
                $j++;
            }
            if ($j >= count($tabla)) {
                $li = count($tabla) - 1;
                if ($li >= 0) {
                    $ap["fechaPago"] = $tabla[$li]["fecha"] ?? ($ap["fechaPago"] ?? null);
                    $tabla[$li]["aplicados"][] = $ap;
                }
                continue;
            }
            $ap["fechaPago"] = $tabla[$j]["fecha"] ?? ($ap["fechaPago"] ?? null);
            $tabla[$j]["aplicados"][] = $ap;
            $montoCargoJ = (float) ($tabla[$j]["monto_cargo"] ?? 0);
            $aplicadoJ = $this->sumaAplicadosQueCuentanCapital($tabla[$j]);
            $deficitJ = round($montoCargoJ - $aplicadoJ, 2);
            if ($deficitJ <= 0.009) {
                $j++;
            }
        }
    }

    private function recalcularTotalesTablaEstadoCuenta(array &$tabla): void {
        foreach ($tabla as &$fila) {
            $total = 0.0;
            foreach ($fila["aplicados"] ?? [] as $ap) {
                if (!empty($ap["cc_invalido"])) {
                    continue;
                }
                if (isset($ap["tipo"]) && $ap["tipo"] === "contracargo") {
                    continue;
                }
                if (!empty($ap["no_cuenta_para_total_cuota"])) {
                    continue;
                }
                $total += (float) ($ap["aplicado"] ?? 0);
            }
            $montoCargo = (float) ($fila["monto_cargo"] ?? 0);
            $fila["total_pagado"] = round($total, 2);
            $fila["pendiente"] = round(max($montoCargo - $total, 0), 2);
            $fila["excedente"] = round(max($total - $montoCargo, 0), 2);
        }
        unset($fila);
    }

    /**
     * Total pagado coherente con la tabla del estado de cuenta: suma de total_pagado por fila.
     * Incluye cuotas (y parciales), anticipos y sobrantes ya aplicados a cargos; excluye líneas que
     * no cuentan para cuota (GC puro informativo, contracargo, etc.) vía no_cuenta_para_total_cuota.
     * Más fiel que montoOtorgado − saldoTotalVigente de la API cuando el desglose en pantalla importa.
     */
    private function calcularSaldoTotalPagadoDesdeTabla(array $tabla): float {
        $sum = 0.0;
        foreach ($tabla as $fila) {
            $sum += (float) ($fila['total_pagado'] ?? 0);
        }
        return round($sum, 2);
    }

    /**
     * Orden dentro de aplicados legítimos: primero sobrantes (remanente interno), luego pago principal, al final contracargo; cc_invalido primero.
     */
    private function tierOrdenAplicadoLegitimo(array $ap): int {
        if (!empty($ap["cc_invalido"])) {
            return 0;
        }
        if (isset($ap["tipo"]) && $ap["tipo"] === "contracargo") {
            return 3;
        }
        if (!empty($ap["es_sobrante"])) {
            return 1;
        }
        return 2;
    }

    /**
     * Timestamp para ordenar fechas de registro/depósito (la API a veces manda d/m/Y y strtotime() falla o la interpreta mal).
     */
    private function timestampOrdenFecha($fr): int {
        if ($fr === null || $fr === "") {
            return 0;
        }
        $s = trim((string) $fr);
        $t = strtotime($s);
        if ($t !== false) {
            return $t;
        }
        $dt = \DateTimeImmutable::createFromFormat("d/m/Y", $s);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt->getTimestamp();
        }
        $dt2 = \DateTimeImmutable::createFromFormat("Y-m-d", $s);
        if ($dt2 instanceof \DateTimeImmutable) {
            return $dt2->getTimestamp();
        }
        $dt3 = \DateTimeImmutable::createFromFormat("d-m-Y", $s);
        if ($dt3 instanceof \DateTimeImmutable) {
            return $dt3->getTimestamp();
        }
        return 0;
    }

    private function fechaOrdenAplicado(array $ap): string {
        return isset($ap["_sortDate"]) ? (string) $ap["_sortDate"] : (string) ($ap["fechaRegistro"] ?? "9999-99-99");
    }

    /** Día calendario (Y-m-d) para intercalar cargos con pagos en la misma cuota. */
    private function diaCalendarioOrdenAplicado(array $ap): string {
        $ts = $this->timestampOrdenFecha($this->fechaOrdenAplicado($ap));
        if ($ts <= 0) {
            return "";
        }
        return date("Y-m-d", $ts);
    }

    /**
     * Fecha de la NOTA de cargo (movimiento) para intercalar en la línea de tiempo. No usar _sortDate del pago asociado:
     * si se usa la fecha del depósito, varias notas quedan “ancladas” al último pago de la cuota y salen todos los pagos primero.
     */
    private function timestampNotaCargoParaIntercalacion(array $c): int {
        return $this->timestampOrdenFecha((string) ($c["fechaRegistro"] ?? ""));
    }

    /**
     * Orden dentro de la misma cuota (aplicados legítimos):
     *
     * PREFIJO — sobrantes de cuotas anteriores (es_sobrante=true), ordenados por fecha.
     *           Los cc_invalido NO van al prefijo: quedan en el cuerpo para que la nota
     *           quede pegada a ellos usando la búsqueda por idPago.
     *
     * CUERPO — cc_invalido + pagos regulares, ordenados solo por fecha.
     *
     * NOTAS  — filas tipo=contracargo, ordenadas por fecha del movimiento de la nota.
     *          Para cada nota con id_pago_asociado=X, se sacan del cuerpo todos los pagos
     *          anteriores hasta encontrar el primero con idPago=X; la nota se coloca justo
     *          después de él. Si no hay match por idPago, la nota va al final del cuerpo.
     *
     * Resultado: prefijo + merge(cuerpo, notas)
     */
    private function intercalarLineasCargoCreditoEnAplicadosLegitimos(array $legitimos): array {
        $cargos = [];
        $otros  = [];
        foreach ($legitimos as $ap) {
            if (($ap["tipo"] ?? "") === "contracargo") {
                $cargos[] = $ap;
            } else {
                $otros[] = $ap;
            }
        }

        $cmpFecha = function ($a, $b) {
            $fa = $this->timestampOrdenFecha($this->fechaOrdenAplicado($a));
            $fb = $this->timestampOrdenFecha($this->fechaOrdenAplicado($b));
            if ($fa !== $fb) {
                return $fa <=> $fb;
            }
            $ida = (string) ($a["idPago"] ?? "");
            $idb = (string) ($b["idPago"] ?? "");
            if ($ida !== $idb) {
                return strcmp($ida, $idb);
            }
            $aa = (float) ($a["aplicado"] ?? 0);
            $ab = (float) ($b["aplicado"] ?? 0);
            return $ab <=> $aa;
        };

        if (count($cargos) === 0) {
            usort($otros, $cmpFecha);
            $prefijo = [];
            $cuerpo  = [];
            foreach ($otros as $ap) {
                if (!empty($ap["es_sobrante"])) {
                    $prefijo[] = $ap;
                } else {
                    $cuerpo[] = $ap;
                }
            }
            return array_merge($prefijo, $cuerpo);
        }

        $prefijo = [];
        $cuerpo  = [];
        foreach ($otros as $ap) {
            if (!empty($ap["es_sobrante"])) {
                $prefijo[] = $ap;
            } else {
                $cuerpo[] = $ap;
            }
        }
        usort($prefijo, $cmpFecha);
        usort($cuerpo,  $cmpFecha);
        usort($cargos, function ($a, $b) {
            return $this->timestampNotaCargoParaIntercalacion($a)
                <=> $this->timestampNotaCargoParaIntercalacion($b);
        });

        $cola   = array_values($cuerpo);
        $merged = [];
        foreach ($cargos as $c) {
            $idPC = (string) ($c["id_pago_asociado"] ?? "");
            if ($idPC === "") {
                $tN = $this->timestampNotaCargoParaIntercalacion($c);
                while (count($cola) > 0) {
                    $tP = $this->timestampOrdenFecha($this->fechaOrdenAplicado($cola[0]));
                    if ($tN > 0 && $tP > 0 && $tP < $tN) {
                        $merged[] = array_shift($cola);
                        continue;
                    }
                    break;
                }
                $merged[] = $c;
                continue;
            }
            $colocado = false;
            while (count($cola) > 0) {
                $idPP = (string) ($cola[0]["idPago"] ?? "");
                if ($idPP === $idPC) {
                    $merged[] = array_shift($cola);
                    $merged[] = $c;
                    $colocado = true;
                    break;
                }
                $merged[] = array_shift($cola);
            }
            if (!$colocado) {
                $merged[] = $c;
            }
        }
        while (count($cola) > 0) {
            $merged[] = array_shift($cola);
        }
        return array_merge($prefijo, $merged);
    }

    /**
     * Orden dentro de la misma cuota:
     *   1. extemporaneo_deposito / extemporaneos_resumen  (depósitos extemporáneos reales — van primero)
     *   2. Pagos y sobrantes legítimos (con contracargos intercalados)
     *   3. gasto_cobranza_deposito  (línea informativa de cargo externo — va al final, como los GC normales)
     */
    private function ordenarAplicadosExtemporaneosAntesDeLegitimos(array &$tabla): void {
        foreach ($tabla as &$fila) {
            $aps = $fila["aplicados"] ?? [];
            if (count($aps) < 1) {
                continue;
            }
            $extDepositos = [];
            $gastosCobranza = [];
            $legitimos = [];
            foreach ($aps as $ap) {
                $t = $ap["tipo"] ?? "";
                if ($t === "extemporaneo_deposito" || $t === "extemporaneos_resumen") {
                    $extDepositos[] = $ap;
                } elseif ($t === "gasto_cobranza_deposito") {
                    $gastosCobranza[] = $ap;
                } else {
                    $legitimos[] = $ap;
                }
            }
            $legitimos = $this->intercalarLineasCargoCreditoEnAplicadosLegitimos($legitimos);
            $sortFnInf = function ($a, $b) {
                $fa = isset($a["_sortDate"]) ? $a["_sortDate"] : ($a["fechaRegistro"] ?? "9999-99-99");
                $fb = isset($b["_sortDate"]) ? $b["_sortDate"] : ($b["fechaRegistro"] ?? "9999-99-99");
                return strtotime((string) $fa) <=> strtotime((string) $fb);
            };
            usort($extDepositos, $sortFnInf);
            usort($gastosCobranza, $sortFnInf);
            $fila["aplicados"] = array_merge($extDepositos, $legitimos, $gastosCobranza);
        }
        unset($fila);
    }

    // ---------------- EXTRAER NUMERO DE CUOTA ----------------
    private function extraer_numero_cuota($concepto) {
        if (!$concepto) return null;
        if (preg_match('/(\d+)/', $concepto, $m)) {
            return intval($m[1]);
        }
        return null;
    }

    /**
     * S2 devolvió estadoCuenta sin idCredito usable: misma UX que fallo en validarCredito (vuelta a consulta + Swal).
     */
    private function renderConsultaAlertaSinIdCreditoS2(string $scriptTemplate, bool $tienePermisoFechaCorte): void
    {
        EstadoCuentaTimingLog::finish('sin_id_credito_s2');
        self::set('titulo', 'Estados de Cuenta');
        $scriptConsulta = str_replace('TienePermisoRegistrarDocumentos_PLACEHOLDER', json_encode(false), $scriptTemplate);
        $scriptConsulta = str_replace('TienePermisoFechaCorte_PLACEHOLDER', json_encode($tienePermisoFechaCorte), $scriptConsulta);
        self::set('script', $scriptConsulta);
        self::set('alertaBusqueda', [
            'icon' => 'error',
            'title' => 'ID de crédito incorrecto',
            'html' => "<div style='text-align: center; padding: 10px;'><p style='font-size: 16px; margin-bottom: 15px; color: #333;'><strong>El ID de crédito ingresado no existe o no es válido.</strong></p><p style='font-size: 14px; color: #666;'>Por favor verifícalo y vuelve a intentar.</p></div>",
            'confirmButtonText' => 'Entendido',
            'confirmButtonColor' => '#dc3545',
            'width' => '500px',
        ]);
        self::render('__SPARTA_SECRET_REDACTED___consulta');
    }

    public function Consulta()
    {
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $modulosActuales = $idUsuario ? LoginDAO::getModulosUsuario($idUsuario) : [];
        $modsSesionConsulta = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        // Mismo permiso especial que el antiguo acceso «Never paid» (módulo 29).
        $tienePermisoRastreoNeverPaid = ($idUsuario === 1) || in_array(29, $modsSesionConsulta, true);
        self::set('tienePermisoRastreoNeverPaid', $tienePermisoRastreoNeverPaid);
        // Permiso especial Aclaraciones GC (modulos_web id 30, categoría Permisos especiales).
        $tienePermisoAclaracionesGc = ($idUsuario === 1) || in_array(30, $modsSesionConsulta, true);
        self::set('tienePermisoAclaracionesGc', $tienePermisoAclaracionesGc);
        self::set('tienePermisoDictaminarLlamada', $this->tienePermisoModuloSesion(self::EC_PERMISO_DICTAMINAR_LLAMADA));
        self::set('tienePermisoCondonarGastosCobranza', $this->tienePermisoModuloSesion(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA));
        self::set('tienePermisoNotasCliente', $this->tienePermisoModuloSesion(self::EC_PERMISO_NOTAS_CLIENTE));
        $tienePermisoRegistrarDocumentos = in_array(21, $modulosActuales);
        $tienePermisoFechaCorte = in_array(23, $modulosActuales);
        // --- JS COMPLETO EN EL CONTROLADOR ---
        $script = <<<JS
      <script>
        var tienePermisoRegistrarDocumentos = TienePermisoRegistrarDocumentos_PLACEHOLDER;
        var tienePermisoFechaCorte = TienePermisoFechaCorte_PLACEHOLDER;
        document.addEventListener("DOMContentLoaded", () => {

            // Mostrar calendario de fecha de corte si tiene permiso
            if (tienePermisoFechaCorte) {
                const divFechaCorte = document.getElementById('divFechaCorte');
                if (divFechaCorte) divFechaCorte.style.display = 'block';
            }

            // Cambiar entre ID y Nombre
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    const divNombre = document.getElementById('divNombre');
                    const divID = document.getElementById('divID');

                    // Verificar que los elementos existan antes de acceder a sus propiedades
                    if (divNombre) {
                        divNombre.style.display = modo === 'nombre' ? 'block' : 'none';
                    }
                    if (divID) {
                        divID.style.display = modo === 'id' ? 'block' : 'none';
                    }
            }

            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );

            // Solo agregar el event listener si el modal existe
            const modalDirecciones = document.getElementById('modalDirecciones');
            if (modalDirecciones) {
                modalDirecciones.addEventListener('shown.bs.modal', function() {
                    // No ejecutar actualizarInputs cuando se abre el modal de direcciones
                    // ya que ese modal no tiene los elementos divNombre y divID
                });
            }


            // Botón limpiar filtros
            const btnResetFiltrosEc = document.getElementById("btnResetFiltros");
            if (btnResetFiltrosEc) {
                btnResetFiltrosEc.addEventListener("click", () => {
                    const elId = document.getElementById("idCredito");
                    const elNombre = document.getElementById("nombre");
                    const fechaCorteInput = document.getElementById("fechaCorte");
                    const modoID = document.getElementById("modoID");
                    if (elId) elId.value = "";
                    if (elNombre) elNombre.value = "";
                    if (fechaCorteInput) fechaCorteInput.value = "";
                    if (modoID) modoID.checked = true;
                    actualizarInputs();
                });
            }

            // Validación antes de enviar
            const formBusquedaEc = document.getElementById("formBusqueda");
            if (formBusquedaEc) formBusquedaEc.addEventListener("submit", async e => {
                e.preventDefault();

                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                const idCredito       = (document.getElementById("idCredito")?.value ?? "").trim();
                const nombre          = (document.getElementById("nombre")?.value ?? "").trim();
                const idCreditoLista  = (document.getElementById("idCreditoLista")?.value ?? "").trim();

                // =========================
                // MODO ID
                // =========================
                if (modo === "id") {

                    if (idCredito === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el ID de crédito",
                            text: "Por favor ingresa el ID del crédito."
                        });
                    }

                    // Limpieza defensiva
                    const listaLimp = document.getElementById("idCreditoLista");
                    const nomLimp = document.getElementById("nombre");
                    if (listaLimp) listaLimp.value = "";
                    if (nomLimp) nomLimp.value = "";
                }

                // =========================
                // MODO NOMBRE
                // =========================
                if (modo === "nombre") {

                    if (nombre === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el nombre",
                            text: "Escribe y selecciona un cliente de la lista."
                        });
                    }

                    if (idCreditoLista === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Cliente no seleccionado",
                            text: "Debes seleccionar un cliente del listado, no solo escribirlo."
                        });
                    }

                    // Limpieza defensiva
                    const idLimp = document.getElementById("idCredito");
                    if (idLimp) idLimp.value = "";
                }

                // =========================
                // LOADING
                // =========================
                Swal.fire({
                    title: "Validando ID de crédito...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                // =========================
                // VALIDAR ID ANTES DE ENVIAR
                // =========================
                try {
                    const formData = new FormData();
                    if (modo === "id") {
                        formData.append("idCredito", idCredito);
                    } else {
                        formData.append("nombre", nombre);
                        formData.append("idCreditoLista", idCreditoLista);
                    }
                    formData.append("modoBusqueda", modo);

                    const response = await fetch("/estadocuenta/validarcredito", {
                        method: "POST",
                        body: formData
                    });

                    const ct = (response.headers.get("content-type") || "").toLowerCase();
                    if (!response.ok || ct.indexOf("application/json") === -1) {
                        throw new Error("HTTP " + response.status);
                    }
                    const result = await response.json();

                    if (!result.success) {
                        Swal.close();

                        if (result.tipo === "credito_guatemala") {
                            return Swal.fire({
                                title: "Crédito de Guatemala",
                                html: "<div style='text-align:center;'><div style='margin-bottom:12px;'><span class='fi fi-gt fis' style='font-size:2.8rem;'></span></div><p style='margin:0; font-size:14px; color:#666;'>El crédito ingresado pertenece a Guatemala. Consulta este ID en Estado de Cuenta Guatemala.</p></div>",
                                confirmButtonText: "Entendido"
                            });
                        }

                        if (result.tipo === "error_db") {
                            return Swal.fire({
                                icon: "error",
                                title: "Base de datos no disponible",
                                text: result.mensaje || "No se pudo conectar al servidor de datos para validar el crédito."
                            });
                        }

                        return Swal.fire({
                            icon: "error",
                            title: "ID de crédito incorrecto",
                            html: "<div style='text-align: center; padding: 10px;'><p style='font-size: 16px; margin-bottom: 15px; color: #333;'><strong>El ID de crédito ingresado no existe o no es válido.</strong></p><p style='font-size: 14px; color: #666;'>Por favor verifícalo y vuelve a intentar.</p></div>",
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#dc3545",
                            width: "500px",
                            buttonsStyling: true,
                            customClass: {
                                popup: 'animated bounceIn',
                                title: 'text-danger',
                                confirmButton: 'btn-elegant-error'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(() => {
                            // Agregar estilos personalizados al botón después de que se renderice
                            setTimeout(() => {
                                const confirmBtn = document.querySelector('.swal2-confirm');
                                if (confirmBtn) {
                                    confirmBtn.style.cssText = `
                                        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
                                        border: none !important;
                                        border-radius: 8px !important;
                                        padding: 12px 30px !important;
                                        font-size: 15px !important;
                                        font-weight: 600 !important;
                                        letter-spacing: 0.5px !important;
                                        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
                                        transition: all 0.3s ease !important;
                                        text-transform: uppercase !important;
                                    `;
                                    confirmBtn.addEventListener('mouseenter', function() {
                                        this.style.transform = 'translateY(-2px)';
                                        this.style.boxShadow = '0 6px 20px rgba(220, 53, 69, 0.4)';
                                    });
                                    confirmBtn.addEventListener('mouseleave', function() {
                                        this.style.transform = 'translateY(0)';
                                        this.style.boxShadow = '0 4px 15px rgba(220, 53, 69, 0.3)';
                                    });
                                }
                            }, 100);
                        });
                    }

                    // Si la validación es exitosa, mostrar loading y enviar el formulario
                    Swal.fire({
                        title: "Procesando solicitud...",
                        text: "Espere un momento por favor.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Enviar el formulario usando submit() nativo (no dispara el evento submit)
                    const form = e.target;
                    form.submit();

                } catch (error) {
                    Swal.close();
                    console.error("Error al validar:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo validar el ID de crédito. Por favor intenta nuevamente."
                    });
                }
            });

        });

            // ── Auto-fill desde módulo Adjudicación ───────────────────────────
            // Si se llegó desde Adjudicación con el botón "Estado de cuenta",
            // el crédito fue guardado en sessionStorage con la clave adj_credito_ec.
            (function() {
                const adjCreditoEc = sessionStorage.getItem('adj_credito_ec');
                if (!adjCreditoEc) return;
                sessionStorage.removeItem('adj_credito_ec');

                const elId  = document.getElementById('idCredito');
                const modoID = document.getElementById('modoID');
                if (!elId || !modoID) return;

                modoID.checked = true;
                // Asegurar que el panel del ID esté visible
                const divID = document.getElementById('divID');
                if (divID) divID.style.display = 'block';
                const divNombre = document.getElementById('divNombre');
                if (divNombre) divNombre.style.display = 'none';

                elId.value = adjCreditoEc;

                // Lanzar la búsqueda automáticamente
                const form = document.getElementById('formBusqueda');
                if (form) form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            })();


        </script>
JS;

        // Script de error - solo se ejecuta cuando hay un error
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;
            $idCreditoLista = $_POST['idCreditoLista'] ?? null;
            $fechaCortePost = isset($_POST['fechaCorte']) ? trim((string) $_POST['fechaCorte']) : null;
            if ($fechaCortePost === '') $fechaCortePost = null;
            // Si tiene permiso de fecha de corte personalizada y envió una fecha válida (pasada o hoy), usarla
            if ($tienePermisoFechaCorte && $fechaCortePost && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCortePost) && $fechaCortePost <= date('Y-m-d')) {
                $fechaHoy = $fechaCortePost;
            } else {
                $fechaHoy = date('Y-m-d');
            }
            error_log('[EstadoCuenta Consulta] POST fechaCorte=' . ($fechaCortePost ?? 'null') . ', permisoFechaCorte=' . ($tienePermisoFechaCorte ? '1' : '0') . ', fechaUsada=' . $fechaHoy);

            // Validación cruzada MX -> GT: si no existe en México, verificar Guatemala para mostrar alerta amigable.
            $idConsultado = ($nombre != null && $idCreditoLista != null) ? $idCreditoLista : $idCredito;
            EstadoCuentaTimingLog::start((string) ($idConsultado ?? ''));
            if (!empty($idConsultado)) {
                $probeMx = EmpresasDAO::creditoTieneOfertaEnMx($idConsultado);
                $tieneOfertaMx = !empty($probeMx['success'])
                    && !empty($probeMx['datos'])
                    && is_array($probeMx['datos'])
                    && count($probeMx['datos']) > 0;
                if (!$tieneOfertaMx) {
                    $datosGuatPrevios = EmpresasDAO::getGuatemalaEstadoCuenta($idConsultado);
                    if (!empty($datosGuatPrevios['datos'])) {
                        self::set("titulo", "Estados de Cuenta");
                        $scriptConsulta = str_replace('TienePermisoRegistrarDocumentos_PLACEHOLDER', json_encode(false), $script);
                        $scriptConsulta = str_replace('TienePermisoFechaCorte_PLACEHOLDER', json_encode($tienePermisoFechaCorte), $scriptConsulta);
                        // Inyectar idCredito para cruce async en el script
                        $script = str_replace(
                            'document.addEventListener("DOMContentLoaded", () => {',
                            'var _idCreditoParaCruce = ' . (int)$idConsultado . ';
                            var _ecFechaCorteCruce = ' . json_encode($fechaHoy) . ';
                            document.addEventListener("DOMContentLoaded", () => {
                                if (_idCreditoParaCruce > 0) {
                                    setTimeout(function () {
                                        fetch("/EstadoCuenta/procesarCruceGastos", {
                                            method: "POST",
                                            headers: { "Content-Type": "application/json" },
                                            body: JSON.stringify({ idCredito: _idCreditoParaCruce, fechaCorte: _ecFechaCorteCruce }),
                                            credentials: "same-origin",
                                            keepalive: true
                                        }).catch(function () {});
                                    }, 0);
                                }',
                                                        $script
                                                    );
                        self::set("script", $scriptConsulta);
                        self::set("alertaBusqueda", [
                            'title' => 'Crédito de Guatemala',
                            'html' => "<div style='text-align:center;'><div style='margin-bottom:12px;'><span class='fi fi-gt fis' style='font-size:2.8rem;'></span></div><p style='margin:0; font-size:14px; color:#666;'>El crédito ingresado pertenece a Guatemala. Consulta este ID en Estado de Cuenta Guatemala.</p></div>",
                            'confirmButtonText' => 'Entendido'
                        ]);
                        EstadoCuentaTimingLog::finish('out_guatemala');
                        return self::render("__SPARTA_SECRET_REDACTED___consulta");
                    }
                }
            }
            EstadoCuentaTimingLog::mark('precheck_mx_gt');

            $idCreditoFlujo = ($nombre != null && $idCreditoLista != null)
                ? (int) $idCreditoLista
                : (int) $idCredito;
            $resultado = $this->api___SPARTA_SECRET_REDACTED__($idCreditoFlujo, $fechaHoy);
            EstadoCuentaTimingLog::mark('api___SPARTA_SECRET_REDACTED__');
            // Direcciones, referencias completas y conteo de notas: se cargan async (ver getComplementosEstadoCuenta).
            $respDAO = ['success' => true, 'datos' => []];
            $notas = ['success' => true, 'datos' => [['num' => '…']]];
            $referencias = ['success' => true, 'datos' => []];
            EstadoCuentaTimingLog::mark('complementos_deferidos');

            // Registrar en auditoría: usuario (email), crédito, fecha de corte, éxito/error
            $usuarioEmail = (string) ($_SESSION['usuario'] ?? '');
            $_auditData = [
                'usuario'     => $usuarioEmail,
                'idConsultado'=> $idConsultado,
                'fechaHoy'    => $fechaHoy,
                'ok'          => !empty($resultado['ok']) ? 1 : 0,
                'error'       => $resultado['error'] ?? null,
            ];
            register_shutdown_function(function() use ($_auditData) {
                EstadoCuentaDAO::registrarAuditoria(
                    $_auditData['usuario'],
                    $_auditData['idConsultado'],
                    $_auditData['fechaHoy'],
                    $_auditData['ok'],
                    $_auditData['error']
                );
            });
            EstadoCuentaTimingLog::mark('auditoria');

            // variables para guardar por bloques los arrreglos
            // ---------------------------------------------------------------
            // 1. Extraemos las secciones del JSON original
            // ---------------------------------------------------------------
            $cliente = $resultado['data']['datosCliente'];
            $estadoCuenta = $resultado['data'];
            $otrosDatos = $resultado['data']['datosSaldos'];
            $otrosDatos['saldoTotalPendienteGastoCobranza'] = EstadoCuentaDAO::getTotalGastosCobranzaPendiente(
                (int) ($estadoCuenta['idCredito'] ?? 0)
            );

            // ---------------------------------------------------------------
            // 2. Crear estructura base para cada cuota con la información del cargo
            // ---------------------------------------------------------------
            $cargos = $estadoCuenta["datosCargos"] ?? [];
            if (!is_array($cargos)) $cargos = [];

            $pagos = $estadoCuenta["datosPagos"] ?? [];
            if (!is_array($pagos)) $pagos = [];

            $listaNotasCargosParaSoloGc = $estadoCuenta["datosNotasCargos"] ?? [];
            if (!is_array($listaNotasCargosParaSoloGc)) {
                $listaNotasCargosParaSoloGc = [];
            }

            // Orden estable por idCargo (necesario para expandir lista tras ANTICIPO)
            usort($cargos, function ($a, $b) {
                return $this->safe_int($a["idCargo"] ?? 0) <=> $this->safe_int($b["idCargo"] ?? 0);
            });

            // numeroCuotaSemanal en la API = idCargo(s) que el depósito liquida (no el número de cuota del concepto).
            $maxIdCargoEnCargos = 0;
            foreach ($cargos as $c) {
                $idc = $this->safe_int($c["idCargo"] ?? 0, 0);
                if ($idc > $maxIdCargoEnCargos) {
                    $maxIdCargoEnCargos = $idc;
                }
            }

            $usarMotorV2 = $this->shouldUseEstadoCuentaEngineV2();
            $shadowV2 = $this->shouldShadowCompareEstadoCuentaEngineV2();
            $this->logEstadoCuentaEngineModo((int) $idConsultado, $usarMotorV2, $shadowV2);
            // FIFO ext→GC: necesario para v2 en producción y para el brazo v2 del shadow.
            $necesitaMapaGc = $usarMotorV2 || $shadowV2;
            $gcPorIdPago = $necesitaMapaGc
                ? $this->conciliarExtPagosConNotasGC($pagos, $listaNotasCargosParaSoloGc)
                : [];
            $pagos_list = $this->prepararPagosListEstadoCuenta(
                $pagos,
                $cargos,
                $listaNotasCargosParaSoloGc,
                $maxIdCargoEnCargos,
                $usarMotorV2 ? $gcPorIdPago : [],
                !$usarMotorV2
            );

            if ($shadowV2) {
                $pagosLegacy = $this->prepararPagosListEstadoCuenta(
                    $pagos,
                    $cargos,
                    $listaNotasCargosParaSoloGc,
                    $maxIdCargoEnCargos,
                    [],
                    true
                );
                $pagosV2 = $this->prepararPagosListEstadoCuenta(
                    $pagos,
                    $cargos,
                    $listaNotasCargosParaSoloGc,
                    $maxIdCargoEnCargos,
                    $gcPorIdPago,
                    false
                );
                $idCreditoDebug = (int) $idConsultado;
                $this->registrarDiferenciasPagosListMotores($idCreditoDebug, $pagosLegacy, $pagosV2);
            }
            EstadoCuentaTimingLog::mark('preparar_pagos_list');

            $tabla = [];

            // Plazo total "DE N" en CUOTA SEMANAL (badge Recalculada cuando baja tras anticipo).
            $prevPlazoTotalSemanal = null;

            // -------------------------
            // PROCESAR CARGOS (una fila por idCargo; pagos ↔ idCargo vía numeroCuotaSemanal)
            // -------------------------
            foreach ($cargos as $cargo_idx => $cargo) {

                $concepto = $cargo["concepto"] ?? "";
                $idCargoCargo = $this->safe_int($cargo["idCargo"] ?? 0);
                $esAnticipo = (mb_strtoupper(trim($concepto)) === 'ANTICIPO A CAPITAL');
                $cuota_num_display = $this->extraer_numero_cuota($concepto);

                $recalculada = false;
                if (strpos(mb_strtoupper($concepto), 'CUOTA SEMANAL') !== false) {
                    if (preg_match('/\bDE\s+(\d+)\s*$/i', $concepto, $mPlazo)) {
                        $plazoTotal = (int) $mPlazo[1];
                        if ($prevPlazoTotalSemanal !== null && $plazoTotal < $prevPlazoTotalSemanal) {
                            $recalculada = true;
                        }
                        $prevPlazoTotalSemanal = $plazoTotal;
                    }
                }

                $monto_cargo   = $this->safe_float($cargo["monto"] ?? 0);
                // Monto de la siguiente cuota (si existe): para priorizar liquidarla antes de cobrar Gasto de Cobranza.
                $siguiente_cargo = $cargos[$cargo_idx + 1] ?? null;
                $monto_siguiente_cuota = $siguiente_cargo ? $this->safe_float($siguiente_cargo["monto"] ?? 0) : 0;
                $capital       = $this->safe_float($cargo["capital"] ?? 0);
                $interes       = $this->safe_float($cargo["interes"] ?? 0);
                $seguro_total  = $this->safe_float($cargo["seguroBienes"] ?? 0)
                    + $this->safe_float($cargo["seguroVida"] ?? 0)
                    + $this->safe_float($cargo["seguroDesempleo"] ?? 0);

                $fecha_venc = $cargo["fechaVencimiento"] ?? ($cargo["fechaVenc"] ?? null);

                $monto_restante_cargo = $monto_cargo;
                $aplicados = [];

                // -------------------------
                // Pagos cuyo numeroCuotaSemanal incluye este idCargo.
                // Orden: primero los que NO incluyen el idCargo siguiente (más sobrante para el siguiente cargo).
                // -------------------------
                $siguiente_idCargo = $siguiente_cargo ? $this->safe_int($siguiente_cargo["idCargo"] ?? 0) : 0;
                $indices_aplicables = [];
                foreach ($pagos_list as $idx => $p) {
                    if (in_array($idCargoCargo, $p["cuotas"] ?? [])) {
                        $indices_aplicables[] = $idx;
                    }
                }
                usort($indices_aplicables, function ($a, $b) use ($pagos_list, $siguiente_idCargo) {
                    if ($siguiente_idCargo <= 0) {
                        return 0;
                    }
                    $tiene_siguiente_a = in_array($siguiente_idCargo, $pagos_list[$a]["cuotas"] ?? []);
                    $tiene_siguiente_b = in_array($siguiente_idCargo, $pagos_list[$b]["cuotas"] ?? []);
                    return ($tiene_siguiente_a ? 1 : 0) <=> ($tiene_siguiente_b ? 1 : 0);
                });

                // -------------------------
                // APLICAR PAGOS A ESTE CARGO (en el orden calculado)
                // -------------------------
                foreach ($indices_aplicables as $idx) {
                    $pago = &$pagos_list[$idx];
                    if (!empty($pago["es_pago_solo_extemporaneo_inyectar"])) {
                        continue;
                    }

                    // --- Aplicar monto real (remaining) a la cuota primero ---
                    $aplico_remaining_esta_cuota = false;
                    if ($monto_restante_cargo > 0 && $pago["remaining"] > 0) {

                        $remaining_al_inicio = round($pago["remaining"], 2);
                        $ext_api = round($pago["extemporaneos"] ?? 0, 2);
                        $monto_real_pago = round(($pago["montoPagoOriginal"] ?? 0) - $ext_api, 2);
                        // Es sobrante solo cuando aplicamos el "resto" de un pago que ya se usó en parte en OTRA cuota (remaining < monto real). No cuando la diferencia con el original es solo extemporáneos/gasto cobranza.
                        $es_sobrante_remaining = ($remaining_al_inicio < $monto_real_pago);
                        // El sobrante pertenece a la lógica de cuotas semanales; no debe liquidar ANTICIPO A CAPITAL (sigue disponible para el siguiente cargo, p. ej. cuota semanal siguiente).
                        if ($esAnticipo && $es_sobrante_remaining) {
                            continue;
                        }

                        $aplicar = min($pago["remaining"], $monto_restante_cargo);
                        // Mostrar monto real del depósito solo cuando es la primera aplicación; si ya se usó en cuota anterior, mostrar el resto que llegó.
                        $monto_mostrar = $es_sobrante_remaining ? $remaining_al_inicio : round($pago["montoPagoOriginal"] ?? $pago["remaining"], 2);
                        $aplicado_total_pago = round(($pago["montoPagoOriginal"] ?? 0) - $ext_api, 2);
                        $aplicados[] = [
                            "idPago"            => $pago["idPago"],
                            "montoPago"        => $monto_mostrar,
                            "aplicado"         => round($aplicar, 2),
                            "aplicadoTotalPago" => $aplicado_total_pago,
                            "fechaRegistro"    => $pago["fechaRegistro"],
                            "fechaPago"        => $fecha_venc,
                            "diasMora"         => null,
                            "extemporaneos"    => $ext_api,
                            "es_sobrante"      => $es_sobrante_remaining
                        ];

                        $aplico_remaining_esta_cuota = true;
                        // restar de pago y cargo
                        $pago["remaining"]        = round($pago["remaining"] - $aplicar, 2);
                        $monto_restante_cargo     = round($monto_restante_cargo - $aplicar, 2);
                    }

                    // --- Actualizar remaining del pago. Los extemporáneos (gasto cobranza) NO se suman al sobrante: no van al crédito, solo se cobran. Solo arrastramos lo que sobra del monto real. ---
                    // Solo consumir extemporaneos cuando realmente se aplicó remaining en esta cuota;
                    // si la cuota ya estaba pagada y no aplicamos nada, los extemporaneos deben
                    // conservarse para la primera cuota donde sí aplique el pago.
                    $pago["remaining"] = round($pago["remaining"], 2);
                    if ($aplico_remaining_esta_cuota) {
                        $pago["extemporaneos"] = 0;
                        $pago["_extemporaneo_aplicado"] = true;
                    }
                }
                unset($pago);

                // -------------------------
                // CÁLCULOS FINALES
                // -------------------------
                $total_aplicado = round($monto_cargo - $monto_restante_cargo, 2);
                $pendiente      = round(max($monto_cargo - $total_aplicado, 0), 2);
                $excedente      = round(max($total_aplicado - $monto_cargo, 0), 2);

                $tabla[] = [
                    "cuota"         => $cuota_num_display,
                    "idCargo"       => $idCargoCargo,
                    "tipo"          => $esAnticipo ? "anticipo" : "cuota",
                    "recalculada"   => $recalculada,
                    "fecha"         => $fecha_venc,
                    "monto_cargo"   => round($monto_cargo, 2),
                    "capital"       => round($capital, 2),
                    "interes"       => round($interes, 2),
                    "seguro"        => round($seguro_total, 2),
                    "aplicados"     => $aplicados,
                    "total_pagado"  => $total_aplicado,
                    "pendiente"     => $pendiente,
                    "excedente"     => $excedente,
                    "raw_cargo"     => $cargo
                ];
            }

            // Notas de cargo por fecha: desde datosNotasCargos.
            // extemporáneos en datosPagos puede incluir gasto cobranza + contracargo; aquí se separa por concepto:
            // - GASTO(S) DE COBRANZA → solo acumulamos por fecha (para no descontar dos veces el CC del pool).
            // - CONTRACARGO / REEMBOLSO → flujo de emparejamiento con pagos por fechaMovimiento/fechaVencimiento.
            // - NCCC (nota de cargo compuesta de crédito: capital+interés+comisión+resguardo) → misma suma por fecha que contracargo.
            $notasCargoPorFecha = [];
            $gastoCobranzaPorFecha = [];
            $esReembolsoPorFecha = [];
            $tuvoContracargoTextoPorFecha = [];
            $hayNotasCargos = false;
            $listaNotasCargos = $estadoCuenta['datosNotasCargos'] ?? [];

            // Cruce diferido — se ejecuta async desde el frontend tras renderizar
            self::set("resultadoCruce", null);
            self::set("idCreditoParaCruce", (int) $idConsultado);

            // Diferido — se carga async cuando el usuario abre el modal
            self::set('gastosCobranzaPreload',  []);
            self::set('historialGastosPreload', []);

            if (is_array($listaNotasCargos) && count($listaNotasCargos) > 0) {
                foreach ($listaNotasCargos as $nota) {
                    $concepto = (string) ($nota['concepto'] ?? '');
                    $conceptoUpper = mb_strtoupper($concepto);
                    if (strpos($conceptoUpper, 'EXTEMPORANEO') !== false || strpos($conceptoUpper, 'EXTEMPORÁNEO') !== false) {
                        continue;
                    }
                    $fechaNota = $nota['fechaMovimiento'] ?? $nota['fechaVencimiento'] ?? null;
                    if ($fechaNota === null || $fechaNota === '') {
                        continue;
                    }
                    $fechaNorm = date('Y-m-d', strtotime($fechaNota));
                    $montoNota = $this->safe_float($nota['monto'] ?? 0, 0);

                    $esGastoCobranza = strpos($conceptoUpper, 'GASTO') !== false && strpos($conceptoUpper, 'COBRANZA') !== false;
                    if ($esGastoCobranza) {
                        if (!isset($gastoCobranzaPorFecha[$fechaNorm])) {
                            $gastoCobranzaPorFecha[$fechaNorm] = 0.0;
                        }
                        $gastoCobranzaPorFecha[$fechaNorm] += $montoNota;
                        continue;
                    }

                    $esContracargo = strpos($conceptoUpper, 'CONTRACARGO') !== false;
                    $esReembolso = strpos($conceptoUpper, 'REEMBOLSO') !== false;
                    $esNccc = $this->esNotaCargoCompuestaCredito($conceptoUpper);
                    if (!$esContracargo && !$esReembolso && !$esNccc) {
                        continue;
                    }

                    if (!isset($notasCargoPorFecha[$fechaNorm])) {
                        $notasCargoPorFecha[$fechaNorm] = 0.0;
                    }
                    $notasCargoPorFecha[$fechaNorm] += $montoNota;
                    if ($esReembolso) {
                        $esReembolsoPorFecha[$fechaNorm] = true;
                    }
                    if ($esContracargo) {
                        $tuvoContracargoTextoPorFecha[$fechaNorm] = true;
                    }
                }
                foreach (array_keys($notasCargoPorFecha) as $k) {
                    $notasCargoPorFecha[$k] = round($notasCargoPorFecha[$k], 2);
                }
                foreach (array_keys($gastoCobranzaPorFecha) as $k) {
                    $gastoCobranzaPorFecha[$k] = round($gastoCobranzaPorFecha[$k], 2);
                }
                $hayNotasCargos = array_sum($notasCargoPorFecha) > 0;
            }
            $tipoDisplayCargoPorFecha = [];
            foreach (array_keys($notasCargoPorFecha) as $fn) {
                if (!empty($esReembolsoPorFecha[$fn])) {
                    $tipoDisplayCargoPorFecha[$fn] = 'reembolso';
                } elseif (!empty($tuvoContracargoTextoPorFecha[$fn])) {
                    $tipoDisplayCargoPorFecha[$fn] = 'contracargo';
                } else {
                    $tipoDisplayCargoPorFecha[$fn] = 'nccc';
                }
            }
            // ═══════════════════════════════════════════════════════════════════════════════
            // BLOQUE DE CONTRACARGOS — NO MODIFICAR SIN REVISIÓN EXHAUSTIVA
            // Lachy dedicó mucho esfuerzo a que este flujo funcione correctamente en todos los casos.
            // Cualquier cambio puede romper emparejamiento, sobrantes y fechas de cierre.
            // ═══════════════════════════════════════════════════════════════════════════════
            // Post-process contracargos: reconstruir flujo de pagos.
            // Las notas de cargo vienen desglosadas (capital, interés, comisión, resguardo = NCCC)
            // o como CONTRACARGO/REEMBOLSO; por fecha suman UN monto. Se usa notasCargoPorFecha (sumado)
            // para emparejar cada fecha con el pago revertido, y se redistribuye el dinero
            // legítimo desde la cuota más antigua con déficit.
            if ($hayNotasCargos && !empty($tabla)) {
                // Mapeo idCargo → índice en $tabla (numeroCuotaSemanal = idCargo)
                $cuotaNumToIdx = [];
                for ($ci = 0; $ci < count($tabla); $ci++) {
                    $idCg = $tabla[$ci]['idCargo'] ?? null;
                    if ($idCg !== null && $idCg > 0) {
                        $cuotaNumToIdx[$idCg] = $ci;
                    }
                }

                // Lookup rápido de pagos_list por idPago
                $plById = [];
                foreach ($pagos_list as $pl) {
                    $idP = $pl['idPago'] ?? null;
                    if ($idP) $plById[$idP] = $pl;
                }

                // Último pago desde la tabla (para caso especial: cargo sin pago posterior)
                $ultimoPagoDesdeTabla = null;
                $maxFechaTs = null;
                for ($ci = 0; $ci < count($tabla); $ci++) {
                    foreach ($tabla[$ci]['aplicados'] as $ap) {
                        $idP = $ap['idPago'] ?? null;
                        if (!$idP) continue;
                        $fr = $ap['fechaRegistro'] ?? null;
                        if (!$fr) continue;
                        $ts = strtotime($fr);
                        if ($ts === false) continue;
                        if ($maxFechaTs === null || $ts > $maxFechaTs) {
                            $maxFechaTs = $ts;
                            $ultimoPagoDesdeTabla = [
                                'fecha'    => date('Y-m-d', $ts),
                                'idPago'   => $idP,
                                'cuotaIdx' => $ci,
                            ];
                        }
                    }
                }

                // ── PASO 1: Recopilar TODOS los pagos (aplicados + invisibles de la API) ──
                $todosLosPagos = [];
                for ($ci = 0; $ci < count($tabla); $ci++) {
                    foreach ($tabla[$ci]['aplicados'] as $ap) {
                        $idP = $ap['idPago'] ?? null;
                        if (!$idP) continue;
                        $fp = $ap['fechaRegistro'] ?? null;
                        if (!$fp) continue;
                        if (!isset($todosLosPagos[$idP])) {
                            $todosLosPagos[$idP] = [
                                'fecha'      => date('Y-m-d', strtotime($fp)),
                                'cuotaIdx'   => $ci,
                                'montoTotal' => 0,
                            ];
                        }
                        $todosLosPagos[$idP]['montoTotal'] += (float)($ap['aplicado'] ?? 0);
                    }
                }

                // Pagos invisibles: existentes en la API pero nunca aplicados a ninguna cuota
                foreach ($plById as $idP => $pl) {
                    if (isset($todosLosPagos[$idP])) continue;
                    $fp = $pl['fechaRegistro'] ?? null;
                    if (!$fp) continue;
                    $firstIdx = null;
                    foreach ($pl['cuotas'] ?? [] as $cn) {
                        if (isset($cuotaNumToIdx[$cn])) {
                            $idx = $cuotaNumToIdx[$cn];
                            if ($firstIdx === null || $idx < $firstIdx) $firstIdx = $idx;
                        }
                    }
                    if ($firstIdx === null) continue;
                    $todosLosPagos[$idP] = [
                        'fecha'      => date('Y-m-d', strtotime($fp)),
                        'cuotaIdx'   => $firstIdx,
                        'montoTotal' => round((float)($pl['remaining'] ?? 0), 2),
                        'invisible'  => true,
                    ];
                }

                $pagosParaMatch = [];
                foreach ($todosLosPagos as $idP => $info) {
                    $pagosParaMatch[] = [
                        'idPago'     => $idP,
                        'fecha'      => $info['fecha'],
                        'cuotaIdx'   => $info['cuotaIdx'],
                        'montoTotal' => round($info['montoTotal'], 2),
                    ];
                }
                usort($pagosParaMatch, function ($a, $b) {
                    return strtotime($b['fecha']) <=> strtotime($a['fecha']);
                });

                // ── PASO 2-3: Emparejar cada FECHA de contracargo (sumada) con un pago ──
                $idPagosCC = [];
                $ccMarkers = [];

                $ccFechasOrdenadas = [];
                foreach ($notasCargoPorFecha as $fn => $monto) {
                    if ($monto > 0) $ccFechasOrdenadas[$fn] = round($monto, 2);
                }
                ksort($ccFechasOrdenadas);

                foreach ($ccFechasOrdenadas as $ccFn => $ccMonto) {
                    $matched = false;

                    // Prioridad 1a: misma fecha + monto similar (±$5)
                    foreach ($pagosParaMatch as $pg) {
                        if (isset($idPagosCC[$pg['idPago']])) continue;
                        if ($pg['fecha'] !== $ccFn) continue;
                        if (abs($pg['montoTotal'] - $ccMonto) > 5) continue;
                        $idPagosCC[$pg['idPago']] = true;
                        $ccMarkers[] = ['cuotaIdx' => $pg['cuotaIdx'], 'monto' => $ccMonto, 'fecha' => $ccFn, 'idPago' => $pg['idPago'], 'esReembolso' => !empty($esReembolsoPorFecha[$ccFn])];
                        $matched = true;
                        break;
                    }
                    if ($matched) continue;

                    // Prioridad 1b: misma fecha, cualquier monto
                    foreach ($pagosParaMatch as $pg) {
                        if (isset($idPagosCC[$pg['idPago']])) continue;
                        if ($pg['fecha'] !== $ccFn) continue;
                        $idPagosCC[$pg['idPago']] = true;
                        $ccMarkers[] = ['cuotaIdx' => $pg['cuotaIdx'], 'monto' => $ccMonto, 'fecha' => $ccFn, 'idPago' => $pg['idPago'], 'esReembolso' => !empty($esReembolsoPorFecha[$ccFn])];
                        $matched = true;
                        break;
                    }
                    if ($matched) continue;

                    // Prioridad 2: spillover — pago anterior con monto similar (±$5)
                    foreach ($pagosParaMatch as $pg) {
                        if (isset($idPagosCC[$pg['idPago']])) continue;
                        if ($pg['fecha'] >= $ccFn) continue;
                        if (abs($pg['montoTotal'] - $ccMonto) > 5) continue;
                        $idPagosCC[$pg['idPago']] = true;
                        $ccMarkers[] = ['cuotaIdx' => $pg['cuotaIdx'], 'monto' => $ccMonto, 'fecha' => $ccFn, 'idPago' => $pg['idPago'], 'esReembolso' => !empty($esReembolsoPorFecha[$ccFn])];
                        $matched = true;
                        break;
                    }
                    if ($matched) continue;

                    // Prioridad 2b: REEMBOLSO (datosNotasCargos) con fecha distinta al depósito o monto distinto al pago.
                    // La API es un solo resultado: el reembolso debe restarse del depósito que lo absorbió (p. ej. 6680 − 5344),
                    // no puede quedar la cadena de sobrantes como si el monto íntegro siguiera en el pool.
                    if (!$matched && !empty($esReembolsoPorFecha[$ccFn])) {
                        $candidatosReemb = [];
                        foreach ($pagosParaMatch as $pg) {
                            if (isset($idPagosCC[$pg['idPago']])) {
                                continue;
                            }
                            if ($pg['fecha'] >= $ccFn) {
                                continue;
                            }
                            $candidatosReemb[] = $pg;
                        }
                        usort($candidatosReemb, function ($a, $b) {
                            $cmp = $b['montoTotal'] <=> $a['montoTotal'];
                            if ($cmp !== 0) {
                                return $cmp;
                            }
                            return strtotime($b['fecha']) <=> strtotime($a['fecha']);
                        });
                        foreach ($candidatosReemb as $pg) {
                            if (round((float) $pg['montoTotal'], 2) + 0.01 < round($ccMonto, 2)) {
                                continue;
                            }
                            $idPagosCC[$pg['idPago']] = true;
                            $ccMarkers[] = [
                                'cuotaIdx'   => $pg['cuotaIdx'],
                                'monto'      => $ccMonto,
                                'fecha'      => $ccFn,
                                'idPago'     => $pg['idPago'],
                                'esReembolso'=> true,
                            ];
                            $matched = true;
                            break;
                        }
                    }
                    if ($matched) {
                        continue;
                    }

                    // CASO ESPECIAL — Contracargo sin pago posterior: la nota de cargo tiene fecha posterior al último pago.
                    // Se aplica el contracargo en la última fecha en que el cliente pagó (mismo flujo que contracargo, solo condición distinta).
                    if (!$matched) {
                        $fechaRef = $ultimoPagoDesdeTabla['fecha'] ?? null;
                        if ($fechaRef === null && !empty($pagosParaMatch)) {
                            $fechaRef = $pagosParaMatch[0]['fecha'];
                        }
                        if ($fechaRef !== null && $ccFn > $fechaRef) {
                            $candidato = null;
                            if ($ultimoPagoDesdeTabla !== null && $ultimoPagoDesdeTabla['fecha'] < $ccFn && !isset($idPagosCC[$ultimoPagoDesdeTabla['idPago']])) {
                                $candidato = $ultimoPagoDesdeTabla;
                            }
                            if ($candidato === null && !empty($pagosParaMatch)) {
                                foreach ($pagosParaMatch as $pg) {
                                    if (isset($idPagosCC[$pg['idPago']])) continue;
                                    if ($pg['fecha'] >= $ccFn) continue;
                                    $candidato = ['fecha' => $pg['fecha'], 'idPago' => $pg['idPago'], 'cuotaIdx' => $pg['cuotaIdx']];
                                    break;
                                }
                            }
                            if ($candidato !== null) {
                                $idPagosCC[$candidato['idPago']] = true;
                                $ccMarkers[] = ['cuotaIdx' => $candidato['cuotaIdx'], 'monto' => $ccMonto, 'fecha' => $ccFn, 'idPago' => $candidato['idPago'], 'esReembolso' => !empty($esReembolsoPorFecha[$ccFn])];
                            }
                        }
                    }
                }

                foreach ($ccMarkers as &$mkCc) {
                    $fkCc = $mkCc['fecha'] ?? '';
                    $mkCc['tipoDisplay'] = $tipoDisplayCargoPorFecha[$fkCc] ?? 'contracargo';
                }
                unset($mkCc);

                if (!empty($idPagosCC)) {
                    // Monto total de contracargo por idPago (varios markers pueden apuntar al mismo pago)
                    $montoContracargoPorPago = [];
                    foreach ($ccMarkers as $mk) {
                        $idP = $mk['idPago'] ?? null;
                        if ($idP) {
                            $montoContracargoPorPago[$idP] = ($montoContracargoPorPago[$idP] ?? 0) + (float)($mk['monto'] ?? 0);
                        }
                    }
                    foreach (array_keys($montoContracargoPorPago) as $idP) {
                        $montoContracargoPorPago[$idP] = round($montoContracargoPorPago[$idP], 2);
                    }

                    // ── PASO 4: Primera cuota afectada ──
                    $primeraCuota = PHP_INT_MAX;
                    foreach ($ccMarkers as $mk) {
                        $primeraCuota = min($primeraCuota, $mk['cuotaIdx']);
                    }

                    // Montos aplicados ANTES de la zona afectada (se descuentan del pool)
                    $aplicadoAnterior = [];
                    for ($ci = 0; $ci < $primeraCuota; $ci++) {
                        foreach ($tabla[$ci]['aplicados'] as $ap) {
                            $idP = $ap['idPago'] ?? null;
                            if ($idP) {
                                $aplicadoAnterior[$idP] = ($aplicadoAnterior[$idP] ?? 0) + (float)($ap['aplicado'] ?? 0);
                            }
                        }
                    }

                    // ── PASO 5: Limpiar todas las entradas de cuotas afectadas ──
                    for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                        $tabla[$ci]['aplicados'] = [];
                    }

                    // ── PASO 5b: Pool desde pagos_list (remaining − aplicadoAnterior); si el pago tiene contracargo parcial, entra al pool solo el sobrante (remaining − contracargo) ──
                    $pool = [];
                    foreach ($plById as $idP => $pl) {
                        $extOrig = round((float)($pl['_extOrig'] ?? $pl['extemporaneos'] ?? 0), 2);
                        $remaining = round((float)($pl['montoPagoOriginal'] ?? 0) - $extOrig, 2);
                        $usedBefore = round($aplicadoAnterior[$idP] ?? 0, 2);
                        $contracargoTotal = isset($montoContracargoPorPago[$idP]) ? round($montoContracargoPorPago[$idP], 2) : 0;
                        if (isset($idPagosCC[$idP])) {
                            // extemporáneos puede incluir gasto cobranza y contracargo. (monto − extOrig) ya refleja ambos si el API es coherente.
                            // contracargoTotal viene de notas "POR CONTRACARGO". No restar otra vez lo que ya está dentro de extOrig:
                            // se estima la parte de gasto con notas "GASTO(S) DE COBRANZA" en la misma fecha que el depósito.
                            $ccDeduccion = 0.0;
                            if ($contracargoTotal > 0.009) {
                                $fr = $pl['fechaRegistro'] ?? null;
                                $fechaPagoNorm = ($fr !== null && $fr !== '' && strtotime((string) $fr) !== false)
                                    ? date('Y-m-d', strtotime((string) $fr))
                                    : '';
                                $gastoEnNotas = ($fechaPagoNorm !== '')
                                    ? round((float) ($gastoCobranzaPorFecha[$fechaPagoNorm] ?? 0), 2)
                                    : 0.0;
                                $extAtribGasto = min($extOrig, $gastoEnNotas);
                                $ccYaEnExt = min($contracargoTotal, max(0.0, round($extOrig - $extAtribGasto, 2)));
                                $ccDeduccion = max(0.0, round($contracargoTotal - $ccYaEnExt, 2));
                            }
                            $available = round($remaining - $usedBefore - $ccDeduccion, 2);
                            if ($available <= 0.009) continue;
                            $hasAffectedCuota = true;
                        } else {
                            $available = round($remaining - $usedBefore, 2);
                            if ($available <= 0.009) continue;
                            $hasAffectedCuota = false;
                            foreach ($pl['cuotas'] ?? [] as $cn) {
                                if (isset($cuotaNumToIdx[$cn]) && $cuotaNumToIdx[$cn] >= $primeraCuota) {
                                    $hasAffectedCuota = true;
                                    break;
                                }
                            }
                            if (!$hasAffectedCuota && $usedBefore <= 0.009) continue;
                        }

                        $pool[] = [
                            'idPago'    => $idP,
                            'total'     => $available,
                            'fechaRegistro' => $pl['fechaRegistro'],
                            'esSobranteDesdeAntes' => $usedBefore > 0,
                        ];
                    }
                    usort($pool, function ($a, $b) {
                        return $this->timestampOrdenFecha($a["fechaRegistro"] ?? null) <=> $this->timestampOrdenFecha($b["fechaRegistro"] ?? null);
                    });

                    // ── PASO 6: Re-aplicar pagos legítimos desde la cuota más antigua ──
                    $curCuota = $primeraCuota;
                    foreach ($pool as $pg) {
                        $rem = round($pg['total'], 2);
                        $primera = true;
                        $sobrante = $pg['esSobranteDesdeAntes'];

                        while ($rem > 0 && $curCuota < count($tabla)) {
                            $totalLeg = 0;
                            foreach ($tabla[$curCuota]['aplicados'] as $ap) {
                                $totalLeg += (float)($ap['aplicado'] ?? 0);
                            }
                            $def = round($tabla[$curCuota]['monto_cargo'] - $totalLeg, 2);
                            if ($def <= 0) { $curCuota++; continue; }

                            $apl = min($rem, $def);
                            $montoParaMostrar = round($rem, 2);
                            if ($primera && isset($idPagosCC[$pg['idPago']])) {
                                $plPago = $plById[$pg['idPago']] ?? null;
                                if ($plPago !== null) {
                                    $montoParaMostrar = round((float)($plPago['montoPagoOriginal'] ?? $plPago['montoPago'] ?? 0), 2);
                                }
                            }
                            $tabla[$curCuota]['aplicados'][] = [
                                'idPago'        => $pg['idPago'],
                                'montoPago'     => $montoParaMostrar,
                                'aplicado'      => round($apl, 2),
                                'fechaRegistro' => $pg['fechaRegistro'],
                                'fechaPago'     => $tabla[$curCuota]['fecha'],
                                'diasMora'      => null,
                                'extemporaneos' => 0,
                                'es_sobrante'   => $sobrante || !$primera,
                            ];

                            $rem = round($rem - $apl, 2);
                            $primera = false;
                            $sobrante = true;

                            $totalLeg += $apl;
                            if ($totalLeg >= $tabla[$curCuota]['monto_cargo']) $curCuota++;
                        }
                    }

                    // ── PASO 7: Fecha de cierre de cada cuota (fecha del último pago legítimo si está llena) ──
                    $closingDates = [];
                    for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                        $totalLeg = 0;
                        $maxTs = null;
                        foreach ($tabla[$ci]['aplicados'] as $ap) {
                            $totalLeg += (float)($ap['aplicado'] ?? 0);
                            $fr = $ap['fechaRegistro'] ?? null;
                            if ($fr) {
                                $ts = strtotime($fr);
                                if ($maxTs === null || $ts > $maxTs) $maxTs = $ts;
                            }
                        }
                        if ($totalLeg >= $tabla[$ci]['monto_cargo'] - 0.009 && $maxTs !== null) {
                            $closingDates[$ci] = date('Y-m-d', $maxTs);
                        } else {
                            $closingDates[$ci] = null;
                        }
                    }

                    // ── PASO 8: Colocar CC'd + contracargo en la cuota activa. Preferir la primera cuota donde ese pago (idPCC) quedó aplicado (así pago y contracargo salen en la misma cuota que “pagó”). ──
                    foreach ($ccMarkers as $mk) {
                        $ccDate = $mk['fecha'];
                        $idPCC = $mk['idPago'];

                        $targetCuota = null;
                        $targetCuotaPorAplicado = false;

                        // Reembolso: ubicar en la última cuota cuyo vencimiento sea <= fecha del reembolso (ej. reembolso 31/12 → cuota con venc. 29/12, no la primera donde cayó el depósito).
                        if (!empty($mk['esReembolso'])) {
                            $ccTs = strtotime((string) $ccDate);
                            if ($ccTs !== false) {
                                $bestIdx = null;
                                $bestTs = null;
                                for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                                    $fv = $tabla[$ci]['fecha'] ?? null;
                                    if ($fv === null || $fv === '') {
                                        continue;
                                    }
                                    $ts = strtotime((string) $fv);
                                    if ($ts === false) {
                                        continue;
                                    }
                                    if ($ts <= $ccTs) {
                                        if ($bestTs === null || $ts > $bestTs) {
                                            $bestTs = $ts;
                                            $bestIdx = $ci;
                                        }
                                    }
                                }
                                if ($bestIdx !== null) {
                                    $targetCuota = $bestIdx;
                                    $targetCuotaPorAplicado = true;
                                }
                            }
                        }

                        if ($targetCuota === null) {
                            for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                                foreach ($tabla[$ci]['aplicados'] as $ap) {
                                    if (($ap['idPago'] ?? null) == $idPCC) {
                                        $targetCuota = $ci;
                                        $targetCuotaPorAplicado = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        if ($targetCuota === null) {
                            for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                                $cd = $closingDates[$ci] ?? null;
                                if ($cd === null || $ccDate <= $cd) {
                                    $targetCuota = $ci;
                                    break;
                                }
                            }
                        }
                        if ($targetCuota === null) {
                            for ($ci = count($tabla) - 1; $ci >= $primeraCuota; $ci--) {
                                if ($closingDates[$ci] === null) { $targetCuota = $ci; break; }
                            }
                            if ($targetCuota === null) $targetCuota = count($tabla) - 1;
                        }

                        $ccPl = $plById[$idPCC] ?? null;
                        $ccFechaReg = $mk['fecha'];
                        if ($ccPl && !$targetCuotaPorAplicado) {
                            $montoReal = round((float)($ccPl['montoPagoOriginal'] ?? $ccPl['montoPago'] ?? 0), 2);
                            $ccFechaReg = $ccPl['fechaRegistro'];
                            $tabla[$targetCuota]['aplicados'][] = [
                                'idPago'        => $idPCC,
                                'montoPago'     => $montoReal,
                                'aplicado'      => round(min($montoReal, $tabla[$targetCuota]['monto_cargo']), 2),
                                'fechaRegistro' => $ccFechaReg,
                                'es_sobrante'   => false,
                                'cc_invalido'   => true,
                            ];
                        } elseif ($ccPl) {
                            $ccFechaReg = $ccPl['fechaRegistro'];
                        }
                        if (!empty($mk['esReembolso'])) {
                            $ccFechaReg = $mk['fecha'];
                        }

                        $tdMk = $mk['tipoDisplay'] ?? 'contracargo';
                        if (!empty($mk['esReembolso'])) {
                            $conceptoDispCc = 'reembolso';
                        } elseif ($tdMk === 'nccc') {
                            $conceptoDispCc = 'nccc';
                        } else {
                            $conceptoDispCc = 'contracargo';
                        }

                        $tabla[$targetCuota]['aplicados'][] = [
                            'tipo'              => 'contracargo',
                            'montoPago'         => $mk['monto'],
                            'aplicado'          => $mk['monto'],
                            'fechaRegistro'     => $mk['fecha'],
                            '_sortDate'         => $ccFechaReg,
                            'id_pago_asociado'  => (string) $idPCC,
                            'concepto_display'  => $conceptoDispCc,
                        ];
                    }

                    // ── PASO 9: Recalcular totales (solo pagos legítimos) ──
                    for ($ci = $primeraCuota; $ci < count($tabla); $ci++) {
                        $total = 0;
                        foreach ($tabla[$ci]['aplicados'] as $ap) {
                            if (!empty($ap['cc_invalido'])) continue;
                            if (isset($ap['tipo']) && $ap['tipo'] === 'contracargo') continue;
                            if (!empty($ap['no_cuenta_para_total_cuota'])) continue;
                            $total += (float)($ap['aplicado'] ?? 0);
                        }
                        $tabla[$ci]['total_pagado'] = round($total, 2);
                        $tabla[$ci]['pendiente'] = round(max($tabla[$ci]['monto_cargo'] - $total, 0), 2);
                    }

                    // Orden final de aplicados: ordenarAplicadosExtemporaneosAntesDeLegitimos (intercala cargo/reembolso)

                    // Descontar montos consumidos de notasCargoPorFecha
                    foreach ($ccMarkers as $mk) {
                        if (isset($notasCargoPorFecha[$mk['fecha']])) {
                            $notasCargoPorFecha[$mk['fecha']] = round(max($notasCargoPorFecha[$mk['fecha']] - $mk['monto'], 0), 2);
                        }
                    }
                }
            }

            self::set("notasCargoPorFecha", $notasCargoPorFecha);
            self::set("gastoCobranzaPorFecha", $gastoCobranzaPorFecha ?? []);
            self::set("esReembolsoPorFecha", $esReembolsoPorFecha ?? []);
            self::set("tipoDisplayCargoPorFecha", $tipoDisplayCargoPorFecha ?? []);
            self::set("hayNotasCargos", $hayNotasCargos);

            if ($usarMotorV2) {
                $notasCreditos = $estadoCuenta['datosNotasCreditos'] ?? [];
                if (!is_array($notasCreditos)) {
                    $notasCreditos = [];
                }
                $this->aplicarNotasCreditoEnTablaV2($tabla, $notasCreditos);
            }

            $this->inyectarDepositosSoloGastoCobranza($tabla, $pagos_list);
            $this->consolidarGastosCobranzaVisualizacionEnTabla($tabla, is_array($gastoCobranzaPorFecha ?? null) ? $gastoCobranzaPorFecha : []);
            $pagosListByIdPago = [];
            foreach ($pagos_list as $pl) {
                $idP = $pl['idPago'] ?? null;
                if ($idP !== null && $idP !== '') {
                    $pagosListByIdPago[is_numeric($idP) ? (string) (int) $idP : (string) $idP] = $pl;
                }
            }
            $this->reubicarPagosRealesFueraDeFilasConExtemporaneo($tabla, $pagosListByIdPago);
            $this->inyectarLineasGastoCobranzaFaltantesPorFecha($tabla, is_array($gastoCobranzaPorFecha ?? null) ? $gastoCobranzaPorFecha : []);
            $this->ordenarAplicadosExtemporaneosAntesDeLegitimos($tabla);
            $this->recalcularTotalesTablaEstadoCuenta($tabla);
            $saldoTotalPagadoDesdeTabla = $this->calcularSaldoTotalPagadoDesdeTabla($tabla);
            self::set('saldoTotalPagadoDesdeTabla', $saldoTotalPagadoDesdeTabla);
            EstadoCuentaTimingLog::mark('tabla_post_procesos');

            if (
                !isset($resultado['data']['idCredito']) ||
                $resultado['data']['idCredito'] === null ||
                $resultado['data']['idCredito'] === '' ||
                empty($resultado['data']['idCredito'])
            ) {
                $this->renderConsultaAlertaSinIdCreditoS2($script, $tienePermisoFechaCorte);
                return;
            }

            // Diferido — badge se actualiza async
            self::set('esGestionExternaMx', false);
            self::set('gestionExternaEtiquetaCelula', '');
            EstadoCuentaTimingLog::mark('dao_gestion_externa_mx');

            self::set("dataCliente", $cliente);
            self::set("dataEstadoCuenta", $estadoCuenta);
            $this->setEcAclaracionesUltimoPagoMetaParaVista((int) ($estadoCuenta['idCredito'] ?? 0));
            self::set("dataOtrosDatos", $otrosDatos); //Recurso Front
            self::set("direcciones", $respDAO);
            self::set("referencias", $referencias);
            self::set("notas", $notas);
            $idCredDictamenPreload = (int)($estadoCuenta['idCredito'] ?? 0);
            $refRowDictamenPreload = [];
            if (!empty($referencias['success']) && !empty($referencias['datos'][0]) && is_array($referencias['datos'][0])) {
                $refRowDictamenPreload = $referencias['datos'][0];
            }
            $celDictamenPreload = trim((string)($cliente['celular'] ?? ''));

            // Diferido — se carga async al abrir modal de dictamen
            self::set('dictamenContactoPreload', [
                'id_credito' => $idCredDictamenPreload,
                'opciones'   => [],
            ]);
            EstadoCuentaTimingLog::mark('dictamen_preload');

            $idCreditoCruce = (int) ($estadoCuenta['idCredito'] ?? $idConsultado ?? 0);
            $notasCruceRaw  = $estadoCuenta['datosNotasCargos'] ?? [];
            if (!is_array($notasCruceRaw)) {
                $notasCruceRaw = [];
            }
            self::set('ecCrucePayload', [
                'idCredito'   => $idCreditoCruce,
                'fechaCorte'  => $fechaHoy,
                'notasCargos' => $notasCruceRaw,
            ]);

            self::set("titulo", "Resultado de la solicitud");
            $scriptConPermiso = str_replace('TienePermisoRegistrarDocumentos_PLACEHOLDER', json_encode($tienePermisoRegistrarDocumentos), $script);
            $scriptConPermiso = str_replace('TienePermisoFechaCorte_PLACEHOLDER', json_encode($tienePermisoFechaCorte), $scriptConPermiso);
            self::set("script", $this->appendRastreoSabuesoScriptSiAplica($scriptConPermiso, $tienePermisoRastreoNeverPaid));
            self::set("tabla", $tabla);
            self::set('catalogoMotivosCondonacion', EstadoCuentaDAO::getCatalogoMotivosCondonacion());
            EstadoCuentaTimingLog::finish('render_ok');
            return self::render("__SPARTA_SECRET_REDACTED___request");


        }

        # -----------------------------
        # GET NORMAL (menú Estados de cuentas: el permiso de registrar documentos no aplica)
        # -----------------------------
        self::set("titulo", "Estados de Cuenta");
        $scriptConsulta = str_replace('TienePermisoRegistrarDocumentos_PLACEHOLDER', json_encode(false), $script);
        $scriptConsulta = str_replace('TienePermisoFechaCorte_PLACEHOLDER', json_encode($tienePermisoFechaCorte), $scriptConsulta);
        self::set("script", $scriptConsulta);
        return self::render("__SPARTA_SECRET_REDACTED___consulta");
    }
    public function getclientesEstadoCuenta()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
    }

    public function validarCredito()
    {
        $idCredito = $_POST['idCredito'] ?? null;
        $idCreditoLista = $_POST['idCreditoLista'] ?? null;
        $idAValidar = $idCreditoLista ?? $idCredito;

        if (!$idAValidar) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito no proporcionado']);
            return;
        }

        // ✅ Solo consultas locales — sin API externa
        $referenciasMx = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idAValidar);

        if (empty($referenciasMx['success']) && !empty($referenciasMx['error'])) {
            self::respuestaJSON([
                'success' => false,
                'tipo' => 'error_db',
                'mensaje' => 'No se pudo validar el crédito: falló la conexión a la base de datos (México). Verifique red/firewall del servidor o intente más tarde.',
            ]);
            return;
        }

        if (!empty($referenciasMx['datos'])) {
            self::respuestaJSON(['success' => true, 'mensaje' => 'ID de crédito válido']);
            return;
        }

        $datosGuat = EmpresasDAO::getGuatemalaEstadoCuenta($idAValidar);
        if (empty($datosGuat['success']) && !empty($datosGuat['error'])) {
            self::respuestaJSON([
                'success' => false,
                'tipo' => 'error_db',
                'mensaje' => 'No se pudo validar el crédito: falló la conexión a la base de datos (Guatemala). Verifique red/firewall del servidor o intente más tarde.',
            ]);
            return;
        }

        if (!empty($datosGuat['datos'])) {
            self::respuestaJSON([
                'success' => false,
                'tipo' => 'credito_guatemala',
                'mensaje' => 'El crédito pertenece a Guatemala'
            ]);
            return;
        }

        self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito incorrecto']);
    }
    public static function getclientesEstadoCuentaNombre()
    {
        // Leer JSON del POST
        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($input['nombre'] ?? '');

        // Llamada al DAO usando el término real
        $respDAO = EmpresasDAO::getConsultaPorNombre($nombre);

        $datos = [];

        // Normalizar respuesta del DAO al formato del autocomplete
        if (!empty($respDAO['datos'])) {
            foreach ($respDAO['datos'] as $row) {
                $datos[] = [
                    'id' => $row['Id_credito'],
                    'nombre_completo' => $row['Nombre_cliente']
                ];
            }
        }

        // Respuesta final para el frontend
        header('Content-Type: application/json');
        echo json_encode([
            'resultado' => !empty($datos),
            'datos' => $datos
        ]);
    }

    /**
     * Inicializa el handle cURL para la API S2 (misma configuración que api___SPARTA_SECRET_REDACTED__).
     *
     * @return \CurlHandle|resource|false
     */
    private function api___SPARTA_SECRET_REDACTED___curl_init($idCredito, $fechaCorte, int $timeoutSegundos = 20, bool $logRequest = true)
    {
        $url = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
        $fechaCorteStr = is_string($fechaCorte) ? $fechaCorte : date('Y-m-d', is_numeric($fechaCorte) ? (int) $fechaCorte : time());
        $payload = json_encode([
            'idCredito'  => (int) $idCredito,
            'fechaCorte' => $fechaCorteStr,
        ]);
        if ($logRequest) {
            error_log('[EstadoCuenta API] Request idCredito=' . (int) $idCredito . ', fechaCorte=' . $fechaCorteStr);
        }

        $envTimeout = getenv('ESTADO_CUENTA_S2_TIMEOUT');
        if ($envTimeout !== false && $envTimeout !== '' && ctype_digit((string) $envTimeout)) {
            $timeoutSegundos = max(2, min(60, (int) $envTimeout));
        } else {
            $timeoutSegundos = (int) $timeoutSegundos;
            if ($timeoutSegundos < 2) {
                $timeoutSegundos = 2;
            }
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        $headers = [
            'Token: __SPARTA_TOKEN_REDACTED__',
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSegundos);
        $connectTimeout = min(15, max(3, (int) ($timeoutSegundos / 2)));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        return $ch;
    }

    /**
     * @param string|false $response
     */
    private function api___SPARTA_SECRET_REDACTED___parsear_respuesta($response, int $httpCode, ?string $curlError = null): array
    {
        if ($response === false) {
            $msg = $curlError !== null && $curlError !== '' ? $curlError : 'Sin respuesta del servidor';

            return [
                'ok'     => false,
                'status' => 0,
                'error'  => 'Error al conectar con servidor: ' . $msg,
                'data'   => null,
            ];
        }

        $json = json_decode($response, true);
        if ($json === null) {
            return [
                'ok'     => false,
                'status' => $httpCode,
                'error'  => 'Respuesta no válida del servidor (JSON inválido)',
                'data'   => null,
            ];
        }

        if ($httpCode !== 200) {
            return [
                'ok'     => false,
                'status' => $httpCode,
                'error'  => $json['mensaje'][0] ?? 'No hay conexión con S2',
                'data'   => $json,
            ];
        }

        if (!isset($json['estadoCuenta'])) {
            return [
                'ok'     => false,
                'status' => $httpCode,
                'error'  => 'No se encontraron datos en la API',
                'data'   => $json,
            ];
        }

        return [
            'ok'     => true,
            'status' => 200,
            'error'  => null,
            'data'   => $json['estadoCuenta'],
        ];
    }

    function api___SPARTA_SECRET_REDACTED__($idCredito, $fechaCorte, $timeoutSegundos = 20)
    {
        $ch = $this->api___SPARTA_SECRET_REDACTED___curl_init($idCredito, $fechaCorte, $timeoutSegundos, true);
        if ($ch === false) {
            return [
                'ok'     => false,
                'status' => 0,
                'error'  => 'No se pudo inicializar la conexión con S2',
                'data'   => null,
            ];
        }
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        return $this->api___SPARTA_SECRET_REDACTED___parsear_respuesta($response, $httpCode, $curlErr !== '' ? $curlErr : null);
    }

    /**
     * Varias consultas estado de cuenta en paralelo (curl_multi). Misma API que api___SPARTA_SECRET_REDACTED__.
     *
     * @param int[] $idsCredito
     * @return array<int, array> Mapa id_credito → respuesta con forma de api___SPARTA_SECRET_REDACTED__()
     */
    public function api___SPARTA_SECRET_REDACTED___parallel(array $idsCredito, $fechaCorte, int $timeoutSegundos = 10, int $maxConcurrent = 12): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCredito), static function ($v) {
            return $v > 0;
        })));
        $out = [];
        if ($ids === []) {
            return $out;
        }
        $maxConcurrent = max(1, min(32, $maxConcurrent));
        $fechaCorteStr = is_string($fechaCorte) ? $fechaCorte : date('Y-m-d', is_numeric($fechaCorte) ? (int) $fechaCorte : time());

        foreach (array_chunk($ids, $maxConcurrent) as $chunk) {
            $mh = curl_multi_init();
            if ($mh === false) {
                foreach ($chunk as $cid) {
                    $out[(int) $cid] = [
                        'ok'     => false,
                        'status' => 0,
                        'error'  => 'No se pudo inicializar curl_multi',
                        'data'   => null,
                    ];
                }
                continue;
            }
            $handles = [];
            foreach ($chunk as $idCredito) {
                $idCredito = (int) $idCredito;
                $ch = $this->api___SPARTA_SECRET_REDACTED___curl_init($idCredito, $fechaCorteStr, $timeoutSegundos, false);
                if ($ch === false) {
                    $out[$idCredito] = [
                        'ok'     => false,
                        'status' => 0,
                        'error'  => 'No se pudo inicializar cURL',
                        'data'   => null,
                    ];
                    continue;
                }
                curl_multi_add_handle($mh, $ch);
                $handles[$idCredito] = $ch;
            }
            $running = null;
            do {
                $mrc = curl_multi_exec($mh, $running);
                if ($running > 0) {
                    curl_multi_select($mh, 1.0);
                }
            } while ($running > 0 && $mrc === CURLM_OK);

            foreach ($handles as $idCredito => $ch) {
                $response = curl_multi_getcontent($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                $out[$idCredito] = $this->api___SPARTA_SECRET_REDACTED___parsear_respuesta(
                    $response,
                    $httpCode,
                    $curlErr !== '' ? $curlErr : null
                );
            }
            curl_multi_close($mh);
        }

        return $out;
    }

    /**
     * Registra en auditoria_documentos la consulta de documento (éxito o error).
     */
    private function registrarAuditoriaDocumento($idReferencia, $documentoClave, $documentoNombre, $exito, $mensajeError = null)
    {
        $usuario = (string) ($_SESSION['usuario'] ?? '');
        EstadoCuentaDAO::registrarAuditoriaDocumentos($usuario, $documentoClave, $documentoNombre, $idReferencia, $exito, $mensajeError);
    }

    ///////////////////////////////////////

    public function documentacion()
    {
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $modulosActuales = $idUsuario ? LoginDAO::getModulosUsuario($idUsuario) : [];
        $tienePermisoRegistrarDocumentos = in_array(21, $modulosActuales);
        $tienePermisoControlFAD_DOC = in_array(22, $modulosActuales);
        $tienePermisoDescargarPDFFAD_DOC = in_array(24, $modulosActuales);
        $script = <<<JS
        <script>
            var tienePermisoRegistrarDocumentos = TienePermisoRegistrarDocumentos_PLACEHOLDER;
            var tienePermisoControlFAD_DOC = TienePermisoControlFAD_DOC_PLACEHOLDER;
            var tienePermisoDescargarPDFFAD_DOC = TienePermisoDescargarPDFFAD_DOC_PLACEHOLDER;
            window.tienePermisoControlFAD_DOC = tienePermisoControlFAD_DOC;
            window.tienePermisoDescargarPDFFAD_DOC = tienePermisoDescargarPDFFAD_DOC;
            document.addEventListener('DOMContentLoaded', () => {

                const registroTipos = {
                    'INE': 'INE',
                    'FACTURA': 'Factura',
                    'CONTRATO': 'Validaciones',
                    'FAD_DOC': 'FAD_DOC',
                    'EVIDENCIA': 'Evidencia'
                };

                const abrirModalRegistroDocumento = (idCredito, tipoDocumento) => {
                    const modalElement = document.getElementById('modalRegistrarDocumentoCliente');
                    if (!modalElement) {
                        Swal.fire('Error', 'No se encontró el modal de registro', 'error');
                        return;
                    }

                    const idInput = document.getElementById('registroDocumentoId');
                    const tipoInput = document.getElementById('registroDocumentoTipo');
                    const tipoTexto = document.getElementById('registroDocumentoTipoTexto');
                    const archivoInput = document.getElementById('registroDocumentoArchivo');
                    const archivoUnico = document.getElementById('registroArchivoUnico');
                    const archivosINE = document.getElementById('registroArchivosINE');
                    const ineFrente = document.getElementById('registroINEFrente');
                    const ineReverso = document.getElementById('registroINEReverso');

                    if (idInput) idInput.value = idCredito || '';
                    if (tipoInput) tipoInput.value = tipoDocumento || '';
                    if (tipoTexto) tipoTexto.value = registroTipos[tipoDocumento] || tipoDocumento || '';

                    // Mostrar/ocultar campos según tipo de documento
                    if (tipoDocumento === 'INE') {
                        if (archivoUnico) archivoUnico.style.display = 'none';
                        if (archivosINE) archivosINE.style.display = 'block';
                        if (archivoInput) archivoInput.value = '';
                        if (ineFrente) ineFrente.value = '';
                        if (ineReverso) ineReverso.value = '';
                    } else {
                        if (archivoUnico) archivoUnico.style.display = 'block';
                        if (archivosINE) archivosINE.style.display = 'none';
                        if (archivoInput) archivoInput.value = '';
                        if (ineFrente) ineFrente.value = '';
                        if (ineReverso) ineReverso.value = '';
                    }

                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                };

                const formRegistrar = document.getElementById('formRegistrarDocumentoCliente');
                if (formRegistrar) {
                    formRegistrar.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const idCredito = document.getElementById('registroDocumentoId')?.value.trim();
                        const tipoDocumento = document.getElementById('registroDocumentoTipo')?.value.trim();

                        if (!idCredito || !tipoDocumento) {
                            Swal.fire('Error', 'Datos incompletos para el registro', 'error');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('idCredito', idCredito);
                        formData.append('tipoDocumento', tipoDocumento);

                        // Validar archivos según tipo de documento
                        if (tipoDocumento === 'INE') {
                            const ineFrente = document.getElementById('registroINEFrente');
                            const ineReverso = document.getElementById('registroINEReverso');

                            if (!ineFrente?.files?.[0] || !ineReverso?.files?.[0]) {
                                Swal.fire('Error', 'Debes seleccionar ambos archivos: frente y reverso del INE', 'error');
                                return;
                            }

                            formData.append('ineFrente', ineFrente.files[0]);
                            formData.append('ineReverso', ineReverso.files[0]);
                        } else {
                            const archivoInput = document.getElementById('registroDocumentoArchivo');

                            if (!archivoInput?.files?.[0]) {
                                Swal.fire('Error', 'Selecciona un archivo para registrar', 'error');
                                return;
                            }

                            formData.append('archivo', archivoInput.files[0]);
                        }

                        Swal.fire({
                            title: 'Registrando documento',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading()
                        });

                        try {
                            const endpoint = tipoDocumento === 'INE'
                                ? '/estadocuenta/registrarINE'
                                : '/estadocuenta/registrarDocumentoCliente';

                            const response = await fetch(endpoint, {
                                method: 'POST',
                                body: formData
                            });
                            const data = await response.json();

                            Swal.close();

                            if (!data || !data.success) {
                                Swal.fire('Error', data?.mensaje || 'No se pudo registrar el documento', 'error');
                                return;
                            }

                            const modalElement = document.getElementById('modalRegistrarDocumentoCliente');
                            if (modalElement) {
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Documento registrado',
                                text: data.mensaje || 'El documento se guardo correctamente.'
                            });

                            // Volver a ejecutar la búsqueda para que se muestre el documento recién subido
                            const btnBuscarRef = document.getElementById('btnBuscar');
                            if (btnBuscarRef) {
                                setTimeout(function() { btnBuscarRef.click(); }, 800);
                            }
                        } catch (error) {
                            Swal.close();
                            Swal.fire('Error', 'No se pudo registrar el documento', 'error');
                        }
                    });
                }

                // Botón limpiar filtros
                const btnResetFiltros = document.getElementById('btnResetFiltros');
                if (btnResetFiltros) {
                    btnResetFiltros.addEventListener('click', () => {
                        // Limpiar campo ID de crédito
                        const idCredito = document.getElementById('idCredito');
                        if (idCredito) {
                            idCredito.value = '';
                        }
                        // Limpiar campo nombre (si existe)
                        const nombre = document.getElementById('nombre');
                        if (nombre) {
                            nombre.value = '';
                        }

                        // Resetear select de tipo de documento
                        const tipoDocumento = document.getElementById('tipoDocumento');
                        if (tipoDocumento) {
                            tipoDocumento.value = '';
                        }
                    });
                }

                const btnBuscar = document.getElementById('btnBuscar');
                const form = document.getElementById('formBusqueda');

                if (!btnBuscar || !form) {
                    console.error('Elementos del formulario no encontrados');
                    return;
                }

                // Escuchar clic del botón en lugar del submit del formulario
                btnBuscar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const idInput = document.getElementById('idCredito');
                    const tipoSelect = document.getElementById('tipoDocumento');

                    if (!idInput || !tipoSelect) {
                        console.error('Campos del formulario no encontrados');
                        Swal.fire('Error', 'Error al acceder a los campos del formulario', 'error');
                        return;
                    }

                    const id   = idInput.value.trim();
                    const tipo = tipoSelect.value;

                    // Validar que el ID no esté vacío
                    if (!id) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ID de crédito requerido',
                            text: 'Por favor introduce el ID de crédito'
                        });
                        return;
                    }

                    // Validar que se haya seleccionado un tipo de documento
                    if (!tipo) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tipo de documento requerido',
                            text: 'Por favor selecciona el documento a comprobar'
                        });
                        return;
                    }

                    // ====== LIMPIAR CONTENEDORES ANTES DE CARGAR NUEVO DOCUMENTO ======
                    const limpiarContenedoresDocumento = function() {
                        // Limpiar canvas de PDF
                        const canvas = document.getElementById('pdfCanvas');
                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) {
                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                            }
                            canvas.width = 0;
                            canvas.height = 0;
                        }

                        // Ocultar TODOS los contenedores
                        const pdfContainer = document.getElementById('documentoPdfContainer');
                        if (pdfContainer) pdfContainer.style.display = 'none';

                        const imgContainer = document.getElementById('documentoImagenContainer');
                        if (imgContainer) imgContainer.style.display = 'none';

                        const embedContainer = document.getElementById('visorPdfEmbed');
                        if (embedContainer) {
                            embedContainer.style.display = 'none';
                            embedContainer.innerHTML = '';
                        }

                        const visorLegacy = document.getElementById('visorDocumento');
                        if (visorLegacy) {
                            visorLegacy.style.display = 'none';
                            visorLegacy.src = '';
                        }

                        // Limpiar imagen de documentos
                        const imgDocumento = document.getElementById('imgDocumento');
                        if (imgDocumento) {
                            imgDocumento.src = '';
                            imgDocumento.style.display = 'none';
                        }

                        // Ocultar controles de PDF
                        const pdfControls = document.getElementById('pdfControls');
                        if (pdfControls) pdfControls.style.display = 'none';

                        // Resetear variables globales de PDF si existen
                        if (typeof pdfDocFactura !== 'undefined' && pdfDocFactura) {
                            pdfDocFactura = null;
                        }
                        if (typeof pageNumFactura !== 'undefined') {
                            pageNumFactura = 1;
                        }
                    };

                    // Primero validar que el ID de crédito exista (API estado de cuenta)
                    Swal.fire({
                        title: 'Validando crédito',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading(),
                        didClose: () => {
                            document.body.classList.remove('swal2-shown');
                            document.body.style.overflow = '';
                        }
                    });
                    const formDataValidar = new FormData();
                    formDataValidar.append('idCredito', id);
                    fetch('/estadocuenta/validarcredito', {
                        method: 'POST',
                        body: formDataValidar
                    })
                    .then(function(r) {
                        const ct = (r.headers.get('content-type') || '').toLowerCase();
                        if (!r.ok || ct.indexOf('application/json') === -1) {
                            return Promise.reject(new Error('HTTP ' + r.status));
                        }
                        return r.json();
                    })
                    .then(function(dataValidar) {
                        if (!dataValidar.success) {
                            Swal.close();
                            if (dataValidar.tipo === 'error_db') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Base de datos no disponible',
                                    text: dataValidar.mensaje || 'No se pudo validar el crédito con el servidor de datos.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ID de crédito no válido',
                                    text: dataValidar.mensaje || 'El ID de crédito no existe. Verifica el número.'
                                });
                            }
                            return null;
                        }
                        limpiarContenedoresDocumento();
                        Swal.fire({
                            title: 'Procesando',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading(),
                            didClose: () => {
                                document.body.classList.remove('swal2-shown');
                                document.body.style.overflow = '';
                            }
                        });
                        return fetch('/estadocuenta/descargar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id, tipo })
                        });
                    })
                    .then(function(res) {
                        if (!res) return null;
                        if (!res.ok) {
                            return res.text().then(t => { throw new Error('HTTP ' + res.status + (t ? ': ' + t.substring(0, 80) : '')); });
                        }
                        return res.json();
                    })
                    .then(function(data) {
                        if (!data) return;
                        Swal.close();

                        if (!data.success) {
                            const mensaje = data.mensaje || '';
                            const esSinDocumento = mensaje.indexOf('no tiene') !== -1 && mensaje.indexOf('registrado') !== -1;
                            if (esSinDocumento) {
                                if (tienePermisoRegistrarDocumentos) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Documento no registrado',
                                        text: mensaje,
                                        showCancelButton: true,
                                        confirmButtonText: 'Registrar',
                                        cancelButtonText: 'Cerrar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            abrirModalRegistroDocumento(id, tipo);
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Documento no registrado',
                                        text: mensaje,
                                        showCancelButton: false,
                                        confirmButtonText: 'Cerrar'
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: mensaje
                                });
                            }
                            return;
                        }

                        // Si es INE, mostrar ambas imágenes (frente y reverso)
                        if (data.tipo === 'INE') {
                            const imgFrente = document.getElementById('imgINEfrente');
                            const imgReverso = document.getElementById('imgINEreverso');

                            // Desactivar descarga en imágenes del INE
                            if (typeof desactivarDescargaImagen === 'function') {
                                if (imgFrente) desactivarDescargaImagen(imgFrente);
                                if (imgReverso) desactivarDescargaImagen(imgReverso);
                            }

                            // Configurar imágenes con carga desde servidor (sin descarga)
                            imgFrente.src = data.frente;
                            imgReverso.src = data.reverso;

                            // LIMPIAR el atributo de marcas "SIN VALOR" del overlay pdfWatermark si existe
                            // INE usa imágenes, no PDF.js, por lo que no debe tener este atributo
                            const watermark = document.getElementById('pdfWatermark');
                            if (watermark) {
                                watermark.removeAttribute('data-marcas-sin-valor');
                            }

                            // Crear marcas de agua inmediatamente y después de que las imágenes se carguen
                            const crearMarcasAguaINE = function() {
                                if (typeof crearMarcasAgua === 'function') {
                                    crearMarcasAgua();
                                }
                            };

                            // Crear marcas de agua inmediatamente
                            setTimeout(crearMarcasAguaINE, 100);

                            imgFrente.onload = function() {
                                // Desactivar descarga después de cargar
                                if (typeof desactivarDescargaImagen === 'function') {
                                    desactivarDescargaImagen(imgFrente);
                                }
                                setTimeout(crearMarcasAguaINE, 300);
                            };

                            imgReverso.onload = function() {
                                // Desactivar descarga después de cargar
                                if (typeof desactivarDescargaImagen === 'function') {
                                    desactivarDescargaImagen(imgReverso);
                                }
                                setTimeout(crearMarcasAguaINE, 300);
                            };

                            // Mostrar modal de INE
                            const modalINE = document.getElementById('modalINE');
                            const bsModal = new bootstrap.Modal(modalINE);
                            bsModal.show();

                            // Las marcas de agua se crearán automáticamente cuando el modal se muestre
                            // gracias al listener en documentacion_consulta.php
                        }
                        // Para FAD_DOC, EVIDENCIA, FACTURA, CONTRATO - usar visor de imágenes con marca de agua
                        else if (data.tipo && data.url) {
                            const imgContainer = document.getElementById('documentoImagenContainer');
                            const pdfContainer = document.getElementById('documentoPdfContainer');
                            const imgDocumento = document.getElementById('imgDocumento');
                            const iframeDocumento = document.getElementById('iframeDocumento');

                            if (!imgContainer || !pdfContainer) {
                                Swal.fire('Error', 'No se pudo cargar el visor de documentos', 'error');
                                return;
                            }

                            // Determinar si es PDF o imagen
                            // Si tiene extension pdf o la URL contiene .pdf o esImagen es false explícitamente
                            // O si la URL es de Google Viewer (significa que es PDF)
                            const esPdf = (data.extension === 'pdf') ||
                                         (data.url && (data.url.includes('.pdf') || data.url.includes('docs.google.com/gview'))) ||
                                         (data.archivo && data.archivo.toLowerCase().endsWith('.pdf')) ||
                                         (data.esImagen === false);

                            if (esPdf) {
                                // Para FACTURA, FAD_DOC, CONTRATO y EVIDENCIA, usar PDF.js con la misma función cargarPDFFactura
                                if (data.tipo === 'FACTURA' || data.tipo === 'FAD_DOC' || data.tipo === 'CONTRATO' || data.tipo === 'EVIDENCIA') {
                                    // Usar PDF.js para FACTURA, FAD_DOC, VALIDACIONES y EVIDENCIA con cargarPDFFactura
                                    const tipoNombre = data.tipo === 'FACTURA' ? 'FACTURA' : (data.tipo === 'FAD_DOC' ? 'FAD_DOC' : (data.tipo === 'EVIDENCIA' ? 'EVIDENCIA' : 'VALIDACIONES'));

                                    // Asegurar que el contenedor de visor simple esté oculto
                                    const embedContainer = document.getElementById('visorPdfEmbed');
                                    if (embedContainer) {
                                        embedContainer.style.display = 'none';
                                    }

                                    imgContainer.style.display = 'none';

                                    const visorLegacy = document.getElementById('visorDocumento');
                                    if (visorLegacy) {
                                        visorLegacy.style.display = 'none';
                                    }

                                    // Usar EXACTAMENTE la misma lógica que EVIDENCIA
                                    // IMPORTANTE: Usar directamente la URL que viene del backend (ya es el proxy local)
                                    let pdfUrl = data.url;

                                    // Solo procesar si viene de Google Viewer
                                    if (pdfUrl.includes('docs.google.com/gview')) {
                                        try {
                                            const urlParams = new URL(pdfUrl);
                                            const urlParam = urlParams.searchParams.get('url');
                                            if (urlParam) {
                                                pdfUrl = decodeURIComponent(urlParam);
                                            }
                                        } catch (e) {
                                        }
                                    }

                                    // NO reconstruir la URL si ya es una URL relativa del proxy local
                                    // El backend ya devuelve la URL correcta del proxy: /estadocuenta/verDocumento?fileName=...
                                    // Solo reconstruir si es una URL absoluta del S3 y no incluye el proxy
                                    if (pdfUrl.includes('http') && pdfUrl.includes('98.90.194.116') && pdfUrl.includes('downloadS3File')) {
                                        // Si es URL directa del S3, convertirla a proxy local para evitar descarga
                                        try {
                                            const urlParams = new URL(pdfUrl);
                                            const fileName = urlParams.searchParams.get('fileName');
                                            if (fileName) {
                                                pdfUrl = '/estadocuenta/verDocumento?fileName=' + encodeURIComponent(fileName);
                                            }
                                        } catch (e) {
                                            console.warn('No se pudo convertir URL a proxy local:', e);
                                        }
                                    }


                                    if (typeof cargarPDFFactura === 'function') {
                                        window.tipoDocumentoActual = data.tipo;
                                        window.urlDocumentoActual = pdfUrl;
                                        window.idCreditoDocumentoActual = id;
                                        if (data.tipo === 'FAD_DOC') {
                                            window.paginasConMediaFAD_DOC = [];
                                            fetch('/estadocuenta/paginasConMedia?idCredito=' + encodeURIComponent(id)).then(function(r){ return r.json(); }).then(function(res){ if (res && res.success) window.paginasConMediaFAD_DOC = Array.isArray(res.paginasConMedia) ? res.paginasConMedia : []; if (typeof actualizarBotonVideosMedia === 'function') actualizarBotonVideosMedia(); }).catch(function(){ window.paginasConMediaFAD_DOC = []; if (typeof actualizarBotonVideosMedia === 'function') actualizarBotonVideosMedia(); });
                                        } else {
                                            window.paginasConMediaFAD_DOC = null;
                                            if (typeof actualizarBotonVideosMedia === 'function') actualizarBotonVideosMedia();
                                        }
                                        if (typeof actualizarBotonDescargarFAD === 'function') actualizarBotonDescargarFAD();
                                        cargarPDFFactura(pdfUrl);
                                    } else {
                                        console.error('PDF.js no está cargado o la función cargarPDFFactura no existe');
                                        Swal.fire('Error', 'El visor de PDF no está disponible', 'error');
                                    }

                                    // Los controles de zoom ya están en el HTML y se mostrarán automáticamente
                                    // El header se ocultará desde cargarPDFConPDFjs cuando se muestre el modal
                                }
                                // Este bloque ya no se usa - todos los tipos ahora usan PDF.js arriba
                                else if (false && (data.tipo === 'CONTRATO')) {
                                    const tipoNombre = {
                                        'FAD_DOC': 'FAD_DOC',
                                        'FACTURA': 'FACTURA',
                                        'CONTRATO': 'VALIDACIONES'
                                    };

                                    // Obtener el modal element primero
                                    const modalElement = document.getElementById('modalDocumento');
                                    if (!modalElement) {
                                        Swal.fire('Error', 'No se encontró el modal de documentos', 'error');
                                        return;
                                    }

                                    // Ocultar contenedores de imagen y PDF.js
                                    imgContainer.style.display = 'none';
                                    pdfContainer.style.display = 'none';

                                    // Obtener el contenedor del modal
                                    const modalBody = document.getElementById('documentoModalBody');
                                    if (!modalBody) {
                                        Swal.fire('Error', 'No se encontró el contenedor del modal', 'error');
                                        return;
                                    }

                                    // Ocultar el iframe legacy si existe
                                    const visorLegacy = document.getElementById('visorDocumento');
                                    if (visorLegacy) {
                                        visorLegacy.style.display = 'none';
                                    }

                                    // Crear o reutilizar un contenedor para el embed
                                    let embedContainer = document.getElementById('visorPdfEmbed');
                                    if (!embedContainer) {
                                        embedContainer = document.createElement('div');
                                        embedContainer.id = 'visorPdfEmbed';
                                        embedContainer.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; display: none; overflow: auto; background-color: #525252;';
                                        modalBody.appendChild(embedContainer);
                                    }

                                    // Limpiar contenido anterior
                                    embedContainer.innerHTML = '';

                                    // Asegurar que el contenedor esté visible y tenga el tamaño correcto
                                    embedContainer.style.display = 'block';
                                    embedContainer.style.position = 'absolute';
                                    embedContainer.style.top = '0';
                                    embedContainer.style.left = '0';
                                    embedContainer.style.right = '0';
                                    embedContainer.style.bottom = '0';
                                    embedContainer.style.width = '100%';
                                    embedContainer.style.height = '100%';
                                    embedContainer.style.overflow = 'auto';
                                    embedContainer.style.backgroundColor = '#525252';

                                    // Crear wrapper para el PDF
                                    // Este wrapper se ajustará cuando se haga zoom
                                    const pdfWrapper = document.createElement('div');
                                    pdfWrapper.id = 'pdfWrapperContrato';
                                    pdfWrapper.style.cssText = 'position: relative; top: 0; left: 0; width: 100%; height: 100%;';

                                    // Crear contenedor con marca de agua (similar a las imágenes)
                                    const watermarkContainer = document.createElement('div');
                                    watermarkContainer.className = 'watermark-container';
                                    watermarkContainer.style.cssText = 'position: relative; width: 100%; height: 100%; display: block;';

                                    // Crear iframe para visualizar el PDF (más control que embed)
                                    const iframePdf = document.createElement('iframe');

                                    // Para VALIDACIONES OK (CONTRATO), agregar parámetros de zoom para que se abra sin zoom excesivo
                                    // Para FAD_DOC, ocultar la barra de herramientas
                                    // Para FACTURA, MOSTRAR la barra de herramientas
                                    let pdfUrl = data.url;
                                    const separator = pdfUrl.includes('#') ? '&' : '#';

                                    if (data.tipo === 'CONTRATO') {
                                        // Agregar parámetros para controlar el zoom inicial
                                        // #view=FitH ajusta el PDF al ancho de la ventana sin zoom excesivo
                                        // También agregar #toolbar=0 para ocultar la barra de herramientas del PDF
                                        pdfUrl = pdfUrl + separator + 'view=FitH&toolbar=0';
                                    } else if (data.tipo === 'FAD_DOC') {
                                        // Para FAD_DOC, ocultar la barra de herramientas
                                        pdfUrl = pdfUrl + separator + 'toolbar=0';
                                    }

                                    iframePdf.src = pdfUrl;
                                    iframePdf.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; display: block; pointer-events: auto;';
                                    iframePdf.setAttribute('type', 'application/pdf');
                                    iframePdf.setAttribute('frameborder', '0');
                                    iframePdf.setAttribute('scrolling', 'auto');

                                    // Manejar errores de carga del iframe
                                    iframePdf.addEventListener('error', function(e) {
                                        Swal.fire('Error', 'No se pudo cargar el documento PDF', 'error');
                                    });

                                    // Crear overlay para marcas de agua que cubra todo el visor
                                    // Este overlay se colocará directamente en el embedContainer para cubrir todo
                                    const watermarkOverlay = document.createElement('div');
                                    watermarkOverlay.className = 'watermark-overlay';
                                    watermarkOverlay.id = 'watermarkOverlayPdf';
                                    // Permitir overflow visible y mantener las franjas rojas del CSS
                                    watermarkOverlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10; overflow: visible;';

                                    // Crear overlay de protección que bloquea el click derecho y el menú superior
                                    // Este overlay tiene dos partes: una para el menú superior y otra invisible para bloquear click derecho

                                    // Overlay para bloquear SOLO click derecho en el menú superior (primeros 60px)
                                    // Usar pointer-events: none para permitir clicks izquierdos en el menú
                                    const menuOverlay = document.createElement('div');
                                    menuOverlay.id = 'pdfMenuOverlay';

                                    // Para otros tipos, mantener el comportamiento original
                                    menuOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 60px; z-index: 20; background: transparent; pointer-events: none;';

                                    // Solo bloquear click derecho en el área del menú (parte superior)
                                    // Capturar en fase de captura para bloquear antes de que llegue al iframe
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si el click es en el área superior (primeros 60px)
                                        const rect = embedContainer.getBoundingClientRect();
                                        const clickY = e.clientY - rect.top;
                                        if (clickY <= 60) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    }, true);

                                    // Bloquear SOLO click derecho en el contenedor (permite clicks izquierdos y scroll)
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si es click derecho
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);

                                    watermarkContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si es click derecho
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);

                                    // Bloquear SOLO mousedown del botón derecho en embedContainer
                                    embedContainer.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos (button === 0) - no hacer nada
                                    }, true);

                                    // Bloquear SOLO mouseup del botón derecho en embedContainer
                                    embedContainer.addEventListener('mouseup', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);

                                    // Overlay de protección que bloquea SOLO click derecho pero permite click izquierdo y scroll
                                    // Usamos pointer-events: none para que NO bloquee clicks izquierdos ni scroll
                                    // Pero capturamos eventos en fase de captura para bloquear SOLO el click derecho
                                    const protectionOverlay = document.createElement('div');
                                    protectionOverlay.id = 'pdfProtectionOverlay';
                                    protectionOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 30; background: transparent; pointer-events: none;';

                                    // IMPORTANTE: Como el overlay tiene pointer-events: none, los eventos pasan a través
                                    // Por eso capturamos en el contenedor padre (embedContainer) en fase de captura
                                    // para bloquear SOLO el click derecho antes de que llegue al iframe

                                    // El menuOverlay ya no necesita listeners porque tiene pointer-events: none
                                    // Los clicks pasan a través y se bloquean en embedContainer

                                    // También capturar en el documento para mayor seguridad (máxima prioridad)
                                    const contextMenuHandler = function(e) {
                                        // Solo bloquear si el evento viene del área del PDF
                                        const target = e.target;
                                        if (embedContainer.contains(target) ||
                                            watermarkContainer.contains(target) ||
                                            pdfWrapper.contains(target) ||
                                            iframePdf.contains(target) ||
                                            protectionOverlay.contains(target) ||
                                            menuOverlay.contains(target)) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    };
                                    // Usar capture phase y alta prioridad
                                    document.addEventListener('contextmenu', contextMenuHandler, { capture: true, passive: false });

                                    // También capturar mousedown del botón derecho a nivel de documento
                                    const mouseDownHandler = function(e) {
                                        if (e.button === 2) { // Botón derecho
                                            const target = e.target;
                                            if (embedContainer.contains(target) ||
                                                watermarkContainer.contains(target) ||
                                                pdfWrapper.contains(target) ||
                                                iframePdf.contains(target) ||
                                                protectionOverlay.contains(target) ||
                                                menuOverlay.contains(target)) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                e.stopImmediatePropagation();
                                                return false;
                                            }
                                        }
                                    };
                                    document.addEventListener('mousedown', mouseDownHandler, { capture: true, passive: false });

                                    // También bloquear a nivel de window para máxima seguridad
                                    const windowContextMenuHandler = function(e) {
                                        const target = e.target;
                                        if (embedContainer.contains(target) ||
                                            watermarkContainer.contains(target) ||
                                            pdfWrapper.contains(target) ||
                                            iframePdf.contains(target) ||
                                            protectionOverlay.contains(target) ||
                                            menuOverlay.contains(target)) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    };
                                    window.addEventListener('contextmenu', windowContextMenuHandler, { capture: true, passive: false });

                                    // Limpiar todos los listeners cuando se cierre el modal
                                    modalElement.addEventListener('hidden.bs.modal', function() {
                                        document.removeEventListener('contextmenu', contextMenuHandler, { capture: true });
                                        document.removeEventListener('mousedown', mouseDownHandler, { capture: true });
                                        window.removeEventListener('contextmenu', windowContextMenuHandler, { capture: true });
                                    }, { once: true });

                                    // Bloquear click derecho también en el iframe directamente (ya está arriba, pero lo mantenemos)
                                    // Nota: Estos listeners ya están definidos arriba en la sección de protección reforzada

                                    // Bloquear arrastrar archivos
                                    protectionOverlay.addEventListener('dragover', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);

                                    protectionOverlay.addEventListener('drop', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);

                                    // Bloquear atajos de teclado en el contenedor
                                    embedContainer.addEventListener('keydown', function(e) {
                                        // Bloquear Ctrl+S, Ctrl+P, F12, etc.
                                        if (e.ctrlKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P')) {
                                            e.preventDefault();
                                            return false;
                                        }
                                        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J'))) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    }, true);

                                    // Intentar bloquear el menú del PDF dentro del iframe cuando se carga
                                    iframePdf.addEventListener('load', function() {
                                        try {
                                            // Intentar acceder al documento del iframe para bloquear eventos
                                            const iframeDoc = iframePdf.contentDocument || iframePdf.contentWindow.document;
                                            if (iframeDoc) {
                                                // Bloquear click derecho dentro del iframe
                                                iframeDoc.addEventListener('contextmenu', function(e) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    e.stopImmediatePropagation();
                                                    return false;
                                                }, true);

                                                // Bloquear mousedown del botón derecho dentro del iframe
                                                iframeDoc.addEventListener('mousedown', function(e) {
                                                    if (e.button === 2) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        e.stopImmediatePropagation();
                                                        return false;
                                                    }
                                                }, true);

                                                // Bloquear otros eventos dentro del iframe
                                                iframeDoc.addEventListener('selectstart', function(e) {
                                                    e.preventDefault();
                                                    return false;
                                                }, true);

                                                // Bloquear también en el body del iframe
                                                if (iframeDoc.body) {
                                                    iframeDoc.body.addEventListener('contextmenu', function(e) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        e.stopImmediatePropagation();
                                                        return false;
                                                    }, true);

                                                    iframeDoc.body.addEventListener('mousedown', function(e) {
                                                        if (e.button === 2) {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            e.stopImmediatePropagation();
                                                            return false;
                                                        }
                                                    }, true);
                                                }
                                            }
                                        } catch (e) {
                                            // Error de CORS - normal cuando el iframe carga contenido de otro dominio
                                            // El overlay de protección seguirá funcionando
                                        }
                                    });

                                    // Agregar elementos al contenedor (orden importante: iframe abajo, overlays arriba)
                                    watermarkContainer.appendChild(iframePdf);
                                    pdfWrapper.appendChild(watermarkContainer);

                                    // Agregar overlays de protección al contenedor principal
                                    // IMPORTANTE: El protectionOverlay debe ir DESPUÉS del menuOverlay para tener mayor z-index
                                    pdfWrapper.appendChild(menuOverlay); // Bloquea clicks en el menú superior
                                    pdfWrapper.appendChild(protectionOverlay); // Bloquea click derecho en todo (z-index más alto)

                                    embedContainer.appendChild(pdfWrapper);

                                    // Agregar overlay de marca de agua directamente al embedContainer para que cubra todo
                                    embedContainer.appendChild(watermarkOverlay);

                                    // REFORZAR PROTECCIÓN CONTRA CLICK DERECHO - Múltiples capas de protección

                                    // 1. Bloquear en el contenedor principal (embedContainer)
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);

                                    // 2. Bloquear en el pdfWrapper
                                    pdfWrapper.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);

                                    // 3. Bloquear también con mousedown para capturar antes del contextmenu (ya está arriba, no duplicar)
                                    // Los listeners de mousedown ya están definidos arriba en embedContainer

                                    // Bloquear SOLO click derecho en pdfWrapper
                                    pdfWrapper.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);

                                    // Bloquear SOLO click derecho en iframePdf
                                    iframePdf.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);

                                    // 4. Bloquear también en el overlay de marca de agua
                                    watermarkOverlay.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);

                                    watermarkOverlay.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // Botón derecho del mouse
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    }, true);

                                    // Para VALIDACIONES OK (CONTRATO), FAD_DOC y FACTURACION OK (FACTURA), agregar controles de zoom
                                    // IMPORTANTE: Las variables embedContainer, pdfWrapper, watermarkContainer deben estar en scope
                                    if (data.tipo === 'CONTRATO' || data.tipo === 'FAD_DOC' || data.tipo === 'FACTURA') {
                                        // Variables para el zoom
                                        let currentZoom = 1.0;
                                        const minZoom = 0.5;
                                        const maxZoom = 3.0;

                                        // Asegurar que tenemos referencias a los elementos necesarios
                                        const zoomEmbedContainer = embedContainer;
                                        const zoomPdfWrapper = pdfWrapper;
                                        const zoomWatermarkContainer = watermarkContainer;

                                        // Crear contenedor de controles de zoom
                                        const zoomControls = document.createElement('div');
                                        zoomControls.id = 'zoomControls' + data.tipo; // ID único por tipo
                                        zoomControls.style.cssText = 'position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 25px; z-index: 1000; display: flex; align-items: center; gap: 15px;';

                                        // Botón zoom out
                                        const btnZoomOut = document.createElement('button');
                                        btnZoomOut.className = 'btn btn-sm btn-light';
                                        btnZoomOut.innerHTML = '<i class="fa fa-search-minus"></i>';
                                        btnZoomOut.style.cssText = 'min-width: 40px;';
                                        btnZoomOut.title = 'Alejar';

                                        // Indicador de zoom
                                        const zoomLevel = document.createElement('span');
                                        zoomLevel.id = 'zoomLevel' + data.tipo; // ID único por tipo
                                        zoomLevel.style.cssText = 'color: white; font-size: 0.9rem; min-width: 60px; text-align: center;';
                                        zoomLevel.textContent = '100%';

                                        // Botón zoom in
                                        const btnZoomIn = document.createElement('button');
                                        btnZoomIn.className = 'btn btn-sm btn-light';
                                        btnZoomIn.innerHTML = '<i class="fa fa-search-plus"></i>';
                                        btnZoomIn.style.cssText = 'min-width: 40px;';
                                        btnZoomIn.title = 'Acercar';

                                        // Botón reset
                                        const btnReset = document.createElement('button');
                                        btnReset.className = 'btn btn-sm btn-light';
                                        btnReset.innerHTML = '<i class="fa fa-undo"></i>';
                                        btnReset.style.cssText = 'min-width: 40px;';
                                        btnReset.title = 'Restablecer zoom';

                                        // Función para aplicar zoom
                                        const applyZoom = function() {
                                            // Usar las referencias locales para asegurar que funcionen
                                            const container = zoomEmbedContainer;
                                            const wrapper = zoomPdfWrapper;
                                            const watermarkContainer = zoomWatermarkContainer;

                                            // Obtener las dimensiones actuales del contenedor visible (el modal se mantiene igual)
                                            const containerWidth = container.clientWidth || container.offsetWidth;
                                            const containerHeight = container.clientHeight || container.offsetHeight;

                                            if (containerWidth === 0 || containerHeight === 0) {
                                                // Si las dimensiones no están disponibles aún, intentar de nuevo
                                                setTimeout(applyZoom, 100);
                                                return;
                                            }

                                            // Guardar la posición actual de scroll antes del cambio
                                            const scrollLeftBefore = container.scrollLeft;
                                            const scrollTopBefore = container.scrollTop;

                                            // Calcular el centro visual actual (posición de scroll + mitad del viewport)
                                            const centerXBefore = scrollLeftBefore + (containerWidth / 2);
                                            const centerYBefore = scrollTopBefore + (containerHeight / 2);

                                            // IMPORTANTE: El wrapper debe tener el tamaño escalado para que el scroll funcione
                                            const scaledWidth = containerWidth * currentZoom;
                                            const scaledHeight = containerHeight * currentZoom;

                                            // El wrapper necesita tener el tamaño escalado para permitir scroll correcto
                                            wrapper.style.width = scaledWidth + 'px';
                                            wrapper.style.height = scaledHeight + 'px';
                                            wrapper.style.minWidth = scaledWidth + 'px';
                                            wrapper.style.minHeight = scaledHeight + 'px';

                                            // CORRECCIÓN: El watermarkContainer NO debe escalarse
                                            // Mantiene su tamaño base sin transform
                                            watermarkContainer.style.position = 'absolute';
                                            watermarkContainer.style.top = '0';
                                            watermarkContainer.style.left = '0';
                                            watermarkContainer.style.width = containerWidth + 'px';
                                            watermarkContainer.style.height = containerHeight + 'px';
                                            watermarkContainer.style.transform = '';
                                            watermarkContainer.style.transformOrigin = '';

                                            // CORRECCIÓN: Aplicar zoom SOLO al iframe del PDF usando transform scale
                                            // Esto evita que la marca de agua se escale
                                            const iframePdf = watermarkContainer.querySelector('iframe');
                                            if (iframePdf) {
                                                // El iframe mantiene su tamaño base
                                                iframePdf.style.width = containerWidth + 'px';
                                                iframePdf.style.height = containerHeight + 'px';
                                                // Aplicar transform scale SOLO al iframe (no al contenedor)
                                                iframePdf.style.transform = `scale(\${currentZoom})`;
                                                iframePdf.style.transformOrigin = 'top left';
                                            }

                                            // Asegurar que el contenedor tenga overflow para scroll
                                            container.style.overflow = 'auto';

                                            // Actualizar el indicador de zoom
                                            zoomLevel.textContent = Math.round(currentZoom * 100) + '%';

                                            // Actualizar marcas de agua después del zoom (sin escalar)
                                            setTimeout(() => {
                                                if (typeof crearMarcasAgua === 'function') {
                                                    crearMarcasAgua();
                                                }
                                            }, 100);

                                            // Ajustar la posición de scroll después del zoom para mantener el centro visual
                                            setTimeout(() => {
                                                // Calcular la nueva posición de scroll para mantener el centro visual
                                                const newScrollLeft = centerXBefore - (containerWidth / 2);
                                                const newScrollTop = centerYBefore - (containerHeight / 2);

                                                // Asegurar que no exceda los límites
                                                const maxScrollLeft = Math.max(0, container.scrollWidth - containerWidth);
                                                const maxScrollTop = Math.max(0, container.scrollHeight - containerHeight);

                                                container.scrollLeft = Math.max(0, Math.min(newScrollLeft, maxScrollLeft));
                                                container.scrollTop = Math.max(0, Math.min(newScrollTop, maxScrollTop));
                                            }, 50);
                                        };

                                        // Event listeners para los botones
                                        btnZoomOut.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (currentZoom > minZoom) {
                                                currentZoom = Math.max(currentZoom - 0.25, minZoom);
                                                applyZoom();
                                            }
                                        });

                                        btnZoomIn.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (currentZoom < maxZoom) {
                                                currentZoom = Math.min(currentZoom + 0.25, maxZoom);
                                                applyZoom();
                                            }
                                        });

                                        btnReset.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            currentZoom = 1.0;
                                            applyZoom();
                                        });

                                        // Agregar botones al contenedor
                                        zoomControls.appendChild(btnZoomOut);
                                        zoomControls.appendChild(zoomLevel);
                                        zoomControls.appendChild(btnZoomIn);
                                        zoomControls.appendChild(btnReset);

                                        // Agregar controles al modal body
                                        const modalBody = document.getElementById('documentoModalBody');
                                        if (modalBody) {
                                            modalBody.appendChild(zoomControls);
                                        }

                                        // Limpiar controles cuando se cierre el modal
                                        modalElement.addEventListener('hidden.bs.modal', function() {
                                            const controls = document.getElementById('zoomControls' + data.tipo);
                                            if (controls) {
                                                controls.remove();
                                            }
                                            // Resetear zoom completamente usando las referencias locales
                                            currentZoom = 1.0;
                                            zoomPdfWrapper.style.width = '100%';
                                            zoomPdfWrapper.style.height = '100%';
                                            zoomPdfWrapper.style.minWidth = '';
                                            zoomPdfWrapper.style.minHeight = '';
                                            zoomWatermarkContainer.style.position = 'relative';
                                            zoomWatermarkContainer.style.top = '';
                                            zoomWatermarkContainer.style.left = '';
                                            zoomWatermarkContainer.style.width = '100%';
                                            zoomWatermarkContainer.style.height = '100%';
                                            zoomWatermarkContainer.style.transform = '';
                                            zoomWatermarkContainer.style.transformOrigin = '';
                                            const iframePdf = zoomWatermarkContainer.querySelector('iframe');
                                            if (iframePdf) {
                                                iframePdf.style.width = '100%';
                                                iframePdf.style.height = '100%';
                                                iframePdf.style.transform = '';
                                                iframePdf.style.transformOrigin = '';
                                            }
                                        }, { once: true });

                                        // Zoom con rueda del mouse (Ctrl + scroll) usando la referencia local
                                        zoomEmbedContainer.addEventListener('wheel', function(e) {
                                            if (e.ctrlKey || e.metaKey) {
                                                e.preventDefault();
                                                if (e.deltaY < 0) {
                                                    // Scroll hacia arriba = zoom in
                                                    if (currentZoom < maxZoom) {
                                                        currentZoom = Math.min(currentZoom + 0.1, maxZoom);
                                                        applyZoom();
                                                    }
                                                } else {
                                                    // Scroll hacia abajo = zoom out
                                                    if (currentZoom > minZoom) {
                                                        currentZoom = Math.max(currentZoom - 0.1, minZoom);
                                                        applyZoom();
                                                    }
                                                }
                                            }
                                        }, { passive: false });

                                        // Aplicar zoom inicial después de que el iframe se cargue
                                        const applyInitialZoom = function() {
                                            // Pequeño delay para asegurar que el iframe esté completamente cargado y el DOM esté listo
                                            setTimeout(function() {
                                                applyZoom();
                                            }, 300);
                                        };

                                        // Aplicar zoom cuando el iframe se carga
                                        iframePdf.addEventListener('load', applyInitialZoom, { once: true });

                                        // También aplicar zoom inicial inmediatamente si el iframe ya está cargado
                                        if (iframePdf.complete || iframePdf.readyState === 'complete') {
                                            applyInitialZoom();
                                        }
                                    }

                                    // Asegurar que el contenedor esté visible
                                    embedContainer.style.display = 'block';

                                    // También prevenir click derecho en el contenedor
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);

                                    // Crear marcas de agua después de que el iframe se cargue
                                    iframePdf.addEventListener('load', function() {
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 500);
                                    });

                                    // Actualizar título del modal según el tipo
                                    const modalTitle = document.querySelector('#modalDocumento .modal-title');
                                    if (modalTitle) {
                                        modalTitle.textContent = tipoNombre[data.tipo] || data.tipo;
                                    }

                                    // Prevenir atajos de teclado para descargar
                                    if (typeof prevenirAtajosDescarga === 'function') {
                                        prevenirAtajosDescarga('modalDocumento');
                                    }

                                    // Crear marcas de agua cuando el modal se muestre completamente
                                    modalElement.addEventListener('shown.bs.modal', function() {
                                        // Llamar múltiples veces para asegurar que se generen correctamente
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 300);
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 800);
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 1500);
                                        // Llamar una vez más después de que el iframe cargue completamente
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 2500);
                                    }, { once: true });

                                    // Limpiar cuando se cierre el modal
                                    modalElement.addEventListener('hidden.bs.modal', function() {
                                        // Limpiar el contenedor
                                        const embedContainer = document.getElementById('visorPdfEmbed');
                                        if (embedContainer) {
                                            // Limpiar el src del iframe para liberar recursos
                                            const iframe = embedContainer.querySelector('iframe');
                                            if (iframe) {
                                                iframe.src = '';
                                            }

                                            // Remover los overlays de protección
                                            const protectionOverlay = document.getElementById('pdfProtectionOverlay');
                                            if (protectionOverlay) {
                                                protectionOverlay.remove();
                                            }
                                            const menuOverlay = document.getElementById('pdfMenuOverlay');
                                            if (menuOverlay) {
                                                menuOverlay.remove();
                                            }

                                            embedContainer.innerHTML = '';
                                            embedContainer.style.display = 'none';
                                        }

                                        // Asegurar que el body no tenga clases bloqueantes
                                        document.body.classList.remove('modal-open');
                                        document.body.style.overflow = '';
                                        document.body.style.paddingRight = '';

                                        // Remover overlays de Bootstrap si existen
                                        const backdrops = document.querySelectorAll('.modal-backdrop');
                                        backdrops.forEach(backdrop => backdrop.remove());
                                    }, { once: true });

                                    // Mostrar modal después de un pequeño delay para asegurar que todo esté configurado
                                    setTimeout(() => {
                                        const modal = new bootstrap.Modal(modalElement);
                                        modal.show();
                                    }, 100);

                                } else {
                                    // No debería llegar aquí - todos los tipos de PDF usan el bloque de arriba con FACTURA
                                    console.error('Tipo de documento PDF no reconocido:', data.tipo);
                                    Swal.fire('Error', 'Tipo de documento no soportado: ' + data.tipo, 'error');
                                }
                            } else {
                                // Si no es PDF pero es imagen (p. ej. EVIDENCIA .jpg/.jpeg/.png), usar visor de imagen
                                const esImagen = (data.esImagen === true) || (data.extension && ['jpg', 'jpeg', 'png', 'gif'].indexOf(String(data.extension).toLowerCase()) !== -1);
                                if (esImagen && data.url) {
                                    pdfContainer.style.display = 'none';
                                    const pdfControls = document.getElementById('pdfControls');
                                    if (pdfControls) {
                                        pdfControls.style.display = 'none';
                                    }
                                    imgContainer.style.display = 'block';
                                    const imgDocumento = document.getElementById('imgDocumento');
                                    if (imgDocumento) {
                                        imgDocumento.src = data.url;
                                        imgDocumento.style.display = 'block';
                                    }
                                    window.tipoDocumentoActual = data.tipo;
                                    window.urlDocumentoActual = data.url;
                                    window.idCreditoDocumentoActual = id;
                                    window.paginasConMediaFAD_DOC = null;
                                    if (typeof actualizarBotonVideosMedia === 'function') actualizarBotonVideosMedia();
                                    if (typeof actualizarBotonDescargarFAD === 'function') actualizarBotonDescargarFAD();
                                    if (typeof actualizarVisibilidadZoomPdfControlesEvidencia === 'function') actualizarVisibilidadZoomPdfControlesEvidencia();
                                    const modalElement = document.getElementById('modalDocumento');
                                    const modalTitle = document.querySelector('#modalDocumento .modal-title');
                                    if (modalTitle) {
                                        const tipoNombre = { 'FAD_DOC': 'FAD_DOC', 'EVIDENCIA': 'EVIDENCIA', 'FACTURA': 'FACTURA', 'CONTRATO': 'VALIDACIONES' };
                                        modalTitle.textContent = tipoNombre[data.tipo] || 'Documento';
                                    }
                                    if (modalElement) {
                                        const modal = new bootstrap.Modal(modalElement);
                                        modal.show();
                                        modalElement.addEventListener('shown.bs.modal', function() {
                                            setTimeout(function() {
                                                if (typeof crearMarcasAgua === 'function') crearMarcasAgua();
                                                if (typeof aplicarMarcasAguaEVIDENCIA === 'function') {
                                                    aplicarMarcasAguaEVIDENCIA(0);
                                                    setTimeout(function() { aplicarMarcasAguaEVIDENCIA(0); }, 350);
                                                }
                                                if (typeof desactivarDescargaImagen === 'function' && imgDocumento) desactivarDescargaImagen(imgDocumento);
                                            }, 200);
                                        }, { once: true });
                                    }
                                } else {
                                    Swal.fire('Error', 'El documento debe ser un archivo PDF o imagen válida.', 'error');
                                }
                            }

                            // Actualizar título del modal según el tipo
                            const modalTitle = document.querySelector('#modalDocumento .modal-title');
                            if (modalTitle) {
                                const tipoNombre = {
                                    'FAD_DOC': 'FAD_DOC',
                                    'EVIDENCIA': 'EVIDENCIA',
                                    'FACTURA': 'FACTURA',
                                    'CONTRATO': 'VALIDACIONES'
                                };
                                modalTitle.textContent = tipoNombre[data.tipo] || 'Documento';
                            }
                            if (typeof actualizarVisibilidadZoomPdfControlesEvidencia === 'function') actualizarVisibilidadZoomPdfControlesEvidencia();
                            if (typeof actualizarBotonDescargarFAD === 'function') actualizarBotonDescargarFAD();

                            // El modal se muestra automáticamente desde cargarPDFFactura
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Respuesta del servidor inválida. Tipo: ' + (data.tipo || 'N/A'),
                                icon: 'error'
                            });
                            console.error('Respuesta inválida:', data);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        const msg = err && err.message ? err.message : 'Error de conexión';
                        Swal.fire('Error', msg.indexOf('HTTP') === 0 ? 'El servidor respondió con error. ' + msg : 'Error de comunicación. ' + msg, 'error');
                    });
                });

            });
            </script>

JS;





        # -----------------------------
        # GET NORMAL
        # -----------------------------
        $script = str_replace('TienePermisoRegistrarDocumentos_PLACEHOLDER', json_encode($tienePermisoRegistrarDocumentos), $script);
        $script = str_replace('TienePermisoControlFAD_DOC_PLACEHOLDER', json_encode($tienePermisoControlFAD_DOC), $script);
        $script = str_replace('TienePermisoDescargarPDFFAD_DOC_PLACEHOLDER', json_encode($tienePermisoDescargarPDFFAD_DOC), $script);
        self::set("titulo", "Documentación");
        self::set("script", $script);
        return self::render("documentacion_consulta");
    }

    public static function contentDispositionInline(string $filename): string
    {
        $q = rawurlencode($filename);
        return "inline; filename=\"$filename\"; filename*=UTF-8''$q";
    }

    public static function httpGet(string $url, int $timeout = 10)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, $data];
    }

    public static function httpPostJson(string $url, array $payload, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json'
            ], $headers),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, json_decode($response, true)];
    }
public function descargar()
{
    try {

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        if (!$raw) {
            error_log("DESCARGAR - Body vacío");
            $this->registrarAuditoriaDocumento(null, '', '', 0, 'Body vacío');
            echo json_encode([
                'success' => false,
                'mensaje' => 'Body vacío'
            ]);
            exit;
        }

        $input = json_decode($raw, true);

        if (!$input) {
            error_log("DESCARGAR - JSON inválido");
            $this->registrarAuditoriaDocumento(null, '', '', 0, 'JSON inválido');
            echo json_encode([
                'success' => false,
                'mensaje' => 'JSON inválido'
            ]);
            exit;
        }

        $id   = $input['id']   ?? null;
        $tipo = strtoupper($input['tipo'] ?? '');

        if (!$id || !$tipo) {
            error_log("DESCARGAR - Parámetros incompletos: ID=$id, TIPO=$tipo");
            $this->registrarAuditoriaDocumento($id, $tipo ?: '', $tipo ?: '', 0, 'Parámetros incompletos');
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parámetros incompletos'
            ]);
            exit;
        }

        error_log("====== DESCARGAR INICIADO ======");
        error_log("BUSCANDO: ID=$id, TIPO=$tipo");

        // Nombre del documento para mensajes al usuario
        $nombresDoc = [
            'INE' => 'INE',
            'FACTURA' => 'Factura',
            'CONTRATO' => 'Validaciones',
            'FAD_DOC' => 'FAD_DOC',
            'EVIDENCIA' => 'Evidencia'
        ];
        $nombreDoc = $nombresDoc[$tipo] ?? $tipo;

        // Helper: comprobar si un archivo existe en S3 (HEAD)
        $existeEnS3 = function ($fileName) {
            $s3Url = "http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=" . urlencode($fileName);
            $ch = curl_init($s3Url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 8,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code === 200;
        };

        // ---------------- 3RA FORMA: SOLO PARA DOCUMENTOS EN oferta_documentos ----------------
        $consultarBDTerceraForma = function ($idCredito, $tipoDocumento) {
    try {
        error_log("3RA FORMA - Consultando BD via DAO: ID=$idCredito, TIPO=$tipoDocumento");

        // Mapeo correcto según tu BD
        $tiposBD = [
            'FACTURA' => 'FACTURA',      // BD: FACTURA
            'CONTRATO' => 'VALIDACIONES', // BD: VALIDACIONES
            'FAD_DOC' => 'FAD',          // BD: FAD
            'EVIDENCIA' => 'EVIDENCIA',  // BD: EVIDENCIA
        ];

        $tipoBD = $tiposBD[$tipoDocumento] ?? null;

        if (!$tipoBD) {
            error_log("3RA FORMA - Tipo no mapeado: $tipoDocumento");
            return null;
        }

        // ¡IMPORTANTE! Usar el DAO como lo hace FAD_DOC y EVIDENCIA
        // Pero con una pequeña modificación para aceptar todos los tipos

        if ($tipoDocumento === 'FAD_DOC' || $tipoDocumento === 'EVIDENCIA') {
            // Para estos tipos, usar el DAO existente
            $res = EstadoCuentaDAO::obtenerDocumentoOferta($idCredito, $tipoDocumento);
        } else {
            // Para FACTURA y CONTRATO, usar una función similar
            // O mejor, modificar el DAO para aceptar todos los tipos
            $res = $this->consultarDocumentoGenerico($idCredito, $tipoBD);
        }

        if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
            $nombreArchivo = $res['datos']['nombre_archivo'];
            error_log("3RA FORMA - Encontrado via DAO: " . $nombreArchivo);
            return $nombreArchivo;
        }

        error_log("3RA FORMA - No encontrado via DAO");
        return null;

    } catch (\Throwable $e) {
        error_log("3RA FORMA - Error en DAO: " . $e->getMessage());
        return null;
    }
};

        $buscarLocal = function ($idCredito, $tipoDocumento) {
            $directorioBase = sparta_uploads_join('documentos', 'doc_cliente');

            if (!is_dir($directorioBase)) {
                error_log("1RA FORMA - Directorio NO existe: {$directorioBase}");
                return null;
            }

            error_log("1RA FORMA - Buscando local: ID=$idCredito, TIPO=$tipoDocumento");

            $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
            $tipoSeguro = preg_replace('/[^A-Z0-9_-]/', '_', strtoupper((string)$tipoDocumento));

            if ($idSeguro === '' || $tipoSeguro === '') {
                error_log("1RA FORMA - ID o Tipo inválido");
                return null;
            }

            // Caso especial para INE: buscar frente y reverso
            // En Windows GLOB_BRACE no funciona; buscar por cada extensión y unir
            if ($tipoSeguro === 'INE') {
                $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $archivosFrente = [];
                $archivosReverso = [];
                foreach ($exts as $ext) {
                    $pf = $directorioBase . DIRECTORY_SEPARATOR . $idSeguro . '_INE_frente_*.' . $ext;
                    $pr = $directorioBase . DIRECTORY_SEPARATOR . $idSeguro . '_INE_reverso_*.' . $ext;
                    $archivosFrente = array_merge($archivosFrente, glob($pf) ?: []);
                    $archivosReverso = array_merge($archivosReverso, glob($pr) ?: []);
                }
                if (empty($archivosFrente) || empty($archivosReverso)) {
                    error_log("1RA FORMA - INE no encontrado localmente");
                    return null;
                }

                usort($archivosFrente, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });
                usort($archivosReverso, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });

                $archivoFrente = basename($archivosFrente[0]);
                $archivoReverso = basename($archivosReverso[0]);

                error_log("1RA FORMA - INE encontrado localmente: $archivoFrente, $archivoReverso");

                return [
                    'esINE' => true,
                    'archivoFrente' => $archivoFrente,
                    'archivoReverso' => $archivoReverso,
                    'urlFrente' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivoFrente),
                    'urlReverso' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivoReverso),
                    'extension' => 'jpg',
                    'esImagen' => true
                ];
            }

            // Para otros documentos, búsqueda normal
            $patron = $directorioBase . '/' . $idSeguro . '_' . $tipoSeguro . '_*.{pdf,jpg,jpeg,png}';
            $archivos = glob($patron, GLOB_BRACE);

            if (!$archivos) {
                error_log("1RA FORMA - No encontrado localmente");
                return null;
            }

            usort($archivos, function ($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });

            $rutaCompleta = $archivos[0];
            $archivo = basename($rutaCompleta);
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $esImagen = in_array($extension, ['jpg', 'jpeg', 'png'], true);

            error_log("1RA FORMA - Encontrado localmente: $archivo");

            return [
                'archivo' => $archivo,
                'extension' => $extension,
                'esImagen' => $esImagen,
                'url' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivo)
            ];
        };

        // ---------------- FACTURA ----------------
        if ($tipo === 'FACTURA') {
            error_log("=== PROCESANDO FACTURA ===");

            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("FACTURA $id - RESULTADO: 1RA FORMA (Local)");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }

            error_log("FACTURA $id - 1RA FORMA falló, probando 2DA FORMA...");
            $fileName = "FACTURA/{$id}_factura.pdf";
            if (!$existeEnS3($fileName)) {
                error_log("FACTURA $id - 2DA FORMA falló, probando 3RA FORMA...");

                $nombreBD = $consultarBDTerceraForma($id, 'FACTURA');

                if ($nombreBD) {
                    $fileNameBD = "FACTURA/" . $nombreBD;
                    if ($existeEnS3($fileNameBD)) {
                        error_log("FACTURA $id - RESULTADO: 3RA FORMA (BD + S3)");
                        $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                        $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                        echo json_encode([
                            'success' => true,
                            'tipo' => $tipo,
                            'url' => $fileUrl,
                            'archivo' => $nombreBD,
                            'carpeta' => 'FACTURA',
                            'esImagen' => false,
                            'extension' => 'pdf'
                        ]);
                        exit;
                    } else {
                        error_log("FACTURA $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                    }
                }

                error_log("FACTURA $id - RESULTADO: 3 FORMAS FALLIDAS");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, "Este ID de crédito no tiene {$nombreDoc} registrado.");
                echo json_encode([
                    'success' => false,
                    'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado."
                ]);
                exit;
            }

            error_log("FACTURA $id - RESULTADO: 2DA FORMA (S3 estándar)");
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
            $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'url' => $fileUrl,
                'archivo' => "{$id}_factura.pdf",
                'carpeta' => 'FACTURA',
                'esImagen' => false,
                'extension' => 'pdf'
            ]);
            exit;
        }

        // ---------------- CONTRATO ----------------
        elseif ($tipo === 'CONTRATO') {
            error_log("=== PROCESANDO CONTRATO/VALIDACIONES ===");

            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("CONTRATO $id - RESULTADO: 1RA FORMA (Local)");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }

            error_log("CONTRATO $id - 1RA FORMA falló, probando 2DA FORMA...");
            $fileName = "VALIDACIONES/{$id}_validaciones.pdf";
            if (!$existeEnS3($fileName)) {
                error_log("CONTRATO $id - 2DA FORMA falló, probando 3RA FORMA...");

                $nombreBD = $consultarBDTerceraForma($id, 'CONTRATO');

                if ($nombreBD) {
                    $fileNameBD = "VALIDACIONES/" . $nombreBD;
                    if ($existeEnS3($fileNameBD)) {
                        error_log("CONTRATO $id - RESULTADO: 3RA FORMA (BD + S3)");
                        $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                        $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                        echo json_encode([
                            'success' => true,
                            'tipo' => $tipo,
                            'url' => $fileUrl,
                            'archivo' => $nombreBD,
                            'carpeta' => 'VALIDACIONES',
                            'esImagen' => false,
                            'extension' => 'pdf'
                        ]);
                        exit;
                    } else {
                        error_log("CONTRATO $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                    }
                }

                error_log("CONTRATO $id - RESULTADO: 3 FORMAS FALLIDAS");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, "Este ID de crédito no tiene {$nombreDoc} registrado.");
                echo json_encode([
                    'success' => false,
                    'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado."
                ]);
                exit;
            }

            error_log("CONTRATO $id - RESULTADO: 2DA FORMA (S3 estándar)");
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
            $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'url' => $fileUrl,
                'archivo' => "{$id}_validaciones.pdf",
                'carpeta' => 'VALIDACIONES',
                'esImagen' => false,
                'extension' => 'pdf'
            ]);
            exit;
        }

        // ---------------- INE ----------------
        elseif ($tipo === 'INE') {
            error_log("=== PROCESANDO INE ===");

            // Buscar primero localmente
            $local = $buscarLocal($id, $tipo);
            if ($local && isset($local['esINE']) && $local['esINE'] === true) {
                error_log("INE $id - RESULTADO: 1RA FORMA (Local)");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                echo json_encode([
                    'success' => true,
                    'tipo' => 'INE',
                    'frente' => $local['urlFrente'],
                    'reverso' => $local['urlReverso'],
                    'archivoFrente' => $local['archivoFrente'],
                    'archivoReverso' => $local['archivoReverso']
                ]);
                exit;
            }

            // Si no existe local, buscar en API externo y S3 (2da forma)
            error_log("INE $id - 1RA FORMA falló, probando 2DA FORMA (API + S3)...");
            $endpoint = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";
            $token    = "__SPARTA_TOKEN_REDACTED__";

            $payload = json_encode([
                "idCredito"  => (int)$id,
                "fechaCorte" => date("Y-m-d")
            ]);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "Token: {$token}"
                ],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 10
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (
                !$data ||
                !isset($data['estadoCuenta']['datosCliente']['idCliente'])
            ) {
                error_log("INE $id - 2DA FORMA falló (API), probando 3RA FORMA (persona_documentos)...");
                $resINE = EstadoCuentaDAO::obtenerINEPersonaDocumentos($id);
                if (!empty($resINE['success']) && !empty($resINE['datos']['archivo_ine_frente']) && !empty($resINE['datos']['archivo_ine_reverso'])) {
                    error_log("INE $id - RESULTADO: 3RA FORMA (persona_documentos)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $base = '/estadocuenta/servirINEPersonaDocumento?id=' . urlencode($id) . '&lado=';
                    echo json_encode([
                        'success' => true,
                        'tipo' => 'INE',
                        'frente' => $base . 'frente',
                        'reverso' => $base . 'reverso'
                    ]);
                    exit;
                }
                error_log("INE $id - 3RA FORMA falló, sin INE registrado");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, 'Este ID de crédito no tiene INE registrado.');
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Este ID de crédito no tiene INE registrado.'
                ]);
                exit;
            }

            $idCliente = $data['estadoCuenta']['datosCliente']['idCliente'];

            // URLs directas para frente y reverso del INE (2da forma)
            $urlFrente = "http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_frente.jpeg";
            $urlReverso = "http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_reverso.jpeg";

            // Comprobar que las imágenes INE existan en S3
            $chF = curl_init($urlFrente);
            curl_setopt_array($chF, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 8]);
            curl_exec($chF);
            $codeF = (int) curl_getinfo($chF, CURLINFO_HTTP_CODE);
            curl_close($chF);
            $chR = curl_init($urlReverso);
            curl_setopt_array($chR, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 8]);
            curl_exec($chR);
            $codeR = (int) curl_getinfo($chR, CURLINFO_HTTP_CODE);
            curl_close($chR);

            if ($codeF !== 200 || $codeR !== 200) {
                error_log("INE $id - 2DA FORMA falló (imágenes no encontradas), probando 3RA FORMA (persona_documentos)...");
                $resINE = EstadoCuentaDAO::obtenerINEPersonaDocumentos($id);
                if (!empty($resINE['success']) && !empty($resINE['datos']['archivo_ine_frente']) && !empty($resINE['datos']['archivo_ine_reverso'])) {
                    error_log("INE $id - RESULTADO: 3RA FORMA (persona_documentos)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $base = '/estadocuenta/servirINEPersonaDocumento?id=' . urlencode($id) . '&lado=';
                    echo json_encode([
                        'success' => true,
                        'tipo' => 'INE',
                        'frente' => $base . 'frente',
                        'reverso' => $base . 'reverso'
                    ]);
                    exit;
                }
                error_log("INE $id - 2DA FORMA falló (imágenes no encontradas), 3RA FORMA no disponible");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, 'Este ID de crédito no tiene INE registrado.');
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Este ID de crédito no tiene INE registrado.'
                ]);
                exit;
            }

            error_log("INE $id - RESULTADO: 2DA FORMA (API + S3)");
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
            echo json_encode([
                'success' => true,
                'tipo' => 'INE',
                'frente' => '/estadocuenta/verDocumento?fileName=' . urlencode("INE/{$idCliente}_frente.jpeg"),
                'reverso' => '/estadocuenta/verDocumento?fileName=' . urlencode("INE/{$idCliente}_reverso.jpeg")
            ]);
            exit;
        }

        // ---------------- FAD_DOC ----------------
        elseif ($tipo === 'FAD_DOC') {
            error_log("=== PROCESANDO FAD_DOC ===");

            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("FAD_DOC $id - RESULTADO: 1RA FORMA (Local)");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }

            // Primero intentar DAO (2da forma actual)
            error_log("FAD_DOC $id - 1RA FORMA falló, probando 2DA FORMA (DAO)...");
            try {
                $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, $tipo);
            } catch (\Throwable $e) {
                error_log("FAD_DOC $id - Error en DAO: " . $e->getMessage());
                $res = ['success' => false];
            }

            if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
                $archivo = basename($res['datos']['nombre_archivo']);
                $archivo = str_replace(['doc_cliente/', 'doc_cliente\\'], '', $archivo);
                $archivo = basename($archivo);

                $carpeta = 'FAD';
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

                $fileName = "{$carpeta}/{$archivo}";
                if ($existeEnS3($fileName)) {
                    error_log("FAD_DOC $id - RESULTADO: 2DA FORMA (DAO)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);

                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $archivo,
                        'carpeta' => $carpeta,
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                }
                error_log("FAD_DOC $id - DAO devolvió nombre pero S3 no responde 200 (HEAD): $fileName; probando 3RA FORMA...");
            }

            // Si DAO falla o el archivo no está en S3, intentar 3RA FORMA
            error_log("FAD_DOC $id - 2DA FORMA (DAO) falló, probando 3RA FORMA...");
            $nombreBD = $consultarBDTerceraForma($id, 'FAD_DOC');

            if ($nombreBD) {
                $fileNameBD = "FAD/" . $nombreBD;
                if ($existeEnS3($fileNameBD)) {
                    error_log("FAD_DOC $id - RESULTADO: 3RA FORMA (BD + S3)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $extension = strtolower(pathinfo($nombreBD, PATHINFO_EXTENSION));
                    $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $nombreBD,
                        'carpeta' => 'FAD',
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                } else {
                    error_log("FAD_DOC $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                }
            }

            error_log("FAD_DOC $id - RESULTADO: 3 FORMAS FALLIDAS");
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación.");
            echo json_encode([
                'success' => false,
                'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación."
            ]);
            exit;
        }

        // ---------------- EVIDENCIA ----------------
        elseif ($tipo === 'EVIDENCIA') {
            error_log("=== PROCESANDO EVIDENCIA ===");

            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("EVIDENCIA $id - RESULTADO: 1RA FORMA (Local)");
                $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }

            // Primero intentar DAO (2da forma actual)
            error_log("EVIDENCIA $id - 1RA FORMA falló, probando 2DA FORMA (DAO)...");
            try {
                $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, $tipo);
            } catch (\Throwable $e) {
                error_log("EVIDENCIA $id - Error en DAO: " . $e->getMessage());
                $res = ['success' => false];
            }

            if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
                $archivo = basename($res['datos']['nombre_archivo']);
                $archivo = str_replace(['doc_cliente/', 'doc_cliente\\'], '', $archivo);
                $archivo = basename($archivo);

                $carpeta = 'EVIDENCIA';
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

                $fileName = "{$carpeta}/{$archivo}";
                if ($existeEnS3($fileName)) {
                    error_log("EVIDENCIA $id - RESULTADO: 2DA FORMA (DAO)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);

                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $archivo,
                        'carpeta' => $carpeta,
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                }
                error_log("EVIDENCIA $id - DAO devolvió nombre pero S3 no responde 200 (HEAD): $fileName; probando 3RA FORMA...");
            }

            // Si DAO falla o el archivo no está en S3, intentar 3RA FORMA
            error_log("EVIDENCIA $id - 2DA FORMA (DAO) falló, probando 3RA FORMA...");
            $nombreBD = $consultarBDTerceraForma($id, 'EVIDENCIA');

            if ($nombreBD) {
                $fileNameBD = "EVIDENCIA/" . $nombreBD;
                if ($existeEnS3($fileNameBD)) {
                    error_log("EVIDENCIA $id - RESULTADO: 3RA FORMA (BD + S3)");
                    $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 1, null);
                    $extension = strtolower(pathinfo($nombreBD, PATHINFO_EXTENSION));
                    $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $nombreBD,
                        'carpeta' => 'EVIDENCIA',
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                } else {
                    error_log("EVIDENCIA $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                }
            }

            error_log("EVIDENCIA $id - RESULTADO: 3 FORMAS FALLIDAS");
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación.");
            echo json_encode([
                'success' => false,
                'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación."
            ]);
            exit;
        }

        // ---------------- Tipo no válido ----------------
        else {
            error_log("TIPO NO VÁLIDO: $tipo");
            $this->registrarAuditoriaDocumento($id ?? null, $tipo ?? '', $nombreDoc ?? $tipo ?? '', 0, 'Tipo de documento no válido. Tipos permitidos: FACTURA, CONTRATO, INE, FAD_DOC, EVIDENCIA');
            echo json_encode([
                'success' => false,
                'mensaje' => 'Tipo de documento no válido. Tipos permitidos: FACTURA, CONTRATO, INE, FAD_DOC, EVIDENCIA'
            ]);
            exit;
        }

    } catch (\Throwable $e) {
        error_log("ERROR CRÍTICO en descargar(): " . $e->getMessage());
        if (isset($id) && isset($tipo) && isset($nombreDoc)) {
            $this->registrarAuditoriaDocumento($id, $tipo, $nombreDoc, 0, 'Error interno: ' . $e->getMessage());
        } else {
            $this->registrarAuditoriaDocumento(null, '', '', 0, 'Error interno: ' . $e->getMessage());
        }
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error interno',
            'debug'   => $e->getMessage()
        ]);
        exit;
    }
}

    /**
     * Devuelve ruta a un archivo PDF de FAD_DOC para el id dado.
     * Retorna ['path' => ruta absoluta, 'isTemp' => true si hay que borrarla después] o null.
     * Público para uso desde Sabueso (extracción Información de Ingresos).
     */
    public function getRutaPdfFAD_DOC($idCredito)
    {
        return $this->getPdfPathForFAD_DOC($idCredito);
    }

    /**
     * Devuelve ruta local o temporal del documento FACTURA para un crédito.
     * Público para reutilizar extracción de datos moto en Mis adjudicaciones.
     *
     * @return array{path: string, isTemp: bool}|null
     */
    public function getRutaPdfFactura($idCredito)
    {
        return $this->getPdfPathForFactura((int) $idCredito);
    }

    /**
     * Devuelve ruta a un archivo PDF de FAD_DOC para el id dado.
     * Retorna ['path' => ruta absoluta, 'isTemp' => true si hay que borrarla después] o null.
     */
    private function getPdfPathForFAD_DOC($idCredito)
    {
        $id = (int) $idCredito;
        if ($id <= 0) return null;
        $dirLocal = sparta_uploads_join('documentos', 'doc_cliente');
        $patron = $dirLocal . '/' . $id . '_FAD_DOC_*.pdf';
        $archivos = glob($patron);
        if ($archivos && count($archivos) > 0) {
            usort($archivos, fn($a, $b) => filemtime($b) <=> filemtime($a));
            return ['path' => $archivos[0], 'isTemp' => false];
        }
        try {
            $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, 'FAD_DOC');
            if (!empty($res['success']) && !empty($res['datos']['nombre_archivo'])) {
                $archivo = basename(str_replace(['doc_cliente/', 'doc_cliente\\'], '', $res['datos']['nombre_archivo']));
                $fileName = 'FAD/' . $archivo;
                $s3Url = "http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=" . urlencode($fileName);
                $ch = curl_init($s3Url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 45,
                ]);
                $data = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($code === 200 && $data !== false && strlen($data) > 0) {
                    $tmpDir = __DIR__ . '/../storage/tmp_media';
                    if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
                    $tmpFile = $tmpDir . '/pdf_' . $id . '_' . uniqid() . '.pdf';
                    if (file_put_contents($tmpFile, $data) !== false) {
                        return ['path' => $tmpFile, 'isTemp' => true];
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("getPdfPathForFAD_DOC: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Resuelve ruta local o archivo temporal descargado desde S3 para FACTURA (misma lógica que descargar JSON).
     *
     * @return array{path: string, isTemp: bool}|null
     */
    private function getPdfPathForFactura(int $idCredito)
    {
        $id = (int) $idCredito;
        if ($id <= 0) {
            return null;
        }

        $directorioBase = sparta_uploads_join('documentos', 'doc_cliente');
        if (is_dir($directorioBase)) {
            $idSeguro = preg_replace('/[^0-9]/', '', (string) $id);
            if ($idSeguro !== '') {
                $patron = $directorioBase . DIRECTORY_SEPARATOR . $idSeguro . '_FACTURA_*.{pdf,jpg,jpeg,png}';
                $archivos = glob($patron, GLOB_BRACE);
                if ($archivos && count($archivos) > 0) {
                    usort($archivos, fn ($a, $b) => filemtime($b) <=> filemtime($a));
                    return ['path' => $archivos[0], 'isTemp' => false];
                }
            }
        }

        $existeEnS3 = static function ($fileName) {
            $s3Url = 'http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=' . urlencode($fileName);
            $ch = curl_init($s3Url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 8,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code === 200;
        };

        $pullS3 = function ($fileName) use ($id) {
            $s3Url = 'http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=' . urlencode($fileName);
            $ch = curl_init($s3Url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 45,
            ]);
            $data = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 200 || $data === false || strlen($data) === 0) {
                return null;
            }
            $tmpDir = __DIR__ . '/../storage/tmp_media';
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0755, true);
            }
            $safeBase = preg_replace('/[^\w\.\-]/', '_', basename($fileName));
            $tmpFile = $tmpDir . '/factura_' . $id . '_' . uniqid() . '_' . $safeBase;
            if (file_put_contents($tmpFile, $data) === false) {
                return null;
            }
            return $tmpFile;
        };

        $fileName = 'FACTURA/' . $id . '_factura.pdf';
        if ($existeEnS3($fileName)) {
            $tmp = $pullS3($fileName);
            if ($tmp) {
                return ['path' => $tmp, 'isTemp' => true];
            }
        }

        try {
            $res = $this->consultarDocumentoGenerico($id, 'FACTURA');
            if (!empty($res['success']) && !empty($res['datos']['nombre_archivo'])) {
                $nombreBD = basename(str_replace(['\\', '/'], '/', (string) $res['datos']['nombre_archivo']));
                if ($nombreBD !== '') {
                    $fileNameBD = 'FACTURA/' . $nombreBD;
                    if ($existeEnS3($fileNameBD)) {
                        $tmp = $pullS3($fileNameBD);
                        if ($tmp) {
                            return ['path' => $tmp, 'isTemp' => true];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('getPdfPathForFactura: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Descargar archivo de factura (PDF o imagen según origen). Requiere permiso módulo 24 (misma regla que FAD_DOC).
     * GET: id = id_credito
     */
    public function descargarPdfFactura()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => 'ID de crédito no válido']);
            exit;
        }
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $modulos = $idUsuario ? LoginDAO::getModulosUsuario($idUsuario) : [];
        if (!in_array(24, $modulos)) {
            header('Location: /inicio');
            exit;
        }
        $info = $this->getPdfPathForFactura($id);
        if (!$info || empty($info['path']) || !is_file($info['path'])) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => 'No se encontró la factura para este crédito.']);
            exit;
        }
        $path = $info['path'];
        $baseNombre = basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if ($ext === 'pdf') {
            $mime = 'application/pdf';
        } elseif (in_array($ext, ['jpg', 'jpeg'], true)) {
            $mime = 'image/jpeg';
        } elseif ($ext === 'png') {
            $mime = 'image/png';
        } elseif ($ext === 'gif') {
            $mime = 'image/gif';
        }
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^\w\.\-]/', '_', $baseNombre) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($path);
        if (!empty($info['isTemp'])) {
            @unlink($path);
        }
        exit;
    }

    /**
     * Descargar PDF FAD_DOC. Requiere permiso módulo 24 (Descargar PDF FAD_DOC).
     * GET: id = id_credito
     */
    public function descargarPdfFAD_DOC()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => 'ID de crédito no válido']);
            exit;
        }
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $modulos = $idUsuario ? LoginDAO::getModulosUsuario($idUsuario) : [];
        if (!in_array(24, $modulos)) {
            header('Location: /inicio');
            exit;
        }
        $info = $this->getPdfPathForFAD_DOC($id);
        if (!$info || empty($info['path']) || !is_file($info['path'])) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => 'No se encontró el documento FAD_DOC para este crédito.']);
            exit;
        }
        $path = $info['path'];
        $nombre = basename($path);
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^\w\.\-]/', '_', $nombre) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($path);
        if (!empty($info['isTemp'])) {
            @unlink($path);
        }
        exit;
    }

    /**
     * API: páginas del PDF FAD_DOC que tienen vídeo/audio embebido.
     * Cualquier usuario con acceso al documento puede llamarla (para habilitar/deshabilitar el botón de videos).
     * El permiso 22 solo controla la descarga en extraerVideosDocumento().
     */
    public function paginasConMedia()
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = isset($_GET['idCredito']) ? (int) $_GET['idCredito'] : (int) ($_POST['idCredito'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'idCredito requerido']);
            exit;
        }
        $info = $this->getPdfPathForFAD_DOC($id);
        if (!$info) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo obtener el PDF']);
            exit;
        }
        $script = __DIR__ . '/../scripts/pdf_media.py';
        if (!is_file($script)) {
            if ($info['isTemp']) @unlink($info['path']);
            echo json_encode(['success' => false, 'mensaje' => 'Script de inspección no disponible']);
            exit;
        }
        $path = $info['path'];
        $result = $this->ejecutarPdfMediaInspect($script, $path);
        $out = is_array($result) ? ($result['stdout'] ?? '') : (string) $result;

        if ($info['isTemp']) @unlink($path);
        if ($out === null || $out === '') {
            echo json_encode(['success' => true, 'paginasConMedia' => []]);
            exit;
        }
        $json = @json_decode(trim($out), true);
        if (!$json || !isset($json['paginasConMedia'])) {
            echo json_encode(['success' => true, 'paginasConMedia' => []]);
            exit;
        }
        echo json_encode(['success' => true, 'paginasConMedia' => $json['paginasConMedia']]);
        exit;
    }

    /**
     * Detecta la ruta del ejecutable de Python (cacheada).
     * 1) Si en config.ini existe [pdf_media] python_path (ruta absoluta), se usa esa.
     * 2) Si no, se intenta detectar con shell: py -3.12, py -3, py, python3, python.
     * En el servidor, si "python" no está en PATH, añada en config.ini la ruta.
     */
    private static function getPythonExecutable()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }
        $configFile = __DIR__ . '/../config/config.ini';
        if (is_file($configFile)) {
            $config = @parse_ini_file($configFile, true);
            if (is_array($config)) {
                $path = trim($config['pdf_media']['python_path'] ?? $config['python']['python_path'] ?? '');
                if ($path !== '' && @is_file($path)) {
                    $cached = $path;
                    return $path;
                }
            }
        }
        $candidates = [
            'py -3.12 -c "import sys; print(sys.executable)"',
            'py -3 -c "import sys; print(sys.executable)"',
            'py -c "import sys; print(sys.executable)"',
            'python3 -c "import sys; print(sys.executable)"',
            'python -c "import sys; print(sys.executable)"',
        ];
        foreach ($candidates as $cmd) {
            $out = @shell_exec($cmd . ' 2>&1');
            $path = $out ? trim($out) : '';
            if ($path !== '' && (stripos($path, 'python') !== false || substr($path, -4) === '.exe')) {
                if (@is_file($path)) {
                    $cached = $path;
                    return $path;
                }
            }
        }
        $cached = '';
        return null;
    }

    /**
     * Ejecuta pdf_media.py --inspect para obtener páginas con medios. Usa proc_open con ruta de Python detectada o fallback shell_exec.
     * Devuelve array ['stdout' => string, 'stderr' => string] para poder registrar errores de Python; si solo se necesita el texto, usar ['stdout'].
     */
    private function ejecutarPdfMediaInspect($scriptPath, $pdfPath)
    {
        $rutaPdf = realpath($pdfPath);
        if ($rutaPdf === false || !is_file($rutaPdf)) {
            return ['stdout' => '', 'stderr' => 'PDF no encontrado o ruta inválida'];
        }
        $rutaScript = realpath($scriptPath);
        if ($rutaScript === false || !is_file($rutaScript)) {
            return ['stdout' => '', 'stderr' => 'Script pdf_media.py no encontrado'];
        }
        $interpretes = array_filter(array_merge(
            [self::getPythonExecutable()],
            ['py', 'python3', 'python']
        ));
        foreach ($interpretes as $python) {
            if ($python === null || $python === '') {
                continue;
            }
            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $proc = @proc_open(
                [$python, $rutaScript, '--inspect', $rutaPdf],
                $descriptorSpec,
                $pipes,
                null,
                null
            );
            if (!is_resource($proc)) {
                continue;
            }
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
            $stdout = ($stdout !== false) ? $stdout : '';
            $stderr = ($stderr !== false) ? $stderr : '';
            if ($stdout !== '' && trim($stdout) !== '') {
                return ['stdout' => $stdout, 'stderr' => $stderr];
            }
        }
        // Fallback: shell_exec con ruta de Python detectada o comandos por nombre
        $pythonPath = self::getPythonExecutable();
        $escScript = escapeshellarg($rutaScript);
        $escPdf = escapeshellarg($rutaPdf);
        if ($pythonPath !== null) {
            $cmd = escapeshellarg($pythonPath) . ' ' . $escScript . ' --inspect ' . $escPdf . ' 2>&1';
            $out = @shell_exec($cmd);
            if ($out !== null && $out !== '' && trim($out) !== '') {
                return ['stdout' => $out, 'stderr' => ''];
            }
        }
        $out = @shell_exec('py ' . $escScript . ' --inspect ' . $escPdf . ' 2>&1');
        if ($out !== null && $out !== '' && trim($out) !== '') {
            return ['stdout' => $out, 'stderr' => ''];
        }
        $out = @shell_exec('python ' . $escScript . ' --inspect ' . $escPdf . ' 2>&1');
        return [
            'stdout' => ($out !== null && $out !== '') ? trim($out) : '',
            'stderr' => ''
        ];
    }

    /**
     * API: extrae vídeos/audio del PDF FAD_DOC (opcionalmente de una página). Cualquier usuario con acceso a Documentación puede ver; el permiso 22 solo controla si puede descargar.
     */
    public function extraerVideosDocumento()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $input = $raw ? json_decode($raw, true) : $_POST;
        $id = isset($input['idCredito']) ? (int) $input['idCredito'] : 0;
        $pagina = isset($input['pagina']) ? (int) $input['pagina'] : null;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'idCredito requerido']);
            exit;
        }
        $info = $this->getPdfPathForFAD_DOC($id);
        if (!$info) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo obtener el PDF']);
            exit;
        }
        $script = __DIR__ . '/../scripts/pdf_media.py';
        $tmpDir = __DIR__ . '/../storage/tmp_media';
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
        $reqId = 'm' . $id . '_' . ($pagina ?: 'all') . '_' . uniqid();
        $outdir = $tmpDir . '/' . $reqId;
        if (!is_file($script)) {
            if ($info['isTemp']) @unlink($info['path']);
            echo json_encode(['success' => false, 'mensaje' => 'Script de extracción no disponible']);
            exit;
        }
        $pythonPath = self::getPythonExecutable();
        if ($pythonPath === null) {
            if ($info['isTemp']) @unlink($info['path']);
            echo json_encode(['success' => false, 'mensaje' => 'Python no encontrado. Configure [pdf_media] python_path en backend/config/config.ini con la ruta al ejecutable (ver backend/scripts/README.md).']);
            exit;
        }
        $cmd = escapeshellarg($pythonPath) . ' ' . escapeshellarg($script) . ' --extract ' . escapeshellarg($info['path']) . ' --outdir ' . escapeshellarg($outdir);
        if ($pagina > 0) $cmd .= ' --page ' . (int) $pagina;
        $cmd .= ' 2>&1';
        $out = @shell_exec($cmd);
        if ($info['isTemp']) @unlink($info['path']);
        $json = $out ? @json_decode(trim($out), true) : null;
        if (!$json || empty($json['archivos'])) {
            @array_map('unlink', glob($outdir . '/*') ?: []);
            @rmdir($outdir);
            echo json_encode(['success' => true, 'videos' => []]);
            exit;
        }
        $videos = [];
        foreach ($json['archivos'] as $i => $a) {
            $nombre = $a['nombre'] ?? ('media_' . $i);
            $videos[] = [
                'nombre' => $nombre,
                'url' => '/EstadoCuenta/verMediaExtraida?token=' . urlencode($reqId) . '&f=' . $i,
            ];
        }
        if (!isset($_SESSION['media_tokens'])) $_SESSION['media_tokens'] = [];
        $_SESSION['media_tokens'][$reqId] = ['dir' => $outdir, 'archivos' => array_column($json['archivos'], 'nombre')];
        echo json_encode(['success' => true, 'videos' => $videos]);
        exit;
    }

    /**
     * Sirve un archivo de media extraído (token + índice). Los tokens se guardan en sesión.
     */
    public function verMediaExtraida()
    {
        $token = $_GET['token'] ?? '';
        $f = isset($_GET['f']) ? (int) $_GET['f'] : -1;
        if ($token === '' || $f < 0) {
            http_response_code(400);
            exit;
        }
        $tokens = $_SESSION['media_tokens'][$token] ?? null;
        if (!$tokens || !is_dir($tokens['dir']) || !isset($tokens['archivos'][$f])) {
            http_response_code(404);
            exit;
        }
        $path = $tokens['dir'] . '/' . $tokens['archivos'][$f];
        if (!is_file($path) || strpos(realpath($path), realpath($tokens['dir'])) !== 0) {
            http_response_code(404);
            exit;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimes = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo'];
        $mime = $mimes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $modulos = $idUsuario ? LoginDAO::getModulosUsuario($idUsuario) : [];
        $puedeDescargar = in_array(22, $modulos);
        $forzarDescarga = !empty($_GET['descargar']);
        if ($forzarDescarga && $puedeDescargar) {
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        } else {
            header('Content-Disposition: inline; filename="' . basename($path) . '"');
        }
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function AddNote()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_NOTAS_CLIENTE);
        $input = json_decode(file_get_contents("php://input"), true);

        $data = [
            'id_credito' => (int)($input['id_credito'] ?? 0),
            'nota'       => trim($input['nota'] ?? ''),
            'usuario'    => $_SESSION['usuario'] ?? 'Sistema',
            'usuario_id'    => $_SESSION['usuario_id'] ?? '1'
        ];

        EstadoCuentaDAO::insertNotas($data);

        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'Nota agregada correctamente.',
            'data' => [
                'usuario' => $_SESSION['usuario'] ?? 'Operador',
            ],
        ]);
    }

    public function getNotasCredito()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_NOTAS_CLIENTE);
        $input = json_decode(file_get_contents("php://input"), true);

        $idCredito = $input['idCredito'] ?? null;

        if (empty($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de credito requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getNotasCredito($idCredito);

        self::respuestaJSON($resultado);
    }


    public function getGastosCobranza()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA);
        $input = json_decode(file_get_contents("php://input"), true);
        $idCredito = $input['idCredito'] ?? null;

        if (empty($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de crédito requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getGastosCobranza($idCredito);
        self::respuestaJSON($resultado);
    }

    /**
     * Catálogo de motivos de condonación (gastos de cobranza) para el modal o integraciones.
     * GET/POST: sin cuerpo; la sesión aplica.
     */
    public function getCatalogoMotivosCondonacion()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA);
        self::respuestaJSON([
            'success' => true,
            'datos'   => EstadoCuentaDAO::getCatalogoMotivosCondonacion(),
        ]);
    }

    /**
     * Guarda aclaración GC en __SPARTA_SECRET_REDACTED__.cobranza_gc_verificacion_semana (permiso módulo 30 o usuario 1).
     * POST JSON: id_credito, nombre, tipo_reporte (error|falta_aplicar), monto, mensaje (observaciones).
     * id_usuario_reporte en BD = usuario_id de la sesión (quien guarda; no se toma del cliente).
     * estatus en BD = 3 (reportado por call center / flujo estado de cuenta).
     */
    public function GuardarAclaracionGc()
    {
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        if ($idUsuario !== 1 && !in_array(30, $mods, true)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para registrar aclaraciones.']);
        }

        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $idCredito = $this->safe_int($input['id_credito'] ?? $input['idCredito'] ?? 0, 0);
        if ($idCredito <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.']);
        }

        $nombre = trim((string) ($input['nombre'] ?? ''));
        $tipo = (string) ($input['tipo_reporte'] ?? '');
        $monto = $this->safe_float($input['monto'] ?? 0, 0);
        $mensaje = trim((string) ($input['mensaje'] ?? $input['observaciones'] ?? ''));

        $celula = EstadoCuentaDAO::obtenerCelulaUltimaGastoCobranza($idCredito);

        $resultado = EstadoCuentaDAO::insertAclaracionGcVerificacionSemana([
            'id_credito'           => $idCredito,
            'nombre'               => $nombre,
            'tipo_reporte'         => $tipo,
            'monto'                => $monto,
            'mensaje'              => $mensaje,
            'estatus'              => 3,
            'celula'               => $celula,
            'id_usuario_reporte'   => $idUsuario > 0 ? $idUsuario : null,
        ]);

        if (!empty($resultado['success'])) {
            self::respuestaJSON([
                'success' => true,
                'mensaje' => $resultado['mensaje'] ?? 'Registro guardado.',
            ]);
        }
        $fail = [
            'success' => false,
            'mensaje' => $resultado['mensaje'] ?? 'No se pudo guardar.',
            'error'   => $resultado['error'] ?? null,
        ];
        if (!empty($resultado['datos']['alerta'])) {
            $fail['alerta'] = $resultado['datos']['alerta'];
        }
        self::respuestaJSON($fail);
    }

    /**
     * Guarda la condonación parcial de un gasto de cobranza (monto + motivo).
     * POST: id_gastos_cobranza, monto_parcial, motivo
     */
    public function guardarCondonacionParcialGasto()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA);
        $input = json_decode(file_get_contents("php://input"), true);
        $idGasto = $input['id_gastos_cobranza'] ?? null;
        $montoParcial = $input['monto_parcial'] ?? null;
        $motivo = $input['motivo'] ?? '';

        if (empty($idGasto)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de gasto requerido']);
            return;
        }

        $resultado = EstadoCuentaDAO::updateCondonacionParcialGasto($idGasto, $montoParcial, $motivo);
        self::respuestaJSON($resultado);
    }


    public function getTiposContacto()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $resultado = EstadoCuentaDAO::getTiposContacto();
        self::respuestaJSON($resultado);
    }

    public function getResultadosContacto()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $input = json_decode(file_get_contents("php://input"), true);
        $tipoContactoId = $input['tipo_contacto_id'] ?? null;

        if (empty($tipoContactoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'tipo_contacto_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getResultadosContacto($tipoContactoId);
        self::respuestaJSON($resultado);
    }

    public function getDictamenes()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $input = json_decode(file_get_contents("php://input"), true);
        $resultadoContactoId = $input['resultado_contacto_id'] ?? null;

        if (empty($resultadoContactoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'resultado_contacto_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getDictamenes($resultadoContactoId);
        self::respuestaJSON($resultado);
    }

    public function getMotivosNoPago()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $resultado = EstadoCuentaDAO::getMotivosNoPago();
        self::respuestaJSON($resultado);
    }

    public function getPlataformas()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $resultado = EstadoCuentaDAO::getPlataformas();
        self::respuestaJSON($resultado);
    }

    public function getTiposMotivoNoPago()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $resultado = EstadoCuentaDAO::getTiposMotivoNoPago();
        self::respuestaJSON($resultado);
    }

    public function getMotivosNoPagoPorTipo()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $input = json_decode(file_get_contents("php://input"), true);
        $tipoId = $input['tipo_motivo_id'] ?? null;

        if (empty($tipoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'tipo_motivo_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getMotivosNoPagoPorTipo($tipoId);
        self::respuestaJSON($resultado);
    }

    /**
     * Catálogos del modal Dictaminar llamada en un solo JSON (tipos contacto, plataformas, tipos motivo no pago).
     * GET — precarga al cargar estado de cuenta.
     */
    public function getCatalogosDictamenModal()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $tipos = EstadoCuentaDAO::getTiposContacto();
        $plat = EstadoCuentaDAO::getPlataformas();
        $tiposMot = EstadoCuentaDAO::getTiposMotivoNoPago();
        if (empty($tipos['success']) || empty($plat['success']) || empty($tiposMot['success'])) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al cargar catálogos del dictamen',
            ]);
            return;
        }
        self::respuestaJSON([
            'success' => true,
            'datos' => [
                'tipos_contacto' => $tipos['datos'] ?? [],
                'plataformas' => $plat['datos'] ?? [],
                'tipos_motivo_no_pago' => $tiposMot['datos'] ?? [],
            ],
        ]);
    }

    /**
     * Opciones del select "Llamada a" en el modal Dictaminar llamada (titular, referencias, extras, Otros).
     * POST JSON: id_credito
     */
    public function getOpcionesContactoDictamenLlamada()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $input = json_decode(file_get_contents('php://input'), true);
        $idCredito = $input['id_credito'] ?? null;
        if (empty($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'id_credito requerido',
            ]);
            return;
        }
        $resultado = EstadoCuentaDAO::getOpcionesContactoDictamenLlamada((int)$idCredito);
        self::respuestaJSON($resultado);
    }

    public function guardarDictamen()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        $input = json_decode(file_get_contents("php://input"), true);

        $llamadaOrigenIn = trim((string)($input['llamada_origen'] ?? ''));
        $llamadaTelefono = trim((string)($input['llamada_telefono'] ?? ''));
        $llamadaNombre = trim((string)($input['llamada_nombre_persona'] ?? ''));
        $llamadaParentesco = trim((string)($input['llamada_parentesco'] ?? ''));
        $llamadaOrigenDb = $llamadaOrigenIn;
        $idCreditoPost = (int)($input['id_credito'] ?? 0);

        if ($llamadaOrigenIn === 'otros') {
            $ct = trim((string)($input['contacto_extra_telefono'] ?? ''));
            $cp = trim((string)($input['contacto_extra_parentesco'] ?? ''));
            $cn = trim((string)($input['contacto_extra_nombre'] ?? ''));
            if ($ct === '' || $cp === '' || $cn === '') {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Complete teléfono, parentesco y nombre completo del contacto',
                ]);
                exit;
            }
            $llamadaOrigenDb = 'otros';
            $llamadaTelefono = $ct;
            $llamadaNombre = $cn;
            $llamadaParentesco = $cp;
        } elseif (strpos($llamadaOrigenIn, 'prev:') === 0) {
            $prevId = (int)substr($llamadaOrigenIn, 5);
            if ($prevId <= 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Contacto guardado inválido',
                ]);
                exit;
            }
            $snap = EstadoCuentaDAO::obtenerSnapshotContactoDictamenLlamada($prevId, $idCreditoPost);
            if (
                empty($snap)
                || (int)($snap['id_credito'] ?? 0) !== $idCreditoPost
            ) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se encontró el contacto para este crédito',
                ]);
                exit;
            }
            $llamadaOrigenDb = 'otros';
            $llamadaTelefono = trim((string)($snap['llamada_telefono'] ?? ''));
            $llamadaNombre = trim((string)($snap['llamada_nombre_persona'] ?? ''));
            $llamadaParentesco = trim((string)($snap['llamada_parentesco'] ?? ''));
            if ($llamadaTelefono === '' || $llamadaNombre === '') {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'El dictamen de referencia no tiene teléfono o nombre',
                ]);
                exit;
            }
        } elseif (
            $llamadaOrigenIn !== 'cliente'
            && !preg_match('/^referencia_[123]$/', $llamadaOrigenIn)
        ) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Seleccione a qué número o persona correspondió la llamada',
            ]);
            exit;
        }

        $data = [
            'id_credito'                => (int)($input['id_credito'] ?? 0),
            'tipo_contacto_id'           => (int)($input['tipo_contacto_id'] ?? 0),
            'resultado_contacto_id'      => (int)($input['resultado_contacto_id'] ?? 0),
            'dictamen_id'                => (int)($input['dictamen_id'] ?? 0),
            'tipo_motivo_no_pago_id'     => !empty($input['tipo_motivo_no_pago_id'])
                ? (int)$input['tipo_motivo_no_pago_id']
                : null,
            'motivo_no_pago_id'          => !empty($input['motivo_no_pago_id'])
                ? (int)$input['motivo_no_pago_id']
                : null,
            'plataforma_id'              => !empty($input['plataforma_id'])
                ? (int)$input['plataforma_id']
                : null,
            'fuente_ingresos'            => trim($input['fuente_ingresos'] ?? ''),
            'comentarios'                => trim($input['comentarios'] ?? ''),
            'usuario'                    => $_SESSION['usuario'] ?? 'Sistema',
            'usuario_id'                 => $_SESSION['usuario_id'] ?? 1,
            'llamada_origen'             => $llamadaOrigenDb,
            'llamada_telefono'           => $llamadaTelefono,
            'llamada_nombre_persona'     => $llamadaNombre,
            'llamada_parentesco'         => $llamadaParentesco !== '' ? $llamadaParentesco : null,
        ];

        // 🔴 Validación mínima backend
        if (
            !$data['id_credito'] ||
            !$data['tipo_contacto_id'] ||
            !$data['resultado_contacto_id'] ||
            !$data['dictamen_id'] ||
            !$data['comentarios'] ||
            $data['llamada_origen'] === ''
        ) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos para guardar el dictamen'
            ]);
            exit;
        }

        $resultado = EstadoCuentaDAO::insertDictamenLlamada($data);

        echo json_encode([
            'success' => $resultado['success'],
            'mensaje' => $resultado['mensaje'],
            'data'    => $resultado['data'] ?? null
        ]);
        exit;
    }



    public function buscarReporteDictamen()
{


    // DEBUG: Log de entrada


    header('Content-Type: application/json');

    // Verificar sesión EXPLÍCITAMENTE
    session_start(); // Asegurar que la sesión esté iniciada
    if (!isset($_SESSION['usuario_id'])) {
        error_log('ERROR: No hay usuario_id en sesión - Redirigiría a login');
        // NO redirijas aquí, devuelve JSON error
        echo json_encode([
            'success' => false,
            'mensaje' => 'Sesión expirada',
            'code' => 'SESSION_EXPIRED'
        ]);
        return;
    }

    if (!$this->tienePermisoModuloSesion(self::EC_PERMISO_DICTAMINAR_LLAMADA)) {
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para realizar esta acción.']);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('ERROR: Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
        return;
    }

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
        return;
    }

    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFin = $_POST['fechaFin'] ?? '';

    // Validar fechas
    if (empty($fechaInicio) || empty($fechaFin)) {
        echo json_encode(['success' => false, 'mensaje' => 'Fechas requeridas']);
        return;
    }

    // Validar formato de fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
        echo json_encode(['success' => false, 'mensaje' => 'Formato de fecha inválido']);
        return;
    }

    // Usar tu modelo existente
    $resultado = EstadoCuentaDAO::obtenerReportesDictamenPorFecha($fechaInicio, $fechaFin);

    // Retornar como JSON
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'data' => $resultado['datos'],
            'total' => count($resultado['datos'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => $resultado['mensaje'],
            'error' => $resultado['error'] ?? null
        ]);
    }


}

    public function registrarDocumentoCliente()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Metodo no permitido'
            ]);
            return;
        }

        $idCredito = $_POST['idCredito'] ?? null;
        $tipoDocumento = strtoupper(trim($_POST['tipoDocumento'] ?? ''));

        if (empty($idCredito) || empty($tipoDocumento)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parametros incompletos'
            ]);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se recibio ningun archivo o hubo un error en la carga'
            ]);
            return;
        }

        $archivo = $_FILES['archivo'];

        if (!\Core\SecureUpload::validateMime($archivo['tmp_name'], \Core\SecureUpload::MIME_PDF_OR_IMAGES)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Tipo de archivo no permitido. Solo PDF, JPG o PNG.'
            ]);
            return;
        }

        $mime = \Core\SecureUpload::getMimeType($archivo['tmp_name']);
        $extension = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'bin';

        if ($archivo['size'] > 10 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'El archivo excede el tamano maximo permitido de 10 MB'
            ]);
            return;
        }

        $directorioBase = sparta_uploads_join('documentos', 'doc_cliente');
        \Core\SecureUpload::ensureDir($directorioBase);

        $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
        if ($idSeguro === '') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'ID de credito invalido'
            ]);
            return;
        }

        $tipoSeguro = preg_replace('/[^A-Z0-9_-]/', '_', (string)$tipoDocumento);
        if ($tipoSeguro === '') {
            $tipoSeguro = 'DOC';
        }
        $nombreArchivo = $idSeguro . '_' . $tipoSeguro . '_' . date('Ymd_His') . '.' . $extension;
        $rutaCompleta = $directorioBase . '/' . $nombreArchivo;
        $rutaRelativa = 'uploads/documentos/doc_cliente/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo en el servidor'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'mensaje' => 'Documento guardado correctamente',
            'archivo' => $nombreArchivo,
            'ruta' => $rutaRelativa
        ]);
    }

    /**
     * Registra INE (frente y reverso) localmente
     */
    public function registrarINE()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Metodo no permitido'
            ]);
            return;
        }

        $idCredito = $_POST['idCredito'] ?? null;
        $tipoDocumento = strtoupper(trim($_POST['tipoDocumento'] ?? ''));

        if (empty($idCredito) || $tipoDocumento !== 'INE') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parametros incompletos o tipo de documento incorrecto'
            ]);
            return;
        }

        // Validar que se recibieron ambos archivos
        if (!isset($_FILES['ineFrente']) || $_FILES['ineFrente']['error'] !== UPLOAD_ERR_OK ||
            !isset($_FILES['ineReverso']) || $_FILES['ineReverso']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Debes subir ambos archivos: frente y reverso del INE'
            ]);
            return;
        }

        $frente = $_FILES['ineFrente'];
        $reverso = $_FILES['ineReverso'];

        if (!\Core\SecureUpload::validateMime($frente['tmp_name'], \Core\SecureUpload::MIME_IMAGES) ||
            !\Core\SecureUpload::validateMime($reverso['tmp_name'], \Core\SecureUpload::MIME_IMAGES)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF o WebP para INE.'
            ]);
            return;
        }

        if ($frente['size'] > 10 * 1024 * 1024 || $reverso['size'] > 10 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Uno de los archivos excede el tamano maximo de 10 MB'
            ]);
            return;
        }

        $directorioBase = sparta_uploads_join('documentos', 'doc_cliente');
        \Core\SecureUpload::ensureDir($directorioBase);

        $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
        if ($idSeguro === '') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'ID de credito invalido'
            ]);
            return;
        }

        $mimeF = \Core\SecureUpload::getMimeType($frente['tmp_name']);
        $mimeR = \Core\SecureUpload::getMimeType($reverso['tmp_name']);
        $extFrente = $mimeF ? \Core\SecureUpload::extensionFromMime($mimeF) : 'jpg';
        $extReverso = $mimeR ? \Core\SecureUpload::extensionFromMime($mimeR) : 'jpg';

        // Formato de nombre que espera buscarLocal(): idSeguro_INE_frente_* / idSeguro_INE_reverso_*
        $timestamp = date('Ymd_His');
        $nombreFrente = $idSeguro . '_INE_frente_' . $timestamp . '.' . $extFrente;
        $nombreReverso = $idSeguro . '_INE_reverso_' . $timestamp . '.' . $extReverso;

        $rutaFrente = $directorioBase . '/' . $nombreFrente;
        $rutaReverso = $directorioBase . '/' . $nombreReverso;

        if (!move_uploaded_file($frente['tmp_name'], $rutaFrente)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo frente del INE'
            ]);
            return;
        }

        if (!move_uploaded_file($reverso['tmp_name'], $rutaReverso)) {
            // Si el reverso falla, eliminar el frente
            @unlink($rutaFrente);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo reverso del INE'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'mensaje' => 'INE guardado correctamente (frente y reverso)',
            'archivoFrente' => $nombreFrente,
            'archivoReverso' => $nombreReverso
        ]);
    }

    /**
     * Sirve archivos locales desde uploads/documentos/doc_cliente/
     * con los headers HTTP correctos
     */
    public function servirArchivoLocal()
    {
        if (!isset($_GET['archivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Archivo no especificado']);
            exit;
        }

        $archivo = basename($_GET['archivo']); // Sanitizar para evitar path traversal
        $directorioBase = realpath(sparta_uploads_join('documentos', 'doc_cliente'));
        if ($directorioBase === false || !is_dir($directorioBase)) {
            http_response_code(404);
            echo json_encode(['error' => 'Directorio no encontrado']);
            exit;
        }
        $rutaCompleta = $directorioBase . DIRECTORY_SEPARATOR . $archivo;
        // Evitar path traversal: el archivo debe estar dentro del directorio base
        $rutaReal = realpath($rutaCompleta);
        $dentroDeBase = $rutaReal !== false && (
            strpos($rutaReal, $directorioBase) === 0 ||
            (DIRECTORY_SEPARATOR === '\\' && stripos($rutaReal, $directorioBase) === 0)
        );
        if ($rutaReal === false || !is_file($rutaReal) || !$dentroDeBase) {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no encontrado']);
            exit;
        }

        $rutaCompleta = $rutaReal;

        // Determinar Content-Type según extensión
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Enviar headers correctos
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($rutaCompleta));
        header('Content-Disposition: inline; filename="' . $archivo . '"');
        header('Cache-Control: public, max-age=3600');

        // Leer y enviar el archivo
        readfile($rutaCompleta);
        exit;
    }

    /**
     * Descarga binaria desde el proxy S3 del audit-app (misma base que verDocumento / INE 2DA forma).
     *
     * @return array{0:int,1:string|false} HTTP code y cuerpo, o false si curl falló
     */
    private function ineAuditoriaS3DownloadBinary(string $fileName): array
    {
        $s3Url = 'http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=' . urlencode($fileName);
        $ch = curl_init($s3Url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false,
        ]);
        $data = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $data !== false ? $data : false];
    }

    /** idCliente S2 para rutas estándar INE/{idCliente}_frente|reverso.{jpeg|jpg} */
    private function ineObtenerIdClientePorCreditoApi(int $idCredito): ?int
    {
        $endpoint = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
        $token = '__SPARTA_TOKEN_REDACTED__';
        $payload = json_encode([
            'idCredito' => $idCredito,
            'fechaCorte' => date('Y-m-d'),
        ]);
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Token: ' . $token,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = is_string($response) ? json_decode($response, true) : null;
        $idCliente = $data['estadoCuenta']['datosCliente']['idCliente'] ?? null;
        if ($idCliente === null || $idCliente === '') {
            return null;
        }
        if (!is_numeric($idCliente)) {
            return null;
        }

        return (int) $idCliente;
    }

    /**
     * Sirve frente o reverso del INE desde persona_documentos (3RA FORMA).
     * GET: id = id de crédito (id_oferta), lado = frente|reverso
     */
    public function servirINEPersonaDocumento()
    {
        $id = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
        $lado = isset($_GET['lado']) ? strtolower(trim((string) $_GET['lado'])) : '';
        if (!in_array($lado, ['frente', 'reverso'], true) || $id === '' || !is_numeric($id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Parámetros inválidos (id y lado=frente|reverso)']);
            exit;
        }
        $res = EstadoCuentaDAO::obtenerINEPersonaDocumentos($id);
        if (empty($res['success']) || empty($res['datos'])) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'INE no encontrado']);
            exit;
        }
        $col = $lado === 'frente' ? 'archivo_ine_frente' : 'archivo_ine_reverso';
        $valor = $res['datos'][$col] ?? null;
        if ($valor === null || $valor === '') {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Imagen no encontrada']);
            exit;
        }
        $valor = trim((string) $valor);
        // persona_documentos guarda nombres de archivo (ej. 698912_1748642990507_frente.jpeg); servimos desde S3 carpeta INE/
        $primary = (strpos($valor, 'INE/') === 0) ? $valor : 'INE/' . $valor;

        $candidates = [$primary];
        if (preg_match('/^(.+)\.(jpe?g|png)$/i', $primary, $m)) {
            $base = $m[1];
            $ext = strtolower($m[2]);
            if ($ext === 'jpeg') {
                $candidates[] = $base . '.jpg';
            } elseif ($ext === 'jpg') {
                $candidates[] = $base . '.jpeg';
            }
        }

        $idCreditoInt = (int) $id;
        $idCliente = $this->ineObtenerIdClientePorCreditoApi($idCreditoInt);
        if ($idCliente !== null) {
            $suf = $lado === 'frente' ? 'frente' : 'reverso';
            $candidates[] = 'INE/' . $idCliente . '_' . $suf . '.jpeg';
            $candidates[] = 'INE/' . $idCliente . '_' . $suf . '.jpg';
            $candidates[] = 'INE/' . $idCliente . '_' . $suf . '.png';
        }

        $candidates = array_values(array_unique(array_filter($candidates)));

        foreach ($candidates as $fileName) {
            [$httpCode, $data] = $this->ineAuditoriaS3DownloadBinary($fileName);
            if ($httpCode !== 200 || $data === false || strlen($data) < 16) {
                continue;
            }
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($ext === 'png') {
                $contentType = 'image/png';
            } elseif (in_array($ext, ['jpg', 'jpeg'], true)) {
                $contentType = 'image/jpeg';
            } else {
                $contentType = 'image/jpeg';
            }
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: inline; filename="INE_' . $lado . '.' . ($ext ?: 'jpg') . '"');
            header('Content-Length: ' . strlen($data));
            header('Cache-Control: public, max-age=3600');
            echo $data;
            exit;
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se pudo recuperar la imagen desde S3']);
        exit;
    }

    public function verDocumento()
    {
        $fileName = $_GET['fileName'] ?? '';

        if (empty($fileName)) {
            http_response_code(404);
            echo "Archivo no especificado";
            exit;
        }

        // Decodificar el fileName si viene codificado
        $fileName = urldecode($fileName);

        $s3Url = "http://98.90.194.116:8080/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=" . urlencode($fileName);

        // Determinar Content-Type basado en extensión
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $contentType = 'application/octet-stream';

        switch ($ext) {
            case 'pdf': $contentType = 'application/pdf'; break;
            case 'jpg':
            case 'jpeg': $contentType = 'image/jpeg'; break;
            case 'png': $contentType = 'image/png'; break;
            case 'gif': $contentType = 'image/gif'; break;
        }

        // Obtener el archivo desde S3
        $ch = curl_init($s3Url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false, // No incluir headers en la respuesta
        ]);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $data === false) {
            http_response_code(404);
            echo "No se pudo recuperar el documento (Code: $httpCode" . ($error ? ", Error: $error" : "") . ")";
            exit;
        }

        // Limpiar cualquier output previo
        if (ob_get_length()) {
            ob_clean();
        }

        // Servir el archivo forzando inline para visualización
        // IMPORTANTE: Estos headers sobrescriben cualquier header del servidor S3
        header("Content-Type: $contentType");
        header("Content-Disposition: inline; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($data));
        header("Cache-Control: public, max-age=3600");
        header("Pragma: public");

        // Headers CORS si es necesario
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET");

        echo $data;
        exit;
    }




public function descargarReporteDictamen()
{
    // Limpiar buffer para evitar que cualquier eco previo rompa el Excel
    while (ob_get_level()) {
        ob_end_clean();
    }

    if (!$this->tienePermisoModuloSesion(self::EC_PERMISO_DICTAMINAR_LLAMADA)) {
        die('No tiene permiso para descargar el reporte de dictamen.');
    }

    try {
        // Obtener parámetros de fecha del GET
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        // Validar fechas
        if (empty($fechaInicio) || empty($fechaFin)) {
            die('Fechas requeridas');
        }

        // Validar formato de fechas
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            die('Formato de fecha inválido. Use YYYY-MM-DD');
        }

        // Obtener datos del reporte
        $reportes = EstadoCuentaDAO::obtenerReportesDictamenParaDescarga($fechaInicio, $fechaFin);

        if (empty($reportes)) {
            die('No hay datos para descargar en el rango de fechas especificado');
        }

        // Preparar datos para Excel
        $data = [];
        foreach ($reportes as $reporte) {
            // Formatear fecha si existe
            $fechaRegistro = $reporte['fecha_registro'] ?? '';
            if ($fechaRegistro) {
                try {
                    $fechaRegistro = date('d/m/Y', strtotime($fechaRegistro));
                } catch (\Exception $e) {
                    // Mantener el formato original si hay error
                }
            }

            $data[] = [
                'id_dictamen' => $reporte['id_dictamen'] ?? '',
                'fecha_registro' => $fechaRegistro,
                'hora_registro' => $reporte['hora_registro'] ?? '',
                'id_credito' => $reporte['id_credito'] ?? '',
                'nombre_cliente' => $reporte['nombre_cliente'] ?? '',
                'tipo_contacto' => $reporte['tipo_contacto'] ?? '',
                'resultado_contacto' => $reporte['resultado_contacto'] ?? '',
                'dictamen' => $reporte['dictamen'] ?? '',
                'motivo_no_pago' => $reporte['motivo_no_pago'] ?? '',
                'tipo_motivo_no_pago' => $reporte['tipo_motivo_no_pago'] ?? '',
                'plataforma' => $reporte['plataforma'] ?? '',
                'fuente_ingresos' => $reporte['fuente_ingresos'] ?? '',
                'comentarios' => $reporte['comentarios'] ?? '',
                'agente' => $reporte['agente'] ?? ''
            ];
        }

        // Definir columnas para Excel
        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('id_dictamen', 'ID DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_registro', 'FECHA REGISTRO'),
            \PHPSpreadsheet::ColumnaExcel('hora_registro', 'HORA REGISTRO'),
            \PHPSpreadsheet::ColumnaExcel('id_credito', 'ID CRÉDITO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_cliente', 'CLIENTE'),
            \PHPSpreadsheet::ColumnaExcel('tipo_contacto', 'TIPO CONTACTO'),
            \PHPSpreadsheet::ColumnaExcel('resultado_contacto', 'RESULTADO CONTACTO'),
            \PHPSpreadsheet::ColumnaExcel('dictamen', 'DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('motivo_no_pago', 'MOTIVO NO PAGO'),
            \PHPSpreadsheet::ColumnaExcel('tipo_motivo_no_pago', 'TIPO MOTIVO NO PAGO'),
            \PHPSpreadsheet::ColumnaExcel('plataforma', 'PLATAFORMA'),
            \PHPSpreadsheet::ColumnaExcel('fuente_ingresos', 'FUENTE DE INGRESOS'),
            \PHPSpreadsheet::ColumnaExcel('comentarios', 'COMENTARIOS'),
            \PHPSpreadsheet::ColumnaExcel('agente', 'AGENTE')
        ];

        // Generar nombre del archivo
        $nombreArchivo = 'Dictamen_Llamadas_' . $fechaInicio . '_a_' . $fechaFin . '_' . date('Y-m-d');

        // Descargar Excel directamente usando PHPSpreadsheet
        \PHPSpreadsheet::DescargaExcel(
            $nombreArchivo,
            "Dictamen de Llamadas",
            "Dictamen Llamadas",
            $columnas,
            $data
        );

        // Terminar ejecución para que no se agregue nada extra
        exit;
    } catch (\Exception $e) {
        error_log('Error en descargarReporteDictamen: ' . $e->getMessage());
        die('Error al generar el archivo Excel: ' . $e->getMessage());
    }
}


    public function getDictamenLlamadas()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_DICTAMINAR_LLAMADA);
        // Obtener filtros de fecha
        $fechaInicio = $_POST['fechaInicio'] ?? null;
        $fechaFin = $_POST['fechaFin'] ?? null;

        if (empty($fechaInicio) || empty($fechaFin)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Fechas requeridas'
            ]);
            return;
        }

        // Llamar al DAO con filtros
        $resultado = EstadoCuentaDAO::buscarReporteDictamen(
            $_SESSION['usuario_id'],
            $fechaInicio,
            $fechaFin
        );

        // Verificar resultado
        if (!$resultado['success']) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $resultado['mensaje']
            ]);
            return;
        }

        // Formatear datos si es necesario
        $datos = array_map(function($item) {
            return [
                'id' => $item['id'] ?? '',
                'fecha_registro' => $item['fecha_registro'] ?? '',
                'hora_registro' => $item['hora_registro'] ?? '',
                'id_credito' => $item['id_credito'] ?? '',
                'nombre_cliente' => $item['nombre_cliente'] ?? '',
                'tipo_contacto' => $item['tipo_contacto'] ?? '',
                'resultado_contacto' => $item['resultado_contacto'] ?? '',
                'dictamen' => $item['dictamen'] ?? '',
                'motivo_no_pago' => $item['motivo_no_pago'] ?? '',
                'tipo_motivo_no_pago' => $item['tipo_motivo_no_pago'] ?? '',
                'plataforma' => $item['plataforma'] ?? '',
                'fuente_ingresos' => $item['fuente_ingresos'] ?? '',
                'comentarios' => $item['comentarios'] ?? ''
            ];
        }, $resultado['datos']);

        self::respuestaJSON([
            'success' => true,
            'datos' => $datos,
            'cantidad' => count($datos)
        ]);
    }


    public function reporteDictamen()
    {
        header('Location: /analitica/callcenter', true, 302);
        exit;
    }

    public function confirmarCondonacionGastos()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA);
        $input = json_decode(file_get_contents("php://input"), true);

        $idCredito  = $input['idCredito'] ?? null;
        $comentario = isset($input['comentario']) ? trim((string) $input['comentario']) : '';
        $gastos     = $input['gastos'] ?? [];
        $total      = $input['total'] ?? 0;
        $idMotivoCondonacion = EstadoCuentaDAO::normalizarIdMotivoCondonacionCobranza($input['id_motivo_condonacion'] ?? 1);

        if (empty($idCredito) || $comentario === '' || empty($gastos)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }

        if (mb_strlen($comentario, 'UTF-8') < 25) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'El motivo debe tener al menos 25 caracteres.',
            ]);
            return;
        }

        if (EstadoCuentaDAO::gastosCobranzaBloqueadosPorGestionExterna($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => EstadoCuentaDAO::mensajeBloqueoGestionExternaGastos(),
            ]);
            return;
        }

        // Datos de sesión (usuario_id y usuario del login)
        $dataTicket = [
            'id_credito' => $idCredito,
            'comentario' => $comentario,
            'total'      => $total,
            'id_motivo_condonacion' => $idMotivoCondonacion,
            'usuario'    => $_SESSION['usuario'] ?? 'Sistema',        // user_name del login
            'usuario_id' => $_SESSION['usuario_id'] ?? 0              // id del login
        ];

        // 1️⃣ Insertar ticket
        $ticket = EstadoCuentaDAO::insertCondonacionCobranza($dataTicket);



        if (!$ticket['success']) {
            self::respuestaJSON($ticket);
            return;
        }

        $idCondonacion = $ticket['datos']['id_condonacion'];


        // 2️⃣ Insertar detalle + marcar gastos
        foreach ($gastos as $g) {

            $detalle = EstadoCuentaDAO::insertCondonacionCobranzaDetalle([
                'id_condonacion'     => $idCondonacion,
                'id_gastos_cobranza' => (int) $g['id_gastos_cobranza'],
                'monto'              => (float) $g['monto']
            ]);

            if (!$detalle['success']) {
                self::respuestaJSON($detalle);
                return;
            }

            // 👇 SOLO si el detalle se insertó correctamente
            $marca = EstadoCuentaDAO::marcarGastoCondonado(
                (int) $g['id_gastos_cobranza']
            );

            if (!$marca['success']) {
                self::respuestaJSON($marca);
                return;
            }
        }

        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'Condonación registrada correctamente',
            'data' => [
                'id_condonacion' => $idCondonacion
            ]
        ]);
    }

    ///////////////////////////////////////////// GUATEMALA - Estado de Cuenta con soporte multipaís ///////////////////////////7

    public function Guatemala()
    {
        $idCreditoLista = null;
        $referencias = [];
        $datosGuat = [];
        $alertaBusqueda = null;

        $alertaCreditoInvalido = [
            'icon' => 'error',
            'title' => 'ID de crédito incorrecto',
            'html' => "<div style='text-align: center; padding: 10px;'><p style='font-size: 16px; margin-bottom: 15px; color: #333;'><strong>El ID de crédito ingresado no existe en Guatemala o no es válido.</strong></p><p style='font-size: 14px; color: #666;'>Por favor verifícalo y vuelve a intentar.</p></div>",
            'confirmButtonText' => 'Entendido',
            'confirmButtonColor' => '#dc3545',
            'width' => '500px'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Guatemala: solo búsqueda por ID (no por nombre / idCreditoLista).
            $idCreditoLista = $_POST['idCredito'] ?? null;

            if ($idCreditoLista) {
                // 1) Prioridad Guatemala: si existe aquí, se procesa como GT aunque exista el mismo ID en MX.
                error_log('[Guatemala] Buscando id=' . json_encode($idCreditoLista));
                $datosGuat = EmpresasDAO::getGuatemalaEstadoCuenta($idCreditoLista);
                error_log("[Guatemala] DB result success=" . json_encode($datosGuat['success'] ?? null) . " count=" . count($datosGuat['datos'] ?? []) . " msg=" . json_encode($datosGuat['mensaje'] ?? '') . " error=" . json_encode($datosGuat['error'] ?? ''));
                if (!empty($datosGuat['datos'])) {
                    error_log("[Guatemala] Registro encontrado: pkey_credito=" . json_encode($datosGuat['datos'][0]['pkey_credito'] ?? null) . " fk_oferta=" . json_encode($datosGuat['datos'][0]['fk_oferta'] ?? null));
                    // Llamadas a CROOP API (Bandera=445 saldos, Bandera=401 amortización, Bandera=404 pagos)
                    $pkeyCredito     = $datosGuat['datos'][0]['pkey_credito'] ?? null;
                    $reqCliente      = json_decode($datosGuat['datos'][0]['request_cliente'] ?? '{}', true) ?? [];
                    $fkEmpresa       = $reqCliente['FK_Empresa'] ?? null;
                    // PKey interna de CROOP almacenada en response_credito (diferente al ID local para Bandera=445/404)
                    $respCredito     = json_decode($datosGuat['datos'][0]['response_credito'] ?? '{}', true) ?? [];
                    $pkeyInterno     = $respCredito['PKey'] ?? $respCredito['Pkey'] ?? $respCredito['pkey'] ?? null;
                    $apiSaldos       = [];
                    $apiAmortizacion = [];
                    $apiPagos        = [];
                    $debugCroop      = [
                        'idCreditoLista'        => $idCreditoLista,
                        'pkeyCredito'           => $pkeyCredito,
                        'pkeyInterno'           => $pkeyInterno,
                        'response_credito_keys' => array_keys($respCredito),
                        'fkEmpresa'             => $fkEmpresa,
                        'datosGuat_count'   => count($datosGuat['datos'] ?? []),
                        'referencias_count' => 0,
                        'login_ok'          => false,
                        'token_preview'     => null,
                        'saldos_count'      => 0,
                        'saldos_raw'        => null,
                        'amort_count'       => 0,
                        'amort_raw_first'   => null,
                        'pagos_count'       => 0,
                        'error'             => null,
                    ];

                    if ($pkeyCredito) {
                        $loginResult = $this->croop_login();
                        $croopToken  = $loginResult['token'] ?? null;

                        $debugCroop['login_ok'] = !empty($croopToken);
                        $debugCroop['token_preview'] = $croopToken ? substr($croopToken, 0, 20) . '...' : null;
                        $debugCroop['login_http_code'] = $loginResult['http_code'] ?? null;
                        $debugCroop['login_curl_error'] = $loginResult['curl_error'] ?? null;
                        $debugCroop['login_success'] = $loginResult['success'] ?? false;
                        $debugCroop['login_raw'] = $loginResult['raw'] ?? null;

                        if ($croopToken) {
                            // FK_Empresa del session de login (355 para Guatemala)
                            $fkEmpresaLogin  = $loginResult['fk_empresa'] ?? 355;
                            // 401 usa el ID local del crédito; 445 y 404 requieren FK_Empresa del session
                            $pkeyParaSaldos  = $pkeyInterno ?? $pkeyCredito;

                            $apiSaldos       = $this->croop_get("clsCredito/Listar?Bandera=445&PKey={$pkeyParaSaldos}&FK_Empresa={$fkEmpresaLogin}", $croopToken);
                            $apiAmortizacion = $this->croop_get("clsCredito/Listar?Bandera=401&PKey={$pkeyCredito}", $croopToken);
                            $apiPagos        = $this->croop_get("clsPagos/Listar?Bandera=404&FK_Credito={$pkeyParaSaldos}", $croopToken);

                            $debugCroop['saldos_count'] = count($apiSaldos);
                            $debugCroop['saldos_raw'] = $apiSaldos[0] ?? null;
                            $debugCroop['amort_count'] = count($apiAmortizacion);
                            $debugCroop['amort_raw_first'] = $apiAmortizacion[0] ?? null;
                            $debugCroop['pagos_count'] = count($apiPagos);
                        } else {
                            $debugCroop['error'] = 'Login fallido: token null';
                        }
                    } else {
                        $debugCroop['error'] = 'pkeyCredito es null — no se llamó a CROOP';
                    }

                    $idUsuarioGt = (int) ($_SESSION['usuario_id'] ?? 0);
                    $modsSesionGt = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
                    $tienePermisoRastreoNeverPaidGt = ($idUsuarioGt === 1) || in_array(29, $modsSesionGt, true);
                    self::set('tienePermisoRastreoNeverPaid', $tienePermisoRastreoNeverPaidGt);
                    $tienePermisoAclaracionesGcGt = ($idUsuarioGt === 1) || in_array(30, $modsSesionGt, true);
                    self::set('tienePermisoAclaracionesGc', $tienePermisoAclaracionesGcGt);
                    self::set('tienePermisoDictaminarLlamada', $this->tienePermisoModuloSesion(self::EC_PERMISO_DICTAMINAR_LLAMADA));
                    self::set('tienePermisoCondonarGastosCobranza', $this->tienePermisoModuloSesion(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA));
                    self::set('tienePermisoNotasCliente', $this->tienePermisoModuloSesion(self::EC_PERMISO_NOTAS_CLIENTE));
                    $idCredNotasGt = is_numeric($idCreditoLista) ? (int) $idCreditoLista : 0;
                    $notasGt = ['success' => true, 'datos' => [['num' => 0]]];
                    if ($idCredNotasGt > 0 && $this->tienePermisoModuloSesion(self::EC_PERMISO_NOTAS_CLIENTE)) {
                        $notasGt = EmpresasDAO::getNotasNum($idCredNotasGt);
                    }
                    self::set('notas', $notasGt);
                    self::set(
                        'saldoTotalPendienteGastoCobranza',
                        EstadoCuentaDAO::getTotalGastosCobranzaPendiente($idCredNotasGt)
                    );
                    $this->setEcAclaracionesUltimoPagoMetaParaVista($idCredNotasGt);

                    self::set("titulo", "Estado de Cuenta - Guatemala");
                    self::set("paisData", ['nombre_pais' => 'Guatemala', 'codigo_iso' => 'gt', 'pais_activo' => 1]);
                    self::set("referencias", []);
                    self::set("datosGuat", $datosGuat);
                    self::set("apiSaldos", $apiSaldos);
                    self::set("apiAmortizacion", $apiAmortizacion);
                    self::set("apiPagos", $apiPagos);
                    self::set("debugCroop", $debugCroop);
                    $scriptGt = $this->appendRastreoSabuesoScriptSiAplica('', $tienePermisoRastreoNeverPaidGt);
                    self::set('script', $scriptGt);
                    self::set('catalogoMotivosCondonacion', EstadoCuentaDAO::getCatalogoMotivosCondonacion());
                    return self::render("__SPARTA_SECRET_REDACTED___guatemala");
                }

                // 2) Si no existe en GT, validar en México para mostrar alerta de país incorrecto.
                $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCreditoLista);
                if (!empty($referencias['datos'])) {
                    $alertaBusqueda = [
                        'title' => 'Crédito de México',
                        'html' => "<div style='text-align:center;'><div style='margin-bottom:12px;'><span class='fi fi-mx fis' style='font-size:2.8rem;'></span></div><p style='margin:0; font-size:14px; color:#666;'>El crédito ingresado pertenece a México. Consulta este ID en Estado de Cuenta México.</p></div>",
                        'confirmButtonText' => 'Entendido'
                    ];
                } else {
                    $alertaBusqueda = $alertaCreditoInvalido;
                }
            } else {
                $alertaBusqueda = [
                    'icon' => 'warning',
                    'title' => 'Falta el ID de crédito',
                    'text' => 'Por favor ingresa el ID del crédito.'
                ];
            }
        }

        // GET o sin resultados → mostrar formulario de búsqueda
        self::set("titulo", "Estados de Cuenta - Guatemala");
        self::set("paisData", ['nombre_pais' => 'Guatemala', 'codigo_iso' => 'gt', 'pais_activo' => 1]);
        if (!empty($alertaBusqueda)) {
            self::set("alertaBusqueda", $alertaBusqueda);
        }
        return self::render("__SPARTA_SECRET_REDACTED___guatemala_consulta");
    }

    /* ----------------------------------------------------------------
       CROOP API — helpers internos
       ---------------------------------------------------------------- */
    private function croop_login(): array
    {
        $ch = curl_init("https://api.croop.mx/api/access/Signin");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                "Email"     => "__SPARTA_SECRET_REDACTED__@__SPARTA_SECRET_REDACTED__.mx",
                "Password"  => "Ruvalcaba227$",
                "IPAddress" => "200.188.109.202",
                "UserAgent" => "Mozilla/5.0",
                "ServerURL" => "https://api.croop.mx/api",
            ]),
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "AppID: 2739CAE2-9353-E811-9457-22000A244A86",
                "Cache-Control: no-cache",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log("[croop_login] http_code={$httpCode} curl_error=" . ($curlError ?: 'none') . " response_body=" . ($response ?: 'EMPTY'));

        $json  = $response ? (json_decode($response, true) ?? []) : [];
        $token = $json['Token'] ?? null;

        return [
            'token'      => $token,
            'fk_empresa' => $json['FK_Empresa'] ?? null,
            'http_code'  => $httpCode,
            'curl_error' => $curlError ?: null,
            'raw'        => $response ? substr($response, 0, 300) : null,
            'success'    => !empty($json['Success']),
        ];
    }

    private function croop_get(string $path, string $token): array
    {
        $url = "https://api.croop.mx/api/" . ltrim($path, '/');
        error_log("[croop_get] GET {$url}");
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => [
                "AppID: 2739CAE2-9353-E811-9457-22000A244A86",
                "Token: {$token}",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        error_log("[croop_get] http_code={$httpCode} curl_error=" . ($curlError ?: 'none') . " response_body=" . ($response ?: 'EMPTY'));
        if (!$response) return ['__debug_error' => $curlError, '__http_code' => $httpCode];
        $json = json_decode($response, true);
        return is_array($json) ? $json : ['__debug_raw' => substr($response, 0, 300), '__http_code' => $httpCode];
    }

    private function procesarGastosCobranza(array $notasCargos, $idCredito): array
    {
        // Una sola implementación: Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas (no duplicar cruce aquí).
        $r = EstadoCuentaDAO::procesarGastosCobranzaDesdeNotas($notasCargos, $idCredito, true);
        unset($r['_sin_actualizacion'], $r['_causa']);

        return $r;
    }

    public function getHistorialGastosCobranza()
    {
        $this->requierePermisoEstadoCuentaModulo(self::EC_PERMISO_CONDONAR_GASTOS_COBRANZA);
        $raw = (string) file_get_contents('php://input');
        $input = $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($input)) {
            $input = [];
        }
        $idCredito = (int) ($input['idCredito'] ?? $input['id_credito'] ?? 0);
        if ($idCredito <= 0) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de crédito requerido',
            ]);
        }

        $resultado = EstadoCuentaDAO::getHistorialGastosCobranza($idCredito);
        self::respuestaJSON($resultado);
    }

    public function procesarCruceGastos()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $raw = file_get_contents('php://input');
            $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
            if (!is_array($input)) {
                $input = [];
            }

            $idCredito = (int) ($input['idCredito'] ?? 0);
            if ($idCredito <= 0) {
                echo json_encode(['success' => false, 'mensaje' => 'idCredito requerido']);
                exit;
            }

            $notasCargos = null;
            if (!empty($input['notasCargos']) && is_array($input['notasCargos'])) {
                $notasCargos = $input['notasCargos'];
            }

            if ($notasCargos === null) {
                $fechaCorte = $input['fechaCorte'] ?? date('Y-m-d');
                if (!is_string($fechaCorte) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
                    $fechaCorte = date('Y-m-d');
                }
                $api = $this->api___SPARTA_SECRET_REDACTED__($idCredito, $fechaCorte);
                if (empty($api['ok']) || !is_array($api['data'])) {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'No se pudieron obtener notas de cargo para el cruce',
                        'detalle' => $api['error'] ?? null,
                    ]);
                    exit;
                }
                $notasCargos = $api['data']['datosNotasCargos'] ?? [];
                if (!is_array($notasCargos)) {
                    $notasCargos = [];
                }
            }

            $resultado = $this->procesarGastosCobranza($notasCargos, $idCredito);

            echo json_encode(['success' => true, 'resultado' => $resultado]);
        } catch (\Throwable $e) {
            error_log('[EstadoCuenta::procesarCruceGastos] ' . $e->getMessage());
            echo json_encode(['success' => false, 'mensaje' => 'Error al procesar el cruce de gastos']);
        }
        exit;
    }

    public function getComplementosEstadoCuenta()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($input)) {
            $input = [];
        }
        $idCredito = (int) ($input['idCredito'] ?? 0);
        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'idCredito requerido']);
            exit;
        }
        try {
            $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCredito);
            $direcciones = EmpresasDAO::getConsultaDireccionEstadoCuenta($idCredito);
            $notas = ['success' => true, 'mensaje' => '', 'datos' => [['num' => 0]]];
            if ($this->tienePermisoModuloSesion(self::EC_PERMISO_NOTAS_CLIENTE)) {
                $notas = EmpresasDAO::getNotasNum($idCredito);
            }
            echo json_encode([
                'success'     => true,
                'referencias' => $referencias,
                'direcciones' => $direcciones,
                'notas'       => $notas,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[EstadoCuenta::getComplementosEstadoCuenta] ' . $e->getMessage());
            echo json_encode(['success' => false, 'mensaje' => 'Error al cargar datos complementarios']);
        }
        exit;
    }
}
