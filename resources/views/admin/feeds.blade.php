<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Управление RSS-фидами</h1>
                
                <!-- Форма добавления фида -->
                <div class="mb-8">
                    <form action="{{ route('admin.feeds') }}" method="post" class="space-y-4">
                        @csrf
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL фида</label>
                            <x-text-input id="url" class="block w-full" type="text" name="url" 
                                :value="old('url')" required autofocus autocomplete="url" 
                                placeholder="https://example.com/feed.xml" />
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>
                        <x-primary-button type="submit">Добавить</x-primary-button>
                    </form>
                </div>

                <!-- Список фидов -->
                <div class="space-y-6">
                    <x-secondary-link 
                        href="{{ route('admin.save_Articles_all') }}">
                        Сканировать всё
                    </x-secondary-link>
                    @foreach ($feeds as $feed)
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <!-- Заголовок фида -->
                            <div class="flex items-center space-x-3 mb-3">
                                @if($feed->favicon)
                                    <img src="{{ $feed->favicon }}" alt="favicon" class="w-5 h-5">
                                @endif
                                <a href="{{ $feed->url }}" target="_blank" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                    {{ $feed->title ?: 'Без названия' }}
                                </a>
                            </div>

                            <!-- Детали фида -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    @if (empty($feed->description))
                                        <p class="text-red-600 text-sm">Описание отсутствует</p>
                                    @else
                                        <p class="text-gray-700">{{ $feed->description }}</p>
                                    @endif
                                </div>
                                
                                <div class="space-y-2">
                                    @if ($feed->image)
                                        <img src="{{ $feed->image }}" alt="feed image" class="max-h-40 rounded">
                                    @else
                                        <p class="text-red-600 text-sm">Изображение отсутствует</p>
                                    @endif
                                    
                                    @if ($feed->color)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-gray-700">Цвет бренда:</span>
                                            <span class="w-4 h-4 rounded-full inline-block border border-gray-300" 
                                                style="background-color: {{ $feed->color }};"></span>
                                            <span>{{ $feed->color }}</span>
                                        </div>
                                    @endif
                                    
                                    @if ($feed->language)
                                        <p class="text-gray-700">Язык: {{ strtoupper($feed->language) }}</p>
                                    @endif
                                    
                                    <p class="text-gray-700">Элементов: {{ $feed->articles_count }}</p>
                                </div>
                            </div>

                            <!-- Действия с фидом -->
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-link 
                                    href="{{ route('admin.Articles', ['id' => $feed->id]) }}">
                                    Просмотреть записи
                                </x-secondary-link>

                                <x-secondary-link 
                                    href="{{ route('admin.save_Articles', ['id' => $feed->id]) }}">
                                    Сканировать
                                </x-secondary-link>

                                <x-secondary-link 
                                    href="{{ route('admin.edit_feed', ['id' => $feed->id]) }}">
                                    Редактировать
                                </x-secondary-link>
                            </div>
                            
                            <x-input-error :messages="$errors->get('save_Articles')" class="mt-2" />
                        </div>
                    @endforeach
                </div>
                {{$feeds->links()}}
            </div>
        </div>
    </div>
</x-app-layout>
