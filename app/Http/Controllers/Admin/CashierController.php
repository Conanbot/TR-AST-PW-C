<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();

        return view('admin.cashiers.index', compact('cashiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'alpha_dash', 'min:3', 'max:40', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => 'cashier',
        ]);

        return redirect()->route('admin.cashiers.index')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function resetPassword(Request $request, User $cashier): RedirectResponse
    {
        abort_unless($cashier->role === 'cashier', 403);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $cashier->update(['password' => $data['password']]);

        return redirect()->route('admin.cashiers.index')->with('success', 'Password kasir ' . $cashier->name . ' berhasil diubah.');
    }

    public function destroy(User $cashier): RedirectResponse
    {
        abort_unless($cashier->role === 'cashier', 403);

        $cashier->delete();

        return redirect()->route('admin.cashiers.index')->with('success', 'Akun kasir ' . $cashier->name . ' berhasil dihapus.');
    }
}
