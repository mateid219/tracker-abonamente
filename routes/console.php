<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-admin {email : Emailul contului} {--revoke : Retrage drepturile de administrator}', function (string $email) {
    $user = User::where('email', mb_strtolower($email))->first();

    if (! $user) {
        $this->error("Nu există niciun cont cu emailul {$email}.");

        return 1;
    }

    $makeAdmin = ! $this->option('revoke');
    $user->forceFill(['is_admin' => $makeAdmin])->save();

    $this->info($makeAdmin
        ? "{$user->email} este acum administrator."
        : "{$user->email} nu mai este administrator.");

    return 0;
})->purpose('Acordă sau retrage drepturile de administrator unui cont');
