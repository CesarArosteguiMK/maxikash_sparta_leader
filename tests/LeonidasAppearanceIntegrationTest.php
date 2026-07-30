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
$builder = file_get_contents($root . '/scripts/construir_leonidas_modular.py');
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
        && str_contains($view, 'Arrastra para girar')
        && str_contains($appearanceJs, 'attachLiveModel')
        && str_contains($appearanceJs, 'detachLiveModel')
        && str_contains($threeJs, 'is-appearance-preview-live')
        && str_contains($threeJs, "canvas.addEventListener('pointerdown'")
        && str_contains($threeJs, "canvas.addEventListener('pointermove'")
        && str_contains($threeJs, 'previewRotationTarget'),
    'La vista previa debe reutilizar el modelo 3D, permitir girarlo y devolverlo al cerrar el editor.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, "root.addEventListener('leonidas:appearance'")
        && str_contains($threeJs, 'applyModularPalette')
        && str_contains($threeJs, 'useRigTexture: false'),
    'El modelo modular debe aplicar la paleta por materiales, no repintando el atlas.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, 'leonidas-spartan-rigged.fbx')
        && str_contains($threeJs, 'spartan-modular-v2')
        && str_contains($builder, 'LeonidasHeadUnderlay')
        && str_contains($builder, 'LeonidasTorsoUnderlay')
        && str_contains($builder, 'LeonidasHair'),
    'El modelo modular debe conservar el FBX como respaldo y aportar anatomia reconstruida.'
);
appearanceIntegrationAssert(
    str_contains($builder, 'assign_body_semantic_materials')
        && str_contains($builder, 'uv_face_components')
        && str_contains($builder, 'LeonidasPrimary')
        && str_contains($builder, 'LeonidasSecondary')
        && str_contains($builder, 'LeonidasMetal')
        && str_contains($builder, 'finish_sculpted_helmet')
        && str_contains($builder, 'LeonidasVisorMaterial')
        && str_contains($builder, 'LeonidasCrestRed')
        && str_contains($builder, 'leonidasHelmetOriginalFaces')
        && str_contains($builder, 'leonidasHelmetScale')
        && str_contains($builder, 'leonidasHelmetLift')
        && str_contains($builder, 'leonidasHelmetCrestFaces')
        && str_contains($builder, 'LeonidasHelmetPatina')
        && str_contains($builder, 'LeonidasHelmetHighlight')
        && str_contains($builder, 'assign_chest_semantic_materials')
        && !str_contains($builder, "modifier.operation = 'DIFFERENCE'")
        && str_contains($threeJs, 'leonidasRoughnessOffset')
        && str_contains($threeJs, 'leonidasTone')
        && str_contains(
            $threeJs,
            'modularParts.headUnderlay.visible = !currentAppearance.casco_visible'
        ),
    'Los colores deben usar materiales semanticos y el casco debe ocultar el rostro tras una visera propia.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, '|| region === RIG_REGION.chest')
        && str_contains($view, 'Casco, pechera, grebas y brazales')
        && str_contains($view, 'Correas, ribetes y acentos'),
    'La pechera debe usar el canal metal y no contaminar el color secundario.'
);
appearanceIntegrationAssert(
    str_contains($view, 'data-leonidas-gear-controls hidden')
        && str_contains($appearanceJs, "root.addEventListener('leonidas:capabilities'")
        && str_contains($threeJs, "root.dispatchEvent(new CustomEvent('leonidas:capabilities'")
        && str_contains($threeJs, 'resolveModularParts')
        && str_contains($view, 'data-leonidas-part="cabello_visible"')
        && str_contains($appearanceJs, 'cabello_visible')
        && str_contains($threeJs, 'currentAppearance.cabello_visible')
        && ($manifest['enabled'] ?? null) === true
        && ($manifest['requiredParts'] ?? []) === [
            'body',
            'helmet',
            'chest',
            'headUnderlay',
            'torsoUnderlay',
            'hair',
        ],
    'Los controles de piezas deben habilitarse únicamente después de validar el modelo modular real.'
);

echo "LeonidasAppearanceIntegration: OK\n";
