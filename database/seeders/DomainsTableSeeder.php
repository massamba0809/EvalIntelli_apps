<?php

// database/seeders/DomainsTableSeeder.php
namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainsTableSeeder extends Seeder
{
    public function run()
    {
        $domains = [
            [
                'name' => 'Logique/Math',
                'slug' => 'logique-math',
                'prompt_template' => "Tu es un expert en logique et mathématiques. Résous le problème suivant de manière détaillée en expliquant chaque étape :\n{question}"
            ],
            [
                'name' => 'Programmation',
                'slug' => 'programmation',
                'prompt_template' => "Tu es un expert en développement logiciel. Écris un code clair, pour  résoudre le problème suivant :\n{question}\nInclus des explications sur ton approche."
            ],
            [
                'name' => 'Traduction',
                'slug' => 'traduction',
                'prompt_template' => "Tu es un traducteur professionnel. Traduis le texte suivant en gardant le ton et le style d'origine :\n{question}"
            ],
            [
                'name' => 'Médecine',
                'slug' => 'medecine',
                'prompt_template' => "Tu es un médecin généraliste basé sur les recommandations de l'OMS. Donne une réponse informative au problème médical suivant, mais indique clairement que l'avis médical professionnel est obligatoire :\n{question}"
            ],
        ];

        foreach ($domains as $domain) {
            Domain::create($domain);
        }
    }
}
