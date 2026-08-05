<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create {email? : O e-mail do novo usuário} {name? : O nome do novo usuário} {password? : A senha do novo usuário}';
    protected $description = 'Cria um novo usuário no banco de dados da aplicação';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Qual o e-mail do usuário?');
        $name = $this->argument('name') ?? $this->ask('Qual o nome do usuário?');
        $password = $this->argument('password') ?? $this->secret('Qual a senha do usuário?');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|min:2',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $this->error('Falha na validação ao criar o usuário:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("- {$error}");
            }
            return self::FAILURE;
        }

        $user = User::create([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ]);

        $this->info("Usuário '{$user->name}' ({$user->email}) criado com sucesso!");
        return self::SUCCESS;
    }
}
