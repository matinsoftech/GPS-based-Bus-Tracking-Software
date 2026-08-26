<x-app-layout page="attendance">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $bus->bus_number }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $bus->school->name ?? '—' }} ·
                    Route: {{ $bus->routes->pluck('name')->join(', ') ?: '—' }} ·
                    Driver: {{ $bus->drivers->first()?->full_name ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('attendance.buses.show', $bus) }}" method="GET">
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            id="date"
                            name="date"
                            value="{{ $date }}"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        <button
                            type="submit"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            View
                        </button>
                    </div>
                </form>
                <a
                    href="{{ route('attendance.buses.history', $bus) }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    History
                </a>
                <a
                    href="{{ route('attendance.index', ['date' => $date]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Back to Buses
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
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

        @if ($isToday && $allCompleted)
            <div class="mb-6 flex items-center gap-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                Today's attendance has already been taken. Please try again tomorrow.
            </div>
        @elseif (! $isToday)
            <div class="mb-6 flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                You can only mark attendance for today. This is a read-only view of
                <span class="font-medium">{{ \Illuminate\Support\Carbon::parse($date)->format('D, M d, Y') }}</span>.
            </div>
        @endif

        @php
            $totals = [
                'Picked Up from Home' => $studentStages->filter(fn ($entry) => $entry['stages'][0]['done'])->count(),
                'Dropped at School' => $studentStages->filter(fn ($entry) => $entry['stages'][1]['done'])->count(),
                'Picked Up from School' => $studentStages->filter(fn ($entry) => $entry['stages'][2]['done'])->count(),
                'Dropped at Home' => $studentStages->filter(fn ($entry) => $entry['stages'][3]['done'])->count(),
            ];
            $completedCount = $studentStages->filter(fn ($entry) => $entry['completed'])->count();
            $totalStudents = $studentStages->count();
            $hasStages = $totalStudents > 0;
            $canTakeAttendance = $isToday && ! $allCompleted;
        @endphp

        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-5">
            @foreach ($totals as $label => $count)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $count }} <span class="text-sm font-normal text-gray-400">/ {{ $totalStudents }}</span>
                    </p>
                </div>
            @endforeach
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500 dark:text-gray-400">Students Completed</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $completedCount }} <span class="text-sm font-normal text-gray-400">/ {{ $totalStudents }}</span>
                </p>
            </div>
        </div>

        @if ($hasStages)
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Attendance Sheet</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if ($isToday)
                        Today's attendance
                    @else
                        Attendance for <span class="font-medium text-gray-900 dark:text-white">{{ \Illuminate\Support\Carbon::parse($date)->format('D, M d, Y') }}</span>
                    @endif
                </p>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3 font-medium md:px-5">Student</th>
                            <th class="px-4 py-3 text-center font-medium md:px-5">Pick Up from Home</th>
                            <th class="px-4 py-3 text-center font-medium md:px-5">Drop at School</th>
                            <th class="px-4 py-3 text-center font-medium md:px-5">Pick Up from School</th>
                            <th class="px-4 py-3 text-center font-medium md:px-5">Drop at Home</th>
                            <th class="px-4 py-3 font-medium md:px-5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($studentStages as $entry)
                            @php
                                $student = $entry['student'];
                                $started = collect($entry['stages'])->contains(fn ($stage) => $stage['done']);
                            @endphp
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-3 md:px-5">
                                    <div class="flex items-center gap-3">
                                        @if ($student->photo)
                                            <img
                                                src="{{ asset('storage/' . $student->photo) }}"
                                                alt="{{ $student->full_name }}"
                                                class="h-9 w-9 rounded-full object-cover"
                                            >
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                                {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-gray-900 dark:text-white">{{ $student->full_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $student->grade }}{{ $student->section ? ' - ' . $student->section : '' }} · {{ $student->admission_no }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                @foreach ($entry['buttons'] as $button)
                                    <td class="px-4 py-3 text-center md:px-5">
                                        @if ($button['done'])
                                            <div class="flex flex-col items-center gap-0.5">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-xs text-green-600 dark:bg-green-900/20 dark:text-green-400">✓</span>
                                                @if ($button['at'])
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $button['at']->format('h:i A') }}</span>
                                                @endif
                                            </div>
                                        @elseif ($button['enabled'] && $canTakeAttendance)
                                            <form action="{{ route('attendance.mark', ['bus' => $bus, 'student' => $student]) }}" method="POST" class="attendance-mark-form">
                                                @csrf
                                                <input type="hidden" name="action" value="{{ $button['action'] }}">
                                                <input type="hidden" name="trip" value="{{ $button['trip'] }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <button
                                                    type="submit"
                                                    class="w-full whitespace-nowrap rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    {{ $button['button_label'] }}
                                                </button>
                                            </form>
                                        @else
                                            <button
                                                type="button"
                                                disabled
                                                title="{{ $button['label'] }} — complete the previous step first"
                                                class="w-full cursor-not-allowed whitespace-nowrap rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-400 dark:bg-gray-800 dark:text-gray-500"
                                            >
                                                {{ $button['button_label'] }}
                                            </button>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-4 py-3 md:px-5">
                                    @if ($entry['completed'])
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Completed</span>
                                    @elseif ($started)
                                        <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">In Progress</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Not Started</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No students assigned to this bus yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var forms = document.querySelectorAll('.attendance-mark-form');
                forms.forEach(function (form) {
                    form.addEventListener('submit', function () {
                        var button = form.querySelector('button[type="submit"]');
                        if (button) {
                            button.disabled = true;
                            button.textContent = 'Saving...';
                        }
                    });
                });
            })();
        </script>
    @endpush
</x-app-layout>
