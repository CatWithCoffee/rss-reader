<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Спасибо за регистрацию! Прежде чем начать, не могли бы вы подтвердить свой адрес электронной почты, нажав на ссылку, которую мы только что отправили вам по электронной почте? Если вы не получили письмо, мы с радостью отправим вам другое.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('На адрес электронной почты, указанный вами при регистрации, была отправлена ​​новая ссылка для подтверждения.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Повторно отправить письмо с подтверждением') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                {{ __('Выйти') }}
            </button>
        </form>
    </div>
</x-guest-layout>
