{{-- resources/views/ia/partials/styles.blade.php --}}
<style>
    /* === VARIABLES CSS === */
    :root {
        --primary-color: #3b82f6;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --error-color: #ef4444;
        --transition-speed: 0.3s;
        --border-radius: 0.5rem;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    /* === ANIMATIONS PERSONNALISÉES === */

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes shine {
        0% {
            transform: translateX(-100%) translateY(-100%) skewX(-12deg);
        }
        100% {
            transform: translateX(100vw) translateY(-100%) skewX(-12deg);
        }
    }

    @keyframes pulse-soft {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.05);
        }
    }

    @keyframes bounce-soft {
        0%, 20%, 53%, 80%, 100% {
            animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
            transform: translate3d(0, -8px, 0);
        }
        70% {
            animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
            transform: translate3d(0, -4px, 0);
        }
        90% {
            transform: translate3d(0, -2px, 0);
        }
    }

    @keyframes wave {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    @keyframes spin-slow {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    /* === CLASSES D'ANIMATION === */

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-fade-in-down {
        animation: fadeInDown 0.6s ease-out forwards;
    }

    .animate-slide-in-left {
        animation: slideInLeft 0.4s ease-out forwards;
    }

    .animate-slide-in-right {
        animation: slideInRight 0.4s ease-out forwards;
    }

    .animate-shine {
        animation: shine 2s ease-in-out;
    }

    .animate-pulse-soft {
        animation: pulse-soft 3s ease-in-out infinite;
    }

    .animate-bounce-soft {
        animation: bounce-soft 2s infinite;
    }

    .animate-spin-slow {
        animation: spin-slow 3s linear infinite;
    }

    /* === DOMAIN CARDS === */

    .domain-card {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
    }

    .domain-card:nth-child(1) { animation-delay: 0.1s; }
    .domain-card:nth-child(2) { animation-delay: 0.2s; }
    .domain-card:nth-child(3) { animation-delay: 0.3s; }
    .domain-card:nth-child(4) { animation-delay: 0.4s; }
    .domain-card:nth-child(5) { animation-delay: 0.5s; }
    .domain-card:nth-child(6) { animation-delay: 0.6s; }

    @media (min-width: 768px) {
        .domain-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-xl);
        }
    }

    /* === MENU PANEL === */

    #menu-panel {
        transition: transform var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    #menu-overlay {
        transition: opacity var(--transition-speed) ease;
        backdrop-filter: blur(4px);
    }

    /* === SCROLLBAR PERSONNALISÉE === */

    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 20px;
        border: transparent;
        transition: background-color var(--transition-speed) ease;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(156, 163, 175, 0.8);
    }

    /* === QUESTION ITEMS === */

    .question-item {
        transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform, box-shadow;
    }

    .question-item:hover {
        transform: translateY(-2px) scale(1.01);
        box-shadow: var(--shadow-lg);
    }

    .question-item.selected {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
        border-color: var(--primary-color);
        transform: none !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }

    .question-item:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
    }

    /* === BADGES DYNAMIQUES PAR DOMAINE === */

    .badge-programming {
        @apply bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300;
    }

    .badge-mathematics {
        @apply bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300;
    }

    .badge-translation {
        @apply bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300;
    }

    .badge-general {
        @apply bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300;
    }

    /* === LOADING STATES === */

    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: var(--border-radius);
    }

    .dark .loading-skeleton {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
        background-size: 200% 100%;
    }

    /* === EFFET DE VAGUE POUR LES BOUTONS === */

    .wave-effect {
        position: relative;
        overflow: hidden;
    }

    .wave-effect:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.4);
        opacity: 0;
        border-radius: 50%;
        transform: scale(1, 1) translate(-50%, -50%);
        transform-origin: 50% 50%;
    }

    .wave-effect:focus:not(:active)::after {
        animation: wave 0.6s ease-out;
    }

    /* === FOCUS STATES ACCESSIBLES === */

    .focus-ring:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
    }

    /* === ICON HOVER EFFECTS === */

    .icon-hover-scale {
        transition: transform var(--transition-speed) ease;
    }

    .icon-hover-scale:hover {
        transform: scale(1.1);
    }

    .icon-hover-rotate:hover {
        transform: rotate(5deg) scale(1.1);
    }

    /* === RESPONSIVE STATES === */

    @media (max-width: 1024px) {
        #menu-panel {
            height: 100vh;
            height: 100dvh; /* Pour les nouveaux navigateurs */
        }
    }

    @media (max-width: 768px) {
        .question-item {
            padding: 12px;
        }

        .question-item:hover {
            transform: none !important;
            scale: 1 !important;
        }

        .question-item .group-hover\:opacity-100 {
            opacity: 1 !important;
        }

        .domain-card {
            animation-delay: 0s !important;
        }

        .domain-card:hover {
            transform: none !important;
        }
    }

    /* === DARK MODE IMPROVEMENTS === */

    @media (prefers-color-scheme: dark) {
        :root {
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .custom-scrollbar {
            scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.5);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(75, 85, 99, 0.8);
        }
    }

    /* === UTILITY CLASSES === */

    .transition-all-smooth {
        transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .dark .glass-effect {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* === ANIMATIONS RÉDUITES POUR L'ACCESSIBILITÉ === */

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* === PRINT STYLES === */

    @media print {
        .domain-card,
        .question-item {
            break-inside: avoid;
        }

        #menu-panel,
        #menu-overlay {
            display: none !important;
        }

        .animate-fade-in-up,
        .animate-fade-in-down,
        .animate-slide-in-left,
        .animate-slide-in-right {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
    }
</style>
