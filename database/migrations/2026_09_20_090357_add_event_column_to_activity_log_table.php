<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventColumnToActivityLogTable extends Migration
{
    // Tabel pusat di Supabase: dilewati bila kolom sudah ada, tidak diubah saat rollback
    public function up()
    {
        if (Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'event')) {
            return;
        }

        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->string('event')->nullable()->after('subject_type');
        });
    }

    public function down()
    {
        // Tabel pusat: tidak diubah dari app mana pun
    }
}
