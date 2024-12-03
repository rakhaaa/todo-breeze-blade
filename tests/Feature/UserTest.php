<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'user',
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('admin can edit a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $this->actingAs($admin);

    $response = $this->put("/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'user',
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
});

test('admin can delete a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $this->actingAs($admin);

    $response = $this->delete("/users/{$user->id}");

    $response->assertRedirect('/users');
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('non-admin cannot access user management', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/users');
    $response->assertRedirect('/dashboard');
});
