@php
    $key = $field->key;
    $storedAnswer = $answers->get($key);
    $value = old("answers.{$key}", $storedAnswer?->value);
    $options = $field->options ?: [];
@endphp

<label class="block">
    <span class="ds-label">{{ $field->label }} @if($field->is_required)<span class="text-red-600">*</span>@endif</span>

    @if($field->type === 'textarea')
        <textarea name="answers[{{ $key }}]" rows="4" @required($field->is_required) class="ds-input mt-2 py-3" placeholder="{{ $field->placeholder }}">{{ is_array($value) ? implode(', ', $value) : $value }}</textarea>
    @elseif($field->type === 'dropdown')
        <select name="answers[{{ $key }}]" @required($field->is_required) class="ds-input mt-2">
            <option value="">{{ $field->placeholder ?: 'Select an option' }}</option>
            @foreach($options as $option)
                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
            @endforeach
        </select>
    @elseif($field->type === 'radio')
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach($options as $option)
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="radio" name="answers[{{ $key }}]" value="{{ $option }}" @checked($value === $option) @required($field->is_required) class="border-slate-300 text-slate-950">
                    {{ $option }}
                </label>
            @endforeach
        </div>
    @elseif($field->type === 'checkbox')
        @php($selected = collect(is_array($value) ? $value : ($value ? [$value] : []))->all())
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach($options as $option)
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" @checked(in_array($option, $selected, true)) class="rounded border-slate-300 text-slate-950">
                    {{ $option }}
                </label>
            @endforeach
        </div>
    @elseif($field->type === 'file')
        <input type="file" name="answer_files[{{ $key }}]" @required($field->is_required && ! $storedAnswer?->file_path) class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
        @if($storedAnswer?->file_path)
            <p class="mt-2 text-xs font-semibold text-slate-500">Current file: {{ $storedAnswer->file_path }}</p>
        @endif
    @else
        <input type="{{ in_array($field->type, ['email', 'number', 'date'], true) ? $field->type : 'text' }}" name="answers[{{ $key }}]" value="{{ is_array($value) ? implode(', ', $value) : $value }}" @required($field->is_required) class="ds-input mt-2" placeholder="{{ $field->placeholder }}">
    @endif

    @error("answers.{$key}")<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
    @error("answer_files.{$key}")<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
    @if($field->error_text)
        <span class="mt-2 block text-xs font-semibold text-slate-500">{{ $field->error_text }}</span>
    @endif
</label>
