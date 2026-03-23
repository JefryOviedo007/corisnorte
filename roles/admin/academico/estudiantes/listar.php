<?php
require_once __DIR__ . "/../../../../config.php"; 

try {
    // Consulta de la tabla personas (Estudiantes)
    $sql = "SELECT * FROM personas ORDER BY fecha_creacion DESC";
    $stmt = $pdo->query($sql);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar estudiantes: " . $e->getMessage());
}
?>

<style>
    :root {
        --azul: #1f3c88;
        --azul-claro: #3f6ad8;
        --fondo: #f4f6f9;
        --texto: #2b2b2b;
        --gris-borde: #e0e0e0;
    }
    
    .table { border-radius: 12px; overflow: hidden; min-width: 100%; border: 1px solid var(--gris-borde); }
    .table thead th {
        background: var(--azul) !important;
        color: white !important;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 12px 10px !important;
    }
    .table td { vertical-align: middle; padding: 10px 8px !important; border-bottom: 1px solid #f0f0f0; font-size: 0.82rem; }
    .table-hover tbody tr:hover { background-color: rgba(31, 60, 136, 0.05) !important; }
    
    .student-photo {
        width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
        border: 2px solid #ddd;
    }

    .badge-estado { font-size: 0.7rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; }
    
    /* Contenedor de botones superior */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 15px;
        border-radius: 8px;
        transition: all 0.2s;
    }
</style>

<div class="container-fluid py-4">
    <div class="action-bar">
        <div>
            <h3 class="text-primary fw-bold mb-0">
                <i class="bi bi-people-fill me-2"></i>Gestión de Estudiantes
            </h3>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-dark btn-action" onclick="actualizarGrupo()">
                <i class="bi bi-arrow-repeat"></i> Actualizar Grupo
            </button>
            <button class="btn btn-outline-primary btn-action" onclick="editarSeleccionado()">
                <i class="bi bi-pencil-square"></i> Editar
            </button>
            <button class="btn btn-outline-danger btn-action" onclick="eliminarSeleccionado()">
                <i class="bi bi-trash"></i> Eliminar
            </button>
            <button class="btn btn-outline-success btn-action" onclick="importarDatos()">
                <i class="bi bi-file-earmark-arrow-up"></i> Importar
            </button>
            <button class="btn btn-outline-info btn-action" onclick="exportarExcel()">
                <i class="bi bi-file-earmark-excel"></i> Exportar
            </button>
            <button class="btn btn-outline-dark btn-action" onclick="imprimirListado()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th width="50">Foto</th>
                            <th>Documento</th>
                            <th>Nombres Completos</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>EPS / Sisbén</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No se encontraron estudiantes registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $est): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input check-estudiante" value="<?= $est['id'] ?>">
                                    </td>
                                    <td>
                                        <?php 
                                        $path_foto = "admin/configuracion/uploads/usuarios/" . $est['foto'];
                                        $full_server_path = __DIR__ . "/../../../../" . $path_foto;
                                        if (!empty($est['foto']) && file_exists($full_server_path)): ?>
                                            <img src="<?= $path_foto ?>" class="student-photo">
                                        <?php else: ?>
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($est['nombres_completos']) ?>&background=random&color=fff" class="student-photo">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= $est['numero_documento'] ?></span><br>
                                        <small class="text-muted"><?= $est['tipo_documento'] ?></small>
                                    </td>
                                    <td class="text-uppercase fw-semibold"><?= htmlspecialchars($est['nombres_completos']) ?></td>
                                    <td>
                                        <small class="d-block"><i class="bi bi-telephone text-muted"></i> <?= $est['telefono'] ?: 'N/A' ?></small>
                                        <small class="text-muted text-lowercase"><?= htmlspecialchars($est['correo'] ?: 'Sin correo') ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $bg = match($est['estado']) {
                                            'Matriculado' => 'bg-success',
                                            'Prospecto' => 'bg-info',
                                            'En formación' => 'bg-primary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge-estado <?= $bg ?> text-white"><?= $est['estado'] ?></span>
                                    </td>
                                    <td>
                                        <small class="d-block"><b>EPS:</b> <?= $est['eps'] ?: '-' ?></small>
                                        <small class="d-block"><b>SISBÉN:</b> <?= $est['sisben'] ?: '-' ?></small>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Función para obtener el ID seleccionado de los checkboxes
    function getSelectedId() {
        const selected = document.querySelector('.check-estudiante:checked');
        return selected ? selected.value : null;
    }

    function actualizarGrupo() {
        Swal.fire('Actualizar Grupo', 'Abriendo panel de gestión de grupos masivos...', 'info');
    }

    function editarSeleccionado() {
        const id = getSelectedId();
        if (!id) return Swal.fire('Atención', 'Selecciona un estudiante de la lista para editar.', 'warning');
        console.log("Editando ID:", id);
        // window.location.href = '?page=editar_estudiante&id=' + id;
    }

    function eliminarSeleccionado() {
        const id = getSelectedId();
        if (!id) return Swal.fire('Atención', 'Selecciona un estudiante para eliminar.', 'warning');
        
        Swal.fire({
            title: '¿Eliminar Estudiante?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e63946',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Eliminado', 'El registro ha sido borrado.', 'success');
            }
        });
    }

    function importarDatos() {
        Swal.fire('Importar', 'Cargue el archivo Excel de estudiantes...', 'question');
    }

    function exportarExcel() {
        Swal.fire('Exportar', 'Generando reporte de la tabla personas...', 'success');
    }

    function imprimirListado() {
        window.print();
    }
</script>