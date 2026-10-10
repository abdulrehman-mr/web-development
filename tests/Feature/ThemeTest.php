<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_default_scheme_adds_no_overrides(): void
    {
        $this->get('/')->assertDontSee('--color-brand-700', false);
    }

    public function test_admin_can_choose_a_preset_or_custom_colours(): void
    {
        $this->actingAs(User::first());
        $this->get('/admin/appearance')->assertOk()->assertSee('Indus Blue');

        $this->put('/admin/appearance', ['theme_preset' => 'indus'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('--color-brand-700:#1d4ed8', false);

        $this->put('/admin/appearance', ['theme_preset' => 'custom', 'theme_brand' => '#123456', 'theme_accent' => '#abcdef'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('--color-brand-700:#123456', false)->assertSee('--color-lake-500:#abcdef', false);
    }

    public function test_custom_colours_must_be_hex(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/appearance', ['theme_preset' => 'custom', 'theme_brand' => 'red;}body{display:none', 'theme_accent' => '#abcdef'])
            ->assertSessionHasErrors('theme_brand');
        $this->get('/')->assertDontSee('display:none', false);
    }
}
