<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Manage RSS Feeds</h1>
                
                <!-- Add Feed Form -->
                <div class="mb-8">
                    <form action="{{ route('admin.feeds') }}" method="post" class="space-y-4">
                        @csrf
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Feed URL</label>
                            <x-text-input id="url" class="block w-full" type="text" name="url" 
                                :value="old('url')" required autofocus autocomplete="url" 
                                placeholder="https://example.com/feed.xml" />
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>
                        <x-primary-button type="submit">Add Feed</x-primary-button>
                    </form>
                </div>

                <!-- Feeds List -->
                <div class="space-y-6">
                    <x-secondary-link 
                        href="{{ route('admin.save_FeedItems_all') }}">
                        Save all
                    </x-secondary-link>
                    @foreach ($feeds as $feed)
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <!-- Feed Header -->
                            <div class="flex items-center space-x-3 mb-3">
                                @if($feed->favicon)
                                    <img src="{{ $feed->favicon }}" alt="favicon" class="w-5 h-5">
                                @endif
                                <a href="{{ $feed->url }}" target="_blank" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                    {{ $feed->title ?: 'Untitled Feed' }}
                                </a>
                            </div>

                            <!-- Feed Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    @if (empty($feed->description))
                                        <p class="text-red-600 text-sm">No description available</p>
                                    @else
                                        <p class="text-gray-700">{{ $feed->description }}</p>
                                    @endif
                                </div>
                                
                                <div class="space-y-2">
                                    @if ($feed->image)
                                        <img src="{{ $feed->image }}" alt="feed image" class="max-h-40 rounded">
                                    @else
                                        <p class="text-red-600 text-sm">No image available</p>
                                    @endif
                                    
                                    @if ($feed->color)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-gray-700">Brand color:</span>
                                            <span class="w-4 h-4 rounded-full inline-block border border-gray-300" 
                                                style="background-color: {{ $feed->color }};"></span>
                                            <span>{{ $feed->color }}</span>
                                        </div>
                                    @endif
                                    
                                    @if ($feed->language)
                                        <p class="text-gray-700">Language: {{ strtoupper($feed->language) }}</p>
                                    @endif
                                    
                                    <p class="text-gray-700">Items: {{ $feed->items_count }}</p>
                                </div>
                            </div>

                            <!-- Feed Actions -->
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-link 
                                    href="{{ route('admin.FeedItems', ['id' => $feed->id]) }}">
                                    View Items
                                </x-secondary-link>

                                <x-secondary-link 
                                    href="{{ route('admin.save_FeedItems', ['id' => $feed->id]) }}">
                                    Save Items
                                </x-secondary-link>

                                <x-secondary-link 
                                    href="{{ route('admin.edit_feed', ['id' => $feed->id]) }}">
                                    Edit Feed
                                </x-secondary-link>
                            </div>
                            
                            <x-input-error :messages="$errors->get('save_FeedItems')" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>