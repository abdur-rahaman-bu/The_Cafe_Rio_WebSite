<?php
// frontend/pages/admin-tables.php — Admin Tables CRUD (requires admin login)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • Tables | The Cafe Rio – Gulshan</title>

  <!-- Local Bootstrap -->
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <!-- Icons via CDN to avoid 404 during setup -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
    .muted{ color:#6c757d }
    .badge-pill{ border-radius:999px; padding:.35rem .6rem }
    .w-120{ width:120px }
    .w-90{ width:90px }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Tables</h1>
        <button id="btnReload" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-arrow-clockwise"></i> Reload</button>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- Create form -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <h5 class="fw-bold mb-3">Create table</h5>
          <div class="row g-2">
            <div class="col-sm-3">
              <label class="form-label">Name</label>
              <input id="c_name" class="form-control" placeholder="e.g. T1">
            </div>
            <div class="col-sm-2">
              <label class="form-label">Capacity</label>
              <input id="c_cap" type="number" min="1" class="form-control" placeholder="2">
            </div>
            <div class="col-sm-3">
              <label class="form-label">Zone</label>
              <input id="c_zone" class="form-control" placeholder="couple/family/window">
            </div>
            <div class="col-sm-2">
              <label class="form-label">Status</label>
              <select id="c_status" class="form-select">
                <option value="active" selected>active</option>
                <option value="inactive">inactive</option>
              </select>
            </div>
            <div class="col-sm-2 d-flex align-items-end">
              <button id="btnCreate" class="btn btn-danger w-100" type="button">
                <span id="spCreate" class="spinner-border spinner-border-sm me-2 d-none"></span>
                <i class="bi bi-plus-circle"></i> Create
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- List -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All tables</h5>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">ID</th>
                  <th>Name</th>
                  <th class="w-90">Capacity</th>
                  <th>Zone</th>
                  <th class="w-120">Status</th>
                  <th>Created</th>
                  <th style="width:220px">Actions</th>
                </tr>
              </thead>
              <tbody id="grid">
                <tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Delete confirm modal -->
      <div class="modal fade" id="delModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
          <div class="modal-header border-0"><h5 class="modal-title text-danger"><i class="bi bi-trash3 me-2"></i>Delete table</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body"><div id="delMeta" class="small muted">—</div></div>
          <div class="modal-footer border-0">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="btnDelConfirm" class="btn btn-danger">Delete</button>
          </div>
        </div></div>
      </div>

    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);
    const $$ = s => Array.prototype.slice.call(document.querySelectorAll(s));
    function alertBox(sel, type, msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); if (b) b.classList.add('d-none'); }
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }

    let ADMIN=null, ROWS=[], WILL_DELETE=null;

    function ensureAdmin(){
      const u=curUser();
      if (!u || String(u.role||'').toLowerCase()!=='admin'){
        alertBox('#alert','danger','Admin required');
        return null;
      }
      ADMIN = u; return u;
    }

    async function loadList(){
      hideAlert('#listAlert');
      const grid=$('#grid'); grid.innerHTML='<tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>';
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=list&status=active`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){ alertBox('#listAlert','danger', data?.error || 'Failed to load tables'); return; }
        // also allow viewing inactive by a second call (optional)
        const res2=await fetch(`${APP}/backend/public/index.php?r=tables&a=list&status=inactive`);
        const data2=await res2.json().catch(()=> ({}));
        const items=[...(data.items||[]), ...((data2.items||[]))];
        ROWS = items.sort((a,b)=> (a.table_id - b.table_id));
        renderGrid(ROWS);
      }catch(_){ alertBox('#listAlert','danger','Network error'); }
    }

    function renderGrid(rows){
      const badge = s => (s==='active' ? 'text-bg-success' : 'text-bg-secondary');
      const grid=$('#grid');
      if (!rows.length){ grid.innerHTML='<tr><td colspan="7" class="text-center text-muted">No tables</td></tr>'; return; }
      grid.innerHTML = rows.map(r=>`
        <tr data-id="${r.table_id}">
          <td class="mono">${r.table_id}</td>
          <td><input class="form-control form-control-sm" data-edit="name" value="${r.name||''}"></td>
          <td><input type="number" min="1" class="form-control form-control-sm" data-edit="capacity" value="${r.capacity||1}"></td>
          <td><input class="form-control form-control-sm" data-edit="zone" value="${r.zone||''}"></td>
          <td>
            <select class="form-select form-select-sm" data-edit="status">
              <option value="active"${r.status==='active'?' selected':''}>active</option>
              <option value="inactive"${r.status==='inactive'?' selected':''}>inactive</option>
            </select>
          </td>
          <td><span class="small">${r.created_at||''}</span></td>
          <td>
            <div class="d-flex align-items-center gap-1">
              <button class="btn btn-sm btn-outline-primary" data-act="save"><i class="bi bi-save"></i> Save</button>
              <button class="btn btn-sm btn-outline-danger" data-act="del"><i class="bi bi-trash3"></i> Delete</button>
            </div>
          </td>
        </tr>
      `).join('');
    }

    function readRow(tr){
      return {
        table_id: parseInt(tr.getAttribute('data-id')||'0',10),
        name: tr.querySelector('[data-edit="name"]').value.trim(),
        capacity: parseInt(tr.querySelector('[data-edit="capacity"]').value||'0',10),
        zone: tr.querySelector('[data-edit="zone"]').value.trim(),
        status: tr.querySelector('[data-edit="status"]').value
      };
    }

    // Create
    function setCreateBusy(on){ $('#btnCreate').disabled=!!on; $('#spCreate').classList.toggle('d-none', !on); }
    document.getElementById('btnCreate').addEventListener('click', async ()=>{
      if (!ensureAdmin()) return;
      hideAlert('#alert');
      const name=$('#c_name').value.trim(), cap=parseInt($('#c_cap').value||'0',10), zone=$('#c_zone').value.trim(), status=$('#c_status').value;
      if (!name || !(cap>0)) return alertBox('#alert','warning','Valid name & capacity required');
      setCreateBusy(true);
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=create`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: ADMIN.user_id, name, capacity: cap, zone, status })
        });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Create failed');
        // clear form and reload
        $('#c_name').value=''; $('#c_cap').value=''; $('#c_zone').value=''; $('#c_status').value='active';
        loadList();
      }catch(err){ alertBox('#alert','danger', err?.message || 'Network error'); }
      finally{ setCreateBusy(false); }
    });

    // Row actions
    document.addEventListener('click', async (e)=>{
      const btnSave=e.target.closest('button[data-act="save"]');
      const btnDel=e.target.closest('button[data-act="del"]');
      if (!btnSave && !btnDel) return;
      if (!ensureAdmin()) return;

      const tr=e.target.closest('tr[data-id]'); if (!tr) return;
      const id=parseInt(tr.getAttribute('data-id')||'0',10);

      if (btnSave){
        hideAlert('#listAlert');
        const payload=readRow(tr);
        try{
          const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=update`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ actor_user_id: ADMIN.user_id, table_id: id, name: payload.name, capacity: payload.capacity, zone: payload.zone, status: payload.status })
          });
          const data=await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(data?.error || 'Update failed');
          loadList();
        }catch(err){ alertBox('#listAlert','danger', err?.message || 'Network error'); }
        return;
      }

      if (btnDel){
        WILL_DELETE = id;
        const r = ROWS.find(x=> x.table_id===id);
        document.getElementById('delMeta').textContent = r ? (`Delete "${r.name}" (#${r.table_id})?`) : (`Delete table #${id}?`);
        bootstrap.Modal.getOrCreateInstance('#delModal').show();
      }
    });

    // Confirm delete
    document.getElementById('btnDelConfirm').addEventListener('click', async ()=>{
      if (!ensureAdmin()) return;
      const id=WILL_DELETE; if (!id) return;
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=tables&a=delete`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: ADMIN.user_id, table_id: id })
        });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Delete failed');
        bootstrap.Modal.getInstance(document.getElementById('delModal'))?.hide();
        loadList();
      }catch(err){ alertBox('#listAlert','danger', err?.message || 'Network error'); }
    });

    // reload
    document.getElementById('btnReload').addEventListener('click', loadList);

    // init
    window.addEventListener('load', ()=>{
      if (!ensureAdmin()) return;
      loadList();
    });
  </script>
</body>
</html>
