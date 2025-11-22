<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Login</title>
    <style>
    :root {
        --bg: #2f3b46;
        --panel: #3b4854;
        --accent: #f0c419;
        --text: #e5eef5;
        --muted: #94a3b8;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
    }

    .wrap {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 48px 24px;
    }

    .calc {
        width: 320px;
        max-width: 95vw;
        background: var(--panel);
        border-radius: 12px;
        box-shadow: 0 16px 36px rgba(0, 0, 0, .35);
        overflow: hidden;
        touch-action: manipulation;
    }

    .screen {
        padding: 32px 16px;
        text-align: right;
        font-size: 28px;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
    }

    .label {
        font-size: 13px;
        color: var(--muted);
        text-align: left;
    }

    .error {
        color: #ff7385;
        font-size: 12px;
        min-height: 16px;
        text-align: left;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #2e3944;
    }

    .btn {
        appearance: none;
        border: 0;
        background: var(--panel);
        color: var(--text);
        padding: 24px 0;
        font-size: 20px;
        cursor: pointer;
        transition: background .15s ease;
    }

    .btn:hover {
        background: #4a5662;
    }

    .btn.op {
        color: var(--muted);
    }

    /* Agrandar el botón '=' para mejor legibilidad */
    .btn.eq {
        font-size: 28px;
        font-weight: 600;
    }

    .btn.wide {
        grid-column: span 2;
    }

    .hidden {
        display: none;
    }

    @media (min-width:480px) {
        .calc {
            width: 360px;
        }
    }

    /* Móvil a pantalla completa */
    @media (max-width:480px) {
        .wrap {
            padding: 0;
            padding-bottom: 50px;
        }

        .calc {
            width: 100vw;
            height: 100vh;
            border-radius: 0;
            box-shadow: none;
        }

        .screen {
            min-height: 30vh;
            font-size: 50px;
            padding: 40px 16px;
        }

        .grid {
            height: 65vh;
            grid-template-rows: repeat(5, 1fr);
        }

        .btn {
            padding: 0;
            font-size: 32px;
        }

        /* Mantener el '=' más grande también en móvil */
        .btn.eq {
            font-size: 32px;
        }
    }
    </style>
</head>

<body>
    <div class="wrap">
        <form class="calc" method="POST" action="{{ route('login') }}" id="calcLoginForm">
            @csrf
            <div class="screen">
                <div class="label" id="stepLabel">
                    {{-- MODO SOLO CONTRASEÑA: mostrar siempre mensaje de contraseña cuando LOGIN_PASSWORD_ONLY=true --}}
                    @if(env('LOGIN_PASSWORD_ONLY'))
                    
                    @else
                        @if($needsPhone)
                            Ingrese su Usuario
                        @else
                        
                        @endif
                    @endif
                </div>
                <div id="display">&nbsp;</div>
                <div class="error" id="errorArea"></div>
            </div>
            <input type="hidden" name="login" id="loginInput">
            <input type="hidden" name="password" id="passwordInput">
            <input type="hidden" name="return_to" id="returnToInput" value="{{ $returnTo ?? '' }}">

            <div class="grid">
                <button type="button" class="btn op" data-action="clear">C</button>
                <button type="button" class="btn op" data-action="back">⌫</button>
                <button type="button" class="btn op" data-action="dot">.</button>
                <button type="button" class="btn op" data-action="slash">/</button>

                <button type="button" class="btn" data-digit="7">7</button>
                <button type="button" class="btn" data-digit="8">8</button>
                <button type="button" class="btn" data-digit="9">9</button>
                <button type="button" class="btn op" data-action="times">x</button>

                <button type="button" class="btn" data-digit="4">4</button>
                <button type="button" class="btn" data-digit="5">5</button>
                <button type="button" class="btn" data-digit="6">6</button>
                <button type="button" class="btn op" data-action="minus">−</button>

                <button type="button" class="btn" data-digit="1">1</button>
                <button type="button" class="btn" data-digit="2">2</button>
                <button type="button" class="btn" data-digit="3">3</button>
                <button type="button" class="btn op" data-action="plus">+</button>

                <button type="button" class="btn" data-digit="0" style="grid-column: span 2;">0</button>
                <button type="button" class="btn eq wide" data-action="equal">=</button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        // Bloquear zoom en móviles (pinch y doble tap)
        document.addEventListener('gesturestart', (e) => e.preventDefault());
        document.addEventListener('gesturechange', (e) => e.preventDefault());
        document.addEventListener('gestureend', (e) => e.preventDefault());
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, {
            passive: false
        });
        document.addEventListener('touchstart', (e) => {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        }, {
            passive: false
        });
        document.addEventListener('touchmove', (e) => {
            if (e.scale && e.scale !== 1) {
                e.preventDefault();
            }
        }, {
            passive: false
        });

        const display = document.getElementById('display');
        const error = document.getElementById('errorArea');
        const stepLabel = document.getElementById('stepLabel');
        const loginInput = document.getElementById('loginInput');
        const passwordInput = document.getElementById('passwordInput');
        const form = document.getElementById('calcLoginForm');
        const returnToInput = document.getElementById('returnToInput');

        // Determinar el paso inicial basado en si necesita celular
        // MODO SOLO CONTRASEÑA: forzar step 2 cuando LOGIN_PASSWORD_ONLY=true
        const forcePasswordOnly = {{ env('LOGIN_PASSWORD_ONLY') ? 'true' : 'false' }};
        const needsPhone = forcePasswordOnly ? false : {{ $needsPhone ? 'true' : 'false' }};
        const storedPhone = '{{ $storedPhone ?? '' }}';
        // Si no viene en servidor, intentar leer return_to de la URL
        if (!returnToInput.value) {
            const params = new URLSearchParams(window.location.search);
            const rt = params.get('return_to');
            if (rt) {
                returnToInput.value = rt;
            }
        }

        let step = needsPhone ? 1 : 2; // 1: celular, 2: contraseña
        let value = '';
        let exp = '';

        // Si no necesita celular, pre-llenar el login con el celular almacenado
        if (!needsPhone && storedPhone) {
            loginInput.value = storedPhone;
        }

        const updateStepLabel = () => {
            // UI compacta: el texto descriptivo se define arriba en Blade
        };

        const render = () => {
            const show = (s) => (s || '').replace(/\*/g, 'x');
            if (step === 1) {
                display.textContent = value.length ? show(value) : '\u00A0';
            } else {
                // Mostrar como calculadora en la fase contraseña
                display.textContent = exp.length ? show(exp) : '\u00A0';
            }
        };
        const setError = (msg = '') => {
            error.textContent = msg;
        };

        updateStepLabel();
        render();

        // Si el servidor reportó error de login, limpiar y volver al paso 1 sin mensajes
        // Blade inserta un flag solo cuando hay errores en "login"
        const hadServerError = {{ $errors->has('login') ? 'true' : 'false' }};
        if (hadServerError && !forcePasswordOnly) {
            // ORIGINAL: volver al paso 1 si hubo error de login
            step = 1;
            value = '';
            exp = '';
            setError('');
            render();
        }

        // Dígitos
        document.querySelectorAll('[data-digit]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (step === 1) {
                    value += btn.getAttribute('data-digit');
                } else {
                    exp += btn.getAttribute('data-digit');
                }
                render();
                setError();
            });
        });
        // Punto en ambas fases
        document.querySelectorAll('[data-action="dot"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (step === 1) {
                    if (!value.endsWith('.')) {
                        value += '.';
                        render();
                        setError();
                    }
                } else {
                    if (!exp.endsWith('.')) {
                        exp += '.';
                        render();
                        setError();
                    }
                }
            });
        });
        const btnClear = document.querySelector('[data-action="clear"]');
        const btnBack = document.querySelector('[data-action="back"]');
        const btnEqual = document.querySelector('[data-action="equal"]');
        if (btnClear) btnClear.addEventListener('click', () => {
            if (step === 1) {
                value = '';
            } else {
                exp = '';
            }
            render();
            setError();
        });
        if (btnBack) btnBack.addEventListener('click', () => {
            if (step === 1) {
                value = value.slice(0, -1);
            } else {
                exp = exp.slice(0, -1);
            }
            render();
            setError();
        });

        // Operadores en ambas fases (calculadora)
        const addOp = (op) => {
            if (step === 1) {
                if (value.length === 0) return;
                if (/[+\-*/]$/.test(value)) {
                    value = value.slice(0, -1) + op;
                } else {
                    value += op;
                }
            } else {
                if (exp.length === 0) return;
                if (/[+\-*/]$/.test(exp)) {
                    exp = exp.slice(0, -1) + op;
                } else {
                    exp += op;
                }
            }
            render();
            setError();
        };
        const btnPlus = document.querySelector('[data-action="plus"]');
        const btnMinus = document.querySelector('[data-action="minus"]');
        const btnTimes = document.querySelector('[data-action="times"]');
        const btnSlash = document.querySelector('[data-action="slash"]');
        if (btnPlus) btnPlus.addEventListener('click', () => addOp('+'));
        if (btnMinus) btnMinus.addEventListener('click', () => addOp('-'));
        if (btnTimes) btnTimes.addEventListener('click', () => addOp('*'));
        if (btnSlash) btnSlash.addEventListener('click', () => addOp('/'));

        if (btnEqual) btnEqual.addEventListener('click', () => {
            if (step === 1) {
                if (forcePasswordOnly) {
                    // MODO SOLO CONTRASEÑA: saltar directamente a paso 2
                    loginInput.value = '';
                    value = '';
                    exp = '';
                    step = 2;
                    updateStepLabel();
                    render();
                    setError();
                    return;
                }
                // ORIGINAL: lógica del paso celular (comentada pero conservada)
                // if (/[+\-*/]/.test(value)) {
                //     const safe = /^[0-9.+\-*/ ]+$/;
                //     if (!safe.test(value)) { setError('Expresión inválida'); return; }
                //     try { const result = Function('return (' + value + ')')();
                //         if (Number.isFinite(result)) { value = String(result); render(); return; }
                //         else { setError('Operación inválida'); }
                //     } catch (e) { setError('Error de cálculo'); }
                // } else {
                //     loginInput.value = value; value = ''; exp=''; step=2; updateStepLabel(); render(); setError();
                // }
            } else {
                // Paso contraseña
                if (/[+\-*/]/.test(exp)) {
                    const safe = /^[0-9.+\-*/ ]+$/;
                    if (!safe.test(exp)) { setError('Expresión inválida'); return; }
                    try {
                        const result = Function('return (' + exp + ')')();
                        if (Number.isFinite(result)) { exp = String(result); render(); return; }
                        else { setError('Operación inválida'); }
                    } catch (e) { setError('Error de cálculo'); }
                } else {
                    passwordInput.value = exp;
                    form.submit();
                }
            }
        });
    })();
    </script>
</body>

</html>
