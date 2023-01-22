<?php

namespace Tests\Feature\Api\Auth;

use App\Events\UserRegistered;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_register()
    {
        Event::fake();

        $response = $this->postJson('/api/auth/register', [
            'email' => $email = $this->faker()->safeEmail(),
            'name' => $name = $this->faker()->name(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data.access_token')
                    ->has('data.user')
                    ->has('data.user.id')
                    ->where('data.user.email', $email)
                    ->where('data.user.name', $name)
                    ->missing('data.user.password')
                    ->etc()
            );

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => $name,
        ]);

        Event::assertDispatched(UserRegistered::class, function (UserRegistered $event) use ($email) {
            return $event->user && $event->user->email === $email;
        });
    }
}
