import * as THREE from '../vendor/three/three.module.min.js';
import { GLTFLoader } from '../vendor/three/addons/loaders/GLTFLoader.js';
import { FBXLoader } from '../vendor/three/addons/loaders/FBXLoader.js';

const root = document.getElementById('leonidasAssistant');
const canvas = root?.querySelector('[data-leonidas-canvas]');

if (root && canvas) {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(25, 1, 0.1, 50);
    const characterAnchor = new THREE.Group();
    const loader = new GLTFLoader();
    const fbxLoader = new FBXLoader();
    const textureLoader = new THREE.TextureLoader();
    const leonidasColorTexture = textureLoader.load(
        '/assets/models/leonidas/leonidas-spartan-color.webp',
        () => root.dispatchEvent(new CustomEvent('leonidas:texture-ready'))
    );
    const leonidasNormalTexture = textureLoader.load('/assets/models/leonidas/leonidas-spartan-normal.webp');
    const clock = new THREE.Clock();
    const baseRotations = new Map();
    const openFingerRotations = new Map();
    const poseEuler = new THREE.Euler();
    const poseQuaternion = new THREE.Quaternion();
    const aimBonePosition = new THREE.Vector3();
    const aimChildPosition = new THREE.Vector3();
    const aimCurrentDirection = new THREE.Vector3();
    const aimTargetDirection = new THREE.Vector3();
    const aimBoneWorldQuaternion = new THREE.Quaternion();
    const aimParentWorldQuaternion = new THREE.Quaternion();
    const aimDeltaQuaternion = new THREE.Quaternion();
    const aimDesiredWorldQuaternion = new THREE.Quaternion();
    const bodyWorldQuaternion = new THREE.Quaternion();
    const bodyUp = new THREE.Vector3();
    const bodyForward = new THREE.Vector3();
    const armOutward = new THREE.Vector3();
    const armShoulderPosition = new THREE.Vector3();
    const armElbowPosition = new THREE.Vector3();
    const armHandPosition = new THREE.Vector3();
    const armTorsoPosition = new THREE.Vector3();
    const armTargetElbow = new THREE.Vector3();
    const armTargetHand = new THREE.Vector3();

    scene.add(characterAnchor);
    camera.position.set(0, 0, 4.8);
    camera.lookAt(0, 0, 0);
    leonidasColorTexture.colorSpace = THREE.SRGBColorSpace;
    // The FBX uses the conventional texture orientation. Keeping these maps
    // unflipped mixed skin, cloth and armour across unrelated UV islands.
    leonidasColorTexture.flipY = true;
    leonidasNormalTexture.flipY = true;

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({
            canvas,
            alpha: true,
            antialias: true,
            powerPreference: 'high-performance'
        });
    } catch (error) {
        root.classList.add('is-3d-fallback');
    }

    if (renderer) {
        renderer.setClearColor(0x000000, 0);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.18;
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        const hemisphere = new THREE.HemisphereLight(0xf8faff, 0x76543e, 3.05);
        const keyLight = new THREE.DirectionalLight(0xffffff, 3.35);
        const fillLight = new THREE.DirectionalLight(0xffd8c2, 1.25);
        const rimLight = new THREE.DirectionalLight(0x9db9ee, 1.25);
        keyLight.position.set(3.5, 4.5, 5);
        fillLight.position.set(-3.5, 2.5, 4);
        rimLight.position.set(-3.5, 2.5, -2);
        scene.add(hemisphere, keyLight, fillLight, rimLight);

        const characterHeight = 1.98;
        let characterScale = 1;
        let anchorBaseX = 0;
        let anchorBaseY = 0;
        let patrolDistance = 0;
        let activeModel = null;
        let activeUsesRigTexture = false;
        let modularParts = null;
        let currentAppearance = normalizeAppearance(root._leonidasAppearance);
        let rigAppearanceTexture = null;
        let rigSourceTexture = leonidasColorTexture;
        let appearanceTimer = null;
        const rigMaterials = new Set();
        let rigRegionMask = null;
        let rigRegionMaskModel = null;
        let rigSkinMask = null;
        let pelvis = null;
        let spine = null;
        let head = null;
        let shieldArm = null;
        let shieldForeArm = null;
        let swordArm = null;
        let swordForeArm = null;
        let leftHand = null;
        let rightHand = null;
        let rightUpLeg = null;
        let rightLeg = null;
        let leftUpLeg = null;
        let leftLeg = null;
        let rightFingerBones = [];
        let leftFingerBones = [];
        let greetingHandRotation = null;
        let mixer = null;
        let nativeAnimationTime = 0.45;
        let nativeAnimationDirection = 1;
        let lastPixelCheck = 0;
        let greetingWeight = 0;
        let victoryWeight = 0;
        let walkWeight = 0;
        let patrolX = 0;
        let deliveryWalk = null;
        const frontalRotation = -0.28;
        let previewRotationTarget = 0;
        let previewPointerId = null;
        let previewPointerX = 0;

        function normalizeHex(value, fallback) {
            const color = String(value || '').trim().toUpperCase();
            return /^#[0-9A-F]{6}$/.test(color) ? color : fallback;
        }

        function normalizeAppearance(value) {
            return {
                id: String(value?.id || 'corporativo'),
                color_principal: normalizeHex(value?.color_principal, '#0048B7'),
                color_secundario: normalizeHex(value?.color_secundario, '#D2D854'),
                color_metal: normalizeHex(value?.color_metal, '#D7E0EA'),
                casco_visible: value?.casco_visible !== false
                    && value?.casco_visible !== 0
                    && value?.casco_visible !== '0',
                pechera_visible: value?.pechera_visible !== false
                    && value?.pechera_visible !== 0
                    && value?.pechera_visible !== '0',
                cabello_visible: value?.cabello_visible !== false
                    && value?.cabello_visible !== 0
                    && value?.cabello_visible !== '0',
                escudo_visible: value?.escudo_visible === true
                    || value?.escudo_visible === 1
                    || value?.escudo_visible === '1',
                lanza_visible: value?.lanza_visible === true
                    || value?.lanza_visible === 1
                    || value?.lanza_visible === '1'
            };
        }

        function hexToRgb(value) {
            const color = normalizeHex(value, '#000000');
            return [
                parseInt(color.slice(1, 3), 16),
                parseInt(color.slice(3, 5), 16),
                parseInt(color.slice(5, 7), 16)
            ];
        }

        function hexToNumber(value, fallback) {
            const normalized = normalizeHex(value, fallback);
            return parseInt(normalized.slice(1), 16);
        }

        function scaledHexToNumber(value, fallback, scale) {
            const normalized = normalizeHex(value, fallback);
            const channels = [
                parseInt(normalized.slice(1, 3), 16),
                parseInt(normalized.slice(3, 5), 16),
                parseInt(normalized.slice(5, 7), 16)
            ].map((channel) => Math.max(
                0,
                Math.min(255, Math.round(channel * scale))
            ));
            return (channels[0] << 16) | (channels[1] << 8) | channels[2];
        }

        const normalizedBoneName = (name) => String(name || '')
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '');

        const normalizedPartName = (name) => String(name || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]/g, '');

        const resolveModularParts = (model, manifest) => {
            const resolved = {};
            const definitions = manifest?.parts || {};
            model.traverse((node) => {
                const declaredRole = normalizedPartName(node.userData?.leonidasPart);
                const nodeName = normalizedPartName(node.name);
                Object.entries(definitions).forEach(([role, aliases]) => {
                    if (resolved[role]) return;
                    const allowedNames = (Array.isArray(aliases) ? aliases : [])
                        .map(normalizedPartName);
                    if (
                        declaredRole === normalizedPartName(role)
                        || allowedNames.includes(nodeName)
                    ) {
                        resolved[role] = node;
                    }
                });
            });
            const required = Array.isArray(manifest?.requiredParts)
                ? manifest.requiredParts
                : ['body', 'helmet', 'chest'];
            const valid = required.every((role) => resolved[role]?.isObject3D)
                && resolved.helmet !== resolved.body
                && resolved.chest !== resolved.body
                && resolved.helmet !== resolved.chest;
            return valid ? resolved : null;
        };

        const applyModularVisibility = () => {
            if (!modularParts) return;
            modularParts.helmet.visible = currentAppearance.casco_visible;
            modularParts.chest.visible = currentAppearance.pechera_visible;
            if (modularParts.headUnderlay) {
                // La anatomía permanece separada detrás de la careta y nunca
                // hereda el color del metal.
                modularParts.headUnderlay.visible = true;
            }
            if (modularParts.torsoUnderlay) {
                modularParts.torsoUnderlay.visible = !currentAppearance.pechera_visible;
            }
            if (modularParts.hair) {
                modularParts.hair.visible = !currentAppearance.casco_visible
                    && currentAppearance.cabello_visible;
            }
            if (modularParts.shield) {
                modularParts.shield.visible = currentAppearance.escudo_visible;
            }
            if (modularParts.spear) {
                modularParts.spear.visible = currentAppearance.lanza_visible;
            }
        };

        const applyModularPalette = (model) => {
            const palette = {
                primary: {
                    color: hexToNumber(currentAppearance.color_principal, '#0048B7'),
                    metalness: 0.06,
                    roughness: 0.72
                },
                secondary: {
                    color: hexToNumber(currentAppearance.color_secundario, '#D2D854'),
                    metalness: 0.03,
                    roughness: 0.68
                },
                metal: {
                    color: scaledHexToNumber(
                        currentAppearance.color_metal,
                        '#D7E0EA',
                        0.82
                    ),
                    metalness: 0.52,
                    roughness: 0.42
                }
            };
            model.traverse((node) => {
                if (!node.isMesh) return;
                const materials = Array.isArray(node.material) ? node.material : [node.material];
                materials.forEach((material) => {
                    const role = normalizedPartName(
                        material.userData?.leonidasPalette
                        || node.userData?.leonidasPalette
                        || ''
                    );
                    const style = palette[role];
                    if (!style || !material.color) return;
                    material.color.setHex(style.color);
                    const tone = Number(
                        material.userData?.leonidasTone || 1
                    );
                    if (Number.isFinite(tone) && tone !== 1) {
                        material.color.multiplyScalar(tone);
                    }
                    material.metalness = style.metalness;
                    const roughnessOffset = Number(
                        material.userData?.leonidasRoughnessOffset || 0
                    );
                    material.roughness = THREE.MathUtils.clamp(
                        style.roughness + (
                            Number.isFinite(roughnessOffset)
                                ? roughnessOffset
                                : 0
                        ),
                        0.08,
                        0.92
                    );
                    material.needsUpdate = true;
                });
            });
            applyModularVisibility();
            root.dataset.leonidasAppliedAppearance = currentAppearance.id;
        };

        const findBone = (model, candidates) => {
            let match = null;
            model.traverse((node) => {
                if (match || !node.isBone) return;
                const name = normalizedBoneName(node.name);
                if (candidates.some((candidate) => name.includes(candidate))) match = node;
            });
            return match;
        };

        const rememberRotation = (bone) => {
            if (bone && !baseRotations.has(bone)) baseRotations.set(bone, bone.quaternion.clone());
        };

        const resetBone = (bone) => {
            const base = bone ? baseRotations.get(bone) : null;
            if (base) bone.quaternion.copy(base);
        };

        const offsetBone = (bone, x = 0, y = 0, z = 0) => {
            const base = bone ? baseRotations.get(bone) : null;
            if (!base) return;
            poseEuler.set(x, y, z);
            bone.quaternion.copy(base).multiply(new THREE.Quaternion().setFromEuler(poseEuler));
        };

        const offsetAnimatedBone = (bone, x = 0, y = 0, z = 0) => {
            if (!bone) return;
            poseEuler.set(x, y, z);
            poseQuaternion.setFromEuler(poseEuler);
            bone.quaternion.multiply(poseQuaternion);
        };

        const aimBoneAtWorldPoint = (bone, child, target, weight = 1) => {
            if (!bone || !child || !bone.parent || weight <= 0.001) return;
            activeModel.updateMatrixWorld(true);
            bone.getWorldPosition(aimBonePosition);
            child.getWorldPosition(aimChildPosition);
            aimCurrentDirection.subVectors(aimChildPosition, aimBonePosition);
            aimTargetDirection.subVectors(target, aimBonePosition);
            if (aimCurrentDirection.lengthSq() < 0.000001 || aimTargetDirection.lengthSq() < 0.000001) return;

            aimCurrentDirection.normalize();
            aimTargetDirection.normalize();
            aimTargetDirection.lerp(aimCurrentDirection, 1 - THREE.MathUtils.clamp(weight, 0, 1)).normalize();
            aimDeltaQuaternion.setFromUnitVectors(aimCurrentDirection, aimTargetDirection);
            bone.getWorldQuaternion(aimBoneWorldQuaternion);
            aimDesiredWorldQuaternion.copy(aimDeltaQuaternion).multiply(aimBoneWorldQuaternion);
            bone.parent.getWorldQuaternion(aimParentWorldQuaternion).invert();
            bone.quaternion.copy(aimParentWorldQuaternion.multiply(aimDesiredWorldQuaternion));
            bone.updateMatrixWorld(true);
        };

        const bodyBasis = () => {
            activeModel.getWorldQuaternion(bodyWorldQuaternion);
            bodyUp.set(0, 1, 0).applyQuaternion(bodyWorldQuaternion).normalize();
            bodyForward.set(0, 0, 1).applyQuaternion(bodyWorldQuaternion).normalize();
        };

        const armMeasurements = (upperArm, foreArm, hand) => {
            activeModel.updateMatrixWorld(true);
            upperArm.getWorldPosition(armShoulderPosition);
            foreArm.getWorldPosition(armElbowPosition);
            hand.getWorldPosition(armHandPosition);
            (spine || pelvis).getWorldPosition(armTorsoPosition);
            armOutward.subVectors(armShoulderPosition, armTorsoPosition)
                .addScaledVector(bodyUp, -armOutward.dot(bodyUp))
                .addScaledVector(bodyForward, -armOutward.dot(bodyForward));
            if (armOutward.lengthSq() < 0.000001) armOutward.set(1, 0, 0);
            armOutward.normalize();
            return {
                upperLength: Math.max(armShoulderPosition.distanceTo(armElbowPosition), 0.001),
                foreLength: Math.max(armElbowPosition.distanceTo(armHandPosition), 0.001)
            };
        };

        const poseArm = (upperArm, foreArm, hand, pose, weight, phase = 0) => {
            if (!activeModel || !upperArm || !foreArm || !hand || weight <= 0.001) return;
            bodyBasis();
            const lengths = armMeasurements(upperArm, foreArm, hand);

            if (pose === 'walk') {
                armTargetElbow.copy(armShoulderPosition)
                    .addScaledVector(bodyUp, -lengths.upperLength * 0.82)
                    .addScaledVector(bodyForward, lengths.upperLength * phase * 0.32)
                    .addScaledVector(armOutward, lengths.upperLength * 0.035);
                armTargetHand.copy(armTargetElbow)
                    .addScaledVector(bodyUp, -lengths.foreLength * 0.72)
                    .addScaledVector(bodyForward, lengths.foreLength * phase * 0.2)
                    .addScaledVector(armOutward, -lengths.foreLength * 0.025);
            } else if (pose === 'greeting') {
                const wave = Math.sin(clock.elapsedTime * 5.2);
                armTargetElbow.copy(armShoulderPosition)
                    .addScaledVector(armOutward, lengths.upperLength * 0.58)
                    .addScaledVector(bodyUp, -lengths.upperLength * 0.04)
                    .addScaledVector(bodyForward, lengths.upperLength * 0.16);
                armTargetHand.copy(armTargetElbow)
                    .addScaledVector(bodyUp, lengths.foreLength * 0.78)
                    .addScaledVector(armOutward, lengths.foreLength * (0.05 + wave * 0.16))
                    .addScaledVector(bodyForward, lengths.foreLength * 0.28);
            } else if (pose === 'victory') {
                const pulse = Math.sin(clock.elapsedTime * 2.8) * 0.025;
                // Bent elbows and raised fists read as celebration. Keeping the
                // hands slightly inward avoids the old surrender/T-pose shape.
                armTargetElbow.copy(armShoulderPosition)
                    .addScaledVector(armOutward, lengths.upperLength * 0.72)
                    .addScaledVector(bodyUp, lengths.upperLength * (0.46 + pulse))
                    .addScaledVector(bodyForward, lengths.upperLength * 0.16);
                armTargetHand.copy(armTargetElbow)
                    .addScaledVector(armOutward, -lengths.foreLength * 0.22)
                    .addScaledVector(bodyUp, lengths.foreLength * 0.78)
                    .addScaledVector(bodyForward, lengths.foreLength * 0.12);
            } else {
                // Descanso anatómico: el codo baja casi vertical, pero la mano
                // avanza delante del muslo. La flexión se calcula en espacio
                // mundial para no depender del eje local irregular del FBX.
                armTargetElbow.copy(armShoulderPosition)
                    .addScaledVector(bodyUp, -lengths.upperLength * 0.91)
                    .addScaledVector(armOutward, lengths.upperLength * 0.08)
                    .addScaledVector(bodyForward, lengths.upperLength * 0.06);
                armTargetHand.copy(armTargetElbow)
                    .addScaledVector(bodyUp, -lengths.foreLength * 0.88)
                    .addScaledVector(armOutward, -lengths.foreLength * 0.05)
                    .addScaledVector(bodyForward, lengths.foreLength * 0.34);
            }

            aimBoneAtWorldPoint(upperArm, foreArm, armTargetElbow, weight);
            aimBoneAtWorldPoint(foreArm, hand, armTargetHand, weight);
        };

        const applyOpenFingers = (bones, weight) => {
            if (weight <= 0.001) return;
            bones.forEach((bone) => {
                const openRotation = openFingerRotations.get(bone);
                if (openRotation) bone.quaternion.slerp(openRotation, weight);
            });
        };

        const layoutCharacter = (width, height) => {
            const visibleHeight = 2 * camera.position.z * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
            const visibleWidth = visibleHeight * camera.aspect;
            const previewingAppearance = root.classList.contains('is-appearance-preview-live');
            const desiredHeight = previewingAppearance
                ? Math.min(height * 0.66, width * 1.38)
                : width <= 575 ? 210 : 300;
            const edgeMargin = (previewingAppearance ? 140 : width <= 575 ? 12 : 22) * visibleHeight / height;

            characterScale = desiredHeight * visibleHeight / (height * characterHeight);
            characterAnchor.scale.setScalar(characterScale);
            characterAnchor.position.set(
                previewingAppearance ? 0 : visibleWidth / 2 - characterScale * 0.96 - edgeMargin,
                -visibleHeight / 2 + edgeMargin,
                0
            );
            anchorBaseX = characterAnchor.position.x;
            anchorBaseY = characterAnchor.position.y;
            // Convert a clearly visible screen-space route to world units. The
            // previous 72 px route was almost imperceptible on desktop and made
            // the walk look like a treadmill animation.
            const patrolPixels = width <= 575
                ? Math.min(92, width * 0.24)
                : Math.min(260, Math.max(170, width * 0.18));
            patrolDistance = patrolPixels * visibleWidth / width;
        };

        const resize = () => {
            const width = Math.max(1, canvas.clientWidth);
            const height = Math.max(1, canvas.clientHeight);
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            layoutCharacter(width, height);
        };

        const applyPresence = (elapsed, delta) => {
            const greetingTarget = !root.classList.contains('is-victory') && root.classList.contains('is-greeting') ? 1 : 0;
            const victoryTarget = root.classList.contains('is-victory') ? 1 : 0;
            let walkingTarget = deliveryWalk ? 1 : 0;
            greetingWeight = THREE.MathUtils.damp(greetingWeight, greetingTarget, 7, delta);
            victoryWeight = THREE.MathUtils.damp(victoryWeight, victoryTarget, 7, delta);
            walkWeight = THREE.MathUtils.damp(walkWeight, walkingTarget, 7, delta);

            const breath = Math.sin(elapsed * 1.65);
            const interaction = Math.max(greetingWeight, victoryWeight);
            const acknowledgement = Math.sin(elapsed * 5.3) * interaction;
            const walkPhase = elapsed * 7.2;
            const stride = Math.sin(walkPhase) * 0.42 * walkWeight;
            const armSwing = Math.sin(walkPhase) * walkWeight;
            const walkBob = Math.abs(Math.sin(walkPhase)) * 0.018 * walkWeight;
            let patrolTarget = 0;
            let facingTarget = frontalRotation;
            if (deliveryWalk) {
                const rawProgress = THREE.MathUtils.clamp(
                    (elapsed - deliveryWalk.startedAt) / deliveryWalk.duration,
                    0,
                    1
                );
                const progress = THREE.MathUtils.smoothstep(rawProgress, 0, 1);
                const travelDistance = Math.max(patrolDistance, characterScale * 1.25);
                if (deliveryWalk.direction === 'arrive') {
                    patrolTarget = travelDistance * (1 - progress);
                    facingTarget = frontalRotation - Math.PI / 2;
                } else {
                    patrolTarget = travelDistance * progress;
                    facingTarget = frontalRotation + Math.PI / 2;
                }

                if (rawProgress >= 1) {
                    const completedDirection = deliveryWalk.direction;
                    deliveryWalk = null;
                    walkingTarget = 0;
                    patrolX = 0;
                    patrolTarget = 0;
                    facingTarget = frontalRotation;
                    root.dispatchEvent(new CustomEvent('leonidas:delivery-walk-complete', {
                        detail: { direction: completedDirection }
                    }));
                }
            }
            patrolX = THREE.MathUtils.damp(patrolX, patrolTarget, walkingTarget > 0 ? 12 : 7, delta);
            if (activeModel) {
                const desiredRotation = root.classList.contains('is-appearance-preview-live')
                    ? frontalRotation + previewRotationTarget
                    : facingTarget;
                activeModel.rotation.y = THREE.MathUtils.damp(
                    activeModel.rotation.y,
                    desiredRotation,
                    previewPointerId === null ? 8.5 : 18,
                    delta
                );
                root.dataset.leonidasPreviewRotation = previewRotationTarget.toFixed(3);
            }

            // Idle is deliberately vertical only. The old side-to-side movement made
            // the character look unstable and exaggerated the source T-pose.
            characterAnchor.position.x = anchorBaseX + patrolX;
            characterAnchor.position.y = anchorBaseY
                + (breath * 0.004 * characterScale)
                + (interaction * 0.006 * characterScale)
                + (walkBob * characterScale);
            if (!mixer) {
                resetBone(pelvis);
                resetBone(spine);
                resetBone(head);
                resetBone(shieldArm);
                resetBone(shieldForeArm);
                resetBone(swordArm);
                resetBone(swordForeArm);
                resetBone(leftHand);
                resetBone(rightHand);
                offsetBone(pelvis, breath * 0.006 + acknowledgement * 0.006, 0, 0);
                offsetBone(spine, breath * 0.014 + acknowledgement * 0.018, 0, 0);
            } else {
                // Apply gestures after the native clip so the offsets build on the
                // evaluated pose instead of fighting the animation mixer.
                if (victoryWeight > 0.001) {
                    poseArm(swordArm, swordForeArm, rightHand, 'victory', victoryWeight);
                    poseArm(shieldArm, shieldForeArm, leftHand, 'victory', victoryWeight);
                } else if (greetingWeight > 0.001) {
                    poseArm(swordArm, swordForeArm, rightHand, 'greeting', greetingWeight);
                    if (rightHand && greetingHandRotation) {
                        rightHand.quaternion.slerp(greetingHandRotation, greetingWeight * 0.9);
                    }
                    applyOpenFingers(rightFingerBones, greetingWeight * 0.96);
                }

                if (walkWeight > 0.001 && interaction < 0.02) {
                    offsetAnimatedBone(rightUpLeg, stride, 0, 0);
                    offsetAnimatedBone(leftUpLeg, -stride, 0, 0);
                    offsetAnimatedBone(rightLeg, -Math.max(0, stride) * 1.45, 0, 0);
                    offsetAnimatedBone(leftLeg, -Math.max(0, -stride) * 1.45, 0, 0);
                    // Arms swing front/back close to the torso instead of rotating
                    // laterally on the rig's unusual local shoulder axes.
                    poseArm(swordArm, swordForeArm, rightHand, 'walk', walkWeight, -armSwing);
                    poseArm(shieldArm, shieldForeArm, leftHand, 'walk', walkWeight, armSwing);
                    applyOpenFingers(rightFingerBones, walkWeight * 0.98);
                    applyOpenFingers(leftFingerBones, walkWeight * 0.98);
                    offsetAnimatedBone(pelvis, 0, 0, Math.sin(walkPhase) * 0.035 * walkWeight);
                }

                // Una flexión mínima evita la postura rígida de brazos
                // completamente rectos. Se reduce durante gestos y caminata
                // para no competir con sus poses específicas.
                const idleElbowWeight = Math.max(
                    0,
                    1 - Math.max(interaction, walkWeight)
                );
                if (idleElbowWeight > 0.001) {
                    poseArm(
                        shieldArm,
                        shieldForeArm,
                        leftHand,
                        'idle',
                        idleElbowWeight * 0.86
                    );
                    poseArm(
                        swordArm,
                        swordForeArm,
                        rightHand,
                        'idle',
                        idleElbowWeight * 0.86
                    );
                }
            }
            root.dataset.leonidasMotion = victoryWeight > 0.08
                ? 'victory'
                : greetingWeight > 0.08
                    ? 'greeting'
                    : walkWeight > 0.08
                        ? 'walking'
                        : 'idle';
            root.dataset.leonidasFacing = Math.abs(facingTarget - frontalRotation) < 0.2
                ? 'front'
                : facingTarget < frontalRotation ? 'left' : 'right';
            root.dataset.leonidasPatrolX = patrolX.toFixed(3);
        };

        const animate = () => {
            const delta = Math.min(clock.getDelta(), 0.05);
            if (mixer) {
                // The source clip contains a rig calibration pose around seconds
                // 5-7. Play only its natural body-motion segment and ping-pong it
                // so Leonidas stays alive without forcing individual limbs.
                const speed = root.classList.contains('is-speaking')
                    ? 0.82
                    : root.classList.contains('is-victory')
                        ? 0.72
                        : root.classList.contains('is-greeting')
                            ? 0.66
                            : 0.48;
                nativeAnimationTime += delta * speed * nativeAnimationDirection;
                if (nativeAnimationTime >= 4.55) {
                    nativeAnimationTime = 4.55;
                    nativeAnimationDirection = -1;
                } else if (nativeAnimationTime <= 0.35) {
                    nativeAnimationTime = 0.35;
                    nativeAnimationDirection = 1;
                }
                mixer.setTime(nativeAnimationTime);
            }
            applyPresence(clock.elapsedTime, delta);
            renderer.render(scene, camera);
            if (clock.elapsedTime - lastPixelCheck >= 5) {
                lastPixelCheck = clock.elapsedTime;
                const gl = renderer.getContext();
                const sample = new Uint8Array(4);
                let visibleSamples = 0;
                const width = gl.drawingBufferWidth;
                const height = gl.drawingBufferHeight;
                for (let row = 1; row <= 7; row++) {
                    for (let column = 1; column <= 7; column++) {
                        const x = Math.floor(width * (0.58 + column * 0.05));
                        const y = Math.floor(height * (0.02 + row * 0.11));
                        gl.readPixels(
                            Math.min(width - 1, x),
                            Math.min(height - 1, y),
                            1,
                            1,
                            gl.RGBA,
                            gl.UNSIGNED_BYTE,
                            sample
                        );
                        if (sample[3] > 0) visibleSamples++;
                    }
                }
                root.dataset.leonidasPixelSamples = String(visibleSamples);
            }
            window.requestAnimationFrame(animate);
        };

        root.addEventListener('leonidas:delivery-walk', (event) => {
            const direction = event.detail?.direction === 'arrive' ? 'arrive' : 'depart';
            root.classList.remove('is-greeting', 'is-victory');
            deliveryWalk = {
                direction,
                startedAt: clock.elapsedTime,
                duration: direction === 'arrive' ? 1.45 : 1.6
            };
            if (direction === 'arrive') {
                patrolX = Math.max(patrolDistance, characterScale * 1.25);
            }
        });

        const RIG_REGION = Object.freeze({
            original: 0,
            primary: 1,
            secondary: 2,
            metal: 3,
            helmet: 4,
            chest: 5
        });

        const texturePixel = (pixels, width, height, uValue, vValue) => {
            const u = ((uValue % 1) + 1) % 1;
            const v = ((vValue % 1) + 1) % 1;
            const x = Math.min(width - 1, Math.max(0, Math.floor(u * width)));
            const textureV = modularParts ? v : (1 - v);
            const y = Math.min(
                height - 1,
                Math.max(0, Math.floor(textureV * height))
            );
            const offset = (y * width + x) * 4;
            return [
                pixels[offset],
                pixels[offset + 1],
                pixels[offset + 2],
                pixels[offset + 3]
            ];
        };

        const texturePixelIsSkin = ([red, green, blue, alpha]) => {
            if (alpha < 12) return false;
            const cb = 128 - (0.168736 * red) - (0.331264 * green) + (0.5 * blue);
            const cr = 128 + (0.5 * red) - (0.418688 * green) - (0.081312 * blue);
            return red > 72 && green > 31 && blue > 22
                && red > green * 1.04
                && green >= blue * 0.76
                && blue >= green * 0.66
                && cb > 70 && cb < 142 && cr > 132 && cr < 184;
        };

        const texturePixelLooksMetal = ([redByte, greenByte, blueByte, alpha]) => {
            if (alpha < 12) return false;
            const red = redByte / 255;
            const green = greenByte / 255;
            const blue = blueByte / 255;
            const max = Math.max(red, green, blue);
            const min = Math.min(red, green, blue);
            const saturation = max > 0 ? (max - min) / max : 0;
            const luminance = red * 0.299 + green * 0.587 + blue * 0.114;
            const neutral = saturation < 0.28 && luminance > 0.16;
            const bronze = red > blue * 1.08 && red >= green * 0.94
                && blue < green * 0.86 && luminance > 0.2 && saturation < 0.7;
            return neutral || bronze;
        };

        const buildTextureSkinMask = (pixels, width, height) => {
            if (rigSkinMask?.length === width * height) return rigSkinMask;
            const length = width * height;
            const candidates = new Uint8Array(length);
            const seeds = new Uint8Array(length);
            const visited = new Uint8Array(length);
            const queue = new Int32Array(length);
            const mask = new Uint8Array(length);
            for (let index = 0; index < length; index++) {
                const offset = index * 4;
                const red = pixels[offset];
                const green = pixels[offset + 1];
                const blue = pixels[offset + 2];
                const alpha = pixels[offset + 3];
                if (alpha < 12) continue;
                const luminance = red * 0.299 + green * 0.587 + blue * 0.114;
                const candidate = red > 48
                    && green > 27
                    && blue > 22
                    && red > green * 1.025
                    && red - blue > 13
                    && blue >= green * 0.59
                    && blue <= green * 1.03;
                if (!candidate) continue;
                candidates[index] = 1;
                if (
                    luminance > 78
                    && red > green * 1.105
                    && red - blue > 25
                    && green - blue > 5
                    && blue >= green * 0.82
                ) {
                    seeds[index] = 1;
                }
            }

            for (let start = 0; start < length; start++) {
                if (!candidates[start] || visited[start]) continue;
                let head = 0;
                let tail = 0;
                let seedCount = 0;
                const component = [];
                queue[tail++] = start;
                visited[start] = 1;
                while (head < tail) {
                    const current = queue[head++];
                    component.push(current);
                    seedCount += seeds[current];
                    const x = current % width;
                    const neighbors = [
                        current - width,
                        current + width,
                        x > 0 ? current - 1 : -1,
                        x + 1 < width ? current + 1 : -1
                    ];
                    neighbors.forEach((neighbor) => {
                        if (
                            neighbor >= 0
                            && neighbor < length
                            && candidates[neighbor]
                            && !visited[neighbor]
                        ) {
                            visited[neighbor] = 1;
                            queue[tail++] = neighbor;
                        }
                    });
                }
                const requiredSeeds = Math.max(3, Math.ceil(component.length * 0.02));
                if (seedCount >= requiredSeeds) {
                    component.forEach((pixelIndex) => {
                        mask[pixelIndex] = 1;
                    });
                }
            }
            rigSkinMask = mask;
            return mask;
        };

        const texturePixelMaterial = ([redByte, greenByte, blueByte, alpha]) => {
            if (alpha < 12) return 'original';
            const red = redByte / 255;
            const green = greenByte / 255;
            const blue = blueByte / 255;
            const maximum = Math.max(red, green, blue);
            const minimum = Math.min(red, green, blue);
            const saturation = maximum > 0 ? (maximum - minimum) / maximum : 0;
            const luminance = red * 0.299 + green * 0.587 + blue * 0.114;
            const neutralMetal = saturation < 0.2 && luminance > 0.28;
            const brightBronze = red > green * 1.04
                && green > blue * 1.08
                && luminance > 0.48
                && saturation < 0.5;
            const leather = red > green * 1.035
                && green > blue * 1.025
                && luminance > 0.12
                && luminance < 0.58
                && saturation > 0.12;
            if (neutralMetal || brightBronze) return 'metal';
            if (luminance < 0.23) return 'cloth';
            if (leather) return 'leather';
            return 'cloth';
        };

        const resolveRigPixelRegion = (region, pixel, protectedSkin) => {
            if (
                region === RIG_REGION.secondary
                && (protectedSkin || texturePixelIsSkin(pixel))
            ) {
                return RIG_REGION.original;
            }
            const material = texturePixelMaterial(pixel);
            if (region === RIG_REGION.helmet || region === RIG_REGION.metal) {
                return RIG_REGION.metal;
            }
            if (region === RIG_REGION.chest) {
                return RIG_REGION.metal;
            }
            if (region === RIG_REGION.primary) {
                if (material === 'metal') return RIG_REGION.metal;
                if (material === 'leather') return RIG_REGION.secondary;
                return RIG_REGION.primary;
            }
            if (region === RIG_REGION.secondary) {
                return material === 'metal'
                    ? RIG_REGION.metal
                    : RIG_REGION.secondary;
            }
            return region;
        };

        const rigTriangleRegion = (
            node,
            vertices,
            positions,
            uvs,
            skinIndices,
            skinWeights,
            sourcePixels,
            width,
            height
        ) => {
            const boneScores = new Map();
            let centerX = 0;
            let centerY = 0;
            let centerZ = 0;
            let centerU = 0;
            let centerV = 0;
            vertices.forEach((vertex) => {
                centerX += positions.getX(vertex);
                centerY += positions.getY(vertex);
                centerZ += positions.getZ(vertex);
                centerU += uvs.getX(vertex);
                centerV += uvs.getY(vertex);
                for (let slot = 0; slot < 4; slot++) {
                    const weight = skinWeights.getComponent(vertex, slot);
                    if (weight <= 0) continue;
                    const boneIndex = skinIndices.getComponent(vertex, slot);
                    const boneName = normalizedBoneName(node.skeleton.bones[boneIndex]?.name);
                    boneScores.set(boneName, (boneScores.get(boneName) || 0) + weight);
                }
            });
            centerX /= 3;
            centerY /= 3;
            centerZ /= 3;
            centerU /= 3;
            centerV /= 3;

            const sampledPixel = texturePixel(sourcePixels, width, height, centerU, centerV);
            const sampledPixelIsSkin = texturePixelIsSkin(sampledPixel);

            let headWeight = 0;
            let torsoWeight = 0;
            let hipsWeight = 0;
            let upperLegWeight = 0;
            let metalLimbWeight = 0;
            let totalWeight = 0;
            boneScores.forEach((weight, boneName) => {
                totalWeight += weight;
                if (boneName.includes('head') || boneName.includes('neck')) headWeight += weight;
                if (
                    boneName.includes('spine')
                    || boneName.includes('shoulder')
                    || boneName.includes('upperarm')
                ) {
                    torsoWeight += weight;
                }
                if (boneName.includes('hips')) hipsWeight += weight;
                if (boneName.includes('upleg')) upperLegWeight += weight;
                if (
                    boneName.includes('forearm')
                    || (boneName.includes('leg') && !boneName.includes('upleg'))
                    || boneName.includes('foot')
                    || boneName.includes('toe')
                ) {
                    metalLimbWeight += weight;
                }
            });
            totalWeight = totalWeight || 1;
            const headRatio = headWeight / totalWeight;
            const torsoRatio = torsoWeight / totalWeight;
            const hipsRatio = hipsWeight / totalWeight;
            const upperLegRatio = upperLegWeight / totalWeight;
            const metalLimbRatio = metalLimbWeight / totalWeight;
            if (centerY > 1.08 && headRatio > 0.25) return RIG_REGION.helmet;
            if (
                centerY > 0.72
                && centerY < 1.17
                && Math.abs(centerX) < 0.5
                && torsoRatio > 0.18
                && !sampledPixelIsSkin
            ) {
                return RIG_REGION.chest;
            }
            if (
                centerY > 0.43
                && centerY < 0.84
                && Math.abs(centerX) < 0.38
                && hipsRatio > 0.08
                && hipsRatio >= upperLegRatio * 0.35
                && torsoRatio < 0.18
            ) {
                return RIG_REGION.primary;
            }
            if (
                metalLimbRatio > 0.36
                && (centerY < 0.68 || centerY > 0.72)
                && !sampledPixelIsSkin
            ) {
                return RIG_REGION.metal;
            }
            if (
                hipsRatio > 0.28
                && hipsRatio > upperLegRatio * 1.1
                && centerY < 0.82
            ) {
                return RIG_REGION.primary;
            }
            if (sampledPixelIsSkin) return RIG_REGION.original;
            if (torsoRatio > 0.22) return RIG_REGION.secondary;
            if (texturePixelLooksMetal(sampledPixel)) return RIG_REGION.metal;
            return centerZ > -0.16 ? RIG_REGION.secondary : RIG_REGION.original;
        };

        /**
         * Construye una máscara estable por islas UV completas. Una pieza no
         * cambia de categoría por sus luces o sombras: el faldón permanece
         * tela, las correas cuero y el casco/grebas metal. Las islas donde
         * predomina piel conservan todos sus píxeles originales.
         */
        const buildRigRegionMask = (model, sourcePixels, width, height) => {
            if (
                rigRegionMask
                && rigRegionMaskModel === model
                && rigRegionMask.length === width * height
            ) {
                return rigRegionMask;
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const context = canvas.getContext('2d', { willReadFrequently: true });
            if (!context) return null;
            context.imageSmoothingEnabled = false;
            const regionColors = {
                [RIG_REGION.primary]: '#220000',
                [RIG_REGION.secondary]: '#440000',
                [RIG_REGION.metal]: '#660000',
                [RIG_REGION.helmet]: '#880000',
                [RIG_REGION.chest]: '#AA0000'
            };
            const counts = [0, 0, 0, 0, 0, 0];

            model.traverse((node) => {
                if (!node.isSkinnedMesh || !node.geometry || !node.skeleton) return;
                if (modularParts) {
                    const materials = Array.isArray(node.material)
                        ? node.material
                        : [node.material];
                    const usesLeonidasAtlas = materials.some(
                        (material) => normalizedPartName(material?.name)
                            === 'leonidasoriginal'
                    );
                    if (!usesLeonidasAtlas) return;
                }
                const geometry = node.geometry;
                const positions = geometry.getAttribute('position');
                const uvs = geometry.getAttribute('uv');
                const skinIndices = geometry.getAttribute('skinIndex');
                const skinWeights = geometry.getAttribute('skinWeight');
                if (!positions || !uvs || !skinIndices || !skinWeights) return;
                const index = geometry.index;
                const triangleCount = index ? index.count / 3 : positions.count / 3;
                const vertexAt = (offset) => index ? index.getX(offset) : offset;
                const parents = Int32Array.from(
                    { length: triangleCount },
                    (_, triangle) => triangle
                );
                const findRoot = (triangle) => {
                    let rootTriangle = triangle;
                    while (parents[rootTriangle] !== rootTriangle) {
                        rootTriangle = parents[rootTriangle];
                    }
                    while (parents[triangle] !== triangle) {
                        const parent = parents[triangle];
                        parents[triangle] = rootTriangle;
                        triangle = parent;
                    }
                    return rootTriangle;
                };
                const joinTriangles = (first, second) => {
                    const firstRoot = findRoot(first);
                    const secondRoot = findRoot(second);
                    if (firstRoot !== secondRoot) parents[secondRoot] = firstRoot;
                };
                const uvOwners = new Map();
                const triangleRegions = new Uint8Array(triangleCount);

                for (let triangle = 0; triangle < triangleCount; triangle++) {
                    const vertices = [
                        vertexAt(triangle * 3),
                        vertexAt(triangle * 3 + 1),
                        vertexAt(triangle * 3 + 2)
                    ];
                    triangleRegions[triangle] = rigTriangleRegion(
                        node,
                        vertices,
                        positions,
                        uvs,
                        skinIndices,
                        skinWeights,
                        sourcePixels,
                        width,
                        height
                    );
                    for (let edge = 0; edge < 3; edge++) {
                        const firstVertex = vertices[edge];
                        const secondVertex = vertices[(edge + 1) % 3];
                        const firstUv = [
                            Math.round(uvs.getX(firstVertex) * 100000),
                            Math.round(uvs.getY(firstVertex) * 100000)
                        ];
                        const secondUv = [
                            Math.round(uvs.getX(secondVertex) * 100000),
                            Math.round(uvs.getY(secondVertex) * 100000)
                        ];
                        const ordered = (
                            firstUv[0] < secondUv[0]
                            || (
                                firstUv[0] === secondUv[0]
                                && firstUv[1] <= secondUv[1]
                            )
                        )
                            ? [firstUv, secondUv]
                            : [secondUv, firstUv];
                        const key = ordered
                            .map((uv) => uv.join(':'))
                            .join('|');
                        const owner = uvOwners.get(key);
                        if (owner === undefined) uvOwners.set(key, triangle);
                        else joinTriangles(triangle, owner);
                    }
                }

                const islands = new Map();
                for (let triangle = 0; triangle < triangleCount; triangle++) {
                    const rootTriangle = findRoot(triangle);
                    let island = islands.get(rootTriangle);
                    if (!island) {
                        island = {
                            triangles: [],
                            votes: [0, 0, 0, 0, 0, 0]
                        };
                        islands.set(rootTriangle, island);
                    }
                    island.triangles.push(triangle);
                    island.votes[triangleRegions[triangle]]++;
                }

                islands.forEach((island) => {
                    const originalRatio = island.votes[RIG_REGION.original]
                        / island.triangles.length;
                    let region = RIG_REGION.original;
                    let winningRegion = RIG_REGION.original;
                    let winningVotes = 0;
                    for (
                        let candidate = RIG_REGION.primary;
                        candidate <= RIG_REGION.chest;
                        candidate++
                    ) {
                        if (island.votes[candidate] > winningVotes) {
                            winningVotes = island.votes[candidate];
                            winningRegion = candidate;
                        }
                    }
                    const winningRatio = winningVotes / island.triangles.length;
                    const strongEquipmentIsland = (
                        winningRegion === RIG_REGION.primary
                        || winningRegion === RIG_REGION.metal
                        || winningRegion === RIG_REGION.helmet
                        || winningRegion === RIG_REGION.chest
                    ) && winningRatio >= 0.3;
                    if (originalRatio < 0.25 || strongEquipmentIsland) {
                        region = winningRegion;
                    }
                    counts[region] += island.triangles.length;
                    if (region === RIG_REGION.original) return;
                    context.fillStyle = regionColors[region];
                    island.triangles.forEach((triangle) => {
                        const vertices = [
                            vertexAt(triangle * 3),
                            vertexAt(triangle * 3 + 1),
                            vertexAt(triangle * 3 + 2)
                        ];
                        context.beginPath();
                        vertices.forEach((vertex, corner) => {
                            const x = uvs.getX(vertex) * width;
                            const textureV = modularParts
                                ? uvs.getY(vertex)
                                : (1 - uvs.getY(vertex));
                            const y = textureV * height;
                            if (corner === 0) context.moveTo(x, y);
                            else context.lineTo(x, y);
                        });
                        context.closePath();
                        context.fill();
                    });
                });
            });

            const maskFrame = context.getImageData(0, 0, width, height).data;
            rigRegionMask = new Uint8Array(width * height);
            for (let index = 0; index < rigRegionMask.length; index++) {
                rigRegionMask[index] = Math.round(maskFrame[index * 4] / 34);
            }
            rigRegionMaskModel = model;
            root.dataset.leonidasRigRegions = counts.join(':');
            return rigRegionMask;
        };

        const tintRigTexture = () => {
            const source = rigSourceTexture.image;
            if (!source || !source.width || !source.height || !rigMaterials.size || !activeModel) return;

            const canvas = document.createElement('canvas');
            canvas.width = source.width;
            canvas.height = source.height;
            const context = canvas.getContext('2d', { willReadFrequently: true });
            if (!context) return;
            context.drawImage(source, 0, 0);
            const frame = context.getImageData(0, 0, canvas.width, canvas.height);
            const pixels = frame.data;
            const regionMask = buildRigRegionMask(
                activeModel,
                pixels,
                canvas.width,
                canvas.height
            );
            if (!regionMask) return;
            const primary = hexToRgb(currentAppearance.color_principal);
            const secondary = hexToRgb(currentAppearance.color_secundario);
            const metal = hexToRgb(currentAppearance.color_metal);
            const skinMask = buildTextureSkinMask(
                pixels,
                canvas.width,
                canvas.height
            );
            for (let index = 0; index < pixels.length; index += 4) {
                if (pixels[index + 3] < 12) continue;
                const sourcePixel = [
                    pixels[index],
                    pixels[index + 1],
                    pixels[index + 2],
                    pixels[index + 3]
                ];
                const region = resolveRigPixelRegion(
                    regionMask[index / 4],
                    sourcePixel,
                    skinMask[index / 4] === 1
                );
                if (region === RIG_REGION.original) continue;
                const red = pixels[index] / 255;
                const green = pixels[index + 1] / 255;
                const blue = pixels[index + 2] / 255;
                const luminance = red * 0.299 + green * 0.587 + blue * 0.114;

                let target;
                let amount;
                if (
                    region === RIG_REGION.metal
                    || region === RIG_REGION.helmet
                    || region === RIG_REGION.chest
                ) {
                    target = metal;
                    amount = 0.93;
                } else if (region === RIG_REGION.secondary) {
                    target = secondary;
                    amount = 0.86;
                } else {
                    target = primary;
                    amount = 0.94;
                }

                const shade = Math.max(0.34, Math.min(1.28, 0.42 + luminance * 1.08));
                pixels[index] = Math.round(pixels[index] * (1 - amount) + Math.min(255, target[0] * shade) * amount);
                pixels[index + 1] = Math.round(pixels[index + 1] * (1 - amount) + Math.min(255, target[1] * shade) * amount);
                pixels[index + 2] = Math.round(pixels[index + 2] * (1 - amount) + Math.min(255, target[2] * shade) * amount);
            }
            context.putImageData(frame, 0, 0);

            if (rigAppearanceTexture) rigAppearanceTexture.dispose();
            rigAppearanceTexture = new THREE.CanvasTexture(canvas);
            rigAppearanceTexture.colorSpace = THREE.SRGBColorSpace;
            rigAppearanceTexture.flipY = rigSourceTexture.flipY;
            rigAppearanceTexture.wrapS = rigSourceTexture.wrapS;
            rigAppearanceTexture.wrapT = rigSourceTexture.wrapT;
            rigAppearanceTexture.minFilter = rigSourceTexture.minFilter;
            rigAppearanceTexture.magFilter = rigSourceTexture.magFilter;
            rigAppearanceTexture.needsUpdate = true;

            rigMaterials.forEach((material) => {
                material.map = rigAppearanceTexture;
                if (material.emissiveMap) material.emissiveMap = rigAppearanceTexture;
                material.needsUpdate = true;
            });
            root.dataset.leonidasAppliedAppearance = currentAppearance.id;
        };

        const scheduleRigTexture = () => {
            window.clearTimeout(appearanceTimer);
            appearanceTimer = window.setTimeout(tintRigTexture, 55);
        };

        const applyLeonidasSkin = (model, useRigTexture = false) => {
            const palette = {
                body: { color: 0xffd0ad, metalness: 0, roughness: 0.58 },
                shield: { color: hexToNumber(currentAppearance.color_metal, '#D7E0EA'), metalness: 0.76, roughness: 0.28 },
                sword: { color: 0xaebbc8, metalness: 0.92, roughness: 0.22 },
                pants: { color: hexToNumber(currentAppearance.color_principal, '#0048B7'), metalness: 0.1, roughness: 0.58 },
                crossstrap: { color: hexToNumber(currentAppearance.color_secundario, '#D2D854'), metalness: 0.02, roughness: 0.48 },
                legguard: { color: hexToNumber(currentAppearance.color_metal, '#D7E0EA'), metalness: 0.68, roughness: 0.3, solid: true },
                blanket: { color: hexToNumber(currentAppearance.color_principal, '#0048B7'), metalness: 0, roughness: 0.68 },
                helmet: { color: hexToNumber(currentAppearance.color_metal, '#D7E0EA'), metalness: 0.78, roughness: 0.28, solid: true }
            };
            const fallback = { color: 0xb88655, metalness: 0.42, roughness: 0.42, solid: true };

            model.traverse((node) => {
                if (!node.isMesh) return;
                const materials = Array.isArray(node.material) ? node.material : [node.material];
                materials.forEach((material) => {
                    if (useRigTexture) {
                        if (
                            modularParts
                            && normalizedPartName(material.name)
                                !== 'leonidasoriginal'
                        ) {
                            return;
                        }
                        rigMaterials.add(material);
                        if (modularParts && material.map?.image) {
                            rigSourceTexture = material.map;
                        } else if (!modularParts) {
                            rigSourceTexture = leonidasColorTexture;
                        }
                        material.map = rigAppearanceTexture || rigSourceTexture;
                        if (!modularParts) material.normalMap = leonidasNormalTexture;
                        material.color.setHex(0xffffff);
                        if (material.normalScale) material.normalScale.set(0.32, 0.32);
                        material.roughness = 0.66;
                        material.metalness = 0.02;
                        material.shininess = 18;
                        if (material.specular) material.specular.setHex(0x6f5949);
                        if (material.emissive) {
                            material.emissiveMap = rigAppearanceTexture || leonidasColorTexture;
                            material.emissive.setHex(0xffffff);
                            material.emissiveIntensity = 0.055;
                        }
                        material.needsUpdate = true;
                        return;
                    }
                    const key = String(material.name || '').toLowerCase().replace(/[^a-z]/g, '');
                    const style = palette[key] || fallback;
                    material.color.setHex(style.color);
                    material.metalness = style.metalness;
                    material.roughness = style.roughness;
                    if (style.solid) material.map = null;
                    material.needsUpdate = true;
                });
            });
            if (useRigTexture) scheduleRigTexture();
        };

        const prepareModel = (
            model,
            animations = [],
            modelName = 'spartan-ue4-free',
            options = {}
        ) => {
            model.traverse((node) => {
                if (!node.isMesh) return;
                node.castShadow = true;
                node.receiveShadow = true;
                node.frustumCulled = false;
            });
            activeUsesRigTexture = options.useRigTexture === true;
            modularParts = options.modularParts || null;
            if (modularParts) {
                if (activeUsesRigTexture) {
                    applyLeonidasSkin(model, true);
                    applyModularVisibility();
                } else {
                    applyModularPalette(model);
                }
            } else {
                applyLeonidasSkin(model, activeUsesRigTexture);
            }

            model.updateMatrixWorld(true);
            const box = new THREE.Box3();
            if (modularParts?.body) {
                // El equipamiento de mano no debe reducir al personaje ni
                // desplazar su centro. El encuadre se calcula con la anatomía.
                [
                    modularParts.body,
                    modularParts.helmet,
                    modularParts.chest,
                    modularParts.headUnderlay,
                    modularParts.torsoUnderlay,
                    modularParts.hair
                ].filter(Boolean).forEach((part) => box.expandByObject(part));
            } else {
                box.setFromObject(model);
            }
            const size = box.getSize(new THREE.Vector3());
            const center = box.getCenter(new THREE.Vector3());
            const scale = characterHeight / Math.max(size.y, 0.01);
            model.scale.setScalar(scale);
            model.position.set(-center.x * scale, -box.min.y * scale, -center.z * scale);
            // Aim his torso slightly toward the application instead of the
            // outer edge of the screen, while keeping the pose frontal.
            model.rotation.y = -0.28;
            activeModel = model;
            characterAnchor.add(model);

            pelvis = findBone(model, ['pelvis']);
            spine = findBone(model, ['spine03', 'spine3', 'spine02', 'spine2', 'spine']);
            head = findBone(model, ['head']);
            shieldArm = findBone(model, ['upperarml', 'leftarm']);
            shieldForeArm = findBone(model, ['lowerarml', 'leftforearm']);
            swordArm = findBone(model, ['upperarmr', 'rightarm']);
            swordForeArm = findBone(model, ['lowerarmr', 'rightforearm']);
            leftHand = findBone(model, ['lefthand']);
            rightHand = findBone(model, ['righthand']);
            rightUpLeg = findBone(model, ['rightupleg']);
            rightLeg = findBone(model, ['rightleg']);
            leftUpLeg = findBone(model, ['leftupleg']);
            leftLeg = findBone(model, ['leftleg']);
            rightFingerBones = [];
            leftFingerBones = [];
            model.traverse((node) => {
                if (!node.isBone) return;
                const name = normalizedBoneName(node.name);
                if (name.startsWith('mixamorigrighthand') && name !== 'mixamorigrighthand') rightFingerBones.push(node);
                if (name.startsWith('mixamoriglefthand') && name !== 'mixamoriglefthand') leftFingerBones.push(node);
            });
            [
                pelvis, spine, head, shieldArm, shieldForeArm, swordArm,
                swordForeArm, leftHand, rightHand, rightUpLeg, rightLeg, leftUpLeg, leftLeg
            ].forEach(rememberRotation);

            if (animations.length) {
                mixer = new THREE.AnimationMixer(model);
                mixer.clipAction(animations[0]).reset().play();
                mixer.setTime(6.4);
                if (rightHand) greetingHandRotation = rightHand.quaternion.clone();
                [...rightFingerBones, ...leftFingerBones].forEach((bone) => {
                    openFingerRotations.set(bone, bone.quaternion.clone());
                });
                mixer.setTime(nativeAnimationTime);
            }

            root.dataset.leonidasModel = modelName;
            root.dataset.leonidasBones = String(baseRotations.size);
            root.classList.add('is-3d-ready');
            resize();
            root.dispatchEvent(new CustomEvent('leonidas:model-ready'));
            root.dispatchEvent(new CustomEvent('leonidas:capabilities', {
                detail: {
                    validated: Boolean(modularParts),
                    helmet: Boolean(modularParts?.helmet),
                    chest: Boolean(modularParts?.chest),
                    hair: Boolean(modularParts?.hair),
                    shield: Boolean(modularParts?.shield),
                    spear: Boolean(modularParts?.spear)
                }
            }));
        };

        const finishPreviewRotation = (event) => {
            if (
                event
                && previewPointerId !== null
                && event.pointerId !== previewPointerId
            ) return;
            if (
                previewPointerId !== null
                && canvas.hasPointerCapture?.(previewPointerId)
            ) {
                canvas.releasePointerCapture(previewPointerId);
            }
            previewPointerId = null;
            canvas.classList.remove('is-dragging');
        };

        canvas.setAttribute('aria-label', 'Gira a Leónidas arrastrando');
        canvas.setAttribute('title', 'Arrastra para girar a Leónidas');
        canvas.addEventListener('pointerdown', (event) => {
            if (!root.classList.contains('is-appearance-preview-live')) return;
            previewPointerId = event.pointerId;
            previewPointerX = event.clientX;
            canvas.setPointerCapture?.(event.pointerId);
            canvas.classList.add('is-dragging');
            event.preventDefault();
        });
        canvas.addEventListener('pointermove', (event) => {
            if (
                previewPointerId === null
                || event.pointerId !== previewPointerId
            ) return;
            const deltaX = event.clientX - previewPointerX;
            previewPointerX = event.clientX;
            previewRotationTarget += deltaX * 0.012;
            if (previewRotationTarget > Math.PI) previewRotationTarget -= Math.PI * 2;
            if (previewRotationTarget < -Math.PI) previewRotationTarget += Math.PI * 2;
            event.preventDefault();
        });
        canvas.addEventListener('pointerup', finishPreviewRotation);
        canvas.addEventListener('pointercancel', finishPreviewRotation);
        canvas.addEventListener('lostpointercapture', finishPreviewRotation);
        canvas.addEventListener('dblclick', () => {
            if (root.classList.contains('is-appearance-preview-live')) {
                previewRotationTarget = 0;
            }
        });

        root.addEventListener('leonidas:preview-layout', () => {
            if (!root.classList.contains('is-appearance-preview-live')) {
                finishPreviewRotation();
                previewRotationTarget = 0;
            }
            resize();
        });

        root.addEventListener('leonidas:appearance', (event) => {
            currentAppearance = normalizeAppearance(event.detail || root._leonidasAppearance);
            if (!activeModel) return;
            if (modularParts) {
                if (activeUsesRigTexture) scheduleRigTexture();
                else applyModularPalette(activeModel);
                applyModularVisibility();
            } else if (activeUsesRigTexture) {
                scheduleRigTexture();
            } else {
                applyLeonidasSkin(activeModel, false);
                root.dataset.leonidasAppliedAppearance = currentAppearance.id;
            }
        });

        root.addEventListener('leonidas:texture-ready', () => {
            if (activeUsesRigTexture) scheduleRigTexture();
        });

        const loadFallback = () => {
            loader.load(
                '/assets/models/leonidas/leonidas-spartan.glb',
                (gltf) => prepareModel(
                    gltf.scene,
                    gltf.animations,
                    'spartan-static',
                    { useRigTexture: false }
                ),
                undefined,
                () => root.classList.add('is-3d-fallback')
            );
        };

        const loadCurrentRig = () => {
            fbxLoader.load(
                '/assets/models/leonidas/leonidas-spartan-rigged.fbx',
                (model) => prepareModel(
                    model,
                    model.animations || [],
                    'spartan-rigged-fbx',
                    { useRigTexture: true }
                ),
                undefined,
                () => loader.load(
                    '/assets/models/leonidas/leonidas-spartan-free.glb',
                    (gltf) => prepareModel(
                        gltf.scene,
                        gltf.animations,
                        'spartan-fallback-glb',
                        { useRigTexture: false }
                    ),
                    undefined,
                    loadFallback
                )
            );
        };

        const loadConfiguredModel = () => {
            window.fetch('/assets/models/leonidas/leonidas-modular-manifest.json', {
                credentials: 'same-origin',
                cache: 'no-store'
            }).then((response) => {
                if (!response.ok) throw new Error('modular-manifest-unavailable');
                return response.json();
            }).then((manifest) => {
                if (manifest?.enabled !== true || !manifest?.asset) {
                    loadCurrentRig();
                    return;
                }
                loader.load(
                    `${manifest.asset}?v=${encodeURIComponent(manifest.version || 1)}`,
                    (gltf) => {
                        const parts = resolveModularParts(gltf.scene, manifest);
                        if (!parts) {
                            root.dataset.leonidasModularError = 'invalid-parts';
                            loadCurrentRig();
                            return;
                        }
                        prepareModel(
                            gltf.scene,
                            gltf.animations,
                            'spartan-modular-v2',
                            {
                                // El GLB modular ya separa piel, tela, cuero y
                                // metal por materiales. No debe volver a
                                // colorearse mediante inferencias sobre el atlas.
                                useRigTexture: false,
                                modularParts: parts
                            }
                        );
                    },
                    undefined,
                    () => {
                        root.dataset.leonidasModularError = 'load-failed';
                        loadCurrentRig();
                    }
                );
            }).catch(loadCurrentRig);
        };

        loadConfiguredModel();

        const observer = new ResizeObserver(resize);
        observer.observe(canvas);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) clock.getDelta();
        });

        resize();
        animate();
    }
}
