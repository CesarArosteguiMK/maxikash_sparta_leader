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
$appearanceCss = file_get_contents($root . '/public/assets/css/leonidas-assistant.css');
$builder = file_get_contents($root . '/scripts/construir_leonidas_modular.py');
$equipmentBuilder = file_get_contents($root . '/scripts/leonidas_equipment_v2.py');
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
        && str_contains($appearanceJs, 'casco_modelo')
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
        && str_contains($threeJs, 'previewRotationTarget')
        && str_contains($threeJs, 'settleHand(leftHand')
        && str_contains($threeJs, 'settleHand(rightHand')
        && str_contains($threeJs, 'UPRIGHT_ANIMATION_MIN = 6.0')
        && str_contains($threeJs, 'UPRIGHT_ANIMATION_MAX = 6.24')
        && str_contains($threeJs, "pelvis = findBone(model, ['hips', 'pelvis'])")
        && str_contains($threeJs, 'settleBone(spineLower, 0.94)')
        && str_contains($threeJs, 'settleBone(leftLeg, 0.9 * idleLegWeight)')
        && str_contains($threeJs, 'settleBone(rightLeg, 0.9 * idleLegWeight)')
        && str_contains($threeJs, "leftShoulder = findBone(model, ['leftshoulder', 'claviclel'])")
        && str_contains($threeJs, 'orientRelaxedHand')
        && str_contains($threeJs, 'applyRelaxedFingers')
        && str_contains($threeJs, 'relaxedFingerRotations.set')
        && str_contains($threeJs, 'poseRelaxedShoulder')
        && str_contains($threeJs, 'bodyForward, lengths.upperLength * 0.035')
        && str_contains($threeJs, 'armOutward, lengths.foreLength * 0.04')
        && str_contains($threeJs, 'previewBaseMarginPixels')
        && str_contains($threeJs, 'previewCenterCorrection')
        && str_contains($threeJs, "? 'forward-spear-grip-v15'")
        && str_contains($threeJs, ": 'anatomical-idle-v15'")
        && str_contains($threeJs, "pose === 'spear'")
        && str_contains($threeJs, 'orientSpearHand')
        && str_contains($threeJs, "leonidasGreetingPose = 'single-hand-wave-v2'")
        && str_contains($threeJs, "'spear-open-palm-salute-v3'")
        && str_contains($threeJs, "'armed-guard-nod-v2'")
        && str_contains($threeJs, "pose === 'chest_salute'")
        && str_contains($threeJs, 'orientChestSaluteHand')
        && str_contains($threeJs, 'applyOpenFingers(leftFingerBones, greetingWeight * 0.94)')
        && str_contains($threeJs, 'applyRelaxedFingers(leftFingerBones, greetingWeight * 0.34)')
        && str_contains($threeJs, 'applySpearGrip(rightFingerBones, greetingWeight)')
        && str_contains($threeJs, 'getSpearGripWorldPosition')
        && str_contains($threeJs, "leonidasSpearWristInclination = '-3.2deg'")
        && str_contains($threeJs, "leonidasSpearAnchor = 'dynamic-grip-centered-static-orientation-v5'")
        && str_contains($threeJs, 'const supportArmWeight = Math.max(')
        && str_contains($threeJs, 'const spearChestSaluteWeight = currentAppearance.lanza_visible')
        && str_contains($threeJs, 'settleBone(shieldArm, 0.72 * leftArmRestWeight)')
        && str_contains($threeJs, "leftHand,\n                        'idle',\n                        leftArmRestWeight * 0.96")
        && str_contains($threeJs, 'applyRelaxedFingers(leftFingerBones, leftArmRestWeight * 0.96)')
        && str_contains($appearanceCss, 'height: min(690px, calc(100vh - 2rem))')
        && str_contains($appearanceCss, 'scrollbar-gutter: stable'),
    'La vista previa debe girar el modelo y sostener una postura erguida medida sobre el rig.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, "root.addEventListener('leonidas:appearance'")
        && str_contains($threeJs, 'applyModularPalette')
        && str_contains($threeJs, 'syncAppearanceHelmet')
        && str_contains($threeJs, 'leonidas-aqueo-dark-production-v16.glb')
        && str_contains($threeJs, 'attachQaHelmetToHead')
        && str_contains($threeJs, 'head.attach(qaHelmetContainer)')
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
        && str_contains($builder, 'leonidasHelmetOriginalFaces')
        && str_contains($builder, 'leonidasHelmetOpenFace')
        && str_contains($builder, 'leonidasHelmetScale')
        && str_contains($builder, 'leonidasHelmetLift')
        && str_contains($builder, 'leonidasHelmetConstruction')
        && str_contains($builder, 'leonidasHelmetFaceOpening')
        && str_contains($builder, "assign_solid_palette_material(helmet, 'metal')")
        && str_contains($builder, "'original-source'")
        && str_contains($builder, "'t-visor-integrated-nose-v4'")
        && str_contains($builder, "'integrated-contoured-prism-v2'")
        && !str_contains($builder, 'finish_sculpted_helmet(helmet')
        && str_contains($builder, 'assign_chest_semantic_materials')
        && !str_contains($builder, "modifier.operation = 'DIFFERENCE'")
        && str_contains($threeJs, 'leonidasRoughnessOffset')
        && str_contains($threeJs, 'leonidasTone')
        && str_contains($threeJs, 'modularParts.headUnderlay.visible = true'),
    'Los colores deben usar materiales semanticos y el casco debe conservar el rostro anatómico.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, '|| region === RIG_REGION.chest')
        && str_contains($view, 'Pechera, grebas, brazales y broches')
        && str_contains($view, 'Correas, ribetes y acentos'),
    'La pechera debe usar el canal metal y no contaminar el color secundario.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, 'footwear: 6')
        && str_contains($threeJs, 'footRatio > 0.32')
        && str_contains($threeJs, "paintTriangle(triangle, '#000000')")
        && str_contains($builder, 'protected_lower_leg = (')
        && str_contains($builder, 'center.z < 0.50')
        && str_contains($builder, 'metal_limb_ratio > 0.08'),
    'Grebas y calzado deben conservarse fuera del canal metalico amarillo.'
);
appearanceIntegrationAssert(
    str_contains($threeJs, 'return sampledPixelIsSkin')
        && str_contains($builder, 'def open_original_helmet_face(obj):')
        && str_contains($builder, "'t-visor-integrated-nose-v4'")
        && str_contains($builder, 'bmesh.ops.bisect_plane(')
        && str_contains($builder, 'open_original_helmet_face(helmet)'),
    'La piel del rostro debe conservarse separada del material metalico del casco.'
);
appearanceIntegrationAssert(
    str_contains($view, 'data-leonidas-gear-controls hidden')
        && substr_count($view, 'data-leonidas-editor-section') >= 3
        && str_contains($view, '<details class="leonidas-appearance-section"')
        && str_contains($view, 'class="leonidas-helmet-selector"')
        && str_contains($appearanceJs, 'editorSections.forEach')
        && str_contains($appearanceJs, "section.removeAttribute('open')")
        && str_contains($view, 'data-leonidas-greeting-preview')
        && str_contains($view, 'Probar saludo')
        && str_contains($appearanceJs, 'function playGreetingPreview()')
        && str_contains($appearanceJs, "root.classList.add('is-greeting')")
        && str_contains($appearanceCss, '.leonidas-appearance-preview__greeting')
        && str_contains($appearanceJs, "root.addEventListener('leonidas:capabilities'")
        && str_contains($threeJs, "root.dispatchEvent(new CustomEvent('leonidas:capabilities'")
        && str_contains($threeJs, 'resolveModularParts')
        && str_contains($view, 'data-leonidas-part="cabello_visible"')
        && str_contains($view, 'data-leonidas-part="escudo_visible"')
        && str_contains($view, 'data-leonidas-part="lanza_visible"')
        && str_contains($view, 'data-leonidas-helmet-model')
        && str_contains($view, 'Áqueo oscuro')
        && str_contains($appearanceJs, 'cabello_visible')
        && str_contains($appearanceJs, 'escudo_visible')
        && str_contains($appearanceJs, 'lanza_visible')
        && str_contains($threeJs, 'currentAppearance.cabello_visible')
        && str_contains($threeJs, 'currentAppearance.escudo_visible')
        && str_contains($threeJs, 'currentAppearance.lanza_visible')
        && str_contains($threeJs, 'idleElbowWeight')
        && str_contains($appearanceJs, 'escudo_visible: false')
        && str_contains($appearanceJs, 'lanza_visible: false')
        && ($manifest['enabled'] ?? null) === true
        && ($manifest['requiredParts'] ?? []) === [
            'body',
            'helmet',
            'chest',
            'headUnderlay',
            'torsoUnderlay',
            'hair',
            'shield',
            'spear',
        ],
    'Los controles de piezas deben habilitarse únicamente después de validar el modelo modular real.'
);

appearanceIntegrationAssert(
    str_contains($builder, 'build_corporate_shield')
        && str_contains($builder, 'build_spartan_spear')
        && str_contains($builder, "'mixamorig:LeftForeArm'")
        && str_contains($builder, 'attach_static_to_current_rig(spear')
        && str_contains($equipmentBuilder, "'LeonidasShield'")
        && str_contains($equipmentBuilder, "'LeonidasSpear'")
        && str_contains($equipmentBuilder, "'leonidasProcedural'")
        && str_contains($equipmentBuilder, "'leonidasShieldRearGrip'")
        && str_contains($equipmentBuilder, "'convex-pbr-v2'")
        && str_contains($equipmentBuilder, "'forged-pbr-v2'")
        && str_contains($equipmentBuilder, "'leonidasShieldDimensions'")
        && str_contains($equipmentBuilder, "'leonidasSpearGrip'")
        && str_contains($equipmentBuilder, "'occluded-hand-channel-v2'")
        && str_contains($builder, 'precompensate_rigid_pose')
        && str_contains($builder, 'seconds=6.12')
        && str_contains($threeJs, 'preserveSpearOrientation')
        && str_contains($threeJs, 'preserveShieldOrientation')
        && str_contains($threeJs, 'applySpearGrip')
        && str_contains($threeJs, 'La lanza conserva su orientación estática')
        && str_contains($threeJs, 'prepareStaticSpearAnchor')
        && str_contains($threeJs, 'syncStaticSpearAnchor')
        && str_contains($threeJs, 'staticSpearGripLateralOffset = -0.052')
        && str_contains($threeJs, 'staticSpearGripForwardOffset = -0.020')
        && str_contains($threeJs, 'staticSpearGripVerticalOffset = 0.06')
        && str_contains($threeJs, 'Ningun gesto puede desplazarla')
        && str_contains($threeJs, 'dynamic-grip-centered-static-orientation-v5')
        && str_contains($threeJs, "pose === 'spear_walk'")
        && str_contains($threeJs, "'spear-guard-march-v1'")
        && str_contains($threeJs, 'applySpearGrip(rightFingerBones, walkWeight)')
        && str_contains($threeJs, 'armSwing * 0.38')
        && str_contains($threeJs, 'El equipamiento de mano no debe reducir')
        && ($manifest['parts']['shield'] ?? []) === [
            'LeonidasShield',
            'Shield',
        ]
        && ($manifest['parts']['spear'] ?? []) === [
            'LeonidasSpear',
            'Spear',
        ],
    'El escudo y la lanza deben ser piezas procedurales independientes y ancladas al brazo y la mano.'
);

echo "LeonidasAppearanceIntegration: OK\n";
