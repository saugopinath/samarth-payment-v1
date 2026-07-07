<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::first();

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/login', 'GET')
);
$request->setUserResolver(fn() => $user);
// Wait, that's not how you simulate auth.
// Let's just create a route in web.php to dump the redirect url.
