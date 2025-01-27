<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter users by role
        if($request->role){
            $query->where('role', $request->role);
        }

        // Filter users by verification status
        if($request->has('verified')){
            $query->where('verified', $request->verified ?? false);
        }

        // Search users across multiple fields
        if($request->search){
            $query->where('first_name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('last_name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
        }

        $users = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Users listed',
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email',
            'role' => 'nullable|in:admin,manager,organizer',
            'password' => 'required|min:8|confirmed',
            'verified' => 'nullable|boolean',
        ]);

        // Encrypt the password
        $validated['password'] =  Hash::make($validated['password']);
        $validated['verified'] =  1; // All new users are verified by default
        
        // Only allow admin to specify roles
        if(Auth::user()->role != 'admin'){
            unset($validated['role']);
        }

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'User created',
            'user' => $user,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'User shown',
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,
            'role' => 'nullable|in:admin,manager,organizer',
            'password' => 'min:8|confirmed',
            'verified' => 'nullable|boolean',
        ]);

        // Encrypt the password if present
        if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        $validated['verified'] =  1; // All updated users are verified by default

        // Only allow admin to change roles
        if(Auth::user()->role != 'admin'){
            unset($validated['role']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Only allow admin to delete admins & managers
        if($user->role != 'client' && Auth::user()->role != 'admin'){
            abort(401, 'Not authorized to delete this user.');
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted'
        ]);
    }
}
