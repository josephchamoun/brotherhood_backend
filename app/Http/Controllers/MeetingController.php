<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Meeting;

class MeetingController extends Controller
{
    private function canAddMeeting(User $user, int $sectionId): bool
{
    if ($this->isHighAdmin($user)) return true;

    return
        $this->hasActiveRole($user, 'President', $sectionId) ||
        $this->hasActiveRole($user, 'Ne2b al Ra2is', $sectionId) ||
        $this->hasActiveRole($user, 'Wakil Tanchi2a', $sectionId);
}

public function store(Request $request)
{
    $user = auth()->user();
    abort_if(!$user, 401);

    $request->validate([
        'section_id' => 'required|exists:sections,id',
        'title' => 'nullable|string|max:255',
        'drive_link' => 'required|url',
    ]);

    if (!$this->canAddMeeting($user, $request->section_id)) {
        abort(403, 'You cannot add meetings for this section');
    }

    $meeting = Meeting::create([
        'section_id' => $request->section_id,
        'title' => $request->title,
        'drive_link' => $request->drive_link,
        'created_by' => $user->id,
    ]);

    return response()->json([
        'success' => true,
        'meeting' => $meeting,
    ]);
}
public function index()
{
    return Section::with(['meetings' => function ($q) {
        $q->latest();
    }])->get();
}


}
