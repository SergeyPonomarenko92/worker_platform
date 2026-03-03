<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_root_redirects_to_catalog(): void
    {
        $this->get('/')
            ->assertRedirect('/catalog');
    }

    public function test_root_redirect_preserves_query_params(): void
    {
        $this->get('/?q=%D0%BA%D0%B0%D0%B2%D0%B0&city=%D0%9A%D0%B8%D1%97%D0%B2&sort=price_asc')
            ->assertRedirect('/catalog?q=%D0%BA%D0%B0%D0%B2%D0%B0&city=%D0%9A%D0%B8%D1%97%D0%B2&sort=price_asc');
    }
}
