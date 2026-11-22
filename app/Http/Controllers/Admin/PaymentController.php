<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['order', 'confirmedBy', 'histories']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('method')) {
            $query->where('method_code', $request->string('method'));
        }
        $payments = $query->latest()->paginate(20)->withQueryString();
        $counts = [
            'unpaid' => Payment::where('status', 'unpaid')->count(),
            'pending' => Payment::where('status', 'pending_verification')->count(),
            'paid' => Payment::where('status', 'paid')->count(),
        ];
        return view('admin.payments.index', compact('payments', 'counts'));
    }
    public function confirmCash(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);
        $payments->confirmCash($payment, $request->user(), (int) $data['amount']);
        return back()->with('success', 'Pembayaran tunai berhasil dikonfirmasi.');
    }
    public function verifyQris(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:decision,reject', 'nullable', 'string', 'max:255'],
        ]);
        $payments->verifyQris(
            $payment,
            $request->user(),
            $data['decision'] === 'approve',
            $data['rejection_reason'] ?? null
        );
        return back()->with('success', 'Pembayaran QRIS berhasil diperbarui.');
    }
    public function settings(): View
    {
        return view('admin.payments.settings', [
            'settings' => StoreSetting::current(),
            'methods' => PaymentMethod::orderBy('sort_order')->get(),
        ]);
    }
    public function updateSettings(Request $request): RedirectResponse
    {
        $qrisEnabled = $request->boolean('qris_enabled');
        $settings = StoreSetting::current();
        $data = $request->validate([
            'qris_enabled' => ['nullable', 'boolean'],
            'qris_merchant_name' => [$qrisEnabled ? 'required' : 'nullable', 'string', 'max:120'],
            'qris_instructions' => ['nullable', 'string', 'max:1000'],
            'qris_image' => [$qrisEnabled && !$settings->qris_image ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $settings->qris_enabled = $request->boolean('qris_enabled');
        $settings->qris_merchant_name = $data['qris_merchant_name'] ?? null;
        $settings->qris_instructions = $data['qris_instructions'] ?? null;
        if ($request->hasFile('qris_image')) {
            if ($settings->qris_image) {
                Storage::disk('public')->delete($settings->qris_image);
            }
            $settings->qris_image = $request->file('qris_image')->store('qris', 'public');
        }
        $settings->save();
        if ($request->has('methods')) {
            foreach ($request->input('methods', []) as $code => $values) {
                PaymentMethod::where('code', $code)->update([
                    'is_active' => isset($values['is_active']),
                    'sort_order' => (int) ($values['sort_order'] ?? 0),
                ]);
            }
        }
        return back()->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
    public function proof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->proof_image && Storage::disk('local')->exists($payment->proof_image), 404);
        return Storage::disk('local')->response($payment->proof_image);
    }
}
