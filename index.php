<?php
// /restaurant-app/index.php — Industry-level Home (production-focused)
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>The Cafe Rio — Gulshan | Dine-in, Delivery, Reservations</title>
    <meta name="description"
        content="The Cafe Rio — Gulshan: Fresh menu, online ordering, table reservations, and family-friendly dining. Open daily for dine-in and delivery." />
    <link rel="canonical" href="/restaurant-app/index.php" />

    <!-- Favicon / Tab Logo -->
    <link rel="icon" type="image/png" href="frontend/assets/images/logo.png" />

    <!-- Open Graph -->
    <meta property="og:title" content="The Cafe Rio — Gulshan" />
    <meta property="og:description"
        content="Order online, reserve tables, and enjoy signature dishes at The Cafe Rio — Gulshan." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="/restaurant-app/index.php" />
    <meta property="og:image" content="/restaurant-app/frontend/assets/images/_placeholder.png" />

    <!-- Local Bootstrap (no CDN) -->
    <link rel="stylesheet" href="/restaurant-app/frontend/assets/vendor/bootstrap/bootstrap.min.css" />

    <!-- Leaflet CSS (for interactive map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

    <style>
    .heroo {
        height: 90vh;
        width: 100%;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
    }

    .heroo img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
    }

    .heroo .overlay {
        background: rgba(0, 0, 0, 0.5);
        padding: 20px;
        border-radius: 12px;
    }



    :root {
        --brand: #dc3545;
        --ink: #222;
        --muted: #6c757d;
        --line: #eef1f4;
        --radius: 18px;
        --glass1: rgba(255, 255, 255, .35);
        --glass2: rgba(255, 255, 255, .14);
        --blur: 10px;
        --shadow: 0 18px 40px rgba(0, 0, 0, .08);
        --container-pad: clamp(14px, 2vw, 24px);
    }
    .cuisine-text {
      display: inline-block;
      transition: all 0.3s ease-in-out;
    }

    .cuisine-text:hover {
      transform: scale(1.2);
      /* zoom in */
      color: #ff5722;
      /* color change */
      letter-spacing: 2px;
      /* spacing */
    }
    html,
    body {
        scroll-behavior: smooth
    }

    .glass {
        background: linear-gradient(135deg, var(--glass1), var(--glass2));
        border: 1px solid rgba(255, 255, 255, .35);
        backdrop-filter: blur(var(--blur));
        -webkit-backdrop-filter: blur(var(--blur));
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .hero {
        position: relative;
        background: radial-gradient(1200px 600px at 10% 10%, #fff 0, #f7f9fb 45%, #f3f6f9 100%);
        padding: 48px 0;
    }

    .hero-badge {
        display: inline-block;
        padding: .28rem .6rem;
        border-radius: 999px;
        background: #fd7e14;
        border: 1px solid var(--line);
        font-size: .85rem
    }

    .hero h1 {
        font-weight: 800;
        letter-spacing: -.02em
    }

    .cta .btn {
        border-radius: 12px
    }

    .section {
        padding: 48px 0
    }

    .section-title {
        font-weight: 800
    }

    .muted {
        color: var(--muted)
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 14px
    }

    .feature {
        padding: 16px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: #0dcaf0;
    }

    .feature .bi {
        font-size: 1.25rem;
        color: var(--brand)
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px
    }

    .menu-card {
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--line);
        background: #fff;
        min-height: 300px
    }

    .menu-card .img-wrap {
        height: 140px;
        background: #f4f6f8;
        overflow: hidden
    }

    .menu-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block
    }

    .menu-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 10px 12px
    }

    .menu-title {
        font-weight: 700;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden
    }

    .menu-desc {
        color: var(--muted);
        font-size: .92rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.6em
    }

    .menu-chip {
        display: inline-block;
        padding: .12rem .5rem;
        border-radius: 999px;
        background: #eef1f4;
        font-size: .78rem;
        margin: .35rem 0
    }

    .menu-actions {
        margin-top: auto;
        display: flex;
        gap: 8px
    }

    .menu-actions .btn {
        border-radius: 10px
    }

    .skel {
        background: linear-gradient(90deg, #f2f4f7 25%, #e9edf3 37%, #f2f4f7 63%);
        animation: sh 1.4s infinite ease;
        background-size: 400% 100%
    }

    @keyframes sh {
        0% {
            background-position: 100% 0
        }

        100% {
            background-position: -100% 0
        }
    }

    .skel.card {
        height: 300px;
        border-radius: 16px
    }

    .skel.line {
        height: 14px;
        border-radius: 8px
    }

    .review {
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 12px;
        background: #fff
    }

    .star {
        color: #ffc107
    }

    .map {
        height: 280px;
        border-radius: 16px;
        background: linear-gradient(135deg, #f0f3f7, #e9eef4)
    }

    .badge-soft {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: .25rem .5rem
    }
    .custom-alert {
    background-color: #ffebcc; /* light orange */
    color: #663300; /* dark text */
    border: 1px solid #ffcc80;
  }
  .custom-reviews {
    background-color: #f8f9fa; /* light gray */
    border-radius: 8px;
    padding: 15px;
  }
    </style>

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Restaurant",
        "name": "The Cafe Rio — Gulshan",
        "servesCuisine": "Cafe, Continental",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Gulshan",
            "addressRegion": "Dhaka",
            "addressCountry": "BD"
        },
        "url": "/restaurant-app/index.php",
        "image": "/restaurant-app/frontend/assets/images/_placeholder.png",
        "acceptsReservations": true,
        "priceRange": "৳৳"
    }
    </script>
</head>

<body>

    <?php include __DIR__ . "/frontend/partials/header-index.html"; ?>


    <div class="heroo position-relative">
        <video autoplay muted loop playsinline class="w-100" style="object-fit: cover; height: 100vh;">
            <source src="images/bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <!-- Overlay Content -->
        <div class="overlay position-absolute top-50 start-50 translate-middle text-center text-white">
            <h1 class="fw-bold display-5 fst-italic">The Cafe Rio</h1>
            <p class="lead fst-italic">Best Buffet in Town </p>
            <!-- <a href="#about" class="btn btn-primary btn-lg mt-3">Learn More</a> -->
        </div>
    </div>

    <!-- Special Cuisine Section -->
    <section id="cuisine" class="py-5 text-center">
        <div class="container">
            <p class="text-warning mb-1">Cuisine</p>
            <h2 class="fw-bold mb-5">OUR SPECIAL CUISINE</h2>

            <div class="row justify-content-center g-4">
                <!-- Thai -->
                <div class="col-6 col-md-2">
                    <img src="images/index/thai.png" alt="Thai Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">THAI</span>
                </div>
                <!-- Indian -->
                <div class="col-6 col-md-2">
                    <img src="images/index/indian.png" alt="Indian Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">INDIAN</span>
                </div>
                <!-- Chinese -->
                <div class="col-6 col-md-2">
                    <img src="images/index/chinese.png" alt="Chinese Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">CHINESE</span>
                </div>
                <!-- Korean -->
                <div class="col-6 col-md-2">
                    <img src="images/index/korean.png" alt="Korean Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">KOREAN</span>
                </div>
                <!-- Italian -->
                <div class="col-6 col-md-2">
                    <img src="images/index/italian.png" alt="Italian Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">ITALIAN</span>
                </div>
                <!-- Mughlai -->
                <div class="col-6 col-md-2">
                    <img src="images/index/mughlai.png" alt="Mughlai Cuisine" class="img-fluid rounded-circle mb-2">
                    <span class="fs-five text-uppercase fw-700 n900-clr d-block cuisine-text">MUGHLAI</span>
                </div>
            </div>

            <div class="mt-5">
                <a href="/restaurant-app/frontend/pages/reservations.php" class="btn btn-dark btn-lg rounded-pill px-5">RESERVATION</a>
            </div>
        </div>
    </section>


    <!-- Offer Card Section -->
    <section class="py-5">
        <div class="container">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="row g-0">

                    <!-- Offer Image -->
                    <div class="col-md-5">
                        <img src="images/offers.png" class="img-fluid h-100 w-100 object-fit-cover"
                            alt="Mega Discount Offer">
                    </div>

                    <!-- Offer Details -->
                    <div class="col-md-7 bg-light">
                        <div class="card-body p-4">
                            <h3 class="fw-bold text-danger mb-3">MEGA DISCOUNT 🎉</h3>
                            <p class="mb-3">
                                ২০০ টাকা ডিসকাউন্ট জন প্রতি থাকছে আগস্ট মাসে!
                                প্রতিদিন <strong>লাঞ্চ</strong> এবং <strong>ডিনার বুফে</strong> এ অফার চলবে।
                            </p>

                            <ul class="list-unstyled mb-3">
                                <li>🍖 গরু মাংস ভুঁনা, হাঁস ভুনা, মাটন রেজালা</li>
                                <li>🍤 Prawn hot sauce, রূপচাঁদা, Sea-food grill</li>
                                <li>🍗 চিকেন শাসলিক, মোমো, পিজ্জা</li>
                                <li>🍨 আইসক্রিম, জুস, চা, কফি (আনলিমিটেড)</li>
                            </ul>

                            <p class="fw-bold text-success mb-2">💟 লাঞ্চ ডিসকাউন্ট প্রাইস: ৯৯৯৳ (VAT সহ)</p>
                            <p class="fw-bold text-success mb-2">💟 ডিনার ডিসকাউন্ট প্রাইস: ১১৯৯৳ (VAT সহ)</p>
                            <p class="text-muted mb-2">🎁 Buy-1 Get-1 Free (Pubali Bank VISA Platinum & Gold Card)</p>

                            <p class="mb-1">⏰ Lunch: 1PM – 4PM</p>
                            <p class="mb-1">⏰ Dinner: 7PM – 10PM</p>
                            <p class="mb-3">🧒 Child Policy: 4–7 years half price</p>

                            <p class="fw-bold text-danger">☎ Hotline: +880 17 9943 7172</p>
                            <a href="/restaurant-app/frontend/pages/reservations.php" class="btn btn-danger btn-lg rounded-pill">Reserve Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <span class="hero-badge">Open today • 10:00–23:00</span>
                    <h1 class="display-5 mt-3">Crafted plates, warm ambience, and effortless <span
                            style="color:var(--brand)">online ordering</span>.</h1>
                    <p class="lead muted">Reserve a table or order favorites—freshly prepared and delivered fast in
                        Gulshan.</p>
                    <div class="cta d-flex gap-2 mt-3">
                        <a href="/restaurant-app/frontend/pages/reservations.php" class="btn btn-danger btn-lg"><i
                                class="bi bi-calendar2-week me-1"></i> Reserve a table</a>
                        <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-outline-secondary btn-lg"><i
                                class="bi bi-bag me-1"></i> Order online</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="glass p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold">Today’s highlights</div>
                                <div class="small muted">Chef’s picks & most-ordered</div>
                            </div>
                            <button id="btnRefreshHighlights" class="btn btn-sm btn-outline-secondary">
                                <span id="spHigh" class="spinner-border spinner-border-sm me-1 d-none"></span>
                                Refresh
                            </button>
                        </div>
                        <div class="mt-3" id="highlights">
                            <div class="skel line mb-2"></div>
                            <div class="skel line" style="width:70%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="section">
        <div class="container">
            <h2 class="section-title mb-3">Why dine with us</h2>
            <div class="features">
                <div class="feature">
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-truck"></i><b>Fast delivery</b></div>
                    <div class="small muted mt-1">Doorstep delivery across Gulshan within 45 minutes.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-shield-check"></i><b>Fresh &
                            hygienic</b></div>
                    <div class="small muted mt-1">Daily-sourced produce and strict kitchen hygiene.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-credit-card"></i><b>Easy payment</b>
                    </div>
                    <div class="small muted mt-1">Cash-on-delivery or quick digital payment options.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-people"></i><b>Family-friendly</b>
                    </div>
                    <div class="small muted mt-1">Spacious seating, couple & family zones available.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Preview -->
    <section class="section" id="menu">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="section-title mb-0">Popular this week</h2>
                <div class="d-flex gap-2 align-items-center">
                    <input id="q" class="form-control form-control-sm" placeholder="Search menu…"
                        style="min-width:200px">
                    <button id="btnSearch" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i>
                        Search</button>
                    <a href="/restaurant-app/frontend/pages/order.php" class="btn btn-danger btn-sm"><i
                            class="bi bi-bag"></i> Order now</a>
                </div>
            </div>

            <div id="menuAlert" class="alert d-none" role="alert"></div>
            <div id="menuGrid" class="menu-grid" aria-live="polite" aria-busy="true">
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
                <div class="skel card"></div>
            </div>
        </div>
    </section>

    <!-- Reviews -->
    <section class="section">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-2 ">
                <h2 class="section-title mb-0">What guests say</h2>
                <a href="/restaurant-app/frontend/pages/my-reservations.php" class="btn btn-outline-secondary btn-sm"><i
                        class="bi bi-journal-text"></i> My reservations</a>
            </div>
            <div id="revAlert" class="alert alert-success d-none" role="alert">
  This is a success message!
</div>

<div class="row g-3 bg-light p-3 rounded" id="reviews">
  <p>Review content goes here...</p>
</div>

        </div>
    </section>

    <!-- Visit us -->
    <section class="section">
        <div class="container">
            <div class="row g-3">
                <div class="col-lg-6">
                    <h2 class="section-title">Visit us</h2>

                    <!-- Map container -->
                    <div class="map w-90" style="height:350px;" aria-label="Map showing location">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.9918196074176!2d90.4149227753027!3d23.77973638851581!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c72b795390e3%3A0xb52df4007ef5e57f!2sThe%20Cafe%20Rio%20(Gulshan%20-%201)!5e0!3m2!1sen!2sbd!4v1693650000000!5m2!1sen!2sbd"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <span class="badge-soft"><i class="bi bi-geo-alt"></i> Road 12, Gulshan, Dhaka</span>
                        <span class="badge-soft"><i class="bi bi-telephone"></i> +8801799-437172</span>
                        <span class="badge-soft"><i class="bi bi-clock"></i> 10:00AM–11:00 PM</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h2 class="section-title">Reserve a table</h2>
                    <div class="glass p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold">Instant booking</div>
                                <div class="small muted">Live availability • couple, family & window zones</div>
                            </div>
                            <a class="btn btn-danger" href="/restaurant-app/frontend/pages/reservations.php">
                                <i class="bi bi-calendar2-week"></i> Reserve now
                            </a>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-1">Need help?</h6>
                        <div class="small muted">
                            Call us for larger parties, birthdays or events—custom seating and setups available.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include __DIR__ . "/frontend/partials/footer.html"; ?>

    <script>
    const el = s => document.querySelector(s);
    const money = v => Number(v || 0).toFixed(0);
    const esc = s => String(s ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    // Highlights
    async function loadHighlights() {
        const sp = el('#spHigh');
        sp && sp.classList.remove('d-none');
        const box = el('#highlights');
        if (box) box.innerHTML =
        '<div class="skel line mb-2"></div><div class="skel line" style="width:70%"></div>';
        try {
            const url = new URL((window.APP_BASE || '') + '/backend/public/index.php', window.location.origin);
            url.searchParams.set('r', 'menu');
            url.searchParams.set('a', 'list');
            url.searchParams.set('status', 'available');
            url.searchParams.set('limit', '4');
            const r = await fetch(url.toString());
            const d = await r.json().catch(() => ({}));
            const items = (d && d.items) ? d.items.slice(0, 3) : [];
            if (!items.length) {
                box.innerHTML = '<div class="small muted">No highlights available now.</div>';
                return;
            }
            box.innerHTML = items.map(it => `<div class="d-flex align-items-center justify-content-between py-1">
          <div class="text-truncate" title="${esc(it.name||'')}">${esc(it.name||'')}</div>
          <div class="fw-bold">৳${money(it.price)}</div>
        </div>`).join('');
        } catch (_) {
            box.innerHTML = '<div class="text-danger small">Failed to load highlights</div>';
        } finally {
            sp && sp.classList.add('d-none');
        }
    }

    // Menu preview
    let MENU = [];

    function renderMenu(items) {
        const grid = el('#menuGrid');
        grid.setAttribute('aria-busy', 'false');
        grid.innerHTML = '';
        if (!items.length) {
            grid.innerHTML = '<div class="muted">No items</div>';
            return;
        }
        for (let i = 0; i < items.length; i++) {
            const it = items[i];
            const img = it.image ? ((window.APP_BASE || '') + '/backend/public/uploads/menu/' + it.image) : ((window
                .APP_BASE || '') + '/frontend/assets/images/_placeholder.png');
            const card = document.createElement('div');
            card.className = 'menu-card';
            card.innerHTML = `
          <div class="img-wrap"><img src="${img}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='${(window.APP_BASE||'')}/frontend/assets/images/_placeholder.png';"></div>
          <div class="menu-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="menu-title">${esc(it.name||'')}</div>
              <div class="price">৳${money(it.price)}</div>
            </div>
            <div class="menu-desc">${esc(it.description||'')}</div>
            ${it.category ? `<div class="menu-chip">${esc(it.category)}</div>` : ``}
            <div class="menu-actions">
              <a class="btn btn-outline-secondary btn-sm flex-grow-1" href="/restaurant-app/frontend/pages/order.php"><i class="bi bi-plus-circle"></i> Add to cart</a>
            </div>
          </div>`;
            grid.appendChild(card);
        }
    }
    async function loadMenu(q) {
        const grid = el('#menuGrid');
        const alert = el('#menuAlert');
        alert.classList.add('d-none');
        grid.setAttribute('aria-busy', 'true');
        if (!MENU.length) {
            grid.innerHTML =
                '<div class="skel card"></div><div class="skel card"></div><div class="skel card"></div><div class="skel card"></div>';
        }
        try {
            const url = new URL((window.APP_BASE || '') + '/backend/public/index.php', window.location.origin);
            url.searchParams.set('r', 'menu');
            url.searchParams.set('a', 'list');
            url.searchParams.set('status', 'available');
            url.searchParams.set('limit', '24');
            const r = await fetch(url.toString());
            const d = await r.json().catch(() => ({}));
            if (!r.ok) {
                throw new Error(d?.error || 'Load failed');
            }
            MENU = d.items || [];
            const query = (q || '').toLowerCase();
            const list = query ?
                MENU.filter(it => ((it.name || '').toLowerCase().includes(query) || (it.description || '')
                    .toLowerCase().includes(query) || (it.category || '').toLowerCase().includes(query))) :
                MENU;
            renderMenu(list.slice(0, 12));
        } catch (err) {
            alert.className = 'alert alert-danger';
            alert.textContent = err.message || 'Unable to load menu';
            alert.classList.remove('d-none');
        } finally {
            grid.setAttribute('aria-busy', 'false');
        }
    }
    let t = null;
    el('#btnSearch').addEventListener('click', () => {
        const q = (el('#q').value || '').toLowerCase().trim();
        clearTimeout(t);
        t = setTimeout(() => loadMenu(q), 50);
    });

    // Reviews: fetch few items, then per-item reviews and merge
    async function loadReviews() {
        const wrap = el('#reviews');
        const alert = el('#revAlert');
        wrap.innerHTML = '';
        try {
            // 1) get some items to derive item_ids
            const menuUrl = new URL((window.APP_BASE || '') + '/backend/public/index.php', window.location.origin);
            menuUrl.searchParams.set('r', 'menu');
            menuUrl.searchParams.set('a', 'list');
            menuUrl.searchParams.set('status', 'available');
            menuUrl.searchParams.set('limit', '12');
            const mr = await fetch(menuUrl.toString());
            const md = await mr.json().catch(() => ({}));
            const items = (md && md.items) ? md.items.slice(0, 4) : [];
            if (!items.length) {
                wrap.innerHTML = '<div class="small muted">No reviews yet</div>';
                return;
            }

            // 2) fetch per-item reviews (limit 3 each), then merge and take top 6 by created_at
            const reqs = items.map(it => {
                const u = new URL((window.APP_BASE || '') + '/backend/public/index.php', window.location
                    .origin);
                u.searchParams.set('r', 'reviews');
                u.searchParams.set('a', 'list');
                u.searchParams.set('item_id', String(it.item_id));
                u.searchParams.set('limit', '3');
                return fetch(u.toString()).then(r => r.json().catch(() => ({}))).then(d => ({
                    item: it,
                    data: d
                }));
            });
            const packs = await Promise.all(reqs);
            let combined = [];
            packs.forEach(pk => {
                const arr = (pk.data && pk.data.items) ? pk.data.items.map(rv => ({
                    ...rv,
                    _item: pk.item
                })) : [];
                combined = combined.concat(arr);
            });
            combined.sort((a, b) => (new Date(b.created_at) - new Date(a.created_at)));
            const top = combined.slice(0, 6);

            if (!top.length) {
                wrap.innerHTML = '<div class="small muted">No reviews yet</div>';
                return;
            }
            wrap.innerHTML = top.map(rv => `
          <div class="col-md-6 col-lg-4">
            <div class="review h-100">
              <div class="d-flex align-items-center justify-content-between">
                <div class="fw-bold">${esc(rv.user_name || ('User #'+(rv.user_id||'')))}</div>
                <div>${'★'.repeat(rv.rating||0)}${'☆'.repeat(5 - (rv.rating||0))}</div>
              </div>
              <div class="small muted mt-1">${rv._item ? ('On ' + esc(rv._item.name||'')) : ''}</div>
              <div class="mt-2">${esc(rv.comment || '')}</div>
              <div class="small muted mt-1">${esc(rv.created_at||'')}</div>
            </div>
          </div>
        `).join('');
        } catch (err) {
            alert.className = 'alert alert-danger';
            alert.textContent = (err && err.message) ? err.message : 'Unable to load reviews';
            alert.classList.remove('d-none');
        }
    }

    // Visit us: initialize Leaflet map (no API key needed)
    function initMap() {
        // Gulshan, Dhaka coordinates
        const lat = 23.797911,
            lng = 90.414391; // reference coords
        const map = L.map('map').setView([lat, lng], 14);

        // OpenStreetMap tiles with attribution
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Marker + popup
        L.marker([lat, lng]).addTo(map).bindPopup('The Cafe Rio — Gulshan').openPopup();
    }

    // Init
    window.addEventListener('load', () => {
        loadHighlights();
        loadMenu('');
        loadReviews();
        initMap();
    });
    el('#btnRefreshHighlights').addEventListener('click', loadHighlights);
    </script>
</body>

</html>