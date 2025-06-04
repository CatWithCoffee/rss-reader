<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Заявки на добавление RSS-источников</h1>

                <!-- Список заявок -->
                <div class="space-y-6">
                    @if($orders->isNotEmpty())
                        @foreach($orders as $order)
                            <div class="bg-gray-50 p-4 rounded-lg shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <!-- Информация о фиде -->
                                    <div class="flex items-start space-x-3 flex-1">
                                        @if($order->favicon)
                                            <img src="{{ $order->favicon }}" alt="favicon" class="w-5 h-5 mt-1">
                                        @endif
                                        <div class="flex-1">
                                            <a href="{{ $order->url }}" target="_blank" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                {{ $order->title ?: 'Без названия' }}
                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">{{ $order->description }}</p>
                                            <div class="mt-2 text-xs text-gray-500">
                                                <span>Добавлено: {{ $order->created_at->format('d.m.Y H:i') }}</span>
                                                <span class="mx-2">|</span>
                                                <span>Отправитель: {{ $order->user->name }} / {{ $order->user->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
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
                                </div>

                                <!-- Действия (только для новых заявок) -->
                                @if($order->status === 'new')
                                    <div class="flex flex-wrap gap-3 justify-end border-t pt-3">
                                        <form method="POST" action="{{ route('admin.orders.accept', $order->id) }}">
                                            @csrf
                                            <x-primary-button type="submit" class="!bg-green-600 hover:!bg-green-700">
                                                Одобрить
                                            </x-primary-button>
                                        </form>
                                        
                                        <form method="POST" action="{{ route('admin.orders.reject', $order->id) }}">
                                            @csrf
                                            <x-danger-button type="submit">
                                                Отклонить
                                            </x-danger-button>
                                        </form>
                                    </div>
                                @else
                                    <!-- Информация о решении -->
                                    <div class="border-t pt-3 text-sm text-gray-500">
                                        @if($order->status === 'accepted')
                                            <p>Заявка была одобрена {{ $order->updated_at->format('d.m.Y H:i') }}</p>
                                        @elseif($order->status === 'rejected')
                                            <p>Заявка была отклонена {{ $order->updated_at->format('d.m.Y H:i') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Нет заявок на рассмотрение</h3>
                            <p class="mt-1 text-gray-500">Все заявки обработаны</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>