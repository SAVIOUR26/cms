(function () {
    const conf = window.KANDA_CONF || {};
    const wa = conf.wa || '2567XXXXXXX';
    const cur = conf.cur || 'UGX';
    const rate = conf.rateCard || '#';
    const editionsEndpoint = conf.editionsEndpoint || '';

    const $ = (id) => document.getElementById(id);

    const setText = (id, txt) => { const el = $(id); if (el) el.textContent = txt; };
    const setHref = (id, url) => { const el = $(id); if (el) el.href = url; };

    setText('p-d', `${cur} 500`);
    setText('p-w', `${cur} 2,500`);
    setText('p-m', `${cur} 7,500`);

    const subText = encodeURIComponent("Hello KandaNews Uganda 👋%0AI want to subscribe.");
    setHref('wa-sub', `https://wa.me/${wa}?text=${subText}`);

    const adsText = encodeURIComponent("Hello KandaNews Ads (UG) 👋%0APlease share your rate card and booking steps.");
    setHref('wa-ads', `https://wa.me/${wa}?text=${adsText}`);

    setHref('ratecard', rate);
    setHref('ratecard2', rate);
    setText('tel-display', `+${wa}`);

    if (editionsEndpoint) {
        fetch(editionsEndpoint)
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                const ed = (data.items || [])[0];
                if (ed) {
                    setText('ed-title', ed.title || ed.id || 'Latest Edition');
                    setText('ed-date', ed.date || '');
                }
            })
            .catch(() => { /* silent */ });
    }
})();
