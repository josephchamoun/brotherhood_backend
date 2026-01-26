<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Moneybox;
use App\Models\User;

class MoneyboxController extends Controller
{
    /**
     * List all moneyboxes (optional)
     */
    public function index()
    {
        return Moneybox::with('section')->get();
    }

    /**
     * Update the money of a moneybox
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) abort(401, 'Unauthenticated');

        $moneybox = Moneybox::findOrFail($id);

        $sectionId = $moneybox->section_id;

        // Roles allowed per section
        $sectionRoles = [
            1 => ['Chabiba President', 'Ne2b al Ra2is', 'Amin Sandou2'],
            2 => ['Talaee President', 'Ne2b al Ra2is', 'Amin Sandou2'],
            3 => ['Forsan President', 'Ne2b al Ra2is', 'Amin Sandou2'],
        ];

        // Check if user is global or super admin
        $isGlobalOrSuperAdmin = $user->is_global_admin || $user->is_super_admin;

        // Check if user has one of the allowed roles in this section
        $hasSectionRole = $user->sectionRoles()
            ->where('section_id', $sectionId)
            ->whereNull('end_date')
            ->whereHas('role', function ($q) use ($sectionRoles, $sectionId) {
                $q->whereIn('name', $sectionRoles[$sectionId] ?? []);
            })
            ->exists();

        if (!$isGlobalOrSuperAdmin && !$hasSectionRole) {
            abort(403, 'You are not allowed to edit this moneybox');
        }

        // Validate request
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Update money only
        $moneybox->update([
            'amount' => $data['amount'],
        ]);

        return response()->json(['success' => true, 'moneybox' => $moneybox]);
    }
}