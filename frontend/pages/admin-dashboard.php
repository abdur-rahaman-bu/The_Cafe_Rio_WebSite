<?php
// frontend/pages/admin-dashboard.php — Admin Dashboard (Glassmorphic UI)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • Dashboard | The Cafe Rio – Gulshan</title>

  <!-- Local Bootstrap -->
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <!-- Icons via CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* ====== Page Background (Glass Gradient) ====== */
    :root{
      --glass-bg: rgba(255,255,255,.18);
      --glass-brd: rgba(255,255,255,.35);
      --glass-sh: 0 10px 30px rgba(0,0,0,.15);
      --glass-sh-soft: 0 12px 40px rgba(0,0,0,.08);
      --muted: #6c757d;
      --txt: #1d2125;
      --brand1: #7dd3fc; /* sky-300 */
      --brand2: #c084fc; /* violet-400 */
      --brand3: #fbcfe8; /* pink-200 */
    }
    html,body{ height:100%; }
    body{
      color:var(--txt);
      background:
        radial-gradient(1200px 800px at 10% -10%, rgba(125,211,252,.45), transparent 60%),
        radial-gradient(1200px 800px at 110% 10%, rgba(192,132,252,.40), transparent 60%),
        radial-gradient(1000px 700px at 50% 120%, rgba(251,207,232,.35), transparent 60%),
        linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
      background-attachment: fixed;
      position: relative;
      overflow-x: hidden;
    }
    /* optional: very subtle noise for texture */
    body::before{
      content:"";
      position:fixed; inset:0;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feColorMatrix%3E%3CfeComponentTransfer%3E%3CfeFuncA type='table' tableValues='0 0 0 0 0 0 .04 .06 .04 0'/%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.08'/%3E%3C/svg%3E");
      pointer-events:none;
      z-index:-1;
    }

    /* ====== Glass Cards ====== */
    .card-elev{
      border:1px solid var(--glass-brd);
      background: var(--glass-bg);
      border-radius: 22px;
      box-shadow: var(--glass-sh-soft);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
    }
    .card-elev:hover{
      transform: translateY(-2px);
      box-shadow: var(--glass-sh);
      background: rgba(255,255,255,.22);
    }

    /* Header row */
    .page-head{
      background: rgba(255,255,255,.22);
      border:1px solid var(--glass-brd);
      box-shadow: var(--glass-sh-soft);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 18px;
      padding: 14px 18px;
    }

    /* KPIs */
    .kpi{ display:flex; align-items:center; gap:12px }
    .kpi .ico{
      width:46px; height:46px;
      display:flex; align-items:center; justify-content:center;
      border-radius:12px;
      border:1px solid var(--glass-brd);
      background: linear-gradient(135deg, rgba(255,255,255,.55), rgba(255,255,255,.2));
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.4), 0 6px 16px rgba(0,0,0,.05);
    }
    .muted{ color: var(--muted); }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }

    /* Buttons */
    .btn-glass{
      border:1px solid var(--glass-brd)!important;
      background: linear-gradient(135deg, rgba(255,255,255,.55), rgba(255,255,255,.15))!important;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 6px 16px rgba(0,0,0,.08);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .btn-glass:hover{ transform: translateY(-1px); box-shadow: 0 10px 22px rgba(0,0,0,.12); }

    /* Tables inside glass cards */
    .table-responsive{
      border:1px solid var(--glass-brd);
      border-radius: 14px;
      overflow: hidden;
    }
    .table{
      --bs-table-bg: transparent;
      margin-bottom:0;
    }
    .table thead th{
      background: linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,.15));
      border-bottom: 1px solid var(--glass-brd);
      color:#111827;
    }
    .table tbody tr{
      background: rgba(255,255,255,.25);
    }
    .table-striped tbody tr:nth-of-type(odd){
      background: rgba(255,255,255,.18);
    }
    .table td,.table th{ vertical-align: middle; }

    /* Badges: keep Bootstrap semantics but soften look */
    .badge{
      border:1px solid var(--glass-brd);
      background: linear-gradient(135deg, rgba(255,255,255,.6), rgba(255,255,255,.2));
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }
    .text-bg-success{ color:#065f46; }
    .text-bg-warning{ color:#92400e; }
    .text-bg-secondary{ color:#334155; }
    .text-bg-primary{ color:#0f172a; }
    .text-bg-info{ color:#0c4a6e; }

    /* Section spacing */
    .section-glass{
      padding: 32px 0 48px;
    }

    /* Grad label lines under section titles */
    .h5-grad{
      position: relative;
      display: inline-block;
      padding-bottom: .25rem;
    }
    .h5-grad::after{
      content:"";
      position:absolute; left:0; right:0; bottom:-6px; height:3px;
      border-radius: 99px;
      background: linear-gradient(90deg, var(--brand1), var(--brand2), var(--brand3));
      opacity:.7;
    }

    /* Reload area alert glass */
    .alert{
      border:1px solid var(--glass-brd);
      background: rgba(255,255,255,.35);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    /* Reduce motion preference */
    @media (prefers-reduced-motion: reduce){
      .card-elev, .btn-glass{ transition:none; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

  <section class="section-glass">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3 page-head">
        <h1 class="fw-bold mb-0">Dashboard</h1>
        <button id="btnReload" class="btn btn-outline-secondary btn-sm btn-glass" type="button">
          <i class="bi bi-arrow-clockwise me-1"></i> Reload
        </button>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- KPIs -->
      <div class="row g-3">
        <div class="col-md-3">
          <div class="card-elev">
            <div class="card-body">
              <div class="kpi">
                <div class="ico"><i class="bi bi-calendar-check"></i></div>
                <div>
                  <div class="muted small">Today Reservations</div>
                  <div id="k_resv" class="h4 mb-0">—</div>
                </div>
              </div>
              <div class="small muted mt-1" id="k_resv_meta">—</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-elev">
            <div class="card-body">
              <div class="kpi">
                <div class="ico"><i class="bi bi-receipt"></i></div>
                <div>
                  <div class="muted small">Today Orders</div>
                  <div id="k_ord" class="h4 mb-0">—</div>
                </div>
              </div>
              <div class="small muted mt-1" id="k_ord_meta">—</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-elev">
            <div class="card-body">
              <div class="kpi">
                <div class="ico"><i class="bi bi-credit-card-2-front"></i></div>
                <div>
                  <div class="muted small">Unpaid Orders</div>
                  <div id="k_unpaid" class="h4 mb-0">—</div>
                </div>
              </div>
              <div class="small muted mt-1" id="k_unpaid_meta">—</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-elev">
            <div class="card-body">
              <div class="kpi">
                <div class="ico"><i class="bi bi-grid-3x3-gap"></i></div>
                <div>
                  <div class="muted small">Tables Now</div>
                  <div class="h4 mb-0"><span id="k_tab_avl">—</span> <span class="muted small">available</span></div>
                </div>
              </div>
              <div class="small muted mt-1"><span id="k_tab_occ">—</span> occupied</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card-elev mt-4">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0 h5-grad">Recent Orders</h5>
          </div>
          <div id="ordAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">Order</th>
                  <th>User</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Payment</th>
                  <th>Method</th>
                  <th>Placed</th>
                </tr>
              </thead>
              <tbody id="ordGrid">
                <tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Reservations -->
      <div class="card-elev mt-4">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0 h5-grad">Recent Reservations</h5>
          </div>
          <div id="resvAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">Resv</th>
                  <th>User</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Dur</th>
                  <th>People</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Placed</th>
                </tr>
              </thead>
              <tbody id="resvGrid">
                <tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>
              </tbody>
            </table>
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
    function alertBox(sel, type, msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); if (b) b.classList.add('d-none'); }
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }
    function pad(n){ return String(n).padStart(2,'0'); }
    function todayStr(){ const d=new Date(); return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate()); }
    function nowHHMM(){ const d=new Date(); return pad(d.getHours())+':'+pad(d.getMinutes()); }

    function ensureAdmin(){
      const u=curUser();
      if (!u || String(u.role||'').toLowerCase()!=='admin'){
        alertBox('#alert','danger','Admin required');
        return null;
      }
      return u;
    }

    async function loadKPIs(admin){
      $('#k_resv').textContent='…'; $('#k_ord').textContent='…'; $('#k_unpaid').textContent='…'; $('#k_tab_avl').textContent='…'; $('#k_tab_occ').textContent='…';
      const tdy = todayStr();
      const now = nowHHMM();

      try{
        const oq = new URLSearchParams({ r:'orders', a:'list', actor_user_id: String(admin.user_id), limit:'500' });
        const ores = await fetch(`${APP}/backend/public/index.php?${oq.toString()}`);
        const od = await ores.json().catch(()=> ({}));
        const orders = Array.isArray(od.items) ? od.items : [];

        const rq = new URLSearchParams({ r:'reservations', a:'list', limit:'500' });
        const rres = await fetch(`${APP}/backend/public/index.php?${rq.toString()}`);
        const rd = await rres.json().catch(()=> ({}));
        const reservations = Array.isArray(rd.items) ? rd.items : [];

        const avq = new URLSearchParams({ r:'tables', a:'availability', date: tdy, time: now, duration: '90', people: '2' });
        const avres = await fetch(`${APP}/backend/public/index.php?${avq.toString()}`);
        const avd = await avres.json().catch(()=> ({}));
        const available = Array.isArray(avd.available) ? avd.available.length : 0;
        const occupied  = Array.isArray(avd.occupied)  ? avd.occupied.length  : 0;

        const todayOrders = orders.filter(o => (o.created_at||'').startsWith(tdy)).length;
        const unpaidOrders = orders.filter(o => (o.payment_status==='unpaid')).length;
        const todayReservations = reservations.filter(r => (r.reservation_date===tdy)).length;

        $('#k_resv').textContent = String(todayReservations);
        $('#k_ord').textContent = String(todayOrders);
        $('#k_unpaid').textContent = String(unpaidOrders);
        $('#k_tab_avl').textContent = String(available);
        $('#k_tab_occ').textContent = String(occupied);
        $('#k_resv_meta').textContent = 'Date: ' + tdy;
        $('#k_ord_meta').textContent = 'Date: ' + tdy;
        $('#k_unpaid_meta').textContent = 'All-time • unpaid';
      }catch(_){
        alertBox('#alert','danger','Failed to load dashboard KPIs');
      }
    }

    async function loadRecent(admin){
      try{
        hideAlert('#ordAlert');
        const oq = new URLSearchParams({ r:'orders', a:'list', actor_user_id: String(admin.user_id), limit:'100' });
        const res = await fetch(`${APP}/backend/public/index.php?${oq.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#ordAlert','danger', data?.error || 'Failed to load orders'); }
        const items = (data.items || []).slice(0, 10);
        const badge = s => (s==='confirmed' ? 'text-bg-success' : s==='pending' ? 'text-bg-warning' : 'text-bg-secondary');
        const pBadge = s => (s==='paid' ? 'text-bg-primary' : s==='refunded' ? 'text-bg-info' : 'text-bg-secondary');
        const grid = document.getElementById('ordGrid');
        if (!items.length){ grid.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No orders</td></tr>'; }
        else {
          grid.innerHTML = items.map(r=>`
            <tr>
              <td class="mono">${r.order_id}</td>
              <td>${r.customer_name || ('User #'+r.user_id)}</td>
              <td class="mono">${fmt(r.total_amount)}</td>
              <td><span class="badge ${badge(r.status)}">${r.status}</span></td>
              <td><span class="badge ${pBadge(r.payment_status)}">${r.payment_status}</span></td>
              <td>${r.payment_method || '—'}</td>
              <td><span class="small">${r.created_at}</span></td>
            </tr>
          `).join('');
        }
      }catch(_){
        alertBox('#ordAlert','danger','Network error (orders)');
      }

      try{
        hideAlert('#resvAlert');
        const rq = new URLSearchParams({ r:'reservations', a:'list', limit:'100' });
        const res = await fetch(`${APP}/backend/public/index.php?${rq.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#resvAlert','danger', data?.error || 'Failed to load reservations'); }
        const items = (data.items || []).slice(0, 10);
        const badge = s => (s==='confirmed' ? 'text-bg-success' : s==='pending' ? 'text-bg-warning' : 'text-bg-secondary');
        const grid = document.getElementById('resvGrid');
        if (!items.length){ grid.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No reservations</td></tr>'; }
        else {
          grid.innerHTML = items.map(r=>`
            <tr>
              <td class="mono">${r.reservation_id}</td>
              <td>${r.customer_name || ('User #'+r.user_id)}</td>
              <td>${r.reservation_date}</td>
              <td>${r.reservation_time}</td>
              <td>${Number(r.duration_minutes||0)}m</td>
              <td>${r.people_count}</td>
              <td>${r.table_type}</td>
              <td><span class="badge ${badge(r.status)}">${r.status}</span></td>
              <td><span class="small">${r.created_at || ''}</span></td>
            </tr>
          `).join('');
        }
      }catch(_){
        alertBox('#resvAlert','danger','Network error (reservations)');
      }
    }

    async function reloadAll(){
      const admin = ensureAdmin(); if (!admin) return;
      await loadKPIs(admin);
      await loadRecent(admin);
    }

    document.getElementById('btnReload').addEventListener('click', reloadAll);
    window.addEventListener('load', reloadAll);
  </script>
</body>
</html>
