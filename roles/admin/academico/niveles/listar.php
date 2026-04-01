<?php
require_once __DIR__ . "/../../../../config.php";
$niveles = $pdo->query("SELECT * FROM niveles_formacion ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Niveles de Formación</title>

  <!-- ✅ BOOTSTRAP 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ ICONOS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- ✅ SWEETALERT -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
        /* =========================
        🎨 VARIABLES DE MARCA
        ========================= */
        :root {
            --azul: #1f3c88;
            --azul-claro: #3f6ad8;
            --rojo: #e63946;
            --fondo: #f4f6f9;
            --gris: #e0e0e0;
            --blanco: #ffffff;
            --texto: #2b2b2b;
            --sidebar-width: 260px;
        }
        
        
        body { font-family: 'Montserrat', sans-serif; background-color: var(--fondo); margin-top: 70px; }
        
        
        /* =========================
        📄 CONTENIDO PRINCIPAL
        ========================= */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            margin-top: 70px;
        }
        
        .container {margin-top:10px;}
        
        
        
        /* =========================
        🗂️ CARDS
        ========================= */
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
        }
        
        /* =========================
        🔘 BOTONES PERSONALIZADOS
        ========================= */
        .btn-primary {
            background: var(--azul);
            border: none;
        }
        
        .btn-primary:hover {
            background: var(--azul-claro);
        }
        
        .btn-danger {
            background: var(--rojo);
            border: none;
        }
        
        .btn-danger:hover {
            filter: brightness(0.9);
        }
        
        .btn-secondary {
            background: var(--azul-claro);
            border: none;
        }
        
        .btn-secondary:hover {
            filter: brightness(0.9);
        }
        
        .btn-success {
            background: #2ecc71;
            border: none;
        }
        
        .btn-success:hover {
            filter: brightness(0.9);
        }
        
        /* =========================
        📋 TABLAS (FORZADO SOBRE BOOTSTRAP)
        ========================= */
        
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        /* 👉 FORZAR DEGRADADO AUNQUE EXISTA table-dark */
        .table thead,
        .table thead.table-dark,
        .table thead tr,
        .table thead tr th {
            background: var(--azul) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        /* 👉 Texto del encabezado más elegante */
        .table thead th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }
        
        /* Hover de filas */
        .table-hover tbody tr:hover {
            background-color: rgba(63, 106, 216, 0.08);
        }
        
        /* Celdas centradas */
        .table td {
            vertical-align: middle;
        }

        
        /* =========================
        🏷️ BADGES
        ========================= */
        .badge {
            font-size: 0.8rem;
            padding: 6px 10px;
            border-radius: 8px;
        }
        
        /* =========================
        📦 MODALES
        ========================= */
        .modal-content {
            border-radius: 18px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        
        .modal-header {
            background: linear-gradient(90deg, var(--azul), var(--azul-claro));
            color: white;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-footer {
            border-top: none;
        }
        
        /* =========================
        📝 INPUTS Y SELECT
        ========================= */
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid var(--gris);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 .15rem rgba(63, 106, 216, 0.25);
        }
        
        /* =========================
        ✅ CHECKBOX
        ========================= */
        input[type="checkbox"] {
            accent-color: var(--azul);
            transform: scale(1.1);
        }
        
        /* =========================
        📢 SWEETALERT PERSONALIZADO
        ========================= */
        .swal2-popup {
            border-radius: 18px !important;
            font-family: 'Montserrat', sans-serif;
        }
        
        .swal2-confirm {
            background: var(--azul) !important;
            border-radius: 10px !important;
        }
        
        .swal2-cancel {
            border-radius: 10px !important;
        }
        
        /* =========================
        📱 RESPONSIVE
        ========================= */
        @media (max-width: 768px) {
            .sidebar {
                width: 75px;
            }
        
            .sidebar span {
                display: none;
            }
        
            .main-content {
                margin-left: 75px;
            }
        }

  </style>
</head>
<body>

<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-book"></i> Niveles de Formación</h3>

    <div>
      <button class="btn btn-primary" onclick="abrirModalCrear()">
        <i class="bi bi-plus-circle"></i> Crear
      </button>

      <button class="btn btn-warning" id="btnEditar" disabled onclick="editarSeleccionado()">
        <i class="bi bi-pencil"></i> Editar
      </button>

      <button class="btn btn-danger" id="btnEliminar" disabled onclick="eliminarSeleccionado()">
        <i class="bi bi-trash"></i> Eliminar
      </button>
    </div>
  </div>

  <div class="card p-3">
    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr>
          <th width="35" class="text-center">✔</th>
          <th>Nivel</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>

        <!-- ✅ SI NO HAY REGISTROS -->
        <?php if (count($niveles) == 0): ?>
          <tr>
            <td colspan="6" class="text-center text-muted">
              No hay niveles de formación registrados.
            </td>
          </tr>
        <?php else: ?>

          <?php foreach ($niveles as $n): ?>
          <tr>
            <td class="text-center">
              <input type="checkbox" class="chkNivel" value="<?= $n['id'] ?>">
            </td>
            <td><?= htmlspecialchars($n['nombre']) ?></td>
            <td><?= htmlspecialchars($n['descripcion']) ?></td>
            <td>
              <span class="badge bg-<?= $n['estado']=='Activo'?'success':'secondary' ?>">
                <?= $n['estado'] ?>
              </span>
            </td>
            <td><?= $n['fecha_creacion'] ?></td>
          </tr>
          <?php endforeach; ?>

        <?php endif; ?>

      </tbody>
    </table>
  </div>
</div>

<!-- ✅ MODAL CREAR -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form id="formCrearNivel">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle"></i> Crear Nivel
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-2">
            <label>Tipo de Nivel</label>
            <select name="nombre" class="form-control" required>
              <option value="">Seleccione</option>
              <option>Bachillerato</option>
              <option>Técnico</option>
              <option>Tecnólogo</option>
              <option>Profesional</option>
            </select>
          </div>

          <div class="mb-2">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control"></textarea>
          </div>

          <div class="mb-2">
            <label>Estado</label>
            <select name="estado" class="form-control">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- ✅ MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoEditar"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalCrear  = new bootstrap.Modal(document.getElementById('modalCrear'));
const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
const btnEditar   = document.getElementById('btnEditar');
const btnEliminar = document.getElementById('btnEliminar');

// ✅ ACTIVAR / DESACTIVAR BOTONES
document.addEventListener("change", function(){
  const seleccionados = document.querySelectorAll(".chkNivel:checked");
  btnEliminar.disabled = seleccionados.length === 0;
  btnEditar.disabled   = seleccionados.length !== 1;
});

// ✅ ALERTA DE CARGA GLOBAL
function mostrarCargando(texto = "Procesando...") {
  Swal.fire({
    title: texto,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
}

// ✅ ABRIR MODAL CREAR
function abrirModalCrear() {
  modalCrear.show();
}

// ✅ CREAR
document.getElementById("formCrearNivel").addEventListener("submit", function(e){
  e.preventDefault();
  const datos = new FormData(this);

  modalCrear.hide();
  mostrarCargando("Guardando nivel...");

  fetch("/roles/admin/academico/niveles/crear.php", {
    method: "POST",
    body: datos
  })
  .then(() => {
    Swal.fire({
      title: "Correcto",
      text: "Nivel registrado correctamente",
      icon: "success",
      confirmButtonText: "OK"
    }).then(() => location.reload());
  });
});

// ✅ EDITAR
function editarSeleccionado(){
  const seleccionado = document.querySelector(".chkNivel:checked");
  const id = seleccionado.value;

  mostrarCargando("Cargando información...");

  fetch("/roles/admin/academico/niveles/editar.php?id=" + id)
    .then(res => res.text())
    .then(html => {
      Swal.close();
      document.getElementById("contenidoEditar").innerHTML = html;
      modalEditar.show();
    });
}

// ✅ ELIMINAR
function eliminarSeleccionado(){
  const seleccionados = document.querySelectorAll(".chkNivel:checked");
  let ids = [];

  seleccionados.forEach(chk => ids.push(chk.value));

  Swal.fire({
    title: "¿Eliminar niveles?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then(result => {
    if(result.isConfirmed){

      mostrarCargando("Eliminando registros...");

      fetch("/roles/admin/academico/niveles/eliminar.php", {
        method: "POST",
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: "ids=" + ids.join(",")
      })
      .then(() => {
        Swal.fire({
          title: "Eliminados",
          text: "Registros eliminados correctamente",
          icon: "success",
          confirmButtonText: "OK"
        }).then(() => location.reload());
      });
    }
  });
}

// ✅ GUARDAR EDICIÓN
document.addEventListener("click", function(e){
  if(e.target && e.target.id === "btnGuardarEdicion"){
    e.preventDefault();

    const form = document.getElementById("formEditarNivel");
    const datos = new FormData(form);

    modalEditar.hide();
    mostrarCargando("Actualizando nivel...");

    fetch("/roles/admin/academico/niveles/editar.php", {
      method: "POST",
      body: datos
    })
    .then(() => {
      Swal.fire({
        title: "Actualizado",
        text: "Nivel actualizado correctamente",
        icon: "success",
        confirmButtonText: "OK"
      }).then(() => location.reload());
    });
  }
});
</script>


</body>
</html>
