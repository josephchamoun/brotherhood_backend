<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Meeting;
use App\Models\User;

class MeetingController extends Controller
{
public function store(Request $request)
{
    $user = auth()->user();
    if (!$user) abort(401, 'Unauthenticated');

    // Map of section IDs → allowed President role names
    $sectionPresidents = [
        1 => 'Chabiba President',
        2 => 'Tala2e3 President',
        3 => 'Forsan President',
    ];

    $allowedRoles = ['Ne2b al Ra2is', 'wakil tanchi2a'];

    $sectionId = $request->section_id;

    // Check if user is global/super admin
    $isGlobalOrSuperAdmin = $user->is_global_admin || $user->is_super_admin;

    // Check if user has allowed role in this section
    $hasSectionRole = $user->sectionRoles()
        ->where('section_id', $sectionId)
        ->whereNull('end_date')
        ->whereHas('role', function ($q) use ($sectionId, $allowedRoles, $sectionPresidents) {
            $q->whereIn('name', array_merge($allowedRoles, [$sectionPresidents[$sectionId] ?? '']));
        })
        ->exists();

    if (!$isGlobalOrSuperAdmin && !$hasSectionRole) {
        abort(403, 'You are not allowed to add meetings for this section');
    }

    $data = $request->validate([
        'section_id' => 'required|exists:sections,id',
        'drive_link' => 'required|url',
        'title' => 'nullable|string|max:255',
    ]);

    $meeting = Meeting::updateOrCreate(
        ['section_id' => $sectionId],
        [
            'drive_link' => $data['drive_link'],
            'title' => $data['title'] ?? null,
            'created_by' => $user->id,
        ]
    );

    return response()->json(['success' => true, 'meeting' => $meeting], 201);
}


public function index()
{
    return Section::with(['meetings' => function ($q) {
        $q->latest();
    }])->get();
}


}
