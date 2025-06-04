<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Карточки со статистикой -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Карточка фидов -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Фиды</p>
                            <p class="text-2xl font-bold">{{ $stat->feeds_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- Карточка статей -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Статьи</p>
                            <p class="text-2xl font-bold">{{ $stat->articles_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- Карточка пользователей -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Пользователи</p>
                            <p class="text-2xl font-bold">{{ $stat->users_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Диаграмма -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 mx-[40%]">Самые большие фиды</h2>
                <div class="mt-6">
                    <!-- Контейнер для графика -->
                    <canvas id="feedChart" class="h-64"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Получаем контекст canvas
        const ctx = document.getElementById('feedChart').getContext('2d');

        // Данные для графика
        const feedNames = @json($feedNames); // Имена фидов
        const articlesCount = @json($articlesCount); // Количество записей
        const feedColors = @json($feedColors); // Цвета фидов

        // Создаем график
        const feedChart = new Chart(ctx, {
            type: 'bar', // Тип графика (столбчатая диаграмма)
            data: {
                labels: feedNames, // Имена фидов по оси X
                datasets: [{
                    label: '', // Название набора данных
                    data: articlesCount, // Данные по оси Y
                    backgroundColor: feedColors, // Цвет столбцов
                    borderColor: feedColors,  // Цвет границ
                    borderWidth: 0 // Ширина границ
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true // Начинать ось Y с нуля
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</x-app-layout>