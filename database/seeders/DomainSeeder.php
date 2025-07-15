<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer seulement les domaines qui ne font pas partie des 4 principaux
        $domainsToKeep = ['logique-math', 'programmation', 'traduction', 'chimie'];

        $extraDomains = Domain::whereNotIn('slug', $domainsToKeep)->get();

        if ($extraDomains->count() > 0) {
            $this->command->info('🗑️ Suppression des domaines en trop...');
            foreach ($extraDomains as $domain) {
                $this->command->line("- Suppression: {$domain->name} ({$domain->slug})");
                $domain->delete();
            }
        }

        // Définir les 4 domaines principaux
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
                'name' => 'Traduction',
                'slug' => 'traduction',
                'prompt_template' => 'Voici une demande de traduction : {question}

Veuillez fournir une traduction précise et naturelle en respectant :
- Le sens exact du texte original
- La grammaire et syntaxe de la langue cible
- Le ton et le style appropriés au contexte
- Les nuances culturelles si nécessaire

Évitez les commentaires ou annotations entre crochets.',
                'icon' => '🌐',
                'description' => 'Questions de traduction entre différentes langues'
            ],
            [
                'name' => 'Chimie',
                'slug' => 'chimie',
                'prompt_template' => 'Répondez à cette question de chimie en expliquant clairement les concepts : {question}

Consignes :
- Expliquez les mécanismes chimiques
- Utilisez la nomenclature appropriée
- Donnez des exemples concrets
- Mentionnez les applications pratiques
- Respectez les règles de sécurité si pertinent',
                'icon' => '⚗️',
                'description' => 'Questions de chimie, réactions, molécules et processus chimiques'
            ]
        ];

        // Créer ou mettre à jour chaque domaine
        foreach ($domains as $domainData) {
            $domain = Domain::updateOrCreate(
                ['slug' => $domainData['slug']],
                $domainData
            );

            $status = $domain->wasRecentlyCreated ? 'Créé' : 'Mis à jour';
            $this->command->line("✅ {$status}: {$domain->name}");
        }

        $this->command->info("\n🎉 Finalisation terminée !");
        $this->command->table(
            ['Domaine', 'Slug', 'Icône', 'ID'],
            Domain::whereIn('slug', $domainsToKeep)
                ->get()
                ->map(fn($d) => [$d->name, $d->slug, $d->icon, $d->id])
                ->toArray()
        );

        $totalDomains = Domain::count();
        $this->command->info("📊 Total des domaines: {$totalDomains}");
    }
}
