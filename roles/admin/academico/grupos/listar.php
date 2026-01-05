<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

if (!isset($_SESSION['id'], $_SESSION['rol'])) {
  header("Location: /login.php");
  exit;
}

$rol     = $_SESSION['rol'];
$sede_id = $_SESSION['sede_id'] ?? null;

/*
|--------------------------------------------------------------------------
| CONSULTA BASE
|--------------------------------------------------------------------------
*/
$sql = "
  SELECT 
    g.id,
    g.nombre,
    g.jornada,
    g.cupos,
    g.cupos_disponibles,
    g.estado,
    g.fecha_inicio,
    g.fecha_fin,
    n.nombre AS nivel,
    p.nombre AS programa,
    s.nombre AS sede
  FROM grupos g
  INNER JOIN niveles_formacion n ON g.nivel_id = n.id
  INNER JOIN programas p ON g.programa_id = p.id
  INNER JOIN sedes s ON g.sede_id = s.id
";

$params = [];

if ($rol !== 'Admin') {
  $sql .= " WHERE g.sede_id = ? ";
  $params[] = $sede_id;
}

$sql .= " ORDER BY g.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Grupos Académicos</title>

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
        🏷️ BADGES
        ========================= */
        .badge {
          font-size: 0.8rem;
          padding: 6px 10px;
          border-radius: 8px;
        }
        
        /* =========================
        📐 SCROLL TABLAS (SIN CAMBIAR ESTILO)
        ========================= */
        
        .table-responsive-custom {
            width: 100%;
            height: 80vh;              /* 👈 ocupa 80% de la pantalla */
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
        📦 MODALES
        ========================= */
        .modal-content {
          border-radius: 18px;
          border: none;
          box-shadow: 0 10px 30px rgba(0,0,0,.25);
          height: auto;
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

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>
      <i class="bi bi-collection"></i> Grupos Académicos
    </h3>

    <div>
      <button class="btn btn-primary" onclick="abrirModalCrear()">
        <i class="bi bi-plus-circle"></i> Crear
      </button>    
      
      <button class="btn btn-primary"
              id="btnDetalles"
              disabled
              onclick="verDetallesSeleccionado()">
        <i class="bi bi-journal-text"></i> Detalles
      </button>
        
      <button class="btn btn-success" id="btnAbrir" disabled onclick="abrirInscripciones()">
        <i class="bi bi-unlock"></i> Abrir Inscripciones
      </button>
      
      <button class="btn btn-warning" id="btnEditar" disabled onclick="editarSeleccionado()">
        <i class="bi bi-pencil"></i> Editar
      </button>

      <button class="btn btn-danger" id="btnEliminar" disabled onclick="eliminarSeleccionados()">
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
              <th>Grupo</th>
              <th>Nivel</th>
              <th>Programa</th>
              <th>Jornada</th>
              <?php if ($rol === 'Admin'): ?>
                <th>Sede</th>
              <?php endif; ?>
              <th>Cupos</th>
              <th>Disp.</th>
              <th>Estado</th>
              <th>Fechas</th>
            </tr>
          </thead>
        
          <tbody>
          <?php if (!$grupos): ?>
            <tr>
              <td colspan="10" class="text-center text-muted">
                No hay grupos registrados
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($grupos as $g): ?>
              <tr>
                <td>
                  <input type="checkbox" class="chkGrupo" value="<?= $g['id'] ?>" data-nombre="<?= htmlspecialchars($g['nombre']) ?>">
                </td>
        
                <td class="fw-semibold"><?= htmlspecialchars($g['nombre']) ?></td>
                <td><?= $g['nivel'] ?></td>
                <td><?= $g['programa'] ?></td>
                <td><?= $g['jornada'] ?></td>
        
                <?php if ($rol === 'Admin'): ?>
                  <td><?= $g['sede'] ?></td>
                <?php endif; ?>
        
                <td><?= $g['cupos'] ?></td>
                <td><?= $g['cupos_disponibles'] ?></td>
        
                <td>
                  <span class="badge bg-<?=
                    $g['estado'] === 'Inscripciones Abiertas' ? 'success' :
                    ($g['estado'] === 'Creado' ? 'warning' : 'dark')
                  ?>">
                    <?= $g['estado'] ?>
                  </span>
                </td>
        
                <td>
                  <small>
                    <?= $g['fecha_inicio'] ?: '—' ?><br>
                    <?= $g['fecha_fin'] ?: '—' ?>
                  </small>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
    </div>
  </div>


</div>

    
    <?php
    // ... (código PHP existente arriba)
    
    // Agregar consultas para niveles y sedes (si es Admin)
    $sql_niveles = "SELECT id, nombre FROM niveles_formacion WHERE estado = 'Activo' ORDER BY nombre";
    $niveles = $pdo->query($sql_niveles)->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rol === 'Admin') {
      $sql_sedes = "SELECT id, nombre FROM sedes ORDER BY nombre";
      $sedes = $pdo->query($sql_sedes)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Para programas, se cargarán dinámicamente con JS basado en nivel_id
    ?>



      <!-- ✅ MODAL CREAR GRUPO -->
      <div class="modal fade" id="modalCrearGrupo" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
    
            <form id="formCrearGrupo">
    
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="bi bi-plus-circle"></i> Nuevo Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
    
              <div class="modal-body row g-3">
    
                <?php if ($rol === 'Admin'): ?>
                  <div class="col-md-6">
                    <label>Sede</label>
                    <select name="sede_id" class="form-control" required>
                      <option value="">Seleccione</option>
                      <?php foreach($sedes as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= $s['nombre'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                <?php else: ?>
                  <input type="hidden" name="sede_id" value="<?= $sede_id ?>">
                <?php endif; ?>
    
                <div class="col-md-6">
                  <label>Nivel</label>
                  <select name="nivel_id" id="nivel_id" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach($niveles as $n): ?>
                      <option value="<?= $n['id'] ?>"><?= $n['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
    
                <div class="col-md-6">
                  <label>Programa</label>
                  <select name="programa_id" id="programa_id" class="form-control" required>
                    <option value="">Seleccione un nivel primero</option>
                  </select>
                </div>
    
                <div class="col-md-6">
                  <label>Nombre del Grupo</label>
                  <input type="text" name="nombre" class="form-control" required>
                </div>
    
                <div class="col-md-6">
                  <label>Jornada</label>
                  <select name="jornada" class="form-control" required>
                    <option value="">Seleccione</option>
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                    <option value="Noche">Noche</option>
                    <option value="Fin de semana">Fin de semana</option>
                  </select>
                </div>
    
                <div class="col-md-6">
                  <label>Cupos</label>
                  <input type="number" name="cupos" class="form-control" required min="1">
                </div>
    
                <div class="col-md-6">
                  <label>Fecha Inicio</label>
                  <input type="date" name="fecha_inicio" class="form-control">
                </div>
    
                <div class="col-md-6">
                  <label>Fecha Fin</label>
                  <input type="date" name="fecha_fin" class="form-control">
                </div>
    
                <div class="col-md-6">
                  <label>Estado</label>
                  <select name="estado" class="form-control">
                    <option value="Creado">Creado</option>
                  </select>
                </div>
    
              </div>
    
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar Grupo</button>
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
        
        <!-- ✅ MODAL INFO PROGRAMA -->
        <div class="modal fade" id="modalInfoPrograma" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content" id="contenidoInfoPrograma"></div>
          </div>
        </div>



  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  
    const btnAbrir    = document.getElementById('btnAbrir');
    const btnEliminar = document.getElementById('btnEliminar');
    const btnEditar   = document.getElementById('btnEditar');
    const btnDetalles = document.getElementById('btnDetalles');
    
    function actualizarBotones() {
      const seleccionados = document.querySelectorAll('.chkGrupo:checked');
      const total = seleccionados.length;
    
      btnEliminar.disabled = total === 0;
      btnAbrir.disabled    = total === 0;
      btnEditar.disabled   = total !== 1; // 🔥 SOLO UNO
      btnDetalles.disabled = total !== 1; // 👈 IGUAL A EDITAR
    }
    
    // Escuchar cambios en los checkbox
    document.addEventListener('change', (e) => {
      if (e.target.classList.contains('chkGrupo')) {
        actualizarBotones();
      }
    });
  
    // Función para abrir el modal (ya que el botón onclick="abrirModalCrear()" probablemente esté en el HTML)
    function abrirModalCrear() {
      // Resetear el formulario si es necesario
      document.getElementById('formCrearGrupo').reset();
      // Limpiar el select de programas
      document.getElementById('programa_id').innerHTML = '<option value="">Seleccione un nivel primero</option>';
      // Mostrar el modal
      new bootstrap.Modal(document.getElementById('modalCrearGrupo')).show();
    }

    // Evento para cargar programas cuando se selecciona un nivel
    document.getElementById('nivel_id').addEventListener('change', function() {
      const nivelId = this.value;
      const programaSelect = document.getElementById('programa_id');
      
      if (nivelId) {
        // Hacer una petición AJAX para obtener programas del nivel seleccionado
        fetch('roles/admin/academico/grupos/obtener_programas.php?nivel_id=' + nivelId)
          .then(response => response.json())
          .then(data => {
            programaSelect.innerHTML = '<option value="">Seleccione</option>';
            data.forEach(programa => {
              programaSelect.innerHTML += `<option value="${programa.id}">${programa.nombre}</option>`;
            });
          })
          .catch(error => console.error('Error:', error));
      } else {
        programaSelect.innerHTML = '<option value="">Seleccione un nivel primero</option>';
      }
    });

    // Manejar el submit del formulario (ejemplo básico, ajustar según tu backend)
    document.getElementById('formCrearGrupo').addEventListener('submit', function (e) {
      e.preventDefault();
    
      const form = this;
      const formData = new FormData(form);
    
      // 🔄 LOADING
      Swal.fire({
        title: 'Guardando grupo...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    
      fetch('roles/admin/academico/grupos/crear.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text()) // 👈 IMPORTANTE
      .then(text => {
        let data;
    
        try {
          data = JSON.parse(text);
        } catch (e) {
          Swal.fire(
            'Error',
            'Respuesta inválida del servidor',
            'error'
          );
          console.error('Respuesta servidor:', text);
          return;
        }
    
        if (data.success) {
          Swal.fire(
            'Correcto',
            'Grupo creado correctamente',
            'success'
          ).then(() => {
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('modalCrearGrupo')
            );
            modal.hide();
            location.reload();
          });
        } else {
          Swal.fire(
            'Error',
            data.message || 'No se pudo crear el grupo',
            'error'
          );
        }
      })
      .catch(error => {
        console.error(error);
        Swal.fire(
          'Error',
          'Error de conexión con el servidor',
          'error'
        );
      });
    });
    
    
    function abrirInscripciones() {
        
      const checks = document.querySelectorAll('.chkGrupo:checked');
    
      if (checks.length === 0) return;
    
      // Obtener IDs y nombres
      const grupos = [];
      checks.forEach(chk => {
        const fila = chk.closest('tr');
        grupos.push({
          id: chk.value,
          nombre: fila.children[1].innerText.trim()
        });
      });
    
      // HTML listado
      let htmlLista = '<ul style="text-align:left;">';
      grupos.forEach(g => {
        htmlLista += `<li><strong>${g.nombre}</strong></li>`;
      });
      htmlLista += '</ul>';
    
      // 🔔 CONFIRMACIÓN
      Swal.fire({
        title: '¿Abrir inscripciones?',
        html: `
          <p>Los siguientes grupos quedarán visibles en la pagina principal <strong class="text-danger">https://corisnorte.com/</strong>:</p>
          ${htmlLista}
          <p>Por favor, recuerde agregar los detalles y requisitos de cada programa antes de publicar la oferta educativa</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, abrir',
        cancelButtonText: 'Cancelar'
      }).then(result => {
    
        if (!result.isConfirmed) return;
    
        // 🔄 LOADING
        Swal.fire({
          title: 'Procesando...',
          text: 'Actualizando estados',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
    
        const ids = grupos.map(g => g.id).join(',');
    
        fetch('/roles/admin/academico/grupos/abrir_inscripciones.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'ids=' + ids
        })
        .then(res => res.text())
        .then(text => {
    
          let data;
          try {
            data = JSON.parse(text);
          } catch (e) {
            Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
            console.error(text);
            return;
          }
    
          if (data.success) {
            Swal.fire(
              'Correcto',
              'Las inscripciones fueron abiertas correctamente',
              'success'
            ).then(() => location.reload());
          } else {
            Swal.fire(
              'Error',
              data.message || 'No se pudo actualizar',
              'error'
            );
          }
    
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Error de conexión', 'error');
        });
    
      });
    }
    
    
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

    // ✅ EDITAR GRUPO
    function editarSeleccionado() {
    
      const chk = document.querySelector('.chkGrupo:checked');
      if (!chk) return;
    
      const id = chk.value;
    
      Swal.fire({
        title: 'Cargando información...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    
      fetch('/roles/admin/academico/grupos/editar.php?id=' + id)
        .then(res => res.text())
        .then(html => {
    
          document.getElementById('contenidoEditar').innerHTML = html;
    
          Swal.close();
    
          modalEditar.show();
          
          // 🔥 ACTIVAR SUBMIT DESPUÉS DE CARGAR
           activarSubmitEditarGrupo();
    
        })
        .catch(() => {
          Swal.fire(
            'Error',
            'No se pudo cargar la información del grupo',
            'error'
          );
        });
    }
  </script>
  
  <script>
    function activarSubmitEditarGrupo() {
    
      const form = document.getElementById("formEditarGrupo");
      if (!form) {
        console.error("❌ No existe formEditarGrupo");
        return;
      }
    
      form.addEventListener("submit", function(e) {
        e.preventDefault();
    
        Swal.fire({
          title: "Actualizando grupo...",
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
    
        fetch("/roles/admin/academico/grupos/actualizar.php", {
          method: "POST",
          body: new FormData(form)
        })
        .then(res => res.text())
        .then(resp => {
          console.log("RESPUESTA:", resp);
    
          Swal.fire(
            "Correcto",
            "Grupo actualizado correctamente",
            "success"
          ).then(() => location.reload());
        })
        .catch(err => {
          console.error(err);
          Swal.fire("Error", "No se pudo actualizar el grupo", "error");
        });
      });
    }
    </script>
    
    <script>
function eliminarSeleccionados() {

  const checks = document.querySelectorAll(".chkGrupo:checked");

  if (checks.length === 0) return;

  const ids = [];
  let lista = "<ul class='text-start'>";

  checks.forEach(chk => {
    ids.push(chk.value);
    lista += `<li><strong>${chk.dataset.nombre}</strong></li>`;
  });

  lista += "</ul>";

  Swal.fire({
    title: "¿Eliminar grupos?",
    html: `
      <p>Se eliminarán los siguientes grupos:</p>
      ${lista}
      <p class="text-danger mt-2">
        ⚠️ Esta acción no se puede deshacer
      </p>
    `,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#dc3545"
  }).then(result => {

    if (!result.isConfirmed) return;

    Swal.fire({
      title: "Eliminando...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch("/roles/admin/academico/grupos/eliminar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ids })
    })
    .then(res => res.text())
    .then(resp => {

      if (resp === "OK") {
        Swal.fire(
          "Eliminados",
          "Los grupos fueron eliminados correctamente",
          "success"
        ).then(() => location.reload());
      } else {
        Swal.fire("Error", resp, "error");
      }

    })
    .catch(() => {
      Swal.fire("Error", "Error de conexión", "error");
    });

  });
}
</script>

<script>
  const modalInfoPrograma = new bootstrap.Modal(
    document.getElementById('modalInfoPrograma')
  );

  function verDetallesSeleccionado() {

    const chk = document.querySelector('.chkGrupo:checked');
    if (!chk) return;

    const grupoId = chk.value;

    Swal.fire({
      title: 'Cargando información del programa...',
      text: 'Por favor espera',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch('/roles/admin/academico/grupos/info_programa.php?grupo_id=' + grupoId)
      .then(res => res.text())
      .then(html => {
    
        document.getElementById('contenidoInfoPrograma').innerHTML = html;
    
        Swal.close();
        modalInfoPrograma.show();
    
      })
      .catch(() => {
        Swal.fire(
          'Error',
          'No se pudo cargar la información del programa',
          'error'
        );
      });

  }
</script>

<script>
/*
|--------------------------------------------------------------------------
| AGREGAR FUNCIÓN / COMPETENCIA
|--------------------------------------------------------------------------
| Siempre busca el contenedor dentro del modal activo
*/
window.agregarFuncion = function () {

  const cont = document.querySelector(
    '#modalInfoPrograma #contenedorFunciones'
  );

  if (!cont) {
    console.error('contenedorFunciones no encontrado');
    return;
  }

  cont.insertAdjacentHTML('beforeend', `
    <div class="input-group mb-2">
      <input type="text"
             name="funciones[]"
             class="form-control"
             placeholder="Ingrese una función o competencia"
             required>

      <button type="button"
              class="btn btn-outline-danger"
              onclick="this.closest('.input-group').remove()">
        <i class="bi bi-x"></i>
      </button>
    </div>
  `);
};


/*
|--------------------------------------------------------------------------
| SUBMIT DEL FORMULARIO (SIN DUPLICAR LISTENERS)
|--------------------------------------------------------------------------
*/
window.activarSubmitInfoPrograma = function () {

  const form = document.querySelector(
    '#modalInfoPrograma #formInfoPrograma'
  );

  if (!form) {
    console.error('formInfoPrograma no encontrado');
    return;
  }

  // 🔥 Evita múltiples listeners
  form.onsubmit = function (e) {

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando información...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch('/roles/admin/academico/grupos/guardar_info_programa.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {

      if (data.success) {
        Swal.fire(
          'Correcto',
          'Información guardada correctamente',
          'success'
        );
      } else {
        Swal.fire(
          'Error',
          data.message || 'No se pudo guardar la información',
          'error'
        );
      }

    })
    .catch(() => {
      Swal.fire(
        'Error',
        'Error de conexión con el servidor',
        'error'
      );
    });
  };
};


/*
|--------------------------------------------------------------------------
| ACTIVAR SUBMIT AL CARGAR EL MODAL
|--------------------------------------------------------------------------
*/
document.addEventListener('shown.bs.modal', function (e) {
  if (e.target.id === 'modalInfoPrograma') {
    activarSubmitInfoPrograma();
  }
});
</script>


</body>
</html>
