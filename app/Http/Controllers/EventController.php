<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Moneybox;
use App\Models\MoneyTransaction;

class EventController extends Controller
{




public function index(Request $request)
{
    // Calculate the true last_updated for events
    // (includes pivot table so section changes are detected)
    $eventsMax  = DB::table('events')->max('updated_at');
    $pivotMax   = DB::table('event_section')->max('updated_at');
    $lastUpdated = collect([$eventsMax, $pivotMax])->filter()->max();

    // If frontend sends If-Modified-Since and data hasn't changed → 304
    if ($request->hasHeader('If-Modified-Since')) {
        $clientTimestamp = $request->header('If-Modified-Since');
        if ($clientTimestamp === $lastUpdated) {
            return response()->noContent(304); // 304 Not Modified
        }
    }

    $events = Event::with('creator', 'sections')->get();

    return response()->json($events)
        ->header('Last-Modified', $lastUpdated)
        ->header('X-Last-Updated', $lastUpdated); // also expose for JS clients
}

public function destroy($id)
{
    $user = auth()->user();
    if (!$user) {
        abort(401, 'Unauthenticated');
    }

    $event = Event::findOrFail($id);

    if (
        !$this->isHighAdmin($user) &&
        !$this->hasActiveRole($user, 'Amin Ser', 1)
    ) {
        abort(403, 'Not authorized to delete this event');
    }

    // ✅ calculate original impact of event
    $net = ($event->total_revenue ?? 0) - ($event->total_spent ?? 0);

    $money = Moneybox::where('section_id', 1)->first();

    if ($money && $net != 0) {

        // ✅ reverse the effect properly
        $money->decrement('amount', $net);

        // OR clearer:
        // $money->increment('amount', -$net);

        // ✅ log reversal
        MoneyTransaction::create([
            'moneybox_id' => $money->id,
            'amount' => -$net,
            'type' => $net >= 0 ? 'expense' : 'income',
            'source' => 'event_delete',
            'event_id' => $event->id,
            'user_id' => auth()->id(),
        ]);
    }

    $event->delete();

    Cache::forget('meta.last_updated');

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
    // Detect roles
    // ---------------------------
    $isHighAdmin = $this->isHighAdmin($user);

    // Chabiba leaders in section 1 = full freedom
    $isChabibaLeaderSection1 = $user->sectionRoles()
        ->whereNull('end_date')
        ->where('section_id', 1)
        ->whereHas('role', fn ($q) => $q->whereIn('name', [
            'Chabiba President',
            'Ne2b al Ra2is',
            'Amin Ser',
        ]))
        ->exists();

    // Any leader in any section (for sections 2 & 3 case)
    $leaderSectionId = $user->sectionRoles()
        ->whereNull('end_date')
        ->whereHas('role', fn ($q) => $q->whereIn('name', [
            'Tala2e3 President',
            'Forsan President',
            'Ne2b al Ra2is',
            'Amin Ser',
        ]))
        ->value('section_id'); // returns 2 or 3 (or null)

    // ---------------------------
    // Determine sections
    // ---------------------------
    if ($isHighAdmin || $isChabibaLeaderSection1) {

        // Full freedom
        $sections = $request->input('sections', [1, 2, 3]);

        // Handle "Shared" = 4
        if (in_array(4, $sections)) {
            $sections = [1, 2, 3];
        }

    } elseif ($leaderSectionId) {

        // Leaders of section 2 or 3: ONLY their own section
        $sections = [$leaderSectionId];

    } else {

        // Not allowed
        abort(403, 'You are not allowed to create events');
    }

    // ---------------------------
    // Amin Ser financial lock
    // ---------------------------
    $isAnyAminSer = $user->sectionRoles()
        ->whereNull('end_date')
        ->whereHas('role', fn ($q) => $q->where('name', 'Amin Ser'))
        ->exists();

    // ---------------------------
    // Create event
    // ---------------------------
    $event = Event::create([
        'title' => $request->title,
        'type' => $request->type,
        'description' => $request->description ?? '',
        'event_date' => $request->event_date ?? now(),

        // 🔒 Any Amin Ser cannot set financials
        'total_spent' => $isAnyAminSer ? 0 : ($request->total_spent ?? 0),
        'total_revenue' => $isAnyAminSer ? 0 : ($request->total_revenue ?? 0),

        'notes' => $request->notes ?? '',
        'drive_link' => $request->drive_link ?? '',
        'photo_link' => $request->photo_link ?? '',

        'created_by' => $user->id,
    ]);

    // ---------------------------
    // Attach sections
    // ---------------------------
    $event->sections()->sync($sections);
    Cache::forget('meta.last_updated');
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

        // Special case: Wakil Tanchi2a → description ONLY
        if ($this->isWakilTanchi2a($user, $event)) {
            $request->validate([
                'description' => 'nullable|string',
            ]);

            $event->update([
                'description' => $request->description ?? '',
            ]);

            return response()->json([
                'success' => true,
                'event' => $event,
                'role' => 'wakil wanchi2a',
            ]);
        }

        // Normal permission check for other roles
        if (!$this->canEditDetails($user, $event)) {
            abort(403, 'You cannot edit details for this event');
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'type' => $request->type,
            'notes' => $request->notes,
            'drive_link' => $request->drive_link,
            'photo_link' => $request->photo_link,
        ]);
        Cache::forget('meta.last_updated');

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
        abort(403, 'You cannot edit financials for this event');
    }

    // ✅ Validate (important)
    $validated = $request->validate([
        'total_spent' => 'required|numeric|min:0',
        'total_revenue' => 'required|numeric|min:0',
    ]);

    // ✅ Old net
    $oldNet = ($event->total_revenue ?? 0) - ($event->total_spent ?? 0);

    // ✅ Update event
    $event->update($validated);

    // ✅ New net
    $newNet = $validated['total_revenue'] - $validated['total_spent'];

    // ✅ Difference only
    $diff = $newNet - $oldNet;

    $money = Moneybox::where('section_id', 1)->first();

    if ($money && $diff != 0) {
        // safer than manual update
        $money->increment('amount', $diff);

        // ✅ Log ONLY the change
        MoneyTransaction::create([
            'moneybox_id' => $money->id,
            'amount' => $diff,
            'type' => $diff >= 0 ? 'income' : 'expense',
            'source' => 'event_update',
            'event_id' => $event->id,
            'user_id' => auth()->id(),
        ]);
    }

    Cache::forget('meta.last_updated');

    return response()->json([
        'success' => true,
        'event' => $event
    ]);
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

    // ✅ NEW: Amin Ser of Chabiba (section 1) can edit ALL events
    if ($this->hasActiveRole($user, 'Amin Ser', 1)) {
        return true;
    }

    $presidentRoles = [
        'Chabiba President',
        'Forsan President',
        'Tala2e3 President',
    ];

    // Shared events → only Chabiba leadership (but Amin Ser 1 already passed above)
    if ($this->isSharedEvent($event)) {
        return
            $this->hasActiveRole($user, 'Chabiba President', 1) ||
            $this->hasActiveRole($user, 'Ne2b al Ra2is', 1);
    }

    foreach ($event->sections as $section) {
        $sid = $section->id;

        // Presidents always allowed
        foreach ($presidentRoles as $role) {
            if ($this->hasActiveRole($user, $role, $sid)) {
                return true;
            }
        }

        // Ne2b always allowed
        if ($this->hasActiveRole($user, 'Ne2b al Ra2is', $sid)) {
            return true;
        }

        // Amin Ser allowed ONLY in his own section (except section 1 which is global, handled above)
        if ($this->hasActiveRole($user, 'Amin Ser', $sid)) {
            return true;
        }
    }

    return false;
}



    private function canEditFinancials($user, Event $event)
    {
        if ($this->isHighAdmin($user)) return true;

        $presidentRoles = [
            'Chabiba President',
            'Forsan President',
            'Tala2e3 President',
        ];

        // Shared events → only Chabiba leadership
        if ($this->isSharedEvent($event)) {
            return
                $this->hasActiveRole($user, 'Chabiba President', 1) ||
                $this->hasActiveRole($user, 'Ne2b al Ra2is', 1) ||
                $this->hasActiveRole($user, 'Amin Sandou2', 1);
        }

        foreach ($event->sections as $section) {
            $sid = $section->id;

            // Presidents & Ne2b always allowed
            foreach ($presidentRoles as $role) {
                if ($this->hasActiveRole($user, $role, $sid)) {
                    return true;
                }
            }

            if ($this->hasActiveRole($user, 'Ne2b al Ra2is', $sid)) {
                return true;
            }

            // Amin Sandou2 allowed only in his section
            if ($this->hasActiveRole($user, 'Amin Sandou2', $sid)) {
                return true;
            }
        }

        return false;
    }


    private function isWakilTanchi2a($user, Event $event)
{
    if ($this->isHighAdmin($user)) {
        return false; // High admins already have full access
    }

    // Shared events → Wakil Tanchi2a NOT allowed
    if ($this->isSharedEvent($event)) {
        return false;
    }

    foreach ($event->sections as $section) {
        if ($this->hasActiveRole($user, 'wakil tanchi2a', $section->id)) {
            return true;
        }
    }

    return false;
}

}
