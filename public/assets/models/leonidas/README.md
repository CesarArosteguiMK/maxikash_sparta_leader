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
- `LeonidasHelmet`: casco corintio abierto, con nasal y carrilleras.
- `LeonidasChest`: carcasa completa del torso con pechera.
- `LeonidasHeadUnderlay`: cabeza anatómica visible al retirar el casco.
- `LeonidasTorsoUnderlay`: torso anatómico visible al retirar la pechera.
- `LeonidasHair`: cabello corto independiente, visible solamente sin casco y
  configurable por usuario.

El manifiesto `leonidas-modular-manifest.json` está habilitado. El cargador
regresa automáticamente a `leonidas-spartan-rigged.fbx` si el GLB falta o no
cumple el contrato.

## Colores

La paleta modular ya no intenta adivinar zonas a partir del color del atlas.
Durante la construcciÃ³n, cada isla UV se asigna a un material semÃ¡ntico
independiente:

- `primary`: tela interior y base del faldón.
- `secondary`: correas, ribetes y acentos discretos; los paneles grandes de
  cuero conservan su acabado original.
- `metal`: casco, pechera, grebas, brazales y broches.
- `original`: piel y anatomÃ­a; nunca recibe la paleta.

Los materiales de vestuario conservan la geometrÃ­a, las normales y el sombreado
3D, pero reciben un color sÃ³lido independiente. AsÃ­ no existen pÃ­xeles
compartidos capaces de pintar piel, rostro u otra prenda.

La cabeza anatÃ³mica permanece activa debajo del casco. Las aberturas pequeÃ±as
de ojos y boca muestran esa piel real sin destruir la silueta corintia; el
material metÃ¡lico solo pertenece a la carcasa.

## Reconstrucción y validación

`scripts/construir_leonidas_modular.py` reconstruye el GLB con Blender a partir
de los activos locales. Antes de habilitar una nueva exportación:

1. Ejecutar `node scripts/validar_leonidas_modular.mjs`.
2. Revisar las cinco paletas.
3. Revisar las combinaciones de casco, pechera y cabello, incluida la opción
   completamente calva.
4. Confirmar pose, animación, rostro, uniones de hombros y cintura.

El archivo actual pasó la validación estructural y esas revisiones visuales.
