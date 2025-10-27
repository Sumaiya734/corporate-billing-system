<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:password {email=admin@netbillbd.com} {--password=admin123} {--force : Run in non-local environments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or reset an admin user password (default: admin@netbillbd.com / admin123).';

    public function handle()
    {
        $env = config('app.env');

        if ($env !== 'local' && ! $this->option('force')) {
            $this->error("This command is restricted to the local environment. Use --force to override.");
            return 1;
        }

        $email = $this->argument('email');
        $password = $this->option('password');

        $this->info("Ensuring admin user exists: {$email}");

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->name = $user->name ?: 'NetBill Admin';
        $user->password = Hash::make($password);
        $user->save();

        if ($isNew) {
            $this->info("Admin user created: {$email}");
        } else {
            $this->info("Admin password updated for: {$email}");
        }

        $this->line("Email: {$email}");
        $this->line("Password: (hidden)");

        return 0;
    }
}
