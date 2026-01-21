<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Shop::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $user = auth()->user();
    if (!$user) abort(401, 'Unauthenticated');

    // Allowed roles
    $allowedRoles = [
        'Chabiba President',
        'Forsan President',
        'Tala2e3 President',
        'Ne2b al Ra2is',
        'Amin sandou2',
    ];

    $isAllowed = $user->sectionRoles()
        ->whereNull('end_date')
        ->whereHas('role', fn ($q) =>
            $q->whereIn('name', $allowedRoles)
        )
        ->exists() || $user->is_global_admin;

    
    if (!$isAllowed) {
        abort(403, 'You are not allowed to add shops');
    }

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'place' => 'required|string|max:255',
        'phone_number' => 'nullable|string|max:50',
        'description' => 'nullable|string',
    ]);

    $shop = Shop::create([
        ...$data,
        'created_by' => $user->id,
    ]);

    return response()->json($shop, 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy(string $id)
{
    $user = auth()->user();
    if (!$user) {
        abort(401, 'Unauthenticated');
    }

    // Allowed roles
    $allowedRoles = [
        'Chabiba President',
        'Forsan President',
        'Tala2e3 President',
        'Ne2b al Ra2is',
        'Amin sandou2',
    ];

    $isAllowed = $user->sectionRoles()
        ->whereNull('end_date')
        ->whereHas('role', fn ($q) =>
            $q->whereIn('name', $allowedRoles)
        )
        ->exists() || $user->is_global_admin;

    if (!$isAllowed) {
        abort(403, 'You are not allowed to delete shops');
    }

    $shop = Shop::findOrFail($id);

    $shop->delete();

    return response()->json([
        'message' => 'Shop deleted successfully'
    ], 200);
}

}
