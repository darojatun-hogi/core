<?php

return [

    /*
    | Single Sign-On antar app di satu domain induk (mis. *.elips.id).
    |
    | Semua app memakai satu session: tabel sessions di Supabase, cookie session
    | bernama sama (SESSION_COOKIE), berlaku di domain induk (SESSION_DOMAIN=.elips.id),
    | dan APP_KEY yang sama supaya cookie bisa dibaca app lain.
    |
    | Hub (SSO_HUB=true) menangani login, register, verifikasi email, dan reset password.
    | App lain (client, default) mengarahkan halaman-halaman itu ke hub.
    */

    'hub' => (bool) env('SSO_HUB', false),

    'hub_url' => rtrim((string) env('SSO_HUB_URL', 'http://localhost:8000'), '/'),

    // Halaman awal app ini setelah login lewat hub (path di app ini)
    'home' => '/dashboard',

    // Tujuan redirect setelah login hanya boleh host ini atau subdomainnya. Default: domain cookie session
    // (SESSION_DOMAIN=.elips.id -> elips.id), atau host APP_URL bila SESSION_DOMAIN kosong (dev lokal)
    'allowed_domain' => env('SSO_ALLOWED_DOMAIN', ltrim((string) (env('SESSION_DOMAIN') ?: parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)), '.')),

    // Daftar app yang tampil di dashboard hub
    'apps' => [
        ['key' => 'money', 'name' => 'Money', 'url' => env('SSO_APP_MONEY_URL', 'https://money.elips.id'), 'icon' => 'icon-[tabler--wallet]'],
        ['key' => 'clip', 'name' => 'Clip', 'url' => env('SSO_APP_CLIP_URL', 'https://clip.elips.id'), 'icon' => 'icon-[tabler--movie]'],
        ['key' => 'portfolio', 'name' => 'Portfolio', 'url' => env('SSO_APP_PORTFOLIO_URL', 'https://portfolio.elips.id/admin'), 'icon' => 'icon-[tabler--id-badge-2]'],
    ],

];
