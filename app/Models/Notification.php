<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

// Tabel notifications terpusat di Supabase dan dipakai banyak app; kolom "app" memisahkan pemiliknya
class Notification extends DatabaseNotification
{
    protected $connection = 'supabase';

    protected static function booted(): void
    {
        static::creating(function (Notification $notification) {
            $notification->app ??= config('access.app');
        });
    }
}
