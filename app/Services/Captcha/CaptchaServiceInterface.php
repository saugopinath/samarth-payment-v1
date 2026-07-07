<?php

namespace App\Services\Captcha;

interface CaptchaServiceInterface
{
    /**
     * Render the Captcha HTML output (e.g. image tag).
     *
     * @param array $attributes Additional HTML attributes for the rendered element.
     * @return string
     */
    public function render(array $attributes = []): string;

    /**
     * Validate the given captcha code.
     *
     * @param string $code
     * @return bool
     */
    public function validate(string $code): bool;

    /**
     * Get the JavaScript necessary to refresh the captcha without a page reload.
     *
     * @return string
     */
    public function getRefreshScript(): string;
}
