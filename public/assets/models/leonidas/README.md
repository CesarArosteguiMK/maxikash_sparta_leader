# Leonidas 3D

The active character is `leonidas-spartan-rigged.fbx`, derived from the
following Sketchfab model downloaded by the project owner:

- Model: Rigged for UE4 - Spartan - free
- Author: Andy Woodhead
- Source: https://sketchfab.com/3d-models/rigged-for-ue4-spartan-free-666f485199db43488b14035f2a3840bf
- License: Creative Commons Attribution (CC BY)

The FBX contains the character as one textured skinned mesh with a humanoid
skeleton. Helmet, chest harness and body are not independent interchangeable
objects.

Sparta applies its own material palette at runtime. Users with access to
Leonidas can choose a catalog theme or customize the principal cloth, secondary
details and metal/shield colors. Their selection is stored by `persona_id`; the
corporate default uses the blue and lime colors from the Maxikash logo. The
editor preserves the original character texture and anatomy while recoloring
the uniform. The helmet and chest harness remain part of the character because
the active FBX welds those pieces to the body. Recoloring uses complete UV
islands so cloth, leather, metal and skin do not contaminate one another. A
helmet/chest visibility control must not be offered until a compatible modular
body with real underlying anatomy exists. The original sword is hidden in favor
of the custom Three.js spear.

`leonidas-spartan-free.glb` and `leonidas-spartan.glb` remain as loading
fallbacks only.

## Casco y pechera intercambiables

El cargador ya admite un reemplazo modular en
`leonidas-spartan-modular-v2.glb`. La aplicación solo muestra los interruptores
de casco y pechera cuando el archivo supera el contrato; si falta o es
incompatible, vuelve automáticamente al FBX estable y no ofrece controles que
puedan producir huecos negros o anatomía falsa.

El modelo modular debe conservar la identidad, proporciones, esqueleto y
animaciones de Leonidas, e incluir anatomía completa debajo de la armadura.
Debe exponer como nodos diferentes:

- `LeonidasBody`: cuerpo completo, rostro, cabello y torso real.
- `LeonidasHelmet`: casco y penacho, sin partes del rostro.
- `LeonidasChest`: pechera y correas, sin piel ni anatomía.

Los nombres alternativos aceptados están en
`leonidas-modular-manifest.json`. Es preferible agregar en `extras` de cada nodo
la propiedad `leonidasPart` con `body`, `helmet` o `chest`.

Para recolorear un modelo modular sin contaminar la piel, cada material editable
debe declarar en sus `extras` la propiedad `leonidasPalette` con uno de estos
valores:

- `primary`: faldón y telas principales.
- `secondary`: cuero, correas y ribetes.
- `metal`: casco, grebas, broches y otras piezas metálicas.

Las texturas de esos materiales deben tener base blanca o neutra para que el
color elegido se multiplique conservando relieve y detalle. La piel no debe
declarar `leonidasPalette`.

Flujo de activación:

1. Exportar el GLB en metros, mirando al frente, con los pies en Y=0.
2. Copiarlo como `leonidas-spartan-modular-v2.glb` en esta carpeta.
3. Ejecutar `node scripts/validar_leonidas_modular.mjs`.
4. Revisar visualmente rostro, pose, animaciones, casco, pechera y las cinco
   paletas.
5. Cambiar `enabled` a `true` en `leonidas-modular-manifest.json`.

No debe activarse un modelo construido por superposición de personajes
distintos: aunque tape los huecos, cambia el rostro, la anatomía y la pose.
