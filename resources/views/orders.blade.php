<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Добавить RSS-источник</h1>

            <!-- Форма добавления фида -->
            <div class="mb-8">
                <form action="{{ route('orders.store') }}" method="post" class="space-y-4">
                    @csrf
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Ссылка на RSS-фид</label>
                        <x-text-input id="url" class="block w-full" type="text" name="url" :value="old('url')" required
                            autofocus autocomplete="url" placeholder="https://example.com/feed.xml" />
                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                    </div>
                    <x-primary-button type="submit">Отправить на рассмотрение</x-primary-button>
                </form>
            </div>

            <!-- Список заявок пользователя -->
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Мои заявки</h2>

                @forelse ($orders as $order)
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <div class="flex justify-between items-start">
                            <!-- Информация о фиде -->
                            <div class="flex items-center space-x-3">
                                @if($order->favicon)
                                    <img src="{{ $order->favicon }}" alt="favicon" class="w-5 h-5">
                                @endif
                                <div>
                                    <a href="{{ $order->url }}" target="_blank"
                                        class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                        {{ $order->title ?: 'Без названия' }}
                                    </a>
                                    <p class="text-sm text-gray-600 line-clamp-1">{{ $order->description }}</p>
                                </div>
                            </div>

                            <!-- Статус и счётчик -->
                            <div class="flex flex-col items-end">
                                @if($order->status === 'accepted')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        Одобрено
                                    </span>
                                    @if($order->feed->articles_count > 0)
                                        <span class="text-sm text-gray-500 mt-1">
                                            Добавлено уже {{ $order->feed->articles_count }} статей
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 mt-1">
                                            Статьи пока не добавлены
                                        </span>
                                    @endif
                                @else
                                    @if($order->status === 'new')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                            На рассмотрении
                                        </span>
                                    @elseif($order->status === 'accepted')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                            Одобрено
                                        </span>
                                    @elseif($order->status === 'rejected')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                            Отклонено
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Вы пока не отправляли заявки на добавление RSS-источников</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>