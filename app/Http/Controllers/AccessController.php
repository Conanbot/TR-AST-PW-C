<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function showCustomerLogin(): View|RedirectResponse
    {
        return Auth::check() ? $this->redirectForRole(Auth::user()->role) : view('account.login');
    }

    public function showCustomerRegister(): View|RedirectResponse
    {
        return Auth::check() ? $this->redirectForRole(Auth::user()->role) : view('account.register');
    }

    public function loginCustomer(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials + ['role' => 'customer'], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok dengan akun pelanggan.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function registerCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $baseUsername = Str::slug(Str::before($data['email'], '@')) ?: 'pelanggan';
        $username = $baseUsername;
        $suffix = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $suffix++;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.dashboard')->with('success', 'Akun pelanggan berhasil dibuat.');
    }

    public function dashboardCustomer(Request $request): View
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(6);

        return view('account.dashboard', compact('orders'));
    }

    public function showCashierLogin(): View|RedirectResponse
    {
        return Auth::check() ? $this->redirectForRole(Auth::user()->role) : view('cashier.login');
    }

    public function loginCashier(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials + ['role' => 'cashier'], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi kasir tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('cashier.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda sudah keluar dari akun.');
    }

    private function redirectForRole(string $role): RedirectResponse
    {
        return redirect()->route(match ($role) {
            'admin' => 'admin.dashboard',
            'cashier' => 'cashier.dashboard',
            default => 'account.dashboard',
        });
    }
}
