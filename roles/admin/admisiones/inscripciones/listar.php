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
    WHERE i.estado IN ('Preinscrito', 'Inscrito')
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

        body { font-family: 'Montserrat', sans-serif; background-color: var(--fondo); margin-top: 70px; }
        
        .container {margin-top:10px}
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
    
    
</style>

<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>
      <i class="bi bi-person-check"></i> Gestión de Inscripciones
    </h3>

    <div>
      <button class="btn btn-success" id="btnConfirmar" disabled onclick="confirmarInscripcion()">
        <i class="bi bi-check-circle"></i> Confirmar
      </button>    
      
      <button class="btn btn-primary" id="btnConvocar" disabled onclick="convocarMatricula()">
        <i class="bi bi-megaphone"></i> Convocar a Matrícula
      </button>
        
      <button class="btn btn-danger" id="btnAnular" disabled onclick="anularInscripcion()">
        <i class="bi bi-x-circle"></i> Anular
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
                No hay inscripciones registradas
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
                <td><?= $i['jornada'] ?></td>
        
                <?php if ($rol === 'Admin'): ?>
                  <td><?= htmlspecialchars($i['sede']) ?></td>
                <?php endif; ?>
        
                <td>
                  <small><?= date('d/m/Y', strtotime($i['fecha_inscripcion'])) ?></small>
                </td>

                <td>
                  <span class="badge bg-<?= 
                    $i['estado'] === 'Inscrito' ? 'success' : 
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
        const btnConvocar = document.getElementById('btnConvocar');
        const btnAnular = document.getElementById('btnAnular');
    
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
    
            btnConfirmar.disabled = (cantidad === 0);
            btnConvocar.disabled = (cantidad === 0);
            btnAnular.disabled = (cantidad === 0);
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

    
    // Funciones para los botones (Placeholders)
    function confirmarInscripcion() {
        // Obtenemos todos los checkboxes marcados
        const checkboxesSeleccionados = Array.from(document.querySelectorAll('.chkInscripcion:checked'));
        
        if (checkboxesSeleccionados.length === 0) return;
    
        // 1. Verificamos si hay al menos uno que ya esté en estado 'Inscrito'
        const yaProcesados = checkboxesSeleccionados.filter(chk => chk.dataset.estado === 'Inscrito');
    
        if (yaProcesados.length > 0) {
            Swal.fire({
                title: 'Selección Inválida',
                text: `Has seleccionado ${yaProcesados.length} registro(s) que ya aparecen como "Inscrito". Por favor, verifica tu selección y marca solo aquellos en estado Preinscrito.`,
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return; // Detenemos la ejecución aquí
        }
    
        // 2. Si todos están correctos (Preinscritos), procedemos con la confirmación
        const idsParaEnviar = checkboxesSeleccionados.map(chk => chk.value);
    
        Swal.fire({
            title: '¿Confirmar inscripciones?',
            text: `Se cambiará al estado "Inscrito" a ${idsParaEnviar.length} aspirante(s) y se les enviará un correo de notificación.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar modal de carga
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Enviando correos y actualizando registros.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
    
                // Envío de datos al servidor
                fetch('roles/admin/admisiones/inscripciones/procesar_confirmacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ids=${JSON.stringify(idsParaEnviar)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: '¡Logrado!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload(); 
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
                });
            }
        });
    }
    
    
    function convocarMatricula() {
        const checkboxesSeleccionados = Array.from(document.querySelectorAll('.chkInscripcion:checked'));
        
        if (checkboxesSeleccionados.length === 0) return;
    
        // 1. Validar que solo se convoquen registros en estado 'Inscrito'
        const noInscritos = checkboxesSeleccionados.filter(chk => chk.dataset.estado !== 'Inscrito');
    
        if (noInscritos.length > 0) {
            Swal.fire({
                title: 'Acción no Permitida',
                text: `Has seleccionado ${noInscritos.length} registro(s) que no están en estado "Inscrito". Solo puedes convocar a matrícula a quienes ya han sido confirmados.`,
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    
        const idsParaEnviar = checkboxesSeleccionados.map(chk => chk.value);
    
        Swal.fire({
            title: '¿Convocar a Matrícula?',
            text: `Se enviará la citación formal a ${idsParaEnviar.length} estudiante(s) con la información de contacto de su sede.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0D09A4',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, convocar ahora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando Citaciones...',
                    text: 'Generando correos informativos por sede.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
    
                fetch('roles/admin/admisiones/inscripciones/procesar_convocatoria.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ids=${JSON.stringify(idsParaEnviar)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('¡Convocatoria Enviada!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                });
            }
        });
    }
    
    
    
    function anularInscripcion() {
        const seleccionados = Array.from(document.querySelectorAll('.chkInscripcion:checked'));
        
        if (seleccionados.length === 0) return;
    
        // Validar que no se intenten anular registros ya anulados
        const yaAnulados = seleccionados.filter(chk => chk.dataset.estado === 'Anulado');
    
        if (yaAnulados.length > 0) {
            Swal.fire({
                title: 'Acción inválida',
                text: `Hay ${yaAnulados.length} registro(s) que ya están anulados.`,
                icon: 'warning'
            });
            return;
        }
    
        const idsParaEnviar = seleccionados.map(chk => chk.value);
    
        Swal.fire({
            title: '¿Anular inscripciones?',
            text: `Se anularán ${idsParaEnviar.length} registro(s). Esta acción notificará al aspirante y detendrá su proceso.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Actualizando estado y enviando notificaciones.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
    
                fetch('roles/admin/admisiones/inscripciones/procesar_anulacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ids=${JSON.stringify(idsParaEnviar)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('¡Anulado!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Error en el servidor', 'error');
                });
            }
        });
    }
    
    
</script>