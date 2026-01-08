<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Section;
use App\Models\Role;
use App\Models\SectionUserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */


public function index()
{
    return Cache::remember('users.index', 60, function () {
        return User::with([
                'sections:id,name',      // load sections, only needed fields
                'creator:id,name'        // optional, light
            ])
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'is_global_admin',
                'created_by'
            )
            ->orderBy('name')
            ->get();
    });
}


        public function store(Request $request)
    {
        $authUser = auth()->user();

        // 🔒 Only global admins can create users
        if (!$authUser->is_global_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_global_admin' => 'boolean'
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_global_admin' => $validated['is_global_admin'] ?? false,
            'created_by' => $authUser->id,
        ]);
            Cache::forget('users.index');

        return response()->json($newUser, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        return response()->json($user);
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
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
            Cache::forget('users.index');
        return response()->json(['message' => 'User deleted successfully']);
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
        ]);
            Cache::forget('users.index');

        return response()->json(['message' => 'User added to section successfully']);
    }




}