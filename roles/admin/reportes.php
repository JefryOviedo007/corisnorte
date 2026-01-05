<div class="container py-4 reportes-module">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">
      📊 Reportes Financieros
    </h3>

    <div class="d-flex gap-2">
      <input type="date" class="form-control">
      <input type="date" class="form-control">
      <button class="btn btn-primary" onclick="filtrarReporte()">
        Filtrar
      </button>
    </div>
  </div>

  <!-- RESUMEN GENERAL -->
  <div class="row g-4 mb-4">

    <div class="col-md-4">
      <div class="card resumen-card success">
        <h6>Ingresos</h6>
        <h2>$18,900,000</h2>
        <small>Periodo seleccionado</small>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card resumen-card danger">
        <h6>Egresos</h6>
        <h2>$9,500,000</h2>
        <small>Periodo seleccionado</small>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card resumen-card primary">
        <h6>Utilidad</h6>
        <h2>$9,400,000</h2>
        <small>Resultado neto</small>
      </div>
    </div>

  </div>

  <!-- GRAFICOS -->
  <div class="row g-4 mb-4">

    <div class="col-md-6">
      <div class="card p-3">
        <h6 class="fw-bold mb-3">Ingresos vs Egresos</h6>

        <div class="grafico-barras">
          <div class="barra ingresos" style="height: 80%">Ingresos</div>
          <div class="barra egresos" style="height: 45%">Egresos</div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3">
        <h6 class="fw-bold mb-3">Top Conceptos de Pago</h6>

        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between">
            Matrículas <span class="fw-bold">$5,400,000</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            Mensualidades <span class="fw-bold">$11,200,000</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            Inscripciones <span class="fw-bold">$2,300,000</span>
          </li>
        </ul>
      </div>
    </div>

  </div>

  <!-- TABLA DETALLADA -->
  <div class="card shadow-sm">
    <div class="card-header fw-semibold">
      📋 Detalle de movimientos
    </div>

    <div class="p-3">
      <input type="text" id="buscador" class="form-control mb-3" placeholder="Buscar por concepto..." onkeyup="filtrarTabla()">
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="tablaReportes">
        <thead class="table-primary">
          <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Tipo</th>
            <th>Valor</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>2025-11-01</td>
            <td>Mensualidad Juan Pérez</td>
            <td><span class="badge bg-success">Ingreso</span></td>
            <td>$250,000</td>
          </tr>
          <tr>
            <td>2025-11-02</td>
            <td>Pago Profesores</td>
            <td><span class="badge bg-danger">Egreso</span></td>
            <td>$4,500,000</td>
          </tr>
          <tr>
            <td>2025-11-03</td>
            <td>Matrícula Ana Gómez</td>
            <td><span class="badge bg-success">Ingreso</span></td>
            <td>$300,000</td>
          </tr>
          <tr>
            <td>2025-11-04</td>
            <td>Servicios</td>
            <td><span class="badge bg-danger">Egreso</span></td>
            <td>$1,200,000</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ✅ ESTILOS -->
<style>
.reportes-module {
  animation: fadeInReportes 0.4s ease;
}

.resumen-card {
  padding: 20px;
  border-radius: 14px;
  color: white;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,.1);
}

.resumen-card.success { background: linear-gradient(135deg, #198754, #43c47b); }
.resumen-card.danger  { background: linear-gradient(135deg, #dc3545, #ff6b6b); }
.resumen-card.primary { background: linear-gradient(135deg, #0d6efd, #4d8dff); }

.grafico-barras {
  display: flex;
  align-items: end;
  height: 220px;
  gap: 30px;
  padding: 10px;
}

.barra {
  width: 45%;
  display: flex;
  align-items: end;
  justify-content: center;
  color: white;
  font-weight: bold;
  border-radius: 12px 12px 0 0;
  animation: crecer 1s ease;
}

.barra.ingresos { background: #198754; }
.barra.egresos  { background: #dc3545; }

@keyframes crecer {
  from { height: 0; }
}

@keyframes fadeInReportes {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ✅ JAVASCRIPT -->
<script>
function filtrarReporte() {
  alert("📊 Filtro aplicado (demo)");
}

function filtrarTabla() {
  const input = document.getElementById("buscador").value.toLowerCase();
  const filas = document.querySelectorAll("#tablaReportes tbody tr");

  filas.forEach(fila => {
    const texto = fila.innerText.toLowerCase();
    fila.style.display = texto.includes(input) ? "" : "none";
  });
}
</script>
