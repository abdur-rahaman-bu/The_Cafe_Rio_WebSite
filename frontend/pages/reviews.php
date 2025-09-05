<?php
// frontend/pages/reviews.php — Public reviews by item: select item, see aggregate + list, submit if logged-in
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reviews | The Cafe Rio – Gulshan</title>

  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
    .muted{ color:#6c757d }
    .star{ color:#f59f00 }
    .star.gray{ color:#d0d5dd }
    .item-chip{ display:inline-block; padding:.2rem .5rem; border-radius:999px; background:#eef1f4; font-size:.8rem }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Reviews</h1>
        <div class="d-flex align-items-center gap-2">
          <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag"></i> Order</a>
          <a href="/restaurant-app/frontend/pages/my-reviews.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-person-lines-fill"></i> My reviews</a>
        </div>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- Item selector -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Choose item</label>
              <select id="item" class="form-select"></select>
              <div class="form-text">উপরে থেকে মেনু আইটেম সিলেক্ট করলে তার রিভিউ দেখা যাবে</div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Filter by category</label>
              <input id="f_cat" class="form-control" placeholder="e.g. Pizza">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button id="btnApply" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
              <button id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</button>
            </div>
          </div>
          <div id="meta" class="small text-muted mt-2">—</div>
        </div>
      </div>

      <!-- Aggregate + Submit -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-3">
                <div id="avgStars" class="h4 mb-0"></div>
                <div>
                  <div id="avgNum" class="h4 mb-0">—</div>
                  <div id="cnt" class="muted small">—</div>
                </div>
              </div>
              <div id="itemInfo" class="mt-2"></div>
            </div>

            <div class="col-md-6">
              <div id="loginNotice" class="alert alert-warning d-none" role="alert">
                লগইন করলে সরাসরি রিভিউ দেয়া যাবে। <a id="loginLink" class="alert-link" href="login.php">Login</a>
              </div>

              <div id="revForm" class="row g-2 d-none">
                <div class="col-sm-3">
                  <label class="form-label">Rating (1–5)</label>
                  <select id="rating" class="form-select">
                    <option value="5" selected>5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                  </select>
                </div>
                <div class="col-sm-9">
                  <label class="form-label">Comment</label>
                  <input id="comment" class="form-control" placeholder="(optional)">
                </div>
                <div class="col-12 d-flex align-items-end">
                  <button id="btnSubmit" class="btn btn-danger">
                    <span id="sp" class="spinner-border spinner-border-sm me-2 d-none"></span>
                    <i class="bi bi-send"></i> Submit review
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Reviews list -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All reviews</h5>
            <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i></button>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">#</th>
                  <th>User</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody id="grid">
                <tr><td colspan="5" class="text-center text-muted">Select an item</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP=(window.APP_BASE||''); const $=s=>document.querySelector(s); const $$=s=>Array.prototype.slice.call(document.querySelectorAll(s));
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }
    function alertBox(sel,type,msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); setTimeout(()=>b.classList.add('d-none'), 3500); }
    const stars = n => '★'.repeat(n) + '☆'.repeat(5-n);

    // Escape HTML to prevent XSS when using innerHTML
    const esc = s => String(s ?? '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;');

    // State
    let MENU=[], FILTER_CAT='', ITEM_ID=null, USER=null;

    function applyUser(){
      USER = curUser();
      const ln=$('#loginNotice'), lf=$('#revForm'), lk=$('#loginLink');
      const redirect = encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/reviews.php');
      if (lk) lk.href = (window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + redirect;
      if (USER && USER.user_id){ ln.classList.add('d-none'); lf.classList.remove('d-none'); }
      else { ln.classList.remove('d-none'); lf.classList.add('d-none'); }
    }

    function renderItemSelect(){
      const sel=$('#item');
      const list = FILTER_CAT ? MENU.filter(x => String(x.category||'').toLowerCase().includes(FILTER_CAT)) : MENU;
      sel.innerHTML = list.map(it => `<option value="${it.item_id}">${esc(it.name)} ${it.category?('• '+esc(it.category)) : ''}</option>`).join('');
      if (list.length){
        // keep current if still present, else default to first
        ITEM_ID = (ITEM_ID && list.some(x=>x.item_id===ITEM_ID)) ? ITEM_ID : list.item_id;
        sel.value=String(ITEM_ID);
        updateMeta();
        loadReviews();
      } else {
        ITEM_ID=null;
        $('#grid').innerHTML='<tr><td colspan="5" class="text-center text-muted">No items match filter</td></tr>';
        resetAggregate();
      }
    }

    function resetAggregate(){
      $('#avgStars').innerHTML=''; $('#avgNum').textContent='—'; $('#cnt').textContent='—'; $('#itemInfo').innerHTML='';
    }

    function updateMeta(){
      const it = MENU.find(x=> x.item_id===ITEM_ID);
      const meta = it ? (`${esc(it.name)} ${it.category?('<span class="item-chip ms-1">'+esc(it.category)+'</span>'):''}`) : '—';
      $('#meta').innerHTML = meta;
    }

    async function loadMenu(){
      try{
        const qs=new URLSearchParams({ r:'menu', a:'list', status:'available', limit:'500' });
        const res=await fetch(`${APP}/backend/public/index.php?${qs.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Failed to load menu');
        MENU = data.items || [];
        renderItemSelect();
      }catch(err){
        alertBox('#alert','danger', err?.message || 'Network error');
      }
    }

    async function loadReviews(){
      if (!ITEM_ID){ $('#grid').innerHTML='<tr><td colspan="5" class="text-center text-muted">Select an item</td></tr>'; return; }
      resetAggregate(); $('#grid').innerHTML='<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>';
      try{
        const qs=new URLSearchParams({ r:'reviews', a:'list', item_id:String(ITEM_ID), limit:'200' });
        const res=await fetch(`${APP}/backend/public/index.php?${qs.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load'); return; }

        const avg = Number(data?.rating_avg ?? 0);
        const cnt = Number(data?.rating_count ?? 0);
        $('#avgStars').innerHTML = `<span class="star">${'★'.repeat(Math.round(avg))}</span><span class="star gray">${'☆'.repeat(5-Math.round(avg))}</span>`;
        $('#avgNum').textContent = isNaN(avg) || !cnt ? 'No rating' : (avg.toFixed(2) + '/5');
        $('#cnt').textContent = cnt ? (cnt + ' review' + (cnt>1?'s':'')) : 'No reviews yet';

        const items = data.items || [];
        if (!items.length){ $('#grid').innerHTML='<tr><td colspan="5" class="text-center text-muted">No reviews yet</td></tr>'; return; }
        $('#grid').innerHTML = items.map((r,i)=>`
          <tr>
            <td>${i+1}</td>
            <td>${esc(r.user_name || ('User #'+r.user_id))}</td>
            <td>${stars(Number(r.rating||0))}</td>
            <td>${esc(r.comment || '—')}</td>
            <td><span class="small">${esc(r.created_at)}</span></td>
          </tr>
        `).join('');
      }catch(_){
        alertBox('#listAlert','danger','Network error');
      }
    }

    function setBusy(on){ const b=$('#btnSubmit'), sp=$('#sp'); if (b) b.disabled=!!on; if (sp) sp.classList.toggle('d-none', !on); }

    async function submitReview(){
      if (!USER || !USER.user_id){ alertBox('#alert','warning','Login required'); return; }
      if (!ITEM_ID){ alertBox('#alert','warning','Select an item first'); return; }
      const rating = parseInt($('#rating').value||'5',10);
      const comment = ($('#comment').value||'').trim();
      setBusy(true);
      try{
        const res = await fetch(`${APP}/backend/public/index.php?r=reviews&a=create`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ user_id: USER.user_id, item_id: ITEM_ID, rating, comment })
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){
          const msg = data?.error || `Failed (${res.status})`;
          alertBox('#alert', res.status===409 ? 'warning' : 'danger', msg);
          return;
        }
        $('#comment').value=''; loadReviews();
      }catch(_){
        alertBox('#alert','danger','Network error');
      }finally{
        setBusy(false);
      }
    }

    // Events
    $('#btnApply').addEventListener('click', ()=>{
      FILTER_CAT = ($('#f_cat').value||'').toLowerCase().trim();
      renderItemSelect();
    });
    $('#btnReset').addEventListener('click', ()=>{
      FILTER_CAT=''; $('#f_cat').value=''; renderItemSelect();
    });
    $('#btnReload').addEventListener('click', loadReviews);
    $('#item').addEventListener('change', (e)=>{ ITEM_ID = parseInt(e.target.value||'0',10); updateMeta(); loadReviews(); });
    $('#btnSubmit').addEventListener('click', submitReview);

    // Init
    window.addEventListener('load', ()=>{
      applyUser();
      loadMenu();
    });
  </script>
</body>
</html>
