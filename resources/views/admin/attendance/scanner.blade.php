<x-layouts.admin title="Attendance Scanner" heading="Attendance Scanner" subheading="{{ $event->title }}">
    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold">Camera scan</h2>
                    <p class="text-sm text-slate-500">Use a laptop camera or mobile device camera to scan a participant QR code.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" data-start class="btn btn-primary btn-md">Start camera</button>
                    <button type="button" data-stop class="btn btn-outline-primary btn-md">Stop</button>
                </div>
            </div>
            <div id="qr-reader" class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-slate-950"></div>
            <div data-result class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">Scanner idle.</div>
        </section>

        <aside class="space-y-5">
            <form data-manual class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Scan settings</h2>
                <label class="mt-4 block text-sm font-medium">Session</label>
                <select name="event_session_id" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm">
                    <option value="">General event check-in</option>
                    @foreach($event->sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->title }} - {{ $session->starts_at->format('d M H:i') }}</option>
                    @endforeach
                </select>
                <h2 class="mt-5 font-semibold">Manual token</h2>
                <label class="mt-4 block text-sm font-medium">QR token</label>
                <textarea name="token" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Paste or type QR token"></textarea>
                <label class="mt-3 block text-sm font-medium">Notes</label>
                <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="mt-4 grid gap-2">
                    <button data-action="check-in" class="btn btn-primary btn-md">Check in</button>
                    <button data-action="check-out" class="btn btn-outline-primary btn-md">Check out</button>
                </div>
            </form>
            <a href="{{ route('admin.events.attendance.index', $event) }}" class="btn btn-outline-primary btn-md w-full">Back to dashboard</a>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const result = document.querySelector('[data-result]');
            const manual = document.querySelector('[data-manual]');
            const routes = {
                'check-in': @json(route('admin.events.attendance.check-in', $event)),
                'check-out': @json(route('admin.events.attendance.check-out', $event)),
            };
            let scanner = null;
            let busy = false;

            const show = (message, ok = true) => {
                result.textContent = message;
                result.className = `mt-4 rounded-lg border px-4 py-3 text-sm ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'}`;
            };

            const submitScan = async (token, action = 'check-in', notes = '') => {
                if (busy || !token) return;
                busy = true;
                try {
                    const response = await fetch(routes[action], {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                        body: JSON.stringify({token, notes, event_session_id: manual.event_session_id.value, device_name: navigator.userAgent.slice(0, 120)}),
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Scan failed.');
                    show(`${data.message} ${data.registration.name} (${data.registration.email})`);
                } catch (error) {
                    show(error.message, false);
                } finally {
                    setTimeout(() => busy = false, 1400);
                }
            };

            document.querySelector('[data-start]').addEventListener('click', async () => {
                if (!window.Html5Qrcode) {
                    show('Camera scanner library is unavailable. Use manual token entry.', false);
                    return;
                }
                scanner = scanner || new window.Html5Qrcode('qr-reader');
                try {
                    await scanner.start({ facingMode: 'environment' }, { fps: 8, qrbox: { width: 260, height: 260 } }, decoded => submitScan(decoded, 'check-in'));
                    show('Camera active. Point it at a participant QR code.');
                } catch (error) {
                    show('Camera could not start. Check browser camera permission or use manual token entry.', false);
                }
            });

            document.querySelector('[data-stop]').addEventListener('click', async () => {
                if (scanner?.isScanning) await scanner.stop();
                show('Scanner stopped.');
            });

            manual.addEventListener('submit', (event) => {
                event.preventDefault();
                submitScan(manual.token.value.trim(), event.submitter.dataset.action, manual.notes.value);
            });
        });
    </script>
</x-layouts.admin>
