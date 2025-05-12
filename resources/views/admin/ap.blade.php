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
                    <p>Users: {{ $stat->users_count }}</p>
                    <p>Feeds: {{ $stat->feeds_count }}</p>
                    <p>Articles: {{ $stat->items_count }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>