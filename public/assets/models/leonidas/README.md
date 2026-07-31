# Leonidas 3D

El personaje se deriva del modelo:

- Modelo: Rigged for UE4 - Spartan - free
- Autor: Andy Woodhead
- Fuente: https://sketchfab.com/3d-models/rigged-for-ue4-spartan-free-666f485199db43488b14035f2a3840bf
- Licencia: Creative Commons Attribution (CC BY)

## Modelo activo

El activo principal es `leonidas-spartan-modular-v2.glb`. Conserva el
esqueleto, animación, proporciones, texturas y apariencia del FBX anterior,
pero separa ocho partes:

- `LeonidasBody`: cuerpo estable, extremidades y faldón.
- `LeonidasHelmet`: casco abierto con cúpula y nuca metálicas, rostro
  anatómico independiente y penacho rojo volumétrico anclado a la cúpula.
- `LeonidasChest`: carcasa del torso con separación de piel y pechera.
- `LeonidasHeadUnderlay`: cabeza anatómica visible tanto con el casco abierto
  como al retirarlo; nunca recibe el color del metal.
- `LeonidasTorsoUnderlay`: torso anatómico visible al retirar la pechera.
- `LeonidasHair`: cabello corto independiente, visible solamente sin casco y
  configurable por usuario.
- `LeonidasShield`: escudo ovalado original con aro, remaches, circuitos
  luminosos y emblema corporativo entrelazado.
- `LeonidasSpear`: lanza original con asta de madera, amarres, collares,
  punta de acero facetada y regatÃ³n.

El escudo y la lanza se construyen por geometrÃ­a procedural dentro del
proyecto. No dependen de modelos descargados. Son nodos modulares diferentes,
estÃ¡n anclados respectivamente a la mano izquierda y derecha, y conservan la
animaciÃ³n del esqueleto.

El manifiesto `leonidas-modular-manifest.json` está habilitado. El cargador
regresa automáticamente a `leonidas-spartan-rigged.fbx` si el GLB falta o no
cumple el contrato.

## Colores

La paleta modular no modifica píxeles del atlas compartido. Durante la
construcción, cada isla UV se asigna a un material semántico independiente:

- `primary`: tela interior y base del faldón.
- `secondary`: correas, ribetes y acentos discretos; los paneles grandes de
  cuero conservan su acabado original.
- `metal`: pechera, grebas, brazales, botas completas y broches. El casco
  conserva su contrato visual propio de acero oscuro, acero plateado y borde
  pulido para no perder el facetado al aplicar uniformes.
- `original`: piel y anatomía; nunca recibe la paleta.

Los materiales de vestuario conservan la geometría, las normales y el
sombreado 3D, pero reciben un color sólido independiente. Así no existen
píxeles compartidos capaces de pintar piel, rostro u otra prenda.

Cuando el casco está puesto, la cabeza anatómica permanece visible dentro de
un frente abierto. El relieve facial metálico heredado se elimina durante la
construcción, incluidas sus islas residuales pequeñas. No se agrega una careta,
una cámara negra ni un material metálico sobre la piel. Al retirar el casco
desaparecen la cúpula y el penacho, y reaparece el cabello configurado por el
usuario.

La pieza se reduce al 91 % alrededor de su propio centro y se eleva ligeramente
para mantener proporción con la cabeza y liberar el cuello. La cúpula, los
laterales y la nuca conservan el sombreado esculpido y respetan el color de
metal elegido. El contrato prohíbe cortadores booleanos, paneles frontales
artificiales y cualquier recoloración del rostro.

El penacho utiliza una base baja pegada a la cúpula y una malla continua de
crin en tres rojos próximos. Su base estrecha se abre en abanico, la superficie
incluye nervaduras longitudinales y el último tercio cae hacia atrás hasta una
punta. La pechera conserva en el material original las islas que corresponden
a piel, y el calzado completo se clasifica por sus huesos de pie y dedos para
impedir punteras beige.

## Reconstrucción y validación

`scripts/construir_leonidas_modular.py` reconstruye el GLB con Blender a partir
de los activos locales. Antes de habilitar una nueva exportación:

1. Ejecutar `node scripts/validar_leonidas_modular.mjs`.
2. Revisar visualmente las cinco paletas.
3. Revisar las combinaciones de casco, pechera y cabello, incluida la opción
   completamente calva.
4. Confirmar pose, animación, rostro, uniones de hombros, cintura y calzado.

La validación estructural exige declarar el frente abierto, eliminar el relieve
facial heredado, no exportar paneles de careta, conservar escala y elevación,
penacho continuo, materiales semánticos completos y separación explícita entre
piel original y pechera. La revisión visual debe confirmar que el rostro
mantiene su piel con todas las paletas y que la carcasa posterior permanece
sólida.
