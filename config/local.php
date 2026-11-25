<?php

use Database\Seeders\AppAccessSeeder;

// Mode lokal tanpa SSO (docs/lokal-tanpa-sso.md): paket pengguna demo di halaman masuk lokal dan app:local-setup.
$app = env('ACCESS_APP', 'core');

return [
    'seeders' => [AppAccessSeeder::class],

    // label: teks di halaman masuk lokal; roles: role yang diberikan
    'plans' => [
        'user' => ['label' => "Pengguna ($app.user)", 'roles' => ["$app.user"]],
        'admin' => ['label' => "Admin ($app.admin)", 'roles' => ["$app.admin"]],
        'super' => ['label' => 'Super-admin', 'roles' => ['super-admin']],
    ],

    // Kata sandi semua akun demo (LocalDemoSeeder)
    'demo_password' => env('LOCAL_DEMO_PASSWORD', 'password'),

    // [nama, username, paket] yang dibuat LocalDemoSeeder (lewat app:local-setup)
    'demo_users' => [
        ['Admin Lokal', 'admin', 'super'],
        ['Pengguna Demo', 'demo', 'user'],
    ],
];
