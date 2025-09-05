<?php // frontend/pages/login.php — role-aware login ?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/><title>Sign in</title>
<link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
</head><body>
<?php include __DIR__ . "/../partials/header-user.html"; ?>
<section class="py-5 bg-light"><div class="container"><div class="row justify-content-center"><div class="col-md-6">
  <div class="card border-0 shadow-sm"><div class="card-body p-4">
    <h1 class="fw-bold mb-3">Sign in</h1>
    <div id="alert" class="alert d-none" role="alert"></div>
    <label class="form-label">Email</label><input id="email" type="email" class="form-control mb-3" placeholder="name@example.com">
    <label class="form-label">Password</label><input id="password" type="password" class="form-control mb-3" placeholder="••••••••">
    <button id="btnLogin" class="btn btn-danger w-100">Sign in</button>
  </div></div>
</div></div></div></section>
<?php include __DIR__ . "/../partials/footer.html"; ?>
<script>
  const Q=new URLSearchParams(location.search), redirectParam=Q.get('redirect'); const el=s=>document.querySelector(s);
  function showAlert(type,msg){ const b=el('#alert'); b.className=`alert alert-${type}`; b.textContent=msg; b.classList.remove('d-none'); }
  function getUser(){ try{return JSON.parse(localStorage.getItem('cr_user')||'null');}catch{return null;} }
  function saveUser(u){ localStorage.setItem('cr_user', JSON.stringify(u||{})); }
  (function auto(){ const u=getUser(); if (u&&u.user_id){ const role=(u.role||'').toLowerCase(); location.href = role==='admin'? '/restaurant-app/frontend/pages/admin-dashboard.php' : (redirectParam||'/restaurant-app/index.php'); } })();
  el('#btnLogin').addEventListener('click', async ()=>{
    const email=el('#email').value.trim(), password=el('#password').value;
    if(!email||!password){ showAlert('warning','Email ও Password দিন'); return; }
    try{
      const r=await fetch('/restaurant-app/backend/public/index.php?r=auth&a=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password})});
      const d=await r.json().catch(()=>({})); if(!r.ok){ showAlert('danger', d?.error||'Login failed'); return; }
      saveUser(d.user||{}); const role=(d.user?.role||'').toLowerCase(); location.href = role==='admin'? '/restaurant-app/frontend/pages/admin-dashboard.php' : (redirectParam||'/restaurant-app/index.php');
    }catch(_){ showAlert('danger','Network error'); }
  });
</script>
</body>
</html>
