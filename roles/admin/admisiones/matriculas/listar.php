<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

if (!isset($_SESSION['id'], $_SESSION['rol'])) {
    header("Location: /login.php");
    exit;
}

$rol      = $_SESSION['rol'];
$sede_id  = $_SESSION['sede_id'] ?? null;

/*
|--------------------------------------------------------------------------
| CONSULTA DE INSCRIPCIONES CORREGIDA
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT 
        i.id,
        i.codigo,
        i.estado,
        i.fecha_inscripcion,
        p.nombres_completos AS estudiante,
        p.tipo_documento,
        p.numero_documento,
        g.nombre AS grupo,
        g.jornada,
        prog.nombre AS programa,
        prog.costo_matricula, -- Asegúrate de traer el costo para el modal de pago
        s.nombre AS sede
    FROM inscripciones i
    INNER JOIN personas p ON i.persona_id = p.id
    INNER JOIN grupos g ON i.grupo_id = g.id
    INNER JOIN programas prog ON g.programa_id = prog.id
    INNER JOIN sedes s ON g.sede_id = s.id
    WHERE i.estado IN ('Convocado')
";

$params = [];

// Cambiamos "WHERE" por "AND" porque ya existe un WHERE arriba
if ($rol !== 'Admin') {
    $sql .= " AND g.sede_id = ? ";
    $params[] = $sede_id;
}

$sql .= " ORDER BY i.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<style>
       .container {margin-top:10px}
       
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
            height: 70vh;              /* 👈 ocupa 80% de la pantalla */
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
        /* Contenedor padre con borde permanente */
        .custom-filter-group {
            border-radius: 10px;
            overflow: hidden;
            /* Cambiamos var(--gris) por var(--azul-claro) para que sea permanente */
            border: 1px solid var(--azul-claro); 
            background-color: #ffffff;
            transition: box-shadow 0.3s ease; /* Suaviza el efecto de brillo */
        }
        
        /* El icono de la izquierda */
        .custom-filter-group .input-group-text {
            background-color: #ffffff;
            border: none;
            padding-left: 15px;
            /* Color del icono un poco más suave o igual al azul */
            color: var(--azul-claro); 
        }
        
        /* El input y el select sin bordes internos */
        .custom-filter-group .form-control, 
        .custom-filter-group .form-select {
            border: none !important; 
            padding: 10px 14px;
            outline: none;
        }
        
        /* Efecto de "brillo" cuando el usuario entra al campo */
        .custom-filter-group:focus-within {
            box-shadow: 0 0 0 .20rem rgba(63, 106, 216, 0.20);
        }
        
        /* Quitar el sombreado por defecto de Bootstrap en el focus */
        .custom-filter-group .form-control:focus, 
        .custom-filter-group .form-select:focus {
            box-shadow: none !important;
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
        
        /* =========================
        🔘 BOTONES PERSONALIZADOS
        ========================= */
        .btn-primary {
          background: var(--azul);
          border: none;
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
    
    
</style>

<div class="container mt-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>
      <i class="bi bi-person-check"></i> Gestión de Matrículas
    </h3>

    <div>
      <button class="btn btn-primary" id="btnActualizar" disabled onclick="actualizarDatos()">
        <i class="bi bi-person-check"></i> Actualizar Información
      </button>    
      
      <button class="btn btn-success" id="btnConfirmar" disabled onclick="confirmarMatricula()">
        <i class="bi bi-check-circle"></i> Matricular
      </button>
    </div>
  </div>

  <div class="card p-3">
        <div class="row mb-3 g-2">
          <div class="col-md-6">
            <div class="input-group custom-filter-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="inputBusqueda" class="form-control" 
                     placeholder="Buscar por código, nombre o cédula...">
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group custom-filter-group">
              <span class="input-group-text"><i class="bi bi-filter"></i></span>
              <select id="selectPrograma" class="form-select">
                <option value="">Todos los programas/grupos</option>
                <?php 
                  $programasUnicos = array_unique(array_column($inscripciones, 'programa'));
                  foreach($programasUnicos as $p): 
                ?>
                  <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2 text-end d-flex align-items-center justify-content-end">
            <small class="text-muted fw-bold" id="contadorRegistros">
              <i class="bi bi-people"></i> Total: <?= count($inscripciones) ?>
            </small>
          </div>
        </div>
        
      <div class="table-responsive-custom">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th width="35" class="text-center">
                  <input type="checkbox" id="chkTodos" title="Seleccionar todos los visibles">
              </th>
              <th>Código</th>
              <th>Aspirante</th>
              <th>Programa/Grupo</th>
              <th>Costo Matrícula</th>
              <th>Jornada</th>
              <?php if ($rol === 'Admin'): ?>
                <th>Sede</th>
              <?php endif; ?>
              <th>Fecha Reg.</th>
              <th>Estado</th>
            </tr>
          </thead>
        
          <tbody>
          <?php if (!$inscripciones): ?>
            <tr>
              <td colspan="<?= ($rol === 'Admin') ? '8' : '7' ?>" class="text-center text-muted">
                No hay aspirantes para matricular
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($inscripciones as $i): ?>
              <tr>
                <td class="text-center">
                  <input type="checkbox" class="chkInscripcion" value="<?= $i['id'] ?>" 
                         data-estado="<?= $i['estado'] ?>" 
                         data-codigo="<?= $i['codigo'] ?>">
                </td>
        
                <td class="fw-bold text-primary"><?= htmlspecialchars($i['codigo'] ?? 'SIN-COD') ?></td>
                <td>
                    <div class="fw-semibold"><?= htmlspecialchars($i['estudiante']) ?></div>
                    <small class="text-muted"><?= $i['tipo_documento'] ?> <?= $i['numero_documento'] ?></small>
                </td>
                <td>
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($i['programa']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($i['grupo']) ?></small>
                </td>
                <td class="fw-bold text-success text-center">
                    $ <?= number_format($i['costo_matricula'], 0, ',', '.') ?>
                </td>
                <td><?= $i['jornada'] ?></td>
        
                <?php if ($rol === 'Admin'): ?>
                  <td><?= htmlspecialchars($i['sede']) ?></td>
                <?php endif; ?>
        
                <td>
                  <small><?= date('d/m/Y', strtotime($i['fecha_inscripcion'])) ?></small>
                </td>

                <td>
                  <span class="badge bg-<?= 
                    $i['estado'] === 'Convocado' ? 'warning' : 
                    ($i['estado'] === 'Preinscrito' ? 'warning' : 'danger') 
                  ?>">
                    <?= $i['estado'] ?>
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

<div class="modal fade" id="modalEditarPersona" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Actualizar Ficha del Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPersona" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row g-3">
                        <div class="col-md-3 text-center border-end">
                            <label class="form-label fw-bold">Foto Perfil</label>
                            <div class="mb-2">
                                <img src="assets/img/default-user.png" id="preview_foto" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                            <input type="file" class="form-control form-control-sm" name="foto" id="edit_foto" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <div class="col-md-9">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small">Nombre Completo</label>
                                    <input type="text" class="form-control" name="nombres_completos" id="edit_nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Tipo Documento</label>
                                    <select class="form-select" name="tipo_documento" id="edit_tipo_doc">
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="PEP">PEP</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Número Documento</label>
                                    <input type="text" class="form-control" name="numero_documento" id="edit_doc" required>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Dirección de Residencia</label>
                            <input type="text" class="form-control" name="direccion" id="edit_direccion">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" id="edit_correo" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Celular Estudiante</label>
                            <input type="text" class="form-control" name="telefono" id="edit_tel">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Estado Civil</label>
                            <select class="form-select" name="estado_civil" id="edit_estado_civil">
                                <option value="Soltero/a">Soltero/a</option>
                                <option value="Casado/a">Casado/a</option>
                                <option value="Unión Libre">Unión Libre</option>
                                <option value="Divorciado/a">Divorciado/a</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Nivel Sisbén</label>
                            <input type="text" class="form-control" name="sisben" id="edit_sisben" placeholder="Ej: A1, B4...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">EPS</label>
                            <input type="text" class="form-control" name="eps" id="edit_eps">
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-secondary py-2 mb-0 fw-bold small"><i class="bi bi-telephone-outbound"></i> Contacto de Emergencia</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Nombre de Contacto</label>
                            <input type="text" class="form-control" name="contacto_nombre" id="edit_contacto_nombre">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Parentesco</label>
                            <input type="text" class="form-control" name="contacto_parentesco" id="edit_contacto_parentesco">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Celular Contacto</label>
                            <input type="text" class="form-control" name="contacto_celular" id="edit_contacto_cel">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Dirección Contacto</label>
                            <input type="text" class="form-control" name="contacto_direccion" id="edit_contacto_dir">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCambiosPersona()">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        const inputBusqueda = document.getElementById('inputBusqueda');
        const selectPrograma = document.getElementById('selectPrograma');
        const filas = document.querySelectorAll('tbody tr');
        const checkboxes = document.querySelectorAll('.chkInscripcion');
        const chkTodos = document.getElementById('chkTodos'); // El checkbox del header
        
        const btnConfirmar = document.getElementById('btnConfirmar');
        const btnActualizar = document.getElementById('btnActualizar');
        
           // --- LÓGICA DE FILTRADO ---
        function filtrarTabla() {
            const texto = inputBusqueda.value.toLowerCase();
            const programa = selectPrograma.value.toLowerCase();
            let visibles = 0;
    
            // Desmarcar el "Seleccionar todos" al filtrar para evitar errores
            chkTodos.checked = false;
    
            filas.forEach(fila => {
                if (fila.cells.length <= 1) return;
    
                const contenido = fila.textContent.toLowerCase();
                const colPrograma = fila.cells[3].textContent.toLowerCase();
    
                const coincideBusqueda = contenido.includes(texto);
                const coincidePrograma = programa === "" || colPrograma.includes(programa);
    
                if (coincideBusqueda && coincidePrograma) {
                    fila.style.display = "";
                    visibles++;
                } else {
                    fila.style.display = "none";
                    // Desmarcar los checkboxes de las filas que se ocultan
                    const chk = fila.querySelector('.chkInscripcion');
                    if (chk) chk.checked = false;
                }
            });
            
            document.getElementById('contadorRegistros').textContent = `Mostrando: ${visibles}`;
            actualizarEstadoBotones();
        }
    
        // --- SELECCIONAR TODOS (SOLO VISIBLES) ---
        chkTodos.addEventListener('change', function() {
            const isChecked = this.checked;
            
            filas.forEach(fila => {
                // Solo actuar sobre filas que no están ocultas por el filtro
                if (fila.style.display !== "none" && fila.cells.length > 1) {
                    const chk = fila.querySelector('.chkInscripcion');
                    if (chk) chk.checked = isChecked;
                }
            });
            actualizarEstadoBotones();
        });
    
        // --- LÓGICA DE BOTONES Y SELECCIÓN INDIVIDUAL ---
        function actualizarEstadoBotones() {
            const seleccionados = document.querySelectorAll('.chkInscripcion:checked');
            const cantidad = seleccionados.length;
    
            btnConfirmar.disabled = (cantidad !== 1);
            btnActualizar.disabled = (cantidad !== 1);
        }
    
        checkboxes.forEach(chk => {
            chk.addEventListener('change', () => {
                actualizarEstadoBotones();
                // Si uno se desmarca, quitar el check de "Todos"
                if (!chk.checked) chkTodos.checked = false;
            });
        });
    
        inputBusqueda.addEventListener('keyup', filtrarTabla);
        selectPrograma.addEventListener('change', filtrarTabla);
    });
    
    function actualizarDatos() {
    const seleccionados = document.querySelectorAll('.chkInscripcion:checked');
    
    if (seleccionados.length === 0) return;
    
    if (seleccionados.length > 1) {
        Swal.fire('Atención', 'Por favor selecciona solo un registro para editar a la vez.', 'info');
        return;
    }

    const inscripcionId = seleccionados[0].value;

    fetch(`roles/admin/admisiones/matriculas/get_persona.php?id=${inscripcionId}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const p = res.data;
                
                // Llenar datos básicos
                document.getElementById('edit_id').value = p.id;
                document.getElementById('edit_nombre').value = p.nombres_completos;
                document.getElementById('edit_tipo_doc').value = p.tipo_documento;
                document.getElementById('edit_doc').value = p.numero_documento;
                document.getElementById('edit_correo').value = p.correo;
                document.getElementById('edit_tel').value = p.telefono;

                // Llenar campos nuevos
                document.getElementById('edit_direccion').value = p.direccion || '';
                document.getElementById('edit_estado_civil').value = p.estado_civil || 'Soltero/a';
                document.getElementById('edit_sisben').value = p.sisben || '';
                document.getElementById('edit_eps').value = p.eps || '';
                
                // Contacto de emergencia
                document.getElementById('edit_contacto_nombre').value = p.contacto_emergencia_nombre || '';
                document.getElementById('edit_contacto_parentesco').value = p.contacto_emergencia_parentesco || '';
                document.getElementById('edit_contacto_cel').value = p.contacto_emergencia_telefono || '';
                document.getElementById('edit_contacto_dir').value = p.contacto_emergencia_direccion || '';

                // Manejo de la foto (Previsualización)
                const imgPreview = document.getElementById('preview_foto');
                if (p.foto) {
                    // Asegúrate que la ruta sea correcta según tu estructura
                    imgPreview.src = `assets/img/perfiles/${p.foto}`;
                } else {
                    imgPreview.src = 'assets/img/default-user.png';
                }

                // Mostrar modal
                const modalEl = document.getElementById('modalEditarPersona');
                const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                myModal.show();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(err => {
            console.error("Error al obtener datos:", err);
            Swal.fire('Error', 'No se pudo obtener la información del servidor.', 'error');
        });
}
    
    function previewImage(input) {
    if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_foto').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function guardarCambiosPersona() {
    const form = document.getElementById('formEditarPersona');
    const formData = new FormData(form);

    Swal.fire({
        title: '¿Guardar cambios?',
        text: "Se actualizará la información personal del aspirante.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('roles/admin/admisiones/matriculas/update_persona.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Si la respuesta no es un JSON, esto nos mostrará el error real en la consola
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        console.error("Respuesta no válida del servidor:", text);
                        throw new Error("El servidor respondió algo inesperado (revisa la consola)");
                    }
                });
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error de Sistema', error.message, 'error');
            });
        }
    });
}
    
    function confirmarMatricula() {
    const seleccionados = Array.from(document.querySelectorAll('.chkInscripcion:checked'));
    const idsParaEnviar = seleccionados.map(chk => chk.value);
    if (idsParaEnviar.length === 0) return;

    let totalPagar = 0;
    seleccionados.forEach(chk => {
        const fila = chk.closest('tr');
        const costoTexto = fila.cells[4].innerText.replace(/[^0-9]/g, '');
        totalPagar += parseInt(costoTexto);
    });

    const totalFormateado = new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', maximumFractionDigits: 0
    }).format(totalPagar);

    Swal.fire({
        title: 'Confirmar Matrícula Académica',
        html: `
            <div class="text-start mt-3">
                <p class="mb-2">Vas a legalizar <b>${idsParaEnviar.length}</b> estudiante(s).</p>
                <div class="p-3 mb-3 text-center" style="background: #f8f9fa; border-radius: 10px; border: 1px dashed #2ecc71;">
                    <small class="text-muted d-block">TOTAL A RECAUDAR</small>
                    <h3 class="text-success mb-0" id="total_a_pagar_val" data-valor="${totalPagar}">${totalFormateado}</h3>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">MÉTODO DE PAGO</label>
                    <select id="swal_metodo_pago" class="form-select" onchange="toggleDividido(this.value)">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Dividido">Dividido (Efectivo + Transf.)</option>
                    </select>
                </div>

                <div id="seccion_dividido" style="display:none;" class="p-3 mb-3 border rounded bg-light">
                    <div class="row">
                        <div class="col-6">
                            <label class="small fw-bold">Efectivo</label>
                            <input type="number" id="monto_efectivo" class="form-control" placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Transferencia</label>
                            <input type="number" id="monto_transferencia" class="form-control" placeholder="0">
                        </div>
                    </div>
                    <small class="text-danger d-none" id="error_suma">La suma no coincide con el total</small>
                </div>

                <div class="mb-2 d-none">
                    <label class="form-label fw-bold small">REFERENCIA / RECIBO</label>
                    <input type="text" id="swal_referencia" class="form-control" placeholder="Núm. de comprobante">
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Procesar Matrícula',
        didOpen: () => {
            // Función global para el select dentro del modal
            window.toggleDividido = (val) => {
                document.getElementById('seccion_dividido').style.display = (val === 'Dividido') ? 'block' : 'none';
            };
        },
        preConfirm: () => {
            const metodo = document.getElementById('swal_metodo_pago').value;
            const ref = document.getElementById('swal_referencia').value;
            let efec = 0;
            let trans = 0;

            if (metodo === 'Dividido') {
                efec = parseFloat(document.getElementById('monto_efectivo').value) || 0;
                trans = parseFloat(document.getElementById('monto_transferencia').value) || 0;
                
                if ((efec + trans) !== totalPagar) {
                    Swal.showValidationMessage(`La suma ($${efec + trans}) debe ser igual al total ($${totalPagar})`);
                    return false;
                }
            } else if (metodo === 'Efectivo') {
                efec = totalPagar;
            } else {
                trans = totalPagar;
            }

            return { metodo_pago: metodo, referencia: ref, monto_efectivo: efec, monto_transferencia: trans };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const params = new URLSearchParams();
            params.append('ids', JSON.stringify(idsParaEnviar));
            params.append('metodo_pago', result.value.metodo_pago);
            params.append('referencia', result.value.referencia);
            params.append('monto_efectivo', result.value.monto_efectivo);
            params.append('monto_transferencia', result.value.monto_transferencia);

            fetch('roles/admin/admisiones/matriculas/procesar_confirmar_matricula.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}
    
    
</script>