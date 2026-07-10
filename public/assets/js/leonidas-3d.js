import * as THREE from '../vendor/three/three.module.min.js';
import { GLTFLoader } from '../vendor/three/addons/loaders/GLTFLoader.js';
import { FBXLoader } from '../vendor/three/addons/loaders/FBXLoader.js';

const root = document.getElementById('leonidasAssistant');
const canvas = root?.querySelector('[data-leonidas-canvas]');

if (root && canvas) {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(25, 1, 0.1, 50);
    camera.position.set(0, 0.04, 4.8);
    camera.lookAt(0, 0.04, 0);

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
        let shieldForeArm = null;
        let shieldHand = null;
        let shieldAnchor = null;
        let spine = null;
        let head = null;
        let greetingWeight = 0;
        let shieldLoaded = false;
        const baseRotations = new Map();
        const poseEuler = new THREE.Euler();
        const poseQuaternion = new THREE.Quaternion();
        const shieldHandPosition = new THREE.Vector3();
        const shieldForeArmPosition = new THREE.Vector3();

        const resize = () => {
            const width = Math.max(1, canvas.clientWidth);
            const height = Math.max(1, canvas.clientHeight);
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
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

        const applyGreetingPose = (elapsed, delta) => {
            const target = root.classList.contains('is-greeting') ? 1 : 0;
            greetingWeight = THREE.MathUtils.damp(greetingWeight, target, 7, delta);
            const breath = Math.sin(elapsed * 1.8) * 0.018;
            const wave = Math.sin(elapsed * 8.5) * 0.16;

            applyBoneRotation(spine, breath, 0, 0);
            applyBoneRotation(head, 0, Math.sin(elapsed * 0.75) * 0.035, 0);
            applyBoneRotation(
                waveArm,
                0,
                0,
                0.58 * greetingWeight
            );
            applyBoneRotation(
                waveForeArm,
                0,
                0,
                2.45 * greetingWeight
            );
            applyBoneRotation(waveHand, wave * 0.45 * greetingWeight, 0, 0);
        };

        const updateShieldPose = () => {
            if (!shieldAnchor || !shieldHand || !shieldForeArm) return;

            shieldHand.getWorldPosition(shieldHandPosition);
            shieldForeArm.getWorldPosition(shieldForeArmPosition);
            shieldAnchor.position.lerpVectors(shieldForeArmPosition, shieldHandPosition, 0.62);
            shieldAnchor.position.x += 0.12;
            shieldAnchor.position.y -= 0.08;
            shieldAnchor.position.z += 0.26;
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
            const scale = 1.98 / Math.max(size.y, 0.01);
            model.scale.setScalar(scale);
            model.position.set(-center.x * scale, -box.min.y * scale - 0.99, -center.z * scale);
            model.rotation.y = 0;
            scene.add(model);

            if (animations.length) {
                mixer = new THREE.AnimationMixer(model);
                const idle = animations.find((clip) => /idle/i.test(clip.name)) || animations[0];
                mixer.clipAction(idle).play();
            }

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
            shieldForeArm = visualRightLimb.foreArm;
            shieldHand = visualRightLimb.hand;
            spine = findBone(model, ['spine2', 'spine03', 'chest', 'spine']);
            head = findBone(model, ['head']);
            [waveArm, waveForeArm, waveHand, spine, head].forEach(rememberRotation);
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
