<?php use function Laravel\Folio\{name, middleware}; name('password.request'); middleware(['guest']); ?>
<?php

use App\Models\User;
use App\Models\VerificationCode;
use App\Rules\ValidCaptcha;
use App\Services\Captcha\CaptchaFactory;
use App\Services\Otp\OtpGenerator;
use App\Services\Sms\SmsFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component
{
    public string $mobile = '';
    public ?string $captcha = null;

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        try {
            $this->validate([
                'mobile' => ['required', 'digits:10'],
                'captcha' => ['required', new ValidCaptcha()]
            ], [
                'mobile.required' => 'Please enter the mobile number.',
                'mobile.digits' => 'The mobile number must be 10 digits.',
                'captcha.required' => 'Please enter the captcha code.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('reload-captcha');
            throw $e;
        }

        $fieldType = filter_var($this->mobile, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_no';
        $user = User::where($fieldType, $this->mobile)
            ->select('id', 'email', 'mobile_no', 'is_active')
            ->first();

        if (! $user) {
            $this->dispatch('reload-captcha');
            $this->addError('mobile', __('We can\'t find a user with that credential.'));
            return;
        }

        if (! $user->is_active) {
            $this->dispatch('reload-captcha');
            $this->addError('mobile', __('This account is inactive.'));
            return;
        }

        $otpService = app(\App\Services\Otp\OtpService::class);
        if (! $otpService->sendOtp($user)) {
            $this->dispatch('reload-captcha');
            throw ValidationException::withMessages([
                'mobile' => __('Failed to send OTP. Please try again later.'),
            ]);
        }

        session([
            'auth.otp_user_id' => encrypt($user->id),
            'auth.otp_context' => 'reset_password',
        ]);
        Session::flash('status', 'otp-sent');
        Session::flash('message', __('A OTP code has been sent to your registered mobile number: ******' . substr($this->mobile, -4)));
        $this->redirect(route('verify-otp'));
    }
}; ?>
@component('layouts.public.guest')
@volt

<div class="flex-grow grid grid-cols-1 lg:grid-cols-12 w-full min-h-[calc(100vh-160px)]">
    
    <!-- Left Side: Premium Pension Visual & Branding (Desktop only) -->
    <div class="lg:col-span-5 hidden lg:flex p-6 lg:p-8 lg:pr-4">
        <div class="relative w-full h-full flex flex-col justify-between p-12 text-white bg-cover bg-center rounded-3xl shadow-2xl overflow-hidden" style="background-image: url('{{ asset('images/forgot_password_bg.png') }}');">
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
                    {{ __('Forgot your password? No problem. Just let us know your mobile number and we will send you a secure OTP via SMS.') }}
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form wire:submit="sendPasswordResetLink">
                    <!-- Mobile Number -->
                    <div>
                        <x-input-label for="mobile" :value="__('Mobile Number')" />
                        <x-text-input wire:model="mobile" id="mobile" class="block mt-1 w-full" type="text" name="mobile" required autofocus maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
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
                            {{ __('Send OTP') }}
                        </x-primary-button>

                        <a href="{{ route('login') }}" wire:navigate class="text-center underline text-sm text-slate-500 hover:text-[#FF8800] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF8800] transition duration-150 mt-2">
                            {{ __('Back to login') }}
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
