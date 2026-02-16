document.addEventListener('DOMContentLoaded', function () {
    // rotating phrases
    var el = document.getElementById('af-phrase');
    if (el) {
        var phrases = ["for students.", "for professionals.", "for entrepreneurs.", "that inspires action.", "that fits your phone."];
        var i = 0, t = 0, typing = true, cur = phrases[0];
        (function tick() {
            if (typing) {
                t++; el.textContent = cur.slice(0, t);
                if (t === cur.length) { typing = false; return setTimeout(tick, 1100); }
                setTimeout(tick, 45);
            } else {
                t--; el.textContent = cur.slice(0, t);
                if (t === 0) { i = (i + 1) % phrases.length; cur = phrases[i]; typing = true; return setTimeout(tick, 220); }
                setTimeout(tick, 25);
            }
        })();
    }

    // portrait slider
    var slides = document.querySelectorAll('[data-af-slide]');
    if (slides.length) {
        var idx = 0; function show(n) { slides.forEach(s => s.classList.remove('is-active')); slides[n].classList.add('is-active'); }
        show(0); setInterval(function () { idx = (idx + 1) % slides.length; show(idx); }, 2500);
    }

    // autoplay nudge (iOS)
    var v = document.getElementById('af-hero-video');
    if (v) { var p = v.play(); if (p && p.catch) { p.catch(function () { }); } }
});


document.addEventListener('DOMContentLoaded', function () {
    var v = document.getElementById('af-hero-video');
    if (v) { var p = v.play(); if (p && p.catch) { p.catch(() => { }); } }
});


