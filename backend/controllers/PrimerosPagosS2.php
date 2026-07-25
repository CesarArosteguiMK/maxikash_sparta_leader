<?php

namespace Controllers;

use Core\Controller;
use Services\PrimerosPagosS2VerificationService;

final class PrimerosPagosS2 extends Controller
{
    /** Endpoint para Cloud Scheduler: POST /primerospagoss2/ejecutar */
    public function ejecutar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Use POST.']);
            return;
        }

        // En Cloud Run se recomienda bloquear el servicio con IAM y Scheduler OIDC.
        // El secreto es una segunda barrera para ambientes sin IAM (local/legado).
        $secret = trim((string) getenv('SEGUNDOMETRO_PRIMEROS_PAGOS_CRON_SECRET'));
        if ($secret !== '') {
            $recibido = (string) ($_SERVER['HTTP_X_CRON_SECRET'] ?? '');
            if (!hash_equals($secret, $recibido)) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.']);
                return;
            }
        }

        try {
            $input = json_decode((string) file_get_contents('php://input'), true);
            $limite = is_array($input) ? (int) ($input['limite'] ?? 250) : 250;
            echo json_encode((new PrimerosPagosS2VerificationService())->ejecutar($limite), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[PrimerosPagosS2] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'Falló la verificación de primeros pagos.']);
        }
    }
}
