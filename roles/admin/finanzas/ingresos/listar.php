<?php
session_start();
require_once __DIR__ . "/../../../../config.php"; // Ajusta la ruta según tu proyecto

// Consulta de ingresos
try {
    $sql = "SELECT i.*, p.nombres_completos AS cliente 
            FROM ingresos i 
            LEFT JOIN personas p ON i.persona_id = p.id 
            ORDER BY i.fecha_ingreso DESC";
    $stmt = $pdo->query($sql);
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
                            <th>Concepto</th>
                            <th>Cliente / Tercero</th>
                            <th>Método</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ingresos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>No hay ingresos registrados en este momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ingresos as $ing): ?>
                                <tr>
                                    <td class="small"><?= date('d/m/Y h:i A', strtotime($ing['fecha_ingreso'])) ?></td>
                                    <td>
                                        <span class="fw-bold d-block"><?= htmlspecialchars($ing['concepto']) ?></span>
                                        <small class="text-muted">Ref: <?= $ing['referencia'] ?></small>
                                    </td>
                                    <td><?= $ing['cliente'] ?? '<span class="text-muted fst-italic">Venta Directa</span>' ?></td>
                                    <td>
                                        <?php if ($ing['metodo_pago'] === 'Dividido'): ?>
                                            <span class="badge bg-info text-dark">
                                                E: $<?= number_format($ing['monto_efectivo'], 0) ?> | T: $<?= number_format($ing['monto_transferencia'], 0) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= $ing['metodo_pago'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        $<?= number_format($ing['monto'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $ing['estado'] === 'Activo' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $ing['estado'] ?>
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
                        <label class="form-label fw-bold small">CLIENTE / TERCERO</label>
                        <input type="text" name="cliente_nombre" class="form-control" placeholder="Nombre de quien paga">
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
</script>