@php
    $sessionOptions = $sessions->map(fn ($session) => [
        'id' => $session->id,
        'label' => collect([
            $session->title,
            $session->starts_at?->format('d M Y, H:i'),
            $session->venue_name ?: $session->location,
        ])->filter()->implode(' - '),
        'ticket_count' => $session->tickets->count(),
    ])->values();

    $initialRecords = $records->map(fn ($record) => [
        'participant_name' => $record->registration?->full_name,
        'participant_email' => $record->registration?->email,
        'ticket_name' => $record->registration?->ticket?->name,
        'registration_reference' => $record->registration?->reference_number,
        'checked_in_at' => $record->checked_in_at?->format('d M Y, H:i:s'),
        'checked_in_by' => $record->checkedInBy?->name,
        'attendee_url' => $record->registration ? route('core.events.attendees.show', [$event, $record->registration]) : null,
        'latitude' => $record->latitude,
        'longitude' => $record->longitude,
        'location_name' => $record->location_name,
    ])->values();
@endphp

<x-layouts.admin title="Check-In" heading="Check-In" eyebrow="Event Details">
    <x-ui.page-header
        eyebrow="Check-In"
        title="{{ $event->title }}"
        description="Select a session, open the device camera, and scan participant QR codes for check-in."
    >
        <x-slot:actions>
            <a href="{{ route('core.events.show', $event) }}" class="ds-button-secondary">Event Details</a>
        </x-slot:actions>
    </x-ui.page-header>

    @include('admin.core.events._tabs', ['event' => $event, 'active' => 'check-in'])

    <div data-check-in-root
        data-scan-url="{{ route('core.events.check-in.scan', $event) }}"
        data-current-session="{{ $selectedSession?->id }}"
        data-session-options='@json($sessionOptions)'
        data-initial-records='@json($initialRecords)'>
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-6">
                <x-ui.card>
                    <div class="grid gap-4 lg:grid-cols-[1fr_auto_auto] lg:items-end">
                        <label class="form-ticket-select block">
                            <span class="ds-label">Session <span class="text-red-600">*</span></span>
                            <select data-session-select class="mt-2">
                                <option value="">Select session</option>
                                @foreach($sessionOptions as $option)
                                    <option value="{{ $option['id'] }}" @selected($selectedSession?->id === $option['id'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <span class="mt-2 block text-xs font-semibold text-slate-500">Search and select the session for check-in.</span>
                        </label>
                        <button type="button" data-open-camera class="ds-button-primary justify-center" @disabled(! $selectedSession || $selectedSession->tickets->isEmpty())>Open Camera</button>
                        <button type="button" data-stop-camera class="ds-button-secondary justify-center" disabled>Stop Camera</button>
                    </div>

                    @if($selectedSession && $selectedSession->tickets->isEmpty())
                        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800">
                            No ticket is assigned to this session. Assign tickets in Agenda before check-in.
                        </div>
                    @endif
                </x-ui.card>

                <div class="grid gap-4 md:grid-cols-4">
                    <x-ui.card>
                        <p class="text-xs font-black uppercase text-slate-500">Eligible</p>
                        <p data-count-eligible class="mt-2 text-3xl font-black text-slate-950">{{ $counts['eligible'] }}</p>
                    </x-ui.card>
                    <x-ui.card>
                        <p class="text-xs font-black uppercase text-slate-500">Checked In</p>
                        <p data-count-checked-in class="mt-2 text-3xl font-black text-emerald-700">{{ $counts['checked_in'] }}</p>
                    </x-ui.card>
                    <x-ui.card>
                        <p class="text-xs font-black uppercase text-slate-500">Pending</p>
                        <p data-count-pending class="mt-2 text-3xl font-black text-amber-700">{{ $counts['pending'] }}</p>
                    </x-ui.card>
                    <x-ui.card>
                        <p class="text-xs font-black uppercase text-slate-500">Progress</p>
                        <p data-count-percentage class="mt-2 text-3xl font-black text-blue-700">{{ $counts['percentage'] }}%</p>
                    </x-ui.card>
                </div>

                <x-ui.card>
                    <div class="grid gap-5 lg:grid-cols-[1fr_20rem]">
                        <div>
                            <div id="qr-reader" class="grid min-h-[320px] place-items-center overflow-hidden rounded-[24px] border border-dashed border-slate-300 bg-slate-50 text-center text-sm font-semibold text-slate-500">
                                Camera preview appears here.
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div data-scan-message class="rounded-[22px] border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-500">
                                Select a session and open camera to start scanning.
                            </div>
                            <div data-participant-card class="hidden rounded-[22px] border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase text-emerald-700">Last successful check-in</p>
                                <h3 data-participant-name class="mt-2 text-xl font-black text-emerald-950"></h3>
                                <p data-participant-email class="mt-1 text-sm font-semibold text-emerald-800"></p>
                                <p data-participant-meta class="mt-3 text-sm font-bold text-emerald-900"></p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card padding="p-0" class="overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-lg font-black text-slate-950">Checked-In Participants</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                                <tr>
                                    <th class="px-5 py-4">Participant Name</th>
                                    <th class="px-5 py-4">Participant Email</th>
                                    <th class="px-5 py-4">Ticket Name</th>
                                    <th class="px-5 py-4">Reference</th>
                                    <th class="px-5 py-4">Check-In Time</th>
                                    <th class="px-5 py-4">Checked-In By</th>
                                    <th class="px-5 py-4">Location</th>
                                    <th class="px-5 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody data-records-body class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <div data-empty-records class="hidden px-5 py-10">
                        <x-ui.empty-state icon="users" title="No check-ins yet" description="Scanned participants will appear here for the selected session." />
                    </div>
                </x-ui.card>
            </div>

            <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                <x-ui.card>
                    <h2 class="text-xl font-black text-slate-950">Selected Session</h2>
                    @if($selectedSession)
                        <p class="mt-3 font-bold text-slate-950">{{ $selectedSession->title }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $selectedSession->starts_at?->format('d M Y, H:i') }} - {{ $selectedSession->venue_name ?: $selectedSession->location ?: 'No venue' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($selectedSession->tickets as $ticket)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $ticket->name }}</span>
                            @empty
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">No ticket assigned</span>
                            @endforelse
                        </div>
                    @else
                        <p class="mt-3 text-sm font-semibold text-slate-500">No session selected.</p>
                    @endif
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-xl font-black text-slate-950">Camera Tips</h2>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <p>Allow camera permission when prompted by the browser.</p>
                        <p>Use HTTPS or localhost for mobile camera access.</p>
                        <p>The scanner pauses briefly after each QR code to prevent duplicate rapid scans.</p>
                    </div>
                </x-ui.card>
            </aside>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-check-in-root]');
            if (!root) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const sessionSelect = root.querySelector('[data-session-select]');
            const openButton = root.querySelector('[data-open-camera]');
            const stopButton = root.querySelector('[data-stop-camera]');
            const message = root.querySelector('[data-scan-message]');
            const participantCard = root.querySelector('[data-participant-card]');
            const recordsBody = root.querySelector('[data-records-body]');
            const emptyRecords = root.querySelector('[data-empty-records]');
            const scanUrl = root.dataset.scanUrl;
            let scanner = null;
            let scanning = false;
            const recentScans = new Map();

            let cachedLocation = null;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        let locationName = null;
                        try {
                            const r = await fetch(
                                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
                                { headers: { 'Accept-Language': 'en' } },
                            );
                            if (r.ok) {
                                const geo = await r.json();
                                const addr = geo.address || {};
                                const parts = [
                                    addr.road,
                                    addr.suburb || addr.neighbourhood || addr.quarter,
                                    addr.city || addr.town || addr.village || addr.county,
                                ].filter((v, i, a) => v && a.indexOf(v) === i);
                                locationName = parts.join(', ') || geo.display_name?.split(',').slice(0, 3).join(',').trim() || null;
                            }
                        } catch {}
                        cachedLocation = { latitude: lat, longitude: lng, location_name: locationName };
                    },
                    () => {},
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 },
                );
            }

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));

            const setMessage = (text, type = 'neutral') => {
                const classes = {
                    neutral: 'border-slate-200 bg-white text-slate-500',
                    success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                    error: 'border-red-200 bg-red-50 text-red-800',
                    warning: 'border-amber-200 bg-amber-50 text-amber-800',
                };
                message.className = `rounded-[22px] border p-4 text-sm font-semibold ${classes[type]}`;
                message.textContent = text;
            };

            const updateCounts = (counts) => {
                if (!counts) return;
                root.querySelector('[data-count-eligible]').textContent = counts.eligible ?? 0;
                root.querySelector('[data-count-checked-in]').textContent = counts.checked_in ?? 0;
                root.querySelector('[data-count-pending]').textContent = counts.pending ?? 0;
                root.querySelector('[data-count-percentage]').textContent = `${counts.percentage ?? 0}%`;
            };

            const renderRecords = (records) => {
                recordsBody.innerHTML = '';
                emptyRecords.classList.toggle('hidden', records.length > 0);
                records.forEach((record) => {
                    const row = document.createElement('tr');
                    row.className = 'transition hover:bg-slate-50';
                    const hasLocation = record.latitude != null && record.longitude != null;
                    const coordText = hasLocation ? `${Number(record.latitude).toFixed(5)}, ${Number(record.longitude).toFixed(5)}` : null;
                    const mapsUrl = hasLocation ? `https://maps.google.com/?q=${record.latitude},${record.longitude}` : null;
                    const locationCell = hasLocation
                        ? `<a href="${mapsUrl}" target="_blank" rel="noopener" class="text-blue-600 hover:underline leading-tight">${record.location_name ? escapeHtml(record.location_name) : coordText}</a>${record.location_name ? `<br><span class="text-xs text-slate-400">${coordText}</span>` : ''}`
                        : '–';
                    row.innerHTML = `
                        <td class="px-5 py-4 font-bold text-slate-950">${escapeHtml(record.participant_name)}</td>
                        <td class="px-5 py-4 text-slate-600">${escapeHtml(record.participant_email)}</td>
                        <td class="px-5 py-4 text-slate-600">${escapeHtml(record.ticket_name)}</td>
                        <td class="px-5 py-4 font-semibold text-slate-700">${escapeHtml(record.registration_reference)}</td>
                        <td class="px-5 py-4 text-slate-600">${escapeHtml(record.checked_in_at)}</td>
                        <td class="px-5 py-4 text-slate-600">${escapeHtml(record.checked_in_by || '-')}</td>
                        <td class="px-5 py-4 text-slate-600">${locationCell}</td>
                        <td class="px-5 py-4 text-right">${record.attendee_url ? `<a href="${record.attendee_url}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white">View</a>` : ''}</td>
                    `;
                    recordsBody.appendChild(row);
                });
            };

            const stopCamera = async () => {
                if (scanner && scanning) {
                    await scanner.stop();
                    scanning = false;
                }
                stopButton.disabled = true;
                openButton.disabled = !sessionSelect.value;
            };

            const submitScan = async (qrToken) => {
                const now = Date.now();
                if (recentScans.has(qrToken) && now - recentScans.get(qrToken) < 3500) return;
                recentScans.set(qrToken, now);

                const response = await fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        session_id: sessionSelect.value,
                        qr_token: qrToken,
                        device_name: navigator.userAgent.slice(0, 160),
                        latitude: cachedLocation?.latitude ?? null,
                        longitude: cachedLocation?.longitude ?? null,
                        location_name: cachedLocation?.location_name ?? null,
                    }),
                });
                const data = await response.json();
                updateCounts(data.counts);

                if (!response.ok || !data.success) {
                    setMessage(data.message || 'QR code could not be checked in.', 'error');
                    return;
                }

                await stopCamera();
                setMessage(data.message, 'success');
                participantCard.classList.remove('hidden');
                root.querySelector('[data-participant-name]').textContent = data.participant_name;
                root.querySelector('[data-participant-email]').textContent = data.participant_email;
                root.querySelector('[data-participant-meta]').textContent = `${data.ticket_name} - ${data.registration_reference} - ${data.session_name}`;
                renderRecords(data.records || []);
            };

            const openCamera = async () => {
                if (!sessionSelect.value) {
                    setMessage('Select a session before opening the camera.', 'warning');
                    return;
                }
                if (!window.Html5Qrcode) {
                    setMessage('QR scanner library is still loading. Please try again.', 'warning');
                    return;
                }

                try {
                    openButton.disabled = true;
                    scanner = scanner || new window.Html5Qrcode('qr-reader');
                    const cameras = await window.Html5Qrcode.getCameras();
                    if (!cameras.length) {
                        setMessage('No camera was found on this device.', 'error');
                        openButton.disabled = false;
                        return;
                    }
                    const preferred = cameras.find((camera) => /back|rear|environment/i.test(camera.label)) || cameras[0];
                    await scanner.start(
                        preferred.id,
                        { fps: 10, qrbox: { width: 260, height: 260 } },
                        (decodedText) => submitScan(decodedText),
                    );
                    scanning = true;
                    stopButton.disabled = false;
                    setMessage('Camera is active. Scan a participant QR code.', 'neutral');
                } catch (error) {
                    openButton.disabled = false;
                    stopButton.disabled = true;
                    setMessage('Camera could not be opened. Please allow camera permission and try again.', 'error');
                }
            };

            const bootSelect = () => {
                if (!window.TomSelect) {
                    window.setTimeout(bootSelect, 50);
                    return;
                }
                if (!sessionSelect.tomselect) {
                    new window.TomSelect(sessionSelect, {
                        maxItems: 1,
                        create: false,
                        placeholder: 'Search and select session',
                        onChange: (value) => {
                            const url = new URL(window.location.href);
                            if (value) {
                                url.searchParams.set('session_id', value);
                            } else {
                                url.searchParams.delete('session_id');
                            }
                            window.location.href = url.toString();
                        },
                    });
                }
            };

            renderRecords(JSON.parse(root.dataset.initialRecords || '[]'));
            openButton.addEventListener('click', openCamera);
            stopButton.addEventListener('click', () => stopCamera().then(() => setMessage('Camera stopped.', 'neutral')));

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootSelect);
            } else {
                bootSelect();
            }
        })();
    </script>
</x-layouts.admin>
