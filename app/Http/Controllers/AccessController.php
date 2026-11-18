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
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('role', 'customer')
            ->where(function ($q) use ($credentials) {
                $q->where('email', $credentials['email'])
                  ->orWhere('phone', $credentials['email'])
                  ->orWhere('username', $credentials['email']);
            })->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email/No. WhatsApp atau kata sandi tidak cocok dengan akun pelanggan.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirectTo = $request->input('redirect') ?: route('menu');
        return redirect()->intended($redirectTo)->with('success', 'Selamat datang kembali, ' . $user->name . '! Akun Anda aktif. Silakan pilih menu favorit Anda.');
    }

    public function registerCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $baseUsername = Str::slug(Str::before($data['email'], '@')) ?: 'pelanggan';
        $username = $baseUsername;
        $suffix = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $suffix++;
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'username' => $username,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
        ]);

        // Per user requirements: User registers -> data saved to database -> then logs in using newly registered account
        return redirect()->route('account.login')->with('success', 'Registrasi berhasil! Data akun Anda telah disimpan. Silakan masuk dengan email/nomor WhatsApp dan kata sandi yang baru Anda daftarkan.');
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
