@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="department_id" class="block text-sm font-medium text-slate-700">Department</label>
        <select id="department_id" name="department_id" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            <option value="">Not assigned</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', isset($user) ? $user->status->value : 'active') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
    </div>
    <div>
        <label for="position" class="block text-sm font-medium text-slate-700">Position</label>
        <input id="position" name="position" value="{{ old('position', $user->position ?? '') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
    </div>
    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">{{ isset($user) ? 'New password' : 'Password' }}</label>
        <input id="password" name="password" type="password" autocomplete="new-password" @required(! isset($user)) class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! isset($user)) class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
    </div>
</div>

<fieldset class="mt-6 rounded-lg border border-slate-200 p-4">
    <legend class="px-1 text-sm font-semibold text-slate-800">Roles</legend>
    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($roles as $role)
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, old('role_ids', isset($user) ? $user->roles->pluck('id')->all() : []))) class="mt-1 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                <span>
                    <span class="font-medium text-slate-800">{{ $role->name }}</span>
                    <span class="block font-mono text-xs text-slate-500">{{ $role->key }}</span>
                </span>
            </label>
        @endforeach
    </div>
</fieldset>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primary btn-md">{{ $button ?? 'Save user' }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-md">Cancel</a>
</div>
