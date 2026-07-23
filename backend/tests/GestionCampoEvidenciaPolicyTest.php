<?php

use PHPUnit\Framework\TestCase;
use Services\GestionCampoEvidenciaPolicy;

final class GestionCampoEvidenciaPolicyTest extends TestCase
{
    public function testPerfilEtapa2SustituyeVideoClientePorIne(): void
    {
        $slots = GestionCampoEvidenciaPolicy::slotsRequeridos(GestionCampoEvidenciaPolicy::PERFIL_ETAPA2);
        self::assertContains('fis_ine_frente', $slots);
        self::assertContains('fis_ine_reverso', $slots);
        self::assertNotContains('fis_video_cliente_acuerdo', $slots);
    }

    public function testPerfilLegacyPermaneceCompatible(): void
    {
        $slots = GestionCampoEvidenciaPolicy::slotsRequeridos(GestionCampoEvidenciaPolicy::PERFIL_LEGACY);
        self::assertContains('fis_video_cliente_acuerdo', $slots);
        self::assertNotContains('fis_ine_frente', $slots);
    }

    public function testEtapa2ExigeAmbosLadosDeIne(): void
    {
        $presentes = GestionCampoEvidenciaPolicy::slotsRequeridos(GestionCampoEvidenciaPolicy::PERFIL_ETAPA2);
        $presentes = array_values(array_diff($presentes, ['fis_ine_reverso']));
        $resultado = GestionCampoEvidenciaPolicy::evaluar($presentes, GestionCampoEvidenciaPolicy::PERFIL_ETAPA2);
        self::assertFalse($resultado['completo']);
        self::assertSame(['fis_ine_reverso'], $resultado['faltantes']);
    }
}

