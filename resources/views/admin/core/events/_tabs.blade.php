@php($active = $active ?? 'settings')

<div class="mb-6 flex flex-wrap gap-2 rounded-[24px] border border-slate-200 bg-white/80 p-2 shadow-sm">
    <a href="{{ route('core.events.edit', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'settings' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Settings</a>
    <a href="{{ route('core.events.tickets.index', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'tickets' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Tickets</a>
    <a href="{{ route('core.events.forms.index', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'forms' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Forms</a>
    <a href="{{ route('core.events.microsite.edit', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'site' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Site</a>
    <a href="{{ route('core.events.email.edit', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'email' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Email</a>
    <a href="{{ route('core.events.attendees.index', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'attendees' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Attendees</a>
    <a href="{{ route('core.events.agendas.index', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'agenda' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Agenda</a>
    <a href="{{ route('core.events.check-in.index', $event) }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $active === 'check-in' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">Check-In</a>
</div>
