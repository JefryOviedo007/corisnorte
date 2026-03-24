<?php
require_once __DIR__ . "/../../../../config.php"; 

try {
    $sql = "SELECT * FROM personas ORDER BY fecha_creacion DESC";
    $stmt = $pdo->query($sql);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conocer estudiantes: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    .photo-preview-container {
        width: 100px; height: 100px; border-radius: 50%;
        margin: 0 auto 15px; overflow: hidden; border: 3px solid var(--azul-claro);
        position: relative; background: #eee;
    }
    .photo-preview-container img { width: 100%; height: 100%; object-fit: cover; }

    .badge-estado { font-size: 0.7rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; }
    .action-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .btn-action { display: inline-flex; align-items: center; gap: 7px; font-weight: 600; font-size: 0.85rem; padding: 8px 15px; border-radius: 8px; transition: all 0.2s; }
    .btn-action:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }
    tr.selected-row { background-color: rgba(63, 106, 216, 0.1) !important; }
</style>

<div class="container-fluid py-4">
    <div class="action-bar">
        <div>
            <h3 class="text-primary fw-bold mb-0">
                <i class="bi bi-people-fill me-2"></i>Gestión de Estudiantes
            </h3>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-dark btn-action" id="btnActualizar" onclick="actualizarGrupo()" disabled><i class="bi bi-arrow-repeat"></i> Actualizar Grupo</button>
            <button class="btn btn-outline-primary btn-action" id="btnEditar" onclick="editarSeleccionado()" disabled><i class="bi bi-pencil-square"></i> Editar</button>
            <button class="btn btn-outline-danger btn-action" id="btnEliminar" onclick="eliminarSeleccionado()" disabled><i class="bi bi-trash"></i> Eliminar</button>
            <button class="btn btn-outline-success btn-action" onclick="importarDatos()"><i class="bi bi-file-earmark-arrow-up"></i> Importar</button>
            <button class="btn btn-outline-info btn-action" onclick="exportarExcel()"><i class="bi bi-file-earmark-excel"></i> Exportar</button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaEstudiantes">
                    <thead>
                        <tr>
                            <th width="40" class="text-center"><input type="checkbox" class="form-check-input" id="checkAll" onclick="toggleAll(this)"></th>
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
                            <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron estudiantes.</td></tr>
                        <?php else: foreach ($estudiantes as $est): ?>
                            <tr id="row-<?= $est['id'] ?>">
                                <td class="text-center"><input type="checkbox" class="form-check-input check-estudiante" value="<?= $est['id'] ?>" onclick="checkRow(this)"></td>
                                <td>
                                    <?php 
                                    $path_foto = "roles/admin/academico/estudiantes/uploads/" . $est['foto'];
                                    if (!empty($est['foto']) && file_exists(__DIR__ . "/../../../../" . $path_foto)): ?>
                                        <img src="<?= $path_foto ?>" class="student-photo">
                                    <?php else: ?>
                                        <img src="roles/admin/configuracion/uploads/usuarios/user-default.webp" class="student-photo">
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-bold"><?= $est['numero_documento'] ?></span><br><small class="text-muted"><?= $est['tipo_documento'] ?></small></td>
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
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarEstudiante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarEstudiante" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="action" value="update">
                
                <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-12 text-center">
                            <div class="photo-preview-container">
                                <img id="preview_foto" src="" alt="Vista previa">
                            </div>
                            <label for="edit_foto_input" class="btn btn-sm btn-outline-primary mb-3">
                                <i class="bi bi-camera me-1"></i> Cambiar Foto
                            </label>
                            <input type="file" name="foto" id="edit_foto_input" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tipo Doc.</label>
                            <select class="form-select" name="tipo_documento" id="edit_tipo_doc" required>
                                <option value="CC">CC</option>
                                <option value="TI">TI</option>
                                <option value="CE">CE</option>
                                <option value="PASAPORTE">PASAPORTE</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">N° Documento</label>
                            <input type="text" class="form-control" name="numero_documento" id="edit_documento" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nombres Completos</label>
                            <input type="text" class="form-control" name="nombres_completos" id="edit_nombres" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="edit_telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Correo</label>
                            <input type="email" class="form-control" name="correo" id="edit_correo">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="edit_direccion">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">EPS</label>
                            <input type="text" class="form-control" name="eps" id="edit_eps">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Sisbén</label>
                            <input type="text" class="form-control" name="sisben" id="edit_sisben">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Estado Civil</label>
                            <input type="text" class="form-control" name="estado_civil" id="edit_estado_civil">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-danger">Estado</label>
                            <select class="form-select" name="estado" id="edit_estado">
                                <option value="Prospecto">Prospecto</option>
                                <option value="Matriculado">Matriculado</option>
                                <option value="En formación">En formación</option>
                                <option value="Egresado">Egresado</option>
                                <option value="Retirado">Retirado</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3 text-muted"><hr><small>INFORMACIÓN DE EMERGENCIA</small></div>
                        <div class="col-md-6"><label class="small">Contacto Emergencia</label><input type="text" class="form-control" name="contacto_nombre" id="edit_c_nombre"></div>
                        <div class="col-md-6"><label class="small">Parentesco</label><input type="text" class="form-control" name="contacto_parentesco" id="edit_c_parentesco"></div>
                        <div class="col-md-6"><label class="small">Tel. Emergencia</label><input type="text" class="form-control" name="contacto_telefono" id="edit_c_telefono"></div>
                        <div class="col-md-6"><label class="small">Dir. Emergencia</label><input type="text" class="form-control" name="contacto_direccion" id="edit_c_direccion"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalActualizarGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Asignar a Grupo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Selecciona el grupo al que deseas inscribir a los estudiantes seleccionados.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Grupo Destino</label>
                    <select class="form-select" id="select_grupo_destino" required>
                        <option value="">Cargando grupos...</option>
                    </select>
                </div>
                <div id="info_seleccion" class="alert alert-info py-2 small mb-0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" onclick="procesarActualizacionGrupo()">Actualizar Grupo</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- LÓGICA DE FOTO ---
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_foto').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- SELECCIÓN ---
    function actualizarBotones() {
        const seleccionados = document.querySelectorAll('.check-estudiante:checked').length;
        document.getElementById('btnEditar').disabled = (seleccionados !== 1);
        document.getElementById('btnEliminar').disabled = (seleccionados === 0);
        document.getElementById('btnActualizar').disabled = (seleccionados === 0);
    }

    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.check-estudiante');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
            const row = document.getElementById('row-' + cb.value);
            if (source.checked) row.classList.add('selected-row');
            else row.classList.remove('selected-row');
        });
        actualizarBotones();
    }

    function checkRow(cb) {
        const row = document.getElementById('row-' + cb.value);
        if (cb.checked) row.classList.add('selected-row');
        else {
            row.classList.remove('selected-row');
            document.getElementById('checkAll').checked = false;
        }
        actualizarBotones();
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.check-estudiante:checked')).map(cb => cb.value);
    }

    // --- EDITAR ---
    function editarSeleccionado() {
        const ids = getSelectedIds();
        if (ids.length !== 1) return;

        fetch('roles/admin/academico/estudiantes/editar.php?id=' + ids[0])
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const e = res.data;
                    document.getElementById('edit_id').value = e.id;
                    document.getElementById('edit_tipo_doc').value = e.tipo_documento;
                    document.getElementById('edit_documento').value = e.numero_documento;
                    document.getElementById('edit_nombres').value = e.nombres_completos;
                    document.getElementById('edit_telefono').value = e.telefono;
                    document.getElementById('edit_correo').value = e.correo;
                    document.getElementById('edit_direccion').value = e.direccion;
                    document.getElementById('edit_eps').value = e.eps;
                    document.getElementById('edit_sisben').value = e.sisben;
                    document.getElementById('edit_estado_civil').value = e.estado_civil;
                    document.getElementById('edit_estado').value = e.estado;
                    document.getElementById('edit_c_nombre').value = e.contacto_emergencia_nombre;
                    document.getElementById('edit_c_parentesco').value = e.contacto_emergencia_parentesco;
                    document.getElementById('edit_c_telefono').value = e.contacto_emergencia_telefono;
                    document.getElementById('edit_c_direccion').value = e.contacto_emergencia_direccion;

                    // Foto Preview
                    const preview = document.getElementById('preview_foto');
                    if (e.foto) {
                        preview.src = "admin/configuracion/uploads/usuarios/" + e.foto;
                    } else {
                        preview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(e.nombres_completos)}&background=random&color=fff`;
                    }

                    new bootstrap.Modal(document.getElementById('modalEditarEstudiante')).show();
                }
            });
    }

    // --- GUARDAR CAMBIOS ---
    document.getElementById('formEditarEstudiante').onsubmit = function(e) {
        e.preventDefault();
        
        // Bloquear botón para evitar doble envío
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerText = "Guardando...";

        fetch('roles/admin/academico/estudiantes/editar.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                Swal.fire('Éxito', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
                btn.disabled = false;
                btn.innerText = "Guardar Cambios";
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            btn.disabled = false;
            btn.innerText = "Guardar Cambios";
        });
    };

    // --- IMPORTAR ---
    function importarDatos() {
        Swal.fire({
            title: 'Importar Estudiantes',
            text: 'Selecciona el archivo Excel (.csv)',
            icon: 'info',
            input: 'file',
            inputAttributes: { 'accept': '.csv' },
            showCancelButton: true,
            confirmButtonText: 'Subir e Importar',
            showLoaderOnConfirm: true,
            preConfirm: (file) => {
                if (!file) return false;
                const formData = new FormData();
                formData.append('archivo_excel', file);
                return fetch('roles/admin/academico/estudiantes/procesar_importacion.php', {
                    method: 'POST', body: formData
                }).then(res => res.json());
            }
        }).then((result) => {
            if (result.isConfirmed && result.value.status === 'success') {
                Swal.fire('¡Éxito!', `Se agregaron ${result.value.insertados} registros.`, 'success').then(() => location.reload());
            }
        });
    }

    // --- EXPORTAR A EXCEL ---
    async function exportarExcel() {
        Swal.fire({
            title: 'Preparando Excel',
            text: 'Obteniendo datos de la base de datos...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            // 1. Cargamos la librería SheetJS dinámicamente si no está cargada
            if (typeof XLSX === 'undefined') {
                await cargarScript("https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js");
            }

            // 2. Consultamos todos los datos al PHP
            const response = await fetch('roles/admin/academico/estudiantes/exportar_excel.php');
            const res = await response.json();

            if (res.status === 'success') {
                const datos = res.data;

                // 3. Crear el libro y la hoja de Excel
                const worksheet = XLSX.utils.json_to_sheet(datos);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Estudiantes");

                // 4. Descargar el archivo
                XLSX.writeFile(workbook, "Listado_Estudiantes.xlsx");
                
                Swal.close();
            } else {
                throw new Error(res.message);
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo generar el Excel: ' + error.message, 'error');
        }
    }

    // Función auxiliar para cargar la librería bajo demanda
    function cargarScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
    
    // --- ELIMINAR SELECCIONADOS ---
    function eliminarSeleccionado() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        const mensaje = ids.length === 1 
            ? "¿Estás seguro de eliminar este estudiante?" 
            : `¿Estás seguro de eliminar estos ${ids.length} estudiantes?`;

        Swal.fire({
            title: mensaje,
            html: `
                <div class="text-danger fw-bold mb-2">¡ADVERTENCIA CRÍTICA!</div>
                <p class="small text-muted">
                    Esta acción eliminará permanentemente <b>todo el historial</b> relacionado: 
                    pagos, registros académicos y archivos.
                    <br><br>Esta acción <b>no se puede deshacer</b>.
                </p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Sí, eliminar todo',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Usamos FormData para que sea más fácil de leer en PHP con $_POST
                const formData = new FormData();
                formData.append('ids', JSON.stringify(ids)); 

                return fetch('roles/admin/academico/estudiantes/eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    return response.json();
                })
                .then(data => {
                    if (data.status !== 'success') throw new Error(data.message);
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire('Eliminado', result.value.message, 'success').then(() => {
                    location.reload();
                });
            }
        });
    }

    // --- ACTUALIZAR GRUPO (MODAL Y CARGA) ---
    function actualizarGrupo() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        document.getElementById('info_seleccion').innerText = `${ids.length} estudiante(s) seleccionados para reasignar.`;
        
        // Cargar grupos desde el servidor
        fetch('roles/admin/academico/estudiantes/obtener_grupos.php')
            .then(res => res.json())
            .then(res => {
                const select = document.getElementById('select_grupo_destino');
                select.innerHTML = '<option value="">-- Seleccione un grupo --</option>';
                
                if (res.status === 'success') {
                    res.data.forEach(g => {
                        select.innerHTML += `<option value="${g.id}">${g.nombre} (${g.jornada}) - Disponibles: ${g.cupos_disponibles}</option>`;
                    });
                    new bootstrap.Modal(document.getElementById('modalActualizarGrupo')).show();
                } else {
                    Swal.fire('Error', 'No se pudieron cargar los grupos', 'error');
                }
            });
    }

    function procesarActualizacionGrupo() {
        const grupoId = document.getElementById('select_grupo_destino').value;
        const ids = getSelectedIds();

        if (!grupoId) {
            Swal.fire('Atención', 'Debes seleccionar un grupo destino', 'warning');
            return;
        }

        Swal.fire({
            title: 'Procesando...',
            text: 'Inscribiendo estudiantes al nuevo grupo',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('grupo_id', grupoId);
        formData.append('estudiantes_ids', JSON.stringify(ids));

        fetch('roles/admin/academico/estudiantes/procesar_grupo.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                let msg = `Proceso completado. ${res.insertados} inscritos correctamente.`;
                if (res.omitidos > 0) {
                    msg += `<br><small class="text-danger">${res.omitidos} estudiantes fueron omitidos por estar "En formación" o ya inscritos.</small>`;
                }
                
                Swal.fire({
                    title: 'Resultado del proceso',
                    html: msg,
                    icon: res.insertados > 0 ? 'success' : 'info'
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Error de conexión', 'error'));
    }

    
</script>