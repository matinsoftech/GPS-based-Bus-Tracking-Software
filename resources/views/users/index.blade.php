<x-app-layout page="user-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        {{-- Page header with the Add User action --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
            <a href="{{ route('users.create') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                Add User
            </a>
        </div>

        {{-- Flash messages after each operation --}}
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

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search and role filter --}}
        <form action="{{ route('users.index') }}" method="GET"
            class="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:w-80">

            <select name="role"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:w-auto">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:flex-none">
                    Filter
                </button>

                <a href="{{ route('users.index') }}"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:flex-none">
                    Reset
                </a>
            </div>
        </form>

        {{-- Desktop / tablet: users table --}}
        <div
            class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">ID</th>
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Created Date</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td class="px-5 py-3 font-medium">
                                    <div class="flex items-center gap-3">
                                        @if ($user->profile_photo)
                                            <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                                alt="{{ $user->name }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <span
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $roleNames = $user->roles->pluck('name');
                                    @endphp
                                    @forelse ($roleNames as $roleName)
                                        <span
                                            class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ $roleName }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endforelse
                                </td>
                                <td class="px-5 py-3">
                                    @if ($user->status === 'active')
                                        <span
                                            class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}"
                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?');">
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
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($users as $user)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start gap-3">
                        @if ($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}"
                                class="h-10 w-10 shrink-0 rounded-full object-cover">
                        @else
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900 dark:text-white">{{ $user->name }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}
                                    </p>
                                </div>
                                @if ($user->status === 'active')
                                    <span
                                        class="inline-flex shrink-0 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @php
                                    $roleNames = $user->roles->pluck('name');
                                @endphp
                                @forelse ($roleNames as $roleName)
                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $roleName }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endforelse
                            </div>

                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                Created {{ $user->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('users.edit', $user) }}"
                            class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                            Edit
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?');"
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
                    No users found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
