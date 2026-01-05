<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-section position-relative overflow-hidden">

    <!-- VIDEO DESKTOP -->
    <video
        class="hero-video hero-video-desktop"
        autoplay
        muted
        loop
        playsinline
    >
        <source src="/assets/video/hero-desktop.mp4" type="video/mp4">
    </video>

    <!-- VIDEO MOBILE -->
    <video
        class="hero-video hero-video-mobile"
        autoplay
        muted
        loop
        playsinline
    >
        <source src="/assets/video/hero-mobile.mp4" type="video/mp4">
    </video>

    <!-- OVERLAY OSCURO -->
    <div class="hero-overlay"></div>

    <!-- CONTENIDO -->
    <div class="hero-content container text-center text-white position-relative">
        <h1 class="hero-title mb-3">
            ¡Nosotros damos el Norte a tu Vida!
        </h1>

        <p class="hero-subtitle mb-4">
            Formación de calidad para construir tu futuro académico y profesional
        </p>

        <a href="#oferta-educativa" class="btn btn-danger btn-lg px-4 py-2 shadow">
            <i class="bi bi-mortarboard"></i> Ver Oferta Educativa
        </a>
    </div>

</section>

<style>
/* ===== HERO BASE ===== */
.hero-section {
    width: 100%;
    height: 90vh;
    min-height: 500px;
    position: relative;
}

/* ===== VIDEO ===== */
.hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    transform: translate(-50%, -50%);
    object-fit: cover;
    z-index: 1;
}

/* ===== OVERLAY ===== */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    z-index: 2;
}

/* ===== CONTENT ===== */
.hero-content {
    z-index: 3;
    top: 50%;
    transform: translateY(-50%);
    position: relative;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 700;
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    max-width: 700px;
    margin: 0 auto;
    opacity: 0.95;
}

/* ===== RESPONSIVE VIDEO SWITCH ===== */

/* Desktop por defecto */
.hero-video-desktop {
    display: block;
}

.hero-video-mobile {
    display: none;
}

/* Mobile / Pantalla vertical */
@media (max-width: 768px), (orientation: portrait) {
    .hero-video-desktop {
        display: none;
    }

    .hero-video-mobile {
        display: block;
    }

    .hero-section {
        height: 85vh;
    }
}

/* Animación tarjetas */
.grupo-card {
    transition: all 0.35s ease;
}

.grupo-card.oculto {
    opacity: 0;
    transform: scale(0.96);
    pointer-events: none;
    height: 0;
    overflow: hidden;
    margin: 0;
    padding: 0;
}

/* Loader */
.loader-oferta {
    display: none;
    text-align: center;
    padding: 2rem 0;
}

.loader-oferta.active {
    display: block;
}

/* Botones nivel activos */
.btn-nivel.active {
    background-color: #0D09A4;
    color: #fff !important;
    border-color: #0D09A4;
}

.text-primary {
    color: #0D09A4 !important;
}

.text-danger {
    color: #FC0000 !important;
}

.btn-primary {
    background-color: #0D09A4;
}

.btn-danger {
    background-color: #FC0000;
}

/* Tabs de sedes - estado activo */
#tabsSedes .nav-link.active,
#tabsSedes .nav-link.show {
    background-color: #0D09A4 !important;
    color: #fff !important;
}

/* Mantener color del texto */
#tabsSedes .nav-link.active small,
#tabsSedes .nav-link.active i {
    color: #fff !important;
}

/* ===== TARJETAS OFERTA ===== */
.grupo-card .card {
    border-radius: 16px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff, #f8f9ff);
}

.grupo-card .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(13, 9, 164, 0.15);
}

.grupo-card .badge {
    font-size: 0.75rem;
    padding: 0.45em 0.65em;
    border-radius: 20px;
}

.grupo-card h5 {
    line-height: 1.2;
}

.grupo-card .btn {
    border-radius: 10px;
    padding: 0.45rem 0.9rem;
}

.grupo-card .btn-primary {
    background-color: #0D09A4;
    border-color: #0D09A4;
}

.grupo-card .btn-primary:hover {
    background-color: #09067d;
}

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

#modal-header {
          background: linear-gradient(90deg, var(--azul), var(--azul-claro));
          color: white;
          border-bottom: none;
        }


</style>

<?php
require_once __DIR__ . '/config.php';

/* SEDES ACTIVAS */
$sedes = $pdo->query("
  SELECT id, nombre, ciudad
  FROM sedes
  ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

/* NIVELES ACTIVOS */
$niveles = $pdo->query("
  SELECT id, nombre
  FROM niveles_formacion
  WHERE estado = 'Activo'
  ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

/* GRUPOS ABIERTOS */
$grupos = $pdo->query("
  SELECT 
    g.*,
    s.nombre AS sede,
    n.nombre AS nivel,
    p.nombre AS programa
  FROM grupos g
  INNER JOIN sedes s ON s.id = g.sede_id
  INNER JOIN niveles_formacion n ON n.id = g.nivel_id
  INNER JOIN programas p ON p.id = g.programa_id
  WHERE g.estado = 'Inscripciones Abiertas'
  ORDER BY s.nombre, n.nombre, p.nombre
")->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="container my-5" id="oferta-educativa">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary">
            <i class="bi bi-mortarboard"></i> Oferta Educativa
        </h2>
        <p class="text-muted">
            Selecciona una sede y conoce nuestros programas disponibles
        </p>
    </div>

    <!-- TABS SEDES -->
    <ul class="nav nav-pills justify-content-center mb-4" id="tabsSedes">
        <?php foreach ($sedes as $i => $sede): ?>
            <li class="nav-item">
                <button
                    class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                    data-bs-toggle="pill"
                    data-bs-target="#sede-<?= $sede['id'] ?>"
                    type="button"
                >
                    <div class="text-center lh-sm">
                        <div class="fw-bold">
                            <?= htmlspecialchars($sede['nombre']) ?>
                        </div>
                        <small class="text-center">
                            <i class="bi bi-geo-alt"></i>
                            <?= htmlspecialchars($sede['ciudad']) ?>
                        </small>
                    </div>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>



    <div class="tab-content">

    <?php foreach ($sedes as $i => $sede): ?>
        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
             id="sede-<?= $sede['id'] ?>">
    
            <!-- FILTRO POR NIVEL -->
            <div class="text-center mb-4">
            
                <div class="btn-group flex-wrap gap-2" 
                     id="niveles-sede-<?= $sede['id'] ?>">
            
                    <button class="btn btn-outline-primary btn-sm btn-nivel active"
                            data-nivel="all"
                            onclick="filtrarNivel(<?= $sede['id'] ?>, 'all', this)">
                        Todos
                    </button>
            
                    <?php foreach ($niveles as $nivel): ?>
                        <button class="btn btn-outline-primary btn-sm btn-nivel"
                                data-nivel="<?= $nivel['id'] ?>"
                                onclick="filtrarNivel(<?= $sede['id'] ?>, <?= $nivel['id'] ?>, this)">
                            <?= htmlspecialchars($nivel['nombre']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- LOADER -->
            <div class="loader-oferta" id="loader-sede-<?= $sede['id'] ?>">
                <div class="spinner-border text-primary"></div>
                <p class="small text-muted mt-2">Cargando ofertas...</p>
            </div>

    
            <!-- LISTADO DE OFERTAS -->
            <div class="row g-4" id="grupos-sede-<?= $sede['id'] ?>">
    
            <?php
            $hay = false;
            foreach ($grupos as $g):
                if ($g['sede_id'] != $sede['id']) continue;
                $hay = true;
            ?>
    
                <div class="col-12 grupo-card" data-nivel="<?= $g['nivel_id'] ?>">
    
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between">
    
                            <!-- INFO -->
                            <div>
                                <span class="badge bg-primary mb-2">
                                    <?= htmlspecialchars($g['nivel']) ?>
                                </span>
    
                                <h5 class="fw-bold mb-1 text-danger">
                                    <?= htmlspecialchars($g['programa']) ?>
                                </h5>
    
                                <p class="small mb-1">
                                    <i class="bi bi-clock text-primary"></i> <strong>Jornada:</strong> <?= $g['jornada'] ?>
                                </p>
    
                                <p class="small mb-1">
                                    <i class="bi bi-people text-primary"></i>
                                    <strong>Cupos disponibles:</strong> <?= $g['cupos_disponibles'] ?>
                                </p>
    
                                <?php if ($g['fecha_inicio']): ?>
                                    <p class="small text-muted">
                                        <i class="bi bi-calendar-event text-primary"></i>
                                        <strong>Inicia:</strong> <?= date('d/m/Y', strtotime($g['fecha_inicio'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
    
                            <!-- BOTONES -->
                            <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
                            
                                <button class="btn btn-primary btn-sm"
                                    onclick="verDetallesPublicos(<?= $g['id'] ?>)">
                                    <i class="bi bi-eye"></i> Ver detalles
                                </button>
                            
                                <button
                                  class="btn btn-danger btn-sm"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modalInscripcion"
                                
                                  data-grupo-id="<?= $g['id'] ?>"
                                  data-programa="<?= htmlspecialchars($g['programa']) ?>"
                                  data-nivel="<?= htmlspecialchars($g['nivel']) ?>"
                                  data-jornada="<?= htmlspecialchars($g['jornada']) ?>"
                                  data-sede="<?= htmlspecialchars($sede['nombre']) ?>"
                                  data-ciudad="<?= htmlspecialchars($sede['ciudad']) ?>"
                                  data-cupos="<?= $g['cupos_disponibles'] ?>"
                                
                                  onclick="abrirInscripcion(this)"
                                >
                                  <i class="bi bi-pencil-square"></i> Inscribirse
                                </button>

                            
                            </div>
    
                        </div>
                    </div>
    
                </div>
    
            <?php endforeach; ?>
    
            <?php if (!$hay): ?>
                <p class="text-center text-muted">
                    No hay ofertas disponibles en esta sede.
                </p>
            <?php endif; ?>
    
            </div>
        </div>
    <?php endforeach; ?>
    
    </div>

</section>

<div class="modal fade" id="modalDetalles" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" id="contenidoDetalles"></div>
  </div>
</div>


<div class="modal fade" id="modalInscripcion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md-custom">
    <div class="modal-content">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalTitulo">
          <i class="bi bi-pencil-square"></i> Proceso de Inscripción
        </h5>

        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- LOADER -->
        <div id="loaderModal" class="text-center py-4 d-none">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">Procesando información...</p>
        </div>

        <!-- MENSAJE INFO -->
        <div id="mensajeInfo" class="alert alert-info d-none">
          Por favor, llene el formulario cuidadosamente y verifique sus datos antes de continuar.
        </div>
        
        <div id="mensajeInfo" class="alert alert-info d-none">
          Por favor, ingrese su número de documento para continuar.
        </div>

        <!-- CONSULTA -->
        <form id="formConsultar">
          <input type="hidden" id="grupo_id" name="grupo_id">

          <label class="form-label">Tipo de documento</label>
          <select name="tipo_documento" class="form-select mb-3" required>
            <option value="">Seleccione</option>
            <option value="CC">Cédula</option>
            <option value="TI">Tarjeta de Identidad</option>
            <option value="CE">Cédula Extranjería</option>
            <option value="PASAPORTE">Pasaporte</option>
          </select>

          <label class="form-label">Número de documento</label>
          <input type="text" name="numero_documento" class="form-control mb-3" required>

          <button class="btn btn-danger w-100">
            <i class="bi bi-search"></i> Continuar
          </button>
        </form>

        <!-- FORM PERSONA -->
        <form id="formPersona" class="d-none mt-3">
          <input type="hidden" id="persona_id" name="persona_id">

          <label class="form-label">Nombres completos</label>
          <input type="text" name="nombres_completos" class="form-control mb-2" required>

          <label class="form-label">Teléfono</label>
          <input type="text" name="telefono" class="form-control mb-2">

          <label class="form-label">Correo electrónico</label>
          <input type="email" name="correo" class="form-control mb-3">

          <button class="btn btn-danger w-100">
            <i class="bi bi-save"></i> Guardar datos
          </button>
        </form>

        <!-- RESUMEN -->
        <div id="resumenFinal" class="d-none mt-4">
          <div class="alert alert-light border small mb-3">
            <i class="bi bi-shield-check text-success"></i>
            Verifique que la información sea correcta antes de confirmar su inscripción.
          </div>
          <div id="resumenContenido" class="small text-muted mb-3"></div>

          <button id="btnConfirmar" class="btn btn-danger w-100">
            <i class="bi bi-check-circle"></i> Confirmar inscripción
          </button>
        </div>

      </div>
    </div>
  </div>
</div>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function filtrarNivel(sedeId, nivelId, btn) {

    const contenedor = document.getElementById('grupos-sede-' + sedeId);
    const grupos = contenedor.querySelectorAll('.grupo-card');
    const botones = document.querySelectorAll('#niveles-sede-' + sedeId + ' .btn-nivel');
    const loader = document.getElementById('loader-sede-' + sedeId);

    /* 🔁 Estado activo botones */
    botones.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    /* 🔄 Mostrar loader */
    loader.classList.add('active');
    contenedor.style.opacity = '0.4';

    /* ⏳ Simular carga elegante */
    setTimeout(() => {

        grupos.forEach(card => {
            const nivelCard = card.dataset.nivel;

            if (nivelId === 'all' || nivelCard == nivelId) {
                card.classList.remove('oculto');
            } else {
                card.classList.add('oculto');
            }
        });

        loader.classList.remove('active');
        contenedor.style.opacity = '1';

    }, 350); // tiempo corto = sensación profesional
}
</script>

<script>
  const modalDetalles = new bootstrap.Modal(
    document.getElementById('modalDetalles')
  );

  function verDetallesPublicos(grupoId) {

    Swal.fire({
      title: 'Cargando información...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch('/detalles_programa.php?grupo_id=' + grupoId)
      .then(res => res.text())
      .then(html => {

        document.getElementById('contenidoDetalles').innerHTML = html;

        Swal.close();
        modalDetalles.show();
      })
      .catch(() => {
        Swal.fire(
          'Error',
          'No se pudo cargar la información del programa',
          'error'
        );
      });
  }
</script>


<script>
function cargarInscripcion(g) {
    document.getElementById('ins-programa').textContent = g.programa;
    document.getElementById('ins-nivel').textContent = g.nivel;
    document.getElementById('ins-jornada').textContent = g.jornada;
}
</script>

<script>
/* ================== REFERENCIAS ================== */
const loader = document.getElementById('loaderModal');
const mensaje = document.getElementById('mensajeInfo');
const formConsultar = document.getElementById('formConsultar');
const formPersona = document.getElementById('formPersona');
const resumen = document.getElementById('resumenFinal');
const resumenContenido = document.getElementById('resumenContenido');

const personaIdInput = document.getElementById('persona_id');
const grupoIdInput = document.getElementById('grupo_id');
const btnConfirmar = document.getElementById('btnConfirmar');

/* ================== HELPERS ================== */
function mostrarLoader(v = true) {
  loader.classList.toggle('d-none', !v);
}

function resetModal() {
  mostrarLoader(false);

  mensaje.classList.add('d-none');
  formConsultar.classList.remove('d-none');
  formPersona.classList.add('d-none');
  resumen.classList.add('d-none');

  resumenContenido.innerHTML = '';
  personaIdInput.value = '';
}

/* ================== ABRIR MODAL ================== */
let programaActual = null;

function abrirInscripcion(btn) {

  // Reset visual
  resetModal();

  // Leer datos del botón
  programaActual = {
    grupo_id: btn.dataset.grupoId,
    programa: btn.dataset.programa,
    nivel: btn.dataset.nivel,
    jornada: btn.dataset.jornada,
    sede: btn.dataset.sede,
    ciudad: btn.dataset.ciudad,
    cupos: btn.dataset.cupos
  };

  // Guardar grupo_id en el form
  document.getElementById('grupo_id').value = programaActual.grupo_id;

  // 🔹 TÍTULO DEL MODAL
  document.getElementById('modalTitulo').innerHTML = `
    <i class="bi bi-mortarboard"></i> ${programaActual.programa}
  `;

  // 🔹 MENSAJE INICIAL
  const mensaje = document.getElementById('mensajeInfo');
  mensaje.innerHTML = `
    <i class="bi bi-info-circle"></i>
    Por favor, ingrese su número de documento para continuar.
  `;
  mensaje.classList.remove('d-none');
}


/* ================== CONSULTAR PERSONA ================== */
formConsultar.addEventListener('submit', e => {
  e.preventDefault();
  mostrarLoader(true);

  fetch('/consultar_persona.php', {
    method: 'POST',
    body: new FormData(formConsultar)
  })
  .then(r => r.json())
  .then(d => {
    mostrarLoader(false);

    // OCULTAR CONSULTA SIEMPRE
    formConsultar.classList.add('d-none');

    if (d.existe) {
      personaIdInput.value = d.persona.id;

      mensaje.innerHTML = `
        <i class="bi bi-check-circle"></i>
        Tus datos ya se encuentran registrados en nuestras bases de datos.
      `;
      mensaje.classList.remove('d-none');

      renderResumen(d.persona, d.programa);
    } else {
      mensaje.classList.remove('d-none');
      formPersona.classList.remove('d-none');
    }
  });
});


/* ================== REGISTRAR PERSONA ================== */
formPersona.addEventListener('submit', e => {
  e.preventDefault();
  mostrarLoader(true);

  const fd = new FormData(formPersona);
  new FormData(formConsultar).forEach((v, k) => fd.append(k, v));
  fd.append('grupo_id', grupoIdInput.value);

  fetch('/registrar_persona.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(d => {
    mostrarLoader(false);

    personaIdInput.value = d.persona.id;
    formPersona.classList.add('d-none');
    mensaje.classList.add('d-none');

    renderResumen(d.persona, d.programa);
  })
  .catch(() => {
    mostrarLoader(false);
    Swal.fire('Error', 'No se pudo registrar la persona', 'error');
  });
});

/* ================== RESUMEN ================== */
function renderResumen(persona, programa) {

  resumenContenido.innerHTML = `
    <!-- ASPIRANTE -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold text-danger mb-3">
          <i class="bi bi-person-badge"></i> Datos del aspirante
        </h6>

        <div class="row small">
          <div class="col-12 col-md-6 mb-2">
            <i class="bi bi-person-fill text-muted"></i>
            <strong>Nombre:</strong><br>
            ${persona.nombres_completos}
          </div>

          <div class="col-12 col-md-6 mb-2">
            <i class="bi bi-credit-card text-muted"></i>
            <strong>Documento:</strong><br>
            ${persona.tipo_documento} ${persona.numero_documento}
          </div>

          ${persona.telefono ? `
          <div class="col-12 col-md-6 mb-2">
            <i class="bi bi-telephone text-muted"></i>
            <strong>Teléfono:</strong><br>
            ${persona.telefono}
          </div>` : ''}

          ${persona.correo ? `
          <div class="col-12 col-md-6 mb-2">
            <i class="bi bi-envelope text-muted"></i>
            <strong>Correo:</strong><br>
            ${persona.correo}
          </div>` : ''}
        </div>
      </div>
    </div>

    <!-- PROGRAMA -->
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold text-danger mb-3">
          <i class="bi bi-mortarboard"></i> Programa seleccionado
        </h6>

        <div class="row small">
          <div class="col-12 mb-2">
            <strong class="text-dark fs-6">
              ${programa.programa}
            </strong>
          </div>

          <div class="col-6 mb-2">
            <i class="bi bi-diagram-3 text-muted"></i>
            <strong>Nivel:</strong><br>
            ${programa.nivel}
          </div>

          <div class="col-6 mb-2">
            <i class="bi bi-people text-muted"></i>
            <strong>Grupo:</strong><br>
            ${programa.grupo}
          </div>

          <div class="col-6 mb-2">
            <i class="bi bi-clock text-muted"></i>
            <strong>Jornada:</strong><br>
            ${programa.jornada}
          </div>

          <div class="col-6 mb-2">
            <i class="bi bi-geo-alt text-muted"></i>
            <strong>Sede:</strong><br>
            ${programa.sede} (${programa.ciudad})
          </div>

          <div class="col-12 mt-2">
            <i class="bi bi-hourglass-split text-muted"></i>
            <strong>Duración:</strong>
            ${programa.duracion} ${programa.tipo_duracion}
          </div>
        </div>
      </div>
    </div>
  `;

  resumen.classList.remove('d-none');
}



/* ================== CONFIRMAR INSCRIPCIÓN ================== */
btnConfirmar.addEventListener('click', () => {
  mostrarLoader(true);

  const fd = new FormData();
  fd.append('persona_id', personaIdInput.value);
  fd.append('grupo_id', grupoIdInput.value);

  fetch('/guardar_inscripcion.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(d => {
    mostrarLoader(false);

    if (!d.success) {
      Swal.fire({
        icon: 'warning',
        title: 'No fue posible completar la inscripción',
        text: d.message
      });
      return;
    }

    Swal.fire({
      icon: 'success',
      title: 'Preinscripción registrada',
      html: `
        <p>Tu preinscripción fue registrada correctamente.</p>
        <div class="alert alert-light border mt-3">
          <strong>Código de inscripción:</strong><br>
          <span class="fw-bold fs-5 text-danger">${d.codigo}</span>
        </div>
        <p class="small text-muted mt-3">
          Guarda este código. Serás contactado por correo electrónico.
        </p>
      `,
      confirmButtonText: 'Aceptar',
      allowOutsideClick: false,
      allowEscapeKey: false
    }).then(() => {

      // 🔒 Cerrar modal de inscripción
      const modalEl = document.getElementById('modalInscripcion');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();

      // 🧹 Resetear estado interno del modal
      resetModal();

      // 🔄 Recargar página (actualiza cupos y tarjetas)
      location.reload();
    });
  })
  .catch(() => {
    mostrarLoader(false);
    Swal.fire('Error', 'No se pudo completar la inscripción', 'error');
  });
});

</script>







<?php include __DIR__ . '/includes/contacto.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>