<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | EvalIntelli</title>
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
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-plus text-xl text-indigo-600"></i>
            </div>
            <h1 class="text-2xl font-medium text-gray-800 flex items-center justify-center">
                <i class="fas fa-brain text-indigo-600 mr-2"></i>Créer un compte
            </h1>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nom complet -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nom complet</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full px-3 py-2 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="Votre nom">
                @if ($errors->get('name'))
                    @foreach ($errors->get('name') as $message)
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @endforeach
                @endif
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="w-full px-3 py-2 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="email@exemple.com">
                @if ($errors->get('email'))
                    @foreach ($errors->get('email') as $message)
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @endforeach
                @endif
            </div>

            <!-- Mot de passe -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Mot de passe</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full px-3 py-2 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="••••••••">
                @if ($errors->get('password'))
                    @foreach ($errors->get('password') as $message)
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @endforeach
                @endif
            </div>

            <!-- Confirmation mot de passe -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Confirmer le mot de passe</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-3 py-2 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="••••••••">
                @if ($errors->get('password_confirmation'))
                    @foreach ($errors->get('password_confirmation') as $message)
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @endforeach
                @endif
            </div>

            <!-- Bouton sobre -->
            <button type="submit"
                    class="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition mt-4">
                S'inscrire
            </button>

            <div class="text-center text-xs text-gray-500 mt-4">
                En vous inscrivant, vous acceptez nos <a href="#" class="text-indigo-600">CGU</a>
            </div>

            <div class="text-center text-sm text-gray-600 mt-2">
                Déjà inscrit ? <a href="{{ route('login') }}" class="text-indigo-600">Se connecter</a>
            </div>
        </form>
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
