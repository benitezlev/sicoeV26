<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Control Escolar') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Flux Appearance (Dark Mode) -->
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 antialiased font-sans">
        <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:brand href="{{ route('dashboard') }}" logo="{{ asset('img/Logo-UMS-1.png') }}" name="SICOE" class="px-2" />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="squares-2x2" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">Panel</flux:navlist.item>
                <flux:navlist.item icon="cog-6-tooth" href="{{ route('config.index') }}" :current="request()->routeIs('config.*')">Configuración</flux:navlist.item>
                <flux:navlist.item icon="shield-check" href="{{ route('roles') }}" :current="request()->routeIs('roles')">Roles</flux:navlist.item>
                <flux:navlist.item icon="building-library" href="{{ route('plantel.index') }}" :current="request()->routeIs('plantel.*')">Planteles</flux:navlist.item>
                <flux:navlist.item icon="users" href="{{ route('alumnos.index') }}" :current="request()->routeIs('alumnos.*')">Usuarios</flux:navlist.item>
                <flux:navlist.item icon="folder-open" href="{{ route('expedientes.index') }}" :current="request()->routeIs('expedientes.*')">Expedientes</flux:navlist.item>
                <flux:navlist.item icon="academic-cap" href="{{ route('profesores') }}" :current="request()->routeIs('profesores.*')">Docentes</flux:navlist.item>
                <flux:navlist.item icon="book-open" href="{{ route('cursos.index') }}" :current="request()->routeIs('cursos.*')">Cursos</flux:navlist.item>
                <flux:navlist.item icon="bookmark" href="{{ route('materias.index') }}" :current="request()->routeIs('materias.*')">Materias</flux:navlist.item>
                <flux:navlist.item icon="link" href="{{ route('panel.materias') }}" :current="request()->routeIs('panel.materias*')">Materia-Curso</flux:navlist.item>
                <flux:navlist.item icon="user-group" href="{{ route('grupos.index') }}" :current="request()->routeIs('grupos.*')">Grupos</flux:navlist.item>
                <flux:navlist.item icon="check-badge" href="{{ route('asistencias.index') }}" :current="request()->routeIs('asistencias.index')">Asistencias</flux:navlist.item>
                <flux:navlist.item icon="pencil-square" href="{{ route('calificaciones.index') }}" :current="request()->routeIs('calificaciones.*')">Calificaciones</flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="question-mark-circle" href="#">Ayuda</flux:navlist.item>
            </flux:navlist>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:profile avatar="{{ Auth::user()->profile_photo_url }}" name="{{ Auth::user()->name }}" />

                <flux:menu>
                    <flux:menu.item icon="user" href="{{ route('profile.show') }}">Perfil</flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}" @click.prevent="$root.submit();">
                            Cerrar Sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <flux:sidebar.toggle icon="bars-2" />
            <flux:spacer />
            <flux:dropdown>
                <flux:avatar src="{{ Auth::user()->profile_photo_url }}" size="xs" />
                <flux:menu>
                    <flux:menu.item href="{{ route('profile.show') }}">Perfil</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}" @click.prevent="$root.submit();">
                            Cerrar Sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main>
            @if (isset($header))
                <div class="mb-10">
                    <flux:heading size="xl" level="1">{{ $header }}</flux:heading>
                    @if (isset($subheading))
                        <flux:subheading size="lg" class="mt-2">{{ $subheading }}</flux:subheading>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
