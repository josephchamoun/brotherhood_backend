<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }



    public function index()
{
    $events = Event::with('creator', 'sections')->get(); // eager load relations
    return response()->json($events);
}

public function destroy($id)
{
    $event = Event::findOrFail($id);

    // Only High Admin or President of event sections can delete
    if (!$this->isHighAdmin() && !$this->canEditDetails($event)) {
        return abort(403, 'Not authorized to delete this event');
    }

    $event->delete();
    return response()->json(['success' => true]);
}


    /**
     * Create a new event
     */
    public function store(Request $request)
    {
        $sections = $request->sections; // array of section IDs
        $isShared = count($sections) === 3;

        // check permissions for each section
        foreach ($sections as $sectionId) {
            if (!$this->canCreateEvent($sectionId, $isShared)) {
                return abort(403, 'You are not allowed to create events for this section');
            }
        }

        // create event
        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'type' => $request->type,
            'notes' => $request->notes,
            'drive_link' => $request->drive_link,
            'created_by' => $this->user->id,
        ]);

        // attach sections
        $event->sections()->sync($sections);

        return response()->json(['success' => true, 'event' => $event]);
    }

    /**
     * Update event details (title, description, notes, drive_link)
     */
    public function updateDetails(Request $request, $id)
    {
        $event = Event::with('sections')->findOrFail($id);

        if (!$this->canEditDetails($event)) {
            return abort(403, 'You cannot edit details for this event');
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'type' => $request->type,
            'notes' => $request->notes,
            'drive_link' => $request->drive_link,
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    /**
     * Update event financials (total_spent, total_revenue)
     */
    public function updateFinancials(Request $request, $id)
    {
        $event = Event::with('sections')->findOrFail($id);

        if (!$this->canEditFinancials($event)) {
            return abort(403, 'You cannot edit financials for this event');
        }

        $event->update([
            'total_spent' => $request->total_spent,
            'total_revenue' => $request->total_revenue,
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    // ==========================
    // PRIVATE HELPER FUNCTIONS
    // ==========================

    private function isHighAdmin()
    {
        return $this->user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn($q) => $q->where('name', 'High Admin'))
            ->exists();
    }

    private function hasActiveRole($roleName, $sectionId)
    {
        return $this->user->sectionRoles()
            ->whereNull('end_date')
            ->where('section_id', $sectionId)
            ->whereHas('role', fn($q) => $q->where('name', $roleName))
            ->exists();
    }

    private function isPresidentOf($sectionId)
    {
        $map = [
            1 => 'Chabiba President',
            2 => 'Tala2e3 President',
            3 => 'Forsan President',
        ];

        return $this->hasActiveRole($map[$sectionId], $sectionId)
            || $this->hasActiveRole('Ne2b al Ra2is', $sectionId);
    }

private function isSharedEvent(Event $event)
{
    return $event->sections()->count() === 3;
}


    private function canCreateEvent($sectionId, $isShared)
{
    if ($this->isHighAdmin()) return true;

    if ($isShared) {
        return
            $this->hasActiveRole('President', 1) ||
            $this->hasActiveRole('Ne2b al Ra2is', 1) ||
            $this->hasActiveRole('Amin Ser', 1) ||
            $this->hasActiveRole('Amin Sandou2', 1);
    }

    if ($sectionId == 1) {
        return
            $this->hasActiveRole('President', 1) ||
            $this->hasActiveRole('Ne2b al Ra2is', 1) ||
            $this->hasActiveRole('Amin Ser', 1) ||
            $this->hasActiveRole('Amin Sandou2', 1);
    }

    return $this->hasActiveRole('President', $sectionId) ||
           $this->hasActiveRole('Ne2b al Ra2is', $sectionId);
}


private function canEditDetails(Event $event)
{
    if ($this->isHighAdmin()) return true;

    // Shared events → only Chabiba leadership
    if ($this->isSharedEvent($event)) {
        return
            $this->hasActiveRole('President', 1) ||
            $this->hasActiveRole('Ne2b al Ra2is', 1) ||
            $this->hasActiveRole('Amin Ser', 1);
    }

    foreach ($event->sections as $section) {
        $sid = $section->id;

        // Presidents & Ne2b always allowed
        if (
            $this->hasActiveRole('President', $sid) ||
            $this->hasActiveRole('Ne2b al Ra2is', $sid)
        ) {
            return true;
        }

        // Amin Ser can edit details only in his own section
        if ($this->hasActiveRole('Amin Ser', $sid)) {
            return true;
        }
    }

    return false;
}


private function canEditFinancials(Event $event)
{
    if ($this->isHighAdmin()) return true;

    // Shared events → only Chabiba leadership
    if ($this->isSharedEvent($event)) {
        return
            $this->hasActiveRole('President', 1) ||
            $this->hasActiveRole('Ne2b al Ra2is', 1) ||
            $this->hasActiveRole('Amin Sandou2', 1);
    }

    foreach ($event->sections as $section) {
        $sid = $section->id;

        // Presidents & Ne2b always allowed
        if (
            $this->hasActiveRole('President', $sid) ||
            $this->hasActiveRole('Ne2b al Ra2is', $sid)
        ) {
            return true;
        }

        // Amin Sandou2 can edit financials only in his own section
        if ($this->hasActiveRole('Amin Sandou2', $sid)) {
            return true;
        }
    }

    return false;
}


}
