<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Cache bersama semua app di Supabase (store "shared" di config/cache.php), dipakai cache permission Spatie.
// Idempoten seperti tabel pusat lain; down() kosong supaya rollback satu app tidak menghapusnya.
return new class extends Migration
{
    protected $connection = 'supabase';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('shared_cache')) {
            $schema->create('shared_cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration')->index();
            });
        }

        if (! $schema->hasTable('shared_cache_locks')) {
            $schema->create('shared_cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration')->index();
            });
        }

        // Tutup akses REST API publik Supabase, sama seperti tabel auth lain
        if (DB::connection($this->connection)->getDriverName() === 'pgsql') {
            foreach (['shared_cache', 'shared_cache_locks'] as $table) {
                DB::connection($this->connection)->statement("ALTER TABLE public.\"$table\" ENABLE ROW LEVEL SECURITY");
            }
        }
    }

    public function down(): void
    {
        // Tabel pusat di Supabase dipakai semua app: rollback dari satu app tidak boleh menghapusnya
    }
};
