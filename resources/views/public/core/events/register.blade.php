<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register - {{ $event->title }}</title>@include('partials.assets')</head>
<body class="bg-slate-50 text-slate-950">
    <main class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('core.public.events.show', ['event' => $event->custom_url]) }}" class="text-sm font-semibold text-emerald-700">Back to event</a>
            <h1 class="mt-3 text-3xl font-bold">{{ $ticket->name }}</h1>
            <p class="mt-2 text-slate-600">{{ $event->title }} · {{ $event->starts_at->format('d M Y, H:i') }}</p>
        </div>
        @if($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('core.public.submit', ['event' => $event->custom_url, 'ticket' => $ticket]) }}" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block"><span class="text-sm font-medium">Full name</span><input name="full_name" value="{{ old('full_name') }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block"><span class="text-sm font-medium">Email</span><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block"><span class="text-sm font-medium">Phone number</span><input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block"><span class="text-sm font-medium">Organization</span><input name="organization" value="{{ old('organization') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block"><span class="text-sm font-medium">Designation</span><input name="designation" value="{{ old('designation') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block"><span class="text-sm font-medium">Promo code</span><input name="promo_code" value="{{ old('promo_code') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            </div>
            <div class="mt-6 space-y-4">
                @foreach($ticket->form?->fields ?? [] as $field)
                    <label class="block">
                        <span class="text-sm font-medium">{{ $field->label }} @if($field->is_required)<span class="text-red-600">*</span>@endif</span>
                        @if($field->type === 'textarea')
                            <textarea name="answers[{{ $field->key }}]" rows="3" @required($field->is_required) placeholder="{{ $field->placeholder }}" class="mt-1 w-full rounded-lg border-slate-300">{{ old('answers.'.$field->key) }}</textarea>
                        @elseif(in_array($field->type, ['dropdown','radio']))
                            <select name="answers[{{ $field->key }}]" @required($field->is_required) class="mt-1 w-full rounded-lg border-slate-300"><option value="">Select</option>@foreach($field->options ?? [] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>
                        @elseif($field->type === 'checkbox')
                            <div class="mt-2 space-y-2">@foreach($field->options ?? [] as $option)<label class="flex gap-2 text-sm"><input type="checkbox" name="answers[{{ $field->key }}][]" value="{{ $option }}"> {{ $option }}</label>@endforeach</div>
                        @elseif($field->type === 'file')
                            <input type="file" name="answer_files[{{ $field->key }}]" @required($field->is_required) class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2">
                        @else
                            <input type="{{ $field->type }}" name="answers[{{ $field->key }}]" value="{{ old('answers.'.$field->key) }}" @required($field->is_required) placeholder="{{ $field->placeholder }}" class="mt-1 w-full rounded-lg border-slate-300">
                        @endif
                    </label>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end"><button class="min-h-11 rounded-lg bg-emerald-700 px-5 text-sm font-semibold text-white">Submit registration</button></div>
        </form>
    </main>
</body>
</html>
