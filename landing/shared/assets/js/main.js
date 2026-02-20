/**
 * KandaNews Africa — Shared JavaScript
 * Handles: mobile menu, scroll reveal, counter animation, video autoplay, smooth scroll
 */
(function () {
    'use strict';

    // ── Mobile burger menu ──
    var burger = document.querySelector('.kn-header__burger');
    var mobileNav = document.querySelector('.kn-mobile-nav');
    if (burger && mobileNav) {
        burger.addEventListener('click', function () {
            var open = mobileNav.classList.toggle('kn-mobile-nav--open');
            burger.setAttribute('aria-expanded', open);
            burger.querySelector('i').className = open
                ? 'fa-solid fa-xmark'
                : 'fa-solid fa-bars';
            burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        });
        mobileNav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                mobileNav.classList.remove('kn-mobile-nav--open');
                burger.setAttribute('aria-expanded', 'false');
                burger.querySelector('i').className = 'fa-solid fa-bars';
                burger.setAttribute('aria-label', 'Open menu');
            });
        });
    }

    // ── Scroll reveal (IntersectionObserver) ──
    var reveals = document.querySelectorAll('.kn-reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('kn-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        reveals.forEach(function (el, i) {
            el.style.transitionDelay = (i % 4) * 0.08 + 's';
            revealObserver.observe(el);
        });
    } else {
        reveals.forEach(function (el) { el.classList.add('kn-visible'); });
    }

    // ── Animated number counters ──
    var counters = document.querySelectorAll('.kn-counter');
    if (counters.length && 'IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { counterObserver.observe(el); });
    }

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        if (isNaN(target)) return;
        var duration = 1800;
        var startTime = null;
        var prefix = el.getAttribute('data-prefix') || '';
        var suffix = el.getAttribute('data-suffix') || '';

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(eased * target);
            el.textContent = prefix + current.toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = prefix + target.toLocaleString() + suffix;
        }
        requestAnimationFrame(step);
    }

    // ── Hero video autoplay nudge (iOS) ──
    document.querySelectorAll('video[autoplay]').forEach(function (video) {
        var p = video.play();
        if (p && p.catch) p.catch(function () { /* autoplay blocked */ });
    });

    // ── Smooth scroll for anchor links (offset by header height) ──
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var href = a.getAttribute('href');
            if (href === '#') return;
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                var headerH = document.querySelector('.kn-header');
                var offset = headerH ? headerH.offsetHeight : 0;
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

    // ── Header compact on scroll ──
    var header = document.querySelector('.kn-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 80) {
                header.classList.add('kn-header--scrolled');
            } else {
                header.classList.remove('kn-header--scrolled');
            }
        }, { passive: true });
    }
})();
