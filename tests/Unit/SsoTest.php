<?php

namespace Tests\Unit;

use App\Support\Sso;
use Tests\TestCase;

class SsoTest extends TestCase
{
    public function test_safe_redirect_only_allows_the_parent_domain_and_its_subdomains(): void
    {
        config(['sso.allowed_domain' => 'elips.id']);

        $this->assertSame('https://money.elips.id/dashboard', Sso::safeRedirect('https://money.elips.id/dashboard'));
        $this->assertSame('https://elips.id', Sso::safeRedirect('https://elips.id'));

        $this->assertNull(Sso::safeRedirect('https://evil.com/?x=money.elips.id'));
        $this->assertNull(Sso::safeRedirect('https://notelips.id'));
        $this->assertNull(Sso::safeRedirect('https://elips.id.evil.com'));
        $this->assertNull(Sso::safeRedirect('//money.elips.id'));
        $this->assertNull(Sso::safeRedirect('javascript://money.elips.id/%0aalert(1)'));
        $this->assertNull(Sso::safeRedirect(null));
    }

    public function test_hub_url_appends_query(): void
    {
        config(['sso.hub_url' => 'https://auth.elips.id']);

        $this->assertSame(
            'https://auth.elips.id/login?redirect=https%3A%2F%2Fclip.elips.id%2Fdashboard',
            Sso::hubUrl('login', ['redirect' => 'https://clip.elips.id/dashboard'])
        );
    }
}
