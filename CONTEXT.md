# Contexto del proyecto MexiKash

## Módulos existentes y sus controladores
- CapHum → /controllers/CapHum.php (patrón de referencia)
- Sabueso → /controllers/Sabueso.php
- [agrega los tuyos]

## Funciones globales reutilizables
- `formatMoney(n)` → formatea moneda en JS
- `showLoader() / hideLoader()` → SweetAlert2 de carga
- [agrega las que ya tienes]

## Convenciones de nomenclatura
- Métodos PHP: camelCase → `levantarTicket()`
- Variables JS: camelCase → `datosTicket`
- IDs de HTML: kebab-case → `btn-guardar-ticket`
- Tablas MySQL: snake_case → `tickets_soporte`

## Librerías ya cargadas en el layout (NO volver a importar)
- jQuery 3.x
- Bootstrap 5.x
- DataTables + extensiones
- SweetAlert2
- Flatpickr
- Sneat assets en /assets/vendor/
```

---

## 3. Cómo pedirle cosas al agente (prompt discipline)

El 80% del "código basura" viene de cómo se le pide, no solo de la config. Usa esta estructura:

**❌ Mal:**
```
"Hazme el módulo de reportes"
```

**✅ Bien:**
```
"Crea el método `reportes()` en /controllers/Reportes.php
siguiendo el patrón exacto de CapHum.php método `gestion()`.
Usa DataTables con la misma config base del proyecto.
El JS va en el heredoc al final del método.
No uses CSS custom, solo clases Sneat/Bootstrap 5.
No importes librerías nuevas."
```

---

## 4. Configuración en Cursor Settings

Ve a **Cursor Settings → Features** y activa:

- ✅ **Codebase indexing** — que lea todo el repo antes de actuar
- ✅ **Auto-index on save** — actualiza el índice cuando guardas
- En el chat, antes de una tarea grande escribe:
```
@codebase antes de hacer cualquier cambio, 
analiza los patrones existentes en el proyecto
y síguelos estrictamente.
```

---

## 5. Workflow recomendado para módulos nuevos

Cada vez que pidas un módulo nuevo, dale este contexto:
```
@CapHum.php @Sabueso.php

Crea un nuevo módulo llamado [X] siguiendo 
EXACTAMENTE la misma arquitectura de estos dos archivos.
No inventes nada nuevo.
Reutiliza las funciones JS que ya existen.
Sneat + Bootstrap 5 únicamente para estilos.