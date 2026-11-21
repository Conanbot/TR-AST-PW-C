/**
 * Geprek GLO Salatiga - Cart & Checkout Engine
 * Sesuai PRD v1.1 Bab 12, FR-09, FR-10, FR-11, FR-14, BR-01, BR-04, BR-05
 */

const Cart = {
  storageKey: 'geprek_glo_cart',
  settings: {
    isOpen: true,
    waNumber: '6281268177729',
    packagingFee: 1000,
    deliveryEnabled: false,
    deliveryRadiusM: 800,
    storeLat: -7.3117469,
    storeLng: 110.4851234,
    checkoutKey: null,
    promoCode: null,
    promoApplied: null,
    lastCheckout: null
  },

  init(settingsOverride = {}) {
    this.settings = { ...this.settings, ...settingsOverride };
    this.settings.checkoutKey = null;

    if (!window.isUserLoggedIn) {
      try {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem('geprek_glo_last_checkout');
      } catch (e) {}
      const floatingBar = document.getElementById('cart-floating-bar');
      if (floatingBar) floatingBar.style.display = 'none';
      return;
    }

    this.settings.lastCheckout = this.readCheckoutReceipt();
    this.updateCartBadge();
    this.renderCartDrawer();
    if (this.settings.lastCheckout?.order_number) {
      this.showCheckoutResult(this.settings.lastCheckout);
    }
  },

  getItems() {
    try {
      const data = localStorage.getItem(this.storageKey);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error('Error reading cart from localStorage', e);
      return [];
    }
  },

  saveItems(items) {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(items));
      this.updateCartBadge();
      this.renderCartDrawer();
      if (this.settings.promoCode) {
        this.validateAppliedPromotion(false);
      }
    } catch (e) {
      console.error('Error saving cart to localStorage', e);
    }
  },

  addItem(item) {
    // Unique key includes every selection so different configurations remain separate.
    const cutPart = item.cut ? item.cut.trim() : '';
    const drinkPart = item.drink ? item.drink.trim() : '';
    const variantPart = item.variantId || '';
    const modifierPart = (item.modifiers || [])
      .map(modifier => modifier.optionId)
      .sort((a, b) => a - b)
      .join(',');
    const key = `${item.id}-${variantPart}-${modifierPart}-${cutPart}-${drinkPart}`;

    let items = this.getItems();
    const existingIndex = items.findIndex(i => i.key === key && (i.note || '') === (item.note || ''));

    if (existingIndex > -1) {
      items[existingIndex].qty += item.qty;
    } else {
      items.push({
        key: key,
        id: item.id,
        name: item.name,
        price: item.price,
        variantId: item.variantId || null,
        variantName: item.variantName || null,
        modifiers: item.modifiers || [],
        modifierTotal: item.modifierTotal || 0,
        qty: item.qty,
        cut: item.cut || null,
        drink: item.drink || null,
        note: item.note || ''
      });
    }

    this.saveItems(items);

    if (this.getTotalQty() >= 20) {
      window.showToast('Pemesanan ≥ 20 porsi diarahkan untuk negosiasi jadwal melalui chat WA', 'warning');
    }
  },

  updateQty(key, delta) {
    let items = this.getItems();
    const index = items.findIndex(i => i.key === key);
    if (index > -1) {
      items[index].qty += delta;
      if (items[index].qty <= 0) {
        items.splice(index, 1);
      }
      this.saveItems(items);
      if (delta > 0 && this.getTotalQty() >= 20) {
        window.showToast('Pemesanan ≥ 20 porsi diarahkan untuk negosiasi jadwal melalui chat WA', 'warning');
      }
    }
  },

  removeItem(key) {
    let items = this.getItems();
    items = items.filter(i => i.key !== key);
    this.saveItems(items);
    window.showToast('Item berhasil dihapus dari keranjang', 'info');
  },

  clearCart() {
    localStorage.removeItem(this.storageKey);
    this.settings.promoCode = null;
    this.settings.promoApplied = null;
    const promoInput = document.getElementById('promo-code');
    if (promoInput) promoInput.value = '';
    const promoStatus = document.getElementById('promo-status');
    if (promoStatus) promoStatus.textContent = '';
    this.updateCartBadge();
    this.renderCartDrawer();
  },

  readCheckoutReceipt() {
    try {
      const receipt = sessionStorage.getItem('geprek_glo_checkout_receipt');
      return receipt ? JSON.parse(receipt) : null;
    } catch (error) {
      console.error('Error reading checkout receipt', error);
      return null;
    }
  },

  saveCheckoutReceipt(receipt) {
    try {
      sessionStorage.setItem('geprek_glo_checkout_receipt', JSON.stringify(receipt));
    } catch (error) {
      console.error('Error saving checkout receipt', error);
    }
  },

  showCheckoutResult(result) {
    const resultEl = document.getElementById('checkout-result');
    const numberEl = document.getElementById('checkout-result-number');
    const trackingEl = document.getElementById('checkout-result-tracking');

    if (!resultEl || !numberEl || !trackingEl) return;

    numberEl.textContent = `Nomor order: ${result.order_number}`;
    trackingEl.href = result.tracking_url;
    resultEl.style.display = 'block';
  },

  retryWhatsApp() {
    const receipt = this.settings.lastCheckout || this.readCheckoutReceipt();

    if (!receipt?.whatsapp_url) {
      window.showToast('Data WhatsApp order belum tersedia. Muat ulang halaman lalu coba lagi.', 'error');
      return;
    }

    window.open(receipt.whatsapp_url, '_blank', 'noopener,noreferrer');
  },

  getSubtotal() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + ((item.price + (item.modifierTotal || 0)) * item.qty), 0);
  },

  getTotalQty() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + item.qty, 0);
  },

  async validateAppliedPromotion(showMessage = true) {
    const code = (document.getElementById('promo-code')?.value || this.settings.promoCode || '').trim();
    if (!code) return;
    const items = this.getItems();
    if (!items.length) {
      this.removePromotion(false);
      return;
    }
    const serviceMethod = document.querySelector('input[name="service_method"]:checked')?.value || 'dine_in';
    const customerPhone = document.getElementById('customer-phone')?.value.trim() || null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
      const response = await fetch('/api/promotions/validate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          promo_code: code,
          service_method: serviceMethod,
          customer_phone: customerPhone,
          items: items.map(item => ({
            id: item.id,
            qty: item.qty,
            variant_id: item.variantId,
            modifier_option_ids: (item.modifiers || []).map(modifier => modifier.optionId),
            cut: item.cut,
            drink: item.drink,
            note: item.note
          }))
        })
      });
      const result = await response.json();
      if (!response.ok || !result.valid) {
        this.settings.promoApplied = null;
        this.settings.promoCode = null;
        this.updateCheckoutSummary(this.getSubtotal(), serviceMethod);
        const statusEl = document.getElementById('promo-status');
        if (statusEl) {
          statusEl.textContent = result.reason || 'Promo tidak dapat digunakan.';
          statusEl.style.color = 'var(--danger)';
        }
        if (showMessage) window.showToast(result.reason || 'Promo tidak dapat digunakan.', 'error');
        return;
      }
      this.settings.promoCode = result.code;
      this.settings.promoApplied = result;
      const statusEl = document.getElementById('promo-status');
      if (statusEl) {
        statusEl.textContent = `Promo diterapkan: -${window.formatRupiah(result.discount)}`;
        statusEl.style.color = 'var(--text-ok)';
      }
      this.updateCheckoutSummary(result.subtotal, serviceMethod);
      if (showMessage) window.showToast('Kode promo berhasil diterapkan.', 'success');
    } catch (error) {
      console.error(error);
      if (showMessage) window.showToast('Promo belum dapat divalidasi.', 'error');
    }
  },

  applyPromotion() {
    this.validateAppliedPromotion(true);
  },

  removePromotion(showMessage = true) {
    this.settings.promoCode = null;
    this.settings.promoApplied = null;
    const codeInput = document.getElementById('promo-code');
    if (codeInput) codeInput.value = '';
    const statusEl = document.getElementById('promo-status');
    if (statusEl) statusEl.textContent = '';
    const serviceMethod = document.querySelector('input[name="service_method"]:checked')?.value || 'dine_in';
    this.updateCheckoutSummary(this.getSubtotal(), serviceMethod);
    if (showMessage) window.showToast('Kode promo dihapus.', 'info');
  },

  // Haversine formula to compute distance in meters
  calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in metres
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return Math.round(R * c); // Distance in meters
  },

  updateCartBadge() {
    const totalQty = this.getTotalQty();
    const subtotal = this.getSubtotal();

    // Badges in navbar
    document.querySelectorAll('.cart-count-badge').forEach(el => {
      el.textContent = totalQty;
      el.style.display = totalQty > 0 ? 'inline-flex' : 'none';
    });

    // Mobile floating bar
    const floatingBar = document.getElementById('cart-floating-bar');
    if (floatingBar) {
      if (totalQty > 0) {
        floatingBar.style.display = 'flex';
        const countEl = floatingBar.querySelector('.floating-cart-count');
        const totalEl = floatingBar.querySelector('.floating-cart-total');
        if (countEl) countEl.textContent = `${totalQty} menu`;
        if (totalEl) totalEl.textContent = window.formatRupiah(subtotal);
      } else {
        floatingBar.style.display = 'none';
      }
    }
  },

  renderCartDrawer() {
    const container = document.getElementById('cart-drawer-items');
    if (!container) return;

    const items = this.getItems();
    if (items.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 40px 10px; color: var(--text-muted);">
          <svg style="width: 48px; height: 48px; margin: 0 auto 12px auto; opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          <p style="font-weight: 500;">Keranjang belanja Anda masih kosong.</p>
          <p class="text-sm" style="margin-top: 4px;">Pilih menu lezat kami untuk mulai memesan!</p>
        </div>
      `;
      this.updateCheckoutSummary(0, 'dine_in');
      return;
    }

    let html = '<div class="cart-items-list" style="display: flex; flex-direction: column; gap: 12px;">';
    items.forEach(item => {
      const itemSubtotal = (item.price + (item.modifierTotal || 0)) * item.qty;
      html += `
        <div class="cart-item-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; display: flex; flex-direction: column; gap: 6px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="font-weight: 600; font-size: 0.95rem; color: var(--text);">${escapeHtml(item.name)}</div>
            <button type="button" onclick="Cart.removeItem('${item.key}')" style="color: var(--text-dim); padding: 2px 6px;" title="Hapus menu">&times;</button>
          </div>
          
          ${(item.variantName || item.modifiers?.length || item.cut || item.drink) ? `
            <div class="text-xs" style="color: var(--text-muted); display: flex; gap: 8px; flex-wrap: wrap;">
              ${item.variantName ? `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 8px; font-size: 0.75rem; border: 1px solid var(--border);">Varian: ${escapeHtml(item.variantName)}</span>` : ''}
              ${(item.modifiers || []).map(modifier => `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 8px; font-size: 0.75rem; border: 1px solid var(--border);">${escapeHtml(modifier.groupName)}: ${escapeHtml(modifier.optionName)}</span>`).join('')}
              ${item.cut ? `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 8px; font-size: 0.75rem; border: 1px solid var(--border); text-transform: uppercase; font-weight: 600;">Potongan: ${escapeHtml(item.cut)}</span>` : ''}
              ${item.drink ? `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 8px; font-size: 0.75rem; border: 1px solid var(--border); text-transform: uppercase; font-weight: 600;">Minuman: ${escapeHtml(item.drink)}</span>` : ''}
            </div>
          ` : ''}

          ${item.note ? `
            <div class="text-xs" style="color: var(--warning); font-style: italic;">
              Catatan: ${escapeHtml(item.note)}
            </div>
          ` : ''}

          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
            <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
              ${window.formatRupiah((item.price + (item.modifierTotal || 0)) * item.qty)}
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 2px 8px;">
              <button type="button" onclick="Cart.updateQty('${item.key}', -1)" style="font-weight: bold; padding: 2px 6px;">-</button>
              <span style="font-size: 0.9rem; font-weight: 600; min-width: 20px; text-align: center;">${item.qty}</span>
              <button type="button" onclick="Cart.updateQty('${item.key}', 1)" style="font-weight: bold; padding: 2px 6px;">+</button>
            </div>
          </div>
        </div>
      `;
    });
    html += '</div>';

    container.innerHTML = html;

    const serviceSelect = document.querySelector('input[name="service_method"]:checked');
    const serviceMethod = serviceSelect ? serviceSelect.value : 'dine_in';
    this.updateCheckoutSummary(this.getSubtotal(), serviceMethod);
  },

  updateCheckoutSummary(subtotal, serviceMethod) {
    const packagingFee = serviceMethod === 'takeaway' ? this.settings.packagingFee : 0;
    const discount = this.settings.promoApplied?.valid ? Number(this.settings.promoApplied.discount || 0) : 0;
    const total = Math.max(0, subtotal + packagingFee - discount);
    const totalQty = this.getTotalQty();

    const subtotalEl = document.getElementById('checkout-subtotal');
    const feeRowEl = document.getElementById('checkout-fee-row');
    const feeEl = document.getElementById('checkout-fee');
    const discountRowEl = document.getElementById('checkout-discount-row');
    const discountEl = document.getElementById('checkout-discount');
    const totalEl = document.getElementById('checkout-total');
    const warningEl = document.getElementById('checkout-order-warning');
    const closedWarningEl = document.getElementById('checkout-closed-warning');
    const checkoutBtn = document.getElementById('btn-checkout-wa');

    if (subtotalEl) subtotalEl.textContent = window.formatRupiah(subtotal);
    if (feeRowEl) feeRowEl.style.display = packagingFee > 0 ? 'flex' : 'none';
    if (feeEl) feeEl.textContent = window.formatRupiah(packagingFee);
    if (discountRowEl) discountRowEl.style.display = discount > 0 ? 'flex' : 'none';
    if (discountEl) discountEl.textContent = '-' + window.formatRupiah(discount);
    if (totalEl) totalEl.textContent = window.formatRupiah(total);

    // Business Rule 04: >= 20 boxes warning
    if (warningEl) {
      if (totalQty >= 20) {
        warningEl.style.display = 'block';
        warningEl.innerHTML = `
          <strong>Peringatan Pemesanan Rombongan (${totalQty} porsi):</strong><br>
          Pemesanan &ge; 20 porsi diarahkan untuk negosiasi jadwal melalui chat WA.
        `;
      } else {
        warningEl.style.display = 'none';
      }
    }

    // Business Rule 05: Store Tutup warning
    if (closedWarningEl) {
      if (!this.settings.isOpen) {
        closedWarningEl.style.display = 'block';
        closedWarningEl.innerHTML = `
          <strong>Kedai Sedang Tutup:</strong> 
          Saat ini kedai sedang berstatus Tutup. Anda tetap dapat mengirimkan pesan ke WhatsApp untuk menanyakan jadwal buka berikutnya.
        `;
      } else {
        closedWarningEl.style.display = 'none';
      }
    }

    if (checkoutBtn) {
      checkoutBtn.disabled = totalQty === 0;
    }
  },

  // WhatsApp Message Composer (FR-10, BR-01)
  captureDeliveryLocation() {
    if (!navigator.geolocation) {
      window.showToast('Lokasi perangkat tidak didukung browser ini.', 'error');
      return;
    }
    navigator.geolocation.getCurrentPosition(position => {
      document.getElementById('delivery-lat').value = position.coords.latitude;
      document.getElementById('delivery-lng').value = position.coords.longitude;
      window.showToast('Lokasi pengantaran berhasil diambil.', 'success');
    }, () => {
      window.showToast('Lokasi perangkat tidak dapat diakses.', 'error');
    }, {enableHighAccuracy: true, timeout: 10000});
  },

  async checkoutWhatsApp() {
    const items = this.getItems();
    if (items.length === 0) {
      window.showToast('Keranjang Anda masih kosong!', 'error');
      return;
    }

    const customerNameInput = document.getElementById('customer-name');
    const customerPhoneInput = document.getElementById('customer-phone');
    const customerAddressInput = document.getElementById('customer-address');
    const tableNumberInput = document.getElementById('table-number');
    const deliveryLatInput = document.getElementById('delivery-lat');
    const deliveryLngInput = document.getElementById('delivery-lng');

    const customerName = customerNameInput ? customerNameInput.value.trim() : '';
    const customerPhone = customerPhoneInput ? customerPhoneInput.value.trim() : '';
    const customerAddress = customerAddressInput ? customerAddressInput.value.trim() : '';
    const tableNumber = tableNumberInput ? tableNumberInput.value.trim() : '';

    if (!customerName) {
      window.showToast('Harap masukkan nama pemesan terlebih dahulu.', 'error');
      if (customerNameInput) customerNameInput.focus();
      return;
    }

    const serviceSelect = document.querySelector('input[name="service_method"]:checked');
    const serviceMethod = serviceSelect ? serviceSelect.value : 'dine_in';
    const paymentSelect = document.querySelector('input[name="payment_method"]:checked');
    const paymentMethod = paymentSelect ? paymentSelect.value : null;

    if (!paymentMethod) {
      window.showToast('Pilih metode pembayaran terlebih dahulu.', 'error');
      return;
    }
    if (paymentMethod === 'cash_pickup' && !['takeaway', 'dine_in'].includes(serviceMethod)) {
      window.showToast('Cash on Pickup hanya untuk pesanan bawa pulang atau makan di tempat.', 'error');
      return;
    }
    if (paymentMethod === 'cod' && serviceMethod !== 'delivery') {
      window.showToast('Cash on Delivery hanya untuk pesanan delivery.', 'error');
      return;
    }

    if (serviceMethod === 'delivery') {
      if (!customerAddress) {
        window.showToast('Harap cantumkan alamat lengkap untuk pengantaran.', 'error');
        if (customerAddressInput) customerAddressInput.focus();
        return;
      }
      if (!deliveryLatInput?.value || !deliveryLngInput?.value) {
        window.showToast('Ambil lokasi perangkat untuk memvalidasi radius delivery.', 'error');
        return;
      }
    }

    // Information Architecture: Collect Order Notes (Priority Service, Add Cutlery, Birthday Message, Table Change)
    const selectedNoteTags = Array.from(document.querySelectorAll('input[name="order_note_tag"]:checked'))
      .map(cb => cb.value);
    const orderNotes = selectedNoteTags.join(', ');

    const checkoutButton = document.getElementById('btn-checkout-wa');
    if (checkoutButton) checkoutButton.disabled = true;
    const checkoutWindow = window.open('about:blank', '_blank');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const ordersUrl = document.querySelector('meta[name="orders-url"]')?.content;

    try {
      if (!csrfToken || !ordersUrl) {
        throw new Error('Informasi checkout belum tersedia. Muat ulang halaman lalu coba lagi.');
      }

      if (!this.settings.checkoutKey) {
        this.settings.checkoutKey = window.crypto?.randomUUID?.()
          || `checkout-${Date.now()}-${Math.random().toString(36).slice(2)}`;
      }

      const response = await fetch(ordersUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          customer_name: customerName,
          customer_phone: customerPhone || null,
          checkout_key: this.settings.checkoutKey,
          service_method: serviceMethod,
          table_number: (serviceMethod === 'dine_in' && tableNumber) ? tableNumber : null,
          customer_address: customerAddress || null,
          delivery_lat: deliveryLatInput?.value ? Number(deliveryLatInput.value) : null,
          delivery_lng: deliveryLngInput?.value ? Number(deliveryLngInput.value) : null,
          payment_method: paymentMethod,
          promo_code: this.settings.promoCode || null,
          order_notes: orderNotes || null,
          items: items.map(item => ({
            id: item.id,
            qty: item.qty,
            variant_id: item.variantId,
            modifier_option_ids: (item.modifiers || []).map(modifier => modifier.optionId),
            cut: item.cut,
            drink: item.drink,
            note: item.note
          }))
        })
      });
      const result = await response.json();

      if (!response.ok) {
        const firstError = result.errors ? Object.values(result.errors)[0]?.[0] : null;
        throw new Error(firstError || result.message || 'Pesanan belum dapat disimpan.');
      }

      const receipt = {
        ...result,
        checkout_key: this.settings.checkoutKey
      };
      this.settings.lastCheckout = receipt;
      this.saveCheckoutReceipt(receipt);

      const waUrl = result.whatsapp_url;
      if (checkoutWindow) {
        checkoutWindow.location = waUrl;
      }
      this.clearCart();
      this.showCheckoutResult(result);
      window.showToast(`Pesanan ${result.order_number} tersimpan. Lanjutkan konfirmasi di WhatsApp.`, 'success');
    } catch (error) {
      if (checkoutWindow) checkoutWindow.close();
      this.settings.checkoutKey = null;
      console.error('Order checkout failed.', error);
      window.showToast(error.message || 'Pesanan gagal disimpan. Coba lagi.', 'error');
    } finally {
      if (checkoutButton) checkoutButton.disabled = false;
    }
  }
};

function escapeHtml(text) {
  if (!text) return '';
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

window.Cart = Cart;
