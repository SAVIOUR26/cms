/**
 * kanda-dashboard.js - Complete Dashboard with Dual Gateway Support
 * Version: 2.0
 * Supports: Flutterwave + DPO payment gateways
 */
(function () {
    'use strict';

    // ========== Configuration ==========
    const CFG = (typeof window.KandaDashboard !== 'undefined') ? window.KandaDashboard : {};
    const REST_BASE_FLW = CFG.restBaseFLW || '/api/flutterwave';
    const REST_BASE_DPO = CFG.restBaseDPO || '/api/dpo';
    const REST_BASE = CFG.restBase || '/api';
    const NONCE = CFG.nonce || '';
    const USER_PROFILE = CFG.user_profile || {};
    let PUBLIC_KEY_FLW = CFG.public_key || '';

    // ========== Constants ==========
    const PHRASES = [
        'Inspiring Africa 🌍',
        'Forget Traditional Media — Go Smart 📲',
        'Smart Adverts, Smarter Reach 🎯',
        'Add to Cart, Right in Your News 🛒',
        'Video Adverts that Speak Louder 🎥',
        'Getting Personal with Your Space 💡',
        'Podcasts that Talk to You 🎧',
        'Campus Power for Students 🎓',
        'Pro Playbook for Professionals 👔',
        'Startup & Hustle Stories 🚀'
    ];

    // ========== DOM Helpers ==========
    const byId = id => document.getElementById(id);
    const el = sel => document.querySelector(sel);
    const qAll = sel => Array.from(document.querySelectorAll(sel));

    // ========== DOM Elements ==========
    const latestMeta = byId('latestMeta');
    const openLatest = byId('openLatest');
    const latestErr = byId('latestError');
    const latestSkel = byId('latestSkeleton');
    const archiveList = byId('archiveList');
    const archiveEmpty = byId('archiveEmpty');
    const archiveErr = byId('archiveError');
    const subStatusPill = byId('subStatusPill');
    const notif = byId('notif');
    const planMsg = byId('planMsg');
    const monthFilter = byId('monthFilter');
    const searchInput = byId('searchInput');
    const applyFilters = byId('applyFilters');
    const profileButton = byId('profileButton');
    const profileInfoCard = byId('profileInfoCard');
    const closeProfileCardBtn = byId('closeProfileCard');
    const typeTarget = byId('typeTarget');
    const brandLogo = byId('brandLogo');
    const plansContainer = byId('plans');

    // Payment chooser elements
    const paymentChooser = byId('paymentChooser');
    const chooserPlanLabel = byId('chooserPlanLabel');
    const chooserAmountLabel = byId('chooserAmountLabel');
    const btnPayDPO = byId('btnPayDPO');
    const btnPayFlutterwave = byId('btnPayFlutterwave');
    const btnCancelChooser = byId('btnCancelChooser');

    // ========== Utility Functions ==========
    const escapeHTML = s => String(s || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmt = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' });
    const fmtShort = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;

    function explain(e) {
        if (!e) return 'Could not load data. Please retry in a moment.';
        if (e.name === 'AbortError') return 'Network is slow; request timed out. Please try again.';
        if (e.status === 401 || e.status === 403) return 'Your session expired. Please reload and sign in again.';
        return e.message || 'Could not load data. Please retry in a moment.';
    }

    function showNotification(message, isError = false, autoHideMs = 5000) {
        if (!notif) return;
        notif.textContent = message;
        notif.hidden = false;
        notif.classList.toggle('err', !!isError);
        if (autoHideMs > 0) {
            window.clearTimeout(notif.__hideTimeout);
            notif.__hideTimeout = setTimeout(() => { try { notif.hidden = true; } catch (_) { } }, autoHideMs);
        }
    }

    function debounce(fn, wait = 250) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // ========== Fetch Wrapper ==========
    async function fetchJSON(url, opts = {}, { timeoutMs = 12000, retries = 1, backoffMs = 300 } = {}) {
        if (!url) throw new Error('Missing URL for fetchJSON');
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

        const headers = Object.assign({}, opts.headers || {}, {
            'X-CSRF-Token': NONCE,
            'Accept': 'application/json'
        });

        try {
            const resp = await fetch(url, Object.assign({}, opts, { credentials: 'same-origin', signal: controller.signal, headers }));
            if (!resp.ok) {
                const body = await resp.text().catch(() => '');
                const err = new Error(`HTTP ${resp.status}`);
                err.status = resp.status;
                err.body = body;
                throw err;
            }
            const ct = resp.headers.get('content-type') || '';
            if (ct.includes('application/json')) return await resp.json();
            try { return await resp.json(); } catch (_) { return {}; }
        } catch (err) {
            if (retries > 0 && (err.name === 'TypeError' || err.name === 'AbortError' || (err.status >= 500 || !err.status))) {
                await new Promise(r => setTimeout(r, backoffMs));
                return fetchJSON(url, opts, { timeoutMs, retries: retries - 1, backoffMs: backoffMs * 2 });
            }
            throw err;
        } finally {
            clearTimeout(timeoutId);
        }
    }

    async function postJSON(url, payload = {}, opts = {}) {
        return fetchJSON(url, Object.assign({}, opts, {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: Object.assign({}, opts.headers || {}, { 'Content-Type': 'application/json' })
        }));
    }

    // ========== State ==========
    let editions = [];
    let filtered = [];
    let latest = null;
    let selectedPlan = null;
    let gatewayStatus = {
        flutterwave: { available: true },
        dpo: { available: true }
    };

    // ========== Check Gateway Availability ==========
    async function checkGatewayStatus() {
        try {
            const statusUrl = REST_BASE + '/gateways/status';

            console.log('Checking gateway status at:', statusUrl);

            const resp = await fetch(statusUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if (resp.ok) {
                const data = await resp.json();
                gatewayStatus = data;
                console.log('Gateway status loaded:', gatewayStatus);

                // Log what's available
                console.log('Flutterwave available:', gatewayStatus.flutterwave?.available);
                console.log('DPO available:', gatewayStatus.dpo?.available);
            } else {
                console.error('Gateway status check failed:', resp.status);
            }
        } catch (e) {
            console.error('Could not load gateway status:', e);
            // Fallback: assume both available if we can't check
            gatewayStatus = {
                flutterwave: { available: true },
                dpo: { available: true }
            };
        }
    }

    // ========== Payment Chooser ==========
    function showPaymentChooser(plan, amount, currency) {
        selectedPlan = { plan, amount, currency };

        if (chooserPlanLabel) chooserPlanLabel.textContent = plan.charAt(0).toUpperCase() + plan.slice(1);
        if (chooserAmountLabel) chooserAmountLabel.textContent = `${currency} ${amount.toLocaleString()}`;

        // Check gateway availability
        console.log('Current gateway status:', gatewayStatus);

        const flwAvailable = gatewayStatus.flutterwave?.available === true;
        const dpoAvailable = gatewayStatus.dpo?.available === true;

        console.log('Flutterwave available:', flwAvailable);
        console.log('DPO available:', dpoAvailable);

        // Show/hide buttons based on gateway availability
        if (btnPayFlutterwave) {
            if (flwAvailable) {
                btnPayFlutterwave.style.display = 'flex';
                btnPayFlutterwave.disabled = false;
                console.log('Showing Flutterwave button');
            } else {
                btnPayFlutterwave.style.display = 'none';
                console.log('Hiding Flutterwave button');
            }
        }

        if (btnPayDPO) {
            if (dpoAvailable) {
                btnPayDPO.style.display = 'flex';
                btnPayDPO.disabled = false;
                console.log('Showing DPO button');
            } else {
                btnPayDPO.style.display = 'none';
                console.log('Hiding DPO button');
            }
        }

        // If both disabled, show error and don't open chooser
        if (!flwAvailable && !dpoAvailable) {
            console.log('Both gateways disabled!');
            showNotification('Payment system temporarily unavailable. Please try again later or contact support.', true);
            if (plansContainer) {
                plansContainer.querySelectorAll('button').forEach(b => b.disabled = false);
            }
            return;
        }

        // If only one available, skip chooser and go directly
        if (flwAvailable && !dpoAvailable) {
            console.log('Only Flutterwave available, redirecting directly');
            payWithFlutterwave();
            return;
        }

        if (dpoAvailable && !flwAvailable) {
            console.log('Only DPO available, redirecting directly');
            payWithDPO();
            return;
        }

        // Both available, show chooser
        console.log('Both gateways available, showing chooser');
        if (paymentChooser) {
            paymentChooser.style.display = 'flex';
            paymentChooser.hidden = false;
            paymentChooser.setAttribute('aria-hidden', 'false');

            // Focus on first available button
            if (dpoAvailable && btnPayDPO) {
                btnPayDPO.focus();
            } else if (flwAvailable && btnPayFlutterwave) {
                btnPayFlutterwave.focus();
            }
        }
    }

    function hidePaymentChooser() {
        if (paymentChooser) {
            paymentChooser.style.display = 'none';
            paymentChooser.hidden = true;
            paymentChooser.setAttribute('aria-hidden', 'true');
        }
        selectedPlan = null;
        if (plansContainer) {
            plansContainer.querySelectorAll('button').forEach(b => b.disabled = false);
        }
    }

    // ========== Payment: DPO ==========
    async function payWithDPO() {
        if (!selectedPlan) {
            showNotification('Please select a plan first.', true);
            return;
        }

        if (!REST_BASE_DPO) {
            showNotification('DPO payment not available. Please contact support.', true);
            hidePaymentChooser();
            return;
        }

        // Store plan details before hiding chooser
        const planData = {
            plan: selectedPlan.plan,
            amount: selectedPlan.amount,
            currency: selectedPlan.currency
        };

        hidePaymentChooser();
        showNotification('Redirecting to DPO payment...', false, 0);

        try {
            const resp = await postJSON(`${REST_BASE_DPO}/payments/initiate`, planData);

            if (!resp || !resp.redirect_url) {
                throw new Error('No redirect URL from DPO');
            }

            // Redirect to DPO payment page
            window.location.href = resp.redirect_url;
        } catch (e) {
            console.error('DPO payment error', e);
            showNotification('DPO payment failed: ' + explain(e), true);
            if (plansContainer) {
                plansContainer.querySelectorAll('button').forEach(b => b.disabled = false);
            }
        }
    }

    // ========== Payment: Flutterwave ==========
    async function payWithFlutterwave() {
        if (!selectedPlan) {
            showNotification('Please select a plan first.', true);
            return;
        }

        if (!REST_BASE_FLW) {
            showNotification('Flutterwave payment not available. Please contact support.', true);
            hidePaymentChooser();
            return;
        }

        // Store plan details before hiding chooser
        const planData = {
            plan: selectedPlan.plan,
            amount: selectedPlan.amount,
            currency: selectedPlan.currency
        };

        hidePaymentChooser();
        showNotification('Redirecting to Flutterwave payment...', false, 0);

        try {
            // Use standard (hosted) checkout to avoid fingerprinting issues
            const resp = await postJSON(`${REST_BASE_FLW}/payments/standard`, planData);

            if (!resp || !resp.redirect_url) {
                throw new Error('No redirect URL from Flutterwave');
            }

            // Redirect to Flutterwave hosted page
            window.location.href = resp.redirect_url;
        } catch (e) {
            console.error('Flutterwave payment error', e);
            showNotification('Flutterwave payment failed: ' + explain(e), true);
            if (plansContainer) {
                plansContainer.querySelectorAll('button').forEach(b => b.disabled = false);
            }
        }
    }

    // ========== Subscription Status ==========
    async function loadSubStatus() {
        if (!subStatusPill || !REST_BASE) return;

        try {
            const s = await fetchJSON(`${REST_BASE}/subscriptions/status`);
            if (s && s.active) {
                const expiresDate = s.expires_at ? fmt.format(new Date(s.expires_at)) : null;
                const text = expiresDate ?
                    `Active — expires ${expiresDate} (${s.days_left} day${s.days_left === 1 ? '' : 's'} left)` :
                    `Active — ${s.days_left} day(s) left`;
                subStatusPill.className = 'pill ' + (s.days_left <= 1 ? 'warn' : 'ok');
                subStatusPill.textContent = text;
                if (s.days_left <= 2) {
                    showNotification('Your access expires soon. Renew to keep reading.');
                }
            } else {
                subStatusPill.className = 'pill err';
                subStatusPill.textContent = 'No current subscription';
                showNotification('You are not subscribed. Choose a plan to unlock content.');
            }
        } catch (e) {
            subStatusPill.className = 'pill';
            subStatusPill.textContent = 'Status unavailable';
            console.warn('loadSubStatus error', e);
        }
    }

    // ========== Archive Helpers ==========
    function fillMonthFilter(items = []) {
        if (!monthFilter) return;
        const set = new Set(items.map(it => {
            const d = it.published_at ? new Date(it.published_at) : null;
            return d ? fmtShort(d) : '';
        }).filter(Boolean));
        const arr = Array.from(set).sort().reverse();
        monthFilter.innerHTML = '<option value="">All months</option>' + arr.map(v => {
            const [y, m] = v.split('-');
            const label = new Date(Number(y), Number(m) - 1, 1).toLocaleString(undefined, { month: 'long', year: 'numeric' });
            return `<option value="${escapeHTML(v)}">${escapeHTML(label)}</option>`;
        }).join('');
    }

    function renderArchive() {
        if (!archiveList) return;
        archiveList.innerHTML = '';
        if (!filtered.length) {
            if (archiveEmpty) archiveEmpty.hidden = false;
            return;
        }
        if (archiveEmpty) archiveEmpty.hidden = true;
        filtered.forEach(ed => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'row';
            const title = ed.title || ed.id || 'Untitled';
            const right = ed.published_at ? fmt.format(new Date(ed.published_at)) : (ed.id || '');
            row.setAttribute('aria-label', `Open ${title}`);
            row.innerHTML = `<span>${escapeHTML(title)}</span><span class="muted">${escapeHTML(String(right))}</span>`;
            row.addEventListener('click', () => openSigned(ed.id));
            archiveList.appendChild(row);
        });
    }

    function applyArchiveFilters() {
        if (!editions.length) return;
        const mVal = monthFilter ? monthFilter.value : '';
        const qVal = (searchInput && searchInput.value) ? searchInput.value.trim().toLowerCase() : '';
        filtered = editions.slice(1).filter(ed => {
            let pass = true;
            if (mVal && ed.published_at) {
                const v = fmtShort(new Date(ed.published_at));
                pass = pass && (v === mVal);
            }
            if (qVal) {
                const t = (ed.title || '').toLowerCase();
                const id = (String(ed.id) || '').toLowerCase();
                pass = pass && (t.includes(qVal) || id.includes(qVal));
            }
            return pass;
        });
        renderArchive();
    }

    const debouncedApplyFilters = debounce(applyArchiveFilters, 300);

    async function loadEditions() {
        if (latestSkel) latestSkel.style.display = '';
        if (archiveList) archiveList.setAttribute('aria-busy', 'true');
        if (!REST_BASE) {
            if (latestMeta) latestMeta.textContent = 'Editions unavailable';
            if (latestSkel) latestSkel.style.display = 'none';
            return;
        }
        try {
            const data = await fetchJSON(`${REST_BASE}/editions?limit=30`, {}, { retries: 2 });
            editions = Array.isArray(data.items) ? data.items : (Array.isArray(data) ? data : []);
            if (editions.length) {
                latest = editions[0];
                if (latestMeta) {
                    const title = latest.title || latest.id || 'Untitled';
                    const date = latest.published_at ? ` • ${fmt.format(new Date(latest.published_at))}` : '';
                    latestMeta.textContent = `${title}${date}`;
                }
                if (openLatest) openLatest.disabled = false;
            } else {
                if (latestMeta) latestMeta.textContent = 'No edition published yet.';
                if (openLatest) openLatest.disabled = true;
            }
            fillMonthFilter(editions);
            filtered = editions.slice(1);
            renderArchive();
        } catch (e) {
            const msg = explain(e);
            if (latestErr) { latestErr.textContent = msg; latestErr.hidden = false; }
            if (archiveErr) { archiveErr.textContent = msg; archiveErr.hidden = false; }
            console.error('loadEditions error', e);
        } finally {
            if (latestSkel) latestSkel.style.display = 'none';
            if (archiveList) archiveList.setAttribute('aria-busy', 'false');
        }
    }

    async function openSigned(editionId) {
        if (!editionId) return alert('Invalid edition.');
        if (!REST_BASE) return alert('Operation not available.');
        try {
            const urlData = await fetchJSON(`${REST_BASE}/editions/${encodeURIComponent(editionId)}/signed-url`);
            if (urlData && urlData.url) {
                window.location.href = urlData.url;
            } else {
                alert('No URL returned for this edition.');
            }
        } catch (e) {
            alert(explain(e));
        }
    }

    // ========== Plan Click Handler ==========
    function handlePlanClick(ev) {
        const btn = ev.target.closest('button[data-plan]');
        if (!btn || !plansContainer) return;

        plansContainer.querySelectorAll('button').forEach(b => b.disabled = true);

        const plan = btn.dataset.plan;
        const amount = Number(btn.dataset.amount || 0);
        const currency = btn.dataset.currency || 'UGX';

        if (!plan || amount <= 0) {
            showNotification('Invalid plan selection', true);
            plansContainer.querySelectorAll('button').forEach(b => b.disabled = false);
            return;
        }

        // Show payment chooser
        showPaymentChooser(plan, amount, currency);
    }

    // ========== Profile Card ==========
    function trapFocus(container) {
        const focusable = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';
        const nodes = Array.from(container.querySelectorAll(focusable));
        if (!nodes.length) return () => { };

        function onKey(e) {
            if (e.key !== 'Tab') return;
            const i = nodes.indexOf(document.activeElement);
            if (e.shiftKey) {
                if (i === 0) {
                    nodes[nodes.length - 1].focus();
                    e.preventDefault();
                }
            } else {
                if (i === nodes.length - 1) {
                    nodes[0].focus();
                    e.preventDefault();
                }
            }
        }
        container.addEventListener('keydown', onKey);
        return () => container.removeEventListener('keydown', onKey);
    }

    function openProfile() {
        if (!profileInfoCard) return;

        const profileName = byId('profileName');
        const profileEmail = byId('profileEmail');
        const profileWhatsapp = byId('profileWhatsapp');
        const profileCategory = byId('profileCategory');
        const profileOrg = byId('profileOrg');

        if (profileName) profileName.textContent = USER_PROFILE.name || '—';
        if (profileEmail) profileEmail.textContent = USER_PROFILE.email || '—';
        if (profileWhatsapp) profileWhatsapp.textContent = USER_PROFILE.whatsapp || '—';
        if (profileCategory) profileCategory.textContent = USER_PROFILE.category || '—';
        if (profileOrg) profileOrg.textContent = USER_PROFILE.org || '—';

        profileInfoCard.hidden = false;
        profileInfoCard.setAttribute('aria-hidden', 'false');
        profileButton && profileButton.setAttribute('aria-expanded', 'true');

        const removeTrap = trapFocus(profileInfoCard);
        profileInfoCard.__removeTrap = removeTrap;

        const focusable = profileInfoCard.querySelectorAll('button, a, input, [tabindex]:not([tabindex="-1"])');
        if (focusable.length) focusable[0].focus();
    }

    function closeProfile() {
        if (!profileInfoCard) return;
        profileInfoCard.hidden = true;
        profileInfoCard.setAttribute('aria-hidden', 'true');
        profileButton && profileButton.setAttribute('aria-expanded', 'false');
        if (profileInfoCard.__removeTrap) {
            profileInfoCard.__removeTrap();
            delete profileInfoCard.__removeTrap;
        }
        profileButton && profileButton.focus();
    }

    function initProfileCard() {
        if (!profileButton || !profileInfoCard) return;
        profileButton.addEventListener('click', openProfile);
        closeProfileCardBtn && closeProfileCardBtn.addEventListener('click', closeProfile);

        profileInfoCard.addEventListener('click', (ev) => {
            if (ev.target === profileInfoCard) closeProfile();
        });

        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && profileInfoCard && !profileInfoCard.hidden) closeProfile();
        });
    }

    // ========== Typewriter ==========
    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function makeTypewriter() {
        let running = false;
        let stopRequested = false;

        async function typePhrase(text, speed, textNode) {
            const chars = Array.from(text);
            for (let i = 0; i < chars.length; i++) {
                if (stopRequested) return chars;
                textNode.nodeValue = chars.slice(0, i + 1).join('');
                await new Promise(r => setTimeout(r, prefersReducedMotion() ? 10 : speed));
            }
            await new Promise(r => setTimeout(r, 800));
            return chars;
        }

        async function clearPhrase(chars, textNode) {
            for (let i = chars.length; i > 0; i--) {
                if (stopRequested) return;
                textNode.nodeValue = chars.slice(0, i - 1).join('');
                await new Promise(r => setTimeout(r, prefersReducedMotion() ? 5 : 12));
            }
        }

        async function run(targetElement, logoElement) {
            if (!targetElement) return;
            running = true;
            stopRequested = false;

            const textNode = document.createTextNode('');
            const cursor = document.createElement('span');
            cursor.className = 'cursor';
            targetElement.innerHTML = '';
            targetElement.appendChild(textNode);
            targetElement.appendChild(cursor);

            let revealed = false;
            while (!stopRequested) {
                for (const p of PHRASES) {
                    if (stopRequested) break;
                    const chars = await typePhrase(p, 70, textNode);
                    if (!revealed) {
                        revealed = true;
                        setTimeout(() => logoElement && logoElement.classList && logoElement.classList.add('show'), 250);
                    }
                    await clearPhrase(chars, textNode);
                    if (stopRequested) break;
                }
            }
            running = false;
        }

        function stop() { stopRequested = true; }

        return { run, stop, isRunning: () => running };
    }

    // ========== Updates Slider ==========
    function initUpdatesSlider() {
        const container = byId('updates-slider-container');
        if (!container) return;
        const track = byId('updates-slider-track');
        if (!track) return;
        const slides = Array.from(track.querySelectorAll('.slider-image'));
        if (!slides.length || prefersReducedMotion()) return;

        let currentIndex = 0;
        let intervalId = null;

        function getSlideWidth() {
            return Math.round(container.getBoundingClientRect().width);
        }

        function goToSlide(index) {
            const slideWidth = getSlideWidth();
            const offset = -index * slideWidth;
            track.style.transform = `translate3d(${offset}px,0,0)`;
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % slides.length;
            goToSlide(currentIndex);
        }

        function startSlider() {
            stopSlider();
            intervalId = setInterval(nextSlide, 3000);
        }

        function stopSlider() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        let imagesToLoad = slides.length;
        const loadTimer = setTimeout(() => {
            imagesToLoad = 0;
            goToSlide(0);
            startSlider();
        }, 5000);

        slides.forEach(img => {
            if (img.complete) {
                imagesToLoad--;
            } else {
                img.addEventListener('load', () => {
                    imagesToLoad--;
                    if (imagesToLoad === 0) {
                        clearTimeout(loadTimer);
                        goToSlide(0);
                        startSlider();
                    }
                });
                img.addEventListener('error', () => {
                    imagesToLoad--;
                    if (imagesToLoad === 0) {
                        clearTimeout(loadTimer);
                        goToSlide(0);
                        startSlider();
                    }
                });
            }
        });

        if (imagesToLoad === 0) {
            clearTimeout(loadTimer);
            goToSlide(0);
            startSlider();
        }

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => goToSlide(currentIndex), 120);
        });

        container.addEventListener('mouseenter', stopSlider);
        container.addEventListener('mouseleave', startSlider);
        container.addEventListener('focusin', stopSlider);
        container.addEventListener('focusout', startSlider);
    }

    // ========== Initialization ==========
    async function init() {
        // Ensure payment chooser is hidden on load
        if (paymentChooser) {
            paymentChooser.style.display = 'none';
            paymentChooser.hidden = true;
            paymentChooser.setAttribute('aria-hidden', 'true');
        }

        // Check gateway availability first (await it)
        await checkGatewayStatus();

        // Plan selection
        if (plansContainer) {
            plansContainer.addEventListener('click', handlePlanClick);
        }

        // Payment chooser buttons
        if (btnPayDPO) {
            btnPayDPO.addEventListener('click', payWithDPO);
        }
        if (btnPayFlutterwave) {
            btnPayFlutterwave.addEventListener('click', payWithFlutterwave);
        }
        if (btnCancelChooser) {
            btnCancelChooser.addEventListener('click', hidePaymentChooser);
        }

        // Close chooser on Escape or outside click
        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && paymentChooser && !paymentChooser.hidden) {
                hidePaymentChooser();
            }
        });
        if (paymentChooser) {
            paymentChooser.addEventListener('click', (ev) => {
                if (ev.target === paymentChooser) {
                    hidePaymentChooser();
                }
            });
        }

        // Archive filters
        if (applyFilters) applyFilters.addEventListener('click', applyArchiveFilters);
        if (searchInput) searchInput.addEventListener('input', debouncedApplyFilters);
        if (searchInput) searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyArchiveFilters();
            }
        });
        if (monthFilter) monthFilter.addEventListener('change', applyArchiveFilters);

        // Latest edition
        const readLatest = byId('read-latest');
        if (readLatest) readLatest.addEventListener('click', () => {
            if (!latest) return alert('No latest edition yet.');
            openSigned(latest.id);
        });
        if (openLatest) openLatest.addEventListener('click', () => {
            if (!latest) return;
            openSigned(latest.id);
        });

        // Profile card
        initProfileCard();

        // Typewriter animation
        const tw = makeTypewriter();
        try { tw.run(typeTarget, brandLogo); } catch (e) { console.error(e); }

        // Updates slider
        initUpdatesSlider();

        // Load remote data
        loadSubStatus();
        loadEditions();

        // Log initialization
        console.log('KandaNews Dashboard initialized', {
            flutterwaveAvailable: !!REST_BASE_FLW,
            dpoAvailable: !!REST_BASE_DPO,
            editionsAPI: !!REST_BASE
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
