<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private string $registerUrl = '/api/auth/register';

    private function registerFormData($customData = [])
    {
        return array_merge([
            'email' => $this->faker()->safeEmail(),
            'name' => $this->faker()->name(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $customData);
    }

    public function test_user_can_register()
    {
        Event::fake();

        $response = $this->postJson(
            $this->registerUrl,
            $formData = $this->registerFormData()
        );

        $response->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data.access_token')
                    ->has('data.user')
                    ->has('data.user.id')
                    ->where('data.user.email', $formData['email'])
                    ->where('data.user.name', $formData['name'])
                    ->missing('data.user.password')
                    ->etc()
            );

        $this->assertDatabaseHas('users', [
            'email' => $formData['email'],
            'name' => $formData['name'],
        ]);

        Event::assertDispatched(UserRegistered::class, function (UserRegistered $event) use ($formData) {
            return $event->user && $event->user->email === $formData['email'];
        });
    }

    public function test_email_must_be_a_valid_email_format()
    {
        $response = $this->postJson(
            $this->registerUrl,
            $this->registerFormData(['email' => 'not_valid_email'])
        );

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('errors.email')
                    ->where('errors.email.0', 'The email must be a valid email address.')
                    ->etc()
            );
    }

    public function test_email_must_be_unique()
    {
        $existingUser = User::factory()->create();

        $response = $this->postJson(
            $this->registerUrl,
            $this->registerFormData(['email' => $existingUser->email])
        );

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('errors.email')
                    ->where('errors.email.0', 'The email has already been taken.')
                    ->etc()
            );
    }

    public function test_name_is_required()
    {
        $response = $this->postJson(
            $this->registerUrl,
            // post with an empty name
            $this->registerFormData(['name' => ''])
        );

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('errors.name')
                    ->where('errors.name.0', 'The name field is required.')
                    ->etc()
            );
    }

    public function test_password_is_required()
    {
        $response = $this->postJson(
            $this->registerUrl,
            // post with an empty password
            $this->registerFormData(['password' => ''])
        );

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('errors.password')
                    ->where('errors.password.0', 'The password field is required.')
                    ->etc()
            );
    }

    public function test_password_must_be_confirmed()
    {
        $response = $this->postJson(
            $this->registerUrl,
            // post with invalid password confirmation
            $this->registerFormData([
                'password' => 'password',
                'password_confirmation' => 'not_same_password',
            ])
        );

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('errors.password')
                    ->where('errors.password.0', 'The password confirmation does not match.')
                    ->etc()
            );
    }
}
