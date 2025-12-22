<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(Gate::allows('manage-users'), 403);
        $users = User::orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(Gate::allows('manage-users'), 403);

        $data = $request->validate(['role' => 'required|in:admin,editor,viewer']);
        $user->role = $data['role'];
        // Keep is_admin boolean for compatibility
        $user->is_admin = $data['role'] === 'admin';
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado');
    }
    public function destroy(User $user)
    {
        abort_unless(Gate::allows('manage-users'), 403);
        // No permitir que el admin se elimine a sí mismo
        if (\Illuminate\Support\Facades\Auth::id() === $user->id) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No puede eliminar su propio usuario.'], 403);
            }
            return redirect()->route('admin.users.index')->with('error', 'No puede eliminar su propio usuario.');
        }
        $user->delete();
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        }
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente');
    }
}
