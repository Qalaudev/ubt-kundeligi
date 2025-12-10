<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Тест Платформасы - Басты бет</title>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-blue-600">Тест Платформасы</h1>
                <div class="flex items-center gap-4">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                                <div class="hidden sm:block">
                                    <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                Админ Панелі
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                                Шығу
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors">
                            Кіру
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                Тіркелу
                            </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Қолжетімді Тесттер</h2>
            <p class="text-lg text-gray-600">Біліміңізді тексеру үшін тест тақырыбын таңдаңыз</p>
        </div>

        <!-- Topics Grid -->
        <div id="topics-app">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Тесттер жүктелуде...</p>
            </div>

            <div v-else-if="topics.length === 0" class="text-center py-12">
                <p class="text-xl text-gray-600">Қазіргі уақытта тесттер жоқ.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="topic in topics" :key="topic.id" 
                     class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow p-6 cursor-pointer transform hover:scale-105 transition-transform"
                     @click="goToQuiz(topic.id)">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">@{{ topic.title }}</h3>
                        <p class="text-sm text-gray-600 mb-4">@{{ topic.questions_count || 0 }} сұрақ</p>
                        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                            Тестті Бастау
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
