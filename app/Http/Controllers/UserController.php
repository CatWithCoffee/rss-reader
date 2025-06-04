<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);

        return view('admin.users')->with('users', $users);
    }

    public function updateRole(User $user, Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        try {
            $user->update(['role' => $validated['role']]);
        } catch (Throwable $th) {
            dd($th);
        }

        return back()->with('success', 'Роль пользователя обновлена');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Пользователь удален');
    }

    public function search(Request $request)
    {
        $search = $request->input('query');

        $users = User::when($search, function ($query) use ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('login', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users', compact('users'));
    }
}
