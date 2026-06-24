<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class FoundationController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.core.foundation.dashboard');
    }

    public function events(): View
    {
        return view('admin.core.foundation.events.index');
    }

    public function eventCreate(): View
    {
        return view('admin.core.foundation.events.create');
    }

    public function eventShow(?Event $event = null): View
    {
        return view('admin.core.foundation.events.show', compact('event'));
    }

    public function eventEdit(?Event $event = null): View
    {
        return view('admin.core.foundation.events.edit', compact('event'));
    }

    public function microsite(?Event $event = null): View
    {
        return view('admin.core.foundation.events.microsite', compact('event'));
    }

    public function attendees(): View
    {
        return view('admin.core.foundation.attendees');
    }

    public function emails(): View
    {
        return view('admin.core.foundation.emails');
    }
}
