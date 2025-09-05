<?php
// frontend/pages/profile.php — View & update profile (name/email/password)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Profile | The Cafe Rio – Gulshan</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="fw-bold">My Profile</h1>
            <button id="btnRefresh" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
          </div>

          <div id="alert" class="alert d-none" role="alert"></div>

          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">Basic info</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input id="name" class="form-control" placeholder="Your name">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input id="email" type="email" class="form-control" placeholder="name@example.com">
                </div>
              </div>
              <div class="d-flex gap-2 mt-3">
                <button id="btnSaveBasic" class="btn btn-danger">
                  <span id="spBasic" class="spinner-border spinner-border-sm me-2 d-none"></span>
                  Save changes
                </button>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">Change password</h5>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Current password</label>
                  <input id="current_password" type="password" class="form-control" placeholder="••••••••">
                </div>
                <div class="col-md-4">
                  <label class="form-label">New password</label>
                  <input id="new_password" type="password" class="form-control" placeholder="min 6 chars">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Confirm new password</label>
                  <input id="confirm_password" type="password" class="form-control" placeholder="repeat">
                </div>
              </div>
              <div class="d-flex gap-2 mt-3">
                <button id="btnSavePass" class="btn btn-outline-secondary">
                  <span id="spPass" class="spinner-border spinner-border-sm me-2 d-none"></span>
                  Update password
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const el = s => document.querySelector(s);
    function showAlert(type,msg){ const b=el('#alert'); b.className=`alert alert-${type}`; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(){ el('#alert').classList.add('d-none'); }
    function getUser(){ try { return JSON.parse(localStorage.getItem('cr_user')||'null'); } catch { return null; } }
    function saveUser(u){ localStorage.setItem('cr_user', JSON.stringify(u||{})); }

    let SELF = null;

    function requireAuth(){
      const u = getUser();
      if (!u || !u.user_id){ location.href = (window.APP_BASE||'') + '/frontend/pages/login.php?redirect=' + encodeURIComponent((window.APP_BASE||'') + '/frontend/pages/profile.php'); return null; }
      return u;
    }

    async function loadProfile(){
      hideAlert();
      const u = requireAuth(); if (!u) return;
      try{
        const qs = new URLSearchParams({ actor_user_id: String(u.user_id) });
        const res = await fetch((window.APP_BASE||'') + '/backend/public/index.php?r=users&a=get_profile&' + qs.toString());
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ showAlert('danger', data?.error || 'Failed to load profile'); return; }
        SELF = data.user || {};
        el('#name').value = SELF.name || '';
        el('#email').value = SELF.email || '';
      }catch(_){ showAlert('danger','Network error'); }
    }

    async function saveBasic(){
      const u = requireAuth(); if (!u) return;
      const name = el('#name').value.trim();
      const email = el('#email').value.trim();
      if (!name || !email){ showAlert('warning','Name/Email দিন'); return; }
      el('#spBasic').classList.remove('d-none');
      try{
        const res = await fetch((window.APP_BASE||'') + '/backend/public/index.php?r=users&a=update_profile', {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: u.user_id, name, email })
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ showAlert('danger', data?.error || 'Update failed'); return; }
        // Refresh cr_user cache
        const updated = { ...u, name, email };
        saveUser(updated);
        showAlert('success','Profile updated');
      }catch(_){ showAlert('danger','Network error'); }
      finally{ el('#spBasic').classList.add('d-none'); }
    }

    async function savePassword(){
      const u = requireAuth(); if (!u) return;
      const curr = el('#current_password').value;
      const np = el('#new_password').value;
      const cp = el('#confirm_password').value;
      if (!curr || !np){ showAlert('warning','Current/New password দিন'); return; }
      if (np !== cp){ showAlert('warning','New password মিলছে না'); return; }
      el('#spPass').classList.remove('d-none');
      try{
        const res = await fetch((window.APP_BASE||'') + '/backend/public/index.php?r=users&a=update_profile', {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ actor_user_id: u.user_id, current_password: curr, new_password: np })
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ showAlert('danger', data?.error || 'Update failed'); return; }
        // Clear password fields
        el('#current_password').value=''; el('#new_password').value=''; el('#confirm_password').value='';
        showAlert('success','Password updated');
      }catch(_){ showAlert('danger','Network error'); }
      finally{ el('#spPass').classList.add('d-none'); }
    }

    window.addEventListener('load', loadProfile);
    el('#btnRefresh').addEventListener('click', loadProfile);
    el('#btnSaveBasic').addEventListener('click', saveBasic);
    el('#btnSavePass').addEventListener('click', savePassword);
  </script>
</body>
</html>
