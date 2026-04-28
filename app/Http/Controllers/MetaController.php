<?php
// app/Http/Controllers/MetaController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MetaController extends Controller
{
    /**
     * Returns last_updated timestamps for all major resources.
     * Frontend uses this to decide whether to re-fetch full data.
     * This endpoint is intentionally lightweight — no data, only timestamps.
     */
    public function index()
    {
        // Cache the meta for 30 seconds to prevent DB hammering
        // on repeated page loads / tab switching
        $meta = Cache::remember('meta.last_updated', 30, function () {
            return [
                'events'   => $this->getLastUpdated('events', 'event_section'),
                'users'    => $this->getLastUpdated('users', 'section_user_roles'),
                'sections' => DB::table('sections')->max('updated_at'),
                'roles'    => DB::table('roles')->max('updated_at'),
            ];
        });

        return response()->json($meta);
    }

    /**
     * Get the latest timestamp across a main table AND its pivot/related table.
     * This catches cases where only a relation changed (e.g. section added to event).
     */
    private function getLastUpdated(string $mainTable, string $pivotTable): ?string
    {
        $mainMax  = DB::table($mainTable)->max('updated_at');
        $pivotMax = DB::table($pivotTable)->max('updated_at');

        // Return whichever is more recent
        return collect([$mainMax, $pivotMax])->filter()->max();
    }
}