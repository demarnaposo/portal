<?php

namespace Modules\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Admin\Domain\Admin;
use Modules\Admin\Domain\AdminRole;

use function Laravel\Prompts\password;

final class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create {email} {--name= : Nama tampilan} {--role=owner : owner|operator}';

    protected $description = 'Buat akun panel admin (2FA wajib diaktifkan saat login pertama)';

    public function handle(): int
    {
        $data = [
            'email' => mb_strtolower(trim((string) $this->argument('email'))),
            'name' => (string) ($this->option('name') ?: Str::before((string) $this->argument('email'), '@')),
            'role' => (string) $this->option('role'),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'in:'.implode(',', array_column(AdminRole::cases(), 'value'))],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Tanpa terminal interaktif (-n / skrip): sandi acak, ditampilkan sekali.
        $generated = ! $this->input->isInteractive();
        $plain = $generated
            ? Str::password(20)
            : password(label: 'Kata sandi (min. 12 karakter)', required: true, validate: fn (string $v) => mb_strlen($v) < 12 ? 'Minimal 12 karakter.' : null);

        $admin = Admin::create([...$data, 'password' => $plain]);

        $this->info("Admin {$admin->email} ({$admin->role->value}) dibuat. Login di ".url('/admin/login').' lalu aktifkan 2FA.');

        if ($generated) {
            $this->line("Kata sandi sementara: {$plain}");
        }

        return self::SUCCESS;
    }
}
