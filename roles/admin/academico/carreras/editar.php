<?php
require_once __DIR__ . "/../../../../config.php";

$id = $_GET['id'] ?? null;
if (!$id) exit;

// 🔹 Traer programa
$stmt = $pdo->prepare("SELECT * FROM programas WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) exit;

// 🔹 Traer niveles
$niveles = $pdo->query("
  SELECT * FROM niveles_formacion 
  WHERE estado='Activo'
")->fetchAll();
?>

<div class="modal-header">
  <h5 class="modal-title">
    <i class="bi bi-pencil"></i> Editar Programa
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formEditarPrograma">
  <input type="hidden" name="id" value="<?= $p['id'] ?>">

  <div class="modal-body row g-3">

    <div class="col-md-6">
      <label class="form-label">Nivel de formación</label>
      <select name="nivel_id" class="form-select" required>
        <?php foreach($niveles as $n): ?>
          <option value="<?= $n['id'] ?>" <?= $n['id']==$p['nivel_id']?'selected':'' ?>>
            <?= htmlspecialchars($n['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Nombre del programa</label>
      <input type="text" name="nombre" class="form-control"
             value="<?= htmlspecialchars($p['nombre']) ?>" required>
    </div>

    <div class="col-md-12">
      <label class="form-label">Título a obtener</label>
      <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($p['descripcion']) ?></textarea>
    </div>

    <div class="col-md-6">
      <label class="form-label">Tipo de duración</label>
      <select name="tipo_duracion" id="tipo_duracion_edit" class="form-select" required>
        <option value="Meses" <?= $p['tipo_duracion']=='Meses'?'selected':'' ?>>Meses</option>
        <option value="Semestres" <?= $p['tipo_duracion']=='Semestres'?'selected':'' ?>>Semestres</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Duración</label>
      <input type="number" name="duracion" class="form-control"
             value="<?= $p['duracion'] ?>" required>
    </div>

    <div class="col-md-4">
      <label class="form-label">Costo inscripción</label>
      <input type="number" step="0.01" name="costo_inscripcion" class="form-control"
             value="<?= $p['costo_inscripcion'] ?>" required>
    </div>

    <div class="col-md-4">
      <label class="form-label">Costo matrícula</label>
      <input type="number" step="0.01" name="costo_matricula" class="form-control"
             value="<?= $p['costo_matricula'] ?>" required>
    </div>

    <div class="col-md-4">
      <label class="form-label" id="labelCostoEdit">
        <?= $p['tipo_duracion']=='Semestres'?'Costo por semestre':'Costo mensual' ?>
      </label>
      <input type="number" step="0.01" name="mensualidad" class="form-control"
             value="<?= $p['mensualidad'] ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="Activo" <?= $p['estado']=='Activo'?'selected':'' ?>>Activo</option>
        <option value="Inactivo" <?= $p['estado']=='Inactivo'?'selected':'' ?>>Inactivo</option>
      </select>
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save"></i> Guardar cambios
    </button>
  </div>
</form>

<script>
document.getElementById("formEditarPrograma").addEventListener("submit", function(e){
  e.preventDefault();

  Swal.fire({
    title: "Actualizando...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  fetch("/roles/admin/academico/programas/actualizar.php", {
    method: "POST",
    body: new FormData(this)
  })
  .then(() => {
    Swal.fire("Correcto", "Programa actualizado correctamente", "success")
      .then(() => location.reload());
  });
});

// 🔹 Actualizar label según tipo duración
const tipoEdit = document.getElementById('tipo_duracion_edit');
const labelEdit = document.getElementById('labelCostoEdit');

tipoEdit.addEventListener('change', () => {
  labelEdit.textContent =
    tipoEdit.value === 'Semestres'
      ? 'Costo por semestre'
      : 'Costo mensual';
});
</script>
