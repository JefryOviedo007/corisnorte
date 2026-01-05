<?php
$rol = $_SESSION['rol'] ?? 'Invitado';
$mi_sede = $_SESSION['sede_id'] ?? null;
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

<div class="container mt-3">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>
      <i class="bi bi-cash-stack"></i> Gestión de Pagos
    </h3>

    <div class="d-flex gap-2">
        <button class="btn btn-danger shadow-sm" id="btnExportPDF" onclick="exportarPDF()">
            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </button>    
        
        <button class="btn btn-dark shadow-sm" id="btnImprimir" onclick="imprimirTabla()">
            <i class="bi bi-printer"></i> Imprimir Tabla
        </button>
    </div>
  </div>
  
  
<div class="card p-3">
    <div class="card-body">
        <div class="row g-3 mb-4">
            <?php if ($rol === 'Admin'): ?>
                <div class="col-md-3">
                    <label class="small fw-bold">Sede:</label>
                    <select id="select_sede" class="form-select form-select-sm">
                        <option value="">-- Seleccione Sede --</option>
                        </select>
                </div>
            <?php endif; ?>

            <div class="col-md-4">
                <label class="small fw-bold">Grupo:</label>
                <select id="select_grupo" class="form-select form-select-sm" disabled>
                    <option value="">-- Seleccione un grupo --</option>
                </select>
            </div>
        </div>

        <div id="contenedor_tabla" class="table-responsive">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-arrow-up-circle display-4"></i>
                <p>Seleccione los filtros para visualizar la información</p>
            </div>
        </div>
    </div>
</div>

</div>


<div class="modal fade" id="modalVerEstudiante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge"></i> Ficha del Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <div class="mb-3">
                            <img src="assets/img/user-default.webp" id="view_foto" class="rounded shadow-sm border" style="width: 180px; height: 180px; object-fit: cover;">
                        </div>
                        <h5 id="view_nombre_titulo" class="fw-bold text-uppercase small"></h5>
                        <span id="view_doc_badge" class="badge bg-danger"></span>
                    </div>

                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted small d-block">Documento</label>
                                <span id="view_documento" class="fw-bold"></span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">Correo Electrónico</label>
                                <span id="view_correo" class="fw-bold"></span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">Teléfono / Celular</label>
                                <span id="view_tel" class="fw-bold"></span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">Dirección</label>
                                <span id="view_direccion" class="fw-bold"></span>
                            </div>
                            <div class="col-4">
                                <label class="text-muted small d-block">Estado Civil</label>
                                <span id="view_estado_civil" class="fw-bold"></span>
                            </div>
                            <div class="col-4">
                                <label class="text-muted small d-block">Sisbén</label>
                                <span id="view_sisben" class="badge bg-danger text-white"></span>
                            </div>
                            <div class="col-4">
                                <label class="text-muted small d-block">EPS</label>
                                <span id="view_eps" class="fw-bold"></span>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-2 text-primary border-bottom pb-1"><i class="bi bi-telephone-outbound"></i> Contacto de Emergencia</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label class="text-muted small d-block">Nombre</label>
                                    <span id="view_contacto_nombre" class="fw-bold"></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block">Parentesco</label>
                                    <span id="view_contacto_parentesco" class="fw-bold"></span>
                                </div>
                                <div class="col-6 mt-2">
                                    <label class="text-muted small d-block">Celular</label>
                                    <span id="view_contacto_cel" class="fw-bold"></span>
                                </div>
                                <div class="col-6 mt-2">
                                    <label class="text-muted small d-block">Dirección</label>
                                    <span id="view_contacto_dir" class="fw-bold text-truncate d-block"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cerrar Ficha</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.6.0/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectSede = document.getElementById('select_sede');
    const selectGrupo = document.getElementById('select_grupo');
    const contenedor = document.getElementById('contenedor_tabla');
    const miSede = '<?= $mi_sede ?>';
    const esAdmin = '<?= $rol === 'Admin' ?>';

    // 1. Cargar Sedes si es Admin, si no, cargar grupos de su sede directamente
    if (esAdmin) {
        fetch('roles/admin/finanzas/pagos/consultas.php?action=getSedes')
            .then(res => res.json())
            .then(data => {
                data.forEach(s => {
                    selectSede.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
                });
            });
    } else {
        cargarGrupos(miSede);
    }

    if (selectSede) {
        selectSede.addEventListener('change', (e) => cargarGrupos(e.target.value));
    }

    selectGrupo.addEventListener('change', (e) => cargarTabla(e.target.value));

    function cargarGrupos(sedeId) {
        if (!sedeId) return;
        selectGrupo.disabled = false;
        selectGrupo.innerHTML = '<option value="">Cargando...</option>';
        
        fetch(`roles/admin/finanzas/pagos/consultas.php?action=getGrupos&sede_id=${sedeId}`)
            .then(res => res.json())
            .then(data => {
                selectGrupo.innerHTML = '<option value="">-- Seleccione Grupo --</option>';
                data.forEach(g => {
                    selectGrupo.innerHTML += `<option value="${g.id}">${g.nombre} (${g.programa})</option>`;
                });
            });
    }

    function cargarTabla(grupoId) {
        if (!grupoId) return;
        contenedor.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p>Procesando datos financieros...</p></div>';
        
        fetch(`roles/admin/finanzas/pagos/consultas.php?action=getTabla&grupo_id=${grupoId}`)
            .then(res => res.text())
            .then(html => {
                contenedor.innerHTML = html;
            });
    }
});

// --- Función para Imprimir ---
function imprimirTabla() {
    const grupoNombre = document.getElementById('select_grupo').options[document.getElementById('select_grupo').selectedIndex].text;
    const contenido = document.getElementById('contenedor_tabla').innerHTML;
    
    // Crear una ventana temporal para impresión
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <html>
            <head>
                <title>Reporte de Pagos - ${grupoNombre}</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
                <style>
                    body { padding: 20px; font-family: sans-serif; }
                    .table { font-size: 10px !important; width: 100% !important; }
                    .bg-success { background-color: #2ecc71 !important; color: white !important; -webkit-print-color-adjust: exact; }
                    .bg-danger { background-color: #d92550 !important; color: white !important; -webkit-print-color-adjust: exact; }
                    .bg-warning { background-color: #f1c40f !important; -webkit-print-color-adjust: exact; }
                    thead { background-color: #3f6ad8 !important; color: white !important; -webkit-print-color-adjust: exact; }
                </style>
            </head>
            <body>
                <h4 class="text-center mb-4">Sábana de Control de Pagos - ${grupoNombre}</h4>
                ${contenido}
            </body>
        </html>
    `);
    ventana.document.close();
    
    // Esperar a que carguen los estilos y ejecutar impresión
    ventana.onload = function() {
        ventana.print();
        ventana.close();
    };
}

// --- Función para Descargar PDF ---
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // 'l' para formato horizontal (Landscape)
    
    const grupoNombre = document.getElementById('select_grupo').options[document.getElementById('select_grupo').selectedIndex].text;
    const tabla = document.querySelector("#contenedor_tabla table");

    if (!tabla) {
        Swal.fire('Error', 'No hay datos para exportar', 'error');
        return;
    }

    // Título del PDF
    doc.setFontSize(16);
    doc.text(`Reporte de Pagos - ${grupoNombre}`, 14, 15);
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleString()}`, 14, 22);

    // Generar la tabla automática
    doc.autoTable({
        html: tabla,
        startY: 30,
        theme: 'grid',
        styles: { fontSize: 7, cellPadding: 2 },
        headStyles: { fillColor: [63, 106, 216], textColor: 255 }, // Color azul institucional
        didParseCell: function(data) {
            // Aplicar colores según el texto de la celda
            const text = data.cell.raw.innerText || data.cell.raw.textContent;
            if (text && text.includes('PAGADO')) {
                data.cell.styles.fillColor = [46, 204, 113]; // Verde
                data.cell.styles.textColor = 255;
            } else if (text && text.includes('DEBE')) {
                data.cell.styles.fillColor = [217, 37, 80]; // Rojo
                data.cell.styles.textColor = 255;
            } else if (text && text.includes('ABONO')) {
                data.cell.styles.fillColor = [241, 196, 15]; // Amarillo
            }
        }
    });

    doc.save(`Pagos_${grupoNombre.replace(/ /g, "_")}.pdf`);
}
</script>

<script>
// ... (Tus funciones de cargarTabla, cargarGrupos, etc.)

function verDetalle(personaId) {
    // Cambiamos la ruta al nuevo archivo que busca por ID de persona directamente
    fetch(`roles/admin/finanzas/pagos/get_detalle_estudiante.php?id=${personaId}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const p = res.data;

                // Llenar datos en el modal
                document.getElementById('view_nombre_titulo').innerText = p.nombres_completos;
                document.getElementById('view_doc_badge').innerText = p.tipo_documento;
                document.getElementById('view_documento').innerText = p.numero_documento;
                document.getElementById('view_correo').innerText = p.correo;
                document.getElementById('view_tel').innerText = p.telefono || 'No registra';
                document.getElementById('view_direccion').innerText = p.direccion || 'No registra';
                document.getElementById('view_estado_civil').innerText = p.estado_civil || 'No registra';
                document.getElementById('view_sisben').innerText = p.sisben || 'N/A';
                document.getElementById('view_eps').innerText = p.eps || 'No registra';
                
                // Contacto de Emergencia
                document.getElementById('view_contacto_nombre').innerText = p.contacto_emergencia_nombre || 'No registra';
                document.getElementById('view_contacto_parentesco').innerText = p.contacto_emergencia_parentesco || '-';
                document.getElementById('view_contacto_cel').innerText = p.contacto_emergencia_telefono || '-';
                document.getElementById('view_contacto_dir').innerText = p.contacto_emergencia_direccion || '-';

                // Foto con timestamp para evitar caché
                const img = document.getElementById('view_foto');
                if (p.foto) {
                    img.src = `assets/img/perfiles/${p.foto}?t=${new Date().getTime()}`;
                } else {
                    img.src = 'assets/img/user-default.webp';
                }

                // Mostrar el modal (Asegúrate de que el ID del modal sea modalVerEstudiante)
                const modalElement = document.getElementById('modalVerEstudiante');
                const myModal = bootstrap.Modal.getOrCreateInstance(modalElement);
                myModal.show();
            } else {
                // Este es el error que te salía, ahora el mensaje será más descriptivo
                Swal.fire('Atención', res.message, 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error de Conexión', 'No se pudo obtener la ficha del estudiante.', 'error');
        });
}

function abrirModalPago(personaId, concepto, montoPendiente) {
    const montoFormateado = new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', maximumFractionDigits: 0
    }).format(montoPendiente);

    Swal.fire({
        title: 'Procesar Pago',
        html: `
            <div class="text-start mt-2">
                <p class="mb-1 small text-muted text-uppercase fw-bold">Concepto:</p>
                <p class="mb-3 border-bottom pb-2">${concepto}</p>

                <div class="p-3 mb-3 text-center" style="background: #f0fff4; border-radius: 10px; border: 1px dashed #27ae60;">
                    <small class="text-muted d-block">VALOR A COBRAR (EDITABLE PARA ABONOS)</small>
                    <input type="number" id="pago_monto_total" class="form-control form-control-lg text-center text-success fw-bold" 
                           value="${montoPendiente}" style="background: transparent; border: none; font-size: 1.5rem;">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">MÉTODO DE PAGO</label>
                    <select id="pago_metodo" class="form-select" onchange="toggleDividido(this.value)">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Dividido">Dividido (Efectivo + Transf.)</option>
                    </select>
                </div>

                <div id="pago_seccion_dividido" style="display:none;" class="p-3 mb-3 border rounded bg-light">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small fw-bold">Efectivo</label>
                            <input type="number" id="pago_efectivo" class="form-control" placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Transferencia</label>
                            <input type="number" id="pago_transferencia" class="form-control" placeholder="0">
                        </div>
                    </div>
                </div>

            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Procesar Pago',
        confirmButtonColor: '#27ae60',
        preConfirm: () => {
            const elMetodo = document.getElementById('pago_metodo');
            const elRef = document.getElementById('pago_referencia');
            const elMontoTotal = document.getElementById('pago_monto_total');
            
            if (!elMetodo) return false;

            const metodo = elMetodo.value;
            const referencia = elRef ? elRef.value : '';
            let efec = 0; 
            let trans = 0;
            let montoFinal = parseFloat(elMontoTotal.value) || 0;

            if (metodo === 'Dividido') {
                efec = parseFloat(document.getElementById('pago_efectivo').value) || 0;
                trans = parseFloat(document.getElementById('pago_transferencia').value) || 0;
                montoFinal = efec + trans; // El monto es la suma de ambos abonos
            } else if (metodo === 'Efectivo') { 
                efec = montoFinal; 
            } else { 
                trans = montoFinal; 
            }

            if (montoFinal <= 0) {
                Swal.showValidationMessage('El monto debe ser mayor a 0');
                return false;
            }

            return {
                persona_id: personaId,
                concepto: concepto,
                monto: montoFinal,
                metodo_pago: metodo,
                monto_efectivo: efec,
                monto_transferencia: trans,
                referencia: referencia
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            enviarPago(result.value);
        }
    });
}

// Función para mostrar/ocultar campos de pago dividido
function toggleDividido(valor) {
    const seccion = document.getElementById('pago_seccion_dividido');
    seccion.style.display = (valor === 'Dividido') ? 'block' : 'none';
}

// Dentro de tu función abrirModalPago, en la parte de preConfirm:
// ... (resto del código de Swal.fire)
preConfirm: () => {
    const metodo = document.getElementById('pago_metodo').value;
    const ref = document.getElementById('pago_referencia').value;
    let m_efectivo = 0;
    let m_transferencia = 0;

    if (metodo === 'Dividido') {
        m_efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
        m_transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
        
        // Validación básica
        if ((m_efectivo + m_transferencia) !== monto) {
            Swal.showValidationMessage(`La suma (${m_efectivo + m_transferencia}) debe ser igual al total (${monto})`);
            return false;
        }
    }

    return {
        persona_id: personaId,
        concepto: concepto,
        monto: monto,
        metodo_pago: metodo,
        monto_efectivo: m_efectivo,
        monto_transferencia: m_transferencia,
        referencia: ref
    };
}
// ... luego llamas a enviarPago(results.value)

function enviarPago(datos) {
    Swal.fire({ title: 'Registrando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    const params = new URLSearchParams();
    for (const key in datos) { params.append(key, datos[key]); }

    fetch('roles/admin/finanzas/pagos/procesar_pago_individual.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: '¡Pago Registrado!',
                text: 'El estudiante recibirá el comprobante en su correo: ' + data.correo_envio,
                icon: 'success'
            }).then(() => {
                // Refrescar la tabla para que el botón de pago desaparezca y diga "PAGADO"
                document.getElementById('select_grupo').dispatchEvent(new Event('change'));
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo procesar el pago.', 'error');
    });
}

// Actualiza tu función cargarTabla para que use el selector de tabla correcto en PDF e Impresión
</script>