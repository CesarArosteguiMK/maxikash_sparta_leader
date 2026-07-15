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
    const leonidasColorTexture = textureLoader.load('/assets/models/leonidas/leonidas-spartan-color.webp');
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

        const normalizedBoneName = (name) => String(name || '')
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '');

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
            } else {
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
            const desiredHeight = width <= 575 ? 210 : 300;
            const edgeMargin = (width <= 575 ? 12 : 22) * visibleHeight / height;

            characterScale = desiredHeight * visibleHeight / (height * characterHeight);
            characterAnchor.scale.setScalar(characterScale);
            characterAnchor.position.set(
                visibleWidth / 2 - characterScale * 0.96 - edgeMargin,
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
                activeModel.rotation.y = THREE.MathUtils.damp(activeModel.rotation.y, facingTarget, 5.2, delta);
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

        const applyLeonidasSkin = (model, useRigTexture = false) => {
            const palette = {
                body: { color: 0xffd0ad, metalness: 0, roughness: 0.58 },
                shield: { color: 0xd2a15e, metalness: 0.76, roughness: 0.28 },
                sword: { color: 0xaebbc8, metalness: 0.92, roughness: 0.22 },
                pants: { color: 0x48566b, metalness: 0.1, roughness: 0.58 },
                crossstrap: { color: 0x925f39, metalness: 0.02, roughness: 0.48 },
                legguard: { color: 0xd8b37b, metalness: 0.68, roughness: 0.3, solid: true },
                blanket: { color: 0xa91e2c, metalness: 0, roughness: 0.68 },
                helmet: { color: 0xd8b37b, metalness: 0.78, roughness: 0.28, solid: true }
            };
            const fallback = { color: 0xb88655, metalness: 0.42, roughness: 0.42, solid: true };

            model.traverse((node) => {
                if (!node.isMesh) return;
                const materials = Array.isArray(node.material) ? node.material : [node.material];
                materials.forEach((material) => {
                    if (useRigTexture) {
                        material.map = leonidasColorTexture;
                        material.normalMap = leonidasNormalTexture;
                        material.color.setHex(0xffffff);
                        if (material.normalScale) material.normalScale.set(0.32, 0.32);
                        material.roughness = 0.66;
                        material.metalness = 0.02;
                        material.shininess = 18;
                        if (material.specular) material.specular.setHex(0x6f5949);
                        if (material.emissive) {
                            material.emissiveMap = leonidasColorTexture;
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
        };

        const prepareModel = (model, animations = [], modelName = 'spartan-ue4-free') => {
            model.traverse((node) => {
                if (!node.isMesh) return;
                node.castShadow = true;
                node.receiveShadow = true;
                node.frustumCulled = false;
            });
            applyLeonidasSkin(model, animations.length > 0);

            model.updateMatrixWorld(true);
            const box = new THREE.Box3().setFromObject(model);
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
        };

        const loadFallback = () => {
            loader.load(
                '/assets/models/leonidas/leonidas-spartan.glb',
                (gltf) => prepareModel(gltf.scene, gltf.animations, 'spartan-static'),
                undefined,
                () => root.classList.add('is-3d-fallback')
            );
        };

        fbxLoader.load(
            '/assets/models/leonidas/leonidas-spartan-rigged.fbx',
            (model) => prepareModel(model, model.animations || [], 'spartan-rigged-fbx'),
            undefined,
            () => loader.load(
                '/assets/models/leonidas/leonidas-spartan-free.glb',
                (gltf) => prepareModel(gltf.scene, gltf.animations, 'spartan-ue4-free'),
                undefined,
                loadFallback
            )
        );

        const observer = new ResizeObserver(resize);
        observer.observe(canvas);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) clock.getDelta();
        });

        resize();
        animate();
    }
}
