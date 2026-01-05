<div class="container py-4 egresos-module">

  <h3 class="mb-4 fw-bold text-danger">
    📉 Egresos
  </h3>

  <!-- TARJETAS -->
  <div class="row g-4">

    <div class="col-md-4">
      <div class="card tarjeta-egreso borde-izq-danger">
        <div class="card-body">
          <h6 class="text-muted">Gastos Hoy</h6>
          <h2 id="egresosHoy" class="fw-bold text-danger">$ 850,000</h2>
          <small class="text-danger">Pagos operativos</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-egreso borde-izq-warning">
        <div class="card-body">
          <h6 class="text-muted">Gastos del Mes</h6>
          <h2 class="fw-bold text-warning">$ 9,500,000</h2>
          <small class="text-warning">Control mensual</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-egreso borde-izq-dark">
        <div class="card-body">
          <h6 class="text-muted">Total Histórico</h6>
          <h2 class="fw-bold text-dark">$ 124,300,000</h2>
          <small class="text-muted">Desde apertura</small>
        </div>
      </div>
    </div>

  </div>

  <!-- TABLA -->
  <div class="card mt-5 shadow-sm">
    <div class="card-header bg-light fw-semibold">
      📋 Últimos egresos registrados
    </div>

    <div class="card-body p-0">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-danger">
          <tr>
            <th>Concepto</th>
            <th>Responsable</th>
            <th class="text-end">Monto</th>
          </tr>
        </thead>
        <tbody id="tablaEgresos">
          <tr>
            <td>Pago Profesores</td>
            <td>Administración</td>
            <td class="text-end fw-bold">$ 4,500,000</td>
          </tr>
          <tr>
            <td>Servicios Públicos</td>
            <td>Tesorería</td>
            <td class="text-end fw-bold">$ 1,200,000</td>
          </tr>
          <tr>
            <td>Papelería y Útiles</td>
            <td>Secretaría</td>
            <td class="text-end fw-bold">$ 480,000</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ✅ ESTILOS PROPIOS DEL MÓDULO -->
<style>
.egresos-module {
  animation: fadeInEgresos 0.5s ease-in-out;
}

.tarjeta-egreso {
  border-radius: 14px;
  border: none;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tarjeta-egreso:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

/* Línea lateral de color */
.borde-izq-danger::before,
.borde-izq-warning::before,
.borde-izq-dark::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 6px;
  height: 100%;
}

.borde-izq-danger::before {
  background: #dc3545;
}

.borde-izq-warning::before {
  background: #ffc107;
}

.borde-izq-dark::before {
  background: #212529;
}

/* Animación entrada */
@keyframes fadeInEgresos {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<!-- ✅ JAVASCRIPT SIMULACIÓN DE EGRESOS -->
<script>
setInterval(() => {
  const valor = Math.floor(Math.random() * 300000) + 600000;
  const elemento = document.getElementById('egresosHoy');

  elemento.style.opacity = 0;

  setTimeout(() => {
    elemento.innerText = "$ " + valor.toLocaleString();
    elemento.style.opacity = 1;
  }, 300);

}, 3500);
</script>
