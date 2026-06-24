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
                        @elseif(in_array($field->type, ['dropdown', 'radio', 'checkbox'], true))
                            <select disabled class="ds-input mt-2">
                                <option>{{ $field->placeholder ?: 'Select an option' }}</option>
                                @foreach(($field->options ?: []) as $option)
                                    <option>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input disabled type="{{ $field->type === 'file' ? 'text' : $field->type }}" class="ds-input mt-2" placeholder="{{ $field->placeholder }}">
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
