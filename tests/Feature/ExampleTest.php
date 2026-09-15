<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_load_the_home_page(): void
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($person)->get(route('home'));

        $response->assertOk();
    }
}
