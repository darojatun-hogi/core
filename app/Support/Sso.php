<?php

namespace App\Support;

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
}
