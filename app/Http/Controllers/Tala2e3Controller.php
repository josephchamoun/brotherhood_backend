<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class Tala2e3Controller extends Controller
{
function index() {
    $users = User::whereHas('sections', function ($query) {
        $query->where('sections.id', 2); 
    })
    ->with(['sections' => function ($query) {
        $query->where('sections.id', 2); // only Tala2e3 section
    }])
    ->get();

    return response()->json($users);
}

    public function assignRole(Request $request)
    {
        $authUser = auth()->user();
        // 🔒 Only global admins can assign role
        if (!$authUser->is_global_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // ✅ STRICT validation
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'role_id'    => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // ✅ If already assigned → update pivot
        if ($user->sections()->where('section_id', $validated['section_id'])->exists()) {
            $user->sections()->updateExistingPivot(
                $validated['section_id'],
                ['role_id' => $validated['role_id']]
            );
        } else {
            // ✅ If not assigned → attach
            $user->sections()->attach(
                $validated['section_id'],
                ['role_id' => $validated['role_id']]
            );
        }

        return response()->json([
            'message' => 'Role assigned successfully'
        ], 200);
    }


public function removeRole(Request $request)
{
    $validated = $request->validate([
        'user_id'    => 'required|exists:users,id',
        'section_id' => 'required|exists:sections,id',
    ]);

    $user = User::findOrFail($validated['user_id']);
    $user->sections()->detach($validated['section_id']);

    return response()->json([
        'message' => 'Role removed successfully'
    ]);
}
}
