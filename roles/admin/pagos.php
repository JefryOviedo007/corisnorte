<div class="container py-4 pagos-module">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">
      💳 Pagos de Estudiantes
    </h3>

    <button class="btn btn-primary" onclick="mostrarFormularioPago()">
      <i class="bi bi-plus-circle"></i> Registrar nuevo pago
    </button>
  </div>

  <!-- TARJETAS RESUMEN -->
  <div class="row g-4 mb-4">

    <div class="col-md-4">
      <div class="card tarjeta-pago borde-izq-success">
        <div class="card-body">
          <h6 class="text-muted">Pagos Hoy</h6>
          <h2 id="pagosHoy" class="fw-bold text-success">$ 1,050,000</h2>
          <small class="text-success">Pagos efectivos</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-pago borde-izq-warning">
        <div class="card-body">
          <h6 class="text-muted">Pagos Pendientes</h6>
          <h2 class="fw-bold text-warning">$ 430,000</h2>
          <small class="text-warning">Por cobrar</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-pago borde-izq-primary">
        <div class="card-body">
          <h6 class="text-muted">Total del Mes</h6>
          <h2 class="fw-bold text-primary">$ 15,850,000</h2>
          <small class="text-primary">Ingresos académicos</small>
        </div>
      </div>
    </div>

  </div>

  <!-- FILTROS -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">

      <div class="col-md-4">
        <input type="text" id="buscarEstudiante" class="form-control" placeholder="🔍 Buscar estudiante...">
      </div>

      <div class="col-md-4">
        <select class="form-select" id="filtroTipo">
          <option value="">Todos los conceptos</option>
          <option value="Mensualidad">Mensualidad</option>
          <option value="Matrícula">Matrícula</option>
          <option value="Inscripción">Inscripción</option>
          <option value="Derecho a Grado">Derecho a Grado</option>
          <option value="Seminario">Seminario</option>
        </select>
      </div>

      <div class="col-md-4">
        <select class="form-select" id="filtroEstado">
          <option value="">Todos los estados</option>
          <option value="Pagado">Pagado</option>
          <option value="Pendiente">Pendiente</option>
        </select>
      </div>

    </div>
  </div>

  <!-- TABLA -->
  <div class="card shadow-sm">
    <div class="card-header bg-light fw-semibold">
      📋 Historial de Pagos
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
          <tr>
            <th>Estudiante</th>
            <th>Concepto</th>
            <th>Valor</th>
            <th>Estado</th>
          </tr>
        </thead>

        <tbody id="tablaPagos">

          <tr>
            <td>Juan Pérez</td>
            <td>Mensualidad</td>
            <td class="fw-bold">$250,000</td>
            <td><span class="badge bg-success">Pagado</span></td>
          </tr>

          <tr>
            <td>Ana Gómez</td>
            <td>Matrícula</td>
            <td class="fw-bold">$300,000</td>
            <td><span class="badge bg-success">Pagado</span></td>
          </tr>

          <tr>
            <td>José Díaz</td>
            <td>Inscripción</td>
            <td class="fw-bold">$180,000</td>
            <td><span class="badge bg-warning text-dark">Pendiente</span></td>
          </tr>

          <tr>
            <td>María Torres</td>
            <td>Derecho a Grado</td>
            <td class="fw-bold">$220,000</td>
            <td><span class="badge bg-success">Pagado</span></td>
          </tr>

        </tbody>

      </table>
    </div>
  </div>

  <!-- FORMULARIO FLOTANTE REGISTRAR PAGO -->
  <div id="formPago" class="modal-pago">
    <div class="modal-pago-content">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Registrar Nuevo Pago</h5>
        <button class="btn-close" onclick="cerrarFormularioPago()"></button>
      </div>

      <input type="text" class="form-control mb-2" placeholder="Nombre del estudiante">
      <select class="form-select mb-2">
        <option>Mensualidad</option>
        <option>Matrícula</option>
        <option>Inscripción</option>
        <option>Derecho a Grado</option>
        <option>Seminario</option>
      </select>
      <input type="number" class="form-control mb-3" placeholder="Valor del pago">

      <div class="d-grid">
        <button class="btn btn-success" onclick="guardarPagoDemo()">
          Registrar Pago
        </button>
      </div>

    </div>
  </div>

</div>

<!-- ✅ ESTILOS PROPIOS -->
<style>
.pagos-module {
  animation: fadeInPagos 0.4s ease-in-out;
}

.tarjeta-pago {
  border-radius: 14px;
  border: none;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  position: relative;
  transition: all 0.2s ease;
}

.tarjeta-pago:hover {
  transform: translateY(-4px);
}

.borde-izq-success::before,
.borde-izq-warning::before,
.borde-izq-primary::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 6px;
  height: 100%;
}

.borde-izq-success::before { background: #198754; }
.borde-izq-warning::before { background: #ffc107; }
.borde-izq-primary::before { background: #0d6efd; }

/* MODAL */
.modal-pago {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,.55);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-pago-content {
  background: white;
  padding: 25px;
  border-radius: 14px;
  width: 100%;
  max-width: 400px;
  animation: fadeInPagos 0.3s ease;
}

@keyframes fadeInPagos {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ✅ JAVASCRIPT DEMO -->
<script>
function mostrarFormularioPago() {
  document.getElementById('formPago').style.display = 'flex';
}

function cerrarFormularioPago() {
  document.getElementById('formPago').style.display = 'none';
}

function guardarPagoDemo() {
  alert("✅ Pago registrado correctamente (Demo)");
  cerrarFormularioPago();
}

// Simulación de pagos en tiempo real
setInterval(() => {
  const valor = Math.floor(Math.random() * 300000) + 200000;
  document.getElementById('pagosHoy').innerText = "$ " + valor.toLocaleString();
}, 3000);
</script>
