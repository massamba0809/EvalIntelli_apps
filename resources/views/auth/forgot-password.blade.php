<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation mot de passe | EvalIntelli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #FDFDFC;
        }
        .form-container {
            background-color: white;
            border: 1px solid #e3e3e0;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
<!-- En-tête sobre -->
<header class="bg-white py-4 border-b border-[#e3e3e0]">
    <div class="max-w-4xl mx-auto px-4 flex items-center">
        <i class="fas fa-brain text-2xl text-indigo-600 mr-2"></i>
        <span class="text-xl font-bold text-gray-800">EvalIntelli</span>
    </div>
</header>

<!-- Formulaire fonctionnel -->
<main class="flex-grow flex items-center justify-center py-8 px-4">
    <div class="w-full max-w-md form-container rounded-lg p-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-key text-xl text-indigo-600"></i>
            </div>
            <h1 class="text-2xl font-medium text-gray-800 flex items-center justify-center">
                <i class="fas fa-brain text-indigo-600 mr-2"></i>Réinitialisation
            </h1>
        </div>

        <div class="mb-4 text-sm text-gray-600 text-center">
            Mot de passe oublié ? Aucun problème. Indiquez-nous votre adresse email et nous vous enverrons un lien de réinitialisation.
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <div class="relative">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3 py-2 pl-10 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                           placeholder="email@exemple.com">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                @if ($errors->get('email'))
                    @foreach ($errors->get('email') as $message)
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @endforeach
                @endif
            </div>

            <div class="flex items-center justify-center mt-6">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                    Envoyer le lien
                </button>
            </div>
        </form>

        <div class="text-center text-sm text-gray-600 mt-6">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">
                ← Retour à la connexion
            </a>
        </div>
    </div>
</main>

<!-- Pied de page minimal -->
<footer class="py-4 border-t border-[#e3e3e0] bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center text-xs text-gray-500">
        © EvalIntelli - Comparez les intelligences artificielles
    </div>
</footer>
</body>
</html>
