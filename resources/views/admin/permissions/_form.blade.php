@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Permission name</label>
        <input id="name" name="name" value="{{ old('name', $permission->name ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Permission key</label>
        <input id="key" name="key" value="{{ old('key', $permission->key ?? '') }}" required placeholder="module.action" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('key') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="group" class="block text-sm font-medium text-slate-700">Group</label>
        <input id="group" name="group" value="{{ old('group', $permission->group ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('group') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">{{ old('description', $permission->description ?? '') }}</textarea>
        @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
</div>
<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="min-h-11 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800">{{ $button ?? 'Save permission' }}</button>
    <a href="{{ route('admin.permissions.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
</div>
