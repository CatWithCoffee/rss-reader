<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок и фильтры -->
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Лента новостей</h1>
                    <p class="text-gray-500 mt-1">Всего новостей: {{ $items->total() }}</p>
                </div>

                <div class="w-full md:w-auto">
                    <div class="w-full">
                        <form action="{{ route('dashboard') }}" method="GET"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Поиск по заголовку -->
                            <div>
                                <label for="search" class="sr-only">Поиск</label>
                                <input type="text" name="search" id="search" placeholder="Поиск по заголовку"
                                    value="{{ request('search') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>

                            <!-- Фильтр по категориям -->
                            <div>
                                <label for="category" class="sr-only">Тег</label>
                                <input type="text" name="category" id="category" placeholder="Фильтр по категории"
                                    value="{{ request('category') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="sourceSearch" class="sr-only">Поиск источника</label>
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
                                <x-primary-button type="submit">Поиск</x-primary-button>
                                @if(request()->has('search') || request()->has('tag') || request()->has('source'))
                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
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
                @foreach($items as $item)
                    <article
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col">
                        <!-- Изображение -->
                        @if($item->thumbnail)
                            <div class="h-48 overflow-hidden">
                                <img src="{{ $item->thumbnail }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Контент -->
                        <div class="p-6 flex flex-col flex-grow">
                            <!-- Источник и дата -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    @if($item->feed->favicon)
                                        <img src="{{ $item->feed->favicon }}" alt="favicon" class="w-4 h-4 mr-2">
                                    @endif
                                    <span class="text-sm font-medium" style="color: {{ $item->feed->color ?? '#3b82f6' }}">
                                        {{ $item->feed->title }}
                                    </span>
                                </div>
                                <time datetime="{{ $item->published_at->toIso8601String() }}" class="text-sm text-gray-500">
                                    {{ $item->published_at->diffForHumans() }}
                                </time>
                            </div>

                            <!-- Заголовок -->
                            <h2 class="text-xl font-bold mb-3">
                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                    class="hover:text-primary-600 transition-colors">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <!-- Краткое описание -->
                            @if($item->description)
                                <p class="text-gray-600 mb-4 {{ isset($item->thumbnail) ? 'line-clamp-3' : '' }}">
                                    {{ strip_tags($item->description) }}
                                </p>
                            @endif

                            <!-- Категории -->
                            @if($item->categories && count($item->categories) > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($item->categories as $category)
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
                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white hover:bg-primary-700 transition-colors"
                                    style="background-color: {{ $item->feed->color ?? '#3b82f6' }}">
                                    Читать дальше
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>

                                <!-- Кнопка "Добавить в избранное" -->
                                @if (Auth::user())
                                    <button onclick="toggleFavorite({{ $item->id }}, this)"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        <span id="favorite-text-{{ $item->id }}">
                                            {{ auth()->user() && auth()->user()->favorites->contains($item->id) ? 'Удалить из избранного' : 'Добавить в избранное' }}
                                        </span>
                                        <svg id="favorite-icon-{{ $item->id }}" xmlns="http://www.w3.org/2000/svg"
                                            class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
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
                {{ $items->links() }}
            </div>
        </div>
    </div>
    <script>
        document.getElementById('sourceSearch').addEventListener('input', function () {
            const input = this;
            const datalist = document.getElementById('sourcesList');
            const hiddenInput = document.getElementById('sourceId');

            // Находим соответствующую опцию
            const option = Array.from(datalist.options)
                .find(opt => opt.value.toLowerCase() === input.value.toLowerCase());

            // Устанавливаем значение hidden-поля
            hiddenInput.value = option ? option.dataset.value : '';

            // Если точного совпадения нет, очищаем hidden-поле
            if (!option && input.value === '') {
                hiddenInput.value = '';
            }
        });
    </script>
</x-app-layout>