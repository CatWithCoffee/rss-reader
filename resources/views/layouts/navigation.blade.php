<nav x-data="{ open: false }" class="bg-white">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-gray-100">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Главная') }}
                    </x-nav-link>
                    
                    @if (Auth::user())
                        <x-nav-link :href="route('favorites')" :active="request()->routeIs('favorites')">
                            {{ __('Избранное') }}
                        </x-nav-link>
                        <x-nav-link :href="route('orders')" :active="request()->routeIs('orders')">
                            {{ __('Добавить источник') }}
                        </x-nav-link>
                    @else
                        <div class="flex items-center text-sm font-normal">
                            <a href="{{ route('login') }}" class="underline hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Войдите в аккаунт</a>, чтобы получить доступ к списку избранного
                            </div>
                    @endif
                    @if (Auth::user() && Auth::user()->role == 'admin')
                        <x-nav-link :href="route('admin')" :active="request()->routeIs('admin') || request()->is('admin/*')">
                            {{ __('Панель администратора') }}
                        </x-nav-link>
                    @endif
                    
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            @if(Auth::user())
                                <div>{{ Auth::user()->name }}</div>
                            @else
                                <div>{{ __('Аккаунт') }}</div>
                            @endif

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if(Auth::user())
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Профиль') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Выйти') }}
                            </x-dropdown-link>
                        </form>
                        @else
                            <x-dropdown-link :href="route('login')">
                                {{ __('Войти') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('register')">
                                {{ __('Зарегистрироваться') }}
                            </x-dropdown-link>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>
            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if (request()->is('admin*'))
        <div class="max-w-7xl mx-auto px-8 lg:px-12 border-b border-gray-100 hidden sm:block">
            <div class="flex justify-between h-16">
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('admin.feeds')" :active="request()->routeIs('admin.feeds')">
                        {{ __('Источники') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('admin.Articles_all')" :active="request()->routeIs('admin.Articles*')">
                        {{ __('Статьи') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users*')">
                        {{ __('Пользователи') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('admin.orders')" :active="request()->routeIs('admin.orders*')">
                        {{ __('Заявки') }}
                    </x-nav-link>
                </div>
            </div>
        </div>
    @endif

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Главная
            </x-responsive-nav-link>
            @if (Auth::user())
                <x-responsive-nav-link :href="route('favorites')" :active="request()->routeIs('favorites')">
                    Избранное
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders')" :active="request()->routeIs('orders')">
                    Добавить источник
                </x-responsive-nav-link>
            @else
                <div class="font-medium text-sm text-gray-800 px-4 py-2">
                    <a href="{{ route('login') }}" class="underline hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Войдите в аккаунт</a>, чтобы получить доступ к списку избранного
                </div>
            @endif
            @if (Auth::user() && Auth::user()->role == 'admin')
                <x-responsive-nav-link :href="route('admin')" :active="request()->routeIs('admin')">
                    Панель администратора
                </x-responsive-nav-link>
                @if(request()->is('admin*'))
                    <x-responsive-nav-link :href="route('admin.feeds')" :active="request()->routeIs('admin.feeds')">
                        Источники
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.Articles_all')" :active="request()->routeIs('admin.Articles_all')">
                        Статьи
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
                        Пользователи
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.orders')" :active="request()->routeIs('admin.orders*')">
                        Заявки
                    </x-responsive-nav-link>
                @endif
            @endif
            
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                @if (Auth::user())
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @else
                    <div class="font-medium text-base text-gray-800">Аккаунт</div>
                @endif
                
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Профиль') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Выйти') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>