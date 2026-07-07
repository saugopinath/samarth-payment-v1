<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('users are redirected to otp page after entering correct credentials', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('verify-otp'));

    $this->assertGuest();
    $this->assertEquals($user->id, session('auth.otp_user_id'));
});

test('users can authenticate with correct otp', function () {
    $user = User::factory()->create();

    session(['auth.otp_user_id' => $user->id]);

    $otp = '123456';
    $user->update([
        'last_otp' => $otp,
        'last_otp_expire_time' => now()->addMinutes(10),
    ]);
    \App\Models\VerificationCode::create([
        'user_id' => $user->id,
        'otp' => $otp,
        'mobile_no' => $user->mobile_no,
        'expire_at' => now()->addMinutes(10),
    ]);

    $component = Volt::test('pages.auth.verify-otp')
        ->set('otp', '123456');

    $component->call('verify');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
