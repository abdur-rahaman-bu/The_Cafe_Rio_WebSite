<?php
// frontend/pages/my-orders.php — Show "Pay now" for unpaid; calls orders&a=pay and refreshes
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Orders | The Cafe Rio – Gulshan</title>
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
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">My Orders</h1>
        <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag-plus"></i> New order</a>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <div class="card-elev mb-3" id="userWrap">
        <div class="card-body p-3 p-md-4">
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label">User ID</label>
              <input id="user_id" type="number" min="1" class="form-control" placeholder="e.g. 2">
              <div class="form-text">লগইন থাকলে অটো-ফিল হবে</div>
            </div>
            <div class="col-md-3">
              <button id="btnLoad" class="btn btn-outline-secondary" type="button"><i class="bi bi-arrow-repeat"></i> Load</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All orders</h5>
            <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i></button>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th class="mono">Order</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Payment</th>
                  <th>Method</th>
                  <th>Placed</th>
                  <th style="width:140px">Actions</th>
                </tr>
              </thead>
              <tbody id="grid">
                <tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Simple toast-like modal -->
      <div class="modal fade" id="payToast" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
          <div class="modal-header border-0"><h6 class="modal-title"><i class="bi bi-check2-circle text-success me-2"></i>Status updated</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body"><div id="toastMeta" class="small text-muted"></div></div>
        </div></div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP = (window.APP_BASE || '');
    const $  = s => document.querySelector(s);
    function showAlert(sel,type,msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); b && b.classList.add('d-none'); }
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }
    const fmt = v => '৳' + Number(v||0).toFixed(0);

    let USER_ID = null;

    function applyUser(){
      const u=curUser(); const wrap=$('#userWrap'); const inp=$('#user_id');
      if (u && u.user_id){ USER_ID=u.user_id; inp.value=u.user_id; wrap.classList.add('d-none'); } else { wrap.classList.remove('d-none'); }
    }

    async function loadOrders(){
      hideAlert('#listAlert');
      const grid=$('#grid'); grid.innerHTML='<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
      if (!(USER_ID>0)){ grid.innerHTML='<tr><td colspan="8" class="text-center text-muted">Provide a valid User ID</td></tr>'; return; }
      try{
        const qs=new URLSearchParams({ actor_user_id:String(USER_ID), limit:'200' });
        const res=await fetch(`${APP}/backend/public/index.php?r=orders&a=user_list&${qs.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){ showAlert('#listAlert','danger', data?.error || 'Failed to load orders'); return; }
        const items=data.items || [];
        if (!items.length){ grid.innerHTML='<tr><td colspan="8" class="text-center text-muted">No orders</td></tr>'; return; }
        const badge=s=> (s==='confirmed'?'text-bg-success':s==='pending'?'text-bg-warning':'text-bg-secondary');
        const pBadge=s=> (s==='paid'?'text-bg-primary':s==='refunded'?'text-bg-info':'text-bg-secondary');
        grid.innerHTML = items.map((r,i)=>`
          <tr data-id="${r.order_id}">
            <td>${i+1}</td>
            <td class="mono">${r.order_id}</td>
            <td class="mono">${fmt(r.total_amount)}</td>
            <td><span class="badge ${badge(r.status)} badge-pill">${r.status}</span></td>
            <td><span class="badge ${pBadge(r.payment_status)} badge-pill">${r.payment_status}</span></td>
            <td>${r.payment_method || '—'}</td>
            <td><span class="small">${r.created_at}</span></td>
            <td>
              ${r.payment_status==='unpaid'
                ? `<button class="btn btn-sm btn-danger" data-act="pay"><i class="bi bi-credit-card"></i> Pay now</button>`
                : `<button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-check2"></i> Paid</button>`
              }
            </td>
          </tr>
        `).join('');
      }catch(_){ showAlert('#listAlert','danger','Network error'); }
    }

    // Pay now
    document.addEventListener('click', async (e)=>{
      const btn=e.target.closest('button[data-act="pay"]'); if (!btn) return;
      const tr=btn.closest('tr'); const id=parseInt(tr?.getAttribute('data-id')||'0',10);
      if (!id) return;
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=orders&a=pay`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: USER_ID, order_id: id })
        });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){ alert(data?.error || 'Payment failed'); return; }
        document.getElementById('toastMeta').textContent = `Order #${data.order_id} • Paid ${fmt(data.total_amount||0)}`;
        if (window.bootstrap){ new bootstrap.Modal('#payToast').show(); }
        loadOrders();
      }catch(_){ alert('Network error'); }
    });

    document.getElementById('btnReload').addEventListener('click', loadOrders);
    document.getElementById('btnLoad').addEventListener('click', ()=>{ const v=parseInt(document.getElementById('user_id').value||'0',10); if (v>0){ USER_ID=v; loadOrders(); } });
    window.addEventListener('load', ()=>{ applyUser(); loadOrders(); });
  </script>
</body>
</html>
