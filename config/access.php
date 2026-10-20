<?php

return [

    /*
    | Nama app ini di skema role/permission bersama (Supabase). Role dan permission
    | milik app diberi awalan "<app>.", misalnya core.admin dan core.reports.view.
    | User hanya boleh masuk bila punya permission "<app>.access" (atau role super-admin).
    */

    'app' => env('ACCESS_APP', 'core'),

    'super_admin_role' => 'super-admin',

    // Opsional: akun yang otomatis diberi role "<app>.admin" oleh AppAccessSeeder
    'admin_email' => env('APP_ADMIN_EMAIL'),

    // Permission identitas yang dipakai bersama oleh semua app (tanpa awalan app)
    'shared_permissions' => [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        'activity-log.view',
    ],

];
