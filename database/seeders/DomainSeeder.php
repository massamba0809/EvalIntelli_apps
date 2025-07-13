<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;
use Illuminate\Support\Str;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $domains = [
            [
                'name' => 'Logique/Math',
                'slug' => 'logique-math',
                'prompt_template' => 'Résolvez cette question de mathématiques ou de logique en détaillant clairement vos calculs et raisonnements : {question}

Consignes :
- Montrez chaque étape de calcul
- Justifiez votre raisonnement logique
- Utilisez la notation mathématique appropriée
- Vérifiez votre résultat final
- Expliquez la méthode employée',
                'icon' => '🧮',
                'description' => 'Questions de mathématiques, calculs, équations, logique et raisonnement quantitatif'
            ],
            [
                'name' => 'Programmation',
                'slug' => 'programmation',
                'prompt_template' => 'Répondez à cette question de programmation en fournissant du code propre et bien commenté : {question}

Consignes :
- Fournissez du code fonctionnel
- Ajoutez des commentaires explicatifs
- Respectez les bonnes pratiques
- Expliquez votre approche
- Proposez des améliorations si possible',
                'icon' => '💻',
                'description' => 'Questions de développement, algorithmes, langages de programmation et architecture logicielle'
            ],
            [
                'name' => 'Sciences',
                'slug' => 'sciences',
                'prompt_template' => 'Expliquez ce concept scientifique de manière claire et pédagogique : {question}

Consignes :
- Utilisez un langage accessible
- Donnez des exemples concrets
- Citez des sources fiables si nécessaire
- Structurez votre explication
- Précisez le domaine scientifique concerné',
                'icon' => '🔬',
                'description' => 'Questions de physique, chimie, biologie, astronomie et sciences naturelles'
            ],
            [
                'name' => 'Histoire/Géographie',
                'slug' => 'histoire-geographie',
                'prompt_template' => 'Répondez à cette question d\'histoire ou de géographie avec précision et contexte : {question}

Consignes :
- Situez dans le temps et l\'espace
- Donnez le contexte historique/géographique
- Mentionnez les sources importantes
- Expliquez les causes et conséquences
- Soyez objectif et factuel',
                'icon' => '🏛️',
                'description' => 'Questions d\'histoire, géographie, civilisations et événements marquants'
            ],
            [
                'name' => 'Littérature/Philosophie',
                'slug' => 'litterature-philosophie',
                'prompt_template' => 'Analysez cette question de littérature ou philosophie avec profondeur : {question}

Consignes :
- Développez une argumentation structurée
- Citez des auteurs et œuvres pertinents
- Explorez différentes perspectives
- Utilisez un style soutenu
- Concluez avec une synthèse personnelle',
                'icon' => '📚',
                'description' => 'Questions de littérature, philosophie, analyse de textes et réflexions conceptuelles'
            ],
            [
                'name' => 'Économie/Droit',
                'slug' => 'economie-droit',
                'prompt_template' => 'Traitez cette question d\'économie ou de droit de manière rigoureuse : {question}

Consignes :
- Définissez les termes techniques
- Présentez les théories applicables
- Analysez les enjeux pratiques
- Référencez les textes de loi si pertinent
- Proposez une conclusion nuancée',
                'icon' => '⚖️',
                'description' => 'Questions d\'économie, droit, finance, politique et institutions'
            ],
            [
                'name' => 'Santé/Médecine',
                'slug' => 'sante-medecine',
                'prompt_template' => 'Répondez à cette question de santé ou médecine avec prudence et précision : {question}

Consignes :
- Basez-vous sur des sources médicales fiables
- Précisez les limites de votre réponse
- Recommandez de consulter un professionnel si nécessaire
- Évitez les diagnostics ou prescriptions
- Utilisez un langage médical approprié mais accessible',
                'icon' => '🏥',
                'description' => 'Questions de santé, médecine, anatomie et bien-être (à titre informatif uniquement)'
            ],
            [
                'name' => 'Arts/Culture',
                'slug' => 'arts-culture',
                'prompt_template' => 'Explorez cette question artistique ou culturelle avec sensibilité : {question}

Consignes :
- Situez dans le contexte artistique/culturel
- Décrivez les techniques et styles
- Mentionnez les influences et héritages
- Analysez l\'impact esthétique/social
- Respectez la diversité des interprétations',
                'icon' => '🎨',
                'description' => 'Questions d\'art, musique, cinéma, architecture et expressions culturelles'
            ],
            [
                'name' => 'Technologie/Innovation',
                'slug' => 'technologie-innovation',
                'prompt_template' => 'Analysez cette question technologique en restant à jour : {question}

Consignes :
- Expliquez les concepts techniques clairement
- Mentionnez les innovations récentes
- Analysez les impacts sociétaux
- Discutez des enjeux éthiques si pertinent
- Proposez des perspectives d\'évolution',
                'icon' => '🚀',
                'description' => 'Questions de technologie, innovation, intelligence artificielle et transformation numérique'
            ],
            [
                'name' => 'Vie Pratique',
                'slug' => 'vie-pratique',
                'prompt_template' => 'Donnez des conseils pratiques et utiles pour : {question}

Consignes :
- Proposez des solutions concrètes
- Structurez vos conseils par étapes
- Donnez des exemples pratiques
- Mentionnez les alternatives possibles
- Restez bienveillant et constructif',
                'icon' => '🛠️',
                'description' => 'Questions pratiques du quotidien, conseils, organisation et résolution de problèmes'
            ]
        ];

        foreach ($domains as $domainData) {
            Domain::updateOrCreate(
                ['slug' => $domainData['slug']],
                $domainData
            );
        }

        $this->command->info('✅ Domaines créés avec succès !');
        $this->command->table(
            ['Domaine', 'Slug', 'Icône'],
            collect($domains)->map(fn($d) => [$d['name'], $d['slug'], $d['icon']])->toArray()
        );
    }
}
