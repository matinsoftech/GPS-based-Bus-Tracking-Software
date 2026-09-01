<x-app-layout page="roles-permissions">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Roles &amp; Permissions</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage roles and the permissions granted to each role.
                </p>
            </div>
            <a href="{{ route('roles.create') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                Create Role
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('roles.index') }}" method="GET" class="mb-4">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:flex-none">
                        Search
                    </button>

                    <a href="{{ route('roles.index') }}"
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
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Users</th>
                            <th class="px-5 py-3 font-medium">Permissions</th>
                            <th class="px-5 py-3 font-medium">Guard</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($roles as $role)
                            @php $locked = $role->name === 'Super Admin'; @endphp
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $role->name }}</span>
                                        @if ($locked)
                                            <span
                                                class="rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                                Locked
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $role->users_count }}</td>
                                <td class="px-5 py-3">{{ $role->permissions_count }}</td>
                                <td class="px-5 py-3">{{ $role->guard_name }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('roles.show', $role) }}"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            View
                                        </a>
                                        @if (!$locked)
                                            <a href="{{ route('roles.edit', $role) }}"
                                                class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                                Edit
                                            </a>
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete {{ $role->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($roles as $role)
                @php $locked = $role->name === 'Super Admin'; @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $role->name }}</span>
                        @if ($locked)
                            <span
                                class="rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                Locked
                            </span>
                        @endif
                    </div>

                    <dl class="mt-3 grid grid-cols-3 gap-x-3 gap-y-1.5 text-xs">
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Users</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $role->users_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Permissions</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $role->permissions_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Guard</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $role->guard_name }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('roles.show', $role) }}"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            View
                        </a>
                        @if (!$locked)
                            <a href="{{ route('roles.edit', $role) }}"
                                class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                                Edit
                            </a>
                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete {{ $role->name }}?');"
                                class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No roles found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $roles->links() }}
        </div>
    </div>
</x-app-layout>
