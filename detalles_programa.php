<?php
require_once __DIR__ . "/config.php";

$grupo_id = $_GET['grupo_id'] ?? null;
if (!$grupo_id) exit;

/*
|--------------------------------------------------------------------------
| CONSULTA PRINCIPAL (GRUPO + PROGRAMA + NIVEL + SEDE)
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  SELECT
    g.id,
    g.nombre AS grupo,
    g.jornada,
    g.cupos,
    g.cupos_disponibles,
    g.fecha_inicio,
    g.fecha_fin,
    g.estado,
    g.fecha_creacion,

    s.nombre AS sede,
    s.ciudad,

    n.nombre AS nivel,

    p.id AS programa_id,
    p.nombre AS programa,
    p.descripcion AS descripcion_programa,
    p.duracion,
    p.tipo_duracion,
    p.costo_inscripcion,
    p.costo_matricula,
    p.mensualidad

  FROM grupos g
  INNER JOIN sedes s ON s.id = g.sede_id
  INNER JOIN niveles_formacion n ON n.id = g.nivel_id
  INNER JOIN programas p ON p.id = g.programa_id
  WHERE g.id = ?
");
$stmt->execute([$grupo_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) exit;

/*
|--------------------------------------------------------------------------
| INFO ADICIONAL DEL PROGRAMA
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
  SELECT perfil_profesional, descripcion, funciones
  FROM programas_info
  WHERE programa_id = ?
");
$stmt->execute([$data['programa_id']]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$funciones = ($info && $info['funciones'])
  ? json_decode($info['funciones'], true)
  : [];
?>

<!-- ================= HEADER ================= -->
<div class="modal-header text-white" id="modal-header">
  <h5 class="modal-title">
      <i class="bi bi-book"></i>
    <?= htmlspecialchars($data['programa']) ?>
  </h5>
  <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<!-- ================= BODY ================= -->
<div class="modal-body">

  <!-- ===== TITULO / DESCRIPCIÓN ===== -->
  <?php if ($data['descripcion_programa']): ?>
    <p class="mb-4">
    <i class="bi bi-mortarboard text-primary"></i>
      <strong class="text-primary">Titulo a Obtener:</strong> <?= nl2br(htmlspecialchars($data['descripcion_programa'])) ?>
    </p>
  <?php endif; ?>

  <!-- ===== FICHA TÉCNICA ===== -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

      <h6 class="fw-bold text-primary mb-3">
        <i class="bi bi-info-circle"></i> Información del Programa
      </h6>

      <div class="row g-3 small">

        <div class="col-md-6">
          <i class="bi bi-geo-alt text-primary"></i>
          <strong>Sede:</strong>
          <?= htmlspecialchars($data['sede']) ?> (<?= htmlspecialchars($data['ciudad']) ?>)
        </div>

        <div class="col-md-6">
          <i class="bi bi-layers text-primary"></i>
          <strong>Nivel:</strong>
          <?= htmlspecialchars($data['nivel']) ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-people text-primary"></i>
          <strong>Grupo:</strong>
          <?= htmlspecialchars($data['grupo']) ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-clock text-primary"></i>
          <strong>Jornada:</strong>
          <?= htmlspecialchars($data['jornada']) ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-calendar-range text-primary"></i>
          <strong>Duración:</strong>
          <?= $data['duracion'] . ' ' . $data['tipo_duracion'] ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-calendar-event text-primary"></i>
          <strong>Inicio:</strong>
          <?= $data['fecha_inicio'] ? date('d/m/Y', strtotime($data['fecha_inicio'])) : 'Por definir' ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-calendar-x text-primary"></i>
          <strong>Finaliza:</strong>
          <?= $data['fecha_fin'] ? date('d/m/Y', strtotime($data['fecha_fin'])) : 'Por definir' ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-check-circle text-primary"></i>
          <strong>Estado:</strong>
          <?= htmlspecialchars($data['estado']) ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-person-lines-fill text-primary"></i>
          <strong>Cupos totales:</strong>
          <?= $data['cupos'] ?>
        </div>

        <div class="col-md-6">
          <i class="bi bi-person-check text-primary"></i>
          <strong>Cupos disponibles:</strong>
          <?= $data['cupos_disponibles'] ?>
        </div>

      </div>
    </div>
  </div>

  <!-- ===== COSTOS ===== -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

      <h6 class="fw-bold text-primary mb-3">
        <i class="bi bi-cash-coin"></i> Costos del Programa
      </h6>

      <div class="row g-3 small">
        <div class="col-md-4">
          <strong>Inscripción:</strong><br>
          $<?= number_format($data['costo_inscripcion'], 0, ',', '.') ?>
        </div>
        <div class="col-md-4">
          <strong>Matrícula:</strong><br>
          $<?= number_format($data['costo_matricula'], 0, ',', '.') ?>
        </div>
        <div class="col-md-4">
          <strong>Semestre/Mensualidad:</strong><br>
          <?= $data['mensualidad'] ? '$' . number_format($data['mensualidad'], 0, ',', '.') : 'No aplica' ?>
        </div>
      </div>

    </div>
  </div>

  <!-- ===== INFO ACADÉMICA ===== -->
  <?php if ($info): ?>

    <hr>

    <h6 class="fw-bold text-primary">
      <i class="bi bi-person-badge"></i> Perfil Profesional
    </h6>
    <p><?= nl2br(htmlspecialchars($info['perfil_profesional'])) ?></p>

    <?php if ($info['descripcion']): ?>
      <h6 class="fw-bold text-primary mt-3">
        <i class="bi bi-info-circle"></i> Descripción General
      </h6>
      <p><?= nl2br(htmlspecialchars($info['descripcion'])) ?></p>
    <?php endif; ?>

    <?php if ($funciones): ?>
      <h6 class="fw-bold text-primary mt-3">
        <i class="bi bi-list-check"></i> Funciones y Competencias
      </h6>
      <ul>
        <?php foreach ($funciones as $f): ?>
          <li><?= htmlspecialchars($f) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  <?php else: ?>
    <p class="text-muted fst-italic">
      Información académica detallada próximamente disponible.
    </p>
  <?php endif; ?>

</div>
