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
<!-- Favicon / Tab Logo -->
<link rel="icon" type="image/png" href="../assets/images/logo.png" />

  <style>
    .hero {
      height: 90vh;
      width: 100%;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
    }

    .hero img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 80%;
      object-fit: cover;
      z-index: -1;
    }

    .hero .overlay {
      background: rgba(0, 0, 0, 0.5);
      padding: 20px;
      border-radius: 12px;
    }


.event-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    cursor: pointer;
  }
  
  .event-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 12px 24px rgba(0,0,0,0.2);
  }
  
  .event-img img {
    width: 100%;
    height: 230px;
    object-fit: cover;
    transition: transform 0.5s ease;
  }
  
  .event-card:hover .event-img img {
    transform: scale(1.1);
  }
  
  .event-body {
    padding: 15px;
  }
  
  .rating {
    color: #ff9800;
    font-size: 18px;
  }
    .card-elev {
      border: 0;
      box-shadow: 0 10px 25px rgba(0,0,0,.06);
      border-radius: 18px;
      transition: all 0.3s ease;
    }
    .card-elev:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(0,0,0,.12);
    }

    .table td,.table th {
      vertical-align: middle;
      transition: all 0.2s ease;
    }

    /* Table row hover effect */
    .table tbody tr {
      transition: all 0.25s ease;
    }
    .table tbody tr:hover {
      background-color: #f8f9fa !important;
      transform: scale(1.01);
      box-shadow: inset 0 0 8px rgba(0,0,0,0.05);
    }

    .mono { font-family: ui-monospace, Menlo, Consolas, monospace; }

    /* Button hover animations */
    .btn {
      transition: all 0.25s ease;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
  <?php include __DIR__ . "/../partials/header-admin.html"; ?>

   <!-- Hero Section -->
   <div class="hero position-relative">
    <!-- Overlay Content -->
    <img src="../assets/images/sbg.png" alt="Campus">
    <div class="overlay position-absolute top-50 start-50 translate-middle text-center text-white">
      <h1 class="fw-bold display-5 fst-italic">OUR GALLERY</h1>
      <p class="lead fst-italic  ">
        <a class="nav-link" href="/restaurant-app/frontend/pages/admin-dashboard.php">HOME</a>
      </p>
      <!-- <a href="#about" class="btn btn-primary btn-lg mt-3">Learn More</a> -->
    </div>
  </div>

<!-- Event Room Section -->
<section class="py-5 bg-light">
    <div class="container">
      <h2 class="fw-bold mb-5">Event Room</h2>
      <div class="row g-4">
        
        <!-- Card 1 -->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/party.png" class="img-fluid" alt="Metal Zone">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">METAL ZONE</h5>
            </div>
          </div>
        </div>
        
        <!-- Card 2 -->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/holl.png" class="img-fluid" alt="RWS">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">RWS</h5>
            </div>
          </div>
        </div>
        
        <!-- Card 3 -->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/buffet.png" class="img-fluid" alt="Food Point/Kids Zone">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">FOOD POINT</h5>
            </div>
          </div>
        </div>
        
        <!-- Card 4 -->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/see_food.png" class="img-fluid" alt="Food Point/Kids Zone">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">SEE FOOD CORNER</h5>
            </div>
          </div>
        </div>

        <!-- Card 5-->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/food_corner.png" class="img-fluid" alt="Food Point/Kids Zone">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">FOOD ZONE</h5>
            </div>
          </div>
        </div>
        <!-- Card 6-->
        <div class="col-md-4">
          <div class="event-card">
            <div class="event-img">
              <img src="../assets/images/room/rio.png" class="img-fluid" alt="Food Point/Kids Zone">
            </div>
            <div class="event-body text-center">
              <div class="rating mb-2">⭐⭐⭐⭐✰</div>
              <h5 class="fw-bold">PHOTO ZONE</h5>
            </div>
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
