<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SectionUserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;



class ChabibaController extends Controller
{

public function index()
{
    $users = User::whereHas('chabibaRoles')
        ->with('chabibaRoles')
        ->get();

    $activeUsers = [];
    $inactiveUsers = [];

    foreach ($users as $user) {
        $hasActiveRole = $user->chabibaRoles
            ->whereNull('end_date')
            ->count() > 0;

        if ($hasActiveRole) {
            $activeUsers[] = $user;
        } else {
            $inactiveUsers[] = $user;
        }
    }

    return response()->json([
        'active_users'   => $activeUsers,
        'inactive_users' => $inactiveUsers,
    ]);
}
private function canManageChabiba($user)
{
    if (!$user) return false;

    if ($user->is_global_admin) return true;

    // Check if the user has Tala2e3 President or Ne2b al Ra2is in section 2
    return $user->roles()->where('section_id', 1)
                 ->whereIn('role_id', [2, 9])
                 ->whereNull('end_date')
                 ->exists();
}




public function assignRole(Request $request)
{
    $authUser = auth()->user();
    if (!$this->canManageChabiba($authUser)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // ✅ Validate input strictly
    $validated = $request->validate([
        'user_id'    => 'required|exists:users,id',
        'section_id' => 'required|exists:sections,id',
        'role_id'    => 'required|exists:roles,id',
    ]);

    $userId    = $validated['user_id'];
    $sectionId = $validated['section_id'];
    $roleId    = $validated['role_id'];
    $today     = now()->toDateString();

    // 1️⃣ Check if the user already has this role active
    $alreadyActive = SectionUserRole::where('user_id', $userId)
        ->where('section_id', $sectionId)
        ->where('role_id', $roleId)
        ->whereNull('end_date')
        ->exists();

    if ($alreadyActive) {
        return response()->json([
            'message' => 'User already has this role in this section'
        ], 409);
    }
      //  Check if other users already has this role active
    $roleTaken = SectionUserRole::where('section_id', $sectionId)
        ->where('role_id', $roleId)
        ->whereNull('end_date') // only active roles
        ->exists(); // check if **any** user has it

    if ($roleTaken) {
        return response()->json([
            'message' => 'This role is already assigned to another user in this section'
        ], 409);
    }

    // 2️⃣ Close any existing active role for this section
    SectionUserRole::where('user_id', $userId)
        ->where('section_id', $sectionId)
        ->whereNull('end_date')
        ->update(['end_date' => $today]);

    // 3️⃣ Check if there's already a record for this role on today (same day reassignment)
    $existingToday = SectionUserRole::where('user_id', $userId)
        ->where('section_id', $sectionId)
        ->where('role_id', $roleId)
        ->where('start_date', $today)
        ->first();

    if ($existingToday) {
        // Reactivate the existing record from today
        $existingToday->update(['end_date' => null]);
    } else {
        // Create a new role assignment (new pivot row)
        SectionUserRole::create([
            'user_id'    => $userId,
            'section_id' => $sectionId,
            'role_id'    => $roleId,
            'start_date' => $today,
            'end_date'   => null,
        ]);
    }

    // 4️⃣ Clear cache if you use caching
    Cache::forget('users.index');

    return response()->json([
        'message' => 'Role assigned successfully'
    ], 200);
}


public function endRole(Request $request)
{
    $authUser = auth()->user();
    if (!$this->canManageChabiba($authUser)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'user_id'    => 'required|exists:users,id',
        'section_id' => 'required|exists:sections,id',
    ]);

    $today = now()->toDateString();

    DB::transaction(function () use ($validated, $today) {

        // 1️⃣ End current active role (NOT normal)
        $activeRole = SectionUserRole::where('user_id', $validated['user_id'])
            ->where('section_id', $validated['section_id'])
            ->whereNull('end_date')
            ->where('role_id', '!=', 10)
            ->first();

        if (!$activeRole) {
            throw new \Exception('No active non-normal role');
        }

        $activeRole->update([
            'end_date' => $today
        ]);

        // 2️⃣ Reactivate or create NORMAL role
        $existingNormalToday = SectionUserRole::where('user_id', $validated['user_id'])
            ->where('section_id', $validated['section_id'])
            ->where('role_id', 10)
            ->where('start_date', $today)
            ->first();

        if ($existingNormalToday) {
            $existingNormalToday->update(['end_date' => null]);
        } else {
            SectionUserRole::create([
                'user_id'    => $validated['user_id'],
                'section_id' => $validated['section_id'],
                'role_id'    => 10,
                'start_date' => $today,
                'end_date'   => null,
            ]);
        }
    });

    return response()->json([
        'message' => 'Role ended and user returned to normal'
    ]);
}



public function activateUser(Request $request)
{

    $authUser = auth()->user();
    if (!$this->canManageChabiba($authUser)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'section_id' => 'sometimes|exists:sections,id',
    ]);

    $sectionId = $request->section_id ?? 1;
    $today = now()->toDateString();

    try {
        DB::transaction(function () use ($request, $sectionId, $today) {

            // End any active roles in this section
            SectionUserRole::where('user_id', $request->user_id)
                ->where('section_id', $sectionId)
                ->whereNull('end_date')
                ->update([
                    'end_date' => $today,
                ]);

            // Check if there's already a role for today (same day reactivation)
            $existingToday = SectionUserRole::where('user_id', $request->user_id)
                ->where('section_id', $sectionId)
                ->where('role_id', 10)
                ->where('start_date', $today)
                ->first();

            if ($existingToday) {
                // Update the existing record to reactivate it
                $existingToday->update(['end_date' => null]);
            } else {
                // Create new role assignment only if it doesn't exist for today
                SectionUserRole::create([
                    'user_id'    => $request->user_id,
                    'section_id' => $sectionId,
                    'role_id'    => 10,
                    'start_date' => $today,
                    'end_date'   => null,
                ]);
            }
        });

        return response()->json([
            'message' => 'User activated successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error("Activate user error: ".$e->getMessage());
        return response()->json([
            'message' => 'Failed to activate user',
            'error' => $e->getMessage(),
        ], 500);
    }
}







public function inactivateUser(Request $request)
{
        $authUser = auth()->user();
    if (!$this->canManageChabiba($authUser)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    SectionUserRole::where('user_id', $request->user_id)
        ->where('section_id', 1)
        ->whereNull('end_date')
        ->update([
            'end_date' => now()->toDateString(),
        ]);

    return response()->json([
        'message' => 'User inactivated successfully'
    ]);
}




}
