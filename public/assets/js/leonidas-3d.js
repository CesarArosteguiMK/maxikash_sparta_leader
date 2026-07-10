import * as THREE from '../vendor/three/three.module.min.js';
import { GLTFLoader } from '../vendor/three/addons/loaders/GLTFLoader.js';
import { FBXLoader } from '../vendor/three/addons/loaders/FBXLoader.js';

const root = document.getElementById('leonidasAssistant');
const canvas = root?.querySelector('[data-leonidas-canvas]');
const interactionTarget = root?.querySelector('[data-leonidas-toggle]');

if (root && canvas) {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(25, 1, 0.1, 50);
    camera.position.set(0, 0, 4.8);
    camera.lookAt(0, 0, 0);
    const characterAnchor = new THREE.Group();
    scene.add(characterAnchor);

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
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        const hemisphere = new THREE.HemisphereLight(0xf5f8ff, 0x5d3c2f, 2.2);
        const keyLight = new THREE.DirectionalLight(0xffffff, 3.2);
        const rimLight = new THREE.DirectionalLight(0x7fa7ff, 1.8);
        keyLight.position.set(3, 4, 5);
        rimLight.position.set(-4, 2, -2);
        scene.add(hemisphere, keyLight, rimLight);

        const clock = new THREE.Clock();
        const gltfLoader = new GLTFLoader();
        const fbxLoader = new FBXLoader();
        const textureLoader = new THREE.TextureLoader();
        let mixer = null;
        let waveArm = null;
        let waveForeArm = null;
        let waveHand = null;
        let wavePalm = null;
        let shieldArm = null;
        let shieldForeArm = null;
        let shieldHand = null;
        let shieldAnchor = null;
        let spine = null;
        let head = null;
        let greetingWeight = 0;
        let victoryWeight = 0;
        let shieldLoaded = false;
        let characterScale = 1;
        const characterHeight = 1.98;
        const baseRotations = new Map();
        const poseEuler = new THREE.Euler();
        const poseQuaternion = new THREE.Quaternion();
        const parentWorldQuaternion = new THREE.Quaternion();
        const inverseParentQuaternion = new THREE.Quaternion();
        const baseBoneDirection = new THREE.Vector3();
        const desiredBoneDirection = new THREE.Vector3();
        const boneWorldPosition = new THREE.Vector3();
        const boneAimDelta = new THREE.Quaternion();
        const boneTargetQuaternion = new THREE.Quaternion();
        const shieldHandPosition = new THREE.Vector3();
        const shieldForeArmPosition = new THREE.Vector3();
        const palmFingerDirection = new THREE.Vector3();
        const palmSpreadDirection = new THREE.Vector3();
        const palmBaseNormal = new THREE.Vector3();
        const palmDesiredNormal = new THREE.Vector3();
        const palmHandPosition = new THREE.Vector3();
        const palmCameraPosition = new THREE.Vector3();
        const palmRotation = new THREE.Quaternion();
        const headForward = new THREE.Vector3();
        const headDesiredForward = new THREE.Vector3();
        const headPosition = new THREE.Vector3();
        const headCameraPosition = new THREE.Vector3();
        const headRotation = new THREE.Quaternion();

        const layoutCharacter = (width, height) => {
            const visibleHeight = 2 * camera.position.z * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
            const visibleWidth = visibleHeight * camera.aspect;
            const desiredHeight = width <= 575 ? 208 : 300;
            const edgeMargin = (width <= 575 ? 12 : 22) * visibleHeight / height;
            characterScale = desiredHeight * visibleHeight / (height * characterHeight);

            characterAnchor.scale.setScalar(characterScale);
            characterAnchor.position.set(
                visibleWidth / 2 - characterScale * 0.98 - edgeMargin,
                -visibleHeight / 2 + edgeMargin,
                0
            );
            shieldAnchor?.scale.setScalar(characterScale);
        };

        const resize = () => {
            const width = Math.max(1, canvas.clientWidth);
            const height = Math.max(1, canvas.clientHeight);
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            layoutCharacter(width, height);
        };

        const rememberRotation = (bone) => {
            if (bone && !baseRotations.has(bone)) {
                baseRotations.set(bone, bone.quaternion.clone());
            }
        };

        const applyBoneRotation = (bone, x, y, z) => {
            const base = bone ? baseRotations.get(bone) : null;
            if (!bone || !base) return;
            poseEuler.set(x, y, z);
            poseQuaternion.setFromEuler(poseEuler);
            bone.quaternion.copy(base).multiply(poseQuaternion);
        };

        const aimBoneAt = (bone, child, targetWorld, blend = 1) => {
            const base = bone ? baseRotations.get(bone) : null;
            if (!bone || !child || !base || !bone.parent) return;

            bone.parent.getWorldQuaternion(parentWorldQuaternion);
            inverseParentQuaternion.copy(parentWorldQuaternion).invert();
            bone.getWorldPosition(boneWorldPosition);
            desiredBoneDirection.copy(targetWorld).sub(boneWorldPosition).normalize().applyQuaternion(inverseParentQuaternion);
            baseBoneDirection.copy(child.position).normalize().applyQuaternion(base);
            boneAimDelta.setFromUnitVectors(baseBoneDirection, desiredBoneDirection);
            boneTargetQuaternion.copy(boneAimDelta).multiply(base);
            bone.quaternion.slerpQuaternions(base, boneTargetQuaternion, blend);
            characterAnchor.updateMatrixWorld(true);
        };

        const poseLimb = (arm, foreArm, hand, elbowOffset, handOffset, weight, wristWave = 0) => {
            if (!arm || !foreArm || !hand || weight <= 0.001) return;

            const shoulderPosition = arm.getWorldPosition(new THREE.Vector3());
            const restingElbow = foreArm.getWorldPosition(new THREE.Vector3());
            const desiredElbow = shoulderPosition.clone().addScaledVector(elbowOffset, characterScale);
            const elbowTarget = restingElbow.clone().lerp(desiredElbow, weight);
            aimBoneAt(arm, foreArm, elbowTarget);

            applyBoneRotation(foreArm, 0, 0, 0);
            characterAnchor.updateMatrixWorld(true);
            const restingHand = hand.getWorldPosition(new THREE.Vector3());
            const desiredHand = shoulderPosition.clone().addScaledVector(handOffset, characterScale);
            const handTarget = restingHand.clone().lerp(desiredHand, weight);
            aimBoneAt(foreArm, hand, handTarget);
            applyBoneRotation(hand, wristWave * weight, 0, 0);
        };

        const orientWavePalmTowardsCamera = (weight) => {
            const base = waveHand ? baseRotations.get(waveHand) : null;
            const { index, middle, pinky } = wavePalm || {};
            if (!waveHand || !base || !index || !middle || !pinky || !waveHand.parent) return;

            // Build the palm normal from the actual finger roots, then orient it toward the viewer.
            palmFingerDirection.copy(middle.position).normalize();
            palmSpreadDirection.copy(index.position).sub(pinky.position).normalize();
            palmBaseNormal.copy(palmFingerDirection).cross(palmSpreadDirection).normalize().applyQuaternion(base);
            waveHand.getWorldPosition(palmHandPosition);
            camera.getWorldPosition(palmCameraPosition);
            waveHand.parent.getWorldQuaternion(parentWorldQuaternion);
            inverseParentQuaternion.copy(parentWorldQuaternion).invert();
            palmDesiredNormal.copy(palmCameraPosition).sub(palmHandPosition).normalize().multiplyScalar(-1).applyQuaternion(inverseParentQuaternion);
            palmRotation.setFromUnitVectors(palmBaseNormal, palmDesiredNormal);
            poseQuaternion.copy(palmRotation).multiply(base);
            waveHand.quaternion.slerpQuaternions(base, poseQuaternion, weight);
        };

        const faceHeadTowardCamera = () => {
            const base = head ? baseRotations.get(head) : null;
            if (!head || !base || !head.parent) return;

            head.getWorldPosition(headPosition);
            camera.getWorldPosition(headCameraPosition);
            headCameraPosition.y = headPosition.y;
            head.parent.getWorldQuaternion(parentWorldQuaternion);
            inverseParentQuaternion.copy(parentWorldQuaternion).invert();
            headDesiredForward.copy(headCameraPosition).sub(headPosition).normalize().applyQuaternion(inverseParentQuaternion);
            headForward.set(0, 0, 1).applyQuaternion(base);
            headRotation.setFromUnitVectors(headForward, headDesiredForward);
            poseQuaternion.copy(headRotation).multiply(base);
            head.quaternion.copy(poseQuaternion);
        };

        const applyGreetingPose = (elapsed, delta) => {
            const victoryTarget = root.classList.contains('is-victory') ? 1 : 0;
            const greetingTarget = !victoryTarget && root.classList.contains('is-greeting') ? 1 : 0;
            greetingWeight = THREE.MathUtils.damp(greetingWeight, greetingTarget, 8, delta);
            victoryWeight = THREE.MathUtils.damp(victoryWeight, victoryTarget, 8, delta);
            const breath = Math.sin(elapsed * 1.8) * 0.018;
            const wave = Math.sin(elapsed * 8.5) * 0.16;

            applyBoneRotation(spine, breath, 0, 0);
            faceHeadTowardCamera();
            applyBoneRotation(waveArm, 0, 0, 0);
            applyBoneRotation(waveForeArm, 0, 0, 0);
            applyBoneRotation(waveHand, 0, 0, 0);
            applyBoneRotation(shieldArm, 0, 0, 0);
            applyBoneRotation(shieldForeArm, 0, 0, 0);
            applyBoneRotation(shieldHand, 0, 0, 0);

            if (victoryWeight > 0.001) {
                poseLimb(
                    waveArm,
                    waveForeArm,
                    waveHand,
                    new THREE.Vector3(-0.3, 0.2, 0.16),
                    new THREE.Vector3(-0.2, 0.64, 0.24),
                    victoryWeight
                );
                poseLimb(
                    shieldArm,
                    shieldForeArm,
                    shieldHand,
                    new THREE.Vector3(0.3, 0.2, 0.16),
                    new THREE.Vector3(0.2, 0.64, 0.24),
                    victoryWeight
                );
            } else if (greetingWeight > 0.001) {
                poseLimb(
                    waveArm,
                    waveForeArm,
                    waveHand,
                    new THREE.Vector3(-0.36, 0.2, 0.18),
                    new THREE.Vector3(-0.34 + wave * 0.22, 0.62, 0.24),
                    greetingWeight
                );
                orientWavePalmTowardsCamera(greetingWeight);
            }
        };

        const updateShieldPose = () => {
            if (!shieldAnchor || !shieldHand || !shieldForeArm) return;

            shieldHand.getWorldPosition(shieldHandPosition);
            shieldForeArm.getWorldPosition(shieldForeArmPosition);
            shieldAnchor.position.lerpVectors(shieldForeArmPosition, shieldHandPosition, 0.62);
            shieldAnchor.position.x += 0.12 * characterScale;
            shieldAnchor.position.y -= 0.08 * characterScale;
            shieldAnchor.position.z += 0.26 * characterScale;
        };

        const animate = () => {
            const delta = Math.min(clock.getDelta(), 0.05);
            const elapsed = clock.elapsedTime;
            mixer?.update(delta);
            applyGreetingPose(elapsed, delta);
            updateShieldPose();
            renderer.render(scene, camera);
            window.requestAnimationFrame(animate);
        };

        const normalizedBoneName = (name) => String(name || '').toLowerCase().replace(/[^a-z0-9]/g, '');

        const findBone = (model, candidates) => {
            let match = null;
            model.traverse((node) => {
                if (match || !node.isBone) return;
                const name = normalizedBoneName(node.name);
                if (candidates.some((candidate) => name.endsWith(candidate) || name.includes(candidate))) {
                    match = node;
                }
            });
            return match;
        };

        const loadShield = () => {
            if (shieldLoaded) return;
            shieldLoaded = true;
            gltfLoader.load(
                '/assets/models/leonidas/leonidas-shield.glb',
                (gltf) => {
                    const shield = gltf.scene;
                    shield.updateMatrixWorld(true);
                    const box = new THREE.Box3().setFromObject(shield);
                    const size = box.getSize(new THREE.Vector3());
                    const center = box.getCenter(new THREE.Vector3());
                    const scale = 0.58 / Math.max(size.x, size.y, size.z, 0.01);
                    shield.scale.setScalar(scale);
                    shield.position.set(-center.x * scale, -center.y * scale, -center.z * scale);
                    shield.rotation.y = 0;
                    shield.traverse((node) => {
                        if (node.isMesh) {
                            node.castShadow = true;
                            node.frustumCulled = false;
                        }
                    });
                    shieldAnchor = new THREE.Group();
                    shieldAnchor.add(shield);
                    scene.add(shieldAnchor);
                    shieldAnchor.scale.setScalar(characterScale);
                    updateShieldPose();
                }
            );
        };

        const prepareModel = (model, animations = []) => {
            model.traverse((node) => {
                if (node.isMesh) {
                    node.castShadow = true;
                    node.frustumCulled = false;
                }
            });

            model.updateMatrixWorld(true);
            const box = new THREE.Box3().setFromObject(model);
            const size = box.getSize(new THREE.Vector3());
            const center = box.getCenter(new THREE.Vector3());
            const scale = characterHeight / Math.max(size.y, 0.01);
            model.scale.setScalar(scale);
            model.position.set(-center.x * scale, -box.min.y * scale, -center.z * scale);
            model.rotation.y = -0.45;
            characterAnchor.add(model);

            mixer = null;

            const rightLimb = {
                arm: findBone(model, ['rightarm', 'upperarmr', 'rupperarm']),
                foreArm: findBone(model, ['rightforearm', 'lowerarmr', 'rforearm']),
                hand: findBone(model, ['righthand', 'handr', 'rhand'])
            };
            const leftLimb = {
                arm: findBone(model, ['leftarm', 'upperarml', 'lupperarm']),
                foreArm: findBone(model, ['leftforearm', 'lowerarml', 'lforearm']),
                hand: findBone(model, ['lefthand', 'handl', 'lhand'])
            };
            model.updateMatrixWorld(true);
            const rightHandX = rightLimb.hand?.getWorldPosition(new THREE.Vector3()).x ?? -1;
            const leftHandX = leftLimb.hand?.getWorldPosition(new THREE.Vector3()).x ?? 1;
            const visualLeftLimb = rightHandX <= leftHandX ? rightLimb : leftLimb;
            const visualRightLimb = rightHandX <= leftHandX ? leftLimb : rightLimb;

            waveArm = visualLeftLimb.arm;
            waveForeArm = visualLeftLimb.foreArm;
            waveHand = visualLeftLimb.hand;
            shieldArm = visualRightLimb.arm;
            shieldForeArm = visualRightLimb.foreArm;
            shieldHand = visualRightLimb.hand;
            spine = findBone(model, ['spine2', 'spine03', 'chest', 'spine']);
            head = findBone(model, ['head']);
            wavePalm = {};
            waveHand?.traverse((node) => {
                if (!node.isBone) return;
                const name = normalizedBoneName(node.name);
                if (name.endsWith('handindex1')) wavePalm.index = node;
                if (name.endsWith('handmiddle1')) wavePalm.middle = node;
                if (name.endsWith('handpinky1')) wavePalm.pinky = node;
            });
            [waveArm, waveForeArm, waveHand, shieldArm, shieldForeArm, shieldHand, spine, head].forEach(rememberRotation);
            loadShield();

            root.dataset.leonidasModel = 'spartan';
            root.dataset.leonidasBones = String(baseRotations.size);
            root.classList.add('is-3d-ready');
            resize();
        };

        const loadStaticSpartan = () => {
            gltfLoader.load(
                '/assets/models/leonidas/leonidas-spartan.glb',
                (gltf) => prepareModel(gltf.scene, gltf.animations),
                undefined,
                () => root.classList.add('is-3d-fallback')
            );
        };

        const loadTexture = (url) => new Promise((resolve, reject) => {
            textureLoader.load(url, resolve, undefined, reject);
        });

        const applySpartanMaterial = async (model) => {
            const [colorMap, normalMap] = await Promise.all([
                loadTexture('/assets/models/leonidas/leonidas-spartan-color.webp'),
                loadTexture('/assets/models/leonidas/leonidas-spartan-normal.webp')
            ]);
            colorMap.colorSpace = THREE.SRGBColorSpace;
            colorMap.flipY = true;
            normalMap.flipY = true;
            const material = new THREE.MeshStandardMaterial({
                map: colorMap,
                normalMap,
                metalness: 0,
                roughness: 1,
                side: THREE.DoubleSide
            });
            model.traverse((node) => {
                if (node.isMesh) node.material = material;
            });
        };

        fbxLoader.load(
            '/assets/models/leonidas/leonidas-spartan-rigged.fbx',
            async (model) => {
                try {
                    await applySpartanMaterial(model);
                } catch (error) {
                    // The rig remains usable even if a texture cannot be decoded.
                }
                prepareModel(model, model.animations || []);
            },
            undefined,
            loadStaticSpartan
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
