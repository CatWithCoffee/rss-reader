@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Пагинация" class="mt-8">
        {{-- Мобильная версия --}}
        <div class="flex justify-between items-center sm:hidden space-x-2">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад
                </a>
            @endif

            <span class="text-sm text-gray-500 px-4 py-2 bg-gray-50 rounded-lg">
                {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors flex items-center shadow-sm">
                    Вперед
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="px-4 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed flex items-center">
                    Вперед
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>

        {{-- Десктопная версия --}}
        <div class="hidden sm:flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Показано <span class="font-medium">{{ $paginator->firstItem() }}—{{ $paginator->lastItem() }}</span> из <span class="font-medium">{{ $paginator->total() }}</span>
            </div>

            <div class="flex items-center space-x-1">
                {{-- Кнопка "Назад" --}}
                @if ($paginator->onFirstPage())
                    <span class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Нумерация страниц --}}
                <div class="flex space-x-1 mx-2">
                    @foreach ($elements as $element)
                        {{-- Разделитель "..." --}}
                        @if (is_string($element))
                            <span class="px-3 py-1 flex items-end text-gray-400">...</span>
                        @endif

                        {{-- Ссылки на страницы --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-medium">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" aria-label="Страница {{ $page }}" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Кнопка "Вперед" --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif