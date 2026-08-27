<x-app-layout page="attendance">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance</h1>
        </div>

        <form action="{{ route('attendance.index') }}" method="GET" class="mb-6">
            <div class="flex items-end gap-3">
                <div>
                    <label for="date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ $today }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                </div>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    View
                </button>
            </div>
        </form>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($routes->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                No active routes available.
            </div>
        @elseif ($groupedBySchool)
            @foreach ($routes->groupBy('school_id') as $schoolId => $group)
                <div class="mb-8">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $group->first()->school->name ?? 'School' }}
                    </h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group as $route)
                            @include('attendance.partials.route-card')
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($routes as $route)
                    @include('attendance.partials.route-card')
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
