<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class NotRecentPassword implements ValidationRule
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Also check current password just in case it wasn't recorded
        if (Hash::check($value, $this->user->password)) {
            $fail('The new password cannot be the same as your current password.');
            return;
        }

        $histories = $this->user->passwordHistories;

        foreach ($histories as $history) {
            if (Hash::check($value, $history->password)) {
                $fail('The new password has been used recently. Please choose a different password.');
                return;
            }
        }
    }
}
