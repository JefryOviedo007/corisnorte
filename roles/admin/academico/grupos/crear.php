<?php
// crear_grupo.php
// Este archivo maneja la creación de un nuevo grupo académico.

session_start();
require_once __DIR__ . "/../../../../config.php";

if (!isset($_SESSION['id'], $_SESSION['rol'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}

$rol = $_SESSION['rol'];
$sede_id_session = $_SESSION['sede_id'] ?? null;

// Solo permitir creación si es Admin, Coordinador o Secretaria
if (!in_array($rol, ['Admin', 'Coordinador', 'Secretaria'])) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para crear grupos.']);
    exit;
}

// Recibir datos del formulario
$sede_id = $_POST['sede_id'] ?? null;
$nivel_id = $_POST['nivel_id'] ?? null;
$programa_id = $_POST['programa_id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$jornada = $_POST['jornada'] ?? null;
$cupos = $_POST['cupos'] ?? null;
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;
$estado = $_POST['estado'] ?? 'Creado';

// Validaciones básicas
$errors = [];

if ($rol === 'Admin') {
    if (!$sede_id || !is_numeric($sede_id)) {
        $errors[] = 'Sede inválida.';
    }
} else {
    // Para Coordinador/Secretaria, usar sede de sesión
    $sede_id = $sede_id_session;
    if (!$sede_id) {
        $errors[] = 'Sede no definida en la sesión.';
    }
}

if (!$nivel_id || !is_numeric($nivel_id)) {
    $errors[] = 'Nivel inválido.';
}

if (!$programa_id || !is_numeric($programa_id)) {
    $errors[] = 'Programa inválido.';
}

if (empty($nombre)) {
    $errors[] = 'El nombre del grupo es obligatorio.';
}

$jornadas_validas = ['Mañana', 'Tarde', 'Noche', 'Fin de semana'];
if (!$jornada || !in_array($jornada, $jornadas_validas)) {
    $errors[] = 'Jornada inválida.';
}

if (!$cupos || !is_numeric($cupos) || $cupos < 1) {
    $errors[] = 'Cupos debe ser un número mayor a 0.';
}

$estados_validos = ['Creado', 'Inscripciones Abiertas', 'Inscripciones Cerradas'];
if (!in_array($estado, $estados_validos)) {
    $errors[] = 'Estado inválido.';
}

// Validar fechas si se proporcionan
if ($fecha_inicio && !strtotime($fecha_inicio)) {
    $errors[] = 'Fecha de inicio inválida.';
}
if ($fecha_fin && !strtotime($fecha_fin)) {
    $errors[] = 'Fecha de fin inválida.';
}
if ($fecha_inicio && $fecha_fin && strtotime($fecha_inicio) > strtotime($fecha_fin)) {
    $errors[] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Verificar que el nivel, programa y sede existan y estén activos (si aplica)
try {
    // Verificar sede
    $stmt = $pdo->prepare("SELECT id FROM sedes WHERE id = ?");
    $stmt->execute([$sede_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Sede no encontrada.']);
        exit;
    }

    // Verificar nivel
    $stmt = $pdo->prepare("SELECT id FROM niveles_formacion WHERE id = ? AND estado = 'Activo'");
    $stmt->execute([$nivel_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Nivel no encontrado o inactivo.']);
        exit;
    }

    // Verificar programa
    $stmt = $pdo->prepare("SELECT id FROM programas WHERE id = ? AND nivel_id = ? AND estado = 'Activo'");
    $stmt->execute([$programa_id, $nivel_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Programa no encontrado, no pertenece al nivel o está inactivo.']);
        exit;
    }

    // Insertar el grupo
    $sql = "INSERT INTO grupos (sede_id, nivel_id, programa_id, nombre, jornada, cupos, cupos_disponibles, fecha_inicio, fecha_fin, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $cupos_disponibles = $cupos; // Inicializar cupos disponibles igual a cupos
    $stmt->execute([$sede_id, $nivel_id, $programa_id, $nombre, $jornada, $cupos, $cupos_disponibles, $fecha_inicio ?: null, $fecha_fin ?: null, $estado]);

    echo json_encode(['success' => true, 'message' => 'Grupo creado correctamente.']);

} catch (PDOException $e) {
    // Log del error si es necesario
    echo json_encode(['success' => false, 'message' => 'Error al crear el grupo: ' . $e->getMessage()]);
}
?>
