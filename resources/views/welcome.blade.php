<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Open CRM') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-4 py-12">
            <section class="w-full rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Open CRM</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                    CRM base listo para crecer
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                    Proyecto construido con Laravel 13, Livewire 4 y Flux UI. Incluye autenticacion con Fortify,
                    permisos por cuenta, onboarding interactivo y datos demo para pruebas.
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @auth
                        <a
                            href="{{ route('crm.dashboard') }}"
                            class="inline-flex items-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                        >
                            Entrar al CRM
                        </a>
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            Dashboard base
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                        >
                            Iniciar sesion
                        </a>
                    @endauth
                </div>

                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Acceso Demo</p>
                    <div class="mt-2 grid gap-2 text-sm text-emerald-900 dark:text-emerald-100 sm:grid-cols-2">
                        <p>
                            <span class="font-medium">Email:</span>
                            demo@opencrm.test
                        </p>
                        <p>
                            <span class="font-medium">Password:</span>
                            password
                        </p>
                    </div>
                    <p class="mt-2 text-xs text-emerald-800/90 dark:text-emerald-200/80">
                        Usa estas credenciales para ingresar y probar el CRM sin crear nuevas cuentas.
                    </p>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h2 class="text-sm font-semibold">Modulos CRM</h2>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Leads, empresas, contactos, pipeline, deals, tareas y reportes.</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h2 class="text-sm font-semibold">Modo demo</h2>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Seeder listo con usuario demo para explorar todo el flujo.</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h2 class="text-sm font-semibold">Onboarding</h2>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Tutorial guiado por modulo con estado persistente por cuenta.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
