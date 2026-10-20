<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    // Tabel pusat di Supabase (dipakai semua app): dilewati bila sudah ada, tidak di-drop saat rollback
    public function up()
    {
        if (Schema::connection(config('activitylog.database_connection'))->hasTable(config('activitylog.table_name'))) {
            return;
        }

        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        // Tabel pusat: tidak di-drop dari app mana pun
    }
}
