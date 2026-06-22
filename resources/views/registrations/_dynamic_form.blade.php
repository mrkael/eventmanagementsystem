@php
    $invite = $invite ?? null;
    $submitLabel = $submitLabel ?? 'Submit registration';
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Full name <span class="text-red-600">*</span></span>
            <input name="name" value="{{ old('name', $invite?->name) }}" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Email <span class="text-red-600">*</span></span>
            <input type="email" name="email" value="{{ old('email', $invite?->email) }}" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Phone</span>
            <input name="phone" value="{{ old('phone') }}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Organization</span>
            <input name="organization" value="{{ old('organization') }}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </label>
    </div>

    @foreach($form->groups as $group)
        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-semibold text-slate-900">{{ $group->title }}</legend>
            @if($group->description)
                <p class="mt-1 text-sm text-slate-500">{{ $group->description }}</p>
            @endif
            <div class="mt-4 grid gap-4">
                @foreach($group->questions as $question)
                    @php
                        $field = "answers.{$question->key}";
                        $name = "answers[{$question->key}]";
                        $oldValue = old($field);
                        $options = $question->options ?: [];
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="question-{{ $question->id }}">
                            {{ $question->label }} @if($question->is_required)<span class="text-red-600">*</span>@endif
                        </label>
                        @if($question->help_text)
                            <p class="mt-1 text-xs text-slate-500">{{ $question->help_text }}</p>
                        @endif

                        @if($question->type === 'textarea')
                            <textarea id="question-{{ $question->id }}" name="{{ $name }}" @required($question->is_required) rows="4" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ $oldValue }}</textarea>
                        @elseif($question->type === 'dropdown')
                            <select id="question-{{ $question->id }}" name="{{ $name }}" @required($question->is_required) class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select an option</option>
                                @foreach($options as $option)
                                    <option value="{{ $option }}" @selected($oldValue === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif($question->type === 'radio')
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($options as $option)
                                    <label class="flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm">
                                        <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($oldValue === $option) @required($question->is_required)>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($question->type === 'checkbox')
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($options as $option)
                                    <label class="flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm">
                                        <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" @checked(in_array($option, (array) $oldValue, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($question->type === 'file')
                            <input id="question-{{ $question->id }}" type="file" name="answer_files[{{ $question->key }}]" @required($question->is_required) class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:font-semibold file:text-emerald-800">
                            <p class="mt-1 text-xs text-slate-500">PDF, DOC, DOCX, JPG, or PNG up to 10MB.</p>
                        @else
                            <input id="question-{{ $question->id }}" type="{{ $question->type === 'email' ? 'email' : ($question->type === 'number' ? 'number' : ($question->type === 'date' ? 'date' : 'text')) }}" name="{{ $name }}" value="{{ is_array($oldValue) ? '' : $oldValue }}" @required($question->is_required) class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @endif
                        @error($field)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    <button type="submit" class="min-h-11 w-full rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 sm:w-auto">{{ $submitLabel }}</button>
</div>
