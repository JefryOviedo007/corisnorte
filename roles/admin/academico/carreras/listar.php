<?php
require_once __DIR__ . "/../../../../config.php";

// Traer programas con su nivel
$sql = "SELECT p.*, n.nombre AS nivel 
        FROM programas p
        INNER JOIN niveles_formacion n ON p.nivel_id = n.id
        ORDER BY p.id DESC";
$programas = $pdo->query($sql)->fetchAll();

// Traer niveles para los selects
$niveles = $pdo->query("SELECT * FROM niveles_formacion WHERE estado='Activo'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Programas / Carreras</title>

  <!-- ✅ BOOTSTRAP 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
        🔝 TOPBAR
        ========================= */
        .topbar {
          background: white;
          padding: 15px 25px;
          border-radius: 15px;
          box-shadow: 0 4px 12px rgba(0,0,0,.1);
          margin-bottom: 25px;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        
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
        📐 SCROLL TABLAS (SIN CAMBIAR ESTILO)
        ========================= */
        
        .table-responsive-custom {
            width: 100%;
            height: 75vh;              /* 👈 ocupa 80% de la pantalla */
            overflow-y: auto;          /* 👈 scroll vertical si hace falta */
            overflow-x: auto;          /* 👈 scroll horizontal si hace falta */
            -webkit-overflow-scrolling: touch;
        }
        
        /* Mantener ancho automático según contenido */
        .table {
            width: max-content;
            min-width: 100%;
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
    <h3><i class="bi bi-mortarboard-fill"></i> Programas / Carreras</h3>

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
      <div class="table-responsive-custom">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th width="35" class="text-center">✔</th>
              <th>Programa</th>
              <th>Nivel</th>
              <th>Insc.</th>
              <th>Matrícula</th>
              <th>Mensualidad</th>
              <th>Duración</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
    
            <?php if (count($programas) == 0): ?>
              <tr>
                <td colspan="9" class="text-center text-muted">
                  No hay programas registrados.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($programas as $p): ?>
                <tr>
                  <td class="text-center">
                    <input type="checkbox" class="chkPrograma" value="<?= $p['id'] ?>">
                  </td>
                  <td><?= htmlspecialchars($p['nombre']) ?></td>
                  <td><?= $p['nivel'] ?></td>
                  <td>$<?= number_format($p['costo_inscripcion'], 0, ',', '.') ?></td>
                  <td>$<?= number_format($p['costo_matricula'], 0, ',', '.') ?></td>
                  <td>$<?= number_format($p['mensualidad'], 0, ',', '.') ?></td>
                  <td><?= $p['duracion'] . " " . $p['tipo_duracion'] ?></td>
                  <td>
                    <span class="badge bg-<?= $p['estado']=='Activo'?'success':'secondary' ?>">
                      <?= $p['estado'] ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
    
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- ✅ MODAL CREAR -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <form id="formCrearPrograma">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle"></i> Nuevo Programa
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">

          <div class="col-md-6">
            <label>Nivel</label>
            <select name="nivel_id" class="form-control" required>
              <option value="">Seleccione</option>
              <?php foreach($niveles as $n): ?>
                <option value="<?= $n['id'] ?>"><?= $n['nombre'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label>Nombre del Programa</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>

          <div class="col-md-12">
            <label>Titulo a Obtener</label>
            <input type="text" name="descripcion" class="form-control"></textarea>
          </div>
          
          <div class="col-md-6">
            <label>Tipo Duración</label>
            <select name="tipo_duracion" id="tipo_duracion" class="form-control" required>
              <option value="Meses">Meses</option>
              <option value="Semestres">Semestres</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Duración</label>
            <input type="number" name="duracion" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label>Costo Inscripción</label>
            <input type="number" name="costo_inscripcion" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label>Costo Matrícula</label>
            <input type="number" name="costo_matricula" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label id="labelCosto">Costo Mensual</label>
            <input type="number" name="mensualidad" class="form-control">
          </div>

          <div class="col-md-4">
            <label>Estado</label>
            <select name="estado" class="form-control">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar Programa</button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- ✅ MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="contenidoEditar"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const modalCrear  = new bootstrap.Modal(document.getElementById('modalCrear'));
const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
const btnEditar   = document.getElementById('btnEditar');
const btnEliminar = document.getElementById('btnEliminar');

// ✅ ACTIVAR BOTONES
document.addEventListener("change", function(){
  const seleccionados = document.querySelectorAll(".chkPrograma:checked");
  btnEliminar.disabled = seleccionados.length === 0;
  btnEditar.disabled   = seleccionados.length !== 1;
});

// ✅ ABRIR MODAL CREAR
function abrirModalCrear() {
  modalCrear.show();
}

// ✅ CREAR
document.getElementById("formCrearPrograma").addEventListener("submit", function(e){
  e.preventDefault();
  Swal.fire({title:"Procesando...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

  fetch("/roles/admin/academico/carreras/crear.php", {
    method: "POST",
    body: new FormData(this)
  })
  .then(() => {
    modalCrear.hide();
    Swal.fire("Correcto", "Programa registrado correctamente", "success")
    .then(() => location.reload());
  });
});

// ✅ EDITAR
function editarSeleccionado() {
  const chk = document.querySelector(".chkPrograma:checked");
  if (!chk) return;

  const id = chk.value;

  // 🔵 SWEETALERT DE CARGA
  Swal.fire({
    title: "Cargando información...",
    text: "Por favor espera",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch("/roles/admin/academico/carreras/editar.php?id=" + id)
    .then(res => res.text())
    .then(html => {
      // Insertar contenido
      document.getElementById("contenidoEditar").innerHTML = html;

      // Cerrar loading
      Swal.close();

      // Mostrar modal SOLO cuando ya esté listo
      modalEditar.show();

      // Activar submit del formulario
      activarSubmitEditar();
    })
    .catch(() => {
      Swal.fire(
        "Error",
        "No se pudo cargar la información del programa",
        "error"
      );
    });
}



// ✅ ELIMINAR
function eliminarSeleccionado(){
  const seleccionados = document.querySelectorAll(".chkPrograma:checked");
  let ids = [];

  seleccionados.forEach(chk => ids.push(chk.value));

  Swal.fire({
    title: "¿Eliminar programas?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then(result => {
    if(result.isConfirmed){
      Swal.fire({title:"Procesando...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

      fetch("/roles/admin/academico/carreras/eliminar.php", {
        method: "POST",
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: "ids=" + ids.join(",")
      })
      .then(() => {
        Swal.fire("Eliminados", "Registros eliminados correctamente", "success")
        .then(() => location.reload());
      });
    }
  });
}
</script>

<script>
function activarSubmitEditar() {

  const form = document.getElementById("formEditarPrograma");
  if (!form) {
    console.error("❌ No existe formEditarPrograma");
    return;
  }

  form.addEventListener("submit", function(e) {
    e.preventDefault();

    Swal.fire({
      title: "Actualizando...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch("/roles/admin/academico/carreras/actualizar.php", {
      method: "POST",
      body: new FormData(form)
    })
    .then(res => res.text())
    .then(resp => {
      console.log("RESPUESTA:", resp);

      Swal.fire(
        "Correcto",
        "Programa actualizado correctamente",
        "success"
      ).then(() => location.reload());
    })
    .catch(err => {
      console.error(err);
      Swal.fire("Error", "No se pudo actualizar", "error");
    });
  });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const tipoDuracion = document.getElementById('tipo_duracion');
  const labelCosto = document.getElementById('labelCosto');

  function actualizarLabel() {
    if (tipoDuracion.value === 'Semestres') {
      labelCosto.textContent = 'Costo por Semestre';
    } else {
      labelCosto.textContent = 'Costo Mensual';
    }
  }

  tipoDuracion.addEventListener('change', actualizarLabel);

  // Ejecutar al cargar por defecto
  actualizarLabel();
});
</script>


</body>
</html>
