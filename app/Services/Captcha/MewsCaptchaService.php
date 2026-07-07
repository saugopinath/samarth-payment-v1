<?php

namespace App\Services\Captcha;

class MewsCaptchaService implements CaptchaServiceInterface
{
    /**
     * @inheritDoc
     */
    public function render(array $attributes = []): string
    {
        // By default we use the 'math' preset.
        return captcha_img('math', $attributes);
    }

    /**
     * @inheritDoc
     */
    public function validate(string $code): bool
    {
        return app('captcha')->check($code);
    }

    /**
     * @inheritDoc
     */
    public function getRefreshScript(): string
    {
        return "document.getElementById('captcha-img').src = '/captcha/math?'+Math.random();";
    }
}
