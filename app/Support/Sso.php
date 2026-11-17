<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class Sso
{
    public static function isHub(): bool
    {
        return (bool) config('sso.hub');
    }

    // URL halaman di hub, mis. Sso::hubUrl('login', ['redirect' => ...])
    public static function hubUrl(string $path = '', array $query = []): string
    {
        $url = config('sso.hub_url').'/'.ltrim($path, '/');

        return $query ? $url.'?'.http_build_query($query) : $url;
    }

    // Kembalikan $url bila aman dipakai sebagai tujuan redirect (domain induk atau subdomainnya), selain itu null
    public static function safeRedirect(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $domain = strtolower(ltrim((string) config('sso.allowed_domain'), '.'));

        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || $host === '' || $domain === '') {
            return null;
        }

        return $host === $domain || str_ends_with($host, '.'.$domain) ? $url : null;
    }

    // Logout di app client diteruskan ke hub lewat URL ini, supaya hub juga menghapus cookie lama miliknya
    // (lihat forgetHostCookies). Ditandatangani dengan APP_KEY bersama, berlaku 2 menit: situs lain tidak bisa
    // membuat tautan yang memaksa pengguna logout.
    public static function logoutUrl(string $redirect): string
    {
        $expires = now()->addMinutes(2)->timestamp;

        return self::hubUrl('sso/logout', [
            'redirect' => $redirect,
            'expires' => $expires,
            'signature' => self::logoutSignature($redirect, $expires),
        ]);
    }

    public static function hasValidLogoutSignature(Request $request): bool
    {
        $expires = (int) $request->query('expires');

        return $expires >= now()->timestamp && hash_equals(
            self::logoutSignature((string) $request->query('redirect'), $expires),
            (string) $request->query('signature'),
        );
    }

    // Cookie "Ingat saya" dan session bisa tersimpan dua kali: di domain induk (.elips.id) dan khusus host ini
    // (dibuat sebelum SESSION_DOMAIN diisi). Logout bawaan Laravel hanya menghapus salinan domain induk, sehingga
    // salinan khusus host membuat pengguna langsung login lagi. Browser hanya mengizinkan host ini menghapusnya.
    public static function forgetHostCookies(Response $response): Response
    {
        if (! config('session.domain')) {
            return $response;
        }

        foreach ([Auth::guard()->getRecallerName(), config('session.cookie')] as $name) {
            $response->headers->setCookie(new Cookie($name, null, 1, config('session.path', '/'), null));
        }

        return $response;
    }

    private static function logoutSignature(string $redirect, int $expires): string
    {
        return hash_hmac('sha256', 'sso-logout|'.$expires.'|'.$redirect, (string) config('app.key'));
    }
}
