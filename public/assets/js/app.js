(() => {
  'use strict';

  const qs = (selector, scope = document) => scope.querySelector(selector);
  const qsa = (selector, scope = document) => [...scope.querySelectorAll(selector)];
  const money = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

  const safeStorage = {
    get(key, fallback) {
      try {
        const value = localStorage.getItem(key);
        return value === null ? fallback : JSON.parse(value);
      } catch (_) {
        return fallback;
      }
    },
    set(key, value) {
      try { localStorage.setItem(key, JSON.stringify(value)); } catch (_) { /* private mode */ }
    }
  };

  function showToast(message, type = 'success') {
    if (window.Swal) {
      const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2600,
        timerProgressBar: true
      });
      toast.fire({
        icon: type === 'error' ? 'error' : (type === 'info' ? 'info' : 'success'),
        title: message
      });
      return;
    }

    const toastEl = qs('#site-toast');
    if (!toastEl || typeof bootstrap === 'undefined') return;
    const icon = qs('[data-toast-icon]', toastEl);
    const body = qs('[data-toast-message]', toastEl);
    if (body) body.textContent = message;
    if (icon) {
      icon.className = type === 'success'
        ? 'bi bi-check-circle-fill text-primary me-2'
        : 'bi bi-info-circle-fill text-primary me-2';
    }
    bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2600 }).show();
  }

  function updateHeaderCounts() {
    // Số lượng được render từ server và cập nhật sau các request thành công.
  }

  function initStickyNavigation() {
    const navbar = qs('[data-site-navbar]');
    if (!navbar) return;
    const update = () => navbar.classList.toggle('is-sticky', window.scrollY > 110);
    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initMegaMenus() {
    qsa('[data-mega-menu]').forEach(menu => {
      const tabs = qsa('[data-mega-tab]', menu);
      const panels = qsa('[data-mega-panel]', menu);
      if (!tabs.length || !panels.length) return;

      const activate = key => {
        tabs.forEach(tab => tab.classList.toggle('is-active', tab.dataset.megaTab === key));
        panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.megaPanel === key));
      };

      tabs.forEach(tab => {
        tab.addEventListener('mouseenter', () => activate(tab.dataset.megaTab));
        tab.addEventListener('focus', () => activate(tab.dataset.megaTab));
      });
    });
  }

  function initHomeHeroSwiper() {
    if (typeof window.Swiper === 'undefined') return;

    qsa('[data-home-hero-swiper]').forEach(slider => {
      const slideCount = Number(slider.dataset.slideCount || 0);
      const hasMultipleSlides = slideCount > 1;

      new window.Swiper(slider, {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: hasMultipleSlides,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: 700,
        grabCursor: hasMultipleSlides,
        watchOverflow: true,
        observer: true,
        observeParents: true,
        keyboard: { enabled: true },
        a11y: {
          enabled: true,
          prevSlideMessage: 'Slide trước',
          nextSlideMessage: 'Slide sau',
        },
        autoplay: hasMultipleSlides ? {
          delay: 6000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true
        } : false,
        pagination: hasMultipleSlides ? {
          el: qs('.swiper-pagination', slider),
          clickable: true
        } : false,
        navigation: hasMultipleSlides ? {
          prevEl: qs('.home-hero-prev', slider),
          nextEl: qs('.home-hero-next', slider)
        } : false
      });
    });
  }

  function initHomeAdviceSwiper() {
    if (typeof window.Swiper === 'undefined') return;

    qsa('[data-home-advice-swiper]').forEach(slider => {
      const slideCount = Number(slider.dataset.slideCount || 0);
      const hasMultipleSlides = slideCount > 1;

      new window.Swiper(slider, {
        slidesPerView: 1,
        spaceBetween: 18,
        loop: hasMultipleSlides,
        speed: 650,
        watchOverflow: true,
        keyboard: { enabled: true },
        autoplay: hasMultipleSlides ? {
          delay: 5500,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        } : false,
        pagination: hasMultipleSlides ? {
          el: qs('.home-advice-pagination', slider),
          clickable: true,
        } : false,
        navigation: hasMultipleSlides ? {
          prevEl: qs('.home-advice-prev', slider),
          nextEl: qs('.home-advice-next', slider),
        } : false,
        a11y: {
          enabled: true,
          prevSlideMessage: 'Nhóm bài viết trước',
          nextSlideMessage: 'Nhóm bài viết sau',
        },
      });
    });
  }

  function initHomeTestimonialsSwiper() {
    if (typeof window.Swiper === 'undefined') return;

    qsa('[data-home-testimonials-swiper]').forEach(slider => {
      const slideCount = Number(slider.dataset.slideCount || 0);
      const hasMultipleSlides = slideCount > 1;

      new window.Swiper(slider, {
        slidesPerView: 1,
        spaceBetween: 12,
        autoHeight: false,
        loop: slideCount > 3,
        speed: 700,
        grabCursor: hasMultipleSlides,
        watchOverflow: true,
        keyboard: { enabled: true },
        autoplay: hasMultipleSlides ? {
          delay: 6000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        } : false,
        pagination: hasMultipleSlides ? {
          el: qs('.home-testimonials-pagination', slider),
          clickable: true,
        } : false,
        navigation: hasMultipleSlides ? {
          prevEl: qs('.home-testimonials-prev', slider),
          nextEl: qs('.home-testimonials-next', slider),
        } : false,
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 16,
          },
          1200: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
        },
        a11y: {
          enabled: true,
          prevSlideMessage: 'Cảm nhận trước',
          nextSlideMessage: 'Cảm nhận tiếp theo',
        },
      });
    });
  }

  function initFlashSaleSwiper() {
    if (typeof window.Swiper === 'undefined') return;

    qsa('[data-flash-sale-swiper]').forEach(slider => {
      const slideCount = Number(slider.dataset.slideCount || 0);
      const hasMultipleSlides = slideCount > 1;

      new window.Swiper(slider, {
        slidesPerView: 1.25,
        spaceBetween: 12,
        speed: 650,
        grabCursor: hasMultipleSlides,
        watchOverflow: true,
        rewind: hasMultipleSlides,
        autoplay: hasMultipleSlides ? {
          delay: 4500,
          disableOnInteraction: false,
          pauseOnMouseEnter: true
        } : false,
        navigation: hasMultipleSlides ? {
          prevEl: qs('.flash-sale-prev', slider),
          nextEl: qs('.flash-sale-next', slider)
        } : false,
        breakpoints: {
          480: { slidesPerView: 1.7 },
          576: { slidesPerView: 2.2 },
          768: { slidesPerView: 3 },
          992: { slidesPerView: 4 },
          1200: { slidesPerView: 5 }
        }
      });
    });
  }

  function initProductGallerySwiper() {
    if (typeof window.Swiper === 'undefined') return;

    qsa('[data-product-gallery]').forEach(gallery => {
      const main = qs('[data-product-gallery-main]', gallery);
      const thumbs = qs('[data-product-gallery-thumbs]', gallery);
      if (!main) return;

      const slideCount = qsa('.swiper-slide', main).length;
      if (slideCount < 2) return;

      const thumbsSwiper = thumbs
        ? new window.Swiper(thumbs, {
          slidesPerView: 4,
          spaceBetween: 8,
          freeMode: true,
          watchSlidesProgress: true,
          slideToClickedSlide: true,
          breakpoints: {
            576: { slidesPerView: 5 },
            992: { slidesPerView: 6 }
          }
        })
        : null;

      new window.Swiper(main, {
        slidesPerView: 1,
        spaceBetween: 12,
        speed: 400,
        grabCursor: true,
        keyboard: { enabled: true },
        navigation: {
          prevEl: qs('[data-product-gallery-prev]', gallery),
          nextEl: qs('[data-product-gallery-next]', gallery)
        },
        ...(thumbsSwiper ? { thumbs: { swiper: thumbsSwiper } } : {})
      });
    });
  }

  function initBackToTop() {
    const button = qs('[data-back-to-top]');
    if (!button) return;
    const update = () => button.classList.toggle('is-visible', window.scrollY > 500);
    update();
    window.addEventListener('scroll', update, { passive: true });
    button.addEventListener('click', event => {
      event.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  function initCountdowns() {
    qsa('[data-countdown]').forEach(timer => {
      let deadline = timer.dataset.deadline ? new Date(timer.dataset.deadline).getTime() : NaN;
      if (!Number.isFinite(deadline) || deadline <= Date.now()) {
        const now = new Date();
        deadline = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 2, 23, 59, 59).getTime();
      }

      const render = () => {
        const distance = Math.max(0, deadline - Date.now());
        const days = Math.floor(distance / 86400000);
        const hours = Math.floor((distance % 86400000) / 3600000);
        const minutes = Math.floor((distance % 3600000) / 60000);
        const seconds = Math.floor((distance % 60000) / 1000);
        const values = { days, hours, minutes, seconds };
        Object.entries(values).forEach(([key, value]) => {
          const target = qs(`[data-${key}]`, timer);
          if (target) target.textContent = String(value).padStart(2, '0');
        });
      };
      render();
      window.setInterval(render, 1000);
    });
  }

  function initVoucherCopy() {
    qsa('[data-copy-code]').forEach(button => {
      button.addEventListener('click', async () => {
        const code = button.dataset.copyCode || '';
        try {
          await navigator.clipboard.writeText(code);
          const original = button.innerHTML;
          button.innerHTML = '<i class="bi bi-check2 me-1"></i>Đã chép';
          showToast(`Đã sao chép mã ${code}`);
          window.setTimeout(() => { button.innerHTML = original; }, 1800);
        } catch (_) {
          showToast(`Mã ưu đãi: ${code}`, 'info');
        }
      });
    });
  }

  function initWishlist() {
    qsa('[data-wishlist]').forEach(button => {
      button.addEventListener('click', async event => {
        event.preventDefault();
        const id = button.dataset.wishlist;
        const response = await fetch(`/yeu-thich/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': qs('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' } });
        if (response.redirected || response.status === 401) { window.location.href = '/dang-nhap'; return; }
        if (!response.ok) { showToast('Không thể cập nhật danh sách yêu thích', 'info'); return; }
        const data = await response.json();
        button.classList.toggle('is-active', data.active);
        button.setAttribute('aria-pressed', data.active ? 'true' : 'false');
        const icon = qs('i', button); if (icon) icon.className = data.active ? 'bi bi-heart-fill' : 'bi bi-heart';
        qsa('[data-wishlist-count]').forEach(el => { el.textContent = String(data.count); });
        showToast(data.active ? 'Đã thêm vào danh sách yêu thích' : 'Đã bỏ khỏi danh sách yêu thích', 'info');
      });
    });
  }

  function initAddToCart() {
    qsa('[data-add-cart]').forEach(button => {
      button.addEventListener('click', async event => {
        event.preventDefault();
        const form = button.dataset.purchaseForm
          ? qs(button.dataset.purchaseForm)
          : button.closest('form');
        const quantityTarget = button.dataset.quantityTarget
          ? qs(button.dataset.quantityTarget, form || document)
          : null;
        const quantity = Math.max(1, Number(quantityTarget?.value || 1));
        const variantInput = form
          ? (qs('[name="variant_id"]:checked', form) || qs('[data-selected-variant]', form))
          : null;
        const hasVariantOption = form ? !!qs('[name="variant_id"]', form) : false;
        const buttonVariantId = button.dataset.variantId || '';

        if (hasVariantOption && (!variantInput || !variantInput.value)) {
          showToast('Vui lòng chọn phân loại sản phẩm', 'info');
          return;
        }
        const comboId = button.dataset.comboId || qs('[name="combo_id"]', form)?.value;
        const productId = button.dataset.productId || qs('[name="product_id"]', form)?.value;
        if (!productId && !comboId) {
          showToast('Không xác định được sản phẩm hoặc Combo', 'info');
          return;
        }
        const buyNow = button.hasAttribute('data-buy-now');
        const payload = { quantity, action: buyNow ? 'buy_now' : 'add_to_cart' };
        if (comboId) payload.combo_id = comboId;
        else payload.product_id = productId;
        if (variantInput?.value) payload.variant_id = variantInput.value;
        else if (buttonVariantId) payload.variant_id = buttonVariantId;

        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${buyNow ? 'Đang chuyển' : 'Đang thêm'}`;
        try {
          const response = await fetch('/gio-hang', { method: 'POST', headers: { 'X-CSRF-TOKEN': qs('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) });
          const data = await response.json();
          if (!response.ok) {
            showToast(Object.values(data.errors || {}).flat()[0] || data.message || 'Không thể thêm sản phẩm', 'info');
            return;
          }
          qsa('[data-cart-count]').forEach(el => { el.textContent = String(data.count); });
          if (buyNow && data.redirect) {
            window.location.assign(data.redirect);
            return;
          }
          showCartConfirmation(data.product_name || button.dataset.productName || 'Sản phẩm');
        } catch (_) {
          showToast('Không thể kết nối để thêm sản phẩm. Vui lòng thử lại.', 'error');
        } finally {
          button.disabled = false;
          button.innerHTML = original;
        }
      });
    });
  }

  function showCartConfirmation(productName) {
    const panel = qs('[data-cart-confirmation]');
    if (!panel) {
      showToast(`Đã thêm ${productName} vào giỏ`);
      return;
    }
    const name = qs('[data-cart-confirmation-name]', panel);
    if (name) name.textContent = productName;
    panel.classList.add('is-visible');
    panel.setAttribute('aria-hidden', 'false');
    window.clearTimeout(panel._hideTimer);
    panel._hideTimer = window.setTimeout(() => {
      panel.classList.remove('is-visible');
      panel.setAttribute('aria-hidden', 'true');
    }, 6500);
  }

  function initCartConfirmation() {
    const panel = qs('[data-cart-confirmation]');
    const close = qs('[data-cart-confirmation-close]', panel || document);
    close?.addEventListener('click', () => {
      panel?.classList.remove('is-visible');
      panel?.setAttribute('aria-hidden', 'true');
    });
  }

  function initQuickView() {
    const modalEl = qs('#quickViewModal');
    if (!modalEl) return;
    modalEl.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      if (!button) return;
      const image = qs('[data-quick-image]', modalEl);
      const brand = qs('[data-quick-brand]', modalEl);
      const title = qs('[data-quick-title]', modalEl);
      const price = qs('[data-quick-price]', modalEl);
      const oldPrice = qs('[data-quick-old-price]', modalEl);
      const addButton = qs('[data-add-cart]', modalEl);
      const selectVariantLink = qs('[data-quick-select-variant]', modalEl);
      const wishlistButton = qs('[data-wishlist]', modalEl);
      if (image) { image.src = button.dataset.image || ''; image.alt = button.dataset.title || 'Sản phẩm'; }
      if (brand) brand.textContent = button.dataset.brand || '';
      if (title) title.textContent = button.dataset.title || '';
      if (price) price.textContent = button.dataset.price || '';
      if (oldPrice) {
        oldPrice.textContent = button.dataset.oldPrice || '';
        oldPrice.classList.toggle('d-none', !button.dataset.oldPrice);
      }
      if (addButton) addButton.dataset.productName = button.dataset.title || '';
      if (addButton) addButton.dataset.productId = button.dataset.productId || '';
      if (addButton) addButton.dataset.variantId = button.dataset.variantId || '';
      const canQuickAdd = button.dataset.canQuickAdd === '1' && button.dataset.availability !== 'out_of_stock';
      addButton?.classList.toggle('d-none', !canQuickAdd);
      selectVariantLink?.classList.toggle('d-none', canQuickAdd || button.dataset.availability === 'out_of_stock');
      if (selectVariantLink) selectVariantLink.href = button.dataset.detailUrl || '/san-pham';
      if (wishlistButton) wishlistButton.dataset.wishlist = button.dataset.productId || '';
    });
  }

  function initQuantityControls() {
    qsa('[data-quantity-control]').forEach(control => {
      const input = qs('input', control);
      if (!input) return;
      qsa('[data-quantity-action]', control).forEach(button => {
        button.addEventListener('click', () => {
          const current = Number(input.value) || 1;
          const next = button.dataset.quantityAction === 'increase' ? current + 1 : Math.max(1, current - 1);
          input.value = String(next);
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    });
  }

  function initGallery() {
    const mainImage = qs('[data-gallery-main]');
    if (!mainImage) return;
    qsa('[data-gallery-thumb]').forEach(button => {
      button.addEventListener('click', () => {
        mainImage.src = button.dataset.galleryThumb || '';
        qsa('[data-gallery-thumb]').forEach(item => item.classList.remove('active'));
        button.classList.add('active');
      });
    });
  }

  function initVariants() {
    const updateActionAvailability = (picker, variant, selectionComplete = true) => {
      const tracksInventory = picker.dataset.trackInventory === '1';
      const allowsPreorder = picker.dataset.allowPreorder === '1';
      const outOfStock = variant && tracksInventory && !allowsPreorder && Number(variant.stock) < 1;
      const unavailable = selectionComplete && (!variant || outOfStock);

      qsa('[data-variant-actions]').forEach(actions => actions.classList.toggle('d-none', unavailable));
    };

    const updateDisplayedPrice = (price, oldPrice = null) => {
      const priceBox = qs('[data-product-price]');
      const currentPrice = qs('[data-current-price]', priceBox);
      const comparePrice = qs('[data-compare-price]', priceBox);
      const discountBadge = qs('[data-discount-badge]', priceBox);
      const numericPrice = Number(price) || 0;
      const numericOldPrice = Number(oldPrice) || 0;
      const formatPrice = value => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}₫`;

      if (currentPrice) currentPrice.textContent = formatPrice(numericPrice);
      const mobilePrice = qs('[data-mobile-product-price]');
      if (mobilePrice) mobilePrice.textContent = formatPrice(numericPrice);
      if (comparePrice) {
        comparePrice.textContent = numericOldPrice > numericPrice ? formatPrice(numericOldPrice) : '';
        comparePrice.classList.toggle('d-none', numericOldPrice <= numericPrice);
      }
      if (discountBadge) {
        const discount = numericOldPrice > numericPrice
          ? Math.round((1 - numericPrice / numericOldPrice) * 100)
          : 0;
        discountBadge.textContent = discount > 0 ? `-${discount}%` : '';
        discountBadge.classList.toggle('d-none', discount <= 0);
      }
    };

    qsa('[data-variant-group]').forEach(group => {
      qsa('[data-variant]', group).forEach(button => {
        button.addEventListener('click', () => {
          qsa('[data-variant]', group).forEach(item => item.classList.remove('active'));
          button.classList.add('active');
          updateDisplayedPrice(button.dataset.price, button.dataset.comparePrice);
          updateActionAvailability(group, { stock: button.dataset.stock });
        });
      });

      const selectedVariant = qs('[name="variant_id"]:checked', group)?.closest('[data-variant]')
        || qs('[data-variant].active', group);
      if (selectedVariant) {
        updateDisplayedPrice(selectedVariant.dataset.price, selectedVariant.dataset.comparePrice);
        updateActionAvailability(group, { stock: selectedVariant.dataset.stock });
      }
    });

    qsa('[data-option-variant-picker]').forEach(picker => {
      const form = picker.closest('form');
      const groups = qsa('[data-option-group]', picker);
      const variantInput = qs('[data-selected-variant]', picker);
      const actions = qs('[data-variant-actions]', form);
      const priceBox = qs('[data-product-price]');
      const currentPrice = qs('[data-current-price]', priceBox);
      const comparePrice = qs('[data-compare-price]', priceBox);
      const discountBadge = qs('[data-discount-badge]', priceBox);
      const selected = new Map();
      let variants = [];

      try {
        variants = JSON.parse(qs('[data-variant-data]', picker)?.textContent || '[]');
      } catch (error) {
        variants = [];
      }

      const formatPrice = value => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}₫`;
      const matches = (variant, valueIds) => valueIds.every(id => variant.value_ids.map(Number).includes(Number(id)));

      const renderPrice = variant => {
        if (!currentPrice) return;

        if (!variant) {
          currentPrice.textContent = `Từ ${formatPrice(picker.dataset.minPrice)}`;
          comparePrice?.classList.add('d-none');
          discountBadge?.classList.add('d-none');
          return;
        }

        currentPrice.textContent = formatPrice(variant.price);
        const mobilePrice = qs('[data-mobile-product-price]');
        if (mobilePrice) mobilePrice.textContent = formatPrice(variant.price);
        if (comparePrice) {
          comparePrice.textContent = variant.compare_price ? formatPrice(variant.compare_price) : '';
          comparePrice.classList.toggle('d-none', !variant.compare_price);
        }
        if (discountBadge) {
          const discount = variant.compare_price
            ? Math.round((1 - Number(variant.price) / Number(variant.compare_price)) * 100)
            : 0;
          discountBadge.textContent = discount > 0 ? `-${discount}%` : '';
          discountBadge.classList.toggle('d-none', discount <= 0);
        }
      };

      const refresh = () => {
        const selectedIds = [...selected.values()];
        const matchedVariant = selectedIds.length === groups.length
          ? variants.find(variant => variant.value_ids.length === selectedIds.length && matches(variant, selectedIds))
          : null;

        if (variantInput) variantInput.value = matchedVariant?.id || '';
        updateActionAvailability(picker, matchedVariant, selectedIds.length === groups.length);
        renderPrice(matchedVariant);

        groups.forEach(group => {
          const optionId = group.dataset.optionId;
          qsa('[data-option-value]', group).forEach(button => {
            const candidate = new Map(selected);
            candidate.set(optionId, Number(button.dataset.valueId));
            const available = variants.some(variant => matches(variant, [...candidate.values()]));
            button.disabled = !available;
          });
        });
      };

      groups.forEach(group => {
        const optionId = group.dataset.optionId;
        qsa('[data-option-value]', group).forEach(button => {
          button.addEventListener('click', () => {
            qsa('[data-option-value]', group).forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            selected.set(optionId, Number(button.dataset.valueId));
            refresh();
          });
        });
      });

      refresh();
    });
  }

  function initForms() {
    qsa('[data-newsletter-form]').forEach(form => {
      form.addEventListener('submit', event => {
        event.preventDefault();
        const email = qs('input[type="email"]', form);
        if (!email?.value || !email.checkValidity()) {
          email?.reportValidity();
          return;
        }
        showToast('Đăng ký nhận tin thành công');
        form.reset();
      });
    });
  }

  function initCartRows() {
    qsa('[data-cart-row]').forEach(row => {
      const price = Number(row.dataset.unitPrice || 0);
      const quantityInput = qs('[data-cart-quantity]', row);
      const totalEl = qs('[data-cart-line-total]', row);
      const quantityForm = qs('[data-cart-quantity-form]', row);
      const removeForm = qs('[data-cart-remove-form]', row);

      quantityForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const requestedQuantity = event.submitter?.name === 'quantity'
          ? Number(event.submitter.value)
          : Number(quantityInput?.value || 1);
        await submitCartForm(quantityForm, { quantity: requestedQuantity }, data => {
          if (requestedQuantity < 1) row.remove();
          else {
            quantityInput.value = String(requestedQuantity);
            if (totalEl) totalEl.textContent = formatMoney(price * requestedQuantity);
            const buttons = qsa('button[name="quantity"]', quantityForm);
            if (buttons[0]) buttons[0].value = String(Math.max(0, requestedQuantity - 1));
            if (buttons[1]) buttons[1].value = String(requestedQuantity + 1);
          }
          applyCartResponse(data);
        });
      });
      quantityInput?.addEventListener('change', () => quantityForm?.requestSubmit());

      removeForm?.addEventListener('submit', async event => {
        event.preventDefault();
        await submitCartForm(removeForm, {}, data => {
          row.remove();
          applyCartResponse(data);
        });
      });
    });
  }

  const formatMoney = value => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}₫`;

  async function submitCartForm(form, values, onSuccess) {
    const body = new FormData(form);
    Object.entries(values).forEach(([key, value]) => body.set(key, String(value)));
    const submitters = qsa('button, input', form);
    submitters.forEach(element => { element.disabled = true; });

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body,
      });
      const data = await response.json();
      if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Không thể cập nhật giỏ hàng.');
      onSuccess(data);
      showToast(data.message || 'Đã cập nhật giỏ hàng.');
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      submitters.forEach(element => { element.disabled = false; });
    }
  }

  function applyCartResponse(data) {
    const summary = data.summary || {};
    qsa('[data-cart-count]').forEach(element => { element.textContent = String(data.count || 0); });
    if (qs('[data-cart-subtotal]')) qs('[data-cart-subtotal]').textContent = formatMoney(summary.subtotal);
    if (qs('[data-cart-discount]')) qs('[data-cart-discount]').textContent = `-${formatMoney(summary.discount)}`;
    if (qs('[data-cart-shipping]')) qs('[data-cart-shipping]').textContent = Number(summary.shipping) > 0 ? formatMoney(summary.shipping) : 'Miễn phí';
    if (qs('[data-cart-total]')) qs('[data-cart-total]').textContent = formatMoney(summary.total);

    const couponForm = qs('[data-cart-remove-coupon]');
    couponForm?.classList.toggle('d-none', !summary.coupon);
    const couponCode = qs('[data-cart-coupon-code]');
    if (couponCode) couponCode.textContent = summary.coupon?.code || '';

    const remain = Number(summary.freeShippingRemain) || 0;
    const percent = Number(summary.freeShippingPercent) || 0;
    if (qs('[data-shipping-message]')) qs('[data-shipping-message]').textContent = remain > 0 ? `Mua thêm ${formatMoney(remain)} để được miễn phí vận chuyển` : 'Đơn hàng đã được miễn phí vận chuyển';
    if (qs('[data-shipping-percent]')) qs('[data-shipping-percent]').textContent = `${percent}%`;
    if (qs('[data-shipping-progress]')) qs('[data-shipping-progress]').style.width = `${percent}%`;

    const unavailableCount = Array.isArray(summary.unavailableItems)
      ? summary.unavailableItems.length
      : Object.keys(summary.unavailableItems || {}).length;
    const checkoutControl = qs('[data-cart-checkout]');
    if (checkoutControl && unavailableCount === 0 && checkoutControl.tagName === 'BUTTON') {
      checkoutControl.outerHTML = '<a class="btn btn-primary w-100 py-3" href="/thanh-toan" data-cart-checkout><i class="bi bi-shield-lock me-2"></i>Tiến hành thanh toán</a>';
    }

    if (!qs('[data-cart-row]')) {
      const content = qs('[data-cart-content]');
      if (content) content.innerHTML = '<div class="content-card text-center py-5"><i class="bi bi-bag display-3 text-primary"></i><h2 class="h4 mt-3">Giỏ hàng đang trống</h2><p class="text-muted">Hãy chọn sản phẩm phù hợp để bắt đầu đơn hàng.</p><a class="btn btn-primary" href="/san-pham">Mua sắm ngay</a></div>';
    }
  }

  function initCartForms() {
    const couponForm = qs('[data-cart-coupon-form]');
    couponForm?.addEventListener('submit', async event => {
      event.preventDefault();
      await submitCartForm(couponForm, {}, applyCartResponse);
    });

    const removeCouponForm = qs('[data-cart-remove-coupon]');
    removeCouponForm?.addEventListener('submit', async event => {
      event.preventDefault();
      await submitCartForm(removeCouponForm, {}, applyCartResponse);
    });
  }

  function initCheckout() {
    const form = qs('[data-checkout-form]');
    if (!form) return;

    const invoiceToggle = qs('[data-invoice-toggle]', form);
    const invoiceFields = qs('[data-invoice-fields]', form);
    const syncInvoice = () => {
      const enabled = !!invoiceToggle?.checked;
      invoiceFields?.classList.toggle('d-none', !enabled);
      qsa('input', invoiceFields || document).forEach(input => { input.required = enabled; });
    };
    invoiceToggle?.addEventListener('change', syncInvoice);
    syncInvoice();

    const provinceSelect = qs('[data-shipping-province]', form);
    const wardSelect = qs('[data-shipping-ward]', form);
    const districtInput = qs('[name="shipping_district"]', form);
    const loadWards = async (provinceCode, selectedWard = '') => {
      if (!wardSelect) return;
      wardSelect.disabled = true;
      wardSelect.innerHTML = '<option value="">Đang tải phường/xã…</option>';
      if (!provinceCode) {
        wardSelect.innerHTML = '<option value="">Chọn tỉnh/thành trước</option>';
        return;
      }
      try {
        const response = await fetch(`https://provinces.open-api.vn/api/v2/w/?province=${encodeURIComponent(provinceCode)}`, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('location');
        const wards = await response.json();
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
        wards.forEach(ward => {
          const option = document.createElement('option');
          option.value = ward.name;
          option.textContent = ward.name;
          option.selected = ward.name === selectedWard;
          wardSelect.append(option);
        });
        if (selectedWard && !wards.some(ward => ward.name === selectedWard)) {
          const option = new Option(selectedWard, selectedWard, true, true);
          wardSelect.append(option);
        }
        wardSelect.disabled = false;
      } catch (_) {
        wardSelect.innerHTML = '<option value="">Không tải được danh mục phường/xã — vui lòng thử lại</option>';
        wardSelect.disabled = false;
        showToast('Chưa tải được danh mục phường/xã. Vui lòng kiểm tra kết nối.', 'info');
      }
    };

    provinceSelect?.addEventListener('change', () => {
      const option = provinceSelect.options[provinceSelect.selectedIndex];
      if (districtInput) districtInput.value = '';
      loadWards(option?.dataset.code || '', '');
    });
    if (provinceSelect?.value) {
      const option = provinceSelect.options[provinceSelect.selectedIndex];
      loadWards(option?.dataset.code || '', wardSelect?.dataset.selected || '');
    }

    const addressSelect = qs('[data-saved-address]', form);
    addressSelect?.addEventListener('change', () => {
      const option = addressSelect.options[addressSelect.selectedIndex];
      if (!option?.dataset.address) return;
      let address;
      try { address = JSON.parse(option.dataset.address); } catch (_) { return; }
      const setValue = (name, value) => { const field = qs(`[name="${name}"]`, form); if (field) field.value = value || ''; };
      setValue('customer_name', address.name);
      setValue('customer_phone', address.phone);
      setValue('shipping_address', address.address);
      if (districtInput) districtInput.value = address.district || '';
      if (provinceSelect) {
        provinceSelect.value = address.province || '';
        const provinceOption = provinceSelect.options[provinceSelect.selectedIndex];
        loadWards(provinceOption?.dataset.code || '', address.ward || '');
      }
    });

    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const button = qs('[data-checkout-submit]', form);
      const errorBox = qs('[data-checkout-errors]');
      qsa('.is-invalid', form).forEach(element => element.classList.remove('is-invalid'));
      qsa('[data-ajax-error]', form).forEach(element => element.remove());
      errorBox?.classList.add('d-none');
      button.disabled = true;
      const original = button.innerHTML;
      button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đặt hàng';

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok) {
          const errors = data.errors || {};
          Object.entries(errors).forEach(([name, messages]) => {
            const field = qs(`[name="${CSS.escape(name)}"]`, form);
            if (!field) return;
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.dataset.ajaxError = '';
            feedback.textContent = messages[0];
            field.insertAdjacentElement('afterend', feedback);
          });
          throw new Error(Object.values(errors).flat()[0] || data.message || 'Không thể đặt hàng.');
        }
        showToast(data.message || 'Đặt hàng thành công.');
        window.location.assign(data.redirect);
      } catch (error) {
        if (errorBox) {
          errorBox.textContent = error.message;
          errorBox.classList.remove('d-none');
          errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else showToast(error.message, 'error');
        button.disabled = false;
        button.innerHTML = original;
      }
    });
  }

  function initBootstrapHelpers() {
    if (typeof bootstrap === 'undefined') return;
    qsa('[data-bs-toggle="tooltip"]').forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));
  }

  function initGlightbox() {
    if (typeof window.GLightbox !== 'function' || !qs('.glightbox')) return;
    window.GLightbox({ selector: '.glightbox' });
  }

  function initCopyFields() {
    qsa('[data-copy-value]').forEach(button => {
      button.addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(button.dataset.copyValue || '');
          showToast('Đã sao chép', 'info');
        } catch (_) {
          showToast('Không thể sao chép tự động', 'info');
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateHeaderCounts();
    initBootstrapHelpers();
    initGlightbox();
    initStickyNavigation();
    initMegaMenus();
    initHomeHeroSwiper();
    initHomeAdviceSwiper();
    initHomeTestimonialsSwiper();
    initFlashSaleSwiper();
    initProductGallerySwiper();
    initBackToTop();
    initCountdowns();
    initVoucherCopy();
    initWishlist();
    initAddToCart();
    initCartConfirmation();
    initQuickView();
    initQuantityControls();
    initGallery();
    initVariants();
    initForms();
    initCartRows();
    initCartForms();
    initCheckout();
    initCopyFields();
  });
})();
