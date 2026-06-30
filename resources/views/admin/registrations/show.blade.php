<x-layouts.admin title="Registration Detail" heading="{{ $registration->name }}" subheading="{{ $event->title }}">
    <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <section class="space-y-5">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-slate-500">Email</dt><dd class="font-medium">{{ $registration->email }}</dd></div>
                    <div><dt class="text-sm text-slate-500">Phone</dt><dd>{{ $registration->phone ?? '-' }}</dd></div>
                    <div><dt class="text-sm text-slate-500">Organization</dt><dd>{{ $registration->organization ?? '-' }}</dd></div>
                    <div><dt class="text-sm text-slate-500">Source</dt><dd>{{ ucfirst($registration->source) }}</dd></div>
                </dl>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Answers</h2>
                <div class="mt-4 space-y-3">
                    @foreach($registration->answers as $answer)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-sm font-semibold">{{ $answer->question_label }}</p>
                            @if($answer->files->isNotEmpty())
                                @foreach($answer->files as $file)
                                    <a href="{{ asset('storage/'.$file->path) }}" class="mt-2 inline-block text-sm font-semibold text-emerald-700">{{ $file->original_name }}</a>
                                @endforeach
                            @else
                                <p class="mt-1 text-sm text-slate-600">{{ is_array($answer->value) ? implode(', ', $answer->value) : ($answer->value ?? '-') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <aside class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Status</p>
                <p class="mt-1 text-lg font-semibold">{{ $registration->status->label() }}</p>
            </div>
            <form method="POST" action="{{ route('admin.events.registrations.status', [$event, $registration]) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium">Change status</label>
                <select name="status" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected($registration->status === $status)>{{ $status->label() }}</option>@endforeach</select>
                <button class="btn btn-primary btn-md mt-3 w-full">Update status</button>
            </form>
            @if($registration->status->value === 'pending')
                <form method="POST" action="{{ route('admin.events.registrations.approve', [$event, $registration]) }}">@csrf<button class="btn btn-primary btn-md w-full">Approve registration</button></form>
            @endif
            @if(in_array($registration->status->value, ['confirmed', 'attended'], true))
                <form method="POST" action="{{ route('admin.events.attendance.qr', [$event, $registration]) }}">@csrf<button class="btn btn-primary btn-md w-full">Generate attendance QR</button></form>
            @endif
            <a href="{{ route('admin.events.attendance.index', $event) }}" class="btn btn-outline-primary btn-md w-full">Attendance dashboard</a>
            <a href="{{ route('admin.events.registrations.index', $event) }}" class="btn btn-outline-primary btn-md w-full">Back to participants</a>
        </aside>
    </div>
</x-layouts.admin>
