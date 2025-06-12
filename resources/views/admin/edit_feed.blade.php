<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Заголовок и кнопки -->
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Редактирование источника</h1>
                    <div class="flex space-x-2">
                        <x-secondary-link href="{{ route('admin.feeds') }}">
                            Назад к списку
                        </x-secondary-link>
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('admin.update_feed', $feed->id) }}" class="space-y-8">
                        @method('PUT')
                        @csrf

                        <!-- Основные настройки -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-medium text-gray-900 border-b pb-2">Основные настройки</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Название -->
                                <div>
                                    <x-input-label for="title" :value="__('Название*')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                        :value="old('title', $feed->title ?? '')" required autofocus 
                                        placeholder="Название источника"/>
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <!-- URL ленты -->
                                <div>
                                    <x-input-label for="url" :value="__('URL RSS/Atom*')" />
                                    <x-text-input id="url" class="block mt-1 w-full" type="url" name="url"
                                        :value="old('url', $feed->url ?? '')" required 
                                        placeholder="https://example.com/feed.xml"/>
                                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                </div>

                                <!-- URL сайта -->
                                <div>
                                    <x-input-label for="site_url" :value="__('URL сайта')" />
                                    <x-text-input id="site_url" class="block mt-1 w-full" type="url" name="site_url"
                                        :value="old('site_url', $feed->site_url ?? '')" 
                                        placeholder="https://example.com"/>
                                    <x-input-error :messages="$errors->get('site_url')" class="mt-2" />
                                </div>

                                <!-- Язык -->
                                <div>
                                    <x-input-label for="language" :value="__('Язык контента')" />
                                    <x-text-input id="language" class="block mt-1 w-full" type="text" name="language"
                                        :value="old('language', $feed->language ?? '')" 
                                        placeholder="ru, en, es и т.д."/>
                                    <x-input-error :messages="$errors->get('language')" class="mt-2" />
                                </div>

                                <!-- Категория -->
                                <div>
                                    <x-input-label for="category" :value="__('Категория')" />
                                    <x-text-input id="category" class="block mt-1 w-full" type="text" name="category"
                                        :value="old('category', $feed->category ?? '')" 
                                        placeholder="Новости, Блоги, Технологии"/>
                                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                                </div>

                                <!-- Частота обновления -->
                                <div>
                                    <x-input-label for="update_frequency" :value="__('Частота обновления (мин)*')" />
                                    <x-text-input id="update_frequency" class="block mt-1 w-full" type="number"
                                        name="update_frequency" :value="old('update_frequency', $feed->update_frequency ?? 60)" 
                                        min="5" step="5" />
                                    <x-input-error :messages="$errors->get('update_frequency')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Описание -->
                            <div>
                                <x-input-label for="description" :value="__('Описание')" />
                                <textarea id="description"
                                    class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
                                    name="description"
                                    rows="3"
                                    placeholder="Краткое описание источника">{{ old('description', $feed->description ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Визуальные настройки -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-medium text-gray-900 border-b pb-2">Визуальные настройки</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Иконка -->
                                <div>
                                    <x-input-label for="favicon" :value="__('URL иконки (favicon)')" />
                                    <x-text-input id="favicon" class="block mt-1 w-full" type="url" name="favicon"
                                        :value="old('favicon', $feed->favicon ?? '')" 
                                        placeholder="https://example.com/favicon.ico"/>
                                    @if($feed->favicon)
                                        <div class="mt-2 flex items-center space-x-2">
                                            <span class="text-sm text-gray-500">Текущая:</span>
                                            <img src="{{ $feed->favicon }}" alt="favicon" class="h-5 w-5">
                                        </div>
                                    @endif
                                    <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                                </div>

                                <!-- Обложка -->
                                <div>
                                    <x-input-label for="image" :value="__('URL обложки')" />
                                    <x-text-input id="image" class="block mt-1 w-full" type="url" name="image"
                                        :value="old('image', $feed->image ?? '')" 
                                        placeholder="https://example.com/image.jpg"/>
                                    @if($feed->image)
                                        <div class="mt-2">
                                            <img src="{{ $feed->image }}" alt="preview" class="h-20 rounded border">
                                        </div>
                                    @endif
                                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                </div>

                                <!-- Цвет -->
                                <div>
                                    <x-input-label for="color" :value="__('Цвет бренда (HEX)')" />
                                    <div class="flex items-center space-x-2 mt-1">
                                        <input id="color" type="color" 
                                            class="h-10 w-10 rounded border border-gray-300 cursor-pointer"
                                            value="{{ old('color', $feed->color ?? '#3b82f6') }}" 
                                            oninput="document.getElementById('color_text').value = this.value">
                                        <x-text-input id="color_text" class="block w-full" type="text" name="color"
                                            :value="old('color', $feed->color ?? '#3b82f6')" 
                                            placeholder="#3b82f6" data-maska=""/>
                                    </div>
                                    <x-input-error :messages="$errors->get('color')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Технические настройки -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-medium text-gray-900 border-b pb-2">Технические настройки</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Статус -->
                                <div class="flex items-start space-x-3">
                                    <div class="flex items-center h-5">
                                        <input id="is_active" name="is_active" type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 h-4 w-4" 
                                            {{ old('is_active', $feed->is_active ?? true) ? 'checked' : '' }}>
                                    </div>
                                    <div class="text-sm">
                                        <x-input-label for="is_active" :value="__('Активный источник')" />
                                        <p class="text-gray-500">Если отключено, источник не будет обновляться</p>
                                    </div>
                                </div>

                                <!-- ETag -->
                                <div>
                                    <x-input-label for="etag" :value="__('ETag (кеширование)')" />
                                    <x-text-input id="etag" class="block mt-1 w-full bg-gray-100" type="text" 
                                        name="etag" readonly
                                        :value="old('etag', $feed->etag ?? 'Не установлен')" />
                                    <x-input-error :messages="$errors->get('etag')" class="mt-2" />
                                </div>
                                
                                <!-- Статистика -->
                                <div>
                                    <x-input-label value="Статистика" />
                                    <div class="mt-1 text-sm text-gray-900 bg-gray-50 p-2 rounded">
                                        <p>Записей: {{ $feed->articles_count ?? 0 }}</p>
                                        <p>Последнее обновление: {{ $feed->last_fetched_at ? \Carbon\Carbon::parse($feed->last_fetched_at)->format('d.m.Y H:i') : 'Никогда' }}</p>
                                    </div>
                                </div>

                                <!-- Последнее изменение -->
                                <div>
                                    <x-input-label for="last_modified" :value="__('Последнее изменение')" />
                                    <x-text-input id="last_modified" class="block mt-1 w-full bg-gray-100" 
                                        type="text" name="last_modified" readonly
                                        value="{{ old('last_modified', $feed->last_modified ? \Carbon\Carbon::parse($feed->last_modified)->format('d.m.Y H:i') : 'Не установлено') }}" />
                                    <x-input-error :messages="$errors->get('last_modified')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <x-secondary-link href="{{ route('admin.feeds') }}">
                                Отмена
                            </x-secondary-link>
                            <x-primary-button type="submit">
                                Сохранить изменения
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Синхронизация color picker и текстового поля
        document.getElementById('color_text').addEventListener('input', function(e) {
            document.getElementById('color').value = e.target.value;
        });
    </script>
</x-app-layout>