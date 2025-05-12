<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feeds') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-900">
                    <form action="{{ route('admin.feeds') }}" method="post">
                        @csrf
                        <x-text-input id="url" class="block mt-1 w-full" type="text" name="url" :value="old('url')"
                            required autofocus autocomplete="url" />
                        <x-primary-button type="submit">Submit</x-primary-button>
                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                    </form>
                </div>
                <div class="flex flex-col gap-3">
                    @foreach ($feeds as $feed)
                        <div class=" flex-grow text-gray-900">
                            <a href="{{$feed->url}}" class="flex items-center" target="_blank">
                                <img src="{{$feed->favicon}}" alt="favicon">
                                {{ $feed->title }}
                            </a>
                            @if (empty($feed->description))
                                <p class="text-justify text-red-700">Missing description</p>
                            @else
                                <p class="text-justify">{{  $feed->description }}</p>
                            @endif
                            @if (empty($feed->image))
                                <p class="text-justify text-red-700">Missing image</p>
                            @else
                                <img src="{{ $feed->image }}" alt="image">
                            @endif
                            @if (empty($feed->color))
                                <p class="text-justify text-red-700">Missing color</p>
                            @else
                                <p style="color: {{ $feed->color }};">Color: {{ $feed->color }}</p>
                            @endif
                            @if (empty($feed->language))
                                <p class="text-justify text-red-700">Missing language</p>
                            @else
                                <p>Lang:{{ $feed->language }}</p>
                            @endif
                            <p><a href="{{ route('admin.direct_feed_items', ['id' => $feed->id])}}" target="">direct items link</a></p>
                            <p><a href="{{ route('admin.save_feed_items', ['id' => $feed->id])}}" target="">save items</a></p>
                            <p><a href="{{ route('admin.edit_feed', ['id' => $feed->id])}}" target="">edit feed</a></p>
                            <x-input-error :messages="$errors->get('save_feed_items')" class="mt-2" />
                        </div>

                        @if (!last($feeds) == $feed)
                            <div class="border"></div>
                        @endif
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-app-layout>