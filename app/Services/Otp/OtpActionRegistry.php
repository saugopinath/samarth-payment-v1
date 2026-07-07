<?php

namespace App\Services\Otp;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OtpActionRegistry
{
    protected array $handlers = [];

    /**
     * Register a callback for a specific OTP context.
     * The callback should take a User model and return a string URL to redirect to.
     */
    public function register(string $context, \Closure $handler): void
    {
        $this->handlers[$context] = $handler;
    }

    /**
     * Execute the handler for the given context and return the redirect URL.
     */
    public function handle(?string $context, User $user): array
    {
        if (is_null($context) || !isset($this->handlers[$context])) {
            return [
                'url' => route('dashboard', absolute: false),
                'type' => 'navigate'
            ];
        }

        $result = $this->handlers[$context]($user);

        return [
            'url' => $result,
            'type' => 'navigate'
        ];
    }
}
