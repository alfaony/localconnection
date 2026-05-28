<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password
                            {email : Email user yang akan direset}
                            {password? : Password baru (default: abcde12345)}';

    protected $description = 'Reset password user berdasarkan email';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password') ?: 'abcde12345';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email '{$email}' tidak ditemukan.");
            return 1;
        }

        $user->password = bcrypt($password);
        $user->save();

        $this->info("Password berhasil direset.");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $user->email],
                ['Password', $password],
            ]
        );

        return 0;
    }
}
