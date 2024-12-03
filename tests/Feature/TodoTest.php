<?php

use App\Models\User;
use App\Models\ToDo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a todo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/todos', [
        'title' => 'Test ToDo',
        'description' => 'Test Description',
        'user_id' => $user->id
    ]);

    $response->assertRedirect('/todos');
    $this->assertDatabaseHas('todos', ['title' => 'Test ToDo']);
});

test('user can edit their own todo', function () {
    $user = User::factory()->create();
    $todo = ToDo::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user);

    $response = $this->put("/todos/{$todo->id}", [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $response->assertRedirect('/todos');
    $this->assertDatabaseHas('todos', ['title' => 'Updated Title']);
});

test('user can delete their own todo', function () {
    $user = User::factory()->create();
    $todo = ToDo::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user);

    $response = $this->delete("/todos/{$todo->id}");

    $response->assertRedirect('/todos');
    $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
});

test('user cannot edit others todo', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $todo = ToDo::factory()->create(['user_id' => $otherUser->id]);
    $this->actingAs($user);

    $response = $this->put("/todos/{$todo->id}", [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $response->assertForbidden();
});

test('admin can edit any todo', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $todo = ToDo::factory()->create(['user_id' => $user->id]);
    $this->actingAs($admin);

    $response = $this->put("/todos/{$todo->id}", [
        'title' => 'Admin Updated Title',
        'description' => 'Admin Updated Description',
    ]);

    $response->assertRedirect('/todos');
    $this->assertDatabaseHas('todos', ['title' => 'Admin Updated Title']);
});
