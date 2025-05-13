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
                    <form action="{{ route('dashboard') }}" method="GET" class="flex gap-2">
                        <select name="source"
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Все источники</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" @if(request('source') == $source->id) selected @endif>
                                    {{ $source->title }}
                                </option>
                            @endforeach
                        </select>
                        <x-primary-button type="submit">Применить</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $item)
                    <article
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <!-- Изображение -->
                        @if($item->thumbnail)
                            <div class="h-48 overflow-hidden">
                                <img src="{{ $item->thumbnail }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Контент -->
                        <div class="p-6">
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
                                    class="hover:text-indigo-600 transition-colors">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <!-- Краткое описание -->
                            @if($item->description)
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    {{ Str::limit(strip_tags($item->description), 150) }}
                                </p>
                            @endif

                            <!-- Категории -->
                            @if($item->categories && count($item->categories) > 0)
                                <div class="flex flex-wrap gap-2 mt-4">
                                    @foreach($item->categories as $category)
                                        <span class="px-2 py-1 bg-gray-100 text-xs rounded-full">
                                            {{ $category }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Кнопка -->
                            <div class="mt-6">
                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white hover:bg-indigo-700 transition-colors"
                                    style="background-color: {{ $item->feed->color ?? '#3b82f6' }}">
                                    Читать полностью
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
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
</x-app-layout>