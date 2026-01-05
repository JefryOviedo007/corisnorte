<?php
require_once __DIR__ . "/../../../config.php";

// 🔹 Traer datos institución (solo un registro)
$institucion = $pdo->query("SELECT * FROM institucion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configuración General</title>

<!-- ✅ BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- ✅ SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ✅ FUENTE -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* =========================
🎨 VARIABLES DE MARCA
========================= */
:root {
  --azul: #1f3c88;
  --azul-claro: #3f6ad8;
  --rojo: #e63946;
  --fondo: #f4f6f9;
  --sidebar-width: 260px;
}

/* =========================
🧍 BODY
========================= */
body {
  font-family: 'Montserrat', sans-serif;
  background-color: var(--fondo);
}

/* =========================
🗂️ CARDS
========================= */
.card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
}

/* =========================
🔘 BOTONES
========================= */
.btn-primary {
  background: var(--azul);
  border: none;
}
.btn-primary:hover {
  background: var(--azul-claro);
}

/* =========================
📋 TABS
========================= */
.nav-tabs .nav-link {
  font-weight: 600;
  color: var(--azul);
}
.nav-tabs .nav-link.active {
  background-color: var(--azul);
  color: white;
  border-radius: 8px 8px 0 0;
}

/* =========================
📝 INPUTS
========================= */
.form-control {
  border-radius: 10px;
}
.form-control:focus {
  border-color: var(--azul-claro);
  box-shadow: 0 0 0 .15rem rgba(63,106,216,.25);
}

/* =========================
✨ ANIMACIÓN
========================= */
.configuracion-module {
  animation: fadeIn .4s ease;
}
@keyframes fadeIn {
  from {opacity:0; transform:translateY(10px);}
  to {opacity:1; transform:translateY(0);}
}

#institucion .card-header {
  background: linear-gradient(
    135deg,
    #c1121f 0%,
    #e63946 45%,
    rgba(230, 57, 70, 1) 100%
  );
  color: #fff;
  border-radius: 16px 16px 0 0;
  box-shadow: inset 0 -1px 0 rgba(255,255,255,.1);
}

#institucion .card-header span,
#institucion .card-header i {
  color: #fff;
  font-weight: 600;
}

/* =========================
🔴 HEADER SEDES — ROJO DEGRADADO
========================= */
#sedes .card-header {
  background: linear-gradient(
    135deg,
    #c1121f 0%,
    #e63946 45%,
    rgba(230, 57, 70, 1) 100%
  );
  color: #fff;
  border-radius: 16px 16px 0 0;
  box-shadow: inset 0 -1px 0 rgba(255,255,255,.1);
}

#sedes .card-header span,
#sedes .card-header i {
  color: #fff;
  font-weight: 600;
}

/* =========================
📦 TARJETICA SEDE — MÁS PRESENCIA
========================= */
#sedes .card.mb-3 {
  border-radius: 16px;
  border: 1px solid rgba(230, 57, 70, 0.35);
  border-left: 6px solid var(--rojo);
  background: linear-gradient(
    135deg,
    #ffffff 0%,
    rgba(230, 57, 70, 0.10) 65%,
    rgba(230, 57, 70, 0.18) 100%
  );
  box-shadow:
    0 6px 14px rgba(0,0,0,0.08),
    0 0 0 1px rgba(230,57,70,0.22);
  transition: transform .25s ease, box-shadow .25s ease;
}

#sedes .card.mb-3:hover {
  transform: translateY(-3px);
  box-shadow:
    0 12px 26px rgba(31, 60, 136, 0.20),
    0 0 0 1px rgba(230,57,70,0.38);
}

/* =========================
🖼️ BLOQUE IMAGEN — PANEL LATERAL
========================= */
#sedes .row.g-0 > .col-md-3 {
  flex: 0 0 180px;
  max-width: 180px;
  max-height: 205px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.55);
  border-right: 1px dashed rgba(230,57,70,.35);
}

/* IMAGEN RESPONSIVA SIN RECORTE */
#sedes .row.g-0 > .col-md-3 img {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: 100%;
  object-fit: contain;
  border-radius: 12px;
  margin: 12px;
  background: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,.15);
}

/* =========================
📄 INFO SEDE
========================= */
#sedes h5 {
  color: var(--azul);
}

#sedes .small i {
  color: var(--azul-claro);
}

#sedes hr {
  border-color: rgba(230, 57, 70, 0.35);
}

/* =========================
⚙️ PANEL ACCIONES
========================= */
#sedes .border-start {
  border-left: 1px dashed rgba(230,57,70,.35);
  background: rgba(255,255,255,.45);
  backdrop-filter: blur(2px);
}

/* =========================
🔵 MODAL HEADER — AZUL DE MARCA
========================= */
#modalSede .modal-header {
  background: linear-gradient(
    135deg,
    var(--azul) 0%,
    var(--azul-claro) 100%
  );
  color: #fff;
}

</style>
</head>

<body>

<div class="container py-4 configuracion-module">

  <h3 class="fw-bold text-primary mb-4">
    <i class="bi bi-gear-fill"></i> Configuración General
  </h3>

  <!-- ✅ PESTAÑAS -->
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#institucion">
        <i class="bi bi-building"></i> Institución
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#sedes">
        <i class="bi bi-geo-alt"></i> Sedes
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#usuarios">
        <i class="bi bi-people"></i> Usuarios
      </a>
    </li>
  </ul>

  <div class="tab-content">

    <!-- ================== INSTITUCIÓN ================== -->
    <div class="tab-pane fade show active" id="institucion">
      <div class="card">
        <div class="card-header fw-bold">
          <i class="bi bi-building-check"></i> Datos de la Institución
        </div>

        <div class="card-body">
          <form id="formInstitucion" class="row g-3">

            <div class="col-md-6">
              <label>Nombre Institucional</label>
              <input type="text" name="nombre" class="form-control"
                value="<?= htmlspecialchars($institucion['nombre'] ?? '') ?>"
                placeholder="Nombre de la institución">
            </div>

            <div class="col-md-6">
              <label>Ciudad</label>
              <input type="text" name="ciudad" class="form-control"
                value="<?= htmlspecialchars($institucion['ciudad'] ?? '') ?>"
                placeholder="Ciudad">
            </div>

            <div class="col-md-6">
              <label>Teléfono</label>
              <input type="text" name="telefono" class="form-control"
                value="<?= htmlspecialchars($institucion['telefono'] ?? '') ?>"
                placeholder="Teléfono">
            </div>

            <div class="col-md-6">
              <label>Correo Institucional</label>
              <input type="email" name="correo" class="form-control"
                value="<?= htmlspecialchars($institucion['correo'] ?? '') ?>"
                placeholder="correo@institucion.edu.co">
            </div>

            <div class="col-md-12">
              <label>Dirección</label>
              <input type="text" name="direccion" class="form-control"
                value="<?= htmlspecialchars($institucion['direccion'] ?? '') ?>"
                placeholder="Dirección completa">
            </div>

            <div class="col-md-12 text-end mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar Cambios
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <?php
    $sedes = $pdo->query("SELECT * FROM sedes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- ================== SEDES ================== -->
    <div class="tab-pane fade" id="sedes">
    
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">
            <i class="bi bi-geo-alt-fill"></i> Sedes del Instituto
          </span>
    
          
        </div>
    
        <div class="card-body">
          <button style="margin-bottom:8px" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSede">
            <i class="bi bi-plus-circle"></i> Crear sede
          </button><br>
    
          <?php if (!$sedes): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-building-x fs-2"></i><br>
              No hay sedes registradas
            </div>
          <?php endif; ?>
    
          <?php foreach ($sedes as $sede): ?>
            <div class="card mb-3 shadow-sm">
              <div class="row g-0 align-items-stretch">
    
                <!-- 🖼️ Imagen -->
                <div class="col-md-3">
                  <img src="<?= $sede['imagen'] ?>" class="img-fluid">
    
                </div>
    
                <!-- 📍 Información -->
                <div class="col-md-7">
                  <div class="card-body">
                    <h5 class="fw-bold mb-2">
                      <?= htmlspecialchars($sede['nombre']) ?>
                    </h5>
    
                    <div class="row small text-muted">
                      <div class="col-md-6">
                        <i class="bi bi-geo"></i> <?= $sede['ciudad'] ?><br>
                        <i class="bi bi-telephone"></i> <?= $sede['telefono'] ?>
                      </div>
                      <div class="col-md-6">
                        <i class="bi bi-envelope"></i> <?= $sede['correo'] ?><br>
                        <i class="bi bi-pin-map"></i> <?= $sede['direccion'] ?>
                      </div>
                    </div>
    
                    <hr class="my-2">
    
                    <div class="small">
                      <strong>Coordinador:</strong><br>
                      <?= $sede['coordinador_nombre'] ?? 'No asignado' ?><br>
                      <?= $sede['coordinador_telefono'] ?><br>
                      <?= $sede['coordinador_correo'] ?>
                    </div>
                  </div>
                </div>
    
                <!-- ⚙️ Acciones -->
                <div class="col-md-2 border-start d-flex flex-column justify-content-center gap-2 p-3">
                    <button class="btn btn-primary"
                      onclick='editarSede(<?= json_encode($sede) ?>)'>
                      <i class="bi bi-pencil"></i> Editar
                    </button>
                    
                    <button class="btn btn-danger"
                      onclick="eliminarSede(<?= $sede['id'] ?>)">
                      <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
    
              </div>
            </div>
          <?php endforeach; ?>
    
        </div>
      </div>
    </div>
    
    <!-- ================== MODAL CREAR SEDE ================== -->
    <div class="modal fade" id="modalSede" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
    
          <form id="formSede" enctype="multipart/form-data">
              
            <input type="hidden" name="id" id="sede_id">
            <input type="hidden" name="imagen_actual" id="imagen_actual">

    
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-building-add"></i> Crear sede
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
    
            <div class="modal-body">
              <div class="row g-3">
    
                <div class="col-md-6">
                  <label>Nombre de la sede</label>
                  <input type="text" name="nombre" class="form-control" required>
                </div>
    
                <div class="col-md-6">
                  <label>Ciudad</label>
                  <input type="text" name="ciudad" class="form-control" required>
                </div>
    
                <div class="col-md-6">
                  <label>Teléfono</label>
                  <input type="text" name="telefono" class="form-control">
                </div>
    
                <div class="col-md-6">
                  <label>Correo</label>
                  <input type="email" name="correo" class="form-control">
                </div>
    
                <div class="col-md-12">
                  <label>Dirección</label>
                  <input type="text" name="direccion" class="form-control">
                </div>
    
                <div class="col-md-12">
                  <label>Imagen de la sede</label>
                  <input type="file" name="imagen" class="form-control">
                </div>
                
                <div class="col-md-12 d-none" id="previewImagen">
                  <label class="fw-bold">Imagen actual</label><br>
                  <img id="imagenPreview" src="" class="img-fluid rounded" style="max-height:150px">
                </div>

    
                <hr>
    
                <div class="col-md-4">
                  <label>Coordinador</label>
                  <input type="text" name="coordinador_nombre" class="form-control">
                </div>
    
                <div class="col-md-4">
                  <label>Tel. coordinador</label>
                  <input type="text" name="coordinador_telefono" class="form-control">
                </div>
    
                <div class="col-md-4">
                  <label>Correo coordinador</label>
                  <input type="email" name="coordinador_correo" class="form-control">
                </div>
    
              </div>
            </div>
    
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar sede
              </button>
            </div>
    
          </form>
    
        </div>
      </div>
    </div>
    
    <script>
    document.getElementById('formSede').addEventListener('submit', function(e){
      e.preventDefault();
    
      const formData = new FormData(this);
      const esEdicion = formData.get('id') !== '';
    
      Swal.fire({
        title: esEdicion ? 'Actualizando sede...' : 'Guardando sede...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    
      fetch('/roles/admin/configuracion/sedes_guardar.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(resp => {
    
        Swal.fire({
          icon: 'success',
          title: esEdicion ? 'Sede actualizada' : 'Sede creada',
          text: esEdicion
            ? 'Los cambios fueron guardados correctamente'
            : 'La sede fue registrada correctamente'
        }).then(() => location.reload());
    
      });
    });
    </script>

    <script>
    function editarSede(sede) {
    
      const form = document.getElementById('formSede');
    
      // ✅ Control edición
      form.querySelector('#sede_id').value = sede.id || '';
      form.querySelector('#imagen_actual').value = sede.imagen || '';
    
      // ✅ CAMPOS PRINCIPALES (YA NO FALLAN)
      form.querySelector('[name="nombre"]').value = sede.nombre || '';
      form.querySelector('[name="ciudad"]').value = sede.ciudad || '';
      form.querySelector('[name="telefono"]').value = sede.telefono || '';
      form.querySelector('[name="correo"]').value = sede.correo || '';
      form.querySelector('[name="direccion"]').value = sede.direccion || '';
    
      // ✅ COORDINADOR
      form.querySelector('[name="coordinador_nombre"]').value = sede.coordinador_nombre || '';
      form.querySelector('[name="coordinador_telefono"]').value = sede.coordinador_telefono || '';
      form.querySelector('[name="coordinador_correo"]').value = sede.coordinador_correo || '';
    
      // ✅ Imagen actual
      if (sede.imagen) {
        document.getElementById('imagenPreview').src = sede.imagen;
        document.getElementById('previewImagen').classList.remove('d-none');
      }
    
      // ✅ Texto modal
      document.querySelector('#modalSede .modal-title').innerHTML =
        '<i class="bi bi-pencil"></i> Editar sede';
    
      document.querySelector('#modalSede button[type="submit"]').innerHTML =
        '<i class="bi bi-save"></i> Guardar cambios';
    
      new bootstrap.Modal(document.getElementById('modalSede')).show();
    }
    </script>

    <script>
    document.getElementById('modalSede').addEventListener('hidden.bs.modal', function () {
    
      // ✅ Limpiar formulario
      document.getElementById('formSede').reset();
    
      // ✅ Quitar ID → vuelve a modo CREAR
      document.getElementById('sede_id').value = '';
      document.getElementById('imagen_actual').value = '';
    
      // ✅ Ocultar imagen preview
      document.getElementById('previewImagen').classList.add('d-none');
      document.getElementById('imagenPreview').src = '';
    
      // ✅ Restaurar textos
      document.querySelector('#modalSede .modal-title').innerHTML =
        '<i class="bi bi-building-add"></i> Crear sede';
    
      document.querySelector('#modalSede button[type="submit"]').innerHTML =
        '<i class="bi bi-save"></i> Guardar sede';
    });
    </script>
    <script>
    function eliminarSede(id) {
    
      Swal.fire({
        title: '¿Eliminar sede?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
    
        if (result.isConfirmed) {
    
          Swal.fire({
            title: 'Eliminando sede...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
    
          fetch('/roles/admin/configuracion/eliminar_sede.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
          })
          .then(response => response.text())
          .then(() => {
    
            Swal.fire({
              icon: 'success',
              title: 'Sede eliminada',
              text: 'La sede fue eliminada correctamente',
              confirmButtonText: 'Aceptar'
            }).then(() => location.reload());
    
          })
          .catch(() => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'No se pudo eliminar la sede'
            });
          });
        }
    
      });
    }
    </script>
    
    
    <?php
    $usuarios = $pdo->query("
        SELECT 
            u.id,
            u.nombre,
            u.correo,
            u.telefono,
            u.rol,
            u.estado,
            u.img_profile,
            u.sede_id,
            s.nombre AS sede_nombre
        FROM usuarios u
        LEFT JOIN sedes s ON u.sede_id = s.id
        ORDER BY u.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $sedesListado = $pdo->query("SELECT id, nombre FROM sedes ORDER BY nombre ASC")
                        ->fetchAll(PDO::FETCH_ASSOC);
    ?>

    
    
    <!-- ================== USUARIOS ================== -->
    <div class="tab-pane fade" id="usuarios">
    
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">
            <i class="bi bi-people-fill"></i> Usuarios del Instituto
          </span>
    
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="bi bi-person-plus"></i> Crear usuario
          </button>
        </div>
    
        <div class="card-body">
    
          <?php if (empty($usuarios)): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-person-x fs-2"></i><br>
              Sin usuarios registrados
            </div>
          <?php endif; ?>
    
          <?php foreach ($usuarios as $u): ?>
            <div class="card mb-3 shadow-sm">
              <div class="row g-0 align-items-center">
    
                <!-- 👤 FOTO -->
                <div class="col-md-3 text-center p-3">
                  <img src="<?= $u['img_profile'] 
                    ? '/roles/admin/configuracion/uploads/usuarios/' . $u['img_profile'] 
                    : '/assets/img/user-default.png' ?>"
                    class="rounded-circle border"
                    style="width:90px;height:90px;object-fit:cover">
                </div>
    
                <!-- 📋 INFORMACIÓN -->
                <div class="col-md-7">
                  <div class="card-body">
                    <h5 class="fw-bold mb-1">
                      <?= htmlspecialchars($u['nombre']) ?>
                    </h5>
    
                    <div class="small text-muted mb-2">
                      <i class="bi bi-envelope"></i> <?= htmlspecialchars($u['correo']) ?><br>
                      <?php if ($u['telefono']): ?>
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($u['telefono']) ?><br>
                      <?php endif; ?>
                    </div>
    
                    <div class="d-flex gap-2 flex-wrap">
                      <span class="badge bg-secondary"><?= $u['rol'] ?></span>
                    
                      <?php if ($u['sede_nombre']): ?>
                        <span class="badge bg-info">Sede <?= htmlspecialchars($u['sede_nombre']) ?></span>
                      <?php endif; ?>
                    
                      <?php if ($u['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-danger">Inactivo</span>
                      <?php endif; ?>
                    </div>

                  </div>
                </div>
    
                <!-- ⚙️ ACCIONES -->
                <div class="col-md-2 border-start d-flex flex-column gap-2 p-3">
                  <button class="btn btn-sm btn-primary"
                    onclick='editarUsuario(<?= json_encode($u) ?>)'>
                    <i class="bi bi-pencil"></i> Editar
                  </button>
    
                  <button class="btn btn-sm btn-danger"
                    onclick="eliminarUsuario(<?= $u['id'] ?>)">
                    <i class="bi bi-trash"></i> Eliminar
                  </button>
                </div>
    
              </div>
            </div>
          <?php endforeach; ?>
    
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalUsuario" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
    
          <form id="formUsuario" enctype="multipart/form-data">
    
            <input type="hidden" name="id" id="usuario_id">
            <input type="hidden" name="imagen_actual" id="usuario_imagen_actual">
    
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-person-plus"></i> Crear usuario
              </h5>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal">
              </button>
            </div>

    
            <div class="modal-body">
              <div class="row g-3">
    
                <!-- ✅ SEDE -->
                <div class="col-md-12">
                  <label>Sede</label>
                  <select name="sede_id" id="sedeSelect" class="form-select" required>
                    <option value="">-- Seleccione una sede --</option>
                    <?php foreach ($sedesListado as $s): ?>
                      <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
    
                <!-- ✅ ROL -->
                <div class="col-md-6">
                  <label>Rol</label>
                  <select name="rol" id="rolSelect" class="form-select" required>
                    <option value="">-- Seleccione rol --</option>
                  </select>
                </div>
    
                <!-- ✅ CONTRASEÑA -->
                <div class="col-md-6">
                  <label>Contraseña</label>
                  <input type="password" name="contrasena" class="form-control">
                </div>
    
                <!-- ✅ DATOS -->
                <div class="col-md-6">
                  <label>Nombre</label>
                  <input type="text" name="nombre" id="nombreUsuario" class="form-control" required>
                </div>
    
                <div class="col-md-6">
                  <label>Correo</label>
                  <input type="email" name="correo" id="correoUsuario" class="form-control" required>
                </div>
    
                <div class="col-md-6">
                  <label>Teléfono</label>
                  <input type="text" name="telefono" id="telefonoUsuario" class="form-control">
                </div>
    
                <!-- ✅ IMAGEN -->
                <div class="col-md-6">
                  <label>Imagen</label>
                  <input type="file" name="imagen" class="form-control">
                </div>
    
              </div>
            </div>
    
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar usuario
              </button>
            </div>
    
          </form>
    
        </div>
      </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const sedeSelect = document.getElementById('sedeSelect');
    const rolSelect = document.getElementById('rolSelect');
    
    const nombreInput = document.getElementById('nombreUsuario');
    const correoInput = document.getElementById('correoUsuario');
    const telefonoInput = document.getElementById('telefonoUsuario');
    
    let datosSedeActual = null;
    
    // 🔹 Al seleccionar sede
    sedeSelect.addEventListener('change', () => {
    
      rolSelect.innerHTML = '<option value="">-- Seleccione rol --</option>';
      nombreInput.value = '';
      correoInput.value = '';
      telefonoInput.value = '';
    
      if (!sedeSelect.value) return;
    
      fetch('/roles/admin/configuracion/obtener_datos_sede.php?id=' + sedeSelect.value)
        .then(res => res.json())
        .then(data => {
    
          datosSedeActual = data;
    
          // ✅ Si NO tiene coordinador → permitir ambos
          if (!data.tiene_coordinador) {
            rolSelect.innerHTML += `
              <option value="Coordinador">Coordinador</option>
              <option value="Secretaria">Secretaria</option>
            `;
          } else {
            // ✅ Si ya tiene coordinador → solo secretaria
            rolSelect.innerHTML += `<option value="Secretaria">Secretaria</option>`;
    
            Swal.fire({
              icon: 'info',
              title: 'Sede con coordinador',
              text: 'Esta sede ya tiene coordinador. Solo puede crear Secretaria.'
            });
          }
    
        });
    });
    
    // 🔹 Al cambiar rol
    rolSelect.addEventListener('change', () => {
    
      if (!datosSedeActual) return;
    
      if (rolSelect.value === 'Coordinador') {
    
        nombreInput.value = datosSedeActual.coordinador.nombre || '';
        correoInput.value = datosSedeActual.coordinador.correo || '';
        telefonoInput.value = datosSedeActual.coordinador.telefono || '';
    
        Swal.fire({
          icon: 'info',
          title: 'Datos cargados',
          text: 'Datos del coordinador cargados desde la sede'
        });
    
      } else {
        nombreInput.value = '';
        correoInput.value = '';
        telefonoInput.value = '';
      }
    });
    </script>
    
    <script>
    document.getElementById('formUsuario').addEventListener('submit', function(e){
      e.preventDefault();
    
      const formData = new FormData(this);
      const esEdicion = formData.get('id') !== '';
    
      Swal.fire({
        title: esEdicion ? 'Actualizando usuario...' : 'Creando usuario...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    
      fetch('/roles/admin/configuracion/usuarios_guardar.php', {
        method: 'POST',
        body: formData
      })
      .then(() => {
        Swal.fire({
          icon: 'success',
          title: esEdicion ? 'Usuario actualizado' : 'Usuario creado'
        }).then(() => location.reload());
      });
    });
    </script>
    
    <script>
    function editarUsuario(u){
    
      const form = document.getElementById('formUsuario');
    
      form.usuario_id.value = u.id;
      form.usuario_imagen_actual.value = u.img_profile || '';
    
      form.nombre.value = u.nombre;
      form.correo.value = u.correo;
      form.telefono.value = u.telefono || '';
    
      sedeSelect.value = u.sede_id || '';
      sedeSelect.disabled = true;
    
      rolSelect.innerHTML = `<option value="${u.rol}" selected>${u.rol}</option>`;
      rolSelect.disabled = true;
    
      document.querySelector('#modalUsuario .modal-title').innerHTML =
        '<i class="bi bi-pencil"></i> Editar usuario';
    
      new bootstrap.Modal(document.getElementById('modalUsuario')).show();
    }
    </script>
    
    <script>
    document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', () => {
      document.getElementById('formUsuario').reset();
      sedeSelect.disabled = false;
      rolSelect.disabled = false;
      rolSelect.innerHTML = '<option value="">-- Seleccione rol --</option>';
    });
    </script>
    
    <script>
    function eliminarUsuario(id) {
    
      Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
    
        if (result.isConfirmed) {
    
          Swal.fire({
            title: 'Eliminando usuario...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
    
          fetch('/roles/admin/configuracion/usuarios_eliminar.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
          })
          .then(response => response.text())
          .then(() => {
    
            Swal.fire({
              icon: 'success',
              title: 'Usuario eliminado',
              text: 'El usuario fue eliminado correctamente',
              confirmButtonText: 'Aceptar'
            }).then(() => location.reload());
    
          })
          .catch(() => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'No se pudo eliminar el usuario'
            });
          });
    
        }
    
      });
    }
    </script>






<script>
document.getElementById('formInstitucion').addEventListener('submit', function(e){
  e.preventDefault();

  Swal.fire({
    title: 'Guardando...',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  fetch('/roles/admin/configuracion/guardar_institucion.php', {
    method: 'POST',
    body: new FormData(this)
  })
  .then(() => {
    Swal.fire({
      icon: 'success',
      title: 'Correcto',
      text: 'Datos actualizados correctamente'
    });
  });
});
</script>

</body>
</html>
