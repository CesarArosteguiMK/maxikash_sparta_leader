param(
    [Parameter(Mandatory = $true)] [string] $ProjectId,
    [Parameter(Mandatory = $true)] [string] $Region,
    [Parameter(Mandatory = $true)] [string] $ServiceUrl,
    [Parameter(Mandatory = $true)] [string] $CloudRunService,
    [Parameter(Mandatory = $true)] [string] $SchedulerServiceAccount,
    [string] $JobName = 'segundometro-primeros-pagos-s2-cada-2h'
)

$uri = ($ServiceUrl.TrimEnd('/') + '/primerospagoss2/ejecutar')
$body = '{"limite":250}'

# Cloud Run debe requerir autenticacion. Esta cuenta es la unica invocadora del job.
gcloud run services add-iam-policy-binding $CloudRunService `
    --project=$ProjectId --region=$Region `
    --member=('serviceAccount:' + $SchedulerServiceAccount) --role='roles/run.invoker'
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo otorgar roles/run.invoker a la cuenta de Cloud Scheduler.'
}

gcloud scheduler jobs describe $JobName --project=$ProjectId --location=$Region 2>$null
$action = if ($LASTEXITCODE -eq 0) { 'update' } else { 'create' }
gcloud scheduler jobs $action http $JobName `
    --project=$ProjectId --location=$Region `
    --schedule='0 */2 * * *' --time-zone='America/Mexico_City' `
    --uri=$uri --http-method=POST --headers='Content-Type=application/json' `
    --message-body=$body --oidc-service-account-email=$SchedulerServiceAccount

if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo crear o actualizar el Cloud Scheduler job.'
}
