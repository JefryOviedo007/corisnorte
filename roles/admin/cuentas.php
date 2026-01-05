<div class="container py-4 cuentas-module">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">
      🏦 Gestión de Cuentas
    </h3>

    <button class="btn btn-primary" onclick="mostrarFormularioCuenta()">
      <i class="bi bi-plus-circle"></i> Nueva Cuenta
    </button>
  </div>

  <!-- TARJETAS DE CUENTAS -->
  <div class="row g-4 mb-4">

    <div class="col-md-4">
      <div class="card tarjeta-cuenta borde-izq-success">
        <div class="card-body">
          <h6 class="text-muted">Caja Principal</h6>
          <h2 class="fw-bold text-success">$ 3,450,000</h2>
          <small>Efectivo disponible</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-cuenta borde-izq-primary">
        <div class="card-body">
          <h6 class="text-muted">Banco</h6>
          <h2 class="fw-bold text-primary">$ 27,800,000</h2>
          <small>Saldo bancario</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-cuenta borde-izq-warning">
        <div class="card-body">
          <h6 class="text-muted">Plataformas Digitales</h6>
          <h2 class="fw-bold text-warning">$ 4,200,000</h2>
          <small>Nequi / Daviplata</small>
        </div>
      </div>
    </div>

  </div>

  <!-- TABLA DE CUENTAS -->
  <div class="card shadow-sm">
    <div class="card-header bg-light fw-semibold">
      📋 Listado de cuentas registradas
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
          <tr>
            <th>Cuenta</th>
            <th>Tipo</th>
            <th>Saldo</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody id="tablaCuentas">

          <tr>
            <td>Caja Principal</td>
            <td>Efectivo</td>
            <td class="fw-bold">$3,450,000</td>
            <td><span class="badge bg-success">Activa</span></td>
          </tr>

          <tr>
            <td>Bancolombia</td>
            <td>Banco</td>
            <td class="fw-bold">$17,200,000</td>
            <td><span class="badge bg-success">Activa</span></td>
          </tr>

          <tr>
            <td>Davivienda</td>
            <td>Banco</td>
            <td class="fw-bold">$10,600,000</td>
            <td><span class="badge bg-success">Activa</span></td>
          </tr>

          <tr>
            <td>Nequi Empresarial</td>
            <td>Plataforma</td>
            <td class="fw-bold">$4,200,000</td>
            <td><span class="badge bg-warning text-dark">En revisión</span></td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>

  <!-- MODAL REGISTRAR CUENTA -->
  <div id="formCuenta" class="modal-cuenta">
    <div class="modal-cuenta-content">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Registrar Nueva Cuenta</h5>
        <button class="btn-close" onclick="cerrarFormularioCuenta()"></button>
      </div>

      <input type="text" class="form-control mb-2" placeholder="Nombre de la cuenta">
      
      <select class="form-select mb-2">
        <option>Efectivo</option>
        <option>Banco</option>
        <option>Plataforma Digital</option>
      </select>

      <input type="number" class="form-control mb-3" placeholder="Saldo inicial">

      <div class="d-grid">
        <button class="btn btn-success" onclick="guardarCuentaDemo()">
          Registrar Cuenta
        </button>
      </div>

    </div>
  </div>

</div>

<!-- ✅ ESTILOS VISUALES -->
<style>
.cuentas-module {
  animation: fadeInCuentas 0.4s ease-in-out;
}

.tarjeta-cuenta {
  border-radius: 14px;
  border: none;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  position: relative;
  transition: all 0.2s ease;
}

.tarjeta-cuenta:hover {
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
.modal-cuenta {
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

.modal-cuenta-content {
  background: white;
  padding: 25px;
  border-radius: 14px;
  width: 100%;
  max-width: 400px;
  animation: fadeInCuentas 0.3s ease;
}

@keyframes fadeInCuentas {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ✅ JAVASCRIPT DEMO -->
<script>
function mostrarFormularioCuenta() {
  document.getElementById('formCuenta').style.display = 'flex';
}

function cerrarFormularioCuenta() {
  document.getElementById('formCuenta').style.display = 'none';
}

function guardarCuentaDemo() {
  alert("✅ Cuenta registrada correctamente (Demo)");
  cerrarFormularioCuenta();
}
</script>
