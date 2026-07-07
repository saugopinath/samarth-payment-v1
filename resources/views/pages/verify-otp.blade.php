<?php use function Laravel\Folio\{name, middleware}; name('verify-otp'); middleware(['guest']); ?>
<?php

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\Sms\SmsFactory;
use App\Rules\ValidCaptcha;
use App\Services\Captcha\CaptchaFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component
{
    public string $otp = '';
    public ?string $captcha = null;

    public function mount(): void
    {
        if (! session()->has('auth.otp_user_id')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    /**
     * Verify the submitted OTP.
     */
    public function verify(): void
    {
        try {
            $this->validate([
                'otp' => ['required', 'digits:6'],
                'captcha' => ['required', new ValidCaptcha()]
            ], [
                'otp.required' => 'Please enter the OTP.',
                'otp.digits' => 'The OTP must be 6 digits.',
                'captcha.required' => 'Please enter the captcha code.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('reload-captcha');
            throw $e;
        }

        $encryptedUserId = session('auth.otp_user_id');

        if (! $encryptedUserId) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        try {
            $userId = decrypt($encryptedUserId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $this->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ]);

        $user = User::find($userId);

        if (! $user) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $otpService = app(\App\Services\Otp\OtpService::class);

        if (! $otpService->verifyOtp($user, $this->otp)) {
            $this->dispatch('reload-captcha');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'otp' => __('The OTP is invalid or has expired.'),
            ]);
        }

        $context = session('auth.otp_context');
        session()->forget(['auth.otp_user_id', 'auth.otp_context']);

        $user->update([
            'last_otp' => null,
            'last_otp_generation_time' => null,
            'last_otp_expire_time' => null,
        ]);

        $registry = app(\App\Services\Otp\OtpActionRegistry::class);
        $action = $registry->handle($context, $user);

        if ($context !== 'reset_password') {
            // Default login context if not resetting password
            Auth::login($user, session('auth.remember', false));
            session()->forget('auth.remember');
            Session::regenerate();
            $user->update(['current_session_id' => session()->getId()]);
        }

        if ($action['type'] === 'redirect') {
            $this->redirect($action['url']);
        } else {
            if ($context !== 'reset_password') {
                $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            } else {
                $this->redirect($action['url'], navigate: true);
            }
        }
    }

    /**
     * Resend the OTP to the mobile number.
     */
    public function resend(): void
    {
        $encryptedUserId = session('auth.otp_user_id');

        if (! $encryptedUserId) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        try {
            $userId = decrypt($encryptedUserId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $user = User::find($userId);

        if (! $user) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $otpService = app(\App\Services\Otp\OtpService::class);
        if ($otpService->sendOtp($user)) {
            Session::flash('status', 'otp-sent');
            Session::flash('message', __('A new OTP code has been sent to your registered mobile number: ******' . substr($user->mobile_no, -4)));
        } else {
            Session::flash('status', 'error');
            Session::flash('message', __('Failed to send OTP. Please try again later.'));
        }
    }
}; ?>
@component('layouts.public.guest')
@volt

<div class="flex-grow grid grid-cols-1 lg:grid-cols-12 w-full min-h-[calc(100vh-160px)]">
    
    <!-- Left Side: Premium Pension Visual & Branding (Desktop only) -->
    <div class="lg:col-span-5 hidden lg:flex p-6 lg:p-8 lg:pr-4">
        <div class="relative w-full h-full flex flex-col justify-between p-12 text-white bg-cover bg-center rounded-3xl shadow-2xl overflow-hidden" style="background-image: url('{{ asset('images/otp_verification_bg.png') }}');">
            <!-- Dark Amber Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-amber-950/80 via-amber-900/50 to-amber-950/90 z-0"></div>
        </div>
    </div>

    <!-- Right Side: Forms & Headers -->
    <div class="lg:col-span-7 flex flex-col justify-center px-6 md:px-16 py-12">
        <!-- Form Card slot -->
        <div class="max-w-md w-full mx-auto py-4">
            <div class="bg-white rounded-2xl border border-amber-200/40 p-6 md:p-8 shadow-md transition duration-200">
                <div class="mb-4 text-sm text-slate-600">
                    {{ __('Please enter the 6-digit verification code (OTP) sent to your registered mobile number to access your account.') }}
                </div>

                <!-- Session Status / Flash Messages -->
                @if (session('status') == 'otp-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('message') }}
                    </div>
                @elseif (session('status') == 'error')
                    <div class="mb-4 font-medium text-sm text-red-600">
                        {{ session('message') }}
                    </div>
                @endif

                <form wire:submit="verify">
                    <!-- OTP -->
                    <div>
                        <x-input-label for="otp" :value="__('Verification Code (OTP)')" />
                        <x-text-input wire:model="otp" id="otp" class="block mt-1 w-full text-center tracking-widest text-lg font-bold" type="text" name="otp" required autofocus maxlength="6" autocomplete="one-time-code" />
                        <x-input-error :messages="$errors->get('otp')" class="mt-2" />
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
                            {{ __('Verify') }}
                        </x-primary-button>

                        <button wire:click.prevent="resend" type="button" class="text-center underline text-sm text-slate-500 hover:text-[#FF8800] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF8800] transition duration-150">
                            {{ __('Resend OTP Code') }}
                        </button>
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
