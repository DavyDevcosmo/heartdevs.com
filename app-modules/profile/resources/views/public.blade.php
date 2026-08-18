<x-profile::layout.guest :title="$profile->name">
    <main class="mx-auto max-w-5xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ $profile->name }}</h1>
        <p class="text-zinc-500 dark:text-zinc-400">{{ '@'.$profile->username }}</p>
    </main>
</x-profile::layout.guest>
