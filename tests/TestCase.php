<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Kedua koneksi dibungkus transaksi; tanpa ini PDO in-memory supabase tidak di-cache RefreshDatabase dan kosong di test berikutnya
    protected array $connectionsToTransact = [null, 'supabase'];

    // Koneksi supabase (Postgres) diganti SQLite in-memory terpisah supaya test tidak menyentuh database auth pusat
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.connections.supabase', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        return $app;
    }
}
