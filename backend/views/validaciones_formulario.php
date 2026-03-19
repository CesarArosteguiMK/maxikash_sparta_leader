<?php
$idFormulario = isset($idFormulario) ? (int) $idFormulario : 0;
$nombreFormulario = isset($nombreFormulario) ? htmlspecialchars($nombreFormulario, ENT_QUOTES, 'UTF-8') : 'Formulario';
$descripcionFormulario = isset($descripcionFormulario) ? htmlspecialchars($descripcionFormulario, ENT_QUOTES, 'UTF-8') : '';
$formBuilderEmbed = !empty($formBuilderEmbed);
$formBuilderJsVer = isset($formBuilderJsVer) ? (int) $formBuilderJsVer : 0;
?>
<style>
/* Form Builder: variables del sistema (core.css) */
:root { --fb-primary: #26344e; --fb-primary-bg: #dcdfe3; --fb-warning: #ffab00; --fb-warning-text: #664400; }

.form-builder-wrap { font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f1f5f9; display: flex; flex-direction: column; }
.form-builder-wrap:not(.form-builder-embed) { min-height: 100vh; }
.form-builder-wrap.form-builder-embed { height: 100%; max-height: 100%; min-height: 0; overflow: hidden; box-sizing: border-box; }

.form-builder-top { position: relative; flex-shrink: 0; background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); padding: 0 20px; display: flex; align-items: center; gap: 12px; height: 54px; }
.form-builder-logo { margin-left: 44px; display: inline-flex; align-items: center; gap: 6px; background: var(--fb-primary-bg); padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(38,52,78,.2); font-weight: 700; font-size: 13px; color: var(--fb-primary); }
.form-builder-tabs { flex: 1; display: flex; justify-content: center; gap: 4px; }
.form-builder-tab { padding: 6px 16px; border-radius: 8px; border: none; cursor: pointer; background: #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600; }
.form-builder-tab:hover { background: #cbd5e1; }
.form-builder-tab.active { background: var(--fb-primary); color: #fff; }
.form-builder-tab i { margin-right: 6px; }
.form-builder-badge { background: #fff2d6; border: 1px solid rgba(255,171,0,.3); border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; color: var(--fb-warning-text); }

.form-builder-split { flex: 1 1 0; display: grid; grid-template-columns: minmax(0, 48fr) minmax(0, 52fr); overflow: hidden; min-height: 0; }
.form-builder-wrap:not(.form-builder-embed) .form-builder-split { flex: 1 1 auto; min-height: calc(100vh - 54px); max-height: calc(100vh - 54px); }

.form-builder-editor-panel { min-width: 0; min-height: 0; overflow-y: auto; padding: 14px 16px; border-right: 1px solid rgba(0,0,0,.06); background: #f8fafc; }
.form-builder-editor-panel.hide { display: none !important; }
.form-builder-preview-panel { min-width: 0; min-height: 0; overflow-y: auto; padding: 20px 18px; background: #f0fdf4; display: flex; flex-direction: column; align-items: center; box-sizing: border-box; }
.form-builder-preview-panel > .form-builder-preview-card { flex-shrink: 0; max-width: 100%; box-sizing: border-box; }
.form-builder-preview-panel.full { grid-column: 1 / -1; }

.form-builder-type-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(136px, 1fr)); gap: 12px 14px; margin-bottom: 14px; }
.form-builder-type-chip { width: 100%; min-height: 44px; padding: 8px 12px; border-radius: 8px; box-sizing: border-box; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #e2e8f0; background: #fff; color: #64748b; }
.form-builder-type-chip:hover { border-color: var(--fb-primary); background: var(--fb-primary-bg); color: var(--fb-primary); }

.form-builder-section { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 14px; margin-bottom: 12px; }
.form-builder-section-title { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }
.form-builder-section-bar { width: 4px; height: 16px; border-radius: 2px; background: var(--fb-primary); }
.form-builder-section-label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .5px; }
.form-builder-lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }

.form-builder-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #111; outline: none; margin-bottom: 10px; box-sizing: border-box; background: #fff; }
.form-builder-input:focus { border-color: var(--fb-primary); box-shadow: 0 0 0 2px rgba(38,52,78,.15); }

.form-builder-questions-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.form-builder-btn-new { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; border: none; background: var(--fb-primary); color: #fff; font-size: 12px; font-weight: 700; cursor: pointer; }
.form-builder-btn-new:hover { background: #1a2940; color: #fff; }
.form-builder-btn-new i { margin-right: 6px; }
.form-builder-btn-outline { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; border: 1.5px dashed var(--fb-primary); background: transparent; color: var(--fb-primary); font-size: 12px; font-weight: 700; cursor: pointer; }
.form-builder-btn-outline:hover { background: rgba(38,52,78,.06); }

.form-builder-qcard { background: #fff; border-radius: 10px; padding: 12px 14px; margin-bottom: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: border-color .15s, box-shadow .15s; }
.form-builder-qcard.active { border-color: var(--fb-primary); box-shadow: 0 0 0 2px var(--fb-primary-bg); }
.form-builder-qcard-num { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; background: #e2e8f0; color: #64748b; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
.form-builder-qcard.active .form-builder-qcard-num { background: var(--fb-primary); color: #fff; }
.form-builder-typebadge { padding: 2px 6px; border-radius: 6px; font-size: 11px; font-weight: 700; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px; }
.form-builder-toggle { width: 34px; height: 18px; border-radius: 9px; background: #cbd5e1; position: relative; cursor: pointer; flex-shrink: 0; transition: background .2s; }
.form-builder-toggle.on { background: var(--fb-primary); }
.form-builder-toggle.on.blue { background: #2563eb; }
.form-builder-toggle-knob { width: 14px; height: 14px; border-radius: 50%; background: #fff; position: absolute; top: 2px; left: 2px; transition: left .2s; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
.form-builder-toggle.on .form-builder-toggle-knob { left: 18px; }

.form-builder-preview-card { width: 100%; max-width: 540px; min-width: 0; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-sizing: border-box; }
.form-builder-preview-strip { height: 4px; background: linear-gradient(90deg, var(--fb-primary), var(--fb-warning), var(--fb-primary)); }
.form-builder-preview-body { padding: 20px 24px; }
.form-builder-preview-sub { text-align: center; color: #64748b; font-size: 11px; margin-top: 10px; }
.form-builder-btn-submit { margin-top: 8px; width: 100%; padding: 10px 12px; border: none; border-radius: 8px; background: var(--fb-primary); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
.form-builder-btn-submit:hover { background: #1a2940; color: #fff; }

.form-builder-back { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); z-index: 1; }
.form-builder-ibtn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #e2e8f0; padding: 0; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; background: #f8fafc; color: #64748b; }
.form-builder-ibtn.danger { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
.form-builder-ibtn:hover { background: #f1f5f9; }
.form-builder-ibtn.danger:hover { background: #fee2e2; }
.form-builder-drag-handle { width: 22px; height: 22px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: #94a3b8; cursor: grab; flex-shrink: 0; user-select: none; background: #f1f5f9; border: 1px solid #e2e8f0; }
.form-builder-drag-handle:hover { color: #64748b; background: #e2e8f0; }
.form-builder-drag-handle:active { cursor: grabbing; }
.form-builder-qcard.form-builder-drag-over { border-color: var(--fb-primary); background: var(--fb-primary-bg); }
.form-builder-qcard.form-builder-dragging { opacity: .7; }
.form-builder-empty-glass { background: #fff; border-radius: 10px; border: 2px dashed #cbd5e1; padding: 32px; text-align: center; color: #64748b; }
.form-builder-formulario-actual { background: #e0f2fe; border: 1px solid rgba(38,52,78,.15); border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 12px; color: var(--fb-primary); display: flex; align-items: center; gap: 8px; }
.form-builder-formulario-actual i { opacity: .9; }
.form-builder-formulario-actual strong { font-weight: 700; }
<?php if ($formBuilderEmbed): ?>
html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
<?php endif; ?>
</style>

<div class="form-builder-wrap<?= $formBuilderEmbed ? ' form-builder-embed' : ''; ?>" id="formBuilderApp">
  <div class="form-builder-top">
    <a href="/validaciones/paneladmin" class="form-builder-back btn btn-sm btn-outline-secondary" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="form-builder-logo"><i class="fa-solid fa-clipboard-list"></i> Form Builder</div>
    <div class="form-builder-tabs">
      <button type="button" class="form-builder-tab active" data-tab="editor"><i class="fa-solid fa-pen-to-square me-1"></i>Editor</button>
      <button type="button" class="form-builder-tab" data-tab="preview"><i class="fa-solid fa-eye me-1"></i>Vista previa</button>
    </div>
    <div class="form-builder-badge" id="formBuilderBadge">0 preguntas incluidas</div>
  </div>
  <div class="form-builder-split">
    <div class="form-builder-editor-panel" id="formBuilderEditorPanel">
      <div class="form-builder-formulario-actual" id="formBuilderFormularioActual">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Cuestionario precargado: <strong id="formBuilderFormularioNombre"><?= $nombreFormulario; ?></strong></span>
      </div>
      <div class="form-builder-section">
        <div class="form-builder-section-title">
          <div class="form-builder-section-bar"></div>
          <span class="form-builder-section-label">Datos del formulario</span>
        </div>
        <div class="form-builder-lbl">Título</div>
        <input type="text" class="form-builder-input" id="formBuilderTitle" placeholder="Nombre del formulario" value="<?= $nombreFormulario; ?>">
        <div class="form-builder-lbl">Descripción</div>
        <textarea class="form-builder-input" id="formBuilderDesc" rows="2" placeholder="Descripción breve..." style="resize: none; margin-bottom: 0;"><?= $descripcionFormulario; ?></textarea>
      </div>
      <div class="form-builder-questions-header">
        <div style="display: flex; align-items: center; gap: 6px;">
          <div class="form-builder-section-bar"></div>
          <span class="form-builder-section-label">Preguntas personalizadas</span>
        </div>
        <button type="button" class="form-builder-btn-new" id="formBuilderBtnNew"><i class="fa-solid fa-plus me-1"></i>Nueva pregunta</button>
      </div>
      <div id="formBuilderQuestionsList"></div>
      <div id="formBuilderEmpty" class="form-builder-empty-glass" style="display: none;">
        <div style="font-size: 28px; margin-bottom: 8px;"><i class="fa-solid fa-pen"></i></div>
        <div style="font-size: 13px;">Crea tu primera pregunta arriba</div>
      </div>
    </div>
    <div class="form-builder-preview-panel" id="formBuilderPreviewPanel">
      <div class="form-builder-preview-card">
        <div class="form-builder-preview-strip"></div>
        <div class="form-builder-preview-body">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--fb-primary-bg, #dcdfe3); display: flex; align-items: center; justify-content: center; font-size: 17px; color: var(--fb-primary, #26344e);"><i class="fa-solid fa-clipboard-list"></i></div>
            <h2 id="formBuilderPreviewTitle" style="margin: 0; font-size: 17px; font-weight: 800; color: #111827;"><?= $nombreFormulario; ?></h2>
          </div>
          <p id="formBuilderPreviewDesc" style="margin: 4px 0 16px; color: #6b7280; font-size: 13px; line-height: 1.5;"><?= $descripcionFormulario; ?></p>
          <div style="height: 1px; background: #e5e7eb; margin: 0 0 18px;"></div>
          <div id="formBuilderPreviewFields"></div>
          <div id="formBuilderPreviewEmpty" style="text-align: center; color: #9ca3af; padding: 28px 0; font-size: 13px;">
            <div style="font-size: 28px; margin-bottom: 8px;"><i class="fa-solid fa-eye"></i></div>
            Activa "Incluir en cuestionario" para ver preguntas aquí
          </div>
          <button type="button" class="form-builder-btn-submit" id="formBuilderBtnSubmit"><i class="fa-solid fa-floppy-disk" style="margin-right:8px"></i>Guardar cuestionario</button>
        </div>
      </div>
      <p class="form-builder-preview-sub" id="formBuilderPreviewSub">Vista previa en tiempo real · 0/0 preguntas incluidas</p>
    </div>
  </div>
</div>

<?php if ($formBuilderEmbed): ?>
<link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* Respaldo para que el modal de SweetAlert se vea bien dentro del iframe del Form Builder */
.swal2-container { background: rgba(0,0,0,0.4) !important; z-index: 1060 !important; }
.swal2-popup { background: #fff !important; border-radius: 0.5rem !important; box-shadow: 0 20px 50px rgba(0,0,0,0.25) !important; padding: 1.5rem !important; }
.swal2-title { color: #111827 !important; font-size: 1.25rem !important; }
.swal2-actions .swal2-confirm { background: #dc2626 !important; color: #fff !important; border-radius: 0.375rem !important; padding: 0.5rem 1rem !important; }
.swal2-actions .swal2-cancel { background: #6b7280 !important; color: #fff !important; border-radius: 0.375rem !important; padding: 0.5rem 1rem !important; }
</style>
<script>
window.FORM_BUILDER_FORMULARIO_ID = <?= $idFormulario; ?>;
window.FORM_BUILDER_TITULO = <?= json_encode(htmlspecialchars_decode($nombreFormulario, ENT_QUOTES), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
window.FORM_BUILDER_DESCRIPCION = <?= json_encode(htmlspecialchars_decode($descripcionFormulario, ENT_QUOTES), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="/assets/js/form_builder_validacion.js?v=<?= $formBuilderJsVer ?: time(); ?>"></script>
<?php endif; ?>
