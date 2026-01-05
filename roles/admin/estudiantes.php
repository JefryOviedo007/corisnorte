<div class="container py-4 estudiantes-module">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">🎓 Estudiantes</h3>
    <button class="btn btn-primary" onclick="abrirEstudiante()">➕ Nuevo Estudiante</button>
  </div>

  <!-- RESUMEN -->
  <div class="row g-4 mb-4">

    <div class="col-md-3">
      <div class="card resumen-estudiante primary">
        <h6>Total Estudiantes</h6>
        <h2>248</h2>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-estudiante success">
        <h6>Activos</h6>
        <h2>213</h2>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-estudiante warning">
        <h6>Pendientes</h6>
        <h2>21</h2>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card resumen-estudiante danger">
        <h6>Retirados</h6>
        <h2>14</h2>
      </div>
    </div>

  </div>

  <!-- FILTROS -->
  <div class="card shadow-sm mb-4 p-3">
    <div class="row g-3">

      <div class="col-md-4">
        <input type="text" id="buscarEstudiante" class="form-control" placeholder="🔍 Buscar estudiante..." onkeyup="filtrarEstudiantes()">
      </div>

      <div class="col-md-4">
        <select id="filtroPrograma" class="form-select" onchange="filtrarEstudiantes()">
          <option value="">🎯 Todos los Programas</option>
          <option>Bachillerato</option>
          <option>Técnico</option>
          <option>Tecnólogo</option>
          <option>Profesional</option>
        </select>
      </div>

      <div class="col-md-4">
        <select id="filtroNivel" class="form-select" onchange="filtrarEstudiantes()">
          <option value="">🏫 Todos los Niveles</option>
          <option>10°</option>
          <option>11°</option>
          <option>Técnico</option>
          <option>Tecnólogo</option>
          <option>Profesional</option>
        </select>
      </div>

    </div>
  </div>

  <!-- TABLA -->
  <div class="card shadow-sm">

    <div class="card-header fw-semibold">
      📋 Listado de Estudiantes
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="tablaEstudiantes">
        <thead class="table-primary">
          <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Programa</th>
            <th>Nivel</th>
            <th>Estado</th>
            <th>Pagos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>

          <tr>
            <td>Juan Pérez</td>
            <td>1023456789</td>
            <td>Bachillerato</td>
            <td>11°</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>$1,250,000</td>
            <td>
              <button class="btn btn-sm btn-outline-primary">👁</button>
              <button class="btn btn-sm btn-outline-success">💳</button>
            </td>
          </tr>

          <tr>
            <td>Ana Gómez</td>
            <td>1009876543</td>
            <td>Técnico</td>
            <td>Técnico</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>$850,000</td>
            <td>
              <button class="btn btn-sm btn-outline-primary">👁</button>
              <button class="btn btn-sm btn-outline-success">💳</button>
            </td>
          </tr>

          <tr>
            <td>Carlos Díaz</td>
            <td>1017896542</td>
            <td>Tecnólogo</td>
            <td>Tecnólogo</td>
            <td><span class="badge bg-warning">Pendiente</span></td>
            <td>$180,000</td>
            <td>
              <button class="btn btn-sm btn-outline-primary">👁</button>
              <button class="btn btn-sm btn-outline-success">💳</button>
            </td>
          </tr>

          <tr>
            <td>Luisa Martínez</td>
            <td>1098765432</td>
            <td>Profesional</td>
            <td>Profesional</td>
            <td><span class="badge bg-danger">Retirado</span></td>
            <td>$0</td>
            <td>
              <button class="btn btn-sm btn-outline-primary">👁</button>
              <button class="btn btn-sm btn-outline-success">💳</button>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

  </div>

</div>

<!-- ✅ MODAL NUEVO ESTUDIANTE -->
<div class="modal fade" id="modalEstudiante">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">➕ Registrar Estudiante</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body row g-3">

        <div class="col-md-6">
          <input class="form-control" placeholder="Nombre completo">
        </div>

        <div class="col-md-6">
          <input class="form-control" placeholder="Documento">
        </div>

        <div class="col-md-6">
          <select class="form-select">
            <option>Bachillerato</option>
            <option>Técnico</option>
            <option>Tecnólogo</option>
            <option>Profesional</option>
          </select>
        </div>

        <div class="col-md-6">
          <input class="form-control" placeholder="Nivel / Grupo">
        </div>

        <div class="col-md-6">
          <input class="form-control" placeholder="Teléfono">
        </div>

        <div class="col-md-6">
          <input class="form-control" placeholder="Correo electrónico">
        </div>

        <div class="col-md-12">
          <select class="form-select">
            <option>Activo</option>
            <option>Pendiente</option>
            <option>Retirado</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="guardarEstudiante()">Guardar</button>
      </div>

    </div>
  </div>
</div>

<!-- ✅ ESTILOS -->
<style>
.estudiantes-module {
  animation: fadeEstudiante 0.4s ease;
}

.resumen-estudiante {
  padding: 20px;
  border-radius: 14px;
  color: white;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,.1);
}

.resumen-estudiante.primary {
  background: linear-gradient(135deg, #0d6efd, #4d8dff);
}
.resumen-estudiante.success {
  background: linear-gradient(135deg, #198754, #43c47b);
}
.resumen-estudiante.warning {
  background: linear-gradient(135deg, #ffc107, #ffdd57);
  color:#333;
}
.resumen-estudiante.danger {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
}

@keyframes fadeEstudiante {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ✅ JAVASCRIPT -->
<script>
function abrirEstudiante() {
  new bootstrap.Modal(document.getElementById("modalEstudiante")).show();
}

function guardarEstudiante() {
  alert("✅ Estudiante registrado (modo demostración)");
  bootstrap.Modal.getInstance(document.getElementById("modalEstudiante")).hide();
}

function filtrarEstudiantes() {
  const texto = document.getElementById("buscarEstudiante").value.toLowerCase();
  const programa = document.getElementById("filtroPrograma").value.toLowerCase();
  const nivel = document.getElementById("filtroNivel").value.toLowerCase();

  const filas = document.querySelectorAll("#tablaEstudiantes tbody tr");

  filas.forEach(fila => {
    const columnas = fila.children;
    const nombre = columnas[0].innerText.toLowerCase();
    const doc = columnas[1].innerText.toLowerCase();
    const prog = columnas[2].innerText.toLowerCase();
    const niv = columnas[3].innerText.toLowerCase();

    const coincideTexto = nombre.includes(texto) || doc.includes(texto);
    const coincidePrograma = programa === "" || prog === programa;
    const coincideNivel = nivel === "" || niv === nivel;

    fila.style.display = (coincideTexto && coincidePrograma && coincideNivel) ? "" : "none";
  });
}
</script>
