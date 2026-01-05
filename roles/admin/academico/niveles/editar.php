<?php
require_once __DIR__ . "/../../../../config.php";

// ✅ POST → ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];

    $stmt = $pdo->prepare("UPDATE niveles_formacion SET nombre=?, descripcion=?, estado=? WHERE id=?");
    $stmt->execute([$nombre, $descripcion, $estado, $id]);
    exit;
}

// ✅ GET → CARGAR DATOS
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM niveles_formacion WHERE id=?");
$stmt->execute([$id]);
$n = $stmt->fetch();
?>

<form id="formEditarNivel">

  <div class="modal-header">
    <h5 class="modal-title">✏️ Editar Nivel</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>

  <div class="modal-body">

    <input type="hidden" name="id" value="<?= $n['id'] ?>">

    <div class="mb-2">
      <label>Nivel</label>
      <select name="nombre" class="form-control">
        <option <?= $n['nombre']=="Bachillerato"?'selected':'' ?>>Bachillerato</option>
        <option <?= $n['nombre']=="Técnico"?'selected':'' ?>>Técnico</option>
        <option <?= $n['nombre']=="Tecnólogo"?'selected':'' ?>>Tecnólogo</option>
        <option <?= $n['nombre']=="Profesional"?'selected':'' ?>>Profesional</option>
      </select>
    </div>

    <div class="mb-2">
      <label>Descripción</label>
      <textarea name="descripcion" class="form-control"><?= $n['descripcion'] ?></textarea>
    </div>

    <div class="mb-2">
      <label>Estado</label>
      <select name="estado" class="form-control">
        <option value="Activo" <?= $n['estado']=="Activo"?'selected':'' ?>>Activo</option>
        <option value="Inactivo" <?= $n['estado']=="Inactivo"?'selected':'' ?>>Inactivo</option>
      </select>
    </div>

  </div>

  <div class="modal-footer">
    <button type="button" id="btnGuardarEdicion" class="btn btn-primary">
      Actualizar
    </button>
  </div>

</form>
