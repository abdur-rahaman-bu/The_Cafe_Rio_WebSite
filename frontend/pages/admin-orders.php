<?php
// frontend/pages/admin-orders.php — Read-only: Order, User, Total, Status, Payment, Method, Placed
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • Orders</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
    .muted{ color:#6c757d }
    .badge-pill{ border-radius:999px; padding:.35rem .6rem }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Orders</h1>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- Simple filters (optional) -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-3">
            <div class="col-sm-3">
              <label class="form-label">Status</label>
              <select id="f_status" class="form-select">
                <option value="">(any)</option>
                <option value="pending">pending</option>
                <option value="confirmed">confirmed</option>
                <option value="cancelled">cancelled</option>
              </select>
            </div>
            <div class="col-sm-3">
              <label class="form-label">From (date)</label>
              <input id="f_from" type="date" class="form-control">
            </div>
            <div class="col-sm-3">
              <label class="form-label">To (date)</label>
              <input id="f_to" type="date" class="form-control">
            </div>
            <div class="col-sm-3">
              <label class="form-label">User ID</label>
              <input id="f_user" type="number" min="1" class="form-control" placeholder="e.g. 2">
            </div>
            <div class="col-12 d-flex align-items-end gap-2">
              <button id="btnApply" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
              <button id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Read-only grid -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All orders</h5>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
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
              <tbody id="grid">
                <tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>
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
    function alertBox(sel, type, msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); if (b) b.classList.add('d-none'); }
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }

    function ensureAdmin(){
      const u = curUser();
      if (!u || String(u.role||'').toLowerCase()!=='admin'){ alertBox('#alert','danger','Admin required'); return null; }
      return u;
    }

    async function loadList(){
      const admin = ensureAdmin(); if (!admin) return;
      hideAlert('#listAlert');
      const grid = $('#grid'); grid.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>';
      try{
        const qs = new URLSearchParams();
        qs.set('actor_user_id', String(admin.user_id));
        const st = $('#f_status').value || '';
        const df = $('#f_from').value || '';
        const dt = $('#f_to').value || '';
        const uid = $('#f_user').value || '';
        if (st) qs.set('status', st);
        if (df) qs.set('date_from', df);
        if (dt) qs.set('date_to', dt);
        if (uid) qs.set('user_id', uid);
        const res = await fetch(`${APP}/backend/public/index.php?r=orders&a=list&${qs.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load orders'); return; }
        const items = data.items || [];
        if (!items.length){ grid.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No orders</td></tr>'; return; }
        const badge = s => (s==='confirmed' ? 'text-bg-success' : s==='pending' ? 'text-bg-warning' : 'text-bg-secondary');
        const pBadge = s => (s==='paid' ? 'text-bg-primary' : s==='refunded' ? 'text-bg-info' : 'text-bg-secondary');
        grid.innerHTML = items.map(r=>`
          <tr>
            <td class="mono">${r.order_id}</td>
            <td>${r.customer_name || ('User #'+r.user_id)}</td>
            <td class="mono">৳${Number(r.total_amount||0).toFixed(0)}</td>
            <td><span class="badge ${badge(r.status)} badge-pill">${r.status}</span></td>
            <td><span class="badge ${pBadge(r.payment_status)} badge-pill">${r.payment_status}</span></td>
            <td>${r.payment_method || '—'}</td>
            <td><span class="small">${r.created_at}</span></td>
          </tr>
        `).join('');
      }catch(_){
        alertBox('#listAlert','danger','Network error');
      }
    }

    document.getElementById('btnApply').addEventListener('click', loadList);
    document.getElementById('btnReset').addEventListener('click', ()=>{ $('#f_status').value=''; $('#f_from').value=''; $('#f_to').value=''; $('#f_user').value=''; loadList(); });
    window.addEventListener('load', loadList);
  </script>
</body>
</html>
