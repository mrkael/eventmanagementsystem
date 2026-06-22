<x-layouts.admin title="Scanner" heading="QR Scanner" subheading="{{ $session->title }}">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div id="reader" class="overflow-hidden rounded-lg border border-slate-200"></div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" data-action="check_in" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Check in mode</button>
                @if($session->checkout_enabled)<button type="button" data-action="check_out" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Check out mode</button>@endif
            </div>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Scan result</h2>
            <div data-result class="mt-4 rounded-lg border border-slate-200 p-4 text-sm text-slate-600">Camera scan result will appear here.</div>
            <div class="mt-6 grid grid-cols-2 gap-3 text-sm" data-counter></div>
        </section>
    </div>
    <script>
        let action = 'check_in';
        const result = document.querySelector('[data-result]');
        const counter = document.querySelector('[data-counter]');
        document.querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', () => action = button.dataset.action));
        const refreshCounter = () => fetch(@json(route('core.events.sessions.counter', [$event, $session]))).then(r => r.json()).then(data => {
            counter.innerHTML = `<div class="rounded border p-3">Eligible<br><strong>${data.eligible}</strong></div><div class="rounded border p-3">Checked in<br><strong>${data.checked_in}</strong></div><div class="rounded border p-3">Checked out<br><strong>${data.checked_out}</strong></div><div class="rounded border p-3">Attendance<br><strong>${data.percentage}%</strong></div>`;
        });
        window.addEventListener('load', () => {
            refreshCounter();
            const scanner = new window.Html5QrcodeScanner('reader', { fps: 10, qrbox: 260 });
            let busy = false;
            scanner.render(text => {
                if (busy) return;
                busy = true;
                fetch(@json(route('core.events.sessions.scan', [$event, $session])), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
                    body: JSON.stringify({token: text, action})
                }).then(async response => {
                    const data = await response.json();
                    result.className = `mt-4 rounded-lg border p-4 text-sm ${data.ok ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-800'}`;
                    result.innerHTML = data.ok ? `<p class="font-semibold">${data.message}</p><p class="mt-2">${data.participant.name}</p><p>${data.participant.email}</p><p>${data.participant.ticket} · ${data.participant.reference}</p>` : data.message;
                    refreshCounter();
                    setTimeout(() => busy = false, 1800);
                });
            });
        });
    </script>
</x-layouts.admin>
