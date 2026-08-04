<?php

require_once __DIR__ . '/../backend/services/LeonidasAppearanceService.php';

use Services\LeonidasAppearanceService;

function appearanceAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$themes = LeonidasAppearanceService::temas();
appearanceAssert(count($themes) >= 5, 'Leonidas debe ofrecer al menos cinco temas.');
appearanceAssert(
    LeonidasAppearanceService::predeterminada()['id'] === 'corporativo',
    'El tema corporativo debe ser el predeterminado.'
);
appearanceAssert(
    LeonidasAppearanceService::predeterminada()['color_principal'] === '#0048B7'
        && LeonidasAppearanceService::predeterminada()['color_secundario'] === '#D2D854'
        && LeonidasAppearanceService::predeterminada()['color_metal'] === '#D7E0EA'
        && LeonidasAppearanceService::predeterminada()['casco_modelo'] === 'original',
    'El tema corporativo debe conservar la paleta Maxikash azul, verde lima y plata.'
);

$classic = LeonidasAppearanceService::normalizarSolicitud([
    'tema' => 'clasico',
    'color_principal' => '#FFFFFF',
    'color_secundario' => '#FFFFFF',
    'color_metal' => '#FFFFFF',
]);
appearanceAssert($classic['color_principal'] === '#A91E2C', 'Un tema de catálogo debe usar su paleta oficial.');

$custom = LeonidasAppearanceService::normalizarSolicitud([
    'tema' => 'personalizado',
    'color_principal' => '#123abc',
    'color_secundario' => '#445566',
    'color_metal' => '#AABBCC',
]);
appearanceAssert($custom['color_principal'] === '#123ABC', 'Los colores personalizados deben normalizarse.');
appearanceAssert(
    $custom['casco_visible'] === true
        && $custom['pechera_visible'] === true
        && $custom['cabello_visible'] === true
        && $custom['escudo_visible'] === false
        && $custom['lanza_visible'] === false,
    'La armadura y el cabello deben permanecer visibles; el equipo de mano debe iniciar oculto.'
);

$withoutHelmet = LeonidasAppearanceService::normalizarSolicitud([
    'tema' => 'clasico',
    'casco_visible' => false,
    'casco_modelo' => 'aqueo',
    'pechera_visible' => true,
    'cabello_visible' => false,
    'escudo_visible' => false,
    'lanza_visible' => true,
]);
appearanceAssert(
    $withoutHelmet['casco_visible'] === false
        && $withoutHelmet['casco_modelo'] === 'aqueo'
        && $withoutHelmet['pechera_visible'] === true
        && $withoutHelmet['cabello_visible'] === false
        && $withoutHelmet['escudo_visible'] === false
        && $withoutHelmet['lanza_visible'] === true,
    'La visibilidad de piezas modulares debe conservarse junto con un tema.'
);

$invalid = false;
try {
    LeonidasAppearanceService::normalizarSolicitud([
        'tema' => 'personalizado',
        'color_principal' => 'red',
        'color_secundario' => '#445566',
        'color_metal' => '#AABBCC',
    ]);
} catch (InvalidArgumentException $error) {
    $invalid = str_contains($error->getMessage(), 'hexadecimal');
}
appearanceAssert($invalid, 'Debe rechazar colores que no sean #RRGGBB.');

$invalidVisibility = false;
try {
    LeonidasAppearanceService::normalizarSolicitud([
        'tema' => 'corporativo',
        'casco_visible' => 'oculto',
    ]);
} catch (InvalidArgumentException $error) {
    $invalidVisibility = str_contains($error->getMessage(), 'verdadera o falsa');
}
appearanceAssert($invalidVisibility, 'Debe rechazar una visibilidad ambigua.');

$invalidHelmet = false;
try {
    LeonidasAppearanceService::normalizarSolicitud([
        'tema' => 'corporativo',
        'casco_modelo' => 'desconocido',
    ]);
} catch (InvalidArgumentException $error) {
    $invalidHelmet = str_contains($error->getMessage(), 'modelo de casco');
}
appearanceAssert($invalidHelmet, 'Debe rechazar modelos de casco no autorizados.');

echo "LeonidasAppearanceService: OK\n";
