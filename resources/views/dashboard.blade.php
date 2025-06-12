<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок и фильтры -->
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Лента новостей</h1>
                    <p class="text-gray-500 mt-1">Всего новостей: {{ $articles->total() }}</p>
                </div>

                <div class="w-full md:w-auto">
                    <div class="w-full">
                        <form action="{{ route('dashboard') }}" method="GET"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="search" :value="__('Поиск')" />
                                <input type="text" name="search" id="search" placeholder="Заголовок/содержимое"
                                    value="{{ request('search') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <x-input-label for="category" :value="__('Категория')" />
                                <input type="text" name="category" id="categorySearch"
                                    placeholder="Начните вводить название категории" value="{{ request('category') }}"
                                    list="categoriesList"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">

                                <datalist id="categoriesList">
                                    @if(request('category'))
                                        <option value="{{ request('category') }}">
                                    @endif
                                </datalist>
                            </div>

                            <div>
                                <x-input-label for="source_search" :value="__('Источник')" />
                                <input list="sourcesList" id="sourceSearch" name="source_search"
                                    placeholder="Начните вводить название..."
                                    value="{{ $sources->where('id', request('source'))->first()->title ?? '' }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">

                                <input type="hidden" name="source" id="sourceId" value="{{ request('source') }}">

                                <datalist id="sourcesList">
                                    @foreach($sources as $source)
                                        <option data-value="{{ $source->id }}" value="{{ $source->title }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- Кнопки -->
                            <div class="flex gap-2">
                                <x-primary-button type="submit" class="md:mt-5">Поиск</x-primary-button>
                                @if(request()->has('search') || request()->has('category') || request()->has('source'))
                                    <a href="{{ route('dashboard') }}"
                                        class="md:mt-5 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Сбросить
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    <article
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col">
                        <!-- Изображение -->
                        @if($article->thumbnail)
                            <div class="h-48 overflow-hidden">
                                <img src="{{ $article->thumbnail }}" alt="{{ $article->title }}"
                                    class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Контент -->
                        <div class="p-6 flex flex-col flex-grow">
                            <!-- Источник и дата -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    @if($article->feed->favicon)
                                        <img src="{{ $article->feed->favicon }}" alt="favicon" class="w-4 h-4 mr-2">
                                    @endif
                                    <span class="text-sm font-medium"
                                        style="color: {{ $article->feed->color ?? '#3b82f6' }}">
                                        {{ $article->feed->title }}
                                    </span>
                                </div>
                                <time datetime="{{ $article->published_at->toIso8601String() }}"
                                    class="text-sm text-gray-500">
                                    {{ $article->published_at->diffForHumans() }}
                                </time>
                            </div>

                            <style>
                                .article-link-{{ $article->id }}:hover {
                                    color:
                                        {{ $article->feed->color }}
                                }
                            </style>
                            <!-- Заголовок -->
                            <h2 class="text-xl font-bold mb-3">
                                <a href="{{ $article->link }}" target="_blank" rel="noopener noreferrer"
                                    class="article-link-{{ $article->id }} transition-colors">
                                    {{ $article->title }}
                                </a>
                            </h2>

                            <!-- Краткое описание -->
                            @if($article->description)
                                <p
                                    class="text-gray-600 mb-4 {{ isset($article->thumbnail) ? 'line-clamp-3' : 'line-clamp-[10]' }}">
                                    {{ strip_tags($article->description) }}
                                </p>
                            @endif

                            <!-- Категории -->
                            @if($article->categories && count($article->categories) > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($article->categories as $category)
                                        <a href="{{ 'dashboard?' . http_build_query(['category' => $category]) }}">
                                            <span class="px-2 py-1 bg-gray-100 text-xs rounded-full">
                                                {{ $category }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Кнопки -->
                            <div class="mt-auto flex items-center gap-4">
                                <!-- Кнопка "Читать полностью" -->
                                <a href="{{ $article->link }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 hover:brightness-[.80]"
                                    style="background-color: {{$article->feed->color}}">
                                    Читать дальше
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>

                                <!-- Кнопка "Добавить в избранное" -->
                                @if (Auth::user())
                                    <button data-favorite-button data-article-id="{{ $article->id }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white transition ease-in-out duration-150 hover:brightness-[.80]">
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
                                @endif
                            </div>
                        </div>
                    </article>

                @endforeach
            </div>

            <!-- Пагинация -->
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</x-app-layout>