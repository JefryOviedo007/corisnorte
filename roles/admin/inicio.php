<div class="container py-4 dashboard-module">

  <h3 class="fw-bold text-primary mb-4">📊 Panel General - Corporación Instituto del Norte</h3>

  <!-- ===================== TARJETAS RESUMEN ===================== -->
  <div class="row g-4 mb-4">

    <div class="col-md-3">
      <div class="card resumen-card border-left-ingresos">
        <h6>Ingresos Hoy</h6>
        <h3 id="ingresosHoy">$ 1,250,000</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-card border-left-egresos">
        <h6>Egresos del Mes</h6>
        <h3>$ 9,500,000</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-card border-left-estudiantes">
        <h6>Estudiantes Activos</h6>
        <h3>1,284</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-card border-left-cuentas">
        <h6>Saldo en Cuentas</h6>
        <h3>$ 31,250,000</h3>
      </div>
    </div>

  </div>

  <!-- ===================== ATAJOS RÁPIDOS ===================== -->
  <div class="row g-4 mb-4">

    <div class="col-md-2 col-6">
      <a href="?page=ingresos" class="card atajo text-center">
        💰<br>Ingresos
      </a>
    </div>

    <div class="col-md-2 col-6">
      <a href="?page=pagos" class="card atajo text-center">
        💳<br>Pagos
      </a>
    </div>

    <div class="col-md-2 col-6">
      <a href="?page=estudiantes" class="card atajo text-center">
        👨‍🎓<br>Estudiantes
      </a>
    </div>

    <div class="col-md-2 col-6">
      <a href="?page=proveedores" class="card atajo text-center">
        🚚<br>Proveedores
      </a>
    </div>

    <div class="col-md-2 col-6">
      <a href="?page=cuentas" class="card atajo text-center">
        🏦<br>Cuentas
      </a>
    </div>

    <div class="col-md-2 col-6">
      <a href="?page=reportes" class="card atajo text-center">
        📊<br>Reportes
      </a>
    </div>

  </div>

  <!-- ===================== SECCIONES INFERIORES ===================== -->
  <div class="row g-4">

    <!-- PAGOS RECIENTES -->
    <div class="col-md-6">
      <div class="card p-3 shadow-sm">
        <h5 class="fw-bold">💳 Pagos Recientes</h5>
        <table class="table mt-3">
          <tr><td>Juan Pérez</td><td>Matrícula</td><td>$300,000</td></tr>
          <tr><td>Ana Gómez</td><td>Mensualidad</td><td>$250,000</td></tr>
          <tr><td>Carlos Díaz</td><td>Inscripción</td><td>$180,000</td></tr>
        </table>
      </div>
    </div>

    <!-- EGRESOS RECIENTES -->
    <div class="col-md-6">
      <div class="card p-3 shadow-sm">
        <h5 class="fw-bold">📉 Egresos Recientes</h5>
        <table class="table mt-3">
          <tr><td>Pago Profesores</td><td>$4,500,000</td></tr>
          <tr><td>Arrendamiento</td><td>$1,800,000</td></tr>
          <tr><td>Servicios</td><td>$1,200,000</td></tr>
        </table>
      </div>
    </div>

  </div>

</div>

<!-- ===================== ESTILOS ===================== -->
<style>
.dashboard-module {
  animation: fadeDashboard 0.4s ease;
}

@keyframes fadeDashboard {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.resumen-card {
  padding: 18px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,.08);
  background: white;
}

.border-left-ingresos { border-left: 6px solid #198754; }
.border-left-egresos  { border-left: 6px solid #dc3545; }
.border-left-estudiantes { border-left: 6px solid #0d6efd; }
.border-left-cuentas { border-left: 6px solid #fd7e14; }

.atajo {
  padding: 20px 10px;
  text-decoration: none;
  font-weight: bold;
  border-radius: 12px;
  transition: .3s;
  background: white;
  color: #333;
  box-shadow: 0 4px 12px rgba(0,0,0,.08);
}

.atajo:hover {
  transform: translateY(-5px);
  background: #0d6efd;
  color: #fff;
}
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===================== JS ===================== -->
<script>
setInterval(() => {
  let valor = Math.floor(Math.random() * 400000) + 800000;
  document.getElementById("ingresosHoy").innerText =
    "$ " + valor.toLocaleString("es-CO");
}, 3000);
</script>
