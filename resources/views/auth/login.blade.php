<!DOCTYPE html>
<html lang="fr" style="height: 100%; margin: 0; padding: 0;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - SENTRAVAUX SENELEC</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @font-face {
            font-family: 'Conthrax';
            src: url('../fonts/Conthrax-SemiBold.otf') format('opentype');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }

        .font-conthrax {
            font-family: 'Conthrax', 'Rajdhani', 'Open Sans', system-ui, sans-serif;
            font-weight: 600;
        }

        .font-title {
            font-family: 'Rajdhani', 'Open Sans', system-ui, sans-serif;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
        }
        
        .login-input {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(200, 200, 200, 0.5);
            border-radius: 10px;
            padding: 12px 14px;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .login-input:focus {
            outline: none;
            border-color: #B3006C;
            box-shadow: 0 0 0 3px rgba(179, 0, 108, 0.15);
        }
        
        .login-input::placeholder {
            color: #9CA3AF;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #2B1444 0%, #B3006C 100%);
            color: white;
            font-family: 'Rajdhani', 'Open Sans', sans-serif;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.1em;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(43, 20, 68, 0.4);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #2B1444 0%, #B3006C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-light {
            background: linear-gradient(90deg, #FFD100 0%, #E87400 25%, #B3006C 60%, #0A91A3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-pill {
            background: rgba(255, 255, 255, 0.9);
            color: #2B1444;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .link-magenta {
            color: #B3006C;
            transition: color 0.2s ease;
        }
        .link-magenta:hover {
            color: #8F0056;
        }
    </style>
</head>
<body class="m-0 p-0 overflow-hidden" style="min-height: 100vh; height: 100vh;">
    <div class="fixed inset-0 flex items-center justify-center p-4 md:p-6"
         style="background-image: url('{{ asset('img/login_bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        
        <div class="absolute inset-0"
             style="background: linear-gradient(135deg, rgba(10, 5, 20, 0.9) 0%, rgba(89, 0, 54, 0.9) 100%);">
        </div>
        
        <div class="relative z-10 w-full max-w-6xl flex items-center">
            <div class="flex flex-row w-full justify-between items-center">
                
                <!-- Left Side - Branding -->
                <div class="flex-1 flex flex-col justify-center pr-4 text-white">
                    <h1 class="font-conthrax text-4xl md:text-5xl xl:text-6xl font-bold mb-6 leading-tight tracking-wider uppercase text-gradient-light">
                        SENTRAVAUX
                    </h1>
                    
                    <p class="text-lg md:text-xl xl:text-2xl font-semibold mb-4" style="font-family: 'Open Sans', sans-serif;">
                        Gestion des Travaux SENELEC
                    </p>
                    
                    <p class="text-sm md:text-base text-white/80 max-w-md mb-4" style="font-family: 'Open Sans', sans-serif;">
                        Gérez et suivez les travaux sur le réseau électrique SENELEC.
                    </p>
                    
                    <!-- Features pills -->
                    <div class="mt-8 flex flex-nowrap gap-2 overflow-x-auto">
                        <span class="feature-pill px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">
                            Gestion Travaux
                        </span>
                        <span class="feature-pill px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">
                            Suivi en temps réel
                        </span>
                        <span class="feature-pill px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">
                            Validation hiérarchique
                        </span>
                        <span class="feature-pill px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">
                            Rapports
                        </span>
                    </div>
                </div>
                
                <!-- Right Side - Login Form -->
                <div class="shrink-0 w-130 xl:w-140 flex items-center justify-center">
                    <div class="glass-card w-full max-w-lg p-8">
                        <div class="mb-5 flex justify-center">
                            <img src="{{ asset('img/logo.png') }}" alt="SENELEC" class="h-14 md:h-16 w-auto">
                        </div>
                        
                        @if ($errors->any())
                            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">Erreur de connexion</p>
                                        @foreach ($errors->all() as $error)
                                            <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ rtrim(config('app.url'), '/') }}/" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="matricule" class="block text-sm font-medium text-gray-700 mb-2" style="font-family: 'Open Sans', sans-serif;">
                                    Matricule
                                </label>
                                <input type="text" 
                                       id="matricule" 
                                       name="matricule" 
                                       value="{{ old('matricule') }}"
                                       required 
                                       autofocus
                                       placeholder="Matricule"
                                       class="login-input">
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2" style="font-family: 'Open Sans', sans-serif;">
                                    Mot de passe
                                </label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" 
                                           id="password" 
                                           name="password" 
                                           required
                                           placeholder="••••••••••"
                                           class="login-input pr-12">
                                    <button type="button" 
                                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                                            @click="show = !show">
                                        <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           name="remember"
                                           class="w-4 h-4 rounded border-gray-300 text-[#B3006C] focus:ring-[#B3006C] cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-600" style="font-family: 'Open Sans', sans-serif;">Se souvenir</span>
                                </label>
                                <a href="#" class="text-sm font-medium link-magenta" style="font-family: 'Open Sans', sans-serif;">
                                    Mot de passe oublié ?
                                </a>
                            </div>
                            
                            <button type="submit" class="login-btn w-full flex items-center justify-center">
                                <span>Se connecter</span>
                            </button>
                        </form>
                        
                        <div class="mt-4 flex items-center">
                            <div class="flex-1 border-t border-gray-200"></div>
                            <span class="px-4 text-sm text-gray-500" style="font-family: 'Open Sans', sans-serif;">ou</span>
                            <div class="flex-1 border-t border-gray-200"></div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600" style="font-family: 'Open Sans', sans-serif;">
                                Problème de connexion ?
                                <a href="mailto:support@senelec.sn" class="font-medium link-magenta">
                                    Contacter le support
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="absolute bottom-4 left-0 right-0 text-center">
            <p class="text-sm text-white/70" style="font-family: 'Open Sans', sans-serif;">
                © {{ date('Y') }} SENELEC - Direction de l'Exploitation du Système Électrique
            </p>
        </div>
    </div>
</body>
</html>
