<?php

namespace App\Http\Controllers;

use App\Models\Order;

use App\Models\User;

use App\Services\OrderAccessService;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;

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

        $user = User::where(function ($q) use ($credentials) {

                $q->where('email', $credentials['email'])

                  ->orWhere('phone', $credentials['email'])

                  ->orWhere('name', $credentials['email']);

            })->first();

        if (!$user || !hash_equals((string) $user->password, (string) $credentials['password'])) {

            throw ValidationException::withMessages([

                'email' => 'Email/No. WhatsApp atau kata sandi tidak cocok.',

            ]);

        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        if ($user->role === 'admin') {

            return redirect()->route('admin.dashboard')

                ->with('success', 'Selamat datang kembali di Panel Admin Geprek GLO.');

        }

        if ($user->role === 'cashier') {

            return redirect()->route('cashier.dashboard')

                ->with('success', 'Selamat datang kembali, ' . $user->name . '.');

        }

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

        User::create([

            'name' => $data['name'],

            'email' => $data['email'],

            'phone' => $data['phone'] ?? null,

            'password' => $data['password'],

            'role' => 'customer',

        ]);

        return redirect()->route('account.login')->with('success', 'Registrasi berhasil! Data akun Anda telah disimpan. Silakan masuk dengan email/nomor WhatsApp dan kata sandi yang baru Anda daftarkan.');

    }

    public function dashboardCustomer(Request $request): View

    {

        $user = $request->user();

        $totalOrders = Order::where('user_id', $user->id)->count();

        return view('account.dashboard', compact('user', 'totalOrders'));

    }

    public function updateCustomerProfile(Request $request): RedirectResponse

    {

        /** @var User $user */

        $user = $request->user();

        $data = $request->validate([

            'name' => ['required', 'string', 'max:120'],

            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],

            'phone' => ['nullable', 'string', 'max:30'],

            'password' => ['nullable', 'string', 'min:6', 'confirmed'],

        ], [

            'name.required' => 'Nama lengkap wajib diisi.',

            'email.required' => 'Email wajib diisi.',

            'email.email' => 'Format email tidak valid.',

            'email.unique' => 'Email ini sudah digunakan akun lain.',

            'password.min' => 'Kata sandi baru minimal 6 karakter.',

            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',

        ]);

        $updatePayload = [

            'name' => $data['name'],

            'email' => $data['email'],

            'phone' => $data['phone'] ?? null,

        ];

        if (!empty($data['password'])) {

            $updatePayload['password'] = $data['password'];

        }

        $user->update($updatePayload);

        return redirect()->route('account.dashboard')->with('success', 'Profil akun berhasil diperbarui.');

    }

    public function ordersCustomer(Request $request): View

    {

        $orders = Order::with('items')

            ->where('user_id', $request->user()->id)

            ->latest()

            ->paginate(8);

        return view('account.orders', compact('orders'));

    }

    public function trackCustomerOrder(Request $request, Order $order, OrderAccessService $access): RedirectResponse

    {

        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $token = $access->issue($order);

        return redirect()->route('orders.track', [

            'orderNumber' => $order->order_number,

            'token' => $token,

        ]);

    }

    public function showCashierLogin(): View|RedirectResponse

    {

        return Auth::check() ? $this->redirectForRole(Auth::user()->role) : redirect()->route('account.login');

    }

    public function loginCashier(Request $request): RedirectResponse

    {

        $credentials = $request->validate([

            'email' => ['required', 'string'],

            'password' => ['required', 'string'],

        ]);

        $user = User::where('role', 'cashier')

            ->where(function ($q) use ($credentials) {

                $q->where('email', $credentials['email'])

                  ->orWhere('name', $credentials['email']);

            })

            ->first();

        if (!$user || !hash_equals((string) $user->password, (string) $credentials['password'])) {

            throw ValidationException::withMessages([

                'email' => 'Email atau kata sandi kasir tidak cocok.',

            ]);

        }

        Auth::login($user, $request->boolean('remember'));

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

