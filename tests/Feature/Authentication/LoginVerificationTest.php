<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Re-seed with DatabaseSetupSeeder to have clean test data with real creds
    $this->artisan('migrate:fresh');
    $this->seed(\Database\Seeders\DatabaseSetupSeeder::class);
});

it('logs in as superadmin using email', function () {
    $response = $this->post('/login', [
        'username' => 'superadmin@sicoe.mx',
        'password' => 'Admin_2026',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

it('logs in as control_escolar using username', function () {
    // Note: DatabaseSetupSeeder sets username to the prefix of email for testing purposes
    $response = $this->post('/login', [
        'username' => 'control_escolar',
        'password' => 'Control_2026',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    expect(auth()->user()->hasRole('control_escolar'))->toBeTrue();
});

it('logs in as operador using email', function () {
    $response = $this->post('/login', [
        'username' => 'operador@sicoe.mx',
        'password' => 'Operador_2026',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    expect(auth()->user()->hasRole('operador'))->toBeTrue();
});

it('fails login with wrong password', function () {
    $response = $this->post('/login', [
        'username' => 'superadmin@sicoe.mx',
        'password' => 'Wrong_Pass_123',
    ]);

    $response->assertSessionHasErrors('username'); 
});
