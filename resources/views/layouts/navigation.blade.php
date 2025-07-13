<nav class="bg-white shadow-sm py-4 px-6 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
            <i class="fas fa-brain text-2xl text-indigo-600 animate-wave"></i>
            <span class="text-xl font-bold text-gray-800">EvalIntelli</span>
        </div>

        <!-- Navigation Links -->
        <div class="hidden sm:flex space-x-8">
            <a href="{{ route('dashboard') }}" class="nav-link px-4 py-1.5 text-gray-800 text-sm font-medium transition-fast">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
        </div>

        <!-- User Dropdown -->
        <div class="hidden sm:flex sm:items-center">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-1 focus:outline-none">
                    <span class="text-gray-800 font-medium">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">
                        <i class="fas fa-user-circle mr-2"></i> Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">
                            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="sm:hidden">
            <button @click="open = !open" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 border-indigo-500 text-base font-medium text-indigo-700 bg-indigo-50">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                    <i class="fas fa-user-circle mr-2"></i> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                    </a>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Font Awesome pour les icônes -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .transition-fast { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }

    .nav-link {
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: currentColor;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .animate-wave {
        animation: wave 2.5s linear infinite;
    }

    @keyframes wave {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(-5deg); }
        20% { transform: rotate(10deg); }
        30% { transform: rotate(-5deg); }
        40% { transform: rotate(10deg); }
        50% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
</style>
