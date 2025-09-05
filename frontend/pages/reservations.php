<?php
// frontend/pages/reservations.php — Auto-load tables + availability; success shows "Reservation confirmed"
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reserve a Table | The Cafe Rio – Gulshan</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .muted{ color:#6c757d }
    .tbl-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:12px }
    .tbl-card{ border:1px solid #e9ecef; border-radius:12px; padding:10px; background:#fff; display:flex; flex-direction:column; gap:6px; }
    .tbl-card .btn{ padding:.25rem .5rem; font-size:.8rem }
    .tbl-badge{ display:inline-block; padding:.16rem .4rem; border-radius:999px; font-size:.75rem; background:#f1f3f5 }
    .ok{ background:rgba(25,135,84,.06); border:1px solid rgba(25,135,84,.22) }
    .bad{ background:rgba(220,53,69,.06); border:1px solid rgba(220,53,69,.22) }
    .selected{ outline:2px solid #0d6efd; }
  </style>
</head>
<body>

  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Reserve Your Table</h1>
        <a href="/restaurant-app/frontend/pages/my-reservations.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-journal-text"></i> My Reservations
        </a>
      </div>

      <div id="alertBox" class="alert d-none" role="alert"></div>

      <div class="card-elev mb-4">
        <div class="card-body p-3 p-md-4">
          <div id="loginNotice" class="alert alert-warning d-none" role="alert">
            লগইন করলে User ID অটো-ফিল হবে। <a id="loginLink" class="alert-link" href="#">Login</a>
          </div>
          <div class="row g-3">
            <div class="col-md-3" id="userIdWrap">
              <label class="form-label">User ID <span class="text-danger">*</span></label>
              <input id="user_id" type="number" min="1" class="form-control" placeholder="e.g. 2">
            </div>
            <div class="col-md-3"><label class="form-label">Date</label><input id="reservation_date" type="date" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Time</label><input id="reservation_time" type="time" class="form-control"></div>
            <div class="col-md-3">
              <label class="form-label">Duration</label>
              <select id="duration" class="form-select">
                <option value="60">60 min</option><option value="90" selected>90 min</option><option value="120">120 min</option><option value="150">150 min</option><option value="180">180 min</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label">People</label><input id="people_count" type="number" min="1" value="2" class="form-control"></div>
            <div class="col-md-3">
              <label class="form-label">Table Type</label>
              <select id="table_type" class="form-select">
                <option value="family" selected>Family</option><option value="couple">Couple</option><option value="window">Window</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Special Request</label><input id="special_request" class="form-control" placeholder="(optional)"></div>
            <div class="col-12 d-flex align-items-end gap-2">
              <button id="btnCheck" class="btn btn-outline-secondary" type="button"><i class="bi bi-search me-1"></i> Check Availability</button>
              <span id="slotMeta" class="small text-muted"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All Tables</h5>
            <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i></button>
          </div>
          <div id="gridAlert" class="alert d-none" role="alert"></div>
          <div id="tablesGrid" class="tbl-grid"></div>
          <input type="hidden" id="table_id">
          <div class="d-flex align-items-center gap-3 mt-3">
            <button id="btnReserve" class="btn btn-danger" type="button" disabled>
              <span id="btnSpin" class="spinner-border spinner-border-sm me-2 d-none"></span>
              Reserve
            </button>
            <button id="btnReset" class="btn btn-outline-secondary" type="button">Reset</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Success Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0">
          <h5 class="modal-title text-success"><i class="bi bi-check2-circle me-2"></i>Reservation confirmed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="okMeta" class="small text-muted">—</div>
        </div>
        <div class="modal-footer border-0">
          <a class="btn btn-danger" href="/restaurant-app/frontend/pages/my-reservations.php">My Reservations</a>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);
    const $$ = s => Array.prototype.slice.call(document.querySelectorAll(s));
    const fmt = n => String(n);

    function showAlert(sel, type, msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); b && b.classList.add('d-none'); }
    function pad(n){ return String(n).padStart(2,'0'); }
    function getUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }

    function applyUser(){
      const u = getUser(), wrap=$('#userIdWrap'), input=$('#user_id'), ln=$('#loginNotice'), lk=$('#loginLink');
      const redirect = encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/reservations.php');
      if (lk) lk.href = (window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + redirect;
      if (u && u.user_id){ input.value=u.user_id; wrap.classList.add('d-none'); ln.classList.add('d-none'); } else { wrap.classList.remove('d-none'); ln.classList.remove('d-none'); }
    }

    function roundUpToNext30(date){ const d=new Date(date.getTime()); const m=d.getMinutes(); const add=(m%30===0)?30:(30-(m%30)); d.setMinutes(m+add,0,0); return d; }
    function setDefaults(){
      const now=new Date(); const today=now.getFullYear()+'-'+pad(now.getMonth()+1)+'-'+pad(now.getDate());
      const next=roundUpToNext30(now);
      $('#reservation_date').value=today; $('#reservation_time').value=pad(next.getHours())+':'+pad(next.getMinutes());
    }

    let TABLES=[]; let SELECTED=null;

    function renderAllTables(){
      const grid=$('#tablesGrid'); grid.innerHTML='';
      if (!TABLES.length){ grid.innerHTML='<div class="muted">No tables found.</div>'; return; }
      TABLES.forEach(t=>{
        const card=document.createElement('div'); card.className='tbl-card';
        card.innerHTML = `
          <div class="d-flex justify-content-between">
            <div class="fw-semibold">${t.name||''}</div>
            <span class="tbl-badge">Cap: ${t.capacity||''}</span>
          </div>
          <div class="small muted">${t.zone||''}</div>
          <div class="d-flex gap-2 mt-1">
            <button class="btn btn-outline-primary btn-sm" data-act="view">View</button>
            <button class="btn btn-outline-success btn-sm" data-act="use">Use</button>
          </div>`;
        card.addEventListener('click', e=>{
          const btn=e.target.closest('button[data-act]'); if (!btn) return;
          const act=btn.getAttribute('data-act');
          if (act==='use'){ $$('.tbl-card').forEach(x=>x.classList.remove('selected')); card.classList.add('selected'); $('#table_id').value=t.table_id; $('#btnReserve').disabled=false; SELECTED=t.table_id; }
          if (act==='view') openDetails(t);
        });
        grid.appendChild(card);
      });
    }

    function paintAvailability(availIds, occIds){
      $$('.tbl-card').forEach((card,i)=>{
        card.classList.remove('ok','bad');
        const t=TABLES[i]; if (!t) return;
        if (availIds.has(t.table_id)) card.classList.add('ok'); else if (occIds.has(t.table_id)) card.classList.add('bad');
      });
    }

    function openDetails(t){
      const s=getSlot();
      const msg = `${t.name||'—'} • Cap ${t.capacity||'—'} • ${t.zone||'—'} • ${s.date||''} ${s.time||''} • ${s.duration||0}m`;
      alert(msg);
    }

    async function fetchTables(){
      hideAlert('#gridAlert');
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=list`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Failed to load tables');
        TABLES=(data.items||[]).filter(t=>t.status==='active'); renderAllTables();
      }catch(err){ showAlert('#gridAlert','danger', err.message || 'Unable to load tables'); }
    }

    function getSlot(){
      return {
        user_id: parseInt($('#user_id').value||'0',10),
        date: $('#reservation_date').value,
        time: $('#reservation_time').value,
        duration: parseInt($('#duration').value||'90',10),
        people: parseInt($('#people_count').value||'0',10),
        type: $('#table_type').value
      };
    }

    function slotValid(s){ return s.user_id>0 && s.date && s.time && s.duration>=30 && s.people>0; }

    async function checkAvailability(){
      hideAlert('#gridAlert'); hideAlert('#alertBox');
      $('#table_id').value=''; $('#btnReserve').disabled=true; SELECTED=null;
      const s=getSlot(); if (!slotValid(s)){ showAlert('#alertBox','warning','User/Date/Time/Duration/People পূরণ করুন।'); return; }
      $('#slotMeta').textContent = `Slot: ${s.date} ${s.time} • ${s.duration} min • People: ${s.people}`;
      try{
        const q=new URLSearchParams({ date:s.date, time:s.time, duration:String(s.duration), people:String(s.people) });
        const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=availability&${q.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Availability failed');
        const avail=new Set((data.available||[]).map(x=>x.table_id||x)); const occ=new Set((data.occupied||[]).map(x=>x.table_id||x));
        paintAvailability(avail, occ);
      }catch(err){ showAlert('#gridAlert','warning', err.message || 'Availability not available; showing base list'); }
    }

    async function submitReservation(){
      hideAlert('#alertBox');
      const s=getSlot(); const table_id=parseInt($('#table_id').value||'0',10); const special=$('#special_request').value.trim();
      if (!slotValid(s)) return showAlert('#alertBox','warning','User/Date/Time/Duration/People পূরণ করুন।');
      if (!table_id) return showAlert('#alertBox','warning','একটি Available টেবিল সিলেক্ট করুন।');

      $('#btnReserve').disabled=true; $('#btnSpin').classList.remove('d-none');
      try{
        const payload={ user_id: s.user_id, reservation_date: s.date, reservation_time: s.time, duration_minutes: s.duration, people_count: s.people, table_type: s.type, table_id, special_request: special };
        const res=await fetch(`${APP}/backend/public/index.php?r=reservations&a=create`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Reservation failed');
        const rid = data?.reservation?.reservation_id || '—';
        const st  = data?.reservation?.status || 'confirmed';
        const meta = `Reservation #${rid} • Status ${st} • ${s.date} ${s.time} • ${s.duration}m • People ${s.people} • Table #${table_id}`;
        document.getElementById('okMeta').textContent = meta;
        if (window.bootstrap){ new bootstrap.Modal('#successModal').show(); } else { alert('Reservation confirmed: ' + meta); }
        // reset select + refresh availability
        $('#table_id').value=''; SELECTED=null; $('#btnReserve').disabled=true; checkAvailability();
      }catch(err){ showAlert('#alertBox','danger', err.message || 'Network/Server error'); }
      finally{ $('#btnSpin').classList.add('d-none'); }
    }

    // Events
    document.getElementById('btnCheck').addEventListener('click', checkAvailability);
    document.getElementById('btnReload').addEventListener('click', ()=> fetchTables().then(checkAvailability));
    document.getElementById('btnReserve').addEventListener('click', submitReservation);
    document.getElementById('btnReset').addEventListener('click', ()=>{ $('#special_request').value=''; setDefaults(); fetchTables().then(checkAvailability); });

    // Init
    window.addEventListener('load', ()=>{
      const lk=$('#loginLink'); if (lk){ const r=encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/reservations.php'); lk.href=(window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + r; }
      applyUser(); setDefaults(); fetchTables().then(checkAvailability);
    });
  </script>
</body>
</html>
