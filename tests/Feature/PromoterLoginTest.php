<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function login_in_with_valid_credentials()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create  ([
            'email' => 'jane@example.com',
            'password' => bcrypt('123456789')
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => '123456789'
        ]);

        $response->assertRedirect('/backstage/concerts/new');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /**
     * @test
     */
    public function logging_in_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'jane1@example.com',
            'password' => bcrypt('123456789')
        ]);

        $response = $this->post('/login', [
            'email' => 'jane1@example.com',
            'password' => '123456456466'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    /**
     * @test
     */
    public function logging_in_with_an_account_that_does_not_exist()
    {
        $response = $this->post('/login', [
            'email' => 'jane2@example.com',
            'password' => '1234567'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    /**
     * @test
     */
    public function logging_out_the_current_user()
    {
        Auth::login(User::factory()->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
