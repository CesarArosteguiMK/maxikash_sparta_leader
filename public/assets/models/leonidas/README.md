# Leonidas 3D

El personaje se deriva del modelo:

- Modelo: Rigged for UE4 - Spartan - free
- Autor: Andy Woodhead
- Fuente: https://sketchfab.com/3d-models/rigged-for-ue4-spartan-free-666f485199db43488b14035f2a3840bf
- Licencia: Creative Commons Attribution (CC BY)

## Modelo activo

El activo principal es `leonidas-spartan-modular-v2.glb`. Conserva el
esqueleto, animación, proporciones, texturas y apariencia del FBX anterior,
pero separa seis partes:

- `LeonidasBody`: cuerpo estable, extremidades y faldón.
- `LeonidasHelmet`: casco cerrado, con visor negro en forma de T.
- `LeonidasChest`: carcasa del torso con separación de piel y pechera.
- `LeonidasHeadUnderlay`: cabeza anatómica visible al retirar el casco.
- `LeonidasTorsoUnderlay`: torso anatómico visible al retirar la pechera.
- `LeonidasHair`: cabello corto independiente, visible solamente sin casco y
  configurable por usuario.

El manifiesto `leonidas-modular-manifest.json` está habilitado. El cargador
regresa automáticamente a `leonidas-spartan-rigged.fbx` si el GLB falta o no
cumple el contrato.

## Colores

La paleta modular no modifica píxeles del atlas compartido. Durante la
construcción, cada isla UV se asigna a un material semántico independiente:

- `primary`: tela interior y base del faldón.
- `secondary`: correas, ribetes y acentos discretos; los paneles grandes de
  cuero conservan su acabado original.
- `metal`: casco, pechera, grebas, brazales, botas completas y broches.
- `original`: piel y anatomía; nunca recibe la paleta.

Los materiales de vestuario conservan la geometría, las normales y el
sombreado 3D, pero reciben un color sólido independiente. Así no existen
píxeles compartidos capaces de pintar piel, rostro u otra prenda.

La cabeza anatómica permanece activa debajo del casco, pero la carcasa es
cerrada y no se recorta alrededor del rostro. Una pieza negra independiente
forma el visor en T: nunca recibe los colores del metal y evita que la cara
quede expuesta o parezca pintada. La pechera conserva en el material original
las islas que corresponden a piel, y el calzado completo se clasifica por sus
huesos de pie y dedos para impedir punteras beige.

## Reconstrucción y validación

`scripts/construir_leonidas_modular.py` reconstruye el GLB con Blender a partir
de los activos locales. Antes de habilitar una nueva exportación:

1. Ejecutar `node scripts/validar_leonidas_modular.mjs`.
2. Revisar visualmente las cinco paletas.
3. Revisar las combinaciones de casco, pechera y cabello, incluida la opción
   completamente calva.
4. Confirmar pose, animación, rostro, uniones de hombros, cintura y calzado.

La validación estructural exige un visor independiente, materiales semánticos
completos y separación explícita entre piel original y pechera metálica.
