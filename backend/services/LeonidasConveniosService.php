<?php

namespace Services;

/**
 * Conocimiento operativo verificado del módulo de Convenios.
 *
 * Las reglas críticas se responden de forma determinista para evitar que el
 * modelo confunda ofertas, convenios, pagos y permisos.
 */
class LeonidasConveniosService
{
    public function resolver(string $mensaje, ?string $normalizado = null): ?array
    {
        $normalizado = $normalizado !== null
            ? $this->normalizar($normalizado)
            : $this->normalizar($mensaje);

        if (!$this->esConsultaDeConvenios($normalizado) || !$this->esPregunta($normalizado)) {
            return null;
        }

        if ($this->contiene($normalizado, ['pendiente de conciliar', 'pendiente conciliar', 'conciliacion', 'conciliar pago'])) {
            return $this->respuesta(
                'pago_pendiente_conciliar',
                "\"Pendiente de conciliar\" significa que ya se cargó un comprobante para una semana pendiente, vencida o parcial, pero el pago todavía no está confirmado como pagado. El sistema conserva la evidencia, la fecha y el comentario, y deja la semana en revisión. Para pasar a \"conciliado\" debe registrarse la conciliación con el monto pagado, el monto aplicado, cualquier sobrante, la fecha y el usuario que la realizó. Si la validación se hace contra S2, el sistema busca pagos desde 3 días antes de la fecha de la cuota hasta 6 días después. Subir un comprobante por sí solo no liquida la semana."
            );
        }

        if ($this->contiene($normalizado, ['plazo', 'semanas', 'cuantas semanas', 'cuanto tiempo'])
            && !$this->contiene($normalizado, ['cancelar', 'cancelacion'])) {
            return $this->respuesta(
                'plazo_maximo',
                'No existe un plazo máximo único para todos los convenios. El sistema lo calcula con el producto de convenio y el monto del adeudo. Primero busca el rango de monto configurado en producto_convenio_plazos_monto y toma sus semanas máximas; si no existe un rango aplicable, usa el periodo final configurado en el producto. Por eso dos créditos pueden tener plazos máximos diferentes. Al preparar un convenio, Leónidas debe consultar las ofertas vigentes del crédito y mostrar el rango real permitido antes de pedir las semanas.'
            );
        }

        if ($this->contiene($normalizado, ['reactivar', 'reactivacion', 'incumplido'])) {
            return $this->respuesta(
                'reactivacion',
                'Un convenio incumplido no se reactiva como si fuera el mismo registro. Lo que puede reactivarse es la oferta del producto para permitir un convenio nuevo. El crédito no debe tener otro convenio activo, debe existir historial previo del producto y la reactivación debe quedar solicitada y aprobada por usuarios con los permisos correspondientes. Una vez aprobada, el sistema vuelve a calcular la oferta con los datos actuales y, si sigue siendo válida, se crea un convenio nuevo ligado al convenio de origen y a la petición de reactivación. La operación queda auditada.'
            );
        }

        if ($this->contiene($normalizado, ['cancelar', 'cancelacion', 'cancela'])) {
            if ($this->contiene($normalizado, ['tiempo', 'cuando', 'cuantos dias', 'cuanto tarda', 'plazo'])) {
                return $this->respuesta(
                    'tiempo_cancelacion',
                    'Para la cancelación automática, el sistema revisa la primera cuota que sigue pendiente y espera más de 3 días naturales después de su fecha de pago. Antes de cancelar consulta S2: si encuentra el pago o detecta el crédito liquidado, no cancela el convenio y actualiza el pago correspondiente. Si no encuentra respaldo de pago, cancela el convenio y las cuotas que aún no estaban pagadas. La cancelación manual no depende de esperar esos 3 días: puede solicitarse o ejecutarse de inmediato, pero exige motivo, permisos y auditoría.'
                );
            }

            return $this->respuesta(
                'causas_cancelacion',
                'El sistema distingue dos caminos. En el automático toma la primera cuota pendiente, verifica que hayan pasado más de 3 días naturales desde su vencimiento y consulta S2 para confirmar que no exista pago ni liquidación. Solo entonces cancela el convenio y sus cuotas no pagadas. En el manual, un usuario autorizado debe indicar el motivo; según sus permisos, la cancelación queda como solicitud para autorización o se ejecuta directamente. Un convenio ya completado o cancelado no vuelve a cancelarse.'
            );
        }

        if ($this->contiene($normalizado, ['modificar', 'modifico', 'editar', 'cambiar convenio', 'actualizar convenio'])) {
            return $this->respuesta(
                'modificacion',
                'El flujo actual no permite editar libremente las condiciones comerciales ni el calendario de un convenio activo. El PDF del convenio sí puede reemplazarse, y los comprobantes o conciliaciones se administran por separado. Si deben cambiarse producto, descuento, plazo, pago inicial o fechas, se cancela el convenio con motivo y permisos, se vuelve a calcular la oferta vigente y se crea uno nuevo; cuando corresponda, antes debe aprobarse la reactivación de la oferta. Esto evita alterar un acuerdo activo sin trazabilidad.'
            );
        }

        if ($this->contiene($normalizado, ['requiere', 'requisitos', 'obtener', 'elegible', 'puede tener', 'aplica para'])) {
            return $this->respuesta(
                'elegibilidad',
                'Para obtener un convenio, el crédito debe existir en la fuente operativa, no tener un convenio activo ni uno completado que lo bloquee, y normalmente estar desde 8 días de mora en adelante. Además, debe existir un producto activo cuyo bucket incluya al crédito, cumplir el avance mínimo de pago cuando el producto lo exija y tener una base monetaria válida, ya sea adeudo total o saldo de capital según la configuración. Si ese producto tuvo un convenio cancelado, la oferta queda bloqueada hasta que una reactivación sea solicitada y aprobada. Finalmente, el usuario necesita acceso al módulo y permiso para registrar el convenio.'
            );
        }

        return $this->respuesta(
            'general',
            'Convenios calcula ofertas con datos actuales del crédito, productos activos y reglas de bucket, avance, monto, descuento y plazo. Un convenio nuevo requiere vista previa y confirmación. Los pagos se validan contra S2 o pasan por conciliación; los incumplimientos, cancelaciones y reactivaciones siguen flujos separados y auditados. Puedes preguntarme por elegibilidad, plazo, pagos pendientes de conciliar, cancelación, reactivación o modificación.'
        );
    }

    /** @return array<string, mixed> */
    public function conocimiento(): array
    {
        return [
            'plazo' => 'Es dinámico por producto y rango de adeudo; si no hay rango usa el periodo final del producto.',
            'elegibilidad' => 'Crédito existente, sin convenio activo, no completado, normalmente con 8 o más días de mora, producto activo, bucket permitido y avance mínimo cuando aplique.',
            'reactivacion' => 'Reactiva la oferta para crear un convenio nuevo; no revive el convenio anterior.',
            'cancelacion_automatica' => 'Primera cuota pendiente con más de 3 días naturales de atraso y sin pago o liquidación confirmada en S2.',
            'cancelacion_manual' => 'Requiere motivo, permiso y auditoría; puede pasar por solicitud/autorización o ejecutarse directamente.',
            'pendiente_conciliar' => 'Existe comprobante cargado, pero el pago aún no está conciliado ni confirmado como pagado.',
            'modificacion' => 'No se editan libremente condiciones o calendario de un convenio activo; se cancela y se genera uno nuevo con oferta recalculada.',
            'ventana_s2_pago' => 'Desde 3 días antes de la fecha de la cuota hasta 6 días después.',
        ];
    }

    private function respuesta(string $tema, string $mensaje): array
    {
        return [
            'mensaje' => $mensaje,
            'tipo' => 'consulta_convenios',
            'tema' => $tema,
            'fuente' => 'reglas_operativas_convenios',
            'datos' => $this->conocimiento(),
        ];
    }

    private function esConsultaDeConvenios(string $mensaje): bool
    {
        return $this->contiene($mensaje, [
            'convenio',
            'oferta de convenio',
            'pendiente de conciliar',
            'pendiente conciliar',
        ]);
    }

    private function esPregunta(string $mensaje): bool
    {
        if ($this->contiene($mensaje, [
            'que ', 'como ', 'cual ', 'cuanto ', 'cuantos ', 'cuando ',
            'se puede', 'significa', 'explica', 'dime', 'por que', 'porque',
        ])) {
            return true;
        }

        return str_contains($mensaje, '?');
    }

    /** @param list<string> $terminos */
    private function contiene(string $mensaje, array $terminos): bool
    {
        foreach ($terminos as $termino) {
            if (str_contains($mensaje, $termino)) {
                return true;
            }
        }
        return false;
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $convertido === false ? $texto : $convertido;
    }
}
