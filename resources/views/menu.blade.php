@extends('layouts.app')

@section('title', 'Katalog Menu & Paket Hemat — Geprek GLO Salatiga')

@section('content')
<section class="menu-hero">
    <div class="container text-center">
        <span class="home-kicker">Pesan tanpa antre</span>
        <h1>Daftar Menu ayam crispy yang siap dipilih</h1>
        <p class="text-muted text-sm" style="max-width: 600px; margin: 0 auto; line-height: 1.5;">
            Pilih menu favorit Anda, tentukan potongan ayam dan minuman paket, lalu kirimkan langsung pesanan Anda ke WhatsApp kedai.
        </p>

        <div style="max-width: 720px; margin: var(--space-lg) auto 0 auto;">
            <div style="position: relative; margin-bottom: var(--space-md);">
                <input type="text" id="menu-search-input" class="form-control" placeholder="Cari nama menu (contoh: ayam crispy, sup, es teh)..." style="padding-left: 42px; border-radius: var(--radius-pill);">
                <svg style="position: absolute; left: 14px; top: 13px; width: 20px; height: 20px; color: var(--text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex gap-xs justify-center flex-wrap" id="category-tabs">
                <button type="button" class="btn btn-secondary btn-sm category-tab {{ $activeCategory === 'all' ? 'active' : '' }}" data-category="all" style="border-radius: var(--radius-pill);">
                    Semua Menu
                </button>
                @foreach($categories as $cat)
                <button type="button" class="btn btn-secondary btn-sm category-tab {{ $activeCategory === $cat->slug ? 'active' : '' }}" data-category="{{ $cat->slug }}" style="border-radius: var(--radius-pill);">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
</section>

@if(auth()->check() && auth()->user()->role === 'customer')
    <div style="background: var(--surface-card); border-bottom: 1px solid var(--border); padding: 12px 0;">
        <div class="container flex flex-wrap justify-between items-center gap-md">
            <div class="flex items-center gap-sm flex-wrap">
                <span class="badge badge-open">Pelanggan Aktif: {{ auth()->user()->name }}</span>
                <div class="flex items-center gap-xs">
                    <label for="flow-table-select" class="text-xs text-muted" style="margin-right: 4px;">Pilih / Tetapkan Meja (Dine-In):</label>
                    <select id="flow-table-select" class="form-control" style="padding: 4px 10px; font-size: 0.85rem; width: auto; background: var(--surface); color: var(--text);" onchange="updateFlowTable(this.value)">
                        <option value="">Belum Pilih Meja</option>
                        <option value="Meja 1">Meja 1 (Depan)</option>
                        <option value="Meja 2">Meja 2 (Depan)</option>
                        <option value="Meja 3">Meja 3 (Tengah)</option>
                        <option value="Meja 4">Meja 4 (Tengah)</option>
                        <option value="Meja 5">Meja 5 (Dalam)</option>
                        <option value="Meja 6">Meja 6 (Dalam)</option>
                        <option value="Meja 7">Meja 7 (Lesehan)</option>
                        <option value="Meja 8">Meja 8 (Lesehan)</option>
                        <option value="Meja Luar / Teras">Meja Luar / Teras</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-sm">
                <a href="{{ route('account.dashboard') }}" class="btn btn-secondary btn-sm" title="Lihat pesanan yang sedang berjalan atau riwayat">
                    Lihat Pesanan Saya
                </a>
            </div>
        </div>
    </div>
@else
    <div style="background: rgba(215, 38, 61, 0.08); border-bottom: 1px solid rgba(215, 38, 61, 0.2); padding: 12px 0;">
        <div class="container flex flex-wrap justify-between items-center gap-md">
            <div class="flex items-center gap-sm">
                <svg style="width: 20px; height: 20px; color: var(--primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm">Anda dapat memilih menu dan checkout sebagai tamu. Masuk agar riwayat pesanan tersimpan di akun.</span>
            </div>
            <div class="flex items-center gap-xs">
                <a href="{{ route('account.login') }}?redirect=/menu" class="btn btn-secondary btn-sm">Masuk</a>
                <a href="{{ route('account.register') }}" class="btn btn-secondary btn-sm">Daftar</a>
            </div>
        </div>
    </div>
@endif

<section style="padding: var(--space-xl) 0 var(--space-2xl) 0;">
    <div class="container">
        <div id="no-menu-found" style="display: none; text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <svg style="width: 48px; height: 48px; margin: 0 auto 12px auto; opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h4 style="color: var(--text);">Menu tidak ditemukan</h4>
            <p class="text-sm">Tidak ada menu yang cocok dengan kata kunci atau filter yang Anda pilih.</p>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 grid-cols-3 gap-lg" id="menu-grid">
            @foreach($categories as $category)
                @foreach($category->menuItems as $item)
                <div class="card menu-card flex flex-col justify-between"
                     data-category="{{ $category->slug }}"
                     data-name="{{ strtolower($item->name) }}"
                     style="{{ !$item->is_available ? 'opacity: 0.7;' : '' }}{{ $activeCategory !== 'all' && $activeCategory !== $category->slug ? ' display: none;' : '' }}">
                    <div>
                        <x-product-image :src="$item->image" :alt="$item->name" />

                        <div style="padding: var(--space-md);">
                            <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                                <span class="text-xs text-muted">{{ $category->name }}</span>
                                <x-badge-status :available="$item->is_available" :bestSeller="$item->is_best_seller" :package="$item->is_package" />
                            </div>
                            <h4 style="font-size: 1.05rem; margin-bottom: 6px; color: var(--text);">{{ $item->name }}</h4>
                            <p class="text-sm text-muted" style="line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $item->description ?? 'Olahan menu lezat higienis khas kedai Geprek GLO.' }}
                            </p>
                        </div>
                    </div>

                    <div style="padding: 0 var(--space-md) var(--space-md) var(--space-md); border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                            {{ $item->formatted_price }}
                        </div>

                        @if(!$item->is_available)
                            <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;">
                                Stok Habis
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-sm"
                                    onclick='openProductModal(@json($item->toArray() + ["image_url" => $item->image_url]), @json($category->slug))'>
                                {{ $item->is_package || in_array($category->slug, ['paket-hemat', 'aneka-ayam']) ? 'Pilih Varian' : '+ Tambah' }}
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>
</section>

<div id="product-detail-modal" class="modal-overlay" onclick="handleProductModalClick(event)">
    <div class="modal-content" role="dialog" aria-labelledby="modal-product-name">
        <div class="modal-header">
            <h3 id="modal-product-name">Detail Menu</h3>
            <button type="button" class="close-btn" onclick="closeProductModal()" aria-label="Tutup">&times;</button>
        </div>

        <div class="modal-body">
            <div id="modal-product-image-container" style="margin-bottom: var(--space-md);"></div>

            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                <span id="modal-product-price" style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">Rp0</span>
                <span id="modal-product-badge"></span>
            </div>
            <p id="modal-product-desc" class="text-sm text-muted" style="line-height: 1.5; margin-bottom: var(--space-md);"></p>

            <div id="variant-selection-container" class="form-group" style="display: none; padding-top: 12px; border-top: 1px solid var(--border);">
                <label class="form-label" for="selected-product-variant">Pilih varian</label>
                <select id="selected-product-variant" class="form-control"></select>
            </div>
            <p class="text-muted text-xs" style="margin-top: 10px;">Pilih varian dan kustomisasi sesuai grup pilihan yang tersedia.</p>

            <div id="modifier-groups-container" class="form-group" style="display: none; padding-top: 12px; border-top: 1px solid var(--border);">
            </div>

            <div class="form-group" style="padding-top: 12px; border-top: 1px solid var(--border);">
                <label for="modal-item-note" class="form-label">Catatan Khusus (Opsional)</label>
                <input type="text" id="modal-item-note" class="form-control" placeholder="Contoh: sambal dipisah, cabai 5, nasi sedikit...">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border);">
                <span class="font-semibold" style="font-size: 0.95rem;">Jumlah Pesanan:</span>
                <div style="display: flex; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 4px 10px;">
                    <button type="button" onclick="adjustModalQty(-1)" style="font-weight: bold; font-size: 1.2rem; padding: 2px 8px;">-</button>
                    <span id="modal-item-qty" style="font-size: 1.1rem; font-weight: 700; min-width: 32px; text-align: center;">1</span>
                    <button type="button" onclick="adjustModalQty(1)" style="font-weight: bold; font-size: 1.2rem; padding: 2px 8px;">+</button>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeProductModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitModalToCart()">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah ke Keranjang
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeModalItem = null;
    let modalQty = 1;

    // Filter categories & search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('menu-search-input');
        const categoryTabs = document.querySelectorAll('.category-tab');
        const menuCards = document.querySelectorAll('.menu-card');
        const noMenuFound = document.getElementById('no-menu-found');

        let activeCategory = @json($activeCategory);
        let searchQuery = '';

        function applyFilter() {
            let visibleCount = 0;

            menuCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                const cardName = card.getAttribute('data-name');

                const matchCat = (activeCategory === 'all' || cardCat === activeCategory);
                const matchSearch = (!searchQuery || cardName.includes(searchQuery));

                if (matchCat && matchSearch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noMenuFound) {
                noMenuFound.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                categoryTabs.forEach(t => {
                    t.classList.remove('active');
                    t.style.backgroundColor = 'var(--surface-card)';
                    t.style.borderColor = 'var(--border)';
                    t.style.color = 'var(--text)';
                });
                tab.classList.add('active');
                tab.style.backgroundColor = 'var(--primary)';
                tab.style.borderColor = 'var(--primary)';
                tab.style.color = '#FFFFFF';

                activeCategory = tab.getAttribute('data-category');
                applyFilter();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value.toLowerCase().trim();
                applyFilter();
            });
        }
    });

    function updateFlowTable(tableVal) {
        if (tableVal) {
            localStorage.setItem('glo_assigned_table', tableVal);
            const cartTable = document.getElementById('table-number');
            if (cartTable) cartTable.value = tableVal;
            window.showToast('Meja berhasil ditetapkan: ' + tableVal, 'info');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedTable = localStorage.getItem('glo_assigned_table');
        if (savedTable) {
            const flowSelect = document.getElementById('flow-table-select');
            if (flowSelect) flowSelect.value = savedTable;
            const cartTable = document.getElementById('table-number');
            if (cartTable) cartTable.value = savedTable;
        }
    });

    // Product Customization Modal logic
    function openProductModal(item, categorySlug) {
        activeModalItem = { ...item, categorySlug };
        modalQty = 1;

        document.getElementById('modal-product-name').textContent = item.name;
        document.getElementById('modal-product-price').textContent = item.formatted_price;
        document.getElementById('modal-product-desc').textContent = item.description || 'Menu lezat higienis pilihan khas Geprek GLO.';
        document.getElementById('modal-item-qty').textContent = modalQty;
        document.getElementById('modal-item-note').value = '';

        const variantContainer = document.getElementById('variant-selection-container');
        const variantSelect = document.getElementById('selected-product-variant');
        const modifierContainer = document.getElementById('modifier-groups-container');
        const variants = (item.variants || []).filter(variant => variant.is_available);
        const modifierGroups = item.modifier_groups || [];

        variantContainer.style.display = variants.length ? 'block' : 'none';
        variantSelect.innerHTML = variants.map(variant =>
            `<option value="${variant.id}">${escapeHtml(variant.name)} — ${escapeHtml(variant.formatted_price)}</option>`
        ).join('');

        modifierContainer.style.display = modifierGroups.length ? 'block' : 'none';
        modifierContainer.innerHTML = modifierGroups.map(group => {
            const max = group.max_selections || 1;
            const inputType = max === 1 ? 'radio' : 'checkbox';
            const requiredLabel = group.is_required ? ' *' : '';

            return `
                <div class="modifier-group" data-group-id="${group.id}" data-min="${group.min_selections || (group.is_required ? 1 : 0)}" data-max="${group.max_selections || ''}" style="margin-bottom: 14px;">
                    <label class="form-label">${escapeHtml(group.name)}${requiredLabel}</label>
                    <div style="display: grid; gap: 8px;">
                        ${group.options.map(option => `
                            <label class="form-check" style="background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <input type="${inputType}" name="modifier_group_${group.id}" value="${option.id}" data-group-id="${group.id}" data-group-name="${escapeHtml(group.name)}" data-option-name="${escapeHtml(option.name)}" data-price-delta="${option.price_delta}">
                                <span style="font-size: 0.9rem;">${escapeHtml(option.name)}${option.price_delta > 0 ? ` (+${window.formatRupiah(option.price_delta)})` : ''}</span>
                            </label>
                        `).join('')}
                    </div>
                    <div class="form-error modifier-error" style="display: none;">Pilih opsi yang sesuai untuk grup ini.</div>
                </div>
            `;
        }).join('');

        // Set image container
        const imgContainer = document.getElementById('modal-product-image-container');
        if (item.image_url) {
            imgContainer.innerHTML = `
                <div class="product-image-box" style="aspect-ratio: 4/3; border-radius: var(--radius-md);">
                    <img src="${item.image_url}" alt="${item.name}">
                </div>
            `;
        } else {
            imgContainer.innerHTML = `
                <div class="product-image-box" style="aspect-ratio: 4/3; border-radius: var(--radius-md);">
                    <div class="placeholder-content">
                        <svg class="placeholder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="placeholder-text">Foto belum tersedia</span>
                    </div>
                </div>
            `;
        }

        const modal = document.getElementById('product-detail-modal');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeProductModal() {
        const modal = document.getElementById('product-detail-modal');
        modal.classList.remove('open');
        document.body.style.overflow = '';
        activeModalItem = null;
    }

    function handleProductModalClick(e) {
        if (e.target.id === 'product-detail-modal') {
            closeProductModal();
        }
    }

    function adjustModalQty(delta) {
        modalQty = Math.max(1, modalQty + delta);
        document.getElementById('modal-item-qty').textContent = modalQty;
    }

    function submitModalToCart() {
        if (!activeModalItem) return;

        const selectedModifiers = [];
        let invalidGroup = false;
        document.querySelectorAll('.modifier-group').forEach(group => {
            const selected = group.querySelectorAll('input:checked');
            const min = Number(group.dataset.min || 0);
            const max = group.dataset.max ? Number(group.dataset.max) : null;
            const error = group.querySelector('.modifier-error');
            const invalid = selected.length < min || (max !== null && selected.length > max);

            if (error) error.style.display = invalid ? 'block' : 'none';
            invalidGroup = invalidGroup || invalid;

            selected.forEach(input => selectedModifiers.push({
                optionId: Number(input.value),
                groupName: input.dataset.groupName,
                optionName: input.dataset.optionName,
                priceDelta: Number(input.dataset.priceDelta || 0)
            }));
        });

        if (invalidGroup) {
            return;
        }

        const note = document.getElementById('modal-item-note').value.trim();
        const variantId = document.getElementById('selected-product-variant').value || null;
        const selectedVariant = (activeModalItem.variants || []).find(variant => String(variant.id) === String(variantId));

        // Add item to Cart
        Cart.addItem({
            id: activeModalItem.id,
            name: activeModalItem.name,
            price: selectedVariant ? selectedVariant.price : activeModalItem.price,
            qty: modalQty,
            variantId: variantId ? Number(variantId) : null,
            variantName: selectedVariant ? selectedVariant.name : null,
            modifiers: selectedModifiers,
            modifierTotal: selectedModifiers.reduce((total, modifier) => total + modifier.priceDelta, 0),
            note: note
        });

        closeProductModal();
    }
</script>
@endpush
