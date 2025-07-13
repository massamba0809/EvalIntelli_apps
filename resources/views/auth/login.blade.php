<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | EvalIntelli</title>
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
                <i class="fas fa-sign-in-alt text-xl text-indigo-600"></i>
            </div>
            <h1 class="text-2xl font-medium text-gray-800 flex items-center justify-center">
                <i class="fas fa-brain text-indigo-600 mr-2"></i>Connectez-vous
            </h1>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <div class="relative">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="w-full px-3 py-2 pl-10 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                           placeholder="email@exemple.com">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                @if ($errors->has('email'))
                    <p class="text-sm text-red-600 mt-1">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Mot de passe</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full px-3 py-2 pl-10 border border-[#e3e3e0] rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"
                           placeholder="••••••••">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                @if ($errors->has('password'))
                    <p class="text-sm text-red-600 mt-1">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
                </div>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            <!-- Bouton sobre -->
            <button type="submit"
                    class="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition mt-4">
                Se connecter
            </button>

            <div class="text-center text-sm text-gray-600 mt-4">
                Pas encore de compte ? <a href="{{ route('register') }}" class="text-indigo-600">Créer un compte</a>
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
