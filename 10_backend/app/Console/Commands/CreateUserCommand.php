<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

// Simple Artisan command to create users from CLI
class CreateUserCommand extends Command
{
    protected $signature = 'user:create {email? : O e-mail do usuário} {name? : O nome do usuário} {password? : A senha do usuário}';

    protected $description = 'Cria um novo usuário no banco de dados';

    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('E-mail do usuário');
        $name = $this->argument('name') ?? $this->ask('Nome do usuário');
        $password = $this->argument('password') ?? $this->secret('Senha do usuário');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password, // Cast 'hashed' no Model cuida da criptografia
        ]);

        $this->info("Usuário {$user->email} criado com sucesso! (ID: {$user->id})");
        return 0;
    }
}
