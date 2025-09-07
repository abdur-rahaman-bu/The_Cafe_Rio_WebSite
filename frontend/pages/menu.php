<?php
// frontend/pages/menu.php — Public menu browser (read-only list with search/category filter)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>The Cafe Rio — Gulshan | Dine-in, Delivery, Reservations</title>
  <meta name="description" content="The Cafe Rio — Gulshan: Fresh menu, online ordering, table reservations, and family-friendly dining. Open daily for dine-in and delivery." />
  <link rel="canonical" href="./assets/images/logo.png" />

 <!-- Favicon / Tab Logo -->
<link rel="icon" type="image/png" href="../assets/images/logo.png" />

  <!-- Open Graph -->
  <meta property="og:title" content="The Cafe Rio — Gulshan" />
  <meta property="og:description" content="Order online, reserve tables, and enjoy signature dishes at The Cafe Rio — Gulshan." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="/restaurant-app/index.php" />
  <meta property="og:image" content="/restaurant-app/frontend/assets/images/_placeholder.png" />

  <!-- Local Bootstrap (no CDN) -->
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />

  <!-- Leaflet CSS (for interactive map) -->
  <link rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin="anonymous">
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin="anonymous"></script>


  
  <style>
    .hero {
      height: 90vh;
      width: 100%;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
    }

    .hero img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 80%;
      object-fit: cover;
      z-index: -1;
    }

    .hero .overlay {
      background: rgba(0, 0, 0, 0.5);
      padding: 20px;
      border-radius: 12px;
    }

    .card-elev {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
  }
  .card-elev:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  }
  /* Menu item design */
  .menu-item {
    background: #f8f9fa;
    padding: 15px;
    text-align: center;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease-in-out;
    cursor: pointer;
  }

  /* Menu item hover colourful effect */
  .menu-item:hover {
    background: linear-gradient(135deg, #ff9a9e, #fad0c4);
    color: #fff;
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  }
    
    .muted{ color:#6c757d }
    .menu-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px }
    .menu-card{ border:1px solid #eef1f4; border-radius:14px; background:#fff; overflow:hidden; display:flex; flex-direction:column; }
    .menu-img{ height:160px; background:#f5f7fa; }
    .menu-img img{ width:100%; height:100%; object-fit:cover; display:block; }
    .menu-body{ padding:12px; display:flex; flex-direction:column; gap:6px; }
    .menu-title{ font-weight:700; line-height:1.2; }
    .chip{ display:inline-block; padding:.12rem .5rem; border-radius:999px; background:#eef1f4; font-size:.78rem }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

   <!-- Hero Section -->
   <div class="hero position-relative">
    <!-- Overlay Content -->
    <img src="../assets/images/sbg.png" alt="Campus">
    <div class="overlay position-absolute top-50 start-50 translate-middle text-center text-white">
      <h1 class="fw-bold display-5 fst-italic">OUR MENU</h1>
      <p class="lead fst-italic  ">
        <a class="nav-link" href="/restaurant-app/index.php">HOME</a>
      </p>
      <!-- <a href="#about" class="btn btn-primary btn-lg mt-3">Learn More</a> -->
    </div>
  </div>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Our Menu</h1>
        <div class="d-flex align-items-center gap-2">
          <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag"></i> Order</a>
          <a href="/restaurant-app/frontend/pages/reviews.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-star"></i> Reviews</a>
        </div>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-2 align-items-end">
            <div class="col-sm-4"><label class="form-label">Search</label><input id="q" class="form-control" placeholder="name, description"></div>
            <div class="col-sm-4"><label class="form-label">Category</label><input id="cat" class="form-control" placeholder="e.g. Pizza, Pasta"></div>
            <div class="col-sm-4 d-flex align-items-end gap-2">
              <button id="btnApply" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
              <button id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</button>
              <button id="btnReload" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Reload</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card-elev">
  <div class="card-body p-3 bg-info-subtle">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="fw-bold mb-0">All items</h5>
      <span id="meta" class="small muted">—</span>
    </div>
    <div id="menuAlert" class="alert d-none" role="alert"></div>

    <!-- Example grid items -->
    <div id="grid" class="menu-grid" aria-live="polite" aria-busy="true">
      <div class="menu-item">🍕 Pizza</div>
      <div class="menu-item">🍔 Burger</div>
      <div class="menu-item">🥗 Salad</div>
      <div class="menu-item">🍣 Sushi</div>
    </div>
  </div>
</div>
    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);
    const fmt = v => '৳' + Number(v||0).toFixed(0);

    let MENU = [];

    function cardHtml(it){
      const img = it.image ? `${APP}/backend/public/uploads/menu/${it.image}` : '';
      return `
        <div class="menu-card">
          <div class="menu-img">
            ${img ? `<img src="${img}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='${APP}/frontend/assets/images/_placeholder.png';">` : `<img src='${APP}/frontend/assets/images/_placeholder.png' alt=''>`}
          </div>
          <div class="menu-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="menu-title">${it.name||''}</div>
              <div class="fw-bold mono">${fmt(it.price)}</div>
            </div>
            <div class="muted small">${it.description || ''}</div>
            ${it.category ? `<span class="chip">${it.category}</span>` : ''}
          </div>
        </div>`;
    }

    function render(list){
      const grid = $('#grid');
      grid.setAttribute('aria-busy','false');
      $('#meta').textContent = `${list.length} item${list.length>1?'s':''}`;
      grid.innerHTML = list.length ? list.map(cardHtml).join('') : '<div class="muted">No items found</div>';
    }

    function applyFilter(){
      const q = ($('#q').value||'').toLowerCase().trim();
      const c = ($('#cat').value||'').toLowerCase().trim();
      const list = MENU.filter(it=>{
        const blob = `${it.name||''} ${it.description||''} ${it.category||''}`.toLowerCase();
        const okQ = q ? blob.includes(q) : true;
        const okC = c ? String(it.category||'').toLowerCase().includes(c) : true;
        return okQ && okC;
      });
      render(list);
    }

    async function loadMenu(){
      const grid=$('#grid'), alert=$('#menuAlert'); alert.classList.add('d-none'); grid.setAttribute('aria-busy','true');
      try{
        const qs = new URLSearchParams({ r:'menu', a:'list', limit:'500' }); // all items (available+unavailable if not filtered)
        const res = await fetch(`${APP}/backend/public/index.php?${qs.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Failed to load menu');
        MENU = data.items || [];
        applyFilter();
      }catch(err){
        alert.className='alert alert-danger';
        alert.textContent = err?.message || 'Network error';
        alert.classList.remove('d-none');
        $('#meta').textContent = '—';
        $('#grid').innerHTML = '<div class="muted">Unable to load menu</div>';
      }finally{
        grid.setAttribute('aria-busy','false');
      }
    }

    // Events
    document.getElementById('btnApply').addEventListener('click', applyFilter);
    document.getElementById('btnReset').addEventListener('click', ()=>{ $('#q').value=''; $('#cat').value=''; applyFilter(); });
    document.getElementById('btnReload').addEventListener('click', loadMenu);

    // Init
    window.addEventListener('load', loadMenu);
  </script>
</body>
</html>
