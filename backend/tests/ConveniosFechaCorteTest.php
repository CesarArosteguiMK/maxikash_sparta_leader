<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/core/Model.php';
require_once dirname(__DIR__) . '/models/Convenios.php';

final class ConveniosFechaCorteTest extends TestCase
{
    public function testCalendarioDinamicoPermanecePausado(): void
    {
        self::assertFalse(\Models\Convenios::CALENDARIO_DINAMICO_ACTIVO);
    }

    private function invokePrivate(string $metodo, array $argumentos = [])
    {
        $reflection = new ReflectionClass(\Models\Convenios::class);
        $method = $reflection->getMethod($metodo);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $argumentos);
    }

    public function testFechaCorteAceptaPasadoYRechazaFuturo(): void
    {
        self::assertSame('2026-01-15', $this->invokePrivate('_normalizarFechaCorte', ['2026-01-15']));
        self::assertNull($this->invokePrivate('_normalizarFechaCorte', [date('Y-m-d', strtotime('+1 day'))]));
        self::assertNull($this->invokePrivate('_normalizarFechaCorte', ['2026-02-31']));
    }

    public function testRespuestaS2SeConvierteEnFotografiaHistoricaParaOfertas(): void
    {
        $estadoCuenta = [
            'idCredito' => 12345,
            'montoOtorgado' => 50000,
            'statusCredito' => 'Vigente',
            'datosCliente' => ['nombreCliente' => 'CLIENTE HISTORICO'],
            'datosSaldos' => [
                'diasMoraMaximo' => 17,
                'cuotasContratadas' => 100,
                'cuotasPagadas' => 85,
                'saldoVigenteCapital' => 9000,
                'adeudoTotal' => 10500,
            ],
        ];

        $credito = $this->invokePrivate(
            '_creditoDesdeEstadoCuentaS2',
            [$estadoCuenta, '2026-01-01', ['Sucursal' => 'CENTRO']]
        );

        self::assertSame(12345, $credito['Id_credito']);
        self::assertSame('d) 15 a 21 dias', $credito['Bucket_Morosidad_Real']);
        self::assertSame(17, $credito['Dias_mora']);
        self::assertSame('81-100%', $credito['Avance_Pago_Plazo']);
        self::assertSame(9000.0, $credito['Saldo_total_capital']);
        self::assertSame(10500.0, $credito['Adeudo_total']);
        self::assertSame('2026-01-01', $credito['Fecha_corte_consulta']);
        self::assertSame('S2', $credito['Origen_consulta']);
    }
}
