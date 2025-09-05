<?php
// frontend/pages/admin-menu.php — Admin Menu CRUD + image upload + EDIT MODAL (requires admin login)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • Menu | The Cafe Rio – Gulshan</title>

  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .card-elev{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px }
    .table td,.table th{ vertical-align: middle; }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; }
    .muted{ color:#6c757d }
    .img-thumb{ width:66px; height:44px; object-fit:cover; border-radius:8px; background:#f1f3f5 }
    .w-120{ width:120px } .w-90{ width:90px } .w-160{ width:160px }
    .img-prev{ width:100%; max-width:240px; height:160px; object-fit:cover; border-radius:10px; background:#f1f3f5; border:1px solid #eee }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fw-bold">Menu</h1>
        <button id="btnReload" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Reload</button>
      </div>

      <div id="alert" class="alert d-none" role="alert"></div>

      <!-- Filters -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <div class="row g-2">
            <div class="col-sm-3"><label class="form-label">Search</label><input id="f_q" class="form-control" placeholder="name/desc/category"></div>
            <div class="col-sm-3"><label class="form-label">Category</label><input id="f_cat" class="form-control" placeholder="e.g. Pizza"></div>
            <div class="col-sm-3">
              <label class="form-label">Status</label>
              <select id="f_status" class="form-select">
                <option value="">(any)</option>
                <option value="available">available</option>
                <option value="unavailable">unavailable</option>
              </select>
            </div>
            <div class="col-sm-3 d-flex align-items-end gap-2">
              <button id="btnApply" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
              <button id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create -->
      <div class="card-elev mb-3">
        <div class="card-body p-3 p-md-4">
          <h5 class="fw-bold mb-3">Create item</h5>
          <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label">Name</label><input id="c_name" class="form-control" placeholder="e.g. Margherita"></div>
            <div class="col-md-2"><label class="form-label">Price</label><input id="c_price" type="number" min="0" step="1" class="form-control" placeholder="৳"></div>
            <div class="col-md-3"><label class="form-label">Category</label><input id="c_cat" class="form-control" placeholder="Pizza"></div>
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <select id="c_status" class="form-select">
                <option value="available" selected>available</option>
                <option value="unavailable">unavailable</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button id="btnCreate" class="btn btn-danger w-100" type="button">
                <span id="spCreate" class="spinner-border spinner-border-sm me-2 d-none"></span>
                <i class="bi bi-plus-circle"></i> Create
              </button>
            </div>
            <div class="col-12"><label class="form-label">Description</label><input id="c_desc" class="form-control" placeholder="(optional)"></div>
            <div class="col-md-4">
              <label class="form-label">Image</label>
              <input id="c_img" type="file" accept="image/png,image/jpeg,image/webp" class="form-control">
              <div class="form-text">২–৫MB jpg/png/webp; ফাইল বাছাই করে Create চাপুন—সাথে সাথেই আপলোড হবে।</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid -->
      <div class="card-elev">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="fw-bold mb-0">All items</h5>
          </div>
          <div id="listAlert" class="alert d-none" role="alert"></div>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th class="mono">ID</th>
                  <th>Image</th>
                  <th class="w-160">Name</th>
                  <th class="w-90">Price</th>
                  <th class="w-160">Category</th>
                  <th>Description</th>
                  <th class="w-120">Status</th>
                  <th>Created</th>
                  <th style="width:320px">Actions</th>
                </tr>
              </thead>
              <tbody id="grid">
                <tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Delete confirm -->
      <div class="modal fade" id="delModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
          <div class="modal-header border-0"><h5 class="modal-title text-danger"><i class="bi bi-trash3 me-2"></i>Delete item</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body"><div id="delMeta" class="small muted">—</div></div>
          <div class="modal-footer border-0">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="btnDelConfirm" class="btn btn-danger">Delete</button>
          </div>
        </div></div>
      </div>

      <!-- EDIT modal -->
      <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit item</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="editAlert" class="alert d-none" role="alert"></div>
            <div class="row g-3">
              <div class="col-md-8">
                <div class="row g-2">
                  <div class="col-md-8"><label class="form-label">Name</label><input id="e_name" class="form-control"></div>
                  <div class="col-md-4"><label class="form-label">Price</label><input id="e_price" type="number" min="0" step="1" class="form-control"></div>
                  <div class="col-md-6"><label class="form-label">Category</label><input id="e_cat" class="form-control"></div>
                  <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select id="e_status" class="form-select">
                      <option value="available">available</option>
                      <option value="unavailable">unavailable</option>
                    </select>
                  </div>
                  <div class="col-12"><label class="form-label">Description</label><input id="e_desc" class="form-control"></div>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label d-flex align-items-center justify-content-between">
                  <span>Image</span>
                  <small class="text-muted">optional</small>
                </label>
                <img id="e_prev" class="img-prev mb-2" src="" alt="preview" onerror="this.src='/restaurant-app/frontend/assets/images/_placeholder.png'">
                <input id="e_file" type="file" accept="image/png,image/jpeg,image/webp" class="form-control">
                <div class="form-text">Max 5MB, jpg/png/webp</div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button id="btnEditSave" class="btn btn-danger">
              <span id="spEdit" class="spinner-border spinner-border-sm me-2 d-none"></span>
              Save changes
            </button>
          </div>
        </div></div>
      </div>

    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script src="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
  <script>
    const APP = (window.APP_BASE || '');
    const $ = s => document.querySelector(s);
    const $$ = s => Array.prototype.slice.call(document.querySelectorAll(s));
    function alertBox(sel,type,msg){ const b=$(sel); b.className='alert alert-'+type; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(sel){ const b=$(sel); if (b) b.classList.add('d-none'); }
    function curUser(){ try{ return JSON.parse(localStorage.getItem('cr_user')||'null'); }catch{ return null; } }

    let ADMIN=null, ROWS=[], WILL_DELETE=null, EDIT_ID=null;

    function ensureAdmin(){
      const u=curUser();
      if (!u || String(u.role||'').toLowerCase()!=='admin'){ alertBox('#alert','danger','Admin required'); return null; }
      ADMIN=u; return u;
    }

    // Filters
    function readFilters(){ return { q: $('#f_q').value||'', category: $('#f_cat').value||'', status: $('#f_status').value||'' }; }
    function resetFilters(){ $('#f_q').value=''; $('#f_cat').value=''; $('#f_status').value=''; }

    async function loadList(){
      hideAlert('#listAlert');
      const grid=$('#grid'); grid.innerHTML='<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
      try{
        const f=readFilters();
        const qs=new URLSearchParams({ r:'menu', a:'list', limit:'500' });
        if (f.q) qs.set('q', f.q);
        if (f.category) qs.set('category', f.category);
        if (f.status) qs.set('status', f.status);
        const res=await fetch(`${APP}/backend/public/index.php?${qs.toString()}`);
        const data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Failed to load menu');
        ROWS=(data.items||[]).sort((a,b)=> a.item_id-b.item_id);
        renderGrid(ROWS);
      }catch(err){ alertBox('#listAlert','danger', err?.message || 'Network error'); }
    }

    function renderGrid(rows){
      const grid=$('#grid');
      if (!rows.length){ grid.innerHTML='<tr><td colspan="9" class="text-center text-muted">No items</td></tr>'; return; }
      grid.innerHTML = rows.map(r=>{
        const imgSrc = r.image
          ? `${APP}/backend/public/uploads/menu/${r.image}`
          : `${APP}/frontend/assets/images/_placeholder.png`;
        return `
          <tr data-id="${r.item_id}">
            <td class="mono">${r.item_id}</td>
            <td>
              <img class="img-thumb" src="${imgSrc}" onerror="this.src='${APP}/frontend/assets/images/_placeholder.png'">
              <div class="small mt-1">
                <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" data-edit="file">
              </div>
            </td>
            <td><input class="form-control form-control-sm" data-edit="name" value="${r.name||''}"></td>
            <td><input type="number" min="0" step="1" class="form-control form-control-sm" data-edit="price" value="${Number(r.price||0)}"></td>
            <td><input class="form-control form-control-sm" data-edit="category" value="${r.category||''}"></td>
            <td><input class="form-control form-control-sm" data-edit="description" value="${r.description||''}"></td>
            <td>
              <select class="form-select form-select-sm" data-edit="status">
                <option value="available"${r.status==='available'?' selected':''}>available</option>
                <option value="unavailable"${r.status==='unavailable'?' selected':''}>unavailable</option>
              </select>
            </td>
            <td><span class="small">${r.created_at||''}</span></td>
            <td>
              <div class="d-flex flex-wrap align-items-center gap-1">
                <button class="btn btn-sm btn-outline-primary" data-act="save"><i class="bi bi-save"></i> Save</button>
                <button class="btn btn-sm btn-outline-secondary" data-act="upload"><i class="bi bi-upload"></i> Upload</button>
                <button class="btn btn-sm btn-outline-dark" data-act="edit"><i class="bi bi-pencil-square"></i> Edit</button>
                <button class="btn btn-sm btn-outline-danger" data-act="del"><i class="bi bi-trash3"></i> Delete</button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    function readRow(tr){
      const fileInput = tr.querySelector('[data-edit="file"]');
      const file = (fileInput && fileInput.files && fileInput.files[0]) ? fileInput.files[0] : null;
      return {
        item_id: parseInt(tr.getAttribute('data-id')||'0',10),
        name: tr.querySelector('[data-edit="name"]').value.trim(),
        price: parseFloat(tr.querySelector('[data-edit="price"]').value||'0'),
        category: tr.querySelector('[data-edit="category"]').value.trim(),
        description: tr.querySelector('[data-edit="description"]').value.trim(),
        status: tr.querySelector('[data-edit="status"]').value,
        file
      };
    }

    // Create flow (with optional image upload)
    function setCreateBusy(on){ $('#btnCreate').disabled=!!on; $('#spCreate').classList.toggle('d-none', !on); }
    document.getElementById('btnCreate').addEventListener('click', async ()=>{
      const u=ensureAdmin(); if (!u) return;
      hideAlert('#alert');

      const name=$('#c_name').value.trim();
      const price=parseFloat($('#c_price').value||'0');
      const category=$('#c_cat').value.trim();
      const status=$('#c_status').value;
      const description=$('#c_desc').value.trim();
      const imgInput = $('#c_img');
      const file = (imgInput && imgInput.files && imgInput.files[0]) ? imgInput.files[0] : null;

      if (!name || !(price>=0)) return alertBox('#alert','warning','Valid name & price required');

      setCreateBusy(true);
      try{
        // Step 1: create JSON
        let res=await fetch(`${APP}/backend/public/index.php?r=menu&a=create`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: u.user_id, name, price, category, description, status })
        });
        let data=await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Create failed');
        const id = data?.item_id;

        // Step 2: optional image upload
        if (file){
          const fd=new FormData();
          fd.append('actor_user_id', String(u.user_id));
          fd.append('item_id', String(id));
          fd.append('image', file, file.name);
          res=await fetch(`${APP}/backend/public/index.php?r=menu&a=upload_image`, { method:'POST', body: fd });
          data=await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(data?.error || 'Image upload failed');
        }

        // reset form
        $('#c_name').value=''; $('#c_price').value=''; $('#c_cat').value=''; $('#c_desc').value=''; $('#c_status').value='available'; if (imgInput) imgInput.value='';
        loadList();
      }catch(err){ alertBox('#alert','danger', err?.message || 'Network error'); }
      finally{ setCreateBusy(false); }
    });

    // EDIT modal helpers
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    function fillEdit(r){
      EDIT_ID = r.item_id;
      $('#e_name').value = r.name||'';
      $('#e_price').value = Number(r.price||0);
      $('#e_cat').value = r.category||'';
      $('#e_desc').value = r.description||'';
      $('#e_status').value = r.status||'available';
      const prev = r.image ? `${APP}/backend/public/uploads/menu/${r.image}` : `${APP}/frontend/assets/images/_placeholder.png`;
      $('#e_prev').src = prev;
      $('#e_file').value = '';
      hideAlert('#editAlert');
    }
    function setEditBusy(on){ $('#btnEditSave').disabled=!!on; $('#spEdit').classList.toggle('d-none', !on); }

    // Row actions
    document.addEventListener('click', async (e)=>{
      const save=e.target.closest('button[data-act="save"]');
      const del =e.target.closest('button[data-act="del"]');
      const up  =e.target.closest('button[data-act="upload"]');
      const ed  =e.target.closest('button[data-act="edit"]');
      if (!save && !del && !up && !ed) return;
      const u=ensureAdmin(); if (!u) return;

      const tr=e.target.closest('tr[data-id]'); if (!tr) return;
      const row=readRow(tr);

      if (ed){
        const r = ROWS.find(x=> x.item_id===row.item_id) || row;
        fillEdit(r);
        editModal.show();
        return;
      }

      if (save){
        hideAlert('#listAlert');
        try{
          const res=await fetch(`${APP}/backend/public/index.php?r=menu&a=update`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ actor_user_id: u.user_id, item_id: row.item_id, name: row.name, price: row.price, category: row.category, description: row.description, status: row.status })
          });
          const data=await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(data?.error || 'Update failed');
          loadList();
        }catch(err){ alertBox('#listAlert','danger', err?.message || 'Network error'); }
        return;
      }

      if (up){
        if (!row.file) { alert('Select an image file first'); return; }
        try{
          const fd=new FormData();
          fd.append('actor_user_id', String(u.user_id));
          fd.append('item_id', String(row.item_id));
          fd.append('image', row.file, row.file.name);
          const res=await fetch(`${APP}/backend/public/index.php?r=menu&a=upload_image`, { method:'POST', body: fd });
          const data=await res.json().catch(()=> ({}));
          if (!res.ok){
            const msg = (data && data.error) ? data.error : `Upload failed (${res.status})`;
            throw new Error(msg);
          }
          loadList();
        }catch(err){
          alertBox('#listAlert','danger', err.message || 'Network error');
        }
        return;
      }

      if (del){
        WILL_DELETE = row.item_id;
        $('#delMeta').textContent = `Delete "${row.name}" (#${row.item_id})?`;
        bootstrap.Modal.getOrCreateInstance('#delModal').show();
      }
    });

    // EDIT modal save (update + optional image)
    document.getElementById('btnEditSave').addEventListener('click', async ()=>{
      const u=ensureAdmin(); if (!u || !EDIT_ID) return;
      hideAlert('#editAlert');

      const payload = {
        actor_user_id: u.user_id,
        item_id: EDIT_ID,
        name: $('#e_name').value.trim(),
        price: parseFloat($('#e_price').value||'0'),
        category: $('#e_cat').value.trim(),
        description: $('#e_desc').value.trim(),
        status: $('#e_status').value
      };

      if (!payload.name || !(payload.price>=0)){
        return alertBox('#editAlert','warning','Valid name & price required');
      }

      setEditBusy(true);
      try{
        // Update fields
        let res = await fetch(`${APP}/backend/public/index.php?r=menu&a=update`, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify(payload)
        });
        let data = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data?.error || 'Update failed');

        // Optional image
        const fileInput = $('#e_file');
        const file = (fileInput && fileInput.files && fileInput.files[0]) ? fileInput.files[0] : null;
        if (file){
          const fd=new FormData();
          fd.append('actor_user_id', String(u.user_id));
          fd.append('item_id', String(EDIT_ID));
          fd.append('image', file, file.name);
          res = await fetch(`${APP}/backend/public/index.php?r=menu&a=upload_image`, { method:'POST', body: fd });
          data = await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(data?.error || 'Image upload failed');
        }

        editModal.hide();
        loadList();
      }catch(err){
        alertBox('#editAlert','danger', err?.message || 'Network error');
      }finally{
        setEditBusy(false);
      }
    });

    // Confirm delete with 409 handling
    const btnDelConfirm = document.getElementById('btnDelConfirm');
    function setDelBusy(on){ btnDelConfirm.disabled=!!on; btnDelConfirm.innerHTML = on ? '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...' : 'Delete'; }
    btnDelConfirm.addEventListener('click', async ()=>{
      const u=ensureAdmin(); if (!u) return;
      const id=WILL_DELETE; if (!id) return;
      setDelBusy(true);
      try{
        const res=await fetch(`${APP}/backend/public/index.php?r=menu&a=delete`, {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: u.user_id, item_id: id })
        });
        const data=await res.json().catch(()=> ({}));
        if (!res.ok){
          const msg = data?.error || `Delete failed (${res.status})`;
          bootstrap.Modal.getInstance(document.getElementById('delModal'))?.hide();
          if (res.status===409){
            alertBox('#listAlert','warning', msg + ' Try marking as "unavailable".');
          } else alert(msg);
          return;
        }
        bootstrap.Modal.getInstance(document.getElementById('delModal'))?.hide();
        loadList();
      }catch(_){ alert('Network error'); }
      finally{ setDelBusy(false); }
    });

    // Filters/events
    document.getElementById('btnApply').addEventListener('click', loadList);
    document.getElementById('btnReset').addEventListener('click', ()=>{ resetFilters(); loadList(); });
    document.getElementById('btnReload').addEventListener('click', loadList);

    // Init
    window.addEventListener('load', ()=>{
      if (!ensureAdmin()) return;
      loadList();
    });
  </script>
</body>
</html>
