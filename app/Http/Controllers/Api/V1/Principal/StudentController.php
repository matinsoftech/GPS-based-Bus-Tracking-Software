<?php

namespace App\Http\Controllers\Api\V1\Principal;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request->user());

        if (! $schoolId) {
            return response()->json([
                'message' => 'Principal profile not found.',
            ], 404);
        }

        $students = Student::query()
            ->with(['school', 'parent.user', 'route'])
            ->where('school_id', $schoolId)
            ->when($request->filled('q'), fn ($q) => $q
                ->where(fn ($query) => $query
                    ->where('admission_no', 'like', '%'.$request->string('q').'%')
                    ->orWhere('first_name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('last_name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('grade', 'like', '%'.$request->string('q').'%')
                    ->orWhereHas('parent.user', fn ($query) => $query
                        ->where('name', 'like', '%'.$request->string('q').'%'))))
            ->when($request->filled('grade'), fn ($q) => $q->where('grade', $request->string('grade')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json([
            'message' => 'Students list.',
            'data' => [
                'students' => $students->map(fn (Student $student) => $this->studentPayload($student)),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'last_page' => $students->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request->user());

        if (! $schoolId) {
            return response()->json([
                'message' => 'Principal profile not found.',
            ], 404);
        }

        $validated = $this->validateStudent($request, $schoolId, null);

        $validated['school_id'] = $schoolId;
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($validated);

        $student->load(['school', 'parent.user', 'route']);

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => [
                'student' => $this->studentPayload($student),
            ],
        ], 201);
    }

    public function show(Request $request, Student $student)
    {
        if (! $this->authorizeStudent($request->user(), $student)) {
            return response()->json([
                'message' => 'You are not authorized to access this student.',
            ], 403);
        }

        $student->load(['school', 'parent.user', 'route']);

        return response()->json([
            'message' => 'Student details.',
            'data' => [
                'student' => $this->studentPayload($student),
            ],
        ]);
    }

    public function update(Request $request, Student $student)
    {
        if (! $this->authorizeStudent($request->user(), $student)) {
            return response()->json([
                'message' => 'You are not authorized to access this student.',
            ], 403);
        }

        $schoolId = $this->resolveSchoolId($request->user());

        $validated = $this->validateStudent($request, $schoolId, $student);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }

            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        $student->load(['school', 'parent.user', 'route']);

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => [
                'student' => $this->studentPayload($student),
            ],
        ]);
    }

    public function destroy(Request $request, Student $student)
    {
        if (! $this->authorizeStudent($request->user(), $student)) {
            return response()->json([
                'message' => 'You are not authorized to access this student.',
            ], 403);
        }

        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
            'data' => null,
        ]);
    }

    public function updatePhoto(Request $request, Student $student)
    {
        if (! $this->authorizeStudent($request->user(), $student)) {
            return response()->json([
                'message' => 'You are not authorized to access this student.',
            ], 403);
        }

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->photo = $request
            ->file('profile_photo')
            ->store('students', 'public');
        $student->save();

        $student->load(['school', 'parent.user', 'route']);

        return response()->json([
            'message' => 'Student photo updated.',
            'data' => [
                'student' => $this->studentPayload($student),
            ],
        ]);
    }

    private function validateStudent(Request $request, int $schoolId, ?Student $student): array
    {
        $uniqueAdmissionNo = Rule::unique('students', 'admission_no');

        if ($student) {
            $uniqueAdmissionNo->ignore($student->id);
        }

        return $request->validate([
            'admission_no' => ['required', 'string', 'max:50', $uniqueAdmissionNo],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'grade' => ['required', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:10'],
            'roll_no' => ['nullable', 'string', 'max:20'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'drop_location' => ['required', 'string', 'max:255'],
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'drop_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'drop_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'parent_id' => ['required', Rule::exists('parent_profiles', 'id')->where('school_id', $schoolId)],
            'route_id' => ['nullable', Rule::exists('routes', 'id')->where('school_id', $schoolId)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'parent_id.exists' => 'The selected parent does not belong to your school.',
            'route_id.exists' => 'The selected route does not belong to your school.',
        ]);
    }

    private function authorizeStudent($user, Student $student): bool
    {
        return $student->school_id === $this->resolveSchoolId($user);
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'admission_no' => $student->admission_no,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'gender' => $student->gender,
            'grade' => $student->grade,
            'section' => $student->section,
            'roll_no' => $student->roll_no,
            'date_of_birth' => $student->date_of_birth?->toDateString(),
            'pickup_location' => $student->pickup_location,
            'drop_location' => $student->drop_location,
            'pickup_latitude' => $student->pickup_latitude,
            'pickup_longitude' => $student->pickup_longitude,
            'drop_latitude' => $student->drop_latitude,
            'drop_longitude' => $student->drop_longitude,
            'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
            'is_active' => $student->is_active,
            'parent' => $student->parent ? [
                'id' => $student->parent->id,
                'name' => $student->parent->user?->name,
                'phone' => $student->parent->phone,
            ] : null,
            'route' => $student->route ? [
                'id' => $student->route->id,
                'name' => $student->route->name,
                'route_code' => $student->route->route_code,
                'is_active' => $student->route->is_active,
            ] : null,
            'school' => $student->school ? [
                'id' => $student->school->id,
                'name' => $student->school->name,
            ] : null,
        ];
    }

    private function perPage(Request $request): int
    {
        return max(1, min(50, $request->integer('per_page', 15)));
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
