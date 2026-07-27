# Verificación S2 de primeros pagos

El servicio toma exclusivamente las filas cuya `Fecha_primer_vencimiento` cae en la semana operativa actual: martes a lunes. Por cada una consulta Estado de Cuenta S2 y suma solamente los pagos cuya fecha también está dentro de ese mismo periodo.

En la primera corrida crea, si no existe, `tbl_segundometro_primeros_pagos.Total_pagado_semana_s2 DECIMAL(12,2)`. El valor se recalcula en cada ejecución para la cohorte actual; no acumula pagos de otras semanas. La bitácora permanece en `tbl_segundometro_primeros_pagos_s2`.

## Configuración requerida

Configure en el entorno seguro del servicio:

```text
SEGUNDOMETRO_DB_HOST=...
SEGUNDOMETRO_DB_NAME=db-mega-reporte
SEGUNDOMETRO_DB_USER=...
SEGUNDOMETRO_DB_PASSWORD=...
S2_ESTADO_CUENTA_TOKEN=...
ENDPOINT=https://servicios.s2movil.net/s2maxikash/estadocuenta
```

Opcionalmente establezca `SEGUNDOMETRO_PRIMEROS_PAGOS_CRON_SECRET` y envíelo como `X-Cron-Secret`. En Cloud Run, la protección principal debe ser IAM con una cuenta OIDC de Cloud Scheduler; no haga público el servicio.

## Prueba manual

```powershell
C:\xampp\php\php.exe backend\cronjobs\verificar_primeros_pagos_s2.php --limit=5
```

## Cloud Scheduler cada dos horas

1. Despliegue el contenedor en Cloud Run sin acceso público y configure las variables/secretos.
2. Ejecute (el script concede `roles/run.invoker` a la cuenta de Scheduler):

```powershell
.\scripts\deploy_google_scheduler_primeros_pagos_s2.ps1 `
  -ProjectId TU_PROYECTO -Region us-central1 `
  -ServiceUrl https://TU_SERVICIO-...run.app `
  -CloudRunService NOMBRE_SERVICIO_CLOUD_RUN `
  -SchedulerServiceAccount scheduler-primeros-pagos@TU_PROYECTO.iam.gserviceaccount.com
```

El cron `0 */2 * * *` se interpreta en `America/Mexico_City`.
