@php
    $active      = $active ?? 'settings';
    $tabBase     = 'relative z-10 rounded-full px-4 py-2 text-sm font-semibold transition-colors duration-150 select-none';
    $tabActive   = 'text-white';
    $tabInactive = 'text-slate-600 hover:text-[#002169]';
    $tabs = [
        ['key' => 'settings',  'label' => 'Settings',  'route' => route('core.events.edit', $event)],
        ['key' => 'tickets',   'label' => 'Tickets',   'route' => route('core.events.tickets.index', $event)],
        ['key' => 'forms',     'label' => 'Forms',     'route' => route('core.events.forms.index', $event)],
        ['key' => 'site',      'label' => 'Site',      'route' => route('core.events.microsite.edit', $event)],
        ['key' => 'email',     'label' => 'Email',     'route' => route('core.events.email.edit', $event)],
        ['key' => 'attendees', 'label' => 'Attendees', 'route' => route('core.events.attendees.index', $event)],
        ['key' => 'agenda',    'label' => 'Agenda',    'route' => route('core.events.agendas.index', $event)],
        ['key' => 'check-in',  'label' => 'Check-In',  'route' => route('core.events.check-in.index', $event)],
    ];
@endphp

<nav aria-label="Event sections" data-tab-nav class="relative mb-6 flex flex-wrap gap-1 rounded-2xl border border-slate-200 bg-white/80 p-1.5 shadow-sm">

    {{-- Sliding glider pill — JS positions and sizes it --}}
    <span
        data-tab-glider
        aria-hidden="true"
        class="pointer-events-none absolute rounded-full bg-[#002169] shadow-sm"
        style="top:0;left:0;width:0;height:0;"
    ></span>

    @foreach($tabs as $tab)
        <a
            href="{{ $tab['route'] }}"
            data-tab-key="{{ $tab['key'] }}"
            @if($active === $tab['key']) data-tab-active @endif
            class="{{ $tabBase }} {{ $active === $tab['key'] ? $tabActive : $tabInactive }}"
        >{{ $tab['label'] }}</a>
    @endforeach

</nav>

<script>
(function () {
    const nav    = document.querySelector('[data-tab-nav]');
    if (!nav) return;
    const glider = nav.querySelector('[data-tab-glider]');
    const active = nav.querySelector('[data-tab-active]');
    if (!glider || !active) return;

    const EASING = 'cubic-bezier(0.37, 1.95, 0.66, 0.56)';
    const DURATION = '380ms';

    function applyGlider(el, animate) {
        glider.style.transition = animate
            ? `left ${DURATION} ${EASING}, top ${DURATION} ${EASING}, width ${DURATION} ${EASING}, height ${DURATION} ${EASING}`
            : 'none';
        glider.style.left   = el.offsetLeft + 'px';
        glider.style.top    = el.offsetTop  + 'px';
        glider.style.width  = el.offsetWidth  + 'px';
        glider.style.height = el.offsetHeight + 'px';
    }

    // Snap to active tab on load (no animation)
    applyGlider(active, false);
    // Force reflow so the snap is applied before transitions are enabled
    glider.offsetWidth;
    glider.style.transition = `left ${DURATION} ${EASING}, top ${DURATION} ${EASING}, width ${DURATION} ${EASING}, height ${DURATION} ${EASING}`;

    const allLinks = nav.querySelectorAll('a[data-tab-key]');

    function setColors(target) {
        allLinks.forEach(function (link) {
            if (link === target) {
                link.classList.remove('text-slate-600');
                link.classList.add('text-white');
            } else {
                link.classList.remove('text-white');
                link.classList.add('text-slate-600');
            }
        });
    }

    // Slide glider, swap colours, fade content out, then navigate
    allLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.href;

            applyGlider(this, true);
            setColors(this);

            // Fade out the page content below the tabs
            const main = document.getElementById('main-content');
            if (main) main.classList.add('ds-page-exit');

            // Navigate after the glider reaches its overshoot peak (~220ms)
            setTimeout(function () { window.location.href = href; }, 220);
        });
    });
})();
</script>
