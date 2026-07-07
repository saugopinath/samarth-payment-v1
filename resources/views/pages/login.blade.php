<?php use function Laravel\Folio\{name, middleware}; name('login'); middleware(['guest']); ?>
<?php

use App\Rules\ValidCaptcha;
use App\Services\Captcha\CaptchaFactory;
use App\Livewire\Forms\LoginForm;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\Otp\OtpGenerator;
use App\Services\Sms\SmsFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component
{
    public LoginForm $form;
    
    public ?string $captcha = null;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        try {
            $this->validate([
                'form.mobile' => ['required', 'digits:10'],
                'captcha' => ['required', new ValidCaptcha()]
            ], [
                'form.mobile.required' => 'Please enter the mobile number.',
                'form.mobile.digits' => 'The mobile number must be 10 digits.',
                'captcha.required' => 'Please enter the captcha code.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('reload-captcha');
            throw $e;
        }

        $this->validate(); // validates LoginForm

        $fieldType = filter_var($this->form->mobile, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_no';
        $user = User::where($fieldType, $this->form->mobile)
            ->select('id', 'email', 'mobile_no', 'password', 'is_active', 'bypass_otp', 'remember_token')
            ->first();

        if (! $user || ! Hash::check($this->form->password, $user->password)) {
            $this->dispatch('reload-captcha');
            throw ValidationException::withMessages([
                'form.mobile' => __('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            $this->dispatch('reload-captcha');
            throw ValidationException::withMessages([
                'form.mobile' => __('This account is inactive.'),
            ]);
        }

        // Check if user is allowed to bypass OTP
        if ($user->bypass_otp) {
            Auth::login($user, $this->form->remember);
            Session::regenerate();
            $user->update(['current_session_id' => session()->getId()]);
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        $otpService = app(\App\Services\Otp\OtpService::class);
        if (! $otpService->sendOtp($user)) {
            $this->dispatch('reload-captcha');
            throw ValidationException::withMessages([
                'form.mobile' => __('Failed to send OTP. Please try again later.'),
            ]);
        }

        // Store user details in session temporarily
        session([
            'auth.otp_user_id' => encrypt($user->id),
            'auth.remember' => $this->form->remember,
        ]);
        Session::flash('status', 'otp-sent');
        Session::flash('message', __('A OTP code has been sent to your registered mobile number: ******' . substr($this->form->mobile, -4)));

        // Redirect to OTP verification page
        $this->redirect(route('verify-otp'), navigate: true);
    }
}; ?>
@component('layouts.public.guest')
@volt

<div class="flex-grow grid grid-cols-1 lg:grid-cols-12 w-full min-h-[calc(100vh-160px)]">
    
    <!-- Left Side: Premium Pension Visual & Branding (Desktop only) -->
    <div class="lg:col-span-5 hidden lg:flex p-6 lg:p-8 lg:pr-4">
        <div class="relative w-full h-full flex flex-col justify-between p-12 text-white bg-cover bg-center rounded-3xl shadow-2xl overflow-hidden" style="background-image: url('{{ asset('images/samarth_beneficiaries_collage.png') }}');">
            <!-- Dark Amber Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-amber-950/80 via-amber-900/50 to-amber-950/90 z-0"></div>
            
       

          
        </div>
    </div>

    <!-- Right Side: Forms & Headers -->
    <div class="lg:col-span-7 flex flex-col justify-center px-6 md:px-16 py-12">
        <!-- Form Card slot -->
        <div class="max-w-md w-full mx-auto py-4">
            <div class="bg-white rounded-2xl border border-amber-200/40 p-6 md:p-8 shadow-md transition duration-200">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form wire:submit="login">
                    <!-- Mobile Number -->
                    <div>
                        <x-input-label for="mobile" :value="__('Mobile Number')" />
                        <x-text-input wire:model="form.mobile" id="mobile" class="block mt-1 w-full" type="text" name="mobile" required autofocus autocomplete="username" maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        
                        <x-input-error :messages="$errors->get('form.mobile')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password')" />

                        <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>

                    <!-- Captcha -->
                    <div class="mt-4">
                        <x-input-label for="captcha" :value="__('Security Code')" />
                        
                        <div class="mt-1 flex items-center gap-2">
                            <span class="flex-shrink-0 overflow-hidden rounded-md border border-slate-300 shadow-sm">
                                {!! CaptchaFactory::make()->render(['id' => 'captcha-img']) !!}
                            </span>
                            
                            <button type="button" onclick="{!! CaptchaFactory::make()->getRefreshScript() !!}" class="flex-shrink-0 p-2 text-slate-500 hover:text-[#FF8800] hover:bg-orange-50 rounded-full focus:outline-none transition-colors" title="{{ __('Refresh Security Code') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </button>
                            
                            <x-text-input wire:model="captcha" id="captcha" class="block w-full uppercase"
                                            type="text"
                                            name="captcha"
                                            required />
                        </div>

                        <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <x-primary-button>
                            {{ __('Log in') }}
                        </x-primary-button>

                        <a href="{{ url('/forgot-password') }}" wire:navigate class="inline-flex items-center justify-center px-6 py-3 w-full bg-[#FF9F1A] hover:bg-[#E68A00] active:scale-[0.98] border border-transparent rounded-xl font-display font-extrabold text-sm text-white uppercase tracking-wider shadow-sm hover:shadow-md focus:outline-none transition ease-in-out duration-150 text-center">
                            {{ __('Forgot Password') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('reload-captcha', () => {
        {!! CaptchaFactory::make()->getRefreshScript() !!}
    });
</script>
@endscript

@endvolt
@endcomponent
