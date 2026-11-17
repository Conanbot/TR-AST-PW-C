@props([
    'available' => true,
    'bestSeller' => false,
    'package' => false,
])

<div class="flex gap-xs flex-wrap items-center">
    @if(!$available)
        <span class="badge badge-out-of-stock">Habis</span>
    @else
        @if($bestSeller)
            <span class="badge badge-best-seller">⭐ Best Seller</span>
        @endif
        @if($package)
            <span class="badge badge-promo">Paket Hemat</span>
        @endif
    @endif
</div>
