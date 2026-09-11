<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProblemReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $schoolId = $this->resolveSchoolId($user);

        if (! $schoolId) {
            return response()->json([
                'message' => 'Unable to determine your school. No school profile found.',
            ], 404);
        }

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'in:'.implode(',', ProblemReport::categoryValues())],
            'severity' => ['nullable', 'string', 'in:'.implode(',', ProblemReport::severityValues())],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'bus_id' => ['nullable', 'integer', 'exists:buses,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $validated['school_id'] = $schoolId;
        $validated['reporter_user_id'] = $user->id;
        $validated['category'] = $validated['category'] ?? ProblemReport::CATEGORY_OTHER;
        $validated['severity'] = $validated['severity'] ?? ProblemReport::SEVERITY_MEDIUM;
        $validated['status'] = ProblemReport::STATUS_OPEN;

        $report = $this->createReport($validated, $user);

        return response()->json([
            'message' => 'Problem report submitted.',
            'data' => [
                'problem_report' => $this->reportPayload($report),
            ],
        ], 201);
    }

    private function createReport(array $data, $user): ProblemReport
    {
        $report = ProblemReport::create($data);

        $report->load(['school', 'reporter', 'trip', 'bus']);

        $this->notifySchoolAdmins($report);

        return $report;
    }

    private function notifySchoolAdmins(ProblemReport $report): void
    {
        $notification = new \App\Notifications\ProblemReportedNotification($report);

        $schoolAdmins = \App\Models\SchoolAdmin::where('school_id', $report->school_id)
            ->with('user')
            ->get();

        foreach ($schoolAdmins as $admin) {
            if ($admin->user && $admin->user->id !== $report->reporter_user_id) {
                $admin->user->notify($notification);
            }
        }
    }

    private function reportPayload(ProblemReport $report): array
    {
        return [
            'id' => $report->id,
            'category' => $report->category,
            'category_label' => $report->category_label,
            'severity' => $report->severity,
            'severity_label' => $report->severity_label,
            'status' => $report->status,
            'status_label' => $report->status_label,
            'description' => $report->description,
            'school' => $report->school ? [
                'id' => $report->school->id,
                'name' => $report->school->name,
            ] : null,
            'reporter' => $this->reporterPayload($report),
            'trip' => $report->trip ? [
                'id' => $report->trip->id,
            ] : null,
            'bus' => $report->bus ? [
                'id' => $report->bus->id,
                'bus_number' => $report->bus->bus_number,
            ] : null,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'created_at' => $report->created_at?->toIso8601String(),
            'updated_at' => $report->updated_at?->toIso8601String(),
        ];
    }

    private function reporterPayload(ProblemReport $report): ?array
    {
        $reporter = $report->reporter;

        if (! $reporter) {
            return null;
        }

        $role = $reporter->getRoleNames()->first();

        return [
            'id' => $reporter->id,
            'name' => $reporter->name,
            'role' => $role,
        ];
    }

    private function resolveSchoolId($user): ?int
    {
        $schoolId = $user->school_id;

        if (! $schoolId && $user->driver) {
            $schoolId = $user->driver->school_id;
        }

        if (! $schoolId && $user->parent) {
            $schoolId = $user->parent->school_id;
        }

        if (! $schoolId) {
            $schoolId = \App\Models\SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        if (! $schoolId) {
            $schoolId = \App\Models\School::where('principal_name', $user->name)
                ->orWhere('email', $user->email)
                ->value('id');
        }

        return $schoolId;
    }
}
