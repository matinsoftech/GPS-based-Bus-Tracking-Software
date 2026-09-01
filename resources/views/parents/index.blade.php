<x-app-layout page="parent-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Parents</h1>
            <a href="{{ route('parents.create') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                Create Parent
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('parents.index') }}" method="GET" class="mb-4">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parents..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:flex-none">
                        Search
                    </button>

                    <a href="{{ route('parents.index') }}"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:flex-none">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Desktop / tablet: table view --}}
        <div
            class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">School</th>
                            <th class="px-5 py-3 font-medium">Children</th>
                            <th class="px-5 py-3 font-medium">Phone</th>
                            <th class="px-5 py-3 font-medium">Created</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($parents as $parent)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3 font-medium">{{ $parent->user->name }}</td>
                                <td class="px-5 py-3">{{ $parent->user->email }}</td>
                                <td class="px-5 py-3">{{ $parent->school->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($parent->children as $child)
                                            <span
                                                class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-200/60 dark:border-brand-500/20">
                                                {{ $child->full_name }}
                                            </span>
                                        @empty
                                            —
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $parent->phone }}</td>
                                <td class="px-5 py-3">{{ $parent->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('parents.show', $parent) }}"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            View
                                        </a>
                                        <a href="{{ route('parents.edit', $parent) }}"
                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('parents.destroy', $parent) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete {{ $parent->user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No parents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($parents as $parent)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-gray-900 dark:text-white">{{ $parent->user->name }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $parent->user->email }}</p>
                        </div>
                        <span
                            class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $parent->created_at->format('M d, Y') }}</span>
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">School</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $parent->school->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Phone</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $parent->phone }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3">
                        <dt class="text-xs text-gray-400 dark:text-gray-500">Children</dt>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @forelse ($parent->children as $child)
                                <span
                                    class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-200/60 dark:border-brand-500/20">
                                    {{ $child->full_name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-500 dark:text-gray-400">—</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('parents.show', $parent) }}"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            View
                        </a>
                        <a href="{{ route('parents.edit', $parent) }}"
                            class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                            Edit
                        </a>
                        <form action="{{ route('parents.destroy', $parent) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete {{ $parent->user->name }}?');"
                            class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No parents found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $parents->links() }}
        </div>
    </div>
</x-app-layout>
