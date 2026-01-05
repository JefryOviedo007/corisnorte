<div class="container py-4 proveedores-module">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">
      🚚 Proveedores
    </h3>

    <button class="btn btn-primary" onclick="abrirProveedor()">
      ➕ Nuevo Proveedor
    </button>
  </div>

  <!-- RESUMEN -->
  <div class="row g-4 mb-4">

    <div class="col-md-4">
      <div class="card proveedor-resumen primary">
        <h6>Total Proveedores</h6>
        <h2>12</h2>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card proveedor-resumen success">
        <h6>Pagos Este Mes</h6>
        <h2>$ 9,500,000</h2>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card proveedor-resumen danger">
        <h6>Proveedores Activos</h6>
        <h2>10</h2>
      </div>
    </div>

  </div>

  <!-- TABLA -->
  <div class="card shadow-sm">
    <div class="card-header fw-semibold">
      📋 Lista de Proveedores
    </div>

    <div class="p-3">
      <input type="text" id="buscadorProveedor" class="form-control" placeholder="Buscar proveedor..." onkeyup="filtrarProveedor()">
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="tablaProveedores">
        <thead class="table-primary">
          <tr>
            <th>Proveedor</th>
            <th>Servicio</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Total Pagado</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Academia Docente SAS</td>
            <td>Profesores</td>
            <td>3104567890</td>
            <td>contacto@academia.com</td>
            <td>$4,500,000</td>
            <td><span class="badge bg-success">Activo</span></td>
          </tr>
          <tr>
            <td>Servicios del Norte</td>
            <td>Servicios Públicos</td>
            <td>3127896541</td>
            <td>servicios@energia.com</td>
            <td>$1,200,000</td>
            <td><span class="badge bg-success">Activo</span></td>
          </tr>
          <tr>
            <td>Papelería Moderna</td>
            <td>Útiles</td>
            <td>3151239876</td>
            <td>ventas@papeleria.com</td>
            <td>$480,000</td>
            <td><span class="badge bg-secondary">Inactivo</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ✅ MODAL NUEVO PROVEEDOR -->
<div class="modal fade" id="modalProveedor">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">➕ Nuevo Proveedor</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input class="form-control mb-2" placeholder="Nombre del proveedor">
        <input class="form-control mb-2" placeholder="Servicio">
        <input class="form-control mb-2" placeholder="Teléfono">
        <input class="form-control mb-2" placeholder="Email">
        <select class="form-select">
          <option>Activo</option>
          <option>Inactivo</option>
        </select>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="guardarProveedor()">Guardar</button>
      </div>

    </div>
  </div>
</div>

<!-- ✅ ESTILOS -->
<style>
.proveedores-module {
  animation: fadeInProveedor 0.4s ease;
}

.proveedor-resumen {
  padding: 20px;
  border-radius: 14px;
  color: white;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,.1);
}

.proveedor-resumen.primary { background: linear-gradient(135deg, #0d6efd, #4d8dff); }
.proveedor-resumen.success { background: linear-gradient(135deg, #198754, #43c47b); }
.proveedor-resumen.danger  { background: linear-gradient(135deg, #dc3545, #ff6b6b); }

@keyframes fadeInProveedor {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ✅ JAVASCRIPT -->
<script>
function abrirProveedor() {
  new bootstrap.Modal(document.getElementById("modalProveedor")).show();
}

function guardarProveedor() {
  alert("✅ Proveedor registrado (demo)");
  bootstrap.Modal.getInstance(document.getElementById("modalProveedor")).hide();
}

function filtrarProveedor() {
  const input = document.getElementById("buscadorProveedor").value.toLowerCase();
  const filas = document.querySelectorAll("#tablaProveedores tbody tr");

  filas.forEach(fila => {
    const texto = fila.innerText.toLowerCase();
    fila.style.display = texto.includes(input) ? "" : "none";
  });
}
</script>
