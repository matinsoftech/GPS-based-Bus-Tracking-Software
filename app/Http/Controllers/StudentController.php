<?php

namespace App\Http\Controllers;

use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Student::with(['school', 'parent.user', 'routes']);

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('admission_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('grade', 'like', "%{$search}%")
                    ->orWhereHas('parent.user', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();
        $parentsSchoolId = null;

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
                $parentsSchoolId = $schoolId;
            }
        }

        $parents = $this->availableParents($parentsSchoolId);

        $routes = Route::with('stops')
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->orderBy('name')
            ->get();

        return view('students.create', compact('school', 'schools', 'parents', 'routes'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'admission_no' => 'required|unique:students,admission_no',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => [
                'required',
                Rule::in(['Male', 'Female', 'Other']),
            ],
            'grade' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'roll_no' => 'nullable|string|max:20',
            'stops' => ['nullable', 'array'],
            'stops.*' => ['integer', 'exists:route_stops,id'],
            'route_ids' => ['nullable', 'array'],
            'route_ids.*' => ['integer', 'exists:routes,id'],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            $rules['parent_id'] = [
                'required',
                Rule::exists('parent_profiles', 'id')->where('school_id', $schoolId),
            ];
        } else {
            $rules['school_id'] = 'required|exists:schools,id';
            $rules['parent_id'] = [
                'required',
                Rule::exists('parent_profiles', 'id')->where('school_id', $request->input('school_id')),
            ];
        }

        $validated = $request->validate($rules, [
            'parent_id.exists' => 'The selected parent does not belong to the selected school.',
        ]);

        if ($this->isSchoolLevelAdmin($user)) {
            $validated['school_id'] = $schoolId;
        }

        $parent = ParentProfile::find($validated['parent_id']);

        if (! $parent || (int) $parent->school_id !== (int) $validated['school_id']) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'The selected parent does not belong to the selected school.']);
        }

        $routeIds = $validated['route_ids'] ?? [];

        if (! empty($routeIds)) {
            $invalidRoutes = Route::whereIn('id', $routeIds)
                ->where('school_id', '!=', $validated['school_id'])
                ->pluck('id');

            if ($invalidRoutes->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors(['route_ids' => 'One or more selected routes do not belong to the selected school.']);
            }
        }

        if (! $this->stopsBelongToRoutes($validated['stops'] ?? [], $routeIds, (int) $validated['school_id'])) {
            return back()
                ->withInput()
                ->withErrors(['stops' => 'One or more selected stops do not belong to the selected routes.']);
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request
                ->file('photo')
                ->store('students', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $stops = $validated['stops'] ?? [];
        unset($validated['stops'], $validated['route_ids']);

        $student = Student::create($validated);
        $student->routes()->sync($routeIds);
        $student->stops()->sync($stops);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $student->load(['school', 'parent.user', 'routes', 'stops']);

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $this->authorizeStudent($student);

        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();
        $parentsSchoolId = null;

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
                $parentsSchoolId = $schoolId;
            }
        }

        $parents = $this->availableParents($parentsSchoolId);

        $routes = Route::with('stops')
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->orderBy('name')
            ->get();

        $student->load(['school', 'parent.user', 'routes', 'stops']);

        return view('students.edit', compact('student', 'school', 'schools', 'parents', 'routes'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student)
    {
        $this->authorizeStudent($student);

        $user = Auth::user();

        $rules = [
            'admission_no' => 'required|unique:students,admission_no,'.$student->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => [
                'required',
                Rule::in(['Male', 'Female', 'Other']),
            ],
            'grade' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'roll_no' => 'nullable|string|max:20',
            'stops' => ['nullable', 'array'],
            'stops.*' => ['integer', 'exists:route_stops,id'],
            'route_ids' => ['nullable', 'array'],
            'route_ids.*' => ['integer', 'exists:routes,id'],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            $rules['parent_id'] = [
                'required',
                Rule::exists('parent_profiles', 'id')->where('school_id', $schoolId),
            ];
        } else {
            $rules['school_id'] = 'required|exists:schools,id';
            $rules['parent_id'] = [
                'required',
                Rule::exists('parent_profiles', 'id')->where('school_id', $request->input('school_id')),
            ];
        }

        $validated = $request->validate($rules, [
            'parent_id.exists' => 'The selected parent does not belong to the selected school.',
        ]);

        if ($this->isSchoolLevelAdmin($user)) {
            $validated['school_id'] = $schoolId;
        }

        $parent = ParentProfile::find($validated['parent_id']);

        if (! $parent || (int) $parent->school_id !== (int) $validated['school_id']) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'The selected parent does not belong to the selected school.']);
        }

        $routeIds = $validated['route_ids'] ?? [];

        if (! empty($routeIds)) {
            $invalidRoutes = Route::whereIn('id', $routeIds)
                ->where('school_id', '!=', $validated['school_id'])
                ->pluck('id');

            if ($invalidRoutes->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors(['route_ids' => 'One or more selected routes do not belong to the selected school.']);
            }
        }

        if (! $this->stopsBelongToRoutes($validated['stops'] ?? [], $routeIds, (int) $validated['school_id'])) {
            return back()
                ->withInput()
                ->withErrors(['stops' => 'One or more selected stops do not belong to the selected routes.']);
        }

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }

            $validated['photo'] = $request
                ->file('photo')
                ->store('students', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $stops = $validated['stops'] ?? [];
        unset($validated['stops'], $validated['route_ids']);

        $student->update($validated);
        $student->routes()->sync($routeIds);
        $student->stops()->sync($stops);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student)
    {
        $this->authorizeStudent($student);

        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Make sure school-level admins can only access their own school's students.
     */
    private function authorizeStudent(Student $student): void
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $student->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this student.');
            }
        }
    }

    /**
     * Verify every selected stop belongs to one of the given routes.
     */
    private function stopsBelongToRoutes(array $stopIds, array $routeIds, int $schoolId): bool
    {
        if (empty($stopIds)) {
            return true;
        }

        if (empty($routeIds)) {
            return false;
        }

        $matching = RouteStop::whereIn('id', $stopIds)
            ->whereHas('route', fn ($query) => $query->whereIn('id', $routeIds)->where('school_id', $schoolId))
            ->count();

        return $matching === count($stopIds);
    }

    private function isSchoolLevelAdmin(?User $user): bool
    {
        return $user && $user->hasAnyRole(['School Admin', 'Principal']);
    }

    /**
     * Parents for the student form dropdown.
     * School-level admins only see their own school's parents; the filtering happens in the query.
     */
    private function availableParents(?int $schoolId): Collection
    {
        return ParentProfile::with(['user', 'school'])
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->orderBy('id')
            ->get();
    }

    private function getUserSchoolId(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        if (! empty($user->school_id)) {
            return (int) $user->school_id;
        }

        $schoolAdmin = SchoolAdmin::where('user_id', $user->id)->first();

        if ($schoolAdmin && ! empty($schoolAdmin->school_id)) {
            return (int) $schoolAdmin->school_id;
        }

        $school = School::where('principal_name', $user->name)
            ->orWhere('email', $user->email)
            ->first();

        return $school?->id;
    }
}
