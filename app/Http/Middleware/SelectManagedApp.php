<?php

namespace App\Http\Middleware;

use App\Support\ManagedApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// ?app=money memilih app yang dikelola di halaman Users & Roles, lalu disimpan di session
class SelectManagedApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $app = $request->query('app');

        if (is_string($app) && $request->user()) {
            abort_unless(ManagedApp::canManage($request->user(), $app), 403, 'Kamu tidak bisa mengelola aplikasi ini.');
            $request->session()->put('managed_app', $app);
        }

        return $next($request);
    }
}
