<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\ParentProfile;
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

        $query = Student::with(['school', 'parent.user', 'bus']);

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

        $buses = Bus::query()
            ->with('school')
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->orderBy('bus_number')
            ->get();

        return view('students.create', compact('school', 'schools', 'parents', 'buses'));
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
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'pickup_latitude' => 'nullable|numeric|between:-90,90',
            'pickup_longitude' => 'nullable|numeric|between:-180,180',
            'drop_latitude' => 'nullable|numeric|between:-90,90',
            'drop_longitude' => 'nullable|numeric|between:-180,180',
            'bus_id' => 'nullable|exists:buses,id',
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

        if (! empty($validated['bus_id'])) {
            $bus = Bus::find($validated['bus_id']);

            if (! $bus || (int) $bus->school_id !== (int) $validated['school_id']) {
                return back()
                    ->withInput()
                    ->withErrors(['bus_id' => 'The selected bus does not belong to the selected school.']);
            }
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request
                ->file('photo')
                ->store('students', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        Student::create($validated);

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

        $student->load(['school', 'parent.user', 'bus.route']);

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

        $buses = Bus::query()
            ->with('school')
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->orderBy('bus_number')
            ->get();

        $student->load(['school', 'parent.user', 'bus']);

        return view('students.edit', compact('student', 'school', 'schools', 'parents', 'buses'));
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
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'pickup_latitude' => 'nullable|numeric|between:-90,90',
            'pickup_longitude' => 'nullable|numeric|between:-180,180',
            'drop_latitude' => 'nullable|numeric|between:-90,90',
            'drop_longitude' => 'nullable|numeric|between:-180,180',
            'bus_id' => 'nullable|exists:buses,id',
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

        if (! empty($validated['bus_id'])) {
            $bus = Bus::find($validated['bus_id']);

            if (! $bus || (int) $bus->school_id !== (int) $validated['school_id']) {
                return back()
                    ->withInput()
                    ->withErrors(['bus_id' => 'The selected bus does not belong to the selected school.']);
            }
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

        $student->update($validated);

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
