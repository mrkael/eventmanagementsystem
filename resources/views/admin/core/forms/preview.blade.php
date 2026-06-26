<x-layouts.admin title="Preview Form" heading="Preview Form" eyebrow="Forms">
    <x-ui.page-header
        eyebrow="Preview"
        title="{{ $form->title }}"
        description="This preview shows the saved field order and labels. Submission is intentionally not enabled yet."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.forms.edit', [$event, $form]) }}" class="ds-button-primary">Edit Form</a>
            <a href="{{ route('core.events.forms.index', $event) }}" class="ds-button-secondary">Back to Forms</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'forms'])

    <div class="mx-auto max-w-3xl">
        <x-ui.card>
            <div class="border-b border-slate-100 pb-5">
                <h2 class="text-2xl font-semibold text-slate-950">{{ $form->title }}</h2>
            </div>
            <div class="mt-6 space-y-5">
                @forelse($form->fields as $field)
                    <label class="block">
                        <span class="ds-label">{{ $field->label }} @if($field->is_required)<span class="text-red-600">*</span>@endif</span>
                        @if($field->type === 'textarea')
                            <textarea disabled rows="4" class="ds-input mt-2 py-3" placeholder="{{ $field->placeholder }}"></textarea>
                        @elseif($field->type === 'dropdown')
                            <select disabled class="ds-input mt-2">
                                <option>{{ $field->placeholder ?: 'Select an option' }}</option>
                                @foreach(($field->options ?: []) as $option)
                                    <option>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif($field->type === 'radio')
                            <div class="mt-3 space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @foreach(($field->options ?: []) as $option)
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input disabled type="radio" name="preview_{{ $field->id }}" class="border-slate-300 text-blue-700">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif($field->type === 'checkbox')
                            <div class="mt-3 space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @foreach(($field->options ?: []) as $option)
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input disabled type="checkbox" class="rounded border-slate-300 text-blue-700">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif($field->type === 'file')
                            <div class="mt-3 rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-slate-500 shadow-sm">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4" /><path d="m7 9 5-5 5 5" /><path d="M5 20h14" /></svg>
                                </div>
                                <p class="mt-3 text-sm font-bold text-slate-700">{{ $field->placeholder ?: 'Upload file' }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Click to upload or drag and drop</p>
                                <p class="mt-3 text-xs font-semibold text-slate-400">PDF, JPG, PNG, DOC, DOCX up to 10MB</p>
                            </div>
                        @else
                            <input disabled type="{{ $field->type }}" class="ds-input mt-2" placeholder="{{ $field->placeholder }}">
                        @endif
                        @if($field->error_text)
                            <span class="mt-2 block text-xs font-semibold text-slate-500">{{ $field->error_text }}</span>
                        @endif
                    </label>
                @empty
                    <x-ui.empty-state icon="editor" title="No fields saved" description="Edit this form and add fields to preview the registration experience." />
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
