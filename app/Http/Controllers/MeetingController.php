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

    $allowedRoles = [
        'President',
        'Ne2b al Ra2is',
        'wakil tanchi2a',
    ];

    $isAllowed = $user->sectionRoles()
        ->where('section_id', $request->section_id)
        ->whereNull('end_date')
        ->whereHas('role', fn ($q) =>
            $q->whereIn('name', $allowedRoles)
        )
        ->exists() || $user->is_global_admin || $user->is_super_admin;

    if (!$isAllowed) {
        abort(403, 'You are not allowed to add meetings for this section');
    }

    $data = $request->validate([
        'section_id' => 'required|exists:sections,id',
        'drive_link' => 'required|url',
        'title' => 'nullable|string|max:255',
    ]);

    $meeting = Meeting::updateOrCreate(
        ['section_id' => $request->section_id],
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
