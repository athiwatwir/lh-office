<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name', 'TailAdmin') }}</title>

    @vite(['resources/css/app.css', 'resources/css/custom-app.css', 'resources/js/app.js'])

    @stack('styles')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme || 'light';
                    this.updateTheme();
                }
                , theme: 'light'
                , toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                }
                , updateTheme() {
                    const html = document.documentElement;

                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        document.body?.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        document.body?.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280
                , isMobileOpen: false
                , isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });

            Alpine.store('activeAgent', {
                modalOpen: false,
                required: false,
                open() {
                    this.required = false;
                    this.modalOpen = true;
                },
                close() {
                    if (! this.required) {
                        this.modalOpen = false;
                    }
                },
                setRequired(value) {
                    this.required = value;
                    if (value) {
                        this.modalOpen = true;
                    }
                }
            });

            Alpine.store('notify', {
                items: [],
                show(variant, message, duration = 4000) {
                    const id = Date.now() + Math.random();
                    const item = { id, variant, message, visible: true };

                    this.items.push(item);

                    setTimeout(() => this.dismiss(id), duration);
                },
                success(message) {
                    this.show('success', message);
                },
                error(message) {
                    this.show('error', message);
                },
                dismiss(id) {
                    const item = this.items.find((entry) => entry.id === id);

                    if (! item) {
                        return;
                    }

                    item.visible = false;

                    setTimeout(() => {
                        this.items = this.items.filter((entry) => entry.id !== id);
                    }, 200);
                },
            });

            @if (session('success'))
            Alpine.store('notify').success(@js(session('success')));
            @endif
            @if (session('error'))
            Alpine.store('notify').error(@js(session('error')));
            @endif
        });

    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || 'light';
            const html = document.documentElement;

            if (theme === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        })();

    </script>
</head>

<body x-data x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    <x-common.preloader />
    <x-common.toast-container />

    @auth
        <x-workspace.agent-selector-modal />
    @endauth

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out" :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            @include('layouts.app-header')
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
