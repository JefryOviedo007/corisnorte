<?php
// 1. Iniciar sesión para poder acceder a $_SESSION['sede_id']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evitar bloqueos por tiempo o memoria
set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . "/../../../../config.php"; 
header('Content-Type: application/json');

// Capturamos la sede del usuario que realiza la importación
$sede_id_usuario = $_SESSION['sede_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_excel'])) {
    $file = $_FILES['archivo_excel']['tmp_name'];
    
    // 1. Detectar el delimitador automáticamente (pueden ser , o ;)
    $file_content = file_get_contents($file);
    $first_line = strtok($file_content, "\n");
    $delimitador = (strpos($first_line, ';') !== false) ? ';' : ',';

    if (($handle = fopen($file, "r")) !== FALSE) {
        $insertados = 0;
        $fila_numero = 0;

        // 2. Leer encabezados y limpiar BOM (caracteres invisibles de Excel)
        $headers = fgetcsv($handle, 1000, $delimitador);
        if ($headers) {
            // Limpiamos espacios y posibles caracteres raros al inicio de los nombres
            foreach ($headers as $key => $value) {
                $headers[$key] = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
            }

            // Mapeo dinámico según tu imagen
            $idx_nombres   = array_search('APELLIDOS Y NOMBRES', $headers);
            $idx_tipo_doc  = array_search('TD', $headers);
            $idx_documento = array_search('DOCUMENTO', $headers);
            $idx_telefono  = array_search('TELEFONO', $headers);
            $idx_correo    = array_search('CORREO', $headers);
            $idx_direccion = array_search('DIRECCION', $headers);
        }

        $pdo->beginTransaction();

        try {
            // Preparamos la sentencia SQL fuera del bucle para mayor eficiencia
            $sql = "INSERT INTO personas (
                        tipo_documento, 
                        numero_documento, 
                        nombres_completos, 
                        telefono, 
                        correo, 
                        direccion, 
                        sede_id,
                        estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Prospecto')";
            
            $stmt = $pdo->prepare($sql);

            while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                $fila_numero++;

                // Si la fila está vacía o no tiene suficientes columnas, saltar
                if (count($data) < 2) continue;

                $nombres    = ($idx_nombres !== false)   ? mb_strtoupper(trim($data[$idx_nombres] ?? '')) : '';
                $tipo_doc   = ($idx_tipo_doc !== false)  ? trim($data[$idx_tipo_doc] ?? 'CC') : 'CC';
                $num_doc    = ($idx_documento !== false) ? trim($data[$idx_documento] ?? '') : '';
                $telefono   = ($idx_telefono !== false)  ? trim($data[$idx_telefono] ?? '') : '';
                $correo     = ($idx_correo !== false)    ? strtolower(trim($data[$idx_correo] ?? '')) : '';
                $direccion  = ($idx_direccion !== false) ? trim($data[$idx_direccion] ?? '') : '';

                // Validar que el número de documento no sea nulo para insertar
                if (empty($num_doc)) continue;

                // Ejecutamos la inserción incluyendo el sede_id capturado
                $stmt->execute([
                    $tipo_doc, 
                    $num_doc, 
                    $nombres, 
                    $telefono, 
                    $correo, 
                    $direccion, 
                    $sede_id_usuario
                ]);
                
                $insertados++;
            }

            $pdo->commit();
            fclose($handle);

            echo json_encode([
                'status' => 'success',
                'insertados' => $insertados,
                'message' => "Se procesaron $fila_numero filas y se agregaron $insertados registros."
            ]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo.']);
    }
}