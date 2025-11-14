<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'admin@maawa.example')->first();

dump($user);

dump(Illuminate\Support\Facades\Hash::check('admin123', $user->password));
