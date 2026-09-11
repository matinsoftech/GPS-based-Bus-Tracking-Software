<?php

namespace App\Http\Controllers\Api\V1\Principal;

use App\Http\Controllers\Controller;
use App\Models\ProblemReport;
use App\Models\School;
use App\Models\SchoolAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrincipalProblemReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request->user());

        if (! $schoolId) {
            return response()->json([
                'message' => 'Principal profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', ProblemReport::statusValues())],
            'category' => ['nullable', 'string', 'in:'.implode(',', ProblemReport::categoryValues())],
            'severity' => ['nullable', 'string', 'in:'.implode(',', ProblemReport::severityValues())],
            'q' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $reports = ProblemReport::query()
            ->with(['school', 'reporter', 'trip', 'bus'])
            ->where('school_id', $schoolId)
            ->when(! empty($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->when(! empty($validated['category']), fn ($q) => $q->where('category', $validated['category']))
            ->when(! empty($validated['severity']), fn ($q) => $q->where('severity', $validated['severity']))
            ->when(! empty($validated['q']), fn ($q) => $q
                ->where('description', 'like', '%'.$validated['q'].'%'))
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'message' => 'Problem reports.',
            'data' => [
                'problem_reports' => $reports->map(fn (ProblemReport $report) => $this->reportPayload($report)),
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'per_page' => $reports->perPage(),
                    'last_page' => $reports->lastPage(),
                    'total' => $reports->total(),
                    'from' => $reports->firstItem(),
                    'to' => $reports->lastItem(),
                ],
            ],
        ]);
    }

    public function show(Request $request, ProblemReport $problemReport): JsonResponse
    {
        if (! $this->authorizeReport($request->user(), $problemReport)) {
            return response()->json([
                'message' => 'You are not authorized to access this problem report.',
            ], 403);
        }

        $problemReport->load(['school', 'reporter', 'trip', 'bus']);

        return response()->json([
            'message' => 'Problem report details.',
            'data' => [
                'problem_report' => $this->reportPayload($problemReport),
            ],
        ]);
    }

    public function resolve(Request $request, ProblemReport $problemReport): JsonResponse
    {
        if (! $this->authorizeReport($request->user(), $problemReport)) {
            return response()->json([
                'message' => 'You are not authorized to access this problem report.',
            ], 403);
        }

        $validated = $request->validate([
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($problemReport->isResolved()) {
            return response()->json([
                'message' => 'Problem report is already resolved.',
            ], 422);
        }

        $problemReport->update([
            'status' => ProblemReport::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $problemReport->load(['school', 'reporter', 'trip', 'bus']);

        return response()->json([
            'message' => 'Problem report resolved.',
            'action' => 'resolved',
            'data' => [
                'problem_report' => $this->reportPayload($problemReport),
            ],
        ]);
    }

    private function authorizeReport($user, ProblemReport $report): bool
    {
        return $report->school_id === $this->resolveSchoolId($user);
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
            'reporter' => $report->reporter ? [
                'id' => $report->reporter->id,
                'name' => $report->reporter->name,
                'role' => $report->reporter->getRoleNames()->first(),
            ] : null,
            'trip' => $report->trip ? [
                'id' => $report->trip->id,
            ] : null,
            'bus' => $report->bus ? [
                'id' => $report->bus->id,
                'bus_number' => $report->bus->bus_number,
            ] : null,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'resolved_at' => $report->resolved_at?->toIso8601String(),
            'created_at' => $report->created_at?->toIso8601String(),
            'updated_at' => $report->updated_at?->toIso8601String(),
        ];
    }

    private function resolveSchoolId($user): ?int
    {
        $schoolId = $user->school_id;

        if (! $schoolId) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        if (! $schoolId) {
            $schoolId = School::where('principal_name', $user->name)
                ->orWhere('email', $user->email)
                ->value('id');
        }

        return $schoolId;
    }
}
