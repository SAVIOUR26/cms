/**
 * KandaNews Africa — Shared JavaScript
 * Handles: mobile menu, scroll reveal animations, video autoplay
 */
(function () {
    'use strict';

    // ── Mobile burger menu ──
    var burger = document.querySelector('.kn-header__burger');
    var nav = document.querySelector('.kn-header__actions');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = nav.classList.toggle('kn-header__actions--open');
            burger.setAttribute('aria-expanded', open);
            burger.innerHTML = open
                ? '<i class="fa-solid fa-xmark"></i>'
                : '<i class="fa-solid fa-bars"></i>';
        });
        // Close on link click
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                nav.classList.remove('kn-header__actions--open');
                burger.setAttribute('aria-expanded', 'false');
                burger.innerHTML = '<i class="fa-solid fa-bars"></i>';
            });
        });
    }

    // ── Scroll reveal (IntersectionObserver) ──
    var reveals = document.querySelectorAll('.kn-reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('kn-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        // Fallback: show everything
        reveals.forEach(function (el) { el.classList.add('kn-visible'); });
    }

    // ── Hero video autoplay nudge (iOS) ──
    var heroVideo = document.querySelector('.kn-hero__video');
    if (heroVideo && heroVideo.play) {
        var p = heroVideo.play();
        if (p && p.catch) p.catch(function () { /* autoplay blocked */ });
    }

    // ── Smooth scroll for anchor links ──
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
