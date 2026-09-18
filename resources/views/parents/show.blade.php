<x-app-layout page="parent-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $parentProfile->user->name }}</h1>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('parents.edit', $parentProfile) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Edit
                </a>
                <a
                    href="{{ route('parents.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Back to Parents
                </a>
            </div>
        </div>

        @php
            $parentTabs = [
                ['key' => 'overview', 'label' => 'Overview', 'url' => route('parents.show', $parentProfile), 'icon' => 'user-circle'],
                ['key' => 'children', 'label' => 'Children', 'url' => route('parents.show', [$parentProfile, 'tab' => 'children']), 'icon' => 'academic-cap', 'count' => $childrenCount],
            ];
        @endphp

        <x-pill-tabs :tabs="$parentTabs" :active="$tab" />

        @if ($tab === 'overview')
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                Account Details
            </h2>

            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $parentProfile->user->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->user->email }}</dd>
                </div>
            </dl>

            <h2 class="mb-4 mt-6 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                Parent Details
            </h2>

            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->school->name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->phone }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alternate Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->alternate_phone ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupation</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->occupation ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->address }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $parentProfile->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
        @elseif ($tab === 'children')
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Children</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $childrenCount }} child{{ $childrenCount === 1 ? '' : 'ren' }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Student</th>
                            <th class="px-5 py-3 font-medium">Admission No</th>
                            <th class="px-5 py-3 font-medium">Grade / Section</th>
                            <th class="px-5 py-3 font-medium">School</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($parentProfile->children as $child)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            {{ strtoupper(substr($child->first_name ?? '', 0, 1)) }}
                                        </div>
                                        <a href="{{ route('students.show', $child) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $child->full_name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $child->admission_no ?? '—' }}</td>
                                <td class="px-5 py-3">{{ trim($child->grade.' '.$child->section) }}</td>
                                <td class="px-5 py-3">{{ $child->school?->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if ($child->is_active)
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end">
                                        <a href="{{ route('students.show', $child) }}"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No children linked to this parent yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
