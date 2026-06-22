@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Role name</label>
        <input id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Role key</label>
        <input id="key" name="key" value="{{ old('key', $role->key ?? '') }}" required placeholder="event-admin" @disabled(($role->is_system ?? false)) class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:bg-slate-100 disabled:text-slate-500">
        @if (($role->is_system ?? false))
            <input type="hidden" name="key" value="{{ $role->key }}">
            <p class="mt-2 text-xs text-slate-500">System role keys are locked because permissions and future workflow checks depend on them.</p>
        @endif
        @error('key') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">{{ old('description', $role->description ?? '') }}</textarea>
        @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6">
    <h2 class="text-sm font-semibold text-slate-900">Permissions</h2>
    <div class="mt-3 grid gap-4 lg:grid-cols-2">
        @foreach ($permissionsByGroup as $group => $permissions)
            <fieldset class="rounded-lg border border-slate-200 p-4">
                <legend class="px-1 text-sm font-semibold text-slate-700">{{ $group }}</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 text-sm">
                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', isset($role) ? $role->permissions->pluck('id')->all() : []))) class="mt-1 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                            <span><span class="font-medium text-slate-800">{{ $permission->name }}</span><span class="block font-mono text-xs text-slate-500">{{ $permission->key }}</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">{{ $button ?? 'Save role' }}</button>
    <a href="{{ route('admin.roles.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
</div>
