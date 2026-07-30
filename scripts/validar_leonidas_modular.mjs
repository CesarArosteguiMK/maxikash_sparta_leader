import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const projectRoot = resolve(import.meta.dirname, '..');
const manifestPath = resolve(
    projectRoot,
    'public/assets/models/leonidas/leonidas-modular-manifest.json'
);
const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
const configuredAsset = String(manifest.asset || '').replace(/^\/+/, '');
const assetPath = resolve(projectRoot, 'public', configuredAsset.replace(/^assets[\\/]/, 'assets/'));

function fail(message) {
    console.error(`Leonidas modular: ERROR - ${message}`);
    process.exitCode = 1;
}

function normalized(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]/g, '');
}

function readGlbJson(buffer) {
    if (buffer.length < 20 || buffer.readUInt32LE(0) !== 0x46546c67) {
        throw new Error('el archivo no tiene una cabecera GLB valida');
    }
    if (buffer.readUInt32LE(4) !== 2) {
        throw new Error('solo se admite GLB version 2');
    }
    const declaredLength = buffer.readUInt32LE(8);
    if (declaredLength !== buffer.length) {
        throw new Error('la longitud declarada del GLB no coincide con el archivo');
    }
    const jsonLength = buffer.readUInt32LE(12);
    const jsonType = buffer.readUInt32LE(16);
    if (jsonType !== 0x4e4f534a || 20 + jsonLength > buffer.length) {
        throw new Error('el primer bloque del GLB no es JSON valido');
    }
    return JSON.parse(buffer.subarray(20, 20 + jsonLength).toString('utf8').trim());
}

if (!configuredAsset) {
    fail('el manifiesto no define asset');
} else {
    try {
        const document = readGlbJson(await readFile(assetPath));
        const found = {};
        const definitions = manifest.parts || {};
        (document.nodes || []).forEach((node, index) => {
            const declaredRole = normalized(node.extras?.leonidasPart);
            const nodeName = normalized(node.name);
            Object.entries(definitions).forEach(([role, aliases]) => {
                if (found[role]) return;
                const names = (Array.isArray(aliases) ? aliases : []).map(normalized);
                if (declaredRole === normalized(role) || names.includes(nodeName)) {
                    found[role] = { index, node };
                }
            });
        });

        const required = Array.isArray(manifest.requiredParts)
            ? manifest.requiredParts
            : ['body', 'helmet', 'chest'];
        const paletteRoles = new Set(
            (document.materials || [])
                .map((material) => normalized(material.extras?.leonidasPalette))
                .filter(Boolean)
        );
        const materialNames = new Set(
            (document.materials || []).map((material) => normalized(material.name))
        );
        const missing = required.filter((role) => !found[role]);
        if (missing.length) {
            fail(`faltan piezas independientes: ${missing.join(', ')}`);
        } else if (new Set(required.map((role) => found[role].index)).size !== required.length) {
            fail('body, helmet y chest deben ser nodos diferentes');
        } else if (found.body.node.mesh === undefined && !found.body.node.children?.length) {
            const available = (document.nodes || []).map((node, index) => ({
                index,
                name: node.name || '',
                mesh: node.mesh ?? null,
                part: node.extras?.leonidasPart || null,
            }));
            fail(
                'body no contiene una malla ni hijos con anatomia. Nodos: '
                + JSON.stringify(available)
            );
        } else if (
            !['primary', 'secondary', 'metal']
                .every((role) => paletteRoles.has(role))
        ) {
            fail('faltan materiales semanticos primary, secondary o metal');
        } else if (
            !String(found.body.node.extras?.leonidasSemanticFaces || '')
                .includes('original=')
        ) {
            fail('body no declara la separacion semantica de piel y vestuario');
        } else if (
            Number(found.helmet.node.extras?.leonidasHelmetVisorFaces || 0) <= 0
        ) {
            fail('helmet no contiene el fondo oscuro de la cavidad');
        } else if (
            Number(found.helmet.node.extras?.leonidasHelmetOriginalFaces || 0) < 1000
        ) {
            fail('helmet no conserva la geometria esculpida de alta densidad');
        } else if (
            Number(found.helmet.node.extras?.leonidasHelmetOpeningCutters || 0) > 0
        ) {
            fail('helmet contiene cortadores destructivos que pueden perforar la nuca');
        } else if (
            Number(found.helmet.node.extras?.leonidasHelmetScale || 0) < 0.89
            || Number(found.helmet.node.extras?.leonidasHelmetScale || 0) > 0.93
            || Number(found.helmet.node.extras?.leonidasHelmetLift || 0) <= 0
        ) {
            fail('helmet no declara la escala y elevacion ergonomicas');
        } else if (!materialNames.has('leonidasvisormaterial')) {
            fail('falta el material mate del fondo interior');
        } else if (
            !materialNames.has('leonidashelmetpatina')
            || !materialNames.has('leonidashelmethighlight')
        ) {
            fail('helmet no contiene contraste de patina y metal resaltado');
        } else if (
            Number(found.helmet.node.extras?.leonidasHelmetCrestFaces || 0) <= 0
            || !materialNames.has('leonidascrestred')
            || !materialNames.has('leonidascrestdark')
        ) {
            fail('helmet no contiene el penacho rojo segmentado');
        } else if (
            !String(found.chest.node.extras?.leonidasChestSemanticFaces || '')
                .includes('original=')
        ) {
            fail('chest no separa la piel original de la pechera metalica');
        } else {
            console.log(
                `Leonidas modular: OK - ${required.map((role) => `${role}=${found[role].node.name}`).join(', ')}`
            );
        }
    } catch (error) {
        fail(
            error && error.code === 'ENOENT'
                ? `falta ${assetPath}`
                : error.message
        );
    }
}
