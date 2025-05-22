<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h1 class="text-xl font-bold text-gray-900 mb-4">Все статьи (технический просмотр)</h1>
                <p class="text-sm text-gray-500 mb-6">Всего статей: {{ $items->total() }}</p>

                <div class="space-y-6">
                    @foreach ($items as $item)
                        <div class="border-l-4 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            style="border-color: {{ $item->feed->color ?? '#3b82f6' }}; background-color: rgba({{ hex2rgb($item->feed->color ?? '#3b82f6', 0.05) }})">
                            
                            <!-- Заголовок и источник -->
                            <div class="flex items-start justify-between mb-2">
                                <a href="{{ $item->link }}" target="_blank" 
                                    class="text-lg font-semibold hover:text-blue-600 break-words"
                                    style="color: {{ $item->feed->color ?? '#3b82f6' }}">
                                    {{ $item->title }}
                                </a>
                                
                                <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                    {{ $item->published_at->format('d.m.Y - H:i') }}
                                </span>
                            </div>
                            
                            <!-- Информация о фиде -->
                            <div class="flex items-center mb-3">
                                @if($item->feed->favicon)
                                    <img src="{{ $item->feed->favicon }}" alt="favicon" class="w-4 h-4 mr-1">
                                @endif
                                <span class="text-sm font-medium" style="color: {{ $item->feed->color ?? '#3b82f6' }}">
                                    {{ $item->feed->title }}
                                </span>
                                <span class="mx-2 text-gray-300">|</span>
                                <span class="text-xs text-gray-500">
                                    ID: {{ $item->feed_id }}, 
                                    Items: {{ $item->feed->items_count }}
                                </span>
                            </div>
                            
                            <!-- Контент -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Текстовый контент -->
                                <div class="col-span-2 space-y-2">
                                    @if($item->description)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Description:</p>
                                            <p class="text-sm text-gray-800 break-words max-h-40 overflow-y-auto">
                                                {{ Str::limit(strip_tags($item->description), 300) }}
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if($item->content)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Content:</p>
                                            <p class="text-sm text-gray-800 break-words max-h-40 overflow-y-auto">
                                                {{ Str::limit(strip_tags($item->content), 300) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Медиа и метаданные -->
                                <div class="space-y-3">
                                    @if($item->thumbnail)
                                        <div class="bg-gray-100 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Thumbnail:</p>
                                            <img src="{{ $item->thumbnail }}" alt="thumbnail" 
                                                class="max-w-full h-auto rounded border border-gray-200">
                                        </div>
                                    @endif
                                    
                                    <!-- Категории -->
                                    @if($item->categories && count($item->categories) > 0)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Categories:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($item->categories as $category)
                                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                                                        {{ $category }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Авторы -->
                                    @if($item->authors && count($item->authors) > 0)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Authors:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($item->authors as $author)
                                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                                                        {{ $author }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Техническая информация -->
                            <div class="mt-3 pt-2 border-t border-gray-100">
                                <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                                    <span>ID: {{ $item->id }}</span>
                                    <span>Created: {{ $item->created_at->format('d.m.Y - H:i') }}</span>
                                    <span>Updated: {{ $item->updated_at->format('d.m.Y -  H:i') }}</span>
                                    @if($item->enclosures)
                                        <span>Enclosures: {{ count($item->enclosures) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Пагинация -->
                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>