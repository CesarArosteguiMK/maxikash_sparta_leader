import fs from 'node:fs';

globalThis.document = {
    createElementNS() {
        return {
            style: {},
            addEventListener() {},
            removeEventListener() {},
            set src(value) {},
        };
    },
};

const { FBXLoader } = await import('./public/assets/vendor/three/addons/loaders/FBXLoader.js');

const buffer = fs.readFileSync('./public/assets/models/leonidas/leonidas-spartan-rigged.fbx');
const model = new FBXLoader().parse(
    buffer.buffer.slice(buffer.byteOffset, buffer.byteOffset + buffer.byteLength),
    './public/assets/models/leonidas/'
);
const rows = [];
model.traverse((node) => {
    if (!node.isMesh) return;
    const materials = Array.isArray(node.material) ? node.material : [node.material];
    rows.push({
        mesh: node.name,
        materials: materials.map((material) => material?.name || ''),
    });
});
process.stdout.write(JSON.stringify(rows, null, 2));
