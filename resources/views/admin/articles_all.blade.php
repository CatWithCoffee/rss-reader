<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Все статьи (технический просмотр)</h1>
                <p class="text-sm text-gray-500 mb-6">Всего статей: {{ $articles->total() }}</p>

                <div class="space-y-6">
                    @foreach ($articles as $article)
                        <div class="border-l-4 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            style="border-color: {{ $article->feed->color ?? '#3b82f6' }}; background-color: rgba({{ hex2rgb($article->feed->color ?? '#3b82f6', 0.05) }})">
                            
                            <!-- Заголовок и источник -->
                            <div class="flex items-start justify-between mb-2">
                                <a href="{{ $article->link }}" target="_blank" 
                                    class="text-lg font-semibold hover:text-blue-600 break-words"
                                    style="color: {{ $article->feed->color ?? '#3b82f6' }}">
                                    {{ $article->title }}
                                </a>
                                
                                <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                    {{ $article->published_at->format('d.m.Y - H:i') }}
                                </span>
                            </div>
                            
                            <!-- Информация о фиде -->
                            <div class="flex items-center mb-3">
                                @if($article->feed->favicon)
                                    <img src="{{ $article->feed->favicon }}" alt="favicon" class="w-4 h-4 mr-1">
                                @endif
                                <span class="text-sm font-medium" style="color: {{ $article->feed->color ?? '#3b82f6' }}">
                                    {{ $article->feed->title }}
                                </span>
                                <span class="mx-2 text-gray-300">|</span>
                                <span class="text-xs text-gray-500">
                                    ID: {{ $article->feed_id }}, 
                                    Articles: {{ $article->feed->articles_count }}
                                </span>
                            </div>
                            
                            <!-- Контент -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Текстовый контент -->
                                <div class="col-span-2 space-y-2">
                                    @if($article->description)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Description:</p>
                                            <p class="text-sm text-gray-800 break-words max-h-40 overflow-y-auto">
                                                {{ Str::limit(strip_tags($article->description), 300) }}
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if($article->content)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Content:</p>
                                            <p class="text-sm text-gray-800 break-words max-h-40 overflow-y-auto">
                                                {{ Str::limit(strip_tags($article->content), 300) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Медиа и метаданные -->
                                <div class="space-y-3">
                                    @if($article->thumbnail)
                                        <div class="bg-gray-100 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Thumbnail:</p>
                                            <img src="{{ $article->thumbnail }}" alt="thumbnail" 
                                                class="max-w-full h-auto rounded border border-gray-200">
                                        </div>
                                    @endif
                                    
                                    <!-- Категории -->
                                    @if($article->categories && count($article->categories) > 0)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Categories:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($article->categories as $category)
                                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                                                        {{ $category }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Авторы -->
                                    @if($article->authors && count($article->authors) > 0)
                                        <div class="bg-gray-50 p-2 rounded">
                                            <p class="text-xs font-medium text-gray-500 mb-1">Authors:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($article->authors as $author)
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
                                    <span>ID: {{ $article->id }}</span>
                                    <span>Created: {{ $article->created_at->format('d.m.Y - H:i') }}</span>
                                    <span>Updated: {{ $article->updated_at->format('d.m.Y -  H:i') }}</span>
                                    @if($article->enclosures)
                                        <span>Enclosures: {{ count($article->enclosures) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Пагинация -->
                <div class="mt-6">
                    {{ $articles->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>