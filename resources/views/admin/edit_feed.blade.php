<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feeds') }}
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-900">
                    <form method="POST" action="{{ route('admin.update_feed', $feed->id) }}">
                        @method('PUT')
                        @csrf

                        <!-- Основные поля -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Название -->
                            <div>
                                <x-input-label for="title" :value="__('Название')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                    :value="old('title', $feed->title ?? '')" required autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <!-- URL ленты -->
                            <div>
                                <x-input-label for="url" :value="__('URL ленты (RSS/Atom)')" />
                                <x-text-input id="url" class="block mt-1 w-full" type="url" name="url"
                                    :value="old('url', $feed->url ?? '')" required />
                                <x-input-error :messages="$errors->get('url')" class="mt-2" />
                            </div>

                            <!-- URL сайта -->
                            <div>
                                <x-input-label for="site_url" :value="__('URL сайта')" />
                                <x-text-input id="site_url" class="block mt-1 w-full" type="url" name="site_url"
                                    :value="old('site_url', $feed->site_url ?? '')" />
                                <x-input-error :messages="$errors->get('site_url')" class="mt-2" />
                            </div>

                            <!-- Язык -->
                            <div>
                                <x-input-label for="language" :value="__('Язык контента')" />
                                <x-text-input id="language" class="block mt-1 w-full" type="text" name="language"
                                    :value="old('language', $feed->language ?? '')" placeholder="ru, en и т.д." />
                                <x-input-error :messages="$errors->get('language')" class="mt-2" />
                            </div>

                            <!-- Категория -->
                            <div>
                                <x-input-label for="category" :value="__('Категория')" />
                                <x-text-input id="category" class="block mt-1 w-full" type="text" name="category"
                                    :value="old('category', $feed->category ?? '')" />
                                <x-input-error :messages="$errors->get('category')" class="mt-2" />
                            </div>

                            <!-- Частота обновления -->
                            <div>
                                <x-input-label for="update_frequency" :value="__('Частота обновления (минуты)')" />
                                <x-text-input id="update_frequency" class="block mt-1 w-full" type="number"
                                    name="update_frequency" :value="old('update_frequency', $feed->update_frequency ?? 60)" min="5" />
                                <x-input-error :messages="$errors->get('update_frequency')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Описание -->
                        <div class="mt-6">
                            <x-input-label for="description" :value="__('Описание')" />
                            <textarea id="description"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                name="description"
                                rows="3">{{ old('description', $feed->description ?? '') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Поля для отображения -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <!-- Иконка (favicon) -->
                            <div>
                                <x-input-label for="favicon" :value="__('URL иконки (favicon)')" />
                                <x-text-input id="favicon" class="block mt-1 w-full" type="url" name="favicon"
                                    :value="old('favicon', $feed->favicon ?? '')" />
                                <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                            </div>

                            <!-- Обложка -->
                            <div>
                                <x-input-label for="image" :value="__('URL обложки')" />
                                <x-text-input id="image" class="block mt-1 w-full" type="url" name="image"
                                    :value="old('image', $feed->image ?? '')" />
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            </div>

                            <!-- Цветовой акцент -->
                            <div>
                                <x-input-label for="color" :value="__('Цвет (HEX)')" />
                                <input id="color" data-maska=""
                                    class="block mt-1 w-full h-10 p-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    type="text" name="color" value="{{ old('color', $feed->color ?? '#3b82f6') }}" />
                                <x-input-error :messages="$errors->get('color')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Технические настройки -->
                        <div class="mt-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Технические настройки</h3>

                            <!-- Активность -->
                            <div class="flex items-center">
                                <input id="is_active" name="is_active" type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_active', $feed->is_active) ? 'checked' : '' }}>
                                <x-input-label for="is_active" :value="__('Активный источник')" class="ml-2" />
                            </div>

                            <!-- ETag -->
                            <div>
                                <x-input-label for="etag" :value="__('ETag (кеширование)')" />
                                <x-text-input id="etag" class="block mt-1 w-full" type="text" name="etag" disabled
                                    :value="old('etag', $feed->etag ?? '')" />
                                <x-input-error :messages="$errors->get('etag')" class="mt-2" />
                            </div>

                            <!-- Последнее изменение -->
                            <div>
                                <x-input-label for="last_modified" :value="__('Дата последнего изменения')" />
                                <input id="last_modified"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    type="datetime-local" name="last_modified" disabled
                                    value="{{ old('last_modified', isset($feed->last_modified) ? \Carbon\Carbon::parse($feed->last_modified)->format('Y-m-d\TH:i') : '') }}" />
                                <x-input-error :messages="$errors->get('last_modified')" class="mt-2" />
                            </div>
                        </div>
                        <x-primary-button type="submit">Submit</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>