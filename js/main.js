/* ══════════════════════════════════════════════════════
   NEXUS STORE — Main JavaScript
   ══════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // ── Mobile Nav Toggle ──────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      hamburger.setAttribute('aria-expanded', isOpen);
      hamburger.querySelectorAll('span').forEach((s, i) => {
        if (isOpen) {
          if (i === 0) s.style.transform = 'rotate(45deg) translate(5px, 5px)';
          if (i === 1) s.style.opacity = '0';
          if (i === 2) s.style.transform = 'rotate(-45deg) translate(5px, -5px)';
        } else {
          s.style.transform = '';
          s.style.opacity   = '';
        }
      });
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
        hamburger.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
      }
    });
  }

  // ── Category Filter (Home Page) ────────────────────────
  const catBtns   = document.querySelectorAll('.cat-btn');
  const productCards = document.querySelectorAll('.product-card[data-cat]');

  catBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      catBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.cat;
      productCards.forEach(card => {
        if (cat === 'all' || card.dataset.cat === cat) {
          card.style.display = '';
          card.style.animation = 'fadeIn 0.3s ease both';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // ── Live Search Filter ─────────────────────────────────
  const searchInput = document.getElementById('searchInput');
  if (searchInput && productCards.length > 0) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase().trim();
      productCards.forEach(card => {
        const name = (card.querySelector('.product-name')?.textContent || '').toLowerCase();
        const cat  = (card.querySelector('.product-cat')?.textContent  || '').toLowerCase();
        const visible = name.includes(q) || cat.includes(q);
        card.style.display = visible ? '' : 'none';
      });
    });
  }

  // ── Navbar search form submission ──────────────────────
  const navSearchInput = document.getElementById('navSearchInput');
  const navSearchForm  = document.getElementById('navSearchForm');
  if (navSearchForm) {
    navSearchForm.addEventListener('submit', (e) => {
      const q = navSearchInput?.value?.trim();
      if (!q) e.preventDefault();
    });
  }

  // ── Quantity Selector (Product Detail) ─────────────────
  const qtyDisplay = document.getElementById('qtyDisplay');
  const qtyInput   = document.getElementById('qtyInput');
  const qtyMinus   = document.getElementById('qtyMinus');
  const qtyPlus    = document.getElementById('qtyPlus');
  const stockMax   = parseInt(document.getElementById('stockMax')?.value || '99', 10);

  if (qtyMinus && qtyPlus && qtyDisplay && qtyInput) {
    qtyMinus.addEventListener('click', () => {
      let v = parseInt(qtyInput.value, 10);
      if (v > 1) {
        qtyInput.value = v - 1;
        qtyDisplay.textContent = v - 1;
      }
    });
    qtyPlus.addEventListener('click', () => {
      let v = parseInt(qtyInput.value, 10);
      if (v < stockMax) {
        qtyInput.value = v + 1;
        qtyDisplay.textContent = v + 1;
      }
    });
  }

  // ── Cart Quantity Inline Update ────────────────────────
  document.querySelectorAll('.cart-qty-input').forEach(input => {
    input.addEventListener('change', function () {
      const form = this.closest('form');
      if (form) form.submit();
    });
  });

  // ── Toast Notifications ────────────────────────────────
  window.showToast = function (message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icons[type] || 'ℹ'}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'toastOut 0.3s ease forwards';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  // Auto-show flash messages from PHP as toasts
  const flashEl = document.getElementById('flashMessage');
  if (flashEl) {
    const type = flashEl.dataset.type || 'info';
    const msg  = flashEl.dataset.message || '';
    if (msg) showToast(msg, type);
  }

  // ── Scroll Reveal Animation ────────────────────────────
  if ('IntersectionObserver' in window) {
    const revealEls = document.querySelectorAll('.product-card, .stat-card, .admin-card');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    revealEls.forEach(el => io.observe(el));
  }

  // ── Admin Sidebar Mobile Toggle ────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const adminSidebar  = document.getElementById('adminSidebar');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', () => {
      adminSidebar.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
      if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        adminSidebar.classList.remove('open');
      }
    });
  }

  // ── Admin Modal (Product Add/Edit) ─────────────────────
  const openModalBtns  = document.querySelectorAll('[data-modal-open]');
  const closeModalBtns = document.querySelectorAll('[data-modal-close]');
  const modalOverlay   = document.getElementById('productModal');

  openModalBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.modalOpen);
      if (target) target.classList.add('open');
    });
  });

  closeModalBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.closest('.modal-overlay');
      if (target) target.classList.remove('open');
    });
  });

  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) modalOverlay.classList.remove('open');
    });
  }

  // ── Admin: Pre-fill Edit Modal ─────────────────────────
  document.querySelectorAll('.edit-product-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = btn.dataset;
      const form = document.getElementById('editProductForm');
      if (!form) return;
      form.querySelector('#edit_product_id').value  = data.id  || '';
      form.querySelector('#edit_name').value         = data.name || '';
      form.querySelector('#edit_category_id').value  = data.category || '';
      form.querySelector('#edit_price').value        = data.price || '';
      form.querySelector('#edit_stock').value        = data.stock || '';
      form.querySelector('#edit_description').value  = data.description || '';
      form.querySelector('#edit_image').value        = data.image || '';
      const featuredEl = form.querySelector('#edit_featured');
      if (featuredEl) featuredEl.checked = data.featured === '1';
      const modal = document.getElementById('editProductModal');
      if (modal) modal.classList.add('open');
    });
  });

  // ── Delete Confirm ─────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
        return false;
      }
    });
  });

  // ── Image Preview (Admin Upload) ───────────────────────
  const imageUrlInput = document.getElementById('product_image_url');
  const imagePreview  = document.getElementById('imagePreview');
  if (imageUrlInput && imagePreview) {
    imageUrlInput.addEventListener('input', () => {
      const url = imageUrlInput.value.trim();
      if (url) {
        imagePreview.src = url;
        imagePreview.style.display = 'block';
      } else {
        imagePreview.style.display = 'none';
      }
    });
    // Trigger on load if value exists
    if (imageUrlInput.value) {
      imagePreview.src = imageUrlInput.value;
      imagePreview.style.display = 'block';
    }
  }

  // ── Wishlist heart (cosmetic only) ────────────────────
  document.querySelectorAll('.product-wishlist').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      btn.classList.toggle('active');
      btn.textContent = btn.classList.contains('active') ? '♥' : '♡';
    });
  });

  // ── Number input: prevent non-numeric ─────────────────
  document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('keydown', (e) => {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','-','.'];
      if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
        e.preventDefault();
      }
    });
  });

  // ── Smooth page load fade-in ───────────────────────────
  document.body.style.opacity = '0';
  document.body.style.transition = 'opacity 0.3s ease';
  requestAnimationFrame(() => {
    document.body.style.opacity = '1';
  });

});
