<?php
// frontend/pages/order.php — Browse menu, manage cart, place order, pay prompt
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Order Online | The Cafe Rio – Gulshan</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .muted{ color:#6c757d }
    .menu-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px }
    .menu-card{ border:1px solid #eef1f4; border-radius:14px; background:#fff; overflow:hidden; display:flex; flex-direction:column; }
    .menu-img{ height:140px; background:#f5f7fa; }
    .menu-img img{ width:100%; height:100%; object-fit:cover; display:block; }
    .menu-body{ padding:12px; display:flex; flex-direction:column; gap:6px; }
    .menu-title{ font-weight:700; line-height:1.2; }
    .chip{ display:inline-block; padding:.12rem .5rem; border-radius:999px; background:#eef1f4; font-size:.78rem }
    .qty{ display:flex; align-items:center; gap:6px }
    .qty .btn{ width:28px; height:28px; padding:0; display:inline-flex; align-items:center; justify-content:center }
    .cart-card{ position:sticky; top:16px; }
    .cart-item{ display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 0; border-bottom:1px dashed #e9ecef }
    .cart-title{ font-weight:600 }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Order Online</h1>
        <a href="/restaurant-app/frontend/pages/my-orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt"></i> My Orders</a>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card-elev mb-3">
            <div class="card-body p-3">
              <div id="loginNotice" class="alert alert-warning d-none mb-3" role="alert">
                লগইন করলে User ID অটো-ফিল হবে। <a id="loginLink" class="alert-link" href="#">Login</a>
              </div>
              <div id="userWrap" class="row g-2 align-items-end d-none">
                <div class="col-6 col-sm-4">
                  <label class="form-label">User ID</label>
                  <input id="user_id" type="number" min="1" class="form-control" placeholder="e.g. 2">
                </div>
              </div>

              <div class="row g-2 align-items-end">
                <div class="col-sm-4"><label class="form-label">Search</label><input id="q" class="form-control" placeholder="name, desc, category"></div>
                <div class="col-sm-4"><label class="form-label">Category</label><input id="cat" class="form-control" placeholder="e.g. Pizza"></div>
                <div class="col-sm-4">
                  <button id="btnFilter" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-funnel"></i> Apply
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="card-elev">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="fw-bold mb-0">Menu</h5>
                <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i></button>
              </div>
              <div id="menuAlert" class="alert d-none" role="alert"></div>
              <div id="grid" class="menu-grid" aria-live="polite" aria-busy="true"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card-elev cart-card">
            <div class="card-body p-3">
              <h5 class="fw-bold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bag me-1"></i> Cart</span>
                <button id="btnClear" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Clear</button>
              </h5>

              <div id="cartEmpty" class="muted my-2">Cart is empty</div>
              <div id="cartList"></div>

              <hr>
              <div class="d-flex align-items-center justify-content-between"><div class="muted">Subtotal</div><div id="sub" class="mono">৳0</div></div>
              <div class="d-flex align-items-center justify-content-between"><div class="muted">Delivery</div><div id="delv" class="mono">৳0</div></div>
              <div class="d-flex align-items-center justify-content-between fw-bold"><div>Total</div><div id="tot" class="mono">৳0</div></div>

              <div class="mt-3">
                <label class="form-label">Payment method</label>
                <select id="pay" class="form-select">
                  <option value="cod" selected>Cash on delivery</option>
                  <option value="bkash">bKash</option>
                  <option value="nagad">Nagad</option>
                  <option value="sslcommerz">SSLCOMMERZ</option>
                </select>
              </div>
              <div class="mt-2"><label class="form-label">Notes</label><input id="notes" class="form-control" placeholder="(optional)"></div>

              <div class="d-grid mt-3">
                <button id="btnPlace" class="btn btn-danger"><span id="spPlace" class="spinner-border spinner-border-sm me-2 d-none"></span><i class="bi bi-check2-circle"></i> Place order</button>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- row -->
    </div>
  </section>

  <!-- Prompt Pay Modal -->
  <div class="modal fade" id="payPrompt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0">
          <h5 class="modal-title"><i class="bi bi-credit-card-2-front me-2"></i>Pay now?</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="pp_total" class="fw-bold mono mb-2">Total: ৳0</div>
          <p class="mb-0">আপনি কি এখনই পেমেন্ট করতে চান?</p>
        </div>
        <div class="modal-footer border-0">
          <button id="pp_no" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
          <button id="pp_yes" class="btn btn-danger">Yes, pay now</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Result Modals -->
  <div class="modal fade" id="payOk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
      <div class="modal-header border-0"><h5 class="modal-title text-success"><i class="bi bi-check2-circle me-2"></i>Payment successful</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><div id="payOkMeta" class="small text-muted"></div></div>
      <div class="modal-footer border-0"><a href="/restaurant-app/frontend/pages/my-orders.php" class="btn btn-danger">My Orders</a></div>
    </div></div>
  </div>
  <div class="modal fade" id="payLater" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
      <div class="modal-header border-0"><h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Pay later</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><p class="mb-0">পরবর্তীতে My Orders পেজে গিয়ে “Pay now” থেকে পেমেন্ট করতে পারবেন।</p></div>
      <div class="modal-footer border-0"><a href="/restaurant-app/frontend/pages/my-orders.php" class="btn btn-outline-secondary">My Orders</a></div>
    </div></div>
  </div>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script src="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);
    const $$ = s => Array.prototype.slice.call(document.querySelectorAll(s));
    const fmt = v => '৳' + Number(v||0).toFixed(0);

    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }
    function applyUser(){
      const u = curUser(); const wrap = $('#userWrap'); const input = $('#user_id'); const ln = $('#loginNotice'); const lk = $('#loginLink');
      const redirect = encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/order.php');
      if (lk) lk.href = (window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + redirect;
      if (u && u.user_id){ if (input) input.value = u.user_id; wrap.classList.add('d-none'); ln.classList.add('d-none'); }
      else { wrap.classList.remove('d-none'); ln.classList.remove('d-none'); }
    }

    let MENU = []; let CART = {};
    function loadCart(){ try{ CART = JSON.parse(localStorage.getItem('cr_cart')||'{}') || {}; }catch{ CART = {}; } }
    function saveCart(){ localStorage.setItem('cr_cart', JSON.stringify(CART)); }
    function cartQty(id){ return (CART[id] && CART[id].qty) ? CART[id].qty : 0; }
    function addToCart(it, inc=1){
      const id=it.item_id;
      if (!CART[id]) CART[id]={ item_id:id, name:it.name, unit:Number(it.price||0), qty:0 };
      CART[id].qty = Math.max(0,(CART[id].qty||0)+inc);
      if (CART[id].qty===0) delete CART[id];
      saveCart(); renderCart();
    }
    function setQty(id, qty){ if (qty<=0) delete CART[id]; else if (CART[id]) CART[id].qty=qty; saveCart(); renderCart(); }

    function cardHtml(it){
      const img = it.image ? `${APP}/backend/public/uploads/menu/${it.image}` : '';
      const q = cartQty(it.item_id);
      return `
        <div class="menu-card">
          <div class="menu-img">${img ? `<img src="${img}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='${APP}/frontend/assets/images/_placeholder.png';">` : ''}</div>
          <div class="menu-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="menu-title">${it.name||''}</div>
              <div class="fw-bold mono">${fmt(it.price)}</div>
            </div>
            <div class="muted small">${it.description||''}</div>
            ${it.category ? `<span class="chip">${it.category}</span>` : ''}
            <div class="d-flex align-items-center justify-content-between mt-1">
              <button class="btn btn-sm btn-outline-secondary" data-act="add" data-id="${it.item_id}"><i class="bi bi-plus-circle"></i> Add</button>
              <div class="qty">
                <button class="btn btn-outline-secondary btn-sm" data-act="dec" data-id="${it.item_id}">−</button>
                <span class="mono" id="q_${it.item_id}">${q}</span>
                <button class="btn btn-outline-secondary btn-sm" data-act="inc" data-id="${it.item_id}">+</button>
              </div>
            </div>
          </div>
        </div>`;
    }

    function renderMenu(list){ const grid=$('#grid'); grid.setAttribute('aria-busy','false'); grid.innerHTML = list.map(cardHtml).join('') || '<div class="muted">No items</div>'; }

    async function loadMenu(){
      const grid=$('#grid'), alert=$('#menuAlert'); alert.classList.add('d-none'); grid.setAttribute('aria-busy','true');
      try{
        const url = new URL(`${APP}/backend/public/index.php`, window.location.origin);
        url.searchParams.set('r','menu'); url.searchParams.set('a','list'); url.searchParams.set('status','available'); url.searchParams.set('limit','300');
        const r = await fetch(url.toString()); const d = await r.json().catch(()=> ({}));
        if (!r.ok) throw new Error(d?.error || 'Load failed');
        MENU = d.items || []; applyFilter();
      }catch(err){
        alert.className='alert alert-danger'; alert.textContent = err.message || 'Unable to load menu'; alert.classList.remove('d-none');
      }finally{ grid.setAttribute('aria-busy','false'); }
    }
    function applyFilter(){
      const q=($('#q').value||'').toLowerCase().trim(); const cat=($('#cat').value||'').toLowerCase().trim();
      const list = MENU.filter(it=>{
        const m=(it.name||'')+' '+(it.description||'')+' '+(it.category||'');
        const okQ = q ? m.toLowerCase().includes(q) : true;
        const okC = cat ? String(it.category||'').toLowerCase().includes(cat) : true;
        return okQ && okC;
      });
      renderMenu(list);
    }

    document.addEventListener('click', e=>{
      const btn=e.target.closest('button[data-act]'); if (!btn) return;
      const act=btn.getAttribute('data-act'); const id=parseInt(btn.getAttribute('data-id')||'0',10);
      if (!['add','inc','dec'].includes(act)) return;
      const it=MENU.find(x=>x.item_id===id); if (!it) return;
      if (act==='add'||act==='inc') addToCart(it,1);
      if (act==='dec') addToCart(it,-1);
      const sp=document.getElementById('q_'+id); if (sp) sp.textContent=String(cartQty(id));
    });

    function renderCart(){
      const listEl=$('#cartList'); const emptyEl=$('#cartEmpty'); const ids=Object.keys(CART);
      if (!ids.length){ listEl.innerHTML=''; emptyEl.classList.remove('d-none'); } else { emptyEl.classList.add('d-none'); }
      let sub=0;
      listEl.innerHTML = ids.map(id=>{
        const it=CART[id]; sub += it.unit * it.qty;
        return `
          <div class="cart-item" data-id="${it.item_id}">
            <div class="flex-grow-1"><div class="cart-title">${it.name}</div><div class="small mono">${fmt(it.unit)} × ${it.qty}</div></div>
            <div class="d-flex align-items-center gap-1">
              <button class="btn btn-sm btn-outline-secondary" data-cart="dec">−</button>
              <span class="mono">${it.qty}</span>
              <button class="btn btn-sm btn-outline-secondary" data-cart="inc">+</button>
              <button class="btn btn-sm btn-outline-danger" data-cart="del"><i class="bi bi-x"></i></button>
            </div>
          </div>`;
      }).join('');
      const delv=0; $('#sub').textContent=fmt(sub); $('#delv').textContent=fmt(delv); $('#tot').textContent=fmt(sub+delv);
    }
    document.addEventListener('click', e=>{
      const btn=e.target.closest('button[data-cart]'); if (!btn) return;
      const act=btn.getAttribute('data-cart'); const row=btn.closest('.cart-item'); const id=parseInt(row?.getAttribute('data-id')||'0',10);
      if (!id) return;
      if (act==='inc'){ if (CART[id]) setQty(id,(CART[id].qty||0)+1); }
      if (act==='dec'){ if (CART[id]) setQty(id,Math.max(0,(CART[id].qty||0)-1)); }
      if (act==='del'){ if (CART[id]) setQty(id,0); }
    });
    $('#btnClear').addEventListener('click', ()=>{ if (!Object.keys(CART).length) return; if (!confirm('Clear cart?')) return; CART={}; saveCart(); renderCart(); MENU.forEach(it=>{ const sp=document.getElementById('q_'+it.item_id); if (sp) sp.textContent='0'; }); });

    // Place order -> pay prompt
    let LAST_ORDER = { id: null, total: 0 };
    function setBusy(on){ const btn=$('#btnPlace'), sp=$('#spPlace'); btn.disabled=!!on; sp.classList.toggle('d-none', !on); }
    function alertBox(type,msg){ const b=$('#alert'); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); setTimeout(()=>b.classList.add('d-none'), 3500); }

    // Bootstrap modal instances
    const payPromptEl = document.getElementById('payPrompt');
    const payPrompt = bootstrap.Modal.getOrCreateInstance(payPromptEl);
    const payOk = bootstrap.Modal.getOrCreateInstance(document.getElementById('payOk'));
    const payLater = bootstrap.Modal.getOrCreateInstance(document.getElementById('payLater'));

    $('#btnPlace').addEventListener('click', async ()=>{
      const u=curUser(); const input=$('#user_id'); const user_id=(u&&u.user_id)?u.user_id:parseInt(input.value||'0',10);
      if (!(user_id>0)) return alertBox('warning','Valid User ID required');
      const items = Object.values(CART).map(x=>({ item_id:x.item_id, qty:x.qty })).filter(x=> x.qty>0);
      if (!items.length) return alertBox('warning','Cart is empty');
      const payment_method = $('#pay').value; const notes=$('#notes').value.trim();
      setBusy(true);
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=orders&a=create`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ user_id, payment_method, notes, items }) });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Order failed');
        const oid = data?.order?.order_id || 0; const total = Number(data?.order?.total_amount||0);
        LAST_ORDER = { id: oid, total };
        CART={}; saveCart(); renderCart(); MENU.forEach(it=>{ const sp=document.getElementById('q_'+it.item_id); if (sp) sp.textContent='0'; });
        $('#pp_total').textContent = 'Total: ' + fmt(total);
        payPrompt.show();
      }catch(err){ alertBox('danger', err?.message || 'Network error'); }
      finally{ setBusy(false); }
    });

    // Pay now flow
    document.getElementById('pp_yes').addEventListener('click', async ()=>{
      const u=curUser(); const actor=(u&&u.user_id)?u.user_id:parseInt($('#user_id').value||'0',10);
      if (!(actor>0) || !LAST_ORDER.id) return;
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=orders&a=pay`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ actor_user_id: actor, order_id: LAST_ORDER.id }) });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Payment failed');
        payPrompt.hide();
        document.getElementById('payOkMeta').textContent = `Order #${data.order_id} • Paid ${fmt(data.total_amount||LAST_ORDER.total)}`;
        payOk.show();
      }catch(err){
        alertBox('danger', err?.message || 'Payment error');
      }
    });
    document.getElementById('pp_no').addEventListener('click', ()=>{
      payPrompt.hide();
      payLater.show();
    });

    // Filters + init
    $('#btnFilter').addEventListener('click', applyFilter);
    $('#btnReload').addEventListener('click', loadMenu);
    window.addEventListener('load', ()=>{
      const lk=$('#loginLink'); if (lk){ const r=encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/order.php'); lk.href=(window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + r; }
      applyUser(); loadCart(); renderCart(); loadMenu();
    });
  </script>
</body>
</html>
