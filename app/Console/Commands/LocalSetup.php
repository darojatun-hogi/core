<?php

namespace App\Console\Commands;

use Database\Seeders\LocalDemoSeeder;
use Illuminate\Console\Command;

/**
 * Siapkan database lokal (docs/lokal-tanpa-sso.md): migrasi, seeder role dari config/local.php, lalu pengguna demo
 * (LocalDemoSeeder). File ini sama di semua app Elips. Menolak berjalan kecuali APP_ENV=local, koneksi utama dan
 * "supabase" sama-sama database lokal (SQLite, atau MySQL di 127.0.0.1/localhost), dan keduanya menunjuk database yang
 * sama, supaya migrasi dan seeder tidak pernah mengenai database produksi.
 */
class LocalSetup extends Command
{
    protected $signature = 'app:local-setup {--fresh : Hapus semua tabel di database lokal lalu buat ulang}';

    protected $description = 'Siapkan database lokal dan pengguna demo untuk menjalankan app tanpa SSO';

    public function handle(): int
    {
        if ($problem = self::problem()) {
            $this->error($problem);

            return self::FAILURE;
        }

        $main = config('database.connections.'.config('database.default'));
        if ($main['driver'] === 'sqlite' && ! is_file($main['database'])) {
            touch($main['database']);
        }

        $this->call($this->option('fresh') ? 'migrate:fresh' : 'migrate', ['--force' => true]);
        foreach ([...config('local.seeders', []), LocalDemoSeeder::class] as $seeder) {
            $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        $this->info('Siap. Jalankan composer dev, buka /login, lalu masuk dengan akun demo di atas.');

        return self::SUCCESS;
    }

    /** Alasan menolak menulis database lokal, atau null bila aman. Dipakai juga oleh LocalDemoSeeder. */
    public static function problem(): ?string
    {
        if (! app()->environment('local')) {
            return 'Hanya untuk APP_ENV=local.';
        }

        $main = config('database.connections.'.config('database.default'));
        $auth = config('database.connections.supabase');
        foreach (['koneksi utama' => $main, 'koneksi "supabase"' => $auth] as $label => $c) {
            $local = ($c['driver'] ?? null) === 'sqlite' || in_array($c['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true);
            if (! $local) {
                return "$label bukan database lokal. Isi DB_LOCAL=true dan DB_HOST=127.0.0.1 di .env, lalu php artisan config:clear.";
            }
        }
        if (($main['database'] ?? null) !== ($auth['database'] ?? null)) {
            return 'Koneksi "supabase" belum memakai database lokal yang sama. Isi DB_LOCAL=true di .env, lalu php artisan config:clear.';
        }

        return null;
    }
}
