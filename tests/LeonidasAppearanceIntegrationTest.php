<?php

function appearanceIntegrationAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$view = file_get_contents($root . '/backend/core/View.php');
$controller = file_get_contents($root . '/backend/controllers/Leonidas.php');
$appearanceJs = file_get_contents($root . '/public/assets/js/leonidas-appearance.js');
$threeJs = file_get_contents($root . '/public/assets/js/leonidas-3d.js');
$manifest = json_decode(
    file_get_contents($root . '/public/assets/models/leonidas/leonidas-modular-manifest.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

appearanceIntegrationAssert(
    str_contains($view, '<?php if ($__mostrarLeonidas): ?>')
        && str_contains($view, 'data-leonidas-appearance-open'),
    'El acceso al editor debe depender del mismo permiso que muestra Leonidas.'
);
appearanceIntegrationAssert(
    str_contains($controller, 'exigirAccesoLeonidas()')
        && str_contains($controller, 'guardarApariencia')
        && str_contains($controller, 'restablecerApariencia'),
    'Los endpoints de apariencia deben exigir acceso a Leonidas.'
);
appearanceIntegrationAssert(
    str_contains($appearanceJs, '/Leonidas/obtenerApariencia')
        && str_contains($appearanceJs, '/Leonidas/guardarApariencia')
        && str_contains($appearanceJs, 'leonidas:appearance'),
    'El editor debe consultar, guardar y propagar la apariencia.'
);
appearanceIntegrationAssert(
    str_contains($view, 'data-leonidas-appearance-model')
        && str_contains($appearanceJs, 'attachLiveModel')
        && str_contains($appearanceJs, 'detachLiveModel')
        && str_contains($threeJs, 'is-appearance-preview-live'),
    'La vista previa debe reutilizar el mismo modelo 3D y devolverlo al cerrar el editor.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, "root.addEventListener('leonidas:appearance'")
        && str_contains($threeJs, 'new THREE.CanvasTexture'),
    'El modelo 3D debe aplicar la paleta a una textura propia.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, 'leonidas-spartan-rigged.fbx')
        && !str_contains($threeJs, 'LeonidasBareHead')
        && !str_contains($threeJs, 'LeonidasBareTorso')
        && !str_contains($threeJs, 'prepareRigParts'),
    'El editor debe conservar el modelo original y no fabricar anatomia ni separar armadura inexistente.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, 'buildRigRegionMask')
        && str_contains($threeJs, 'uvOwners')
        && str_contains($threeJs, 'triangleRegions')
        && str_contains($view, 'modelo actual conserva casco y pechera'),
    'Los colores deben aplicarse por islas UV completas y explicar el limite de la armadura soldada.'
);
appearanceIntegrationAssert(
    str_contains($view, 'data-leonidas-gear-controls hidden')
        && str_contains($appearanceJs, "root.addEventListener('leonidas:capabilities'")
        && str_contains($threeJs, "root.dispatchEvent(new CustomEvent('leonidas:capabilities'")
        && str_contains($threeJs, 'resolveModularParts')
        && ($manifest['enabled'] ?? null) === false
        && ($manifest['requiredParts'] ?? []) === ['body', 'helmet', 'chest'],
    'Los controles de piezas deben permanecer ocultos hasta cargar y validar un modelo modular real.'
);

echo "LeonidasAppearanceIntegration: OK\n";
