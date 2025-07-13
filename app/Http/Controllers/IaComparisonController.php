<?php

namespace App\Http\Controllers;

use App\Models\Domain;


class IaComparisonController extends Controller
{
    /**
     * Affiche la page d'accueil avec la liste des domaines
     */
    public function index()
    {
        $domains = Domain::all();
        return view('ia.index', compact('domains'));
    }

    /**
     * Obtient les modèles IA disponibles
     */
    public function getAvailableModels(): array
    {
        return [
            'openai/gpt-4o' => [
                'name' => 'GPT-4 Omni',
                'description' => 'Modèle multimodal avancé d\'OpenAI',
                'cost' => 'Payant'
            ],
            'deepseek/deepseek-r1' => [
                'name' => 'DeepSeek R1',
                'description' => 'Modèle de raisonnement open-source',
                'cost' => 'Payant'
            ],
            'qwen/qwen-2.5-72b-instruct' => [
                'name' => 'Qwen 2.5 72B',
                'description' => 'Modèle Qwen optimisé pour les instructions',
                'cost' => 'Payant'
            ]
        ];
    }
}
