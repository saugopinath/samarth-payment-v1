<?php use function Laravel\Folio\{name, middleware}; name('password.reset.otp'); middleware(['guest']); ?>
<?php

use App\Models\User;
use App\Rules\ValidCaptcha;
use App\Services\Captcha\CaptchaFactory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component
{
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $captcha = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        if (! session()->has('auth.reset_password_user_id')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        try {
            $this->validate([
                'captcha' => ['required', new ValidCaptcha()]
            ], [
                'captcha.required' => 'Please enter the captcha code.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('reload-captcha');
            throw $e;
        }

        $encryptedUserId = session('auth.reset_password_user_id');

        try {
            $userId = decrypt($encryptedUserId);
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        try {
            $this->validate([
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults(), new \App\Rules\NotRecentPassword($user)],
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('reload-captcha');
            throw $e;
        }

        $user->forceFill([
            'password' => Hash::make($this->password),
            'remember_token' => Str::random(60),
        ])->save();

        $user->recordPasswordHistory($user->password);

        event(new PasswordReset($user));

        session()->forget('auth.reset_password_user_id');
        Session::flash('status', __('Password reset successfully. Please login with your new password.'));


        // Redirect user to login after successful password reset
        $this->redirectIntended(default: route('login', absolute: false), navigate: true);
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
                    {{ __('Your identity has been verified! Please choose a new secure password for your account.') }}
                </div>

                <form wire:submit="resetPassword">
                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('New Password')" />
                        <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" autofocus />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                      type="password"
                                      name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
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
                            {{ __('Reset Password') }}
                        </x-primary-button>
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
