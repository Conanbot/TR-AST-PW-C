window.showToast = function(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const label = document.createElement('span');
  label.textContent = message;
  toast.appendChild(label);

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(30px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
};

document.addEventListener('DOMContentLoaded', () => {
  const themeButtons = document.querySelectorAll('[data-theme-toggle]');
  const applyTheme = theme => {
    document.documentElement.dataset.theme = theme;
    try {
      localStorage.setItem('glo-theme', theme);
    } catch (error) {
      console.error('Theme preference could not be saved.', error);
    }
    themeButtons.forEach(button => {
      const isDark = theme === 'dark';
      button.setAttribute('aria-pressed', String(isDark));
      button.setAttribute('aria-label', isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap');
      button.title = isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap';
      const icon = button.querySelector('[data-theme-icon]');
      if (icon) {
        icon.classList.remove('fa-moon', 'fa-sun');
        icon.classList.add(isDark ? 'fa-sun' : 'fa-moon');
      }
    });
  };
  applyTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');
  themeButtons.forEach(button => button.addEventListener('click', () => {
    applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
  }));

  const navToggleBtn = document.getElementById('nav-toggle-btn');
  const mobileNav = document.getElementById('mobile-nav');
  const mobileNavClose = document.getElementById('mobile-nav-close');

  if (navToggleBtn && mobileNav) {
    navToggleBtn.addEventListener('click', () => {
      mobileNav.classList.add('open');
      document.body.style.overflow = 'hidden';
    });
  }

  if (mobileNavClose && mobileNav) {
    mobileNavClose.addEventListener('click', () => {
      mobileNav.classList.remove('open');
      document.body.style.overflow = '';
    });
  }
});

window.formatRupiah = function(number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(number);
};
