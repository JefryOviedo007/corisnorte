<?php
require_once __DIR__ . "/../../../../config.php";

$rol     = $_SESSION['rol']     ?? '';
$sede_id = $_SESSION['sede_id'] ?? null;

// Si es Admin, cargamos todas las sedes para el filtro
$sedes = [];
if ($rol === 'Admin') {
    $sedes = $pdo->query("SELECT id, nombre FROM sedes WHERE estado = 'Activa' ORDER BY nombre ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);
}

// Sede seleccionada en el filtro (solo Admin puede cambiarla)
$sede_filtro = ($rol === 'Admin')
    ? (!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null)
    : (int)$sede_id;

// Parámetros GET sin sede_id (para preservar la URL al filtrar)
$params_base = $_GET;
unset($params_base['sede_id']);
$url_base    = '?' . http_build_query($params_base);
$url_limpiar = $url_base;

try {
    if ($rol === 'Admin' && !$sede_filtro) {
        // Admin sin filtro → ve todos los ingresos de todas las sedes
        $sql = "SELECT i.*, 
                       p.nombres_completos AS cliente,
                       s.nombre            AS sede_nombre
                FROM ingresos i
                LEFT JOIN personas p ON i.persona_id = p.id
                LEFT JOIN usuarios u ON i.usuario_id = u.id
                LEFT JOIN sedes s    ON u.sede_id     = s.id
                ORDER BY i.fecha_ingreso DESC";
        $stmt = $pdo->query($sql);
    } else {
        // Admin con sede seleccionada, o Coordinador/Secretaria → solo su sede
        $sql = "SELECT i.*, 
                       p.nombres_completos AS cliente,
                       s.nombre            AS sede_nombre
                FROM ingresos i
                LEFT JOIN personas p ON i.persona_id = p.id
                LEFT JOIN usuarios u ON i.usuario_id = u.id
                LEFT JOIN sedes s    ON u.sede_id     = s.id
                WHERE u.sede_id = ?
                ORDER BY i.fecha_ingreso DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sede_filtro]);
    }

    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar ingresos: " . $e->getMessage());
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
        

        /* Estilos para Badges Modernos (Soft Colors) */
        .bg-success-subtle { background-color: #d1e7dd !important; }
        .bg-danger-subtle { background-color: #f8d7da !important; }
        .bg-warning-subtle { background-color: #fff3cd !important; }
        
        .table thead th {
            background: #2d3748 !important; /* Gris oscuro profesional */
            font-size: 0.75rem !important;
            padding: 10px !important;
        }
    
        .table td {
            padding: 8px 4px !important;
            border-bottom: 1px solid #f0f0f0;
        }
    
        /* Efecto hover suave */
        .table-hover tbody tr:hover {
            background-color: #f8fafc !important;
        }
    
        /* Ajuste para el icono del ojo */
        .btn-link:hover {
            transform: scale(1.2);
            transition: 0.2s;
        }
</style>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>
      <i class="bi bi-cash-stack"></i> Ingresos
    </h3>

    <div class="d-flex gap-2">
        <?php if ($rol === 'Admin'): ?>
        <form method="GET" action="" class="d-flex align-items-center gap-2 mb-0">

            <?php foreach ($params_base as $key => $value): ?>
                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endforeach; ?>

            <label class="fw-bold small mb-0 text-nowrap">
                <i class="bi bi-building me-1"></i> Sede:
            </label>
            <select name="sede_id" class="form-select form-select-sm" style="max-width:220px;" onchange="this.form.submit()">
                <option value="">— Todas las sedes —</option>
                <?php foreach ($sedes as $sede): ?>
                    <option value="<?= $sede['id'] ?>" <?= $sede_filtro == $sede['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sede['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($sede_filtro): ?>
                <a href="<?= $url_limpiar ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            <?php endif; ?>

        </form>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="nuevoIngreso()">
            <i class="bi bi-plus-circle"></i>Nuevo Ingreso
        </button>
    </div>
  </div>

    <div class="card p-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_ingresos" class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Fecha</th>
                            <?php if ($rol === 'Admin'): ?>
                                <th>Sede</th>
                            <?php endif; ?>
                            <th>Concepto</th>
                            <th>Estudiante / Tercero</th>
                            <th>Método</th>
                            <th class="text-end">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ingresos)): ?>
                            <tr>
                                <td colspan="<?= $rol === 'Admin' ? 6 : 5 ?>" class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle me-2"></i>No hay ingresos registrados en este momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ingresos as $ing): ?>
                                <tr>
                                    <td class="small"><?= date('d/m/Y h:i A', strtotime($ing['fecha_ingreso'])) ?></td>

                                    <?php if ($rol === 'Admin'): ?>
                                        <td class="small">
                                            <?php if (!empty($ing['sede_nombre'])): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($ing['sede_nombre']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Sin sede</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>

                                    <td>
                                        <span class="fw-bold d-block"><?= htmlspecialchars($ing['concepto']) ?></span>
                                        <?php if (!empty($ing['referencia'])): ?>
                                            <small class="text-muted">Ref: <?= htmlspecialchars($ing['referencia']) ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php
                                            $nombreMostrar = $ing['cliente'] ?? $ing['observaciones'] ?? null;
                                            if ($nombreMostrar):
                                                $nombre = htmlspecialchars(mb_convert_case(trim($nombreMostrar), MB_CASE_TITLE, 'UTF-8'));
                                        ?>
                                                <span class="fw-semibold"><?= $nombre ?></span>
                                                <?php if ($ing['persona_id']): ?>
                                                    <i class="bi bi-patch-check-fill text-primary ms-1" title="Estudiante registrado" style="font-size:.85rem;"></i>
                                                <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Sin especificar</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($ing['metodo_pago'] === 'Dividido'): ?>
                                            <span class="badge bg-info text-dark">
                                                E: $<?= number_format($ing['monto_efectivo'], 0, ',', '.') ?> | T: $<?= number_format($ing['monto_transferencia'], 0, ',', '.') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= $ing['metodo_pago'] ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end fw-bold text-success">
                                        $<?= number_format($ing['monto'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoIngreso" tabindex="-1" aria-labelledby="modalNuevoIngresoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalNuevoIngresoLabel"><i class="fas fa-plus-circle me-2"></i>Nuevo Ingreso General</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoIngreso">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">CONCEPTO</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Venta de Uniforme" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">ESTUDIANTE / TERCERO</label>
                        
                        <!-- Input visible con autocomplete -->
                        <input 
                            type="text" 
                            id="ing_cliente_input" 
                            class="form-control" 
                            placeholder="Buscar por nombre o documento..."
                            autocomplete="off"
                        >
                        
                        <!-- Campo oculto que guarda el persona_id si se selecciona -->
                        <input type="hidden" name="persona_id" id="ing_persona_id">
                        
                        <!-- Campo con el nombre libre (por si no selecciona de la lista) -->
                        <input type="hidden" name="cliente_nombre" id="ing_cliente_nombre">

                        <!-- Dropdown de sugerencias -->
                        <ul id="ing_sugerencias" class="list-group mt-1 shadow-sm" style="display:none; position:absolute; z-index:9999; width:100%; max-height:220px; overflow-y:auto;"></ul>

                        <!-- Chip de persona seleccionada -->
                        <div id="ing_persona_seleccionada" class="mt-2" style="display:none;">
                            <span class="badge bg-primary fs-6 fw-normal px-3 py-2">
                                <i class="bi bi-person-check me-1"></i>
                                <span id="ing_persona_nombre_label"></span>
                                <button type="button" class="btn-close btn-close-white ms-2" style="font-size:0.6rem;" onclick="limpiarPersonaIngreso()"></button>
                            </span>
                        </div>
                    </div>

                    <div id="seccion_monto_simple" class="mb-3 p-3 border rounded bg-light text-center">
                        <label class="form-label fw-bold small d-block text-primary">MONTO TOTAL</label>
                        <input type="number" name="monto_total" id="ing_monto_total" class="form-control form-control-lg text-center fw-bold" placeholder="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">MÉTODO DE PAGO</label>
                        <select name="metodo_pago" id="ing_metodo" class="form-select" onchange="toggleMetodoIngreso(this.value)">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Dividido">Dividido (Efectivo + Transf.)</option>
                        </select>
                    </div>

                    <div id="seccion_monto_dividido" style="display:none;" class="p-3 mb-3 border rounded bg-warning-subtle">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small fw-bold">Efectivo</label>
                                <input type="number" name="monto_efectivo" id="ing_efectivo" class="form-control" value="0">
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold">Transferencia</label>
                                <input type="number" name="monto_transferencia" id="ing_transferencia" class="form-control" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">REFERENCIA / OBSERVACIONES</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    function nuevoIngreso() {
    // Inicializamos el modal de Bootstrap por su ID
    const myModal = new bootstrap.Modal(document.getElementById('modalNuevoIngreso'));
    
    // Opcional: Limpiar el formulario cada vez que se abre para que no queden datos viejos
    document.getElementById('formNuevoIngreso').reset();
    
    // Aseguramos que la vista vuelva al estado por defecto (ocultando campos divididos)
    toggleMetodoIngreso('Efectivo'); 
    
    // Mostrar el modal
    myModal.show();
}


    function toggleMetodoIngreso(valor) {
    const simple = document.getElementById('seccion_monto_simple');
    const dividido = document.getElementById('seccion_monto_dividido');
    
    if (valor === 'Dividido') {
        simple.style.display = 'none';
        dividido.style.display = 'block';
    } else {
        simple.style.display = 'block';
        dividido.style.display = 'none';
    }
}

document.getElementById('formNuevoIngreso').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    fetch('roles/admin/finanzas/ingresos/crear.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Cerrar modal de Bootstrap
            bootstrap.Modal.getInstance(document.getElementById('modalNuevoIngreso')).hide();
            this.reset();
            
            // Alerta de éxito SweetAlert
            Swal.fire({
                icon: 'success',
                title: '¡Registrado!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload(); 
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
});

// ── Autocomplete de personas en Ingresos ──────────────────────────────────
let timeoutBusqueda = null;

document.getElementById('ing_cliente_input').addEventListener('input', function () {
    const q = this.value.trim();
    const sugerencias = document.getElementById('ing_sugerencias');

    // Si borra el texto, limpiamos persona_id pero no bloqueamos
    document.getElementById('ing_persona_id').value = '';
    document.getElementById('ing_cliente_nombre').value = q;

    clearTimeout(timeoutBusqueda);

    if (q.length < 2) {
        sugerencias.style.display = 'none';
        sugerencias.innerHTML = '';
        return;
    }

    timeoutBusqueda = setTimeout(() => {
        fetch(`roles/admin/finanzas/ingresos/buscar_persona.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(personas => {
                sugerencias.innerHTML = '';

                if (personas.length === 0) {
                    sugerencias.innerHTML = '<li class="list-group-item text-muted small fst-italic">Sin coincidencias — se guardará como texto libre</li>';
                    sugerencias.style.display = 'block';
                    return;
                }

                personas.forEach(p => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                    li.style.cursor = 'pointer';
                    li.innerHTML = `
                        <span><i class="bi bi-person me-2 text-primary"></i>${p.nombres_completos}</span>
                        <small class="text-muted">${p.tipo_documento}: ${p.numero_documento}</small>
                    `;
                    li.addEventListener('click', () => seleccionarPersonaIngreso(p));
                    sugerencias.appendChild(li);
                });

                sugerencias.style.display = 'block';
            })
            .catch(() => { sugerencias.style.display = 'none'; });
    }, 300); // debounce 300ms
});

function seleccionarPersonaIngreso(persona) {
    // Guardamos los valores en los campos ocultos
    document.getElementById('ing_persona_id').value       = persona.id;
    document.getElementById('ing_cliente_nombre').value   = persona.nombres_completos;

    // Mostramos el chip y ocultamos el input
    document.getElementById('ing_persona_nombre_label').textContent = persona.nombres_completos;
    document.getElementById('ing_persona_seleccionada').style.display = 'block';
    document.getElementById('ing_cliente_input').style.display        = 'none';

    // Ocultamos sugerencias
    document.getElementById('ing_sugerencias').style.display = 'none';
    document.getElementById('ing_sugerencias').innerHTML     = '';
}

function limpiarPersonaIngreso() {
    document.getElementById('ing_persona_id').value             = '';
    document.getElementById('ing_cliente_nombre').value         = '';
    document.getElementById('ing_persona_nombre_label').textContent = '';
    document.getElementById('ing_persona_seleccionada').style.display = 'none';
    document.getElementById('ing_cliente_input').style.display        = 'block';
    document.getElementById('ing_cliente_input').value                = '';
    document.getElementById('ing_cliente_input').focus();
}

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', function (e) {
    const input = document.getElementById('ing_cliente_input');
    const lista = document.getElementById('ing_sugerencias');
    if (!input.contains(e.target) && !lista.contains(e.target)) {
        lista.style.display = 'none';
    }
});

// Al resetear el formulario, limpiar también el autocomplete
document.getElementById('formNuevoIngreso').addEventListener('reset', function () {
    limpiarPersonaIngreso();
});
</script>