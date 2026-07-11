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
    const poseEuler = new THREE.Euler();

    scene.add(characterAnchor);
    camera.position.set(0, 0, 4.8);
    camera.lookAt(0, 0, 0);
    leonidasColorTexture.colorSpace = THREE.SRGBColorSpace;
    leonidasColorTexture.flipY = false;
    leonidasNormalTexture.flipY = false;

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

        const hemisphere = new THREE.HemisphereLight(0xf6f8ff, 0x654533, 2.35);
        const keyLight = new THREE.DirectionalLight(0xffffff, 3.05);
        const rimLight = new THREE.DirectionalLight(0x88a9e8, 1.45);
        keyLight.position.set(3.5, 4.5, 5);
        rimLight.position.set(-3.5, 2.5, -2);
        scene.add(hemisphere, keyLight, rimLight);

        const characterHeight = 1.98;
        let characterScale = 1;
        let anchorBaseY = 0;
        let pelvis = null;
        let spine = null;
        let head = null;
        let shieldArm = null;
        let shieldForeArm = null;
        let swordArm = null;
        let swordForeArm = null;
        let mixer = null;
        let animationElapsed = 0;
        let greetingWeight = 0;
        let victoryWeight = 0;

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
            anchorBaseY = characterAnchor.position.y;
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
            greetingWeight = THREE.MathUtils.damp(greetingWeight, greetingTarget, 7, delta);
            victoryWeight = THREE.MathUtils.damp(victoryWeight, victoryTarget, 7, delta);

            const breath = Math.sin(elapsed * 1.65);
            const interaction = Math.max(greetingWeight, victoryWeight);
            const acknowledgement = Math.sin(elapsed * 5.3) * interaction;

            // Idle is deliberately vertical only. The old side-to-side movement made
            // the character look unstable and exaggerated the source T-pose.
            characterAnchor.position.y = anchorBaseY
                + (breath * 0.004 * characterScale)
                + (interaction * 0.006 * characterScale);
            if (!mixer) {
                resetBone(pelvis);
                resetBone(spine);
                resetBone(head);
                resetBone(shieldArm);
                resetBone(shieldForeArm);
                resetBone(swordArm);
                resetBone(swordForeArm);
                offsetBone(pelvis, breath * 0.006 + acknowledgement * 0.006, 0, 0);
                offsetBone(spine, breath * 0.014 + acknowledgement * 0.018, 0, 0);
            }

        };

        const animate = () => {
            const delta = Math.min(clock.getDelta(), 0.05);
            if (mixer) {
                // The source FBX ends in a rig calibration pose (arms extended).
                // Keep the real motion inside its natural idle segment instead.
                animationElapsed += delta;
                mixer.setTime(1.15 + Math.sin(animationElapsed * 0.7) * 0.32);
            }
            applyPresence(clock.elapsedTime, delta);
            renderer.render(scene, camera);
            window.requestAnimationFrame(animate);
        };

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
                        material.roughness = 0.54;
                        material.metalness = 0.08;
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
            // This asset's authored forward axis is slightly off camera. This yaw
            // places its face toward the viewer without separating the rig pieces.
            model.rotation.y = -0.45;
            characterAnchor.add(model);

            pelvis = findBone(model, ['pelvis']);
            spine = findBone(model, ['spine03', 'spine3', 'spine02', 'spine2', 'spine']);
            head = findBone(model, ['head']);
            shieldArm = findBone(model, ['upperarml', 'leftarm']);
            shieldForeArm = findBone(model, ['lowerarml', 'leftforearm']);
            swordArm = findBone(model, ['upperarmr', 'rightarm']);
            swordForeArm = findBone(model, ['lowerarmr', 'rightforearm']);
            [pelvis, spine, head, shieldArm, shieldForeArm, swordArm, swordForeArm].forEach(rememberRotation);

            if (animations.length) {
                mixer = new THREE.AnimationMixer(model);
                mixer.clipAction(animations[0]).reset().play();
                mixer.setTime(1.15);
            }

            root.dataset.leonidasModel = modelName;
            root.dataset.leonidasBones = String(baseRotations.size);
            root.classList.add('is-3d-ready');
            resize();
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
