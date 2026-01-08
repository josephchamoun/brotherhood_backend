<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;



class ChabibaController extends Controller
{

function index() {
    $users = User::whereHas('sections', function ($query) {
        $query->where('sections.id', 1); 
    })
    ->with(['sections' => function ($query) {
        $query->where('sections.id', 1); // only Chabiba section
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

    // ✅ DO NOT detach
    // ✅ Set role_id = 10 (Normal user)
    $user->sections()->updateExistingPivot(
        $validated['section_id'],
        ['role_id' => 10]
    );

    return response()->json([
        'message' => 'Role removed, user is now normal member'
    ]);
}



}
