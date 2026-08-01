# Laboratorios de componentes de Leónidas

Este directorio es un espacio de control de calidad. Los recursos guardados aquí
no forman parte del avatar activo y no deben agregarse al manifiesto productivo
hasta terminar su revisión.

## Flujo obligatorio

1. **Candidato:** se importa o genera el recurso dentro de `qa/`.
2. **Ajuste aislado:** se corrige escala, posición, materiales y contacto usando
   el laboratorio correspondiente.
3. **Inspección completa:** se revisa de frente, ambos perfiles y espalda.
4. **Contrato de aceptación:** se marcan todos los criterios del componente.
5. **Validación humana:** el responsable visual acepta o rechaza el resultado.
6. **Integración:** solamente un candidato aprobado puede copiarse al paquete
   modular y declararse en `leonidas-modular-manifest.json`.

## Laboratorios

- Casco: candidatos 3D, ajuste y giro.
- Pechera y correas: contacto con torso y separación de materiales.
- Cabello y barba: silueta y compatibilidad con cabeza/casco.
- Escudo: escala visual, relieve, agarre y oclusión.
- Lanza: punta, asta, agarre y encuadre.
- Manos y pose: codos, manos, apoyo y simetría.
- Materiales y colores: piel, tela, cuero y metal sin contaminación.
- Encuadre y giro: centrado, pies, cabeza y revisión 360°.

La definición ejecutable de cada laboratorio vive en `component-labs.json`.

## Regla de aislamiento

Las páginas `leonidas-laboratorio.html`, `leonidas-gear-qa.html` y
`leonidas-component-qa.html` pueden leer recursos de esta carpeta. La interfaz
productiva no puede cargar candidatos de `qa/` ni guardar ajustes de laboratorio
como preferencias de usuario.
