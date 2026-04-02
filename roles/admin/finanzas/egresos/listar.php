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
        // Admin sin filtro → ve todos los egresos de todas las sedes
        $sql = "SELECT e.*, 
                       p.nombres_completos AS proveedor,
                       s.nombre            AS sede_nombre
                FROM egresos e
                LEFT JOIN personas p ON e.persona_id = p.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                LEFT JOIN sedes s    ON u.sede_id     = s.id
                ORDER BY e.fecha_egreso DESC";
        $stmt = $pdo->query($sql);
    } else {
        // Admin con sede seleccionada, o Coordinador/Secretaria → solo su sede
        $sql = "SELECT e.*, 
                       p.nombres_completos AS proveedor,
                       s.nombre            AS sede_nombre
                FROM egresos e
                LEFT JOIN personas p ON e.persona_id = p.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                LEFT JOIN sedes s    ON u.sede_id     = s.id
                WHERE u.sede_id = ?
                ORDER BY e.fecha_egreso DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sede_filtro]);
    }

    $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar egresos: " . $e->getMessage());
}
?>

<style>
    :root {
        --azul: #1f3c88;
        --azul-claro: #3f6ad8;
        --rojo: #e63946;
        --fondo: #f4f6f9;
        --gris: #e0e0e0;
        --blanco: #ffffff;
        --texto: #2b2b2b;
    }
    
    .table { border-radius: 12px; overflow: hidden; width: max-content; min-width: 100%; }
    /* Encabezado en Rojo para Egresos */
    .table thead th {
        background: var(--rojo) !important;
        color: white !important;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 10px !important;
    }
    .table td { vertical-align: middle; padding: 8px 4px !important; border-bottom: 1px solid #f0f0f0; }
    .table-hover tbody tr:hover { background-color: rgba(230, 57, 70, 0.05) !important; }
    
    .modal-header { background: linear-gradient(90deg, var(--rojo), #ff6b6b); color: white; }
    .badge { font-size: 0.8rem; padding: 6px 10px; border-radius: 8px; }
    .bg-warning-subtle { background-color: #fff3cd !important; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-danger">
            <i class="bi bi-cart-dash"></i> Egresos / Gastos
        </h3>

        <div class="d-flex gap-2 align-items-center">
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

            <button class="btn btn-danger" onclick="nuevoEgreso()">
                <i class="bi bi-plus-circle"></i> Nuevo Egreso
            </button>
        </div>
    </div>

    <div class="card p-3 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_egresos" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <?php if ($rol === 'Admin'): ?>
                                <th>Sede</th>
                            <?php endif; ?>
                            <th>Concepto / Gasto</th>
                            <th>Proveedor / Colaborador</th>
                            <th>Método</th>
                            <th class="text-end">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($egresos)): ?>
                            <tr>
                                <td colspan="<?= $rol === 'Admin' ? 7 : 6 ?>" class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle me-2"></i>No hay egresos registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($egresos as $eg): ?>
                                <tr>
                                    <td class="small"><?= date('d/m/Y h:i A', strtotime($eg['fecha_egreso'])) ?></td>

                                    <?php if ($rol === 'Admin'): ?>
                                        <td class="small">
                                            <?php if (!empty($eg['sede_nombre'])): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($eg['sede_nombre']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Sin usuario
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>

                                    <td>
                                        <span class="fw-bold d-block text-uppercase small"><?= htmlspecialchars($eg['concepto']) ?></span>
                                        <?php if (!empty($eg['referencia'])): ?>
                                            <small class="text-muted">Ref: <?= htmlspecialchars($eg['referencia']) ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php
                                            $nombreMostrar = $eg['proveedor'] ?? $eg['observaciones'] ?? null;
                                            if ($nombreMostrar):
                                                $nombre = htmlspecialchars(mb_convert_case(trim($nombreMostrar), MB_CASE_TITLE, 'UTF-8'));
                                        ?>
                                                <span class="fw-semibold"><?= $nombre ?></span>
                                                <?php if ($eg['persona_id']): ?>
                                                    <i class="bi bi-patch-check-fill text-danger ms-1" title="Persona registrada" style="font-size:.85rem;"></i>
                                                <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Gasto General</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($eg['metodo_pago'] === 'Dividido'): ?>
                                            <span class="badge bg-info text-dark">
                                                E: $<?= number_format($eg['monto_efectivo'], 0, ',', '.') ?> | T: $<?= number_format($eg['monto_transferencia'], 0, ',', '.') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= $eg['metodo_pago'] ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end fw-bold text-danger">
                                        $<?= number_format($eg['monto'], 0, ',', '.') ?>
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

<div class="modal fade" id="modalNuevoEgreso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-minus-circle me-2"></i>Registrar Nuevo Egreso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevoEgreso">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">CONCEPTO DEL GASTO</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Pago de Arriendo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">PAGADO A (PROVEEDOR/COLABORADOR)</label>
                        <input type="text" name="proveedor_nombre" class="form-control" placeholder="Nombre de quien recibe el dinero">
                    </div>

                    <div id="sec_monto_simple_eg" class="mb-3 p-3 border rounded bg-light text-center">
                        <label class="form-label fw-bold small d-block text-danger">MONTO TOTAL</label>
                        <input type="number" name="monto_total" class="form-control form-control-lg text-center fw-bold text-danger" placeholder="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">MÉTODO DE PAGO</label>
                        <select name="metodo_pago" id="eg_metodo" class="form-select" onchange="toggleMetodoEgreso(this.value)">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Dividido">Dividido (Efectivo + Transf.)</option>
                        </select>
                    </div>

                    <div id="sec_monto_dividido_eg" style="display:none;" class="p-3 mb-3 border rounded bg-warning-subtle">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small fw-bold">Efectivo</label>
                                <input type="number" name="monto_efectivo" class="form-control" value="0">
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold">Transferencia</label>
                                <input type="number" name="monto_transferencia" class="form-control" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">COMPROBANTE / NOTAS</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Guardar Egreso</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function nuevoEgreso() {
    const modal = new bootstrap.Modal(document.getElementById('modalNuevoEgreso'));
    document.getElementById('formNuevoEgreso').reset();
    toggleMetodoEgreso('Efectivo');
    modal.show();
}

function toggleMetodoEgreso(valor) {
    const simple = document.getElementById('sec_monto_simple_eg');
    const dividido = document.getElementById('sec_monto_dividido_eg');
    simple.style.display = (valor === 'Dividido') ? 'none' : 'block';
    dividido.style.display = (valor === 'Dividido') ? 'block' : 'none';
}

document.getElementById('formNuevoEgreso').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('roles/admin/finanzas/egresos/crear.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('modalNuevoEgreso')).hide();
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message, timer: 1500, showConfirmButton: false })
            .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});
</script>