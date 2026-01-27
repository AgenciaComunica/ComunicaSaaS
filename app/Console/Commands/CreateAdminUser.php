<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin {email} {--name=Admin} {--password=}';
    protected $description = 'Cria um usuário administrador';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: 'Admin';
        $password = $this->option('password') ?: $this->secret('Senha');

        if (!$password) {
            $this->error('Senha é obrigatória.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Email já cadastrado.');
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->info('Admin criado com sucesso.');
        return self::SUCCESS;
    }
}
