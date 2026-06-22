<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = Contact::with('groups')
            ->when($request->filled('group_id'), fn ($query) => $query->whereHas('groups', fn ($groups) => $groups->whereKey($request->group_id)))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('mobile_number', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.core.contacts.index', [
            'contacts' => $contacts,
            'groups' => ContactGroup::withCount('contacts')->orderBy('name')->get(),
        ]);
    }

    public function storeGroup(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $group = ContactGroup::create($request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:1000']]) + ['created_by' => $request->user()->id]);
        $auditLogger->record('eevee.contacts.group.create', "Created contact group {$group->name}.", $group);

        return back()->with('status', 'Contact group saved.');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:80'],
            'organization' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'secretary_name' => ['nullable', 'string', 'max:255'],
            'secretary_email' => ['nullable', 'email:rfc', 'max:255'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:contact_groups,id'],
        ]);
        $contact = Contact::create($data + ['created_by' => $request->user()->id]);
        $contact->groups()->sync($request->input('group_ids', []));
        $auditLogger->record('eevee.contacts.create', "Created contact {$contact->first_name}.", $contact);

        return back()->with('status', 'Contact saved.');
    }

    public function import(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'group_id' => ['nullable', 'exists:contact_groups,id'],
        ]);
        $handle = fopen($data['file']->getRealPath(), 'r');
        $headers = array_map('trim', fgetcsv($handle) ?: []);
        $created = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $payload = array_combine($headers, $row) ?: [];
            $contact = Contact::create([
                'created_by' => $request->user()->id,
                'first_name' => $payload['first_name'] ?? $payload['name'] ?? 'Contact',
                'last_name' => $payload['last_name'] ?? null,
                'email' => $payload['email'] ?? null,
                'mobile_number' => $payload['mobile_number'] ?? $payload['phone'] ?? null,
                'organization' => $payload['organization'] ?? null,
                'designation' => $payload['designation'] ?? null,
                'department' => $payload['department'] ?? null,
                'email_status' => filter_var($payload['email'] ?? null, FILTER_VALIDATE_EMAIL) ? 'valid' : 'unverified',
            ]);
            if ($request->filled('group_id')) {
                $contact->groups()->sync([$request->group_id]);
            }
            $created++;
        }
        fclose($handle);
        $auditLogger->record('eevee.contacts.import', "Imported {$created} contacts.");

        return back()->with('status', "Imported {$created} contacts.");
    }

    public function export()
    {
        $csv = "First Name,Last Name,Email,Mobile,Organization,Designation,Department,Email Status\n";
        foreach (Contact::latest()->get() as $contact) {
            $csv .= collect([$contact->first_name, $contact->last_name, $contact->email, $contact->mobile_number, $contact->organization, $contact->designation, $contact->department, $contact->email_status])
                ->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')
                ->implode(',')."\n";
        }

        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="contacts.csv"']);
    }
}
