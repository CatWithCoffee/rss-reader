<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Избранные статьи</h1>
            <p class="text-sm text-gray-500 mb-6">Всего избранных статей: {{ $favorites->total() }}</p>

            <!-- Список избранных статей -->
            <div class="space-y-4">
                @foreach ($favorites as $article)
                    <div class="bg-white border p-4 rounded-lg shadow hover:shadow-md transition-shadow flex flex-col sm:flex-row gap-4">

                        <!-- Изображение (thumbnail) -->
                        @if($article->thumbnail)
                            <div class="sm:w-48 sm:flex-shrink-0">
                                <img src="{{ $article->thumbnail }}" alt="{{ $article->title }}"
                                    class="w-full h-48 object-cover rounded-lg">
                            </div>
                        @endif

                        <!-- Контент статьи -->
                        <div class="flex-1 flex flex-col">
                            <!-- Заголовок и дата публикации -->
                            <div class="flex justify-between items-start">
                                <div class="flex-1 mr-4">
                                    <a href="{{ $article->link }}" target="_blank"
                                        class="text-lg font-semibold hover:text-blue-600 break-words"
                                        style="color: {{ $article->feed->color ?? '#3b82f6' }}">
                                        {{ $article->title }}
                                    </a>
                                    <p class="text-xs mt-2 text-gray-500">
                                        Опубликовано: {{ $article->published_at->diffForHumans() }}
                                        ({{ $article->published_at->format('d.m.Y - H:i') }})
                                    </p>
                                </div>
                            </div>

                            <!-- Краткое описание -->
                            @if($article->description)
                                <p class="text-sm text-gray-600 mt-2 break-words">
                                    {{ Str::limit(strip_tags($article->description), 400) }}
                                </p>
                            @endif

                            <!-- Источник -->
                            <div class="flex items-center my-3">
                                @if($article->feed->favicon)
                                    <img src="{{ $article->feed->favicon }}" alt="favicon" class="w-4 h-4 mr-2">
                                @endif
                                <span class="text-sm text-gray-600">
                                    Источник: {{ $article->feed->title }}
                                </span>
                            </div>

                            <!-- Контейнер для кнопок (прижат к низу) -->
                            <div class="mt-auto flex items-center gap-4">
                                <!-- Кнопка "Читать дальше" -->
                                <div>
                                    <a href="{{ $article->link }}" target="_blank"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white hover:shadow-md transition-colors"
                                        style="background-color: {{ $article->feed->color ?? '#3b82f6' }}">
                                        Читать дальше
                                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>

                                <!-- Кнопка удаления из избранного -->
                                <div>
                                    <button data-favorite-button data-article-id="{{ $article->id }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        <span id="favorite-text-{{ $article->id }}">
                                            {{ auth()->user() && auth()->user()->favorites->contains($article->id) ? 'Удалить из избранного' : 'Добавить в избранное' }}
                                        </span>
                                        <svg id="favorite-icon-{{ $article->id }}" xmlns="http://www.w3.org/2000/svg"
                                            class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if(auth()->user() && auth()->user()->favorites->contains($article->id))
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            @endif
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $favorites->links() }}
            </div>
        </div>
    </div>
</x-app-layout>