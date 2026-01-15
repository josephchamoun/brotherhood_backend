<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('creator', 'sections')->get(); // eager load relations
        return response()->json($events);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $event = Event::findOrFail($id);

        // Only High Admin or President of event sections can delete
        if (!$this->isHighAdmin($user) && !$this->canEditDetails($user, $event)) {
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
    $user = auth()->user();
    if (!$user) {
        abort(401, 'Unauthenticated');
    }

    // ---------------------------
    // Determine sections automatically
    // ---------------------------
    $sections = [];

   if ($this->isHighAdmin($user)) {
    $sections = $request->input('sections', [1,2,3]); // default to all if nothing selected

    // handle "Shared" selection
    if (in_array(4, $sections)) {
        $sections = [1,2,3];
    }
}
 else {
        // Check if Chabiba leadership
        $isChabibaLeader =
            $this->hasActiveRole($user, 'Chabiba President', 1) ||
            $this->hasActiveRole($user, 'Ne2b al Ra2is', 1);

        $isSharedRequested = $request->boolean('shared_event');

        if ($isChabibaLeader) {
            if ($isSharedRequested) {
                $sections = [1, 2, 3]; // shared event
            } else {
                $sections = [1]; // chabiba only
            }
        } else {
            // Other sections presidents / na2b
            $sectionId = $user->sectionRoles()
                ->whereNull('end_date')
                ->whereHas('role', fn($q) => $q->whereIn('name', [
                    'Tala2e3 President',
                    'Forsan President',
                    'Ne2b al Ra2is',
                ]))
                ->value('section_id');

            if (!$sectionId) {
                abort(403, 'You are not allowed to create events');
            }

            $sections = [$sectionId];
        }
    }

    // ---------------------------
    // Create event
    // ---------------------------
    $event = Event::create([
        'title' => $request->title,
        'type' => $request->type,
        'description' => $request->description ?? '',
        'event_date' => $request->event_date ?? now(),
        'total_spent' => $request->total_spent ?? 0,
        'total_revenue' => $request->total_revenue ?? 0,
        'notes' => $request->notes ?? '',
        'drive_link' => $request->drive_link ?? '',
        'created_by' => $user->id,
    ]);

    // ---------------------------
    // Attach sections
    // ---------------------------
    $event->sections()->sync($sections);

    return response()->json([
        'success' => true,
        'event' => $event->load('sections'),
    ]);
}

    /**
     * Update event details (title, description, notes, drive_link)
     */
    public function updateDetails(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $event = Event::with('sections')->findOrFail($id);

        if (!$this->canEditDetails($user, $event)) {
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
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $event = Event::with('sections')->findOrFail($id);

        if (!$this->canEditFinancials($user, $event)) {
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

    private function isHighAdmin($user)
    {
        return $user->is_global_admin;
        
    }

private function hasActiveRole($user, $roleName, $sectionId)
{
    return $user->sectionRoles()
        ->whereNull('end_date')
        ->where('section_id', $sectionId)
        ->whereHas('role', fn($q) => $q->where('name', $roleName))
        ->exists();
}

    private function isPresidentOf($user, $sectionId)
    {
        $map = [
            1 => 'Chabiba President',
            2 => 'Tala2e3 President',
            3 => 'Forsan President',
        ];

        return $this->hasActiveRole($user, $map[$sectionId], $sectionId)
            || $this->hasActiveRole($user, 'Ne2b al Ra2is', $sectionId);
    }

    private function isSharedEvent(Event $event)
    {
        return $event->sections()->count() === 3;
    }

    private function canCreateEvent($user, $sectionId, $isShared)
    {
        if ($this->isHighAdmin($user)) return true;

        if ($isShared) {
            return
                $this->hasActiveRole($user, 'President', 1) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', 1);
        }

        if ($sectionId == 1) {
            return
                $this->hasActiveRole($user, 'President', 1) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', 1);
        }

        return $this->hasActiveRole($user, 'President', $sectionId) ||
               $this->hasActiveRole($user, 'Ne2b al Ra2is', $sectionId);
    }

    private function canEditDetails($user, Event $event)
    {
        if ($this->isHighAdmin($user)) return true;

        // Shared events → only Chabiba leadership
        if ($this->isSharedEvent($event)) {
            return
                $this->hasActiveRole($user, 'President', 1) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', 1) ||
                $this->hasActiveRole($user, 'Amin Ser', 1);
        }

        foreach ($event->sections as $section) {
            $sid = $section->id;

            // Presidents & Ne2b always allowed
            if (
                $this->hasActiveRole($user, 'President', $sid) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', $sid)
            ) {
                return true;
            }

            // Amin Ser can edit details only in his own section
            if ($this->hasActiveRole($user, 'Amin Ser', $sid)) {
                return true;
            }
        }

        return false;
    }

    private function canEditFinancials($user, Event $event)
    {
        if ($this->isHighAdmin($user)) return true;

        // Shared events → only Chabiba leadership
        if ($this->isSharedEvent($event)) {
            return
                $this->hasActiveRole($user, 'President', 1) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', 1) ||
                $this->hasActiveRole($user, 'Amin Sandou2', 1);
        }

        foreach ($event->sections as $section) {
            $sid = $section->id;

            // Presidents & Ne2b always allowed
            if (
                $this->hasActiveRole($user, 'President', $sid) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', $sid)
            ) {
                return true;
            }

            // Amin Sandou2 can edit financials only in his own section
            if ($this->hasActiveRole($user, 'Amin Sandou2', $sid)) {
                return true;
            }
        }

        return false;
    }
}
