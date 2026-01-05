<?php
require_once __DIR__ . "/../config.php";

/* ==========================
   CONSULTA INSTITUCIÓN
========================== */
$stmtInstitucion = $pdo->prepare("SELECT * FROM institucion LIMIT 1");
$stmtInstitucion->execute();
$institucion = $stmtInstitucion->fetch(PDO::FETCH_ASSOC);

/* ==========================
   CONSULTA SEDES ACTIVAS
========================== */
$stmtSedes = $pdo->prepare("SELECT * FROM sedes WHERE estado = 'Activa' ORDER BY ciudad, nombre");
$stmtSedes->execute();
$sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>


<style>
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
</style>

<div class="container py-5">

    <!-- TITULO -->
    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary">Contáctanos</h2>
        <p class="text-muted fs-5">
            Estamos para orientarte y darte el norte a tu vida académica
        </p>
    </div>

    <div class="row g-4">

        <!-- INFORMACIÓN INSTITUCIONAL -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">

                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-building"></i> Información Institucional
                    </h5>

                    <?php if ($institucion): ?>
                        <a class="navbar-brand d-flex justify-content-center mb-3" style="width:100%;">
                            <img 
                                src="/../assets/img/logo.png" 
                                alt="Logo Corisnorte"
                                style="
                                    max-width: 220px;
                                    max-height: 120px;
                                    width: auto;
                                    height: auto;
                                "
                            >
                        </a>

                        <p class="mb-2 text-danger text-center">
                            <strong><?= htmlspecialchars($institucion['nombre']) ?></strong>
                        </p>

                        <p class="mb-2">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <?= htmlspecialchars($institucion['direccion']) ?><br>
                            <?= htmlspecialchars($institucion['ciudad']) ?>
                        </p>

                        <p class="mb-2">
                            <i class="bi bi-telephone text-primary"></i>
                            <?= htmlspecialchars($institucion['telefono']) ?>
                        </p>

                        <p class="mb-0">
                            <i class="bi bi-envelope text-primary"></i>
                            <?= htmlspecialchars($institucion['correo']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted">Información institucional no disponible.</p>
                    <?php endif; ?>

                    <hr>

                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-diagram-3"></i> Nuestras Sedes
                    </h6>

                    <?php if ($sedes): ?>
                        <?php foreach ($sedes as $sede): ?>
                            <div class="mb-3 p-2 border rounded">
                                <strong class="text-danger"><?= htmlspecialchars($sede['nombre']) ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($sede['ciudad']) ?>
                                </small><br>

                                <?php if ($sede['direccion']): ?>
                                    <small>
                                        <i class="bi bi-geo-alt"></i>
                                        <?= htmlspecialchars($sede['direccion']) ?>
                                    </small><br>
                                <?php endif; ?>

                                <?php if ($sede['telefono']): ?>
                                    <small>
                                        <i class="bi bi-telephone"></i>
                                        <?= htmlspecialchars($sede['telefono']) ?>
                                    </small><br>
                                <?php endif; ?>

                                <?php if ($sede['correo']): ?>
                                    <small>
                                        <i class="bi bi-envelope"></i>
                                        <?= htmlspecialchars($sede['correo']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay sedes activas registradas.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- FORMULARIO DE CONTACTO -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-chat-dots"></i> Escríbenos
                    </h5>

                    <form id="formContacto">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Asunto</label>
                                <input type="text" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mensaje</label>
                                <textarea class="form-control" rows="5" required></textarea>
                            </div>

                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send"></i> Enviar mensaje
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

