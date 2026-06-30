@csrf
@php
    $eventSessions = old('sessions', isset($event) ? $event->sessions->map(fn($s) => ['title'=>$s->title,'description'=>$s->description,'starts_at'=>$s->starts_at?->format('Y-m-d\TH:i'),'ends_at'=>$s->ends_at?->format('Y-m-d\TH:i'),'venue_id'=>$s->venue_id,'capacity'=>$s->capacity])->all() : [['title'=>'','description'=>'','starts_at'=>'','ends_at'=>'','venue_id'=>'','capacity'=>'']]);
    $latestPage = isset($event) ? $event->pageVersions()->latest('version')->first() : null;
    $defaultSections = [
        ['id'=>(string) str()->uuid(),'type'=>'hero','title'=>'Hero Banner','content'=>'','sort_order'=>0,'settings'=>[]],
        ['id'=>(string) str()->uuid(),'type'=>'about','title'=>'About Event','content'=>'','sort_order'=>1,'settings'=>[]],
        ['id'=>(string) str()->uuid(),'type'=>'agenda','title'=>'Agenda','content'=>'','sort_order'=>2,'settings'=>[]],
    ];
    $sections = old('page_sections', json_encode($latestPage?->sections ?? $defaultSections));
    $sections = is_string($sections) ? $sections : json_encode($sections);
    $sections = json_validate($sections) ? $sections : json_encode($defaultSections);
@endphp
<div class="space-y-6">
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold">Event Details</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div><label class="block text-sm font-medium text-slate-700" for="title">Title</label><input id="title" name="title" value="{{ old('title', $event->title ?? '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20"></div>
            <div><label class="block text-sm font-medium text-slate-700" for="slug">Slug</label><input id="slug" name="slug" value="{{ old('slug', $event->slug ?? '') }}" placeholder="auto-generated when empty" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20"></div>
            <div><label class="block text-sm font-medium text-slate-700" for="event_category_id">Category</label><select id="event_category_id" name="event_category_id" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('event_category_id', $event->event_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700" for="event_type_id">Type</label><select id="event_type_id" name="event_type_id" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">@foreach($types as $type)<option value="{{ $type->id }}" @selected((string) old('event_type_id', $event->event_type_id ?? '') === (string) $type->id)>{{ $type->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700" for="venue_id">Venue</label><select id="venue_id" name="venue_id" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"><option value="">No main venue</option>@foreach($venues as $venue)<option value="{{ $venue->id }}" @selected((string) old('venue_id', $event->venue_id ?? '') === (string) $venue->id)>{{ $venue->name }} ({{ $venue->capacity }})</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700" for="event_status_id">Event status</label><select id="event_status_id" name="event_status_id" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">@foreach($statuses as $status)<option value="{{ $status->id }}" @selected((string) old('event_status_id', $event->event_status_id ?? '') === (string) $status->id)>{{ $status->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700" for="event_configuration_id">Configuration</label><select id="event_configuration_id" name="event_configuration_id" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"><option value="">Use system defaults</option>@foreach($configurations as $configuration)<option value="{{ $configuration->id }}" @selected((string) old('event_configuration_id', $event->event_configuration_id ?? '') === (string) $configuration->id)>{{ $configuration->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700" for="capacity">Capacity</label><input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', $event->capacity ?? 0) }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700" for="starts_at">Starts at</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', isset($event) ? $event->starts_at?->format('Y-m-d\TH:i') : '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700" for="ends_at">Ends at</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', isset($event) ? $event->ends_at?->format('Y-m-d\TH:i') : '') }}" required class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium text-slate-700" for="summary">Summary</label><input id="summary" name="summary" value="{{ old('summary', $event->summary ?? '') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium text-slate-700" for="description">Description</label><textarea id="description" name="description" rows="5" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $event->description ?? '') }}</textarea></div>
            <div class="flex flex-wrap gap-5 md:col-span-2"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_registration_enabled" value="1" @checked(old('is_registration_enabled', $event->is_registration_enabled ?? false)) class="rounded border-slate-300 text-emerald-700">Registration enabled</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', $event->is_public ?? false)) class="rounded border-slate-300 text-emerald-700">Public listing</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="publish_now" value="1" @checked(old('publish_now', false)) class="rounded border-slate-300 text-emerald-700">Publish immediately</label></div>
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold">Uploads</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2"><div><label class="block text-sm font-medium text-slate-700" for="banner">Banner image</label><input id="banner" name="banner" type="file" accept="image/*" class="mt-2 block w-full text-sm"></div><div><label class="block text-sm font-medium text-slate-700" for="documents">Documents</label><input id="documents" name="documents[]" type="file" multiple class="mt-2 block w-full text-sm"></div></div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm" id="sessions-section">
        <div class="flex items-center justify-between"><h2 class="text-base font-semibold">Sessions</h2><button type="button" data-add-session class="btn btn-outline-primary btn-sm">Add session</button></div>
        <div data-session-list class="mt-4 space-y-4">
            @foreach($eventSessions as $index => $session)
                <div class="rounded-lg border border-slate-200 p-4" data-session-row><div class="grid gap-4 md:grid-cols-3"><input name="sessions[{{ $index }}][title]" value="{{ $session['title'] ?? '' }}" placeholder="Session title" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><input name="sessions[{{ $index }}][starts_at]" type="datetime-local" value="{{ $session['starts_at'] ?? '' }}" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><input name="sessions[{{ $index }}][ends_at]" type="datetime-local" value="{{ $session['ends_at'] ?? '' }}" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><select name="sessions[{{ $index }}][venue_id]" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><option value="">Main venue</option>@foreach($venues as $venue)<option value="{{ $venue->id }}" @selected((string) ($session['venue_id'] ?? '') === (string) $venue->id)>{{ $venue->name }}</option>@endforeach</select><input name="sessions[{{ $index }}][capacity]" type="number" min="0" value="{{ $session['capacity'] ?? '' }}" placeholder="Capacity" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm"><button type="button" data-remove-row class="btn btn-outline-danger btn-md">Remove</button></div><textarea name="sessions[{{ $index }}][description]" rows="2" placeholder="Session description" class="mt-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $session['description'] ?? '' }}</textarea></div>
            @endforeach
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between"><h2 class="text-base font-semibold">Event Page Builder</h2><a href="{{ isset($event) ? route('admin.events.builder.edit', $event) : '#page-builder-inline' }}" class="text-sm font-semibold text-emerald-700">Open builder</a></div>
        <input type="hidden" name="page_sections" id="page_sections" value="{{ $sections }}">
        <p class="mt-2 text-sm text-slate-500">A draft page is created from the event details. Use the builder after saving for add/remove/reorder/duplicate controls.</p>
    </section>

    <div class="flex flex-wrap gap-3"><button class="btn btn-primary btn-md">{{ $button ?? 'Save event' }}</button><a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary btn-md">Cancel</a></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('[data-session-list]');
    document.querySelector('[data-add-session]')?.addEventListener('click', () => {
        const index = list.children.length;
        const first = list.querySelector('[data-session-row]').cloneNode(true);
        first.querySelectorAll('input, textarea, select').forEach(el => { el.name = el.name.replace(/sessions\[\d+\]/, `sessions[${index}]`); if (el.tagName !== 'SELECT') el.value = ''; });
        list.appendChild(first);
    });
    list?.addEventListener('click', e => { if (e.target.matches('[data-remove-row]') && list.children.length > 1) e.target.closest('[data-session-row]').remove(); });
});
</script>
