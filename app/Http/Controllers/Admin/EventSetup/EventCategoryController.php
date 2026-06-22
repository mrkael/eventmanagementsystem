<?php

namespace App\Http\Controllers\Admin\EventSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventCategoryRequest;
use App\Models\EventCategory;
use App\Services\AuditLogger;
use App\Services\EventSetup\EventMasterUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = EventCategory::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$request->search}%")->orWhere('slug', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.event-setup.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.event-setup.categories.create');
    }

    public function store(EventCategoryRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $this->payload($request);
        $category = EventCategory::create($data);

        $auditLogger->record('event_categories.create', "Created event category {$category->name}.", $category, [], $category->toArray());

        return redirect()->route('admin.event-categories.index')->with('status', 'Event category created successfully.');
    }

    public function show(EventCategory $eventCategory): View
    {
        return view('admin.event-setup.categories.show', ['category' => $eventCategory]);
    }

    public function edit(EventCategory $eventCategory): View
    {
        return view('admin.event-setup.categories.edit', ['category' => $eventCategory]);
    }

    public function update(EventCategoryRequest $request, EventCategory $eventCategory, AuditLogger $auditLogger): RedirectResponse
    {
        $oldValues = $eventCategory->toArray();
        $eventCategory->update($this->payload($request));

        $auditLogger->record('event_categories.update', "Updated event category {$eventCategory->name}.", $eventCategory, $oldValues, $eventCategory->fresh()->toArray());

        return redirect()->route('admin.event-categories.index')->with('status', 'Event category updated successfully.');
    }

    public function destroy(EventCategory $eventCategory, EventMasterUsageService $usageService, AuditLogger $auditLogger): RedirectResponse
    {
        if ($usageService->isUsed($eventCategory)) {
            return back()->withErrors(['category' => 'This category is already used by an event and cannot be deleted.']);
        }

        $oldValues = $eventCategory->toArray();
        $eventCategory->delete();

        $auditLogger->record('event_categories.delete', "Deleted event category {$eventCategory->name}.", $eventCategory, $oldValues);

        return redirect()->route('admin.event-categories.index')->with('status', 'Event category deleted successfully.');
    }

    private function payload(EventCategoryRequest $request): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
