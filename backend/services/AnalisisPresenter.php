<?php

/**
 * Presenter / ViewModel: transforma el JSON de interpretación en vista fija (cascarón).
 * TODO lo visible sale de REGLAS + números verificados. NO se muestra texto libre de la IA.
 * Colores: verde >70%, amarillo 30–70%, rojo <30%. Máx. 2 frases por bloque.
 */

namespace Services;

class AnalisisPresenter
{
    private const UMBRAL_ALTO = 70;
    private const UMBRAL_BAJO = 30;

    /**
     * Convierte el JSON de interpretación en objeto listo para la UI (cascarón fijo).
     *
     * @param array $jsonInterpretacion overall_confidence, status_flags, key_findings, anomalies_detected, recommended_actions
     * @return array { resumen_caso, confianza_pct, confianza_color, cliente, gestion, pagos, acciones_sugeridas }
     */
    public function present(array $jsonInterpretacion): array
    {
        $confianza = (float) ($jsonInterpretacion['overall_confidence'] ?? 0.5);
        $confianzaPct = (int) round($confianza * 100);
        $confianzaColor = $this->pctAColor($confianzaPct);

        $statusFlags = $jsonInterpretacion['status_flags'] ?? [];
        $keyFindings = $jsonInterpretacion['key_findings'] ?? [];
        $metricas = $jsonInterpretacion['metricas_verificadas'] ?? [];
        $missingData = $jsonInterpretacion['missing_data'] ?? [];

        $resumenCaso = $this->textoResumenCaso($statusFlags, $keyFindings, $confianzaPct);
        $cliente = $this->bloqueCliente($statusFlags, $keyFindings, $metricas);
        $gestion = $this->bloqueGestion($statusFlags, $keyFindings, $metricas);
        $pagos = $this->bloquePagos($statusFlags, $keyFindings, $metricas);
        $accionesSugeridas = $this->bloqueAcciones($jsonInterpretacion['recommended_actions'] ?? []);

        $out = [
            'resumen_caso' => $resumenCaso,
            'confianza_pct' => $confianzaPct,
            'confianza_color' => $confianzaColor,
            'cliente' => $cliente,
            'gestion' => $gestion,
            'pagos' => $pagos,
            'acciones_sugeridas' => $accionesSugeridas,
        ];
        if (!empty($missingData)) {
            $out['missing_data'] = $missingData;
        }
        return $out;
    }

    private function pctAColor(int $pct): string
    {
        if ($pct > self::UMBRAL_ALTO) {
            return 'success';
        }
        if ($pct >= self::UMBRAL_BAJO) {
            return 'warning';
        }
        return 'danger';
    }

    private function estadoALabel(string $estado): string
    {
        $map = [
            'positivo' => 'Positivo',
            'neutral' => 'Neutral',
            'riesgo' => 'Riesgo',
            'critico' => 'Crítico',
        ];
        return $map[strtolower($estado)] ?? 'Neutral';
    }

    /**
     * Resumen del caso: texto humano corto por REGLAS, no por IA.
     */
    private function textoResumenCaso(array $statusFlags, array $keyFindings, int $confianzaPct): string
    {
        $gestion = strtolower((string) ($statusFlags['gestion'] ?? ''));
        $pagos = strtolower((string) ($statusFlags['pagos'] ?? ''));
        $cliente = strtolower((string) ($statusFlags['cliente'] ?? ''));

        $frases = [];
        if ($pagos === 'positivo' || ($pagos === 'neutral' && $cliente === 'positivo')) {
            $frases[] = 'El cliente mantiene pagos activos.';
        }
        if ($gestion === 'critico') {
            $frases[] = 'La gestión en campo presenta incumplimientos relevantes.';
        } elseif ($gestion === 'neutral') {
            $frases[] = 'La gestión en campo es regular.';
        }
        if ($pagos === 'riesgo') {
            $frases[] = 'Se detecta riesgo en el historial de pagos.';
        }
        if (empty($frases)) {
            $frases[] = 'Sin señales críticas en los datos actuales.';
        }
        return implode(' ', array_slice($frases, 0, 2));
    }

    /**
     * Bloque Cliente: estado, probabilidad por métricas verificadas o fallback.
     */
    private function bloqueCliente(array $statusFlags, array $keyFindings, array $metricas): array
    {
        $estado = strtolower((string) ($statusFlags['cliente'] ?? 'neutral'));
        if (array_key_exists('home_flag', $metricas)) {
            $pct = $metricas['home_flag'] ? 75 : 50;
        } else {
            $conf = isset($keyFindings['cliente']['confidence']) ? (float) $keyFindings['cliente']['confidence'] : 0.5;
            $pct = (int) round($conf * 100);
        }

        $texto = 'Domicilio no confirmado.';
        if ($estado === 'positivo') {
            $texto = 'Domicilio confirmado. Actividad en ubicación principal.';
        } elseif ($estado === 'neutral') {
            $texto = 'Datos de ubicación suficientes. Actividad externa detectada o no aplicable.';
        } elseif ($estado === 'riesgo') {
            $texto = 'Domicilio no confirmado. Actividad externa detectada o sin datos.';
        }

        return [
            'titulo' => 'Cliente',
            'estado' => $this->estadoALabel($estado),
            'estado_valor' => $estado,
            'probabilidad_pct' => $pct,
            'color' => $this->pctAColor($pct),
            'texto' => $texto,
            'icono' => 'fa-solid fa-user',
        ];
    }

    /**
     * Bloque Gestión: estado y efectividad SIEMPRE desde métricas verificadas (cumplimiento_promedio).
     */
    private function bloqueGestion(array $statusFlags, array $keyFindings, array $metricas): array
    {
        $estado = strtolower((string) ($statusFlags['gestion'] ?? 'neutral'));
        if (isset($metricas['cumplimiento_promedio']) && $metricas['cumplimiento_promedio'] !== null) {
            $pct = (int) round((float) $metricas['cumplimiento_promedio']);
        } else {
            $conf = isset($keyFindings['gestion']['confidence']) ? (float) $keyFindings['gestion']['confidence'] : 0.5;
            $pct = (int) round($conf * 100);
        }

        $texto = 'El desempeño en campo es regular.';
        if ($estado === 'critico') {
            $texto = 'El desempeño en campo es bajo. Se detectan visitas reiteradas lejos del cliente.';
        } elseif ($estado === 'positivo') {
            $texto = 'Gestión en campo adecuada. Visitas cercanas al domicilio.';
        }

        $worstGestor = $keyFindings['gestion']['worst_gestor'] ?? null;
        $worstGestorUi = null;
        if ($worstGestor !== null && is_array($worstGestor) && !empty($worstGestor['nombre'])) {
            $worstGestorUi = [
                'nombre' => $worstGestor['nombre'],
                'distancia_promedio_km' => (float) ($worstGestor['distancia_promedio_km'] ?? 0),
                'visitas_lejanas' => (int) ($worstGestor['visitas_lejanas'] ?? 0),
                'motivo' => $worstGestor['motivo'] ?? 'Visitas lejos del domicilio.',
            ];
        }

        return [
            'titulo' => 'Gestión',
            'estado' => $this->estadoALabel($estado),
            'estado_valor' => $estado,
            'efectividad_pct' => $pct,
            'color' => $this->pctAColor($pct),
            'texto' => $texto,
            'icono' => 'fa-solid fa-users',
            'worst_gestor' => $worstGestorUi,
        ];
    }

    /**
     * Bloque Pagos: estado, cumplimiento % desde métricas verificadas cuando existan.
     */
    private function bloquePagos(array $statusFlags, array $keyFindings, array $metricas): array
    {
        $estado = strtolower((string) ($statusFlags['pagos'] ?? 'neutral'));
        $pct = null;
        if (array_key_exists('last_payment_days', $metricas) && $metricas['last_payment_days'] !== null) {
            $dias = (int) $metricas['last_payment_days'];
            $pct = $dias <= 30 ? 80 : ($dias <= 60 ? 50 : 25);
        }
        if ($pct === null && array_key_exists('total_pagos', $metricas)) {
            $pct = ($metricas['total_pagos'] ?? 0) > 0 ? 50 : 25;
        }
        if ($pct === null) {
            $conf = isset($keyFindings['pagos']['confidence']) ? (float) $keyFindings['pagos']['confidence'] : 0.5;
            $pct = (int) round($conf * 100);
        }

        $texto = 'Historial de pagos irregular o datos limitados.';
        if ($estado === 'positivo') {
            $texto = 'Hábito de pago activo.';
        } elseif ($estado === 'riesgo') {
            $texto = 'Hábito de pago en riesgo o irregular.';
        }

        return [
            'titulo' => 'Pagos',
            'estado' => $this->estadoALabel($estado),
            'estado_valor' => $estado,
            'cumplimiento_pct' => $pct,
            'color' => $this->pctAColor($pct),
            'texto' => $texto,
            'icono' => 'fa-solid fa-money-bill-wave',
        ];
    }

    /**
     * Acciones sugeridas: cards con accion, prioridad, justificacion (de datos, no IA libre).
     */
    private function bloqueAcciones(array $recommendedActions): array
    {
        $out = [];
        $prioridadOrden = ['alta' => 1, 'media' => 2, 'baja' => 3];
        foreach (array_slice($recommendedActions, 0, 5) as $a) {
            $accion = is_string($a) ? $a : (trim((string) ($a['accion'] ?? '')));
            if ($accion === '') {
                continue;
            }
            $prioridad = strtolower((string) ($a['prioridad'] ?? 'media'));
            if (!isset($prioridadOrden[$prioridad])) {
                $prioridad = 'media';
            }
            $justificacion = is_array($a) ? trim((string) ($a['justificacion'] ?? '')) : '';
            $out[] = [
                'accion' => $accion,
                'prioridad' => $prioridad,
                'prioridad_label' => ucfirst($prioridad),
                'justificacion' => $justificacion,
                'color' => $prioridad === 'alta' ? 'danger' : ($prioridad === 'media' ? 'warning' : 'secondary'),
            ];
        }
        return $out;
    }
}
