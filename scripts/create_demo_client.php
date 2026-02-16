<?php

use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::updateOrCreate(
    ['email' => 'client@example.com'],
    [
        'name' => 'Client Demo',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]
);

echo "OK client_id={$user->id}\n";
