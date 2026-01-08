<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SectionUserRole;
use Illuminate\Support\Facades\Cache;



class ChabibaController extends Controller
{

public function index(Request $request)
{
    $date = $request->query('date', now()->toDateString());

    $users = User::whereHas('sections', function ($query) use ($date) {
        $query->where('sections.id', 1)
              ->where('start_date', '<=', $date)
              ->where(function ($q) use ($date) {
                  $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
              });
    })
    ->with(['sections' => function ($query) use ($date) {
        $query->where('sections.id', 1)
              ->where('start_date', '<=', $date)
              ->where(function ($q) use ($date) {
                  $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
              });
    }])
    ->get();

    return response()->json($users);
}


public function assignRole(Request $request)
{
    $authUser = auth()->user();

    // 🔒 Only global admins can assign roles
    if (!$authUser->is_global_admin) {
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

    // 2️⃣ Close any existing active role for this section
    SectionUserRole::where('user_id', $userId)
        ->where('section_id', $sectionId)
        ->whereNull('end_date')
        ->update(['end_date' => $today]);

    // 3️⃣ Create a new role assignment (new pivot row)
    SectionUserRole::create([
        'user_id'    => $userId,
        'section_id' => $sectionId,
        'role_id'    => $roleId,
        'start_date' => $today,
        'end_date'   => null,
    ]);

    // 4️⃣ Clear cache if you use caching
    Cache::forget('users.index');

    return response()->json([
        'message' => 'Role assigned successfully'
    ], 200);
}


public function removeRole(Request $request)
{
    $authUser = auth()->user();

    if (!$authUser || !$authUser->is_global_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'user_id'    => 'required|exists:users,id',
        'section_id' => 'required|exists:sections,id',
    ]);

    $user = User::findOrFail($validated['user_id']);
    $sectionId = $validated['section_id'];
    $today = now()->toDateString();

    // Get the **active role** for this section
    $activePivot = $user->sections()
        ->wherePivot('section_id', $sectionId)
        ->wherePivotNull('end_date') // ONLY active roles
        ->first()?->pivot;

    if (!$activePivot) {
        return response()->json([
            'error' => 'No active role to remove for this user in this section'
        ], 400);
    }

    // ✅ End the active role
    $user->sections()->updateExistingPivot(
        $sectionId,
        ['end_date' => $today]
    );

    // ✅ Start a new Normal User role
    $user->sections()->attach(
        $sectionId,
        [
            'role_id'    => 10, // Normal User
            'start_date' => $today,
            'end_date'   => null,
        ]
    );

    return response()->json([
        'message' => 'Role removed. User is now a normal member.'
    ]);
}





}
