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
    storeLng: 110.4851234
  },

  init(settingsOverride = {}) {
    this.settings = { ...this.settings, ...settingsOverride };
    this.updateCartBadge();
    this.renderCartDrawer();
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
    } catch (e) {
      console.error('Error saving cart to localStorage', e);
    }
  },

  addItem(item) {
    // Unique key combines item id, chicken cut, and drink option
    const cutPart = item.cut ? item.cut.trim() : '';
    const drinkPart = item.drink ? item.drink.trim() : '';
    const key = `${item.id}-${cutPart}-${drinkPart}`;

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
        qty: item.qty,
        cut: item.cut || null,
        drink: item.drink || null,
        note: item.note || ''
      });
    }

    this.saveItems(items);
    window.showToast(`${item.name} berhasil ditambahkan ke keranjang!`, 'success');
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
    this.updateCartBadge();
    this.renderCartDrawer();
  },

  getSubtotal() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + (item.price * item.qty), 0);
  },

  getTotalQty() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + item.qty, 0);
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
      const itemSubtotal = item.price * item.qty;
      html += `
        <div class="cart-item-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; display: flex; flex-direction: column; gap: 6px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="font-weight: 600; font-size: 0.95rem; color: var(--text);">${escapeHtml(item.name)}</div>
            <button type="button" onclick="Cart.removeItem('${item.key}')" style="color: var(--text-dim); padding: 2px 6px;" title="Hapus menu">&times;</button>
          </div>
          
          ${(item.cut || item.drink) ? `
            <div class="text-xs" style="color: var(--text-muted); display: flex; gap: 8px; flex-wrap: wrap;">
              ${item.cut ? `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 6px; font-size: 0.7rem;">🍗 ${escapeHtml(item.cut)}</span>` : ''}
              ${item.drink ? `<span class="badge" style="background: rgba(255,255,255,0.06); padding: 2px 6px; font-size: 0.7rem;">🥤 ${escapeHtml(item.drink)}</span>` : ''}
            </div>
          ` : ''}

          ${item.note ? `
            <div class="text-xs" style="color: var(--warning); font-style: italic;">
              Catatan: ${escapeHtml(item.note)}
            </div>
          ` : ''}

          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
            <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
              ${window.formatRupiah(itemSubtotal)}
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
    const total = subtotal + packagingFee;
    const totalQty = this.getTotalQty();

    const subtotalEl = document.getElementById('checkout-subtotal');
    const feeRowEl = document.getElementById('checkout-fee-row');
    const feeEl = document.getElementById('checkout-fee');
    const totalEl = document.getElementById('checkout-total');
    const warningEl = document.getElementById('checkout-order-warning');
    const closedWarningEl = document.getElementById('checkout-closed-warning');
    const checkoutBtn = document.getElementById('btn-checkout-wa');

    if (subtotalEl) subtotalEl.textContent = window.formatRupiah(subtotal);
    if (feeRowEl) feeRowEl.style.display = packagingFee > 0 ? 'flex' : 'none';
    if (feeEl) feeEl.textContent = window.formatRupiah(packagingFee);
    if (totalEl) totalEl.textContent = window.formatRupiah(total);

    // Business Rule 04: >= 20 boxes warning
    if (warningEl) {
      if (totalQty >= 20) {
        warningEl.style.display = 'block';
        warningEl.innerHTML = `
          ⚠️ <strong>Pesanan Rombongan (${totalQty} porsi):</strong> 
          Pemesanan 20 kotak atau lebih akan diarahkan ke WhatsApp untuk konfirmasi ketersediaan & jadwal masak langsung dengan Ibu Catur.
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
          ⛔ <strong>Kedai Sedang Tutup:</strong> 
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
  checkoutWhatsApp() {
    const items = this.getItems();
    if (items.length === 0) {
      window.showToast('Keranjang Anda masih kosong!', 'error');
      return;
    }

    const customerNameInput = document.getElementById('customer-name');
    const customerAddressInput = document.getElementById('customer-address');
    const customerName = customerNameInput ? customerNameInput.value.trim() : '';
    const customerAddress = customerAddressInput ? customerAddressInput.value.trim() : '';

    if (!customerName) {
      window.showToast('Harap masukkan nama pemesan terlebih dahulu.', 'error');
      if (customerNameInput) customerNameInput.focus();
      return;
    }

    const serviceSelect = document.querySelector('input[name="service_method"]:checked');
    const serviceMethod = serviceSelect ? serviceSelect.value : 'dine_in';

    let serviceLabel = 'Dine-In (Makan di Tempat)';
    let packagingFee = 0;

    if (serviceMethod === 'takeaway') {
      packagingFee = this.settings.packagingFee;
      serviceLabel = `Takeaway (Bungkus / Bawa Pulang) (+${window.formatRupiah(packagingFee)} kemasan)`;
    } else if (serviceMethod === 'delivery') {
      serviceLabel = 'Pesan Antar (Delivery Kedai)';
      if (!customerAddress) {
        window.showToast('Harap cantumkan alamat lengkap untuk pengantaran.', 'error');
        if (customerAddressInput) customerAddressInput.focus();
        return;
      }
    }

    const subtotal = this.getSubtotal();
    const total = subtotal + packagingFee;

    // Compose formatted text according to PRD Bab 12
    let message = `Halo Geprek GLO, saya ingin memesan:\n\n`;

    items.forEach((item, index) => {
      let line = `${index + 1}. ${item.name} x${item.qty}`;
      const details = [];
      if (item.cut) details.push(`Potongan: ${item.cut}`);
      if (item.drink) details.push(`Minuman: ${item.drink}`);
      if (details.length > 0) {
        line += ` (${details.join('; ')})`;
      }
      line += ` – ${window.formatRupiah(item.price * item.qty)}`;
      message += line + `\n`;

      if (item.note) {
        message += `   Catatan: ${item.note}\n`;
      }
    });

    message += `\nLayanan: ${serviceLabel}\n`;
    message += `Subtotal: ${window.formatRupiah(subtotal)}\n`;
    if (packagingFee > 0) {
      message += `Biaya Kemasan: ${window.formatRupiah(packagingFee)}\n`;
    }
    message += `Total Pembayaran: ${window.formatRupiah(total)}\n\n`;
    message += `Nama Pemesan: ${customerName}\n`;
    if (serviceMethod === 'delivery' && customerAddress) {
      message += `Alamat Pengantaran: ${customerAddress}\n`;
    }
    message += `\nMohon konfirmasi pesanan saya. Terima kasih!`;

    const encoded = encodeURIComponent(message);
    const waUrl = `https://wa.me/${this.settings.waNumber}?text=${encoded}`;

    // Open WhatsApp in new tab
    window.open(waUrl, '_blank');
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
