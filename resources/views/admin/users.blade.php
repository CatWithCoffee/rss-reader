<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-xl font-bold text-gray-900 mb-4">Пользователи</h1>
                <div class="px-4 sm:px-6 lg:px-10 py-6 text-gray-900 flex flex-col">
                    @if ($users->isEmpty())
                        <p>Нет пользователей</p>
                    @else
                        <!-- Заголовки столбцов -->
                        <div class="grid grid-cols-6 justify-items-center font-semibold border-b-2 border-gray-200 pb-2">
                            <h2>ID</h2>
                            <h2>Логин</h2>
                            <h2>Имя</h2>
                            <h2>Email</h2>
                            <h2>Роль</h2>
                            <h2>Создан</h2>
                        </div>

                        <!-- Список пользователей -->
                        @foreach ($users as $user)
                            <div class="grid grid-cols-6 items-center justify-items-center py-3">
                                <p>{{ $user->id }}</p>
                                <p>{{ $user->login }}</p>
                                <p>{{ $user->name }}</p>
                                <p>{{ $user->email }}</p>
                                <p>{{ $user->role }}</p>
                                <p>{{ $user->created_at->format('d.m.Y - H:i') }}</p>
                            </div>
                            <!-- Разделитель между пользователями -->
                            @if (!$loop->last)
                                <div class="border-b border-gray-100"></div>
                            @endif
                        @endforeach

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
