<?php
// frontend/pages/my-reservations.php — User-scoped reservations + details modal
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Reservations | The Cafe Rio – Gulshan</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap-icons/bootstrap-icons.css" />
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
    .muted{ color:#6c757d }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">My Reservations</h1>
        <a href="/restaurant-app/frontend/pages/reservations.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-square"></i> New reservation</a>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- User select (if not logged) -->
      <div class="card-elev mb-3" id="userWrap">
        <div class="card-body p-3 p-md-4">
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label">User ID</label>
              <input id="user_id" type="number" min="1" class="form-control" placeholder="e.g. 2">
              <div class="form-text">লগইন থাকলে অটো-ফিল হয়</div>
            </div>
            <div class="col-md-3">
              <button id="btnLoad" class="btn btn-outline-secondary" type="button"><i class="bi bi-arrow-repeat"></i> Load</button>
            </div>
          </div>
        </div>
      </div>

      <!-- List -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All reservations</h5>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>#</th><th class="mono">Resv</th><th>Date</th><th>Time</th><th>Dur</th><th>People</th><th>Type</th><th>Status</th><th style="width:120px">Actions</th>
                </tr>
              </thead>
              <tbody id="grid"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Details Modal -->
  <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0">
          <h5 class="modal-title">Reservation details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2 mb-2">
            <div class="col-md-4"><div class="muted">Resv ID</div><div id="d_id" class="fw-semibold mono">—</div></div>
            <div class="col-md-4"><div class="muted">Status</div><div id="d_status" class="fw-semibold">—</div></div>
            <div class="col-md-4"><div class="muted">User</div><div id="d_user" class="fw-semibold">—</div></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-md-4"><div class="muted">Date</div><div id="d_date" class="fw-semibold">—</div></div>
            <div class="col-md-4"><div class="muted">Time • Dur</div><div id="d_time" class="fw-semibold">—</div></div>
            <div class="col-md-4"><div class="muted">People • Type</div><div id="d_people" class="fw-semibold">—</div></div>
          </div>
          <div class="mb-2"><div class="muted">Special request</div><div id="d_note">—</div></div>
          <div class="table-responsive mt-2">
            <table class="table table-sm">
              <thead><tr><th>#</th><th>Table</th><th>Capacity</th><th>Zone</th><th>From</th><th>To</th></tr></thead>
              <tbody id="d_tables"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);

    function alertBox(sel, type, msg){ const b=$(sel); b.className=`alert alert-${type}`; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); b && b.classList.add('d-none'); }
    function currentUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }

    let USER_ID = null;

    function prefillUser(){
      const u = currentUser(); const wrap=$('#userWrap'), inp=$('#user_id');
      if (u && u.user_id){ USER_ID=u.user_id; inp.value=u.user_id; wrap.classList.add('d-none'); } else { wrap.classList.remove('d-none'); }
    }

    async function loadReservations(){
      hideAlert('#listAlert');
      const grid=$('#grid'); grid.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
      if (!(USER_ID>0)){ grid.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Provide a valid User ID</td></tr>'; return; }
      try{
        const qs = new URLSearchParams({ actor_user_id: String(USER_ID), limit: '200' });
        const res = await fetch(`${APP}/backend/public/index.php?r=reservations&a=user_list&${qs.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load'); return; }
        const items = data.items || [];
        grid.innerHTML = items.length ? items.map((r,i)=>`
          <tr data-id="${r.reservation_id}">
            <td>${i+1}</td>
            <td class="mono">${r.reservation_id}</td>
            <td>${r.reservation_date}</td>
            <td>${r.reservation_time}</td>
            <td>${r.duration_minutes}m</td>
            <td>${r.people_count}</td>
            <td>${r.table_type}</td>
            <td><span class="badge ${r.status==='confirmed'?'text-bg-success':r.status==='pending'?'text-bg-warning':'text-bg-secondary'}">${r.status}</span></td>
            <td><button class="btn btn-sm btn-outline-primary" data-act="view"><i class="bi bi-eye"></i> View</button></td>
          </tr>
        `).join('') : '<tr><td colspan="9" class="text-center text-muted">No reservations</td></tr>';
      }catch(_){ alertBox('#listAlert','danger','Network error'); }
    }

    // View details
    document.addEventListener('click', async (e)=>{
      const btn = e.target.closest('button[data-act="view"]'); if (!btn) return;
      const tr = btn.closest('tr'); const id=parseInt(tr.getAttribute('data-id')||'0',10);
      try{
        const qs = new URLSearchParams({ reservation_id: String(id) });
        const res = await fetch(`${APP}/backend/public/index.php?r=reservations&a=details&${qs.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alert('Failed to fetch details'); return; }
        const r = data.reservation || {}, tables = data.tables || [];
        $('#d_id').textContent = r.reservation_id || '—';
        $('#d_status').textContent = r.status || '—';
        $('#d_user').textContent = r.customer_name ? `${r.customer_name} (${r.email||''})` : ('User #'+(r.user_id||'—'));
        $('#d_date').textContent = r.reservation_date || '—';
        $('#d_time').textContent = (r.reservation_time||'—') + ' • ' + (r.duration_minutes||0) + 'm';
        $('#d_people').textContent = (r.people_count||0) + ' • ' + (r.table_type||'—');
        $('#d_note').textContent = r.special_request || '—';
        $('#d_tables').innerHTML = tables.length
          ? tables.map((t,i)=> `<tr><td>${i+1}</td><td>${t.name} (#${t.table_id})</td><td>${t.capacity}</td><td>${t.zone||''}</td><td>${t.from_time}</td><td>${t.to_time}</td></tr>`).join('')
          : '<tr><td colspan="6" class="text-center text-muted">No tables</td></tr>';
        if (window.bootstrap){ new bootstrap.Modal('#detailsModal').show(); } else { alert('Details loaded'); }
      }catch(_){ alert('Network error'); }
    });

    // Init
    window.addEventListener('load', ()=>{ prefillUser(); loadReservations(); });
    document.getElementById('btnLoad').addEventListener('click', ()=>{
      const v = parseInt(document.getElementById('user_id').value||'0',10);
      USER_ID = (v>0) ? v : USER_ID;
      loadReservations();
    });
  </script>
</body>
</html>
