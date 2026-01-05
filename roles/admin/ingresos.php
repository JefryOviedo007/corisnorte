<div class="container py-4 ingresos-module">

  <h3 class="mb-4 fw-bold text-primary">
    💰 Ingresos
  </h3>

  <!-- TARJETAS -->
  <div class="row g-4">

    <div class="col-md-4">
      <div class="card tarjeta-ingreso borde-izq-primary">
        <div class="card-body">
          <h6 class="text-muted">Ingresos Hoy</h6>
          <h2 id="ingresosHoy" class="fw-bold text-primary">$ 1,250,000</h2>
          <small class="text-success">+12% que ayer</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-ingreso borde-izq-success">
        <div class="card-body">
          <h6 class="text-muted">Ingresos del Mes</h6>
          <h2 class="fw-bold text-success">$ 18,900,000</h2>
          <small class="text-success">Meta en progreso</small>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card tarjeta-ingreso borde-izq-dark">
        <div class="card-body">
          <h6 class="text-muted">Total General</h6>
          <h2 class="fw-bold text-dark">$ 212,450,000</h2>
          <small class="text-muted">Histórico</small>
        </div>
      </div>
    </div>

  </div>

  <!-- TABLA -->
  <div class="card mt-5 shadow-sm">
    <div class="card-header bg-light fw-semibold">
      📋 Últimos ingresos registrados
    </div>

    <div class="card-body p-0">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-primary">
          <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th class="text-end">Monto</th>
          </tr>
        </thead>
        <tbody id="tablaIngresos">
          <tr>
            <td>2025-11-29</td>
            <td>Matrícula</td>
            <td class="text-end fw-bold">$ 300,000</td>
          </tr>
          <tr>
            <td>2025-11-29</td>
            <td>Mensualidad</td>
            <td class="text-end fw-bold">$ 250,000</td>
          </tr>
          <tr>
            <td>2025-11-28</td>
            <td>Inscripción</td>
            <td class="text-end fw-bold">$ 180,000</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ✅ ESTILOS PROPIOS DEL MÓDULO -->
<style>
.ingresos-module {
  animation: fadeIn 0.5s ease-in-out;
}

.tarjeta-ingreso {
  border-radius: 14px;
  border: none;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tarjeta-ingreso:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

/* Línea lateral de color */
.borde-izq-primary::before,
.borde-izq-success::before,
.borde-izq-dark::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 6px;
  height: 100%;
}

.borde-izq-primary::before {
  background: #0d6efd;
}

.borde-izq-success::before {
  background: #198754;
}

.borde-izq-dark::before {
  background: #212529;
}

/* Animación entrada */
@keyframes fadeIn {
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

<!-- ✅ JAVASCRIPT SIMULACIÓN DE INGRESOS -->
<script>
setInterval(() => {
  const valor = Math.floor(Math.random() * 250000) + 150000;
  const elemento = document.getElementById('ingresosHoy');

  elemento.style.opacity = 0;

  setTimeout(() => {
    elemento.innerText = "$ " + valor.toLocaleString();
    elemento.style.opacity = 1;
  }, 300);

}, 3000);
</script>
