<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feeds') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                {{-- <div class="text-gray-900">
                    <form action="{{ route('admin.feeds') }}" method="post">
                        @csrf
                        <x-text-input id="url" class="block mt-1 w-full" type="text" name="url" :value="old('url')"
                            required autofocus autocomplete="url" />
                        <x-primary-button type="submit">Submit</x-primary-button>
                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                    </form>
                </div> --}}
                <div class="flex flex-col gap-3">
                    {{-- {{dd($feed_items)}} --}}
                    @foreach ($feed_items as $item)
                        <div>
                            {{-- {{dd($item['thumbnail'], $item['enclosures'])}} --}}
                            <a href="{{ $item['link'] }}" class="underline" style="text-decoration-color: {{ $item['color'] }}" target="_blank">{{ $item['title'] }}</a>
                            <p>description: {{ $item['description'] }}</p>
                            <p>content: {{ $item['content'] }}</p>
                            <img src="{{ $item['thumbnail'] }}" alt="" class="w-1/4">
                            <p>published_at: {{ $item['published_at'] }}</p>
                            @if (isset($item['enclosures']))
                                <p>Enclosures:</p>
                                @foreach ($item['enclosures'] as $enc)
                                    @if (isset($enc))
                                        <p>{{ $enc['link'] }}</p>
                                        <p>{{ $enc['type'] }}</p>
                                        <p>{{ $enc['thumbnail'] }}</p>
                                        @endif
                                    @endforeach
                            @endif
                            @if (isset($item['authors']))
                                <p>Authors:</p>
                                @foreach ($item['authors'] as $author)
                                    <p>{{ $author }}</p>
                                @endforeach
                            @endif
                            @if (isset($item['categories']))
                                <p>Categories:</p>
                                @foreach ($item['categories'] as $cat)
                                    <p>{{ $cat }}</p>
                                @endforeach
                            @endif
                        </div>
                        
                        @if (last($feed_items) != $item)
                            <div class="border"></div>
                        @endif
                    @endforeach

                    <div class="mt-4">
                        {{ $feed_items->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>