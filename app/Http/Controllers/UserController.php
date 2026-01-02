<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    return User::with('role', 'section', 'creator')->get();
}

public function store(Request $request)
{
    $user = auth()->user(); // logged-in user

    $role_id = $request->role_id;
    $section_id = $request->section_id;

    // 1️⃣ High Admin: can create anyone, including Presidents
    if ($user->role->name === 'High Admin') {
        // no restriction
    }
    // 2️⃣ President: can create only Normal Users in their own section
    elseif (str_contains($user->role->name, 'Chabiba President')) {
        if($section_id != 1 || $role_id == 2) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

    }
    elseif (str_contains($user->role->name, 'Tala2e3 President')) {
        if($section_id != 2 || $role_id == 3) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

    }
    elseif (str_contains($user->role->name, 'Forsan President')) {
        if($section_id != 3 || $role_id == 4) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
    // 3️⃣ Normal Users: cannot create anyone
    else {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // ✅ Passed all checks, create the user
    $newUser = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role_id' => $role_id,
        'section_id' => $section_id,
        'created_by' => $user->id,
    ]);

    return response()->json($newUser);
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
        //
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
}
