<?php
// frontend/pages/register.php — User registration (APP_BASE-safe)
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create account | The Cafe Rio – Gulshan</title>
  <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />
</head>
<body>
  <?php include __DIR__ . "/../partials/header-user.html"; ?>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h1 class="fw-bold mb-3">Create account</h1>
              <div id="alert" class="alert d-none" role="alert"></div>
              <label class="form-label">Name</label>
              <input id="name" class="form-control mb-3" placeholder="Full name">
              <label class="form-label">Email</label>
              <input id="email" type="email" class="form-control mb-3" placeholder="name@example.com">
              <label class="form-label">Password</label>
              <input id="password" type="password" class="form-control mb-3" placeholder="min 6 chars">
              <label class="form-label">Confirm password</label>
              <input id="confirm" type="password" class="form-control mb-3" placeholder="repeat password">
              <button id="btnRegister" class="btn btn-danger w-100">
                <span id="sp" class="spinner-border spinner-border-sm me-2 d-none"></span>
                Create account
              </button>
              <div class="mt-3 text-center">
                Already have an account? <a href="/restaurant-app/frontend/pages/login.php">Sign in</a>
              </div>
            </div>
          </div>
          <div class="text-center mt-3">
            <a class="link-underline link-underline-opacity-0" href="/restaurant-app/index.php">← Back to Home</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . "/../partials/footer.html"; ?>

  <script>
    const el = s => document.querySelector(s);
    const Q = new URLSearchParams(location.search);
    const redirectParam = Q.get('redirect');

    function showAlert(type,msg){ const b=el('#alert'); b.className=`alert alert-${type}`; b.textContent=msg; b.classList.remove('d-none'); }
    function hideAlert(){ el('#alert').classList.add('d-none'); }
    function saveUser(u){ localStorage.setItem('cr_user', JSON.stringify(u||{})); }

    el('#btnRegister').addEventListener('click', async ()=>{
      hideAlert();
      const name = el('#name').value.trim();
      const email = el('#email').value.trim();
      const password = el('#password').value;
      const confirm = el('#confirm').value;

      if (!name || !email || !password){ showAlert('warning','সব ঘর পূরণ করুন'); return; }
      if (password !== confirm){ showAlert('warning','Password মিলছে না'); return; }

      el('#sp').classList.remove('d-none');
      try{
        const res = await fetch((window.APP_BASE||'') + '/backend/public/index.php?r=auth&a=register', {
          method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ name, email, password })
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok){ showAlert('danger', data?.error || 'Registration failed'); return; }
        saveUser(data.user || {});
        const role = (data.user?.role || '').toLowerCase();
        location.href = role === 'admin'
          ? (window.APP_BASE||'') + '/frontend/pages/admin-dashboard.php'
          : (redirectParam || (window.APP_BASE||'') + '/index.php');
      }catch(_){
        showAlert('danger','Network error');
      }finally{
        el('#sp').classList.add('d-none');
      }
    });
  </script>
</body>
</html>
