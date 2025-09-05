<?php
// frontend/pages/admin-reviews.php — Admin read-only reviews with filters
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • Reviews</title>

  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Reviews</h1>
        <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i> Reload</button>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-2">
            <div class="col-sm-2"><label class="form-label">Item ID</label><input id="f_item" type="number" min="1" class="form-control"></div>
            <div class="col-sm-2"><label class="form-label">User ID</label><input id="f_user" type="number" min="1" class="form-control"></div>
            <div class="col-sm-2"><label class="form-label">Min rating</label><input id="f_min" type="number" min="1" max="5" class="form-control" value="1"></div>
            <div class="col-sm-3"><label class="form-label">From</label><input id="f_from" type="date" class="form-control"></div>
            <div class="col-sm-3"><label class="form-label">To</label><input id="f_to" type="date" class="form-control"></div>
            <div class="col-12 d-flex align-items-end gap-2">
              <button id="btnApply" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
              <button id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All reviews</h5>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">ID</th>
                  <th>User</th>
                  <th>Item</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody id="grid"><tr><td colspan="6" class="text-center text-muted">Loading…</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP=(window.APP_BASE||''); const $=s=>document.querySelector(s);
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }
    function alertBox(sel,type,msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }

    function ensureAdmin(){
      const u=curUser();
      if (!u || String(u.role||'').toLowerCase()!=='admin'){ alertBox('#alert','danger','Admin required'); return null; }
      return u;
    }

    function readFilters(){
      return {
        item_id: $('#f_item').value || '',
        user_id: $('#f_user').value || '',
        rating_min: $('#f_min').value || '',
        date_from: $('#f_from').value || '',
        date_to: $('#f_to').value || ''
      };
    }
    function resetFilters(){ $('#f_item').value=''; $('#f_user').value=''; $('#f_min').value='1'; $('#f_from').value=''; $('#f_to').value=''; }

    async function loadList(){
      const admin = ensureAdmin(); if (!admin) return;
      const grid=$('#grid'); grid.innerHTML='<tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>';
      try{
        const f=readFilters(); const qs=new URLSearchParams({ actor_user_id: String(admin.user_id), limit:'200' });
        if (f.item_id) qs.set('item_id', f.item_id);
        if (f.user_id) qs.set('user_id', f.user_id);
        if (f.rating_min) qs.set('rating_min', f.rating_min);
        if (f.date_from) qs.set('date_from', f.date_from);
        if (f.date_to) qs.set('date_to', f.date_to);
        const res=await fetch(`${APP}/backend/public/index.php?r=reviews&a=admin_list&${qs.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load'); return; }
        const items=data.items || [];
        if (!items.length){ grid.innerHTML='<tr><td colspan="6" class="text-center text-muted">No reviews</td></tr>'; return; }
        grid.innerHTML = items.map(r=>`
          <tr>
            <td class="mono">${r.review_id}</td>
            <td>${r.user_name || ('User #'+r.user_id)}</td>
            <td>${r.item_name || ('Item #'+r.item_id)}</td>
            <td>${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</td>
            <td>${r.comment || '—'}</td>
            <td><span class="small">${r.created_at}</span></td>
          </tr>
        `).join('');
      }catch(_){ alertBox('#listAlert','danger','Network error'); }
    }

    document.getElementById('btnApply').addEventListener('click', loadList);
    document.getElementById('btnReset').addEventListener('click', ()=>{ resetFilters(); loadList(); });
    document.getElementById('btnReload').addEventListener('click', loadList);
    window.addEventListener('load', loadList);
  </script>
</body>
</html>