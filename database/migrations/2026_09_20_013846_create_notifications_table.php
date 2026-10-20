<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel auth terpusat di Supabase
    protected $connection = 'supabase';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel pusat: dilewati bila sudah ada
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            // app pemilik notifikasi (clip, money, ...), tabel dipakai bersama
            $table->string('app', 30)->index();
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tabel pusat: tidak di-drop dari app mana pun
    }
};
