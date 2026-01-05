<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

$grupo_id = $_GET['grupo_id'] ?? null;
if (!$grupo_id) exit;

/*
|--------------------------------------------------------------------------
| OBTENER PROGRAMA DESDE GRUPO
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  SELECT 
    g.id AS grupo_id,
    p.id AS programa_id,
    p.nombre,
    p.descripcion AS descripcion_programa,
    p.duracion,
    p.tipo_duracion
  FROM grupos g
  INNER JOIN programas p ON p.id = g.programa_id
  WHERE g.id = ?
");
$stmt->execute([$grupo_id]);
$programa = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$programa) exit;

/*
|--------------------------------------------------------------------------
| OBTENER INFO DEL PROGRAMA (SI EXISTE)
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  SELECT *
  FROM programas_info
  WHERE programa_id = ?
");
$stmt->execute([$programa['programa_id']]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$funciones = $info && $info['funciones']
  ? json_decode($info['funciones'], true)
  : [];
?>


<style>
    .text-primary {
        color: #0D09A4 !important;
    }
    
    .text-danger {
        color: #FC0000 !important;
    }
    
    .btn-primary {
        background-color: #0D09A4;
    }
    
    .btn-danger {
        background-color: #FC0000;
    }
</style>

<div class="modal-header">
  <h5 class="modal-title">
    <i class="bi bi-journal-text"></i>
    Información del Programa
  </h5>
  <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formInfoPrograma">

  <input type="hidden" name="programa_id" value="<?= $programa['programa_id'] ?>">

  <div class="modal-body">

    <!-- 🔹 FICHA INFORMATIVA DEL PROGRAMA -->
    <div class="border rounded bg-light p-3 mb-4">
    
      <h4 class="fw-bold text-primary mb-1">
        <?= htmlspecialchars($programa['nombre']) ?>
      </h4>
    
      <p class="mb-2 text-muted">
        <i class="bi bi-clock-history"></i>
        Duración: <strong><?= htmlspecialchars($programa['duracion'].' '.$programa['tipo_duracion']) ?></strong>
      </p>
    
      <p class="mb-0">
        <i class="bi bi-mortarboard-fill"></i>
        Titulo a Obtener: <strong><?= nl2br(htmlspecialchars($programa['descripcion_programa'])) ?></strong>
      </p>
    
    </div>


    <hr>

    <!-- 🔹 FORMULARIO EDITABLE -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Perfil Profesional</label>
      <textarea name="perfil_profesional"
                class="form-control"
                rows="1"
                required><?= htmlspecialchars($info['perfil_profesional'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">Descripción General</label>
      <textarea name="descripcion"
                class="form-control"
                rows="5"><?= htmlspecialchars($info['descripcion'] ?? '') ?></textarea>
    </div>

    <hr>

    <h6 class="fw-bold text-primary">
      <i class="bi bi-list-check"></i> Funciones y Competencias
    </h6>

    <div id="contenedorFunciones">
      <?php foreach ($funciones as $f): ?>
        <div class="input-group mb-2">
          <input type="text" name="funciones[]" class="form-control"
                 value="<?= htmlspecialchars($f) ?>">
          <button type="button" class="btn btn-outline-danger"
                  onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
          </button>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="button"
            class="btn btn-outline-primary btn-sm mt-2"
            onclick="agregarFuncion()">
      <i class="bi bi-plus-circle"></i> Agregar función
    </button>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save"></i> Guardar Información
    </button>
  </div>

</form>




