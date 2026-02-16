<?php
/* Template Name: Kanda — Dashboard (No-Header/No-Footer Safe) */
kanda_require_auth_or_redirect( defined('KANDA_DASHBOARD_PATH') ? KANDA_DASHBOARD_PATH : '/user-dashboard' );

$REST_BASE  = esc_url( home_url('/wp-json/kanda/v1') );
$REST_NONCE = wp_create_nonce('wp_rest');

// User (read-only)
$current_user   = wp_get_current_user();
$user_name_safe = esc_html( $current_user->display_name ?: $current_user->user_login );

// User meta (rename keys if your schema differs)
$role    = get_user_meta($current_user->ID, 'kanda_role', true); // student|professional|entrepreneur
$org     = get_user_meta($current_user->ID, 'kanda_org', true);  // University / Company
$country = get_user_meta($current_user->ID, 'kanda_country', true); // 'UG','KE', ...

$role_label = [
  'student'       => 'Student',
  'professional'  => 'Professional',
  'entrepreneur'  => 'Entrepreneur',
][$role] ?? '—';
$org_label = $org ? esc_html($org) : '—';

// Country & flag (prefer subdomain, fallback to user meta)
$host = $_SERVER['HTTP_HOST'] ?? '';
if (preg_match('/^([a-z]{2})\./', $host, $m)) { $cc = strtoupper($m[1]); }
else { $cc = strtoupper($country ?: ''); }
$cc_label = $cc ?: 'Global';
function kanda_flag($cc){
  if (strlen($cc)!==2) return '🌍';
  $u = fn($c)=> 0x1F1E6 + (ord($c)-ord('A'));
  return mb_convert_encoding('&#'.($u($cc[0])).';', 'UTF-8', 'HTML-ENTITIES')
       . mb_convert_encoding('&#'.($u($cc[1])).';', 'UTF-8', 'HTML-ENTITIES');
}
$flag = kanda_flag($cc);

// Assets (update if your logo path differs)
$logo_url   = esc_url( get_theme_file_uri('assets/kandanews-logo.png') );
$logout_url = wp_logout_url( home_url('/') );

// OPTIONAL: replace with your Media Library PDF
$rate_card_url = '/advertise/rate-card.pdf';

// Typewriter phrases (emoji)
$phrases = [
  'Inspiring Africa 🌍',
  'Forget Traditional Media — Go Smart 📲',
  'Smart Adverts, Smarter Reach 🎯',
  'Add to Cart, Right in Your News 🛒',
  'Video Adverts that Speak Louder 🎥',
  'Getting Personal with Your Space 💡',
  'Podcasts that Talk to You 🎧',
  'Campus Power for Students 🎓',
  'Pro Playbook for Professionals 👔',
  'Startup & Hustle Stories 🚀',
];
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard | <?php bloginfo('name'); ?></title>
  <?php wp_head(); ?>
  <style>
    :root{
      --accent:#f05a1a;       /* Kanda Orange */
      --primary:#1e2b42;      /* Navy */
      --bg:#f1f2f3;           /* Light grey */
      --ink:#0f172a;          /* Body text */
      --muted:#475569;        /* Secondary */
      --border:#e6e8eb;       /* Borders */
      --radius:14px; --ring:3px;
    }
    *{box-sizing:border-box}
    html,body{margin:0}
    body{
      font-family:system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,Arial,sans-serif;
      color:var(--ink);
      background:
        radial-gradient(1200px 500px at 10% -20%, rgba(240,90,26,.08), transparent 60%),
        radial-gradient(1000px 400px at 120% 0%, rgba(30,43,66,.06), transparent 60%),
        var(--bg);
    }
    a{color:inherit}
    .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .grid{display:grid;gap:16px;grid-template-columns:2fr 1fr}

    .card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 8px 30px rgba(0,0,0,.05);padding:16px}
    .h{margin:0 0 8px;color:var(--primary)} .muted{color:var(--muted)}

    /* Tiles: add Advertise shortcut */
    .tiles{display:grid;gap:12px;grid-template-columns:repeat(4,1fr)}
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
      padding:.7rem 1rem;border-radius:12px;border:2px solid var(--primary);
      font-weight:800;text-decoration:none;color:#fff;background:var(--primary);
      cursor:pointer;transition:transform .06s ease, background .2s ease, border .2s ease
    }
    .btn:hover{transform:translateY(-1px)}
    .btn.primary{background:var(--accent);border-color:var(--accent)}
    .btn.ghost{background:#fff;color:var(--primary);border-color:var(--border)}
    .btn[disabled]{opacity:.6;cursor:not-allowed}
    .btn:focus-visible{outline:var(--ring) solid color-mix(in srgb, var(--accent) 60%, transparent)}

    .list{display:grid;gap:8px}
    .row{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .row:hover{background:linear-gradient(90deg, rgba(240,90,26,.06), rgba(30,43,66,.04))}

    .pill{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .6rem;border-radius:999px;font-size:.75rem;border:1px solid var(--border);background:#fff}
    .ok{color:#059669;background:#ecfdf5;border-color:#d1fae5}
    .warn{color:#b45309;background:#fffbeb;border-color:#fde68a}
    .err{color:#b91c1c;background:#fef2f2;border-color:#fecaca}

    .kv{display:grid;grid-template-columns:160px 1fr;gap:8px;margin-top:8px} .kv .k{color:var(--muted)}

    /* Header ticker */
    .ticker{position:relative;overflow:hidden;border:1px solid var(--border);border-radius:999px;background:#fff;padding:6px 10px;margin:10px 0}
    .track{display:flex;gap:24px;white-space:nowrap;will-change:transform;animation:ticker 18s linear infinite}
    .dot{opacity:.5}
    @keyframes ticker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

    /* Typewriter loop */
    .hero{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:12px}
    .type{
      font-weight:900;font-size:1.25rem;letter-spacing:.2px;color:var(--primary);
      min-height:1.6em;position:relative;padding-bottom:2px;
      border-bottom:3px solid color-mix(in srgb, var(--accent) 60%, transparent)
    }
    .cursor{display:inline-block;width:2px;height:1.15em;background:var(--accent);margin-left:4px;animation:blink .9s steps(1,end) infinite;vertical-align:-2px}
    @keyframes blink{50%{opacity:0}}
    .logoReveal{opacity:0;transform:translateY(6px);transition:opacity .5s ease, transform .5s ease}
    .logoReveal.show{opacity:1;transform:none}
    .logo{height:36px;object-fit:contain}

    /* Ads */
    .ad{display:flex;align-items:center;justify-content:center;min-height:100px;border:1px dashed var(--border);border-radius:10px;background:#fff}
    .ad-actions{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}

    /* Footer + responsive + effects */
    @media (max-width:980px){ .grid{grid-template-columns:1fr} .tiles{grid-template-columns:1fr 1fr} }
    @media (max-width:640px){ .tiles{grid-template-columns:1fr} }
    .site-footer{margin:24px auto 32px;max-width:1100px;padding:0 16px;color:var(--muted);display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px}
    .locked{opacity:.65}
    .shake{animation:shake .3s}
    @keyframes shake{10%,90%{transform:translateX(-1px)} 20%,80%{transform:translateX(2px)} 30%,50%,70%{transform:translateX(-4px)} 40%,60%{transform:translateX(4px)}}
  </style>
</head>
<body <?php body_class(); ?>>

<main class="wrap" role="main">
  <!-- Header -->
  <section class="card" aria-labelledby="welcomeTitle">
    <div class="hero">
      <h1 id="welcomeTitle" class="h" style="margin-bottom:0">Welcome, <?php echo $user_name_safe; ?> 👋</h1>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-left:auto">
        <span class="pill"><?php echo esc_html($flag.' '.$cc_label); ?></span>
        <span id="subStatusPill" class="pill">Loading…</span>
        <a class="btn ghost" href="<?php echo esc_url($logout_url); ?>">Logout</a>
      </div>
    </div>

    <!-- Ticker -->
    <div class="ticker" aria-label="KandaNews audience">
      <div class="track">
        <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
        <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
      </div>
    </div>

    <!-- Typewriter + Logo -->
    <?php $phrases_json = wp_json_encode($phrases); ?>
    <div class="hero">
      <!-- Static fallback text so it's visible even if JS fails; full list sits in data-phrases -->
      <div class="type" id="typeTarget" aria-live="polite" data-phrases='<?php echo esc_attr($phrases_json); ?>'>
        Inspiring Africa 🌍<span class="cursor"></span>
      </div>
      <?php if ($logo_url): ?>
        <img id="brandLogo" class="logo logoReveal" src="<?php echo $logo_url; ?>" alt="KandaNews Logo" loading="lazy">
      <?php else: ?>
        <div id="brandLogo" class="logoReveal" style="font-weight:800;color:var(--accent)">KandaNews</div>
      <?php endif; ?>
    </div>

    <div id="notif" class="pill" style="margin-top:8px" hidden></div>

    <div class="tiles" role="navigation" aria-label="Primary">
      <button class="btn primary" id="read-latest" type="button">Read latest</button>
      <a class="btn" href="#plans">Subscribe / Renew</a>
      <a class="btn" href="#history">Past editions</a>
      <a class="btn" href="#advertise">Advertise</a>
    </div>
  </section>

  <!-- Latest + Subscription -->
  <section class="grid" style="margin-top:16px">
    <aside class="card" id="latestCard" aria-labelledby="latestTitle">
      <h2 id="latestTitle" class="h">Latest edition</h2>
      <p id="latestMeta" class="muted" style="margin-top:0">Loading…</p>
      <div><button id="openLatest" class="btn primary" type="button" disabled>Open</button></div>
      <div id="latestError" class="pill err" hidden></div>
      <div id="latestSkeleton" style="height:44px;background:#f1f5f9;border-radius:10px;margin-top:8px;"></div>
    </aside>

    <aside class="card" id="subscription" aria-labelledby="subTitle">
      <h2 id="subTitle" class="h">Subscription</h2>
      <p class="muted" style="margin-top:0">Status reflects your current access.</p>
      <div class="list" id="plans">
        <button class="row" type="button" data-plan="daily"   data-amount="500"><span>Daily — UGX 500</span><span>Choose</span></button>
        <button class="row" type="button" data-plan="weekly"  data-amount="2500"><span>Weekly — UGX 2,500</span><span>Choose</span></button>
        <button class="row" type="button" data-plan="monthly" data-amount="7500"><span>Monthly — UGX 7,500</span><span>Choose</span></button>
      </div>
      <div id="planMsg" class="muted" style="margin-top:8px;" aria-live="polite"></div>
    </aside>
  </section>

  <!-- Profile + Sponsored / Advertise -->
  <section class="grid" style="margin-top:16px">
    <aside class="card" aria-labelledby="profileTitle">
      <h2 id="profileTitle" class="h">Your profile</h2>
      <div class="kv">
        <div class="k">Name</div><div><?php echo $user_name_safe; ?></div>
        <div class="k">Category</div><div><?php echo esc_html($role_label); ?></div>
        <div class="k">University / Company</div><div><?php echo $org_label; ?></div>
        <div class="k">Country</div><div><?php echo esc_html($cc_label); ?></div>
      </div>
    </aside>

    <aside class="card" id="advertise" aria-labelledby="adTitle">
      <h2 id="adTitle" class="h">Sponsored</h2>
      <div id="adSlot" class="ad muted">Loading ad…</div>
      <div class="ad-actions">
        <a class="btn ghost" href="/advertise" target="_blank" rel="noopener">Advertise with Kanda</a>
        <a class="btn ghost" href="<?php echo esc_url( $rate_card_url ); ?>" target="_blank" rel="noopener">Rate Card (PDF)</a>
      </div>
    </aside>
  </section>

  <!-- Archive with filters -->
  <section class="card" id="history" style="margin-top:16px" aria-labelledby="historyTitle">
    <div style="display:flex;justify-content:space-between;align-items:end;gap:8px;flex-wrap:wrap">
      <h2 id="historyTitle" class="h">Past editions</h2>
      <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
        <select id="monthFilter"><option value="">All months</option></select>
        <input id="searchInput" type="search" placeholder="Search title or ID…" style="padding:10px 12px;border:1px solid var(--border);border-radius:10px">
        <button class="btn ghost" id="applyFilters" type="button">Filter</button>
      </div>
    </div>
    <div class="list" id="archiveList" aria-live="polite" aria-busy="true" style="margin-top:8px">
      <div style="height:44px;background:#f1f5f9;border-radius:10px;"></div>
      <div style="height:44px;background:#f1f5f9;border-radius:10px;"></div>
      <div style="height:44px;background:#f1f5f9;border-radius:10px;"></div>
    </div>
    <div id="archiveEmpty" class="muted" hidden>No results.</div>
    <div id="archiveError" class="pill err" hidden></div>
  </section>
</main>

<footer class="site-footer">
  <div>© <?php echo date('Y'); ?> KandaNews Africa Co.</div>
  <div><a class="muted" href="/privacy">Privacy</a> · <a class="muted" href="/terms">Terms</a></div>
</footer>

<script>
/* REST config into JS */
const KANDA = { base: <?php echo json_encode($rest_base); ?>, nonce: <?php echo json_encode($rest_nonce); ?> };

(function(){
  const $ = id=>document.getElementById(id);

  const latestMeta=$('latestMeta'), openLatest=$('openLatest'), latestErr=$('latestError'), latestSkel=$('latestSkeleton');
  const archiveList=$('archiveList'), archiveEmpty=$('archiveEmpty'), archiveErr=$('archiveError');
  const subStatusPill=$('subStatusPill'), notif=$('notif'), planMsg=$('planMsg');
  const monthFilter=$('monthFilter'), searchInput=$('searchInput'), applyFilters=$('applyFilters');
  const adSlot=$('adSlot');

  let ACTIVE=false;       // subscription gate
  let editions=[], filtered=[], latest=null;

  // Helpers
  async function fetchJSON(url, opts={}, { timeoutMs=12000, retries=1 }={}){
    const ctl = new AbortController(); const t=setTimeout(()=>ctl.abort(),timeoutMs);
    try{
      const res = await fetch(url,{credentials:'include',headers:{'X-WP-Nonce':KANDA.nonce,...(opts.headers||{})},signal:ctl.signal,...opts});
      if(!res.ok){ const text=await res.text().catch(()=> ''); const err=new Error(`HTTP ${res.status}`); err.status=res.status; err.body=text; throw err;}
      const ct = res.headers.get('content-type')||''; if(!ct.includes('application/json')) return {}; return await res.json();
    }catch(e){ if(retries>0) return fetchJSON(url,opts,{timeoutMs,retries:retries-1}); throw e; } finally{ clearTimeout(t); }
  }
  const fmt      = new Intl.DateTimeFormat(undefined,{dateStyle:'medium'});
  const fmtMonth = (y,m)=> new Date(+y,+m-1,1).toLocaleString(undefined,{month:'long',year:'numeric'});
  const ym = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
  const sleep = ms=>new Promise(r=>setTimeout(r,ms));
  function explain(e){
    if (e.name==='AbortError') return 'Network is slow; timed out.';
    if (e.status===401||e.status===403) return 'Session expired or forbidden.';
    return 'Something went wrong.';
  }
  function escapeHTML(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

  // Subscription status
  async function loadSubStatus(){
    try{
      const s = await fetchJSON(`${KANDA.base}/subscriptions/status`);
      ACTIVE = !!(s && s.active);
      if (ACTIVE){
        const d = s.expires_at ? fmt.format(new Date(s.expires_at)) : null;
        subStatusPill.className='pill '+(s.days_left<=1?'warn':'ok');
        subStatusPill.textContent = d ? `Active — expires ${d} (${s.days_left} day${s.days_left===1?'':'s'} left)` : `Active — ${s.days_left} day(s) left`;
      } else {
        subStatusPill.className='pill err'; subStatusPill.textContent='No current subscription';
      }
    }catch(_){ subStatusPill.className='pill'; subStatusPill.textContent='Status unavailable'; }
  }

  // Editions + Filters
  function fillMonthFilter(items){
    const set=new Set(items.map(it=> it.published_at ? ym(new Date(it.published_at)) : '').filter(Boolean));
    const arr=Array.from(set).sort().reverse();
    monthFilter.innerHTML='<option value="">All months</option>'+arr.map(v=>{
      const [y,m]=v.split('-'); return `<option value="${v}">${escapeHTML(fmtMonth(y,m))}</option>`;
    }).join('');
  }

  function applyArchiveFilters(){
    const mVal=monthFilter.value, qVal=(searchInput.value||'').toLowerCase();
    filtered = editions.slice(1).filter(ed=>{
      let pass=true;
      if (mVal && ed.published_at){ pass = pass && (ym(new Date(ed.published_at))===mVal); }
      if (qVal){ const t=(ed.title||'').toLowerCase(), id=(ed.id||'').toLowerCase(); pass = pass && (t.includes(qVal)||id.includes(qVal)); }
      return pass;
    });
    renderArchive();
  }

  function renderArchive(){
    archiveList.innerHTML='';
    if(!filtered.length){ archiveEmpty.hidden=false; return; }
    archiveEmpty.hidden=true;
    filtered.forEach(ed=>{
      const row=document.createElement('button');
      row.type='button'; row.className='row'+(ACTIVE?'':' locked');
      const title=ed.title||ed.id||'Untitled';
      const right=ed.published_at?fmt.format(new Date(ed.published_at)):(ed.id||'');
      row.setAttribute('aria-label',`Open ${title}`);
      row.innerHTML=`<span>${escapeHTML(title)}</span><span class="muted">${escapeHTML(String(right))}</span>`;
      row.addEventListener('click', ()=> requireActive(()=> openSigned(ed.id), row));
      archiveList.appendChild(row);
    });
  }

  async function loadEditions(){
    try{
      const data = await fetchJSON(`${KANDA.base}/editions?limit=30`);
      editions = Array.isArray(data.items) ? data.items : [];
      if (editions.length){
        latest = editions[0];
        const title=latest.title||latest.id||'Untitled';
        const date = latest.published_at ? ` • ${fmt.format(new Date(latest.published_at))}` : '';
        latestMeta.textContent = `${title}${date}`;
        openLatest.disabled = false;
      } else {
        latestMeta.textContent='No edition published yet.'; openLatest.disabled=true;
      }
      fillMonthFilter(editions);
      filtered = editions.slice(1);
      renderArchive();
    }catch(e){
      const msg=explain(e); latestErr.textContent=msg; latestErr.hidden=false; archiveErr.textContent=msg; archiveErr.hidden=false;
    }finally{
      latestSkel.style.display='none'; archiveList.setAttribute('aria-busy','false');
    }
  }

  // Ad slot
  async function loadAd(){
    try{
      const ad = await fetchJSON(`${KANDA.base}/ads/current`);
      if (ad && (ad.html || ad.image)){
        if (ad.html){ adSlot.innerHTML = ad.html; } // ensure your API sanitizes HTML
        else {
          adSlot.innerHTML='';
          const img=new Image(); img.src=ad.image; img.alt=ad.alt||'Sponsored'; img.style.maxWidth='100%'; img.style.borderRadius='10px';
          if (ad.click){ const a=document.createElement('a'); a.href=ad.click; a.target='_blank'; a.rel='noopener'; a.appendChild(img); adSlot.appendChild(a);}
          else adSlot.appendChild(img);
        }
      } else { adSlot.textContent = 'Your brand here — reach students, professionals & entrepreneurs.'; }
    }catch(_){ adSlot.textContent = 'Sponsored content unavailable.'; }
  }

  // Gating (UI)
  function requireActive(okFn, el){
    if (ACTIVE){ okFn(); return; }
    notif.hidden=false; notif.textContent='Subscribe to open editions. Choose a plan below.';
    document.getElementById('plans').scrollIntoView({behavior:'smooth',block:'center'});
    if(el){ el.classList.add('shake'); setTimeout(()=>el.classList.remove('shake'), 350); }
  }

  // Open signed URL (server also guards with 403 if not subscribed)
  async function openSigned(editionId){
    try{
      const urlData = await fetchJSON(`${KANDA.base}/editions/${encodeURIComponent(editionId)}/signed-url`);
      if (urlData && urlData.url) location.href = urlData.url;
      else alert('No URL returned for this edition.');
    }catch(e){
      if (e.status===403){
        notif.hidden=false; notif.textContent='Subscription required. Select a plan below.';
        document.getElementById('plans').scrollIntoView({behavior:'smooth',block:'center'});
        return;
      }
      alert(explain(e));
    }
  }

  // Plans → Flutterwave hosted checkout
  document.getElementById('plans').addEventListener('click', async (ev)=>{
    const btn = ev.target.closest('button[data-plan]'); if(!btn) return;
    const plan=btn.dataset.plan, amount=Number(btn.dataset.amount||0);
    planMsg.textContent=`Selected ${plan} (UGX ${amount.toLocaleString()}). Opening checkout…`;
    try{
      const checkout = await fetchJSON(`${KANDA.base}/subscriptions/checkout`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ plan, amount, currency:'UGX' })
      });
      if (checkout && checkout.redirect){ location.href = checkout.redirect; return; }
      planMsg.textContent='Could not start checkout.';
    }catch(e){ planMsg.textContent = explain(e); }
  });

  // Buttons (guarded)
  document.getElementById('read-latest').addEventListener('click', ()=> requireActive(()=> latest && openSigned(latest.id), document.getElementById('read-latest')));
  openLatest.addEventListener('click', ()=> requireActive(()=> latest && openSigned(latest.id), openLatest));

  // Filters
  applyFilters.addEventListener('click', applyArchiveFilters);
  searchInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); applyArchiveFilters(); } });
  monthFilter.addEventListener('change', applyArchiveFilters);

  // ======== Resilient emoji typewriter (DOM-ready, no globals, static fallback) ========
  (function(){
    if (window.__kandaTypewriterStarted) return;
    window.__kandaTypewriterStarted = true;

    function onReady(cb){
      if (document.readyState === 'complete' || document.readyState === 'interactive') cb();
      else document.addEventListener('DOMContentLoaded', cb, { once:true });
    }

    onReady(()=>{
      const el   = document.getElementById('typeTarget');
      const logo = document.getElementById('brandLogo');
      if (!el) return;

      // Read phrases from data-attribute (set in HTML)
      let phrases = [];
      try { phrases = JSON.parse(el.dataset.phrases || '[]'); } catch(_){ phrases = []; }
      if (!phrases || !phrases.length){ phrases = ['Inspiring Africa 🌍']; }

      // Rebuild inside (but we already had a static fallback visible)
      const textNode = document.createTextNode('');
      const cursor   = document.createElement('span'); cursor.className='cursor';
      el.innerHTML = ''; el.appendChild(textNode); el.appendChild(cursor);

      function segments(s){
        try{
          if ('Segmenter' in Intl){
            const seg = new Intl.Segmenter(undefined, { granularity:'grapheme' });
            return Array.from(seg.segment(s), x => x.segment);
          }
        }catch(_){}
        return Array.from(s); // fallback
      }
      const sleep = ms => new Promise(r=>setTimeout(r, ms));

      async function type(s, speed=70){
        const segs = segments(s);
        for (let i=0;i<segs.length;i++){
          textNode.nodeValue = segs.slice(0,i+1).join('');
          await sleep(speed);
        }
        await sleep(800);
        return segs;
      }
      async function erase(segs){
        for (let i=segs.length;i>0;i--){
          textNode.nodeValue = segs.slice(0,i-1).join('');
          await sleep(12);
        }
      }

      (async function loop(){
        let revealed = false;
        for(;;){
          for (const p of phrases){
            const segs = await type(p);
            if (!revealed){ revealed = true; setTimeout(()=>logo && logo.classList.add('show'), 250); }
            await erase(segs);
          }
        }
      })();
    });
  })();
  // =====================================================================================

  // Boot
  loadSubStatus();
  loadEditions();
  loadAd();

  // Payment return banners (?paid=1|0)
  try{
    const params=new URLSearchParams(location.search);
    if (params.get('paid')==='1'){ notif.hidden=false; notif.textContent='Payment successful — your access is now active.'; await loadSubStatus(); }
    else if (params.get('paid')==='0'){ notif.hidden=false; notif.textContent='Payment was not completed. You can try again below.'; }
  }catch(_){}
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
