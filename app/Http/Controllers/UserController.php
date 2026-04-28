<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Section;
use App\Models\Role;
use App\Models\Event;
use App\Models\SectionUserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;





class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */





public function index(Request $request)
{
    $usersMax    = DB::table('users')->max('updated_at');
    $rolesMax    = DB::table('section_user_roles')->max('updated_at');
    $lastUpdated = collect([$usersMax, $rolesMax])->filter()->max();

    if ($request->hasHeader('If-Modified-Since')) {
        if ($request->header('If-Modified-Since') === $lastUpdated) {
            return response()->noContent(304);
        }
    }

    $data = Cache::remember('users.index.' . $lastUpdated, 300, function () {
        return User::with([
            'sections:id,name',
            'creator:id,name',
            'chabibaRoles',   
            'tala2e3Roles',   
            'forsanRoles',    
        ])
            ->select('id', 'name', 'email', 'phone', 'date_of_birth', 'is_global_admin', 'is_super_admin', 'created_by')
            ->orderBy('name')
            ->get();
    });

    return response()->json($data)
        ->header('Last-Modified', $lastUpdated)
        ->header('X-Last-Updated', $lastUpdated);
}


public function store(Request $request)
{
    $authUser = auth()->user();

    if (!$authUser) {
        abort(401);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'date_of_birth' => 'nullable|date',
        'password' => 'required|string|min:8',
        'is_global_admin' => 'boolean'
    ]);

    // 🔒 Only SUPER ADMIN can create global admins
    if (
        ($validated['is_global_admin'] ?? false) &&
        !$authUser->isSuperAdmin()
    ) {
        return response()->json([
            'error' => 'Only super admin can create global admins'
        ], 403);
    }

    // 🔒 Only global admins or super admin can create users at all
    if (!$authUser->isGlobalAdmin() && !$authUser->isSuperAdmin()) {
        abort(403, 'Unauthorized');
    }

    $newUser = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'date_of_birth' => $validated['date_of_birth'] ?? null,
        'password' => bcrypt($validated['password']),
        'is_global_admin' => $validated['is_global_admin'] ?? false,
        'created_by' => $authUser->id,
    ]);

    Cache::forget('meta.last_updated');

    Cache::forget('users.index');

    return response()->json($newUser, 201);
}


    public function profile($id)
{
    $auth = auth()->user();

    if (!$auth || !$auth->is_global_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

   $user = User::with([
    'sectionRoles.section:id,name',
    'sectionRoles.role:id,name'
])->findOrFail($id);


    return response()->json($user);
}

public function updateProfile(Request $request, $id)
{
    $auth = auth()->user();

    if (!$auth || !$auth->is_global_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $user = User::findOrFail($id);
    
    if(!$auth->is_super_admin && $user->is_super_admin ){
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
        'phone' => 'nullable|string|max:20',
        'date_of_birth' => 'nullable|date',
        'password' => 'nullable|min:8'
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json([
        'success' => true,
        'user' => $user->fresh() // fetch latest from DB
    ]);
}


// Show logged-in user's profile
public function myProfile()
{
    $auth = auth()->user();

    if (!$auth) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $user = User::with([
        'sectionRoles.section:id,name',
        'sectionRoles.role:id,name'
    ])->find($auth->id);

    return response()->json($user);
}

// Update email and password only
public function updateMyProfile(Request $request)
{
    $auth = auth()->user();

    if (!$auth) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $user = User::find($auth->id);

    $validated = $request->validate([
        'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
        'password' => 'nullable|min:8',
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json([
        'success' => true,
        'user' => $user->fresh(),
    ]);
}




    /**
     * Display the specified resource.
     */
public function show(Request $request)
{
    $user = auth()->user();

    $user->load([
        // Only active roles (end_date = null)
        'sectionRoles' => function ($q) {
            $q->whereNull('end_date')
              ->with(['role', 'section']);
        }
    ]);

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'is_global_admin' => (bool) $user->is_global_admin,
        'is_super_admin'=> (bool) $user->is_super_admin,

        // Format roles cleanly
        'roles' => $user->sectionRoles->map(function ($r) {
            return [
                'id' => $r->id,
                'role_id' => $r->role_id,
                'role_name' => $r->role->name,
                'section_id' => $r->section_id,
                'section_name' => $r->section->name,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
            ];
        }),

        // Unique active sections
        'sections' => $user->sectionRoles
            ->pluck('section')
            ->unique('id')
            ->values(),
    ]);
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
public function destroy($id)
{
    $authUser = auth()->user();
    $user = User::findOrFail($id);

    // ❌ No one deletes super admin
    if ($user->is_super_admin) {
        abort(403, 'Cannot delete super admin');
    }

    // 🔒 Deleting global admins → SUPER ADMIN ONLY
    if ($user->is_global_admin && !$authUser->isSuperAdmin()) {
        abort(403, 'Only super admin can delete global admins');
    }

    // 🔒 Deleting normal users → global admin OR super admin
    if (
        !$user->is_global_admin &&
        !$authUser->isGlobalAdmin() &&
        !$authUser->isSuperAdmin()
    ) {
        abort(403);
    }

    $user->delete();
    Cache::forget('meta.last_updated');
    Cache::forget('users.index');
    return response()->json(['success' => true]);
}

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = auth()->user()->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    public function addToSection(Request $request, User $user)
    {
        $loggedUser = auth()->user();

        // Only global admins can assign users to sections
        if (!$loggedUser->is_global_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $sectionId = $request->section_id;

        // Check if the user is already in this section
        $exists = SectionUserRole::where('user_id', $user->id)
            ->where('section_id', $sectionId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'User is already in this section'], 409);
        }

        // Add the user to the section
        SectionUserRole::create([
            'user_id' => $user->id,
            'section_id' => $sectionId,
            'role_id' => 10, // Optional: you can assign a role here if needed
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ]);
            Cache::forget('users.index');
            Cache::forget('meta.last_updated');

        return response()->json(['message' => 'User added to section successfully']);
    }


    public function stats()
{
    return response()->json([
        'total_users' => User::count(),
        'total_events' => Event::count(),

    ]);
}




}