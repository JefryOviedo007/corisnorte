<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

$id = $_GET['id'] ?? null;
if (!$id) exit;

$rol = $_SESSION['rol'] ?? '';

$stmt = $pdo->prepare("
  SELECT g.*, 
         n.nombre AS nivel,
         p.nombre AS programa,
         s.nombre AS sede
  FROM grupos g
  INNER JOIN niveles_formacion n ON n.id = g.nivel_id
  INNER JOIN programas p ON p.id = g.programa_id
  INNER JOIN sedes s ON s.id = g.sede_id
  WHERE g.id = ?
");
$stmt->execute([$id]);
$g = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$g) exit;

$sedes = [];
if ($rol === 'Admin') {
  $sedes = $pdo->query("SELECT id, nombre FROM sedes ORDER BY nombre")
               ->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="modal-header">
  <h5 class="modal-title">
    <i class="bi bi-pencil"></i> Editar Grupo
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formEditarGrupo">

  <input type="hidden" name="id" value="<?= $g['id'] ?>">

  <!-- ✅ BODY CON SCROLL AUTOMÁTICO -->
  <div class="modal-body row g-3">

    <?php if ($rol === 'Admin'): ?>
      <div class="col-md-6">
        <label class="form-label">Sede</label>
        <select name="sede_id" class="form-select" required>
          <?php foreach($sedes as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $s['id']==$g['sede_id']?'selected':'' ?>>
              <?= htmlspecialchars($s['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <div class="col-md-6">
      <label class="form-label">Nivel</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($g['nivel']) ?>" disabled>
    </div>

    <div class="col-md-6">
      <label class="form-label">Programa</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($g['programa']) ?>" disabled>
    </div>

    <div class="col-md-6">
      <label class="form-label">Nombre del Grupo</label>
      <input type="text" name="nombre" class="form-control"
             value="<?= htmlspecialchars($g['nombre']) ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Jornada</label>
      <select name="jornada" class="form-select" required>
        <?php foreach (['Mañana','Tarde','Noche','Fin de semana'] as $j): ?>
          <option value="<?= $j ?>" <?= $g['jornada']===$j?'selected':'' ?>>
            <?= $j ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Cupos</label>
      <input type="number" name="cupos" class="form-control"
             value="<?= $g['cupos'] ?>" min="1" required>
      <small class="text-muted">Disponibles: <?= $g['cupos_disponibles'] ?></small>
    </div>

    <div class="col-md-6">
      <label class="form-label">Fecha Inicio</label>
      <input type="date" name="fecha_inicio" class="form-control"
             value="<?= $g['fecha_inicio'] ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Fecha Fin</label>
      <input type="date" name="fecha_fin" class="form-control"
             value="<?= $g['fecha_fin'] ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="Creado" <?= $g['estado']=='Creado'?'selected':'' ?>>Creado</option>
        <option value="Inscripciones Abiertas" <?= $g['estado']=='Inscripciones Abiertas'?'selected':'' ?>>
          Inscripciones Abiertas
        </option>
        <option value="Inscripciones Cerradas" <?= $g['estado']=='Inscripciones Cerradas'?'selected':'' ?>>
          Inscripciones Cerradas
        </option>
      </select>
    </div>

  </div>

  <!-- ✅ FOOTER FIJO -->
  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save"></i> Guardar cambios
    </button>
  </div>

</form>


