<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-xl font-bold text-gray-900 mb-4">Feed Items (Technical View)</h1>
                <p class="text-sm text-gray-500 mb-6">Total items: {{ $FeedItems->total() }}</p>

                <div class="space-y-6">
                    @foreach ($FeedItems as $item)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <!-- Item Header -->
                            <div class="mb-3">
                                <a href="{{ $item['link'] }}" 
                                   class="font-mono text-lg font-semibold break-all hover:text-blue-600" 
                                   style="color: {{ $item['color'] }}"
                                   target="_blank">
                                    {{ $item['title'] ?? 'No Title' }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    Published: {{ $item['published_at'] ?? 'N/A' }}
                                </p>
                            </div>

                            <!-- Main Content -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <!-- Text Content -->
                                <div class="col-span-2 space-y-2">
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-sm font-medium text-gray-700">Description:</p>
                                        <p class="text-sm text-gray-900 break-words max-h-40 overflow-y-auto">
                                            {{ Str::limit($item['description'] ?? 'No description', 500) }}
                                        </p>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-sm font-medium text-gray-700">Content:</p>
                                        <p class="text-sm text-gray-900 break-words max-h-40 overflow-y-auto">
                                            {{ Str::limit($item['content'] ?? 'No content', 500) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Media & Meta -->
                                <div class="space-y-3">
                                    @if($item['thumbnail'])
                                        <div class="bg-gray-100 p-2 rounded">
                                            <p class="text-sm font-medium text-gray-700 mb-1">Thumbnail:</p>
                                            <img src="{{ $item['thumbnail'] }}" alt="thumbnail" 
                                                class="max-w-full h-auto rounded border border-gray-200">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Data -->
                            <div class="space-y-3">
                                <!-- Authors -->
                                @if (!empty($item['authors']))
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-sm font-medium text-gray-700">Authors ({{ count($item['authors']) }}):</p>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($item['authors'] as $author)
                                                <span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $author }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Categories -->
                                @if (!empty($item['categories']))
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-sm font-medium text-gray-700">Categories ({{ count($item['categories']) }}):</p>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($item['categories'] as $cat)
                                                <span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $cat }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Enclosures -->
                                @if (!empty($item['enclosures']))
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-sm font-medium text-gray-700">Enclosures ({{ count($item['enclosures']) }}):</p>
                                        <div class="space-y-2 mt-2">
                                            @foreach ($item['enclosures'] as $enc)
                                                @if (isset($enc))
                                                    <div class="border-l-4 border-gray-300 pl-2">
                                                        <p class="text-xs break-all"><span class="font-medium">Link:</span> {{ $enc['link'] ?? 'N/A' }}</p>
                                                        <p class="text-xs"><span class="font-medium">Type:</span> {{ $enc['type'] ?? 'N/A' }}</p>
                                                        @if($enc['thumbnail'] ?? false)
                                                            <p class="text-xs"><span class="font-medium">Thumb:</span> {{ $enc['thumbnail'] }}</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $FeedItems->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>