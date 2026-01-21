<?php

namespace App\Http\Controllers;

use App\Models\DriveAccount;
use Illuminate\Http\Request;

class DriveAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DriveAccount::all();
    }

    /**
     * Store a newly created drive account.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Allowed roles
        $allowedRoles = ['Amin Ser'];

        $isAllowed = $user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn ($q) =>
                $q->whereIn('name', $allowedRoles)
            )
            ->exists() || $user->is_global_admin;

        if (!$isAllowed) {
            abort(403, 'You are not allowed to add drive accounts');
        }

        $data = $request->validate([
            'email' => 'required|email|unique:drive_accounts,email',
            'title' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $driveAccount = DriveAccount::create($data);

        return response()->json($driveAccount, 201);
    }

    /**
     * Display the specified resource (with plain password).
     */
    public function show(string $id)
    {
        $account = DriveAccount::findOrFail($id);

        return response()->json([
            'id' => $account->id,
            'email' => $account->email,
            'title' => $account->title,
            'password' => $account->plain_password,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,
        ]);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Allowed roles
        $allowedRoles = ['Amin Ser'];

        $isAllowed = $user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn ($q) =>
                $q->whereIn('name', $allowedRoles)
            )
            ->exists() || $user->is_global_admin;

        if (!$isAllowed) {
            abort(403, 'You are not allowed to update drive accounts');
        }

        $account = DriveAccount::findOrFail($id);

        $data = $request->validate([
            'email' => 'sometimes|email|unique:drive_accounts,email,' . $account->id,
            'title' => 'sometimes|string|max:255',
            'password' => 'sometimes|string',
        ]);

        $account->update($data);

        return response()->json($account);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Allowed roles
        $allowedRoles = ['Amin Ser'];

        $isAllowed = $user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn ($q) =>
                $q->whereIn('name', $allowedRoles)
            )
            ->exists() || $user->is_global_admin;

        if (!$isAllowed) {
            abort(403, 'You are not allowed to delete drive accounts');
        }

        $account = DriveAccount::findOrFail($id);
        $account->delete();

        return response()->json([
            'message' => 'Drive account deleted successfully'
        ], 200);
    }
}
