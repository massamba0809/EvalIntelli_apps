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
                'name' => 'Chimie',
                'slug' => 'chimie',
                'prompt_template' => "Tu es un expert en chimie. Réponds à cette question de chimie en expliquant clairement les concepts :\n{question}\n\nConsignes :\n- Explique les mécanismes chimiques\n- Utilise la nomenclature appropriée\n- Donne des exemples concrets"
            ],
        ];

        foreach ($domains as $domain) {
            Domain::create($domain);
        }
    }
}
