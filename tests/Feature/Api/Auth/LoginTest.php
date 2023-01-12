<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    public function test_registered_user_can_login()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data.access_token')
                    ->has('data.user')
                    ->where('data.user.id', $user->id)
                    ->where('data.user.email', $user->email)
                    ->where('data.user.name', $user->name)
                    ->missing('data.user.password')
                    ->etc()
            );

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_unregistered_user_not_allowed_to_login()
    {
        $user = User::factory()->make();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        // $response->dd();

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('message')
                    ->has('errors.email')
                    ->where('errors.email.0', __('auth.failed'))
                    ->etc()
            );
    }
}
