/**
 * KandaNews Ads Portal — ads.js
 * Vanilla JS for the self-service advertising portal
 */

'use strict';

/* ================================================================
   1. UTILITY HELPERS
   ================================================================ */

function qs(selector, scope) {
    return (scope || document).querySelector(selector);
}

function qsa(selector, scope) {
    return [...(scope || document).querySelectorAll(selector)];
}

function formatUGX(amount) {
    return 'UGX ' + Number(amount).toLocaleString('en-UG');
}

function show(el) {
    if (el) el.classList.remove('hidden');
}

function hide(el) {
    if (el) el.classList.add('hidden');
}

function setLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        btn._origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="kn-spinner"></span> Processing…';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn._origText || 'Submit';
    }
}

function showAlert(container, type, message) {
    if (!container) return;
    const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    container.innerHTML = `
        <div class="kn-alert kn-alert-${type}">
            <i class="fa-solid ${icons[type] || 'fa-circle-info'} kn-alert-icon"></i>
            <span>${message}</span>
        </div>`;
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ================================================================
   2. MOBILE NAVIGATION
   ================================================================ */

(function initMobileNav() {
    const hamburger = qs('#kn-hamburger');
    const mobileNav = qs('#kn-mobile-nav');
    if (!hamburger || !mobileNav) return;

    hamburger.addEventListener('click', function () {
        const isOpen = hamburger.classList.toggle('open');
        mobileNav.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen.toString());
        mobileNav.setAttribute('aria-hidden', (!isOpen).toString());
        document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!hamburger.contains(e.target) && !mobileNav.contains(e.target)) {
            hamburger.classList.remove('open');
            mobileNav.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
            mobileNav.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    });

    // Close on nav link click
    qsa('a', mobileNav).forEach(function (a) {
        a.addEventListener('click', function () {
            hamburger.classList.remove('open');
            mobileNav.classList.remove('open');
            document.body.style.overflow = '';
        });
    });
})();

/* ================================================================
   3. STICKY HEADER
   ================================================================ */

(function initStickyHeader() {
    const header = qs('#kn-header');
    if (!header) return;
    let lastY = 0;
    window.addEventListener('scroll', function () {
        const y = window.scrollY;
        if (y > 80) {
            header.style.boxShadow = '0 4px 24px rgba(0,0,0,0.25)';
        } else {
            header.style.boxShadow = '';
        }
        lastY = y;
    }, { passive: true });
})();

/* ================================================================
   4. BOOKING WIZARD
   ================================================================ */

(function initBookingWizard() {
    const wizard = qs('#kn-booking-wizard');
    if (!wizard) return;

    // State
    const state = {
        step: 1,
        totalSteps: 3,
        selectedFormat: null,
        selectedFormatLabel: '',
        selectedFormatPrice: 0,
        startDate: '',
        days: 1,
    };

    const panels     = qsa('.kn-wizard-panel', wizard);
    const stepNums   = qsa('.kn-wizard-step', wizard);
    const connectors = qsa('.kn-wizard-connector', wizard);

    function updateStepUI() {
        panels.forEach(function (p, i) {
            p.classList.toggle('active', i + 1 === state.step);
        });
        stepNums.forEach(function (s, i) {
            const num = i + 1;
            s.classList.remove('active', 'completed');
            if (num === state.step) s.classList.add('active');
            else if (num < state.step) s.classList.add('completed');
        });
        connectors.forEach(function (c, i) {
            c.classList.toggle('completed', i + 1 < state.step);
        });
    }

    function goTo(step) {
        if (step < 1 || step > state.totalSteps) return;
        state.step = step;
        updateStepUI();
        wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Step 1: Format selection
    const formatCards = qsa('.kn-format-card', wizard);
    formatCards.forEach(function (card) {
        card.addEventListener('click', function () {
            formatCards.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            state.selectedFormat      = card.dataset.formatKey;
            state.selectedFormatLabel = card.dataset.formatLabel;
            state.selectedFormatPrice = parseInt(card.dataset.formatPrice, 10);
            // Enable next btn
            const nextBtn = qs('#wizard-step1-next');
            if (nextBtn) nextBtn.disabled = false;
        });
    });

    qs('#wizard-step1-next')?.addEventListener('click', function () {
        if (!state.selectedFormat) {
            showAlert(qs('#step1-alert'), 'warning', 'Please select an ad format.');
            return;
        }
        updateReviewStep1();
        goTo(2);
    });

    // Step 2: Date selection
    const startDateInput = qs('#booking-start-date');
    const daysInput      = qs('#booking-days');
    const daysRange      = qs('#booking-days-range');

    // Set min date to tomorrow
    if (startDateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        startDateInput.min = tomorrow.toISOString().split('T')[0];
        startDateInput.value = tomorrow.toISOString().split('T')[0];
        state.startDate = startDateInput.value;

        startDateInput.addEventListener('change', function () {
            state.startDate = this.value;
            updatePriceCalculator();
        });
    }

    if (daysInput) {
        daysInput.addEventListener('input', function () {
            let v = parseInt(this.value, 10) || 1;
            if (v < 1) v = 1;
            if (v > 365) v = 365;
            this.value = v;
            state.days = v;
            if (daysRange) daysRange.value = v;
            updatePriceCalculator();
        });
    }

    if (daysRange) {
        daysRange.addEventListener('input', function () {
            const v = parseInt(this.value, 10);
            state.days = v;
            if (daysInput) daysInput.value = v;
            updatePriceCalculator();
        });
    }

    function calcPrice(unitPrice, days) {
        const subtotal = unitPrice * days;
        let discountPct = 0;
        if (days >= 30) discountPct = 20;
        else if (days >= 7) discountPct = 10;
        const discountAmt = Math.round(subtotal * discountPct / 100);
        const total = subtotal - discountAmt;
        return { subtotal, discountPct, discountAmt, total };
    }

    function updatePriceCalculator() {
        if (!state.selectedFormat) return;
        const p = calcPrice(state.selectedFormatPrice, state.days);

        const elSubtotal    = qs('#price-subtotal');
        const elDiscount    = qs('#price-discount');
        const elDiscountRow = qs('#price-discount-row');
        const elTotal       = qs('#price-total');
        const elUnitPrice   = qs('#price-unit');
        const elDays        = qs('#price-days');
        const elEndDate     = qs('#price-end-date');

        if (elUnitPrice)   elUnitPrice.textContent   = formatUGX(state.selectedFormatPrice);
        if (elDays)        elDays.textContent         = state.days + (state.days === 1 ? ' day' : ' days');
        if (elSubtotal)    elSubtotal.textContent     = formatUGX(p.subtotal);
        if (elTotal)       elTotal.textContent        = formatUGX(p.total);

        if (elDiscountRow && elDiscount) {
            if (p.discountPct > 0) {
                elDiscountRow.style.display = '';
                elDiscount.textContent = '-' + formatUGX(p.discountAmt) + ' (' + p.discountPct + '%)';
            } else {
                elDiscountRow.style.display = 'none';
            }
        }

        // End date
        if (elEndDate && state.startDate) {
            const end = new Date(state.startDate);
            end.setDate(end.getDate() + state.days - 1);
            elEndDate.textContent = end.toLocaleDateString('en-UG', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        // Store for form submission
        wizard.dataset.calculatedTotal = p.total;
    }

    qs('#wizard-step2-back')?.addEventListener('click', function () { goTo(1); });

    qs('#wizard-step2-next')?.addEventListener('click', function () {
        if (!startDateInput?.value) {
            showAlert(qs('#step2-alert'), 'warning', 'Please select a start date.');
            return;
        }
        const days = parseInt(daysInput?.value || 1, 10);
        if (days < 1 || days > 365) {
            showAlert(qs('#step2-alert'), 'warning', 'Days must be between 1 and 365.');
            return;
        }
        state.days = days;
        updateReviewStep3();
        goTo(3);
    });

    // Step 3: Review
    function updateReviewStep1() {
        // Update step 2 label showing format name
        const el = qs('#review-format-name');
        if (el) el.textContent = state.selectedFormatLabel;
        const elPrice = qs('#review-unit-price');
        if (elPrice) elPrice.textContent = formatUGX(state.selectedFormatPrice) + '/day';
        updatePriceCalculator();
    }

    function updateReviewStep3() {
        const p = calcPrice(state.selectedFormatPrice, state.days);

        const r = {
            'review-format':     state.selectedFormatLabel,
            'review-start-date': state.startDate,
            'review-days':       state.days + ' day' + (state.days === 1 ? '' : 's'),
            'review-unit-price': formatUGX(state.selectedFormatPrice) + '/day',
            'review-subtotal':   formatUGX(p.subtotal),
            'review-total':      formatUGX(p.total),
        };

        // End date
        const end = new Date(state.startDate);
        end.setDate(end.getDate() + state.days - 1);
        r['review-end-date'] = end.toLocaleDateString('en-UG', { day: 'numeric', month: 'short', year: 'numeric' });

        Object.entries(r).forEach(function ([id, val]) {
            const el = qs('#' + id);
            if (el) el.textContent = val;
        });

        const discountEl    = qs('#review-discount');
        const discountRow   = qs('#review-discount-row');
        if (discountEl && discountRow) {
            if (p.discountPct > 0) {
                discountRow.style.display = '';
                discountEl.textContent = '-' + formatUGX(p.discountAmt) + ' (' + p.discountPct + '%)';
            } else {
                discountRow.style.display = 'none';
            }
        }

        // Populate hidden inputs for form
        setHidden('input-format-key',   state.selectedFormat);
        setHidden('input-format-label', state.selectedFormatLabel);
        setHidden('input-start-date',   state.startDate);
        setHidden('input-days',         state.days);
    }

    function setHidden(id, value) {
        const el = qs('#' + id);
        if (el) el.value = value;
    }

    qs('#wizard-step3-back')?.addEventListener('click', function () { goTo(2); });

    // Booking form submission
    const bookingForm = qs('#booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = qs('#booking-submit-btn');
            const alertBox  = qs('#step3-alert');

            if (!state.selectedFormat || !state.startDate || state.days < 1) {
                showAlert(alertBox, 'error', 'Please complete all booking steps.');
                return;
            }

            // Confirm before submitting
            if (!confirm('Proceed to payment for ' + state.selectedFormatLabel + ' — ' + formatUGX(calcPrice(state.selectedFormatPrice, state.days).total) + '?')) {
                return;
            }

            setLoading(submitBtn, true);

            const formData = new FormData(bookingForm);
            try {
                const res  = await fetch('/api/book.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success && data.booking_id) {
                    window.location.href = '/checkout.php?booking_id=' + data.booking_id;
                } else {
                    showAlert(alertBox, 'error', data.error || 'Booking failed. Please try again.');
                    setLoading(submitBtn, false);
                }
            } catch (err) {
                showAlert(alertBox, 'error', 'Network error. Please check your connection and retry.');
                setLoading(submitBtn, false);
            }
        });
    }

    // Init
    updateStepUI();
    if (daysInput) {
        state.days = parseInt(daysInput.value, 10) || 1;
        updatePriceCalculator();
    }
})();

/* ================================================================
   5. REGISTER FORM (AJAX)
   ================================================================ */

(function initRegisterForm() {
    const form = qs('#register-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors(form);

        const alertBox = qs('#register-alert');
        const submitBtn = qs('#register-btn');

        // Client-side validation
        const password = form.password.value;
        const confirm  = form.password_confirm.value;
        if (password.length < 8) {
            showFieldError('password', 'Password must be at least 8 characters.');
            return;
        }
        if (password !== confirm) {
            showFieldError('password_confirm', 'Passwords do not match.');
            return;
        }

        setLoading(submitBtn, true);
        const formData = new FormData(form);

        try {
            const res  = await fetch('/api/register.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showAlert(alertBox, 'success', 'Account created! Redirecting to dashboard…');
                setTimeout(function () {
                    window.location.href = '/dashboard.php';
                }, 1200);
            } else {
                showAlert(alertBox, 'error', data.error || 'Registration failed. Please try again.');
                setLoading(submitBtn, false);
            }
        } catch (err) {
            showAlert(alertBox, 'error', 'Network error. Please check your connection.');
            setLoading(submitBtn, false);
        }
    });
})();

/* ================================================================
   6. LOGIN FORM (AJAX)
   ================================================================ */

(function initLoginForm() {
    const form = qs('#login-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors(form);

        const alertBox  = qs('#login-alert');
        const submitBtn = qs('#login-btn');

        setLoading(submitBtn, true);
        const formData = new FormData(form);

        try {
            const res  = await fetch('/api/login.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showAlert(alertBox, 'success', 'Login successful! Redirecting…');
                const next = new URLSearchParams(window.location.search).get('next') || '/dashboard.php';
                setTimeout(function () {
                    window.location.href = next;
                }, 800);
            } else {
                showAlert(alertBox, 'error', data.error || 'Invalid email or password.');
                setLoading(submitBtn, false);
            }
        } catch (err) {
            showAlert(alertBox, 'error', 'Network error. Please check your connection.');
            setLoading(submitBtn, false);
        }
    });
})();

/* ================================================================
   7. CHECKOUT / PAYMENT INIT
   ================================================================ */

(function initCheckout() {
    const form = qs('#checkout-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const alertBox  = qs('#checkout-alert');
        const submitBtn = qs('#pay-btn');

        if (!confirm('You will be redirected to Flutterwave to complete your payment. Proceed?')) {
            return;
        }

        setLoading(submitBtn, true);
        const formData = new FormData(form);

        try {
            const res  = await fetch('/api/pay-init.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.payment_url) {
                showAlert(alertBox, 'info', 'Redirecting to payment gateway…');
                setTimeout(function () {
                    window.location.href = data.payment_url;
                }, 600);
            } else {
                showAlert(alertBox, 'error', data.error || 'Could not initiate payment. Please try again.');
                setLoading(submitBtn, false);
            }
        } catch (err) {
            showAlert(alertBox, 'error', 'Network error. Please check your connection.');
            setLoading(submitBtn, false);
        }
    });
})();

/* ================================================================
   8. FORM VALIDATION HELPERS
   ================================================================ */

function showFieldError(name, message) {
    const input = document.querySelector('[name="' + name + '"]');
    if (!input) return;
    input.classList.add('error');
    const existing = input.parentElement.querySelector('.kn-form-error');
    if (!existing) {
        const err = document.createElement('div');
        err.className = 'kn-form-error';
        err.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + message;
        input.after(err);
    }
}

function clearErrors(form) {
    form.querySelectorAll('.error').forEach(function (el) { el.classList.remove('error'); });
    form.querySelectorAll('.kn-form-error').forEach(function (el) { el.remove(); });
}

/* ================================================================
   9. DATE PICKER ENFORCEMENT
   ================================================================ */

(function enforceDateMin() {
    qsa('input[type="date"]').forEach(function (input) {
        if (!input.min) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            input.min = tomorrow.toISOString().split('T')[0];
        }
        input.addEventListener('change', function () {
            if (this.value < this.min) {
                this.value = this.min;
            }
        });
    });
})();

/* ================================================================
   10. AUTO-HIDE FLASH MESSAGES
   ================================================================ */

(function autoHideFlash() {
    qsa('.kn-alert').forEach(function (alert) {
        if (alert.classList.contains('kn-alert-success')) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function () { alert.remove(); }, 500);
            }, 5000);
        }
    });
})();

/* ================================================================
   11. SMOOTH SCROLL FOR ANCHOR LINKS
   ================================================================ */

(function initSmoothScroll() {
    qsa('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            const offset = 80; // header height
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });
})();

/* ================================================================
   12. FORMAT CARDS — LANDING PAGE
   ================================================================ */

(function initLandingAnimations() {
    // Intersection Observer for fade-in animations
    if (!window.IntersectionObserver) return;

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    qsa('[data-animate]').forEach(function (el) {
        observer.observe(el);
    });
})();
