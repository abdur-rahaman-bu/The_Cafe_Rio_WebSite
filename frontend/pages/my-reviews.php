<?php
// frontend/pages/my-reviews.php — User creates and views own reviews
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Reviews | The Cafe Rio – Gulshan</title>

  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">My Reviews</h1>
        <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag-plus"></i> Order</a>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- Create review -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <h5 class="fw-bold mb-3">Write a review</h5>
          <div class="row g-2">
            <div class="col-sm-2">
              <label class="form-label">User ID</label>
              <input id="user_id" type="number" min="1" class="form-control" placeholder="e.g. 2">
              <div class="form-text">লগইন থাকলে অটো-ফিল</div>
            </div>
            <div class="col-sm-3">
              <label class="form-label">Item ID</label>
              <input id="item_id" type="number" min="1" class="form-control" placeholder="e.g. 12">
            </div>
            <div class="col-sm-2">
              <label class="form-label">Rating (1–5)</label>
              <select id="rating" class="form-select">
                <option value="5" selected>5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
              </select>
            </div>
            <div class="col-sm-5">
              <label class="form-label">Comment</label>
              <input id="comment" class="form-control" placeholder="(optional)">
            </div>
            <div class="col-12 d-flex align-items-end">
              <button id="btnSubmit" class="btn btn-danger">
                <span id="sp" class="spinner-border spinner-border-sm me-2 d-none"></span>
                <i class="bi bi-send"></i> Submit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- My reviews -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All my reviews</h5>
            <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i></button>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">ID</th>
                  <th>Item</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody id="grid"><tr><td colspan="5" class="text-center text-muted">Loading…</td></tr></tbody>
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
    function alertBox(sel,type,msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); setTimeout(()=>b.classList.add('d-none'), 3000); }
    function setBusy(on){ $('#btnSubmit').disabled=!!on; $('#sp').classList.toggle('d-none', !on); }

    let USER_ID = null;

    function applyUser(){
      const u=curUser(); const inp=$('#user_id');
      if (u && u.user_id){ USER_ID=u.user_id; inp.value=u.user_id; }
    }

    async function loadMyReviews(){
      const grid=$('#grid'); grid.innerHTML='<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>';
      const uid = USER_ID || parseInt($('#user_id').value||'0',10);
      if (!(uid>0)){ grid.innerHTML='<tr><td colspan="5" class="text-center text-muted">Provide a valid User ID</td></tr>'; return; }
      try{
        const qs = new URLSearchParams({ actor_user_id: String(uid), limit: '200' });
        const res = await fetch(`${APP}/backend/public/index.php?r=reviews&a=user_list&${qs.toString()}`);
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load'); return; }
        const items = data.items || [];
        if (!items.length){ grid.innerHTML='<tr><td colspan="5" class="text-center text-muted">No reviews</td></tr>'; return; }
        grid.innerHTML = items.map(r=>`
          <tr>
            <td class="mono">${r.review_id}</td>
            <td>${r.item_name || ('Item #'+r.item_id)}</td>
            <td>${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</td>
            <td>${r.comment || '—'}</td>
            <td><span class="small">${r.created_at}</span></td>
          </tr>
        `).join('');
      }catch(_){ alertBox('#listAlert','danger','Network error'); }
    }

    document.getElementById('btnReload').addEventListener('click', loadMyReviews);

    document.getElementById('btnSubmit').addEventListener('click', async ()=>{
      const uid = USER_ID || parseInt($('#user_id').value||'0',10);
      const item_id = parseInt($('#item_id').value||'0',10);
      const rating = parseInt($('#rating').value||'5',10);
      const comment = ($('#comment').value||'').trim();
      if (!(uid>0) || !(item_id>0)) { alertBox('#alert','warning','Valid User ID & Item ID required'); return; }
      setBusy(true);
      try{
        const res = await fetch(`${APP}/backend/public/index.php?r=reviews&a=create`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ user_id: uid, item_id, rating, comment })
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){
          const msg = data?.error || `Failed (${res.status})`;
          alertBox('#alert', res.status===409 ? 'warning' : 'danger', msg);
          return;
        }
        $('#comment').value=''; loadMyReviews();
      }catch(_){ alertBox('#alert','danger','Network error'); }
      finally{ setBusy(false); }
    });

    window.addEventListener('load', ()=>{ applyUser(); loadMyReviews(); });
  </script>
</body>
</html>
