<x-app-layout page="drivers">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Driver</h1>
            <a
                href="{{ route('drivers.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back to Drivers
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <form
            action="{{ route('drivers.update', $driver) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            @csrf
            @method('PUT')

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Account Details
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $driver->email) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="Leave blank to keep current password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Leave blank to keep the current password.
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Personal Information
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="profile_photo" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            @if($driver->profile_photo)
                                <img
                                    src="{{ asset('storage/' . $driver->profile_photo) }}"
                                    alt="{{ $driver->full_name }}"
                                    class="h-20 w-20 rounded-full object-cover"
                                >
                            @else
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-2xl font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                    {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <input
                                    type="file"
                                    id="profile_photo"
                                    name="profile_photo"
                                    accept="image/*"
                                    class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-900 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:file:bg-gray-700 dark:file:text-gray-200"
                                >
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Leave empty to keep the current photo.
                                </p>
                            </div>
                        </div>
                        @error('profile_photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="employee_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Employee ID</label>
                        <input
                            type="text"
                            id="employee_id"
                            name="employee_id"
                            value="{{ old('employee_id', $driver->employee_id) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="first_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name', $driver->first_name) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name', $driver->last_name) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gender" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                        <select
                            id="gender"
                            name="gender"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select Gender</option>
                            <option value="Male" @selected(old('gender', $driver->gender) === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', $driver->gender) === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender', $driver->gender) === 'Other')>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="{{ old('date_of_birth', $driver->date_of_birth?->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $driver->phone) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >{{ old('address', $driver->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $driver->city) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="{{ old('state', $driver->state) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                        <input
                            type="text"
                            id="country"
                            name="country"
                            value="{{ old('country', $driver->country ?? 'Nepal') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            value="{{ old('postal_code', $driver->postal_code) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    License Information
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="license_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">License Number</label>
                        <input
                            type="text"
                            id="license_number"
                            name="license_number"
                            value="{{ old('license_number', $driver->license_number) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('license_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="license_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">License Type</label>
                        <select
                            id="license_type"
                            name="license_type"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select License Type</option>
                            <option value="Heavy Vehicle" @selected(old('license_type', $driver->license_type) === 'Heavy Vehicle')>Heavy Vehicle</option>
                            <option value="Bus" @selected(old('license_type', $driver->license_type) === 'Bus')>Bus</option>
                            <option value="Other" @selected(old('license_type', $driver->license_type) === 'Other')>Other</option>
                        </select>
                        @error('license_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="experience_years" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Driving Experience (Years)</label>
                        <input
                            type="number"
                            id="experience_years"
                            name="experience_years"
                            value="{{ old('experience_years', $driver->experience_years) }}"
                            min="0"
                            max="80"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('experience_years')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="license_issue_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">License Issue Date</label>
                        <input
                            type="date"
                            id="license_issue_date"
                            name="license_issue_date"
                            value="{{ old('license_issue_date', $driver->license_issue_date?->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('license_issue_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="license_expiry_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">License Expiry Date</label>
                        <input
                            type="date"
                            id="license_expiry_date"
                            name="license_expiry_date"
                            value="{{ old('license_expiry_date', $driver->license_expiry_date?->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('license_expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Employment Information
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @if(auth()->user()->hasAnyRole(['School Admin', 'Principal']))
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <input
                                type="text"
                                value="{{ $driver->school->name ?? 'School not assigned' }}"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                            >
                        </div>
                    @else
                        <div>
                            <label for="school_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <select
                                id="school_id"
                                name="school_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Select School</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('school_id', $driver->school_id) == $school->id)>{{ $school->name }}</option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="joining_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Joining Date</label>
                        <input
                            type="date"
                            id="joining_date"
                            name="joining_date"
                            value="{{ old('joining_date', $driver->joining_date?->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('joining_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="Active" @selected(old('status', $driver->status) === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('status', $driver->status) === 'Inactive')>Inactive</option>
                            <option value="Suspended" @selected(old('status', $driver->status) === 'Suspended')>Suspended</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="remarks" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks</label>
                        <textarea
                            id="remarks"
                            name="remarks"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >{{ old('remarks', $driver->remarks) }}</textarea>
                        @error('remarks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Emergency Contact
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Name</label>
                        <input
                            type="text"
                            id="emergency_contact_name"
                            name="emergency_contact_name"
                            value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('emergency_contact_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="emergency_contact_phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Number</label>
                        <input
                            type="text"
                            id="emergency_contact_phone"
                            name="emergency_contact_phone"
                            value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('emergency_contact_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Assigned Buses
                </h2>

                @if ($buses->isNotEmpty())
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                        @foreach ($buses as $bus)
                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.03] cursor-pointer transition">
                                <input
                                    type="checkbox"
                                    name="bus_ids[]"
                                    value="{{ $bus->id }}"
                                    @checked($driver->buses->contains($bus->id))
                                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                >
                                <div class="min-w-0 flex-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $bus->bus_number }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $bus->registration_number }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No buses available for this school.</p>
                @endif
                @error('bus_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('bus_ids.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Assigned Routes
                </h2>

                @if ($routes->isNotEmpty())
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                        @foreach ($routes as $route)
                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.03] cursor-pointer transition">
                                <input
                                    type="checkbox"
                                    name="route_ids[]"
                                    value="{{ $route->id }}"
                                    @checked($driver->routes->contains($route->id))
                                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                >
                                <div class="min-w-0 flex-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $route->name }}</span>
                                    @if ($route->route_code)
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $route->route_code }}</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No routes available for this school.</p>
                @endif
                @error('route_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('route_ids.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a
                    href="{{ route('drivers.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Update Driver
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
