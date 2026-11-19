<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Services\PurchaseReceivingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'items']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('purchase_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('purchase_date', '<=', $request->to);
        }

        $purchaseOrders = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $materials = RawMaterial::where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.create', compact('suppliers', 'materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.raw_material_id' => ['required', 'exists:raw_materials,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $po = DB::transaction(function () use ($data, $request) {
            $po = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);
            $subtotal = 0;

            foreach ($data['items'] as $row) {
                $material = RawMaterial::findOrFail($row['raw_material_id']);
                $quantity = (float) $row['ordered_quantity'];
                $unitPrice = (int) $row['unit_price'];
                $lineSubtotal = (int) round($quantity * $unitPrice);
                $subtotal += $lineSubtotal;
                $po->items()->create([
                    'raw_material_id' => $material->id,
                    'material_name' => $material->name,
                    'purchase_unit' => $material->purchase_unit,
                    'usage_unit' => $material->usage_unit,
                    'conversion_factor' => $material->conversion_factor,
                    'ordered_quantity' => $quantity,
                    'received_quantity' => 0,
                    'rejected_quantity' => 0,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'notes' => $row['notes'] ?? null,
                ]);
            }

            $po->update(['subtotal' => $subtotal, 'total' => $subtotal]);
            return $po;
        });

        return redirect()->route('admin.purchasing.show', $po)->with('success', 'Purchase order draft berhasil dibuat.');
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        if ($purchaseOrder->status !== 'draft') {
            abort(422, 'Purchase order hanya dapat diedit saat status draft.');
        }
        $purchaseOrder->load('items');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $materials = RawMaterial::where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.edit', compact('purchaseOrder', 'suppliers', 'materials'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Purchase order hanya dapat diedit saat status draft.']);
        }
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.raw_material_id' => ['required', 'exists:raw_materials,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'notes' => $data['notes'] ?? null,
            ]);
            $purchaseOrder->items()->delete();
            $subtotal = 0;
            foreach ($data['items'] as $row) {
                $material = RawMaterial::findOrFail($row['raw_material_id']);
                $quantity = (float) $row['ordered_quantity'];
                $unitPrice = (int) $row['unit_price'];
                $lineSubtotal = (int) round($quantity * $unitPrice);
                $subtotal += $lineSubtotal;
                $purchaseOrder->items()->create([
                    'raw_material_id' => $material->id,
                    'material_name' => $material->name,
                    'purchase_unit' => $material->purchase_unit,
                    'usage_unit' => $material->usage_unit,
                    'conversion_factor' => $material->conversion_factor,
                    'ordered_quantity' => $quantity,
                    'received_quantity' => 0,
                    'rejected_quantity' => 0,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
            $purchaseOrder->update(['subtotal' => $subtotal, 'total' => $subtotal]);
        });

        return redirect()->route('admin.purchasing.show', $purchaseOrder)->with('success', 'Purchase order draft diperbarui.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.rawMaterial', 'receivings.receiver', 'receivings.items']);
        return view('admin.purchasing.show', compact('purchaseOrder'));
    }

    public function markOrdered(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Purchase order harus berstatus draft untuk ditandai ordered.']);
        }
        $purchaseOrder->update(['status' => 'ordered']);
        return back()->with('success', 'Purchase order ditandai sebagai ordered.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (in_array($purchaseOrder->status, ['cancelled', 'received'], true)) {
            throw ValidationException::withMessages(['status' => 'Purchase order pada status ini tidak dapat dibatalkan.']);
        }
        if ((float) $purchaseOrder->items()->sum('received_quantity') > 0) {
            throw ValidationException::withMessages(['status' => 'Purchase order yang sudah menerima barang tidak dapat dibatalkan.']);
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return back()->with('success', 'Purchase order dibatalkan.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, PurchaseReceivingService $service): RedirectResponse
    {
        $data = $request->validate([
            'received_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.accepted_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.rejected_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.actual_unit_price' => ['nullable', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $key = $data['idempotency_key'] ?: 'grn-form:' . $request->session()->getId() . ':po:' . $purchaseOrder->id . ':' . Str::random(32);
        $service->create(
            $purchaseOrder,
            $data['items'],
            $data['notes'] ?? null,
            $request->user(),
            $key,
            $data['received_at'] ?? null
        );

        return back()->with('success', 'Penerimaan barang berhasil dicatat dan stok diperbarui.');
    }

    private function nextPoNumber(): string
    {
        $maxId = PurchaseOrder::max('id') ?? 0;
        $seq = $maxId + 1;
        return 'PO-' . now()->format('ymd') . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
