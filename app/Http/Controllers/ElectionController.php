<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;

class ElectionController extends Controller
{
    // List all elections with section info
    public function index()
    {
        return Election::with('section')->get();
    }

    // Add new election
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) abort(401, 'Unauthenticated');

        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'election_date' => 'required|date',
        ]);

        // Permissions
        $allowedRoles = ['Admin', 'Super Admin']; // global admins
        $sectionRoles = ['President', 'Ne2b al Ra2is'];

        $isGlobalAdmin = $user->is_global_admin || $user->is_super_admin;
        $hasSectionRole = $user->sectionRoles()
            ->where('section_id', $data['section_id'])
            ->whereNull('end_date')
            ->whereHas('role', function($q) use ($sectionRoles) {
                $q->whereIn('name', $sectionRoles);
            })
            ->exists();

        if (!$isGlobalAdmin && !$hasSectionRole) {
            abort(403, 'You are not allowed to add elections for this section');
        }

        $election = Election::create([
            'section_id' => $data['section_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'election_date' => $data['election_date'],
            'created_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'election' => $election], 201);
    }

    // Delete election
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) abort(401, 'Unauthenticated');

        $election = Election::findOrFail($id);

        $isGlobalAdmin = $user->is_global_admin || $user->is_super_admin;
        $hasSectionRole = $user->sectionRoles()
            ->where('section_id', $election->section_id)
            ->whereNull('end_date')
            ->whereHas('role', function($q) {
                $q->whereIn('name', ['President', 'Ne2b al Ra2is']);
            })
            ->exists();

        if (!$isGlobalAdmin && !$hasSectionRole) {
            abort(403, 'You are not allowed to delete this election');
        }

        $election->delete();

        return response()->json(['success' => true]);
    }
}