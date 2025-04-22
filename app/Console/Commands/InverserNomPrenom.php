<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class InverserNomPrenom extends Command
{
    protected $signature = 'users:inverser-nom-prenom';
    protected $description = 'Inverse les champs nom et prenom dans la table users';

    public function handle()
    {
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $tmp = $user->nom;
            $user->nom = $user->prenom;
            $user->prenom = $tmp;
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Tous les utilisateurs ont été mis à jour (nom <-> prénom).');
        return 0;
    }
}
