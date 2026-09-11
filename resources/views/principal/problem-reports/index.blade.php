<x-app-layout page="problem-reports">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Problem Reports</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and resolve problem reports for your school</p>
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div
                class="mb-4 rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                {{ session('warning') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('principal.problem-reports.index') }}" class="mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="status" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                    <select id="status" name="status"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All statuses</option>
                        <option value="open" @selected(request('status') === 'open')>Open</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    </select>
                </div>
                <div>
                    <label for="category" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                    <select id="category" name="category"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All categories</option>
                        @foreach (\App\Models\ProblemReport::categories() as $value => $label)
                            <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="severity" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Severity</label>
                    <select id="severity" name="severity"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All severities</option>
                        @foreach (\App\Models\ProblemReport::severities() as $value => $label)
                            <option value="{{ $value }}" @selected(request('severity') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="q" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Search description or reporter..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Filter
                </button>
                @if (request()->hasAny(['status', 'category', 'severity', 'q']))
                    <a href="{{ route('principal.problem-reports.index') }}"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Desktop / tablet: table view --}}
        <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 text-left font-medium">Severity</th>
                            <th class="px-5 py-3 text-left font-medium">Category</th>
                            <th class="px-5 py-3 text-left font-medium">Reporter</th>
                            <th class="px-5 py-3 text-left font-medium">Bus</th>
                            <th class="px-5 py-3 text-left font-medium">Description</th>
                            <th class="px-5 py-3 text-left font-medium">Submitted</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($problemReports as $report)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        @if ($report->severity === 'high') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400
                                        @elseif ($report->severity === 'medium') bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400
                                        @else bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 @endif
                                    ">
                                        {{ $report->severity_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm">{{ $report->category_label }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm">{{ $report->reporter?->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $report->reporter?->getRoleNames()->first() ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm">{{ $report->bus?->bus_number ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="block max-w-xs truncate text-sm" title="{{ $report->description }}">{{ $report->description }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $report->created_at->format('M d, Y H:i') }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        @if ($report->status === 'open') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400
                                        @elseif ($report->status === 'in_progress') bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400
                                        @else bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 @endif
                                    ">
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if (! $report->isResolved())
                                        <form action="{{ route('principal.problem-reports.resolve', $report) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-600"
                                                onclick="return confirm('Mark this problem report as resolved?')">
                                                Resolve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $report->resolved_at?->format('M d, Y H:i') ?? '—' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No problem reports found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($problemReports->hasPages())
                    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                        {{ $problemReports->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($problemReports as $report)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @if ($report->severity === 'high') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400
                                    @elseif ($report->severity === 'medium') bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400
                                    @else bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 @endif
                                ">
                                    {{ $report->severity_label }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $report->category_label }}</span>
                            </div>
                        </div>
                        @if ($report->status === 'open')
                            <span class="inline-flex shrink-0 items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                Open
                            </span>
                        @elseif ($report->status === 'in_progress')
                            <span class="inline-flex shrink-0 items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">
                                In Progress
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                Resolved
                            </span>
                        @endif
                    </div>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ Str::limit($report->description, 120) }}</p>

                    <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Reporter</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $report->reporter?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Bus</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $report->bus?->bus_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Submitted</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $report->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Resolved</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $report->resolved_at ? $report->resolved_at->format('M d, Y') : '—' }}</dd>
                        </div>
                    </dl>

                    @if (! $report->isResolved())
                        <form action="{{ route('principal.problem-reports.resolve', $report) }}" method="POST"
                            class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-lg bg-green-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-600"
                                onclick="return confirm('Mark this problem report as resolved?')">
                                Resolve
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No problem reports found.
                </div>
            @endforelse

            @if ($problemReports->hasPages())
                <div class="pt-2">
                    {{ $problemReports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>