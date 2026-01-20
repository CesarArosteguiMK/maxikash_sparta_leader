<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;
use Models\EstadoCuenta as EstadoCuentaDAO;


class EstadoCuenta extends Controller
{

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

    // ---------------- PARSEAR CUOTAS ----------------
    private function parse_cuotas_field($value) {
        if (!$value) return [];

        $value = trim($value);

        if (strpos($value, ",") !== false) {
            return array_map('intval', explode(",", $value));
        }

        if (strpos($value, "-") !== false) {
            list($start, $end) = explode("-", $value);
            return range(intval($start), intval($end));
        }

        return [intval($value)];
    }

    // ---------------- EXTRAER NUMERO DE CUOTA ----------------
    private function extraer_numero_cuota($concepto) {
        if (!$concepto) return null;
        if (preg_match('/(\d+)/', $concepto, $m)) {
            return intval($m[1]);
        }
        return null;
    }
    public function Consulta()
    {
        // --- JS COMPLETO EN EL CONTROLADOR ---
        $script = <<<JS
        <script>
        
        document.addEventListener("DOMContentLoaded", () => {
        
            // Cambiar entre ID y Nombre
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    document.getElementById('divNombre').style.display = modo === 'nombre' ? 'block' : 'none';
                    document.getElementById('divID').style.display = modo === 'id' ? 'block' : 'none';
            }
        
            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            actualizarInputs();
        
            // Botón limpiar filtros
            document.getElementById("btnResetFiltros").addEventListener("click", () => {
                document.getElementById("idCredito").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("modoID").checked = true;
                actualizarInputs();
            });
        
            // Validación antes de enviar
            document.getElementById("formBusqueda").addEventListener("submit", e => {
                
                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                const idCredito       = document.getElementById("idCredito").value.trim();
                const nombre          = document.getElementById("nombre").value.trim();
                const idCreditoLista  = document.getElementById("idCreditoLista").value.trim();
        
                // =========================
                // MODO ID
                // =========================
                if (modo === "id") {
            
                    if (idCredito === "") {
                        e.preventDefault();
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el ID de crédito",
                            text: "Por favor ingresa el ID del crédito."
                        });
                    }
            
                    // Limpieza defensiva
                    document.getElementById("idCreditoLista").value = "";
                    document.getElementById("nombre").value = "";
                }
            
                // =========================
                // MODO NOMBRE
                // =========================
                if (modo === "nombre") {
            
                    if (nombre === "") {
                        e.preventDefault();
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el nombre",
                            text: "Escribe y selecciona un cliente de la lista."
                        });
                    }
            
                    if (idCreditoLista === "") {
                        e.preventDefault();
                        return Swal.fire({
                            icon: "warning",
                            title: "Cliente no seleccionado",
                            text: "Debes seleccionar un cliente del listado, no solo escribirlo."
                        });
                    }
            
                    // Limpieza defensiva
                    document.getElementById("idCredito").value = "";
                }
            
                // =========================
                // LOADING
                // =========================
                Swal.fire({
                    title: "Procesando solicitud...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            });
        
        });
        

        </script>
JS;
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;

        # -----------------------------
        # PETICIÓN POST
        # -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;
            $idCreditoLista = $_POST['idCreditoLista'] ?? null;
            $fechaHoy = date('Y-m-d');

            if($nombre != null && $idCreditoLista != null)
            {
                $resultado = $this->api___SPARTA_SECRET_REDACTED__($idCreditoLista, $fechaHoy);
                $respDAO = EmpresasDAO::getConsultaDireccionEstadoCuenta($idCreditoLista);
                $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCreditoLista);
                $notas = EmpresasDAO::getNotasNum($idCreditoLista);
            }
            else
            {
                $resultado =  $this->api___SPARTA_SECRET_REDACTED__($idCredito, $fechaHoy);
                $respDAO = EmpresasDAO::getConsultaDireccionEstadoCuenta($idCredito);
                $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCredito);
                $notas = EmpresasDAO::getNotasNum($idCredito);
            }




            //$GestionesAll = GestionesDao::getAllGestiones($idCredito, $nombre);
            //$resultado =  $this->api___SPARTA_SECRET_REDACTED__($idCredito, "2025-12-04");
            // variables para guardar por bloques los arrreglos
            // ---------------------------------------------------------------
            // 1. Extraemos las secciones del JSON original
            // ---------------------------------------------------------------
            $cliente = $resultado['data']['datosCliente'];
            $estadoCuenta = $resultado['data'];
            $otrosDatos = $resultado['data']['datosSaldos'];
            //$datosCargos = $resultado['data']['datosCargos'];
            //$datosPagos = $resultado['data']['datosPagos'];

            // ---------------------------------------------------------------
            // 2. Crear estructura base para cada cuota con la información del cargo
            // ---------------------------------------------------------------
            $cargos = $estadoCuenta["datosCargos"] ?? [];
            if (!is_array($cargos)) $cargos = [];

            $pagos = $estadoCuenta["datosPagos"] ?? [];
            if (!is_array($pagos)) $pagos = [];

            $pagos_list = [];

            // -------------------------
            // PREPARAR PAGOS
            // -------------------------
            foreach ($pagos as $p) {

                $montoPago      = $this->safe_float($p["montoPago"] ?? 0);
                $extemporaneos  = $this->safe_float($p["extemporaneos"] ?? 0);
                $monto_real     = max($montoPago - $extemporaneos, 0);

                $cuotas = $this->parse_cuotas_field($p["numeroCuotaSemanal"] ?? null);

                $pagos_list[] = [
                    "idPago"              => $p["idPago"] ?? null,
                    "remaining"           => round($monto_real, 2),
                    "cuotas"              => $cuotas,
                    "fechaValor"          => $p["fechaValor"] ?? null,
                    // *** fechaRegistro usa fechaDeposito si viene ***
                    "fechaRegistro"       => $p["fechaDeposito"] ?? ($p["fechaRegistro"] ?? null),
                    "montoPagoOriginal"   => $montoPago,
                    "extemporaneos"       => $extemporaneos,
                    "_extemporaneo_aplicado" => false
                ];
            }

            // -------------------------
            // ORDENAR CARGOS POR idCargo
            // -------------------------
            usort($cargos, function($a, $b){
                return $this->safe_int($a["idCargo"] ?? 0) <=> $this->safe_int($b["idCargo"] ?? 0);
            });

            $tabla = [];

            // -------------------------
            // PROCESAR CARGOS
            // -------------------------
            foreach ($cargos as $cargo) {

                $concepto = $cargo["concepto"] ?? "";
                $cuota_num = $this->extraer_numero_cuota($concepto);

                if ($cuota_num === null) {
                    $cuota_num = $this->safe_int($cargo["idCargo"] ?? 0);
                }

                $monto_cargo   = $this->safe_float($cargo["monto"] ?? 0);
                $capital       = $this->safe_float($cargo["capital"] ?? 0);
                $interes       = $this->safe_float($cargo["interes"] ?? 0);
                $seguro_total  = $this->safe_float($cargo["seguroBienes"] ?? 0)
                    + $this->safe_float($cargo["seguroVida"] ?? 0)
                    + $this->safe_float($cargo["seguroDesempleo"] ?? 0);

                $fecha_venc = $cargo["fechaVencimiento"] ?? ($cargo["fechaVenc"] ?? null);

                $monto_restante_cargo = $monto_cargo;
                $aplicados = [];

                // -------------------------
                // APLICAR PAGOS A ESTE CARGO
                // -------------------------
                foreach ($pagos_list as &$pago) {

                    // ¿Este pago aplica a esta cuota?
                    if (!in_array($cuota_num, $pago["cuotas"])) {
                        continue;
                    }

                    // --- Aplicar monto real (remaining) ---
                    if ($monto_restante_cargo > 0 && $pago["remaining"] > 0) {

                        $aplicar = min($pago["remaining"], $monto_restante_cargo);

                        $aplicados[] = [
                            "idPago"       => $pago["idPago"],
                            "montoPago"    => round($pago["remaining"], 2), // cuánto quedaba antes de aplicar
                            "aplicado"     => round($aplicar, 2),
                            "fechaRegistro"=> $pago["fechaRegistro"],
                            "fechaPago"    => $fecha_venc,
                            "diasMora"     => null,
                            "extemporaneos"=> 0.0
                        ];

                        // restar de pago y cargo
                        $pago["remaining"]        = round($pago["remaining"] - $aplicar, 2);
                        $monto_restante_cargo     = round($monto_restante_cargo - $aplicar, 2);
                    }

                    // --- Aplicar extemporáneo solo una vez ---
                    if ($pago["extemporaneos"] > 0 && !$pago["_extemporaneo_aplicado"]) {

                        $aplicados[] = [
                            "idPago"       => $pago["idPago"],
                            "montoPago"    => round($pago["extemporaneos"], 2),
                            "aplicado"     => round($pago["extemporaneos"], 2),
                            "fechaRegistro"=> $pago["fechaRegistro"],
                            "fechaPago"    => $fecha_venc,
                            "diasMora"     => null,
                            "extemporaneos"=> round($pago["extemporaneos"], 2)
                        ];

                        $pago["_extemporaneo_aplicado"] = true;
                    }
                }

                // -------------------------
                // CÁLCULOS FINALES
                // -------------------------
                $total_aplicado = round($monto_cargo - $monto_restante_cargo, 2);
                $pendiente      = round(max($monto_cargo - $total_aplicado, 0), 2);
                $excedente      = round(max($total_aplicado - $monto_cargo, 0), 2);

                $tabla[] = [
                    "cuota"         => $cuota_num,
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


            if (empty($resultado["data"]["idCredito"])) {
                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                $script_completo = $script . "\n" . $script_error;
                self::set("script", $script_completo);
                self::set("tabla", $tabla);
                return self::render("__SPARTA_SECRET_REDACTED___request");
            }
            else
            {
                self::set("dataCliente", $cliente);
                self::set("dataEstadoCuenta", $estadoCuenta);
                self::set("dataOtrosDatos", $otrosDatos);
                self::set("direcciones", $respDAO);
                self::set("referencias", $referencias);
                self::set("notas", $notas);


                self::set("titulo", "Resultado de la solicitud");
                self::set("script", $script);
                self::set("tabla", $tabla);
                return self::render("__SPARTA_SECRET_REDACTED___request");

            }


        }

        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Busqueda Gestiones SKY");
        self::set("script", $script);
        return self::render("__SPARTA_SECRET_REDACTED___consulta");
    }
    public function getclientesEstadoCuenta()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
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
    function api___SPARTA_SECRET_REDACTED__($idCredito, $fechaCorte) {

        $url = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";

        // --- Construir el JSON que la API externa espera ---
        $payload = json_encode([
            "idCredito" => intval($idCredito),
            "fechaCorte" => $fechaCorte
        ]);

        // --- Headers obligatorios ---
        $headers = [
            "Token: __SPARTA_TOKEN_REDACTED__",
            "Content-Type: application/json"
        ];

        // --- Preparar CURL ---
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // --- Ejecutar ---
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // --- Manejo de errores de CURL ---
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                "ok" => false,
                "status" => 0,
                "error" => "Error al conectar con servidor: $error",
                "data" => null
            ];
        }

        curl_close($ch);

        // --- Convertir JSON en arreglo ---
        $json = json_decode($response, true);

        // --- Si el JSON viene roto ---
        if ($json === null) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => "Respuesta no válida del servidor (JSON inválido)",
                "data" => null
            ];
        }

        // --- Si la API respondió error ---
        if ($httpCode !== 200) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => $json["mensaje"][0] ?? "No hay conexión con S2",
                "data" => $json
            ];
        }

        // --- Si no trae estadoCuenta ---
        if (!isset($json["estadoCuenta"])) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => "No se encontraron datos en la API",
                "data" => $json
            ];
        }

        // --- Todo OK ---
        return [
            "ok" => true,
            "status" => 200,
            "error" => null,
            "data" => $json["estadoCuenta"]
        ];
    }
    ///////////////////////////////////////

    public function documentacion()
    {
        $script = <<<JS
        <script>
            document.addEventListener('DOMContentLoaded', () => {
            
                const form = document.getElementById('formBusqueda');
            
                form.addEventListener('submit', e => {
                    e.preventDefault();
            
                    const id   = document.getElementById('idCredito').value.trim();
                    const tipo = document.getElementById('tipoDocumento').value;
            
                    if (!id || !tipo) {
                        Swal.fire('Error', 'Datos incompletos', 'error');
                        return;
                    }
            
                    Swal.fire({
                        title: 'Procesando',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
            
                    fetch('/estadocuenta/descargar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, tipo })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
            
                        if (!data.success) {
                            Swal.fire('Error', data.mensaje, 'error');
                            return;
                        }
            
                        // Si es INE, mostrar ambas imágenes (frente y reverso)
                        if (data.tipo === 'INE') {
                            const imgFrente = document.getElementById('imgINEfrente');
                            const imgReverso = document.getElementById('imgINEreverso');
                            
                            // Configurar imágenes con carga desde servidor (sin descarga)
                            imgFrente.src = data.frente;
                            imgReverso.src = data.reverso;
                            
                            // Crear marcas de agua después de que las imágenes se carguen
                            imgFrente.onload = function() {
                                setTimeout(() => {
                                    if (typeof crearMarcasAgua === 'function') {
                                        crearMarcasAgua();
                                    }
                                }, 300);
                            };
                            
                            imgReverso.onload = function() {
                                setTimeout(() => {
                                    if (typeof crearMarcasAgua === 'function') {
                                        crearMarcasAgua();
                                    }
                                }, 300);
                            };
                            
                            // Mostrar modal de INE
                            new bootstrap.Modal(
                                document.getElementById('modalINE')
                            ).show();
                        } 
                        // Para FAD_DOC, EVIDENCIA, FACTURA, CONTRATO y otros documentos, usar el visor normal
                        else if (data.tipo && data.url) {
                            const visor = document.getElementById('visorDocumento');
                            if (visor) {
                                visor.src = data.url;
                                
                                // Aplicar zoom predeterminado de 125% después de cargar el iframe
                                visor.onload = function() {
                                    setTimeout(() => {
                                        if (typeof applyZoomDocumento === 'function') {
                                            applyZoomDocumento();
                                        }
                                    }, 500);
                                };
                                
                                // Aplicar zoom inmediatamente también
                                setTimeout(() => {
                                    if (typeof applyZoomDocumento === 'function') {
                                        applyZoomDocumento();
                                    }
                                }, 300);
                                
                                // Actualizar título del modal según el tipo
                                const modalTitle = document.querySelector('#modalDocumento .modal-title');
                                if (modalTitle) {
                                    const tipoNombre = {
                                        'FAD_DOC': 'Contrato Firmado',
                                        'EVIDENCIA': 'Foto Entrega Moto',
                                        'FACTURA': 'Factura',
                                        'CONTRATO': 'Validaciones'
                                    };
                                    modalTitle.textContent = tipoNombre[data.tipo] || 'Documento';
                                }
                                
                                // Mostrar información de debug en consola si está disponible
                                if (data.archivo) {
                                    console.log('Documento cargado:', {
                                        tipo: data.tipo,
                                        archivo: data.archivo,
                                        carpeta: data.carpeta || 'N/A',
                                        url: data.url
                                    });
                                }
                                
                                const modal = new bootstrap.Modal(
                                    document.getElementById('modalDocumento')
                                );
                                modal.show();
                                
                                // Aplicar zoom después de que el modal se muestre completamente
                                modal._element.addEventListener('shown.bs.modal', function() {
                                    setTimeout(() => {
                                        if (typeof applyZoomDocumento === 'function') {
                                            applyZoomDocumento();
                                        }
                                    }, 500);
                                }, { once: true });
                                
                                // Manejar errores de carga del iframe
                                visor.onerror = function() {
                                    console.error('Error cargando documento:', data);
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'No se pudo cargar el documento. Verifique que el archivo exista en el servidor.',
                                        icon: 'error',
                                        footer: data.archivo ? 'Archivo: ' + data.archivo : ''
                                    });
                                };
                                
                            } else {
                                Swal.fire('Error', 'No se pudo cargar el visor de documentos', 'error');
                            }
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
                        Swal.fire('Error', 'Error de comunicación', 'error');
                    });
                });
            
            });
            </script>



JS;




        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Busqueda Gestiones SKY");
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
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Body vacío'
                ]);
                exit;
            }

            $input = json_decode($raw, true);

            if (!$input) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'JSON inválido'
                ]);
                exit;
            }

            $id   = $input['id']   ?? null;
            $tipo = strtoupper($input['tipo'] ?? '');

            if (!$id || !$tipo) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Parámetros incompletos'
                ]);
                exit;
            }

            // ---------------- FACTURA ----------------
            if ($tipo === 'FACTURA') {
                $fileUrl = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=FACTURA/{$id}_factura.pdf";
                
                // 🔥 GOOGLE VIEWER
                $viewer = "https://docs.google.com/gview?url=" . urlencode($fileUrl) . "&embedded=true";
                
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $viewer
                ]);
                exit;
            }

            // ---------------- CONTRATO ----------------
            elseif ($tipo === 'CONTRATO') {
                $fileUrl = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=VALIDACIONES/{$id}_validaciones.pdf";
                
                // 🔥 GOOGLE VIEWER
                $viewer = "https://docs.google.com/gview?url=" . urlencode($fileUrl) . "&embedded=true";
                
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $viewer
                ]);
                exit;
            }
            // ---------------- INE ----------------
            elseif ($tipo === 'INE') {

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
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'No se pudo obtener idCliente desde estado de cuenta'
                    ]);
                    exit;
                }

                $idCliente = $data['estadoCuenta']['datosCliente']['idCliente'];

                // URLs directas para frente y reverso del INE
                $urlFrente = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_frente.jpeg";
                $urlReverso = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_reverso.jpeg";

                // Para INE, devolvemos ambas URLs en lugar de usar Google Viewer
                echo json_encode([
                    'success' => true,
                    'tipo' => 'INE',
                    'frente' => $urlFrente,
                    'reverso' => $urlReverso
                ]);
                exit;
            }


            // ----------------  FAD / EVIDENCIA ----------------
            elseif ($tipo === 'FAD_DOC' || $tipo === 'EVIDENCIA') {
                $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, $tipo);

                if (!$res['success']) {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => $res['mensaje'] . ' (Tipo: ' . $tipo . ', ID: ' . $id . ')'
                    ]);
                    exit;
                }

                if (!isset($res['datos']) || !is_array($res['datos'])) {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error: Datos del documento no válidos (Tipo: ' . $tipo . ')'
                    ]);
                    exit;
                }

                if (!isset($res['datos']['nombre_archivo']) || empty($res['datos']['nombre_archivo'])) {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'No se encontró el archivo del documento solicitado. (Tipo: ' . $tipo . ', ID: ' . $id . ')'
                    ]);
                    exit;
                }

                $archivo = basename($res['datos']['nombre_archivo']);
                $carpeta = $tipo === 'FAD_DOC' ? 'FAD' : 'EVIDENCIA';

                // Validar que el archivo tenga extensión válida
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $extensionesValidas = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($extension, $extensionesValidas)) {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Tipo de archivo no soportado: ' . $extension . ' (Archivo: ' . $archivo . ')'
                    ]);
                    exit;
                }

                $fileUrl = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName={$carpeta}/{$archivo}";
                
                // 🔥 GOOGLE VIEWER (solo para documentos que no sean INE)
                $viewer = "https://docs.google.com/gview?url=" . urlencode($fileUrl) . "&embedded=true";
                
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $viewer,
                    'archivo' => $archivo,
                    'carpeta' => $carpeta
                ]);
                exit;
            }
            // ---------------- Tipo no válido ----------------
            else {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Tipo de documento no válido. Tipos permitidos: FACTURA, CONTRATO, INE, FAD_DOC, EVIDENCIA'
                ]);
                exit;
            }

        } catch (\Throwable $e) {

            echo json_encode([
                'success' => false,
                'mensaje' => 'Error interno',
                'debug'   => $e->getMessage()
            ]);
            exit;
        }
    }


    public function AddNote()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $data = [
            'id_credito' => (int)($input['id_credito'] ?? 0),
            'nota'       => trim($input['nota'] ?? ''),
            'usuario'    => $_SESSION['usuario'] ?? 'Sistema',
            'usuario_id'    => $_SESSION['usuario_id'] ?? '1'
        ];

        $resultado = EstadoCuentaDAO::insertNotas($data);

        // 👇 NORMALIZAMOS RESPUESTA PARA JS
        echo json_encode([
            'success' => true,
            'mensaje' => 'Nota agregada correctamente.',
            'data' => [
                'usuario' => $_SESSION['usuario'] ?? 'Operador'
            ]
        ]);
        exit;
    }

    public function getNotasCredito()
    {
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


    public function getTiposContacto()
    {
        $resultado = EstadoCuentaDAO::getTiposContacto();
        self::respuestaJSON($resultado);
    }

    public function getResultadosContacto()
    {
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
        $resultado = EstadoCuentaDAO::getMotivosNoPago();
        self::respuestaJSON($resultado);
    }

    public function getPlataformas()
    {
        $resultado = EstadoCuentaDAO::getPlataformas();
        self::respuestaJSON($resultado);
    }

    public function getTiposMotivoNoPago()
    {
        $resultado = EstadoCuentaDAO::getTiposMotivoNoPago();
        self::respuestaJSON($resultado);
    }

    public function getMotivosNoPagoPorTipo()
    {
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

    public function guardarDictamen()
    {
        $input = json_decode(file_get_contents("php://input"), true);

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
            'usuario_id'                 => $_SESSION['usuario_id'] ?? 1
        ];

        // 🔴 Validación mínima backend
        if (
            !$data['id_credito'] ||
            !$data['tipo_contacto_id'] ||
            !$data['resultado_contacto_id'] ||
            !$data['dictamen_id'] ||
            !$data['comentarios']
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






}
