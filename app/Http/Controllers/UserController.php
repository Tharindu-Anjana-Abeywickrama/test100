<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::with('territory')->get(),
            'territories' => Territory::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'address' => 'required|string',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:users',
            'gender' => 'required|in:male,female',
            'territory_id' => 'required|exists:territories,id',
            // 'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users,nic,' . $user->id,
            'address' => 'required|string',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'gender' => 'required|in:male,female',
            'territory_id' => 'required|exists:territories,id',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back();
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back();
    }
}