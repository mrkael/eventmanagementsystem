@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Department name</label>
        <input id="name" name="name" value="{{ old('name', $department->name ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
        <input id="code" name="code" value="{{ old('code', $department->code ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm uppercase focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
        @error('code') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">{{ old('description', $department->description ?? '') }}</textarea>
        @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $department->is_active ?? true)) class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            Active department
        </label>
    </div>
</div>
<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primary btn-md">{{ $button ?? 'Save department' }}</button>
    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary btn-md">Cancel</a>
</div>
