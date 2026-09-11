<?php

namespace App\Http\Controllers;

use App\Models\ProblemReport;
use App\Models\School;
use App\Models\SchoolAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrincipalProblemReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

        $query = ProblemReport::with(['school', 'reporter', 'trip', 'bus'])
            ->orderByDesc('created_at');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity'));
        }

        if ($request->filled('q')) {
            $search = $request->string('q');

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            });
        }

        $problemReports = $query->paginate(15)->withQueryString();

        return view('principal.problem-reports.index', compact('problemReports'));
    }

    public function resolve(ProblemReport $problemReport)
    {
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

        if ($schoolId !== null && $problemReport->school_id !== $schoolId) {
            abort(403, 'You are not authorized to resolve this problem report.');
        }

        if ($problemReport->isResolved()) {
            return redirect()->route('principal.problem-reports.index')
                ->with('warning', 'That problem report is already resolved.');
        }

        $problemReport->update([
            'status' => ProblemReport::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        return redirect()->route('principal.problem-reports.index')
            ->with('success', 'Problem report marked as resolved.');
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