<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;

class TranslationDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le domaine Traduction s'il n'existe pas
        $translationDomain = Domain::firstOrCreate(
            ['slug' => 'traduction'],
            [
                'name' => 'Traduction',
                'prompt_template' => 'Voici une demande de traduction : {question}

Veuillez fournir une traduction précise et naturelle en respectant :
- Le sens exact du texte original
- La grammaire et syntaxe de la langue cible
- Le ton et le style appropriés au contexte
- Les nuances culturelles si nécessaire

Évitez les commentaires ou annotations entre crochets.',
            ]
        );

        $this->command->info('✅ Domaine Traduction créé/mis à jour: ' . $translationDomain->name);

        // Optionnel : Créer d'autres domaines liés aux langues
        $linguisticsDomain = Domain::firstOrCreate(
            ['slug' => 'linguistique'],
            [
                'name' => 'Linguistique',
                'prompt_template' => 'Question de linguistique : {question}

Veuillez analyser cette question du point de vue linguistique en considérant :
- Les aspects grammaticaux, syntaxiques et sémantiques
- Les variations dialectales ou régionales si pertinentes
- L\'évolution historique de la langue si nécessaire
- Les comparaisons inter-linguistiques appropriées

Fournissez une réponse claire et documentée.',
            ]
        );

        $this->command->info('✅ Domaine Linguistique créé/mis à jour: ' . $linguisticsDomain->name);

        // Optionnel : Domaine "Langues étrangères"
        $languagesDomain = Domain::firstOrCreate(
            ['slug' => 'langues-etrangeres'],
            [
                'name' => 'Langues étrangères',
                'prompt_template' => 'Question sur les langues étrangères : {question}

Répondez en tenant compte :
- De l\'apprentissage et de l\'enseignement des langues
- Des aspects culturels liés à la langue
- Des difficultés courantes pour les apprenants
- Des méthodes et ressources recommandées

Adaptez votre réponse au niveau présumé de l\'apprenant.',
            ]
        );

        $this->command->info('✅ Domaine Langues étrangères créé/mis à jour: ' . $languagesDomain->name);
    }
}
