<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Contact::all();
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) abort(401, 'Unauthenticated');

        // Allowed roles
        $allowedRoles = [
            'Wakil Risele',
            'Chabiba President',
            'Forsan President',
            'Tala2e3 President',
            'Ne2b al Ra2is',
        ];

        $isAllowed = $user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn ($q) =>
                $q->whereIn('name', $allowedRoles)
            )
            ->exists() || $user->is_global_admin;

        if (!$isAllowed) {
            abort(403, 'You are not allowed to add contacts');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'town_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $contact = Contact::create([
            ...$data,
            
        ]);

        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
        //
    }
}
