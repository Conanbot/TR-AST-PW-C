<?php

namespace App\Services;

use App\Models\Customer;

use App\Models\Order;

use App\Models\User;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Illuminate\Database\QueryException;

class CustomerService

{

    public function normalizePhone(?string $phone): ?string

    {

        return Customer::normalizePhone($phone);

    }

    public function resolveCustomerForOrder(array $data, ?User $user = null): ?Customer

    {

        $rawPhone = $data['customer_phone'] ?? null;

        $normalizedPhone = Customer::normalizePhone($rawPhone);

        if (!$normalizedPhone) {

            return null;

        }

        $rawName = trim($data['customer_name'] ?? '');

        if ($rawName === '') {

            $rawName = 'Pelanggan ' . substr($normalizedPhone, -4);

        }

        $customer = Customer::where('normalized_phone', $normalizedPhone)->first();

        if ($customer) {

            $dirty = false;

            if (empty($customer->phone) && !empty($rawPhone)) {

                $customer->phone = $rawPhone;

                $dirty = true;

            }

            if (empty($customer->email) && !empty($user?->email)) {

                $customer->email = $user->email;

                $dirty = true;

            }

            if ($dirty) {

                $customer->save();

            }

            return $customer;

        }

        try {

            return Customer::create([

                'name' => $rawName,

                'phone' => $rawPhone ?: $normalizedPhone,

                'normalized_phone' => $normalizedPhone,

                'email' => $user?->email,

                'status' => 'active',

            ]);

        } catch (QueryException $e) {

            if (str_contains($e->getMessage(), 'normalized_phone')) {

                return Customer::where('normalized_phone', $normalizedPhone)->first();

            }

            throw $e;

        }

    }

    public function getStatistics(Customer $customer): array

    {

        $totalOrders = $customer->orders()->count();

        $completedOrders = $customer->completedOrders()->count();

        $cancelledOrders = $customer->cancelledOrders()->count();

        $successfulPaidOrdersQuery = $customer->successfulPaidOrders();

        $paidOrdersCount = (clone $successfulPaidOrdersQuery)->count();

        $totalSpending = (int) (clone $successfulPaidOrdersQuery)->sum('total');

        $averageOrderValue = $paidOrdersCount > 0

            ? (int) round($totalSpending / $paidOrdersCount)

            : 0;

        $firstOrderDate = $customer->orders()->oldest()->value('created_at');

        $lastOrderDate = $customer->orders()->latest()->value('created_at');

        return [

            'total_orders' => $totalOrders,

            'completed_orders' => $completedOrders,

            'cancelled_orders' => $cancelledOrders,

            'total_spending' => $totalSpending,

            'formatted_total_spending' => 'Rp' . number_format($totalSpending, 0, ',', '.'),

            'average_order_value' => $averageOrderValue,

            'formatted_average_order_value' => 'Rp' . number_format($averageOrderValue, 0, ',', '.'),

            'first_order_date' => $firstOrderDate,

            'last_order_date' => $lastOrderDate,

        ];

    }

    public function getCustomerOrders(Customer $customer, int $perPage = 15): LengthAwarePaginator

    {

        return $customer->orders()

            ->withCount('items')

            ->with(['items', 'latestPayment'])

            ->latest()

            ->paginate($perPage)

            ->withQueryString();

    }

    public function backfillOrders(bool $apply = false): array

    {

        $unlinkedOrders = Order::whereNull('customer_id')

            ->whereNotNull('customer_phone')

            ->where('customer_phone', '!=', '')

            ->orderBy('id')

            ->get();

        $totalFound = $unlinkedOrders->count();

        $validPhones = 0;

        $invalidPhones = 0;

        $existingCustomersMatched = 0;

        $newCustomersCreated = 0;

        $ordersLinked = 0;

        $customerCache = [];

        foreach ($unlinkedOrders as $order) {

            $normalized = Customer::normalizePhone($order->customer_phone);

            if (!$normalized) {

                $invalidPhones++;

                continue;

            }

            $validPhones++;

            if (!isset($customerCache[$normalized])) {

                $existing = Customer::where('normalized_phone', $normalized)->first();

                if ($existing) {

                    $customerCache[$normalized] = $existing;

                    $existingCustomersMatched++;

                } else {

                    $newCustomer = new Customer([

                        'name' => trim($order->customer_name) ?: 'Pelanggan ' . substr($normalized, -4),

                        'phone' => $order->customer_phone,

                        'normalized_phone' => $normalized,

                        'status' => 'active',

                    ]);

                    if ($apply) {

                        $newCustomer->save();

                    }

                    $customerCache[$normalized] = $newCustomer;

                    $newCustomersCreated++;

                }

            }

            $targetCustomer = $customerCache[$normalized];

            if ($apply && $targetCustomer->exists) {

                $order->customer_id = $targetCustomer->id;

                $order->saveQuietly();

            }

            $ordersLinked++;

        }

        return [

            'mode' => $apply ? 'apply' : 'dry-run',

            'total_found' => $totalFound,

            'valid_phones' => $validPhones,

            'invalid_phones' => $invalidPhones,

            'existing_customers_matched' => $existingCustomersMatched,

            'new_customers_created' => $newCustomersCreated,

            'orders_linked' => $ordersLinked,

        ];

    }

}

