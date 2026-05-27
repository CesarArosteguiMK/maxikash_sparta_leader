<?php
$solicitudes = is_array($solicitudesActualizacionInfo ?? null) ? $solicitudesActualizacionInfo : [];
$error = (string)($errorActualizacionInfo ?? '');

$h = static function ($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-xxl py-4">
  <div class="d-flex align-items-center gap-3 mb-4">
    <span class="btn btn-primary btn-lg disabled rounded-3">
      <i class="fa fa-user-check"></i>
    </span>
    <div>
      <h2 class="mb-0">Revisión de actualizaciones RR.HH.</h2>
      <div class="text-muted">Datos enviados desde MaxikashApp pendientes de comprobar.</div>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-warning"><?= $h($error) ?></div>
  <?php endif; ?>

  <?php if (!$solicitudes): ?>
    <div class="card">
      <div class="card-body text-center text-muted py-5">
        <i class="fa fa-inbox fa-2x mb-2"></i>
        <div class="fw-bold">No hay actualizaciones recibidas por revisar.</div>
      </div>
    </div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <label class="d-inline-flex align-items-center gap-2 mb-0">
            <span>Mostrar</span>
            <select class="form-select form-select-sm w-auto" id="rrhhReviewPageSize">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>registros</span>
          </label>
          <label class="d-inline-flex align-items-center gap-2 mb-0">
            <span>Buscar:</span>
            <input type="search" class="form-control form-control-sm" id="rrhhReviewSearch" autocomplete="off">
          </label>
        </div>

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Actualización</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="rrhhReviewList">
              <?php foreach ($solicitudes as $index => $solicitud): ?>
                <?php
                  $nombre = (string)($solicitud['nombre_persona'] ?? 'Trabajador');
                  $numero = (string)($solicitud['numero_empleado'] ?? '');
                  $nombrePartes = preg_split('/\s+/', trim($nombre)) ?: [];
                  $iniciales = strtoupper(substr($nombrePartes[0] ?? 'R', 0, 1) . substr(end($nombrePartes) ?: 'H', 0, 1));
                  $campos = is_array($solicitud['campos'] ?? null) ? $solicitud['campos'] : [];
                  $modalId = 'rrhhReviewModal' . (int)$index;
                  $searchText = trim(implode(' ', [
                      $numero,
                      $nombre,
                      $solicitud['id_solicitud'] ?? '',
                      $solicitud['recibido_app_at'] ?? '',
                      $solicitud['comentario'] ?? '',
                      json_encode($campos, JSON_UNESCAPED_UNICODE),
                  ]));
                ?>
                <tr data-rrhh-review-item data-search="<?= $h(mb_strtolower($searchText, 'UTF-8')) ?>">
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <span class="badge rounded-circle bg-primary text-white fw-bold p-3 shadow-sm">
                        <?= $h($iniciales ?: 'RH') ?>
                      </span>
                      <div>
                        <div class="fw-bold text-muted"># <?= $h($numero) ?></div>
                        <div class="fw-bold text-muted"><?= $h($nombre) ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-bold">Actualización de información</div>
                    <div class="text-muted small"><?= count($campos) ?> campo<?= count($campos) === 1 ? '' : 's' ?> enviado<?= count($campos) === 1 ? '' : 's' ?></div>
                    <?php if (!empty($solicitud['recibido_app_at'])): ?>
                      <div class="text-muted small">Recibida <?= $h($solicitud['recibido_app_at']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-warning text-dark">En revisión</span>
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#<?= $h($modalId) ?>" title="Ver detalle">
                      <i class="fa fa-eye"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="d-none text-center text-muted border rounded py-4 mt-3" id="rrhhReviewNoResults">
          <i class="fa fa-magnifying-glass fa-2x mb-2"></i>
          <div class="fw-bold">No se encontraron actualizaciones con esa búsqueda.</div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
          <div class="text-muted" id="rrhhReviewInfo">Mostrando 0 a 0 de 0 registros</div>
          <div class="d-inline-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="rrhhReviewPrev">«</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="rrhhReviewNext">»</button>
          </div>
        </div>
      </div>
    </div>

    <?php foreach ($solicitudes as $index => $solicitud): ?>
      <?php
        $nombre = (string)($solicitud['nombre_persona'] ?? 'Trabajador');
        $numero = (string)($solicitud['numero_empleado'] ?? '');
        $campos = is_array($solicitud['campos'] ?? null) ? $solicitud['campos'] : [];
        $modalId = 'rrhhReviewModal' . (int)$index;
      ?>
      <div class="modal fade" id="<?= $h($modalId) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title mb-0">Actualización de información</h5>
                <div class="text-muted small"># <?= $h($numero) ?> - <?= $h($nombre) ?></div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <?php if (!empty($solicitud['comentario'])): ?>
                <div class="alert alert-light border">
                  <strong>Comentario del gestor:</strong> <?= $h($solicitud['comentario']) ?>
                </div>
              <?php endif; ?>

              <?php foreach ($campos as $campo): ?>
                <div class="row g-3 align-items-stretch border-bottom py-3">
                  <div class="col-lg-3 fw-bold text-muted">
                    <?= $h($campo['etiqueta'] ?? $campo['campo'] ?? '') ?>
                  </div>
                  <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                      <div class="text-uppercase small fw-bold text-muted mb-1">Dato anterior</div>
                      <?= nl2br($h($campo['valor_anterior'] ?? '')) ?>
                    </div>
                  </div>
                  <div class="col-lg-5">
                    <div class="border border-success rounded p-3 h-100 bg-success bg-opacity-10">
                      <div class="text-uppercase small fw-bold text-muted mb-1">Dato enviado</div>
                      <?= nl2br($h($campo['valor_nuevo'] ?? '')) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if ($solicitudes): ?>
<script>
(function () {
  const rows = Array.from(document.querySelectorAll('[data-rrhh-review-item]'));
  const search = document.getElementById('rrhhReviewSearch');
  const pageSize = document.getElementById('rrhhReviewPageSize');
  const info = document.getElementById('rrhhReviewInfo');
  const prev = document.getElementById('rrhhReviewPrev');
  const next = document.getElementById('rrhhReviewNext');
  const noResults = document.getElementById('rrhhReviewNoResults');
  let page = 1;

  function normalize(value) {
    return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function render() {
    const q = normalize(search ? search.value : '');
    const size = parseInt(pageSize ? pageSize.value : '10', 10) || 10;
    const filtered = rows.filter(row => normalize(row.dataset.search || row.textContent).includes(q));
    const totalPages = Math.max(1, Math.ceil(filtered.length / size));
    page = Math.min(Math.max(1, page), totalPages);
    const start = (page - 1) * size;
    const visible = filtered.slice(start, start + size);

    rows.forEach(row => { row.hidden = true; });
    visible.forEach(row => { row.hidden = false; });

    if (noResults) noResults.classList.toggle('d-none', filtered.length > 0);
    if (info) {
      const from = filtered.length ? start + 1 : 0;
      const to = Math.min(start + size, filtered.length);
      info.textContent = `Mostrando ${from} a ${to} de ${filtered.length} registros`;
    }
    if (prev) prev.disabled = page <= 1;
    if (next) next.disabled = page >= totalPages;
  }

  if (search) search.addEventListener('input', () => { page = 1; render(); });
  if (pageSize) pageSize.addEventListener('change', () => { page = 1; render(); });
  if (prev) prev.addEventListener('click', () => { page -= 1; render(); });
  if (next) next.addEventListener('click', () => { page += 1; render(); });
  render();
})();
</script>
<?php endif; ?>
