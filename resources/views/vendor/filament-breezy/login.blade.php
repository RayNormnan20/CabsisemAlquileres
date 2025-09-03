<x-filament-breezy::auth-card action="authenticate">

    <div class="w-full flex justify-center">
        <x-filament::brand />
    </div>

    <h2 class="font-bold tracking-tight text-center text-2xl">
        {{ __('filament::login.heading') }}
    </h2>

    @if(session()->has('oidc_error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">{{ __('OIDC Connect error') }}</span> {{ __('Invalid account!') }}
        </div>
    @endif

    @if(config('system.login_form.is_enabled'))
        <div>
            @if(config("filament-breezy.enable_registration"))
                <p class="mt-2 text-sm text-center">
                    {{ __('filament-breezy::default.or') }}
                    <a class="text-primary-600" href="{{route(config('filament-breezy.route_group_prefix').'register')}}">
                        {{ strtolower(__('filament-breezy::default.registration.heading')) }}
                    </a>
                </p>
            @endif
        </div>

        {{ $this->form }}

        <x-filament::button type="submit" class="w-full">
            {{ __('filament::login.buttons.submit.label') }}
        </x-filament::button>

        {{-- Forgot password link removed as requested --}}
        {{-- <div class="text-center">
            <a class="text-primary-600 hover:text-primary-700" href="{{route(config('filament-breezy.route_group_prefix').'password.request')}}">{{ __('filament-breezy::default.login.forgot_password_link') }}</a>
        </div> --}}
    @endif

    @if(config('services.oidc.is_enabled'))
        <x-filament::button
            color="secondary"
            class="w-full"
            tag="a"
            :href="route('oidc.redirect')"
        >
            <div class="w-full flex items-center gap-2">
                <x-heroicon-o-login class="w-5 h-5" />
                {{ __('OIDC Connect') }}
            </div>
        </x-filament::button>
    @endif

    @if(config('filament-socialite.enabled'))
        <x-filament-socialite::buttons />
    @endif
</x-filament-breezy::auth-card>


<!-- <x-filament-breezy::auth-card action="authenticate">
    <div class="w-full flex justify-center">
        <x-filament::brand />
    </div>

    <h2 class="font-bold tracking-tight text-center text-2xl mb-4">
        {{ __('Acceso') }}
    </h2>

    <div 
        x-data="{
            input: '',
            step: 'celular',
            inputRef: null,
            setInputRef(el) {
                this.inputRef = el;
            },
            append(val) {
                this.input += val;
            },
            backspace() {
                this.input = this.input.slice(0, -1);
            },
            clear() {
                this.input = '';
            },
            next() {
                if (this.step === 'celular' && this.input.length > 0) {
                    this.$refs.hiddenCelular.value = this.input;
                    this.input = '';
                    this.step = 'password';
                } else if (this.step === 'password') {
                    this.$refs.hiddenPassword.value = this.input;
                    this.$el.closest('form').submit();
                }
            }
        }"
        class="flex flex-col items-center space-y-4"
    >
        {{-- Input visible tipo calculadora --}}
        <input 
            type="text"
            class="w-full text-center border border-gray-300 rounded px-4 py-2 text-xl"
            x-model="input"
            :placeholder="step === 'celular' ? 'Ingresa celular' : 'Ingresa contraseña'"
            :type="step === 'password' ? 'password' : 'text'"
            readonly
        />

        {{-- Inputs ocultos reales para el login --}}
        <input x-ref="hiddenCelular" type="hidden" name="data[celular]">
        <input x-ref="hiddenPassword" type="hidden" name="data[password]">
        <input type="hidden" name="data[remember]" value="true">

        {{-- Teclado tipo calculadora --}}
        <div class="grid grid-cols-3 gap-3 max-w-xs w-full">
            @foreach([1,2,3,4,5,6,7,8,9,'←',0,'C'] as $key)
                <button
                    type="button"
                    class="bg-gray-200 text-2xl font-bold py-4 rounded hover:bg-gray-300"
                    @click="
                        if ('{{ $key }}' === 'C') clear();
                        else if ('{{ $key }}' === '←') backspace();
                        else append('{{ $key }}');
                    "
                >
                    {{ $key }}
                </button>
            @endforeach
        </div>

        {{-- Botón = para avanzar de celular a password o enviar --}}
        <button
            type="button"
            class="w-full mt-4 py-3 bg-primary-600 text-white rounded hover:bg-primary-700 font-bold"
            @click="next()"
            x-text="step === 'celular' ? '=' : 'Ingresar'"
        ></button>

        <div class="text-center mt-2">
            <a class="text-primary-600 hover:text-primary-700" href="{{ route(config('filament-breezy.route_group_prefix').'password.request') }}">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </div>
</x-filament-breezy::auth-card> -->
