<?php

/**
 * index.php — Alan Bespoke Shoes
 * Luxury bespoke leather shoes (single-page, no external dependencies)
 */

// -------------------------
// Site configuration
// -------------------------
$siteName = "Alan Bespoke Shoes";
$tagline  = "Luxury bespoke leather shoes — made-to-measure, hand-finished";
$address  = "13206 Clover Creek Point Ln, Humble, TX 77346, USA";
$phone    = "2817731969";
$emailTo  = "hello@Alanbespokeshoes.com"; // change to your real inbox

// IMPORTANT: replace after deploy (used for canonical + og)
$siteUrl = "https://example.com";
$canonicalUrl = rtrim($siteUrl, "/") . "/";

// -------------------------
// Helpers
// -------------------------
function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }
function tel_clean($p) { return preg_replace('/[^0-9+]/', '', (string)$p); }

// -------------------------
// Contact form handling
// -------------------------
$form = ["name"=>"","email"=>"","phone"=>"","interest"=>"","message"=>""];
$errors = [];
$success = false;

// bootstrap.php already starts session; do NOT call session_start() here.
if (!isset($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"]) || strlen($_SESSION["csrf_token"]) < 20) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];

if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST" && isset($_POST["contact_form"])) {

  // CSRF
  $postedToken = $_POST["csrf_token"] ?? "";
  if (!$postedToken || !hash_equals($csrfToken, $postedToken)) {
    $errors[] = "Security check failed. Please refresh and try again.";
  }

  // Honeypot
  $honeypot = trim($_POST["website"] ?? "");
  if ($honeypot !== "") {
    $errors[] = "Submission rejected.";
  }

  // Collect
  $form["name"]     = trim($_POST["name"] ?? "");
  $form["email"]    = trim($_POST["email"] ?? "");
  $form["phone"]    = trim($_POST["phone"] ?? "");
  $form["interest"] = trim($_POST["interest"] ?? "");
  $form["message"]  = trim($_POST["message"] ?? "");

  // Validate
  if (mb_strlen($form["name"]) < 2) $errors[] = "Name is required (min 2 characters).";
  if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if ($form["phone"] !== "" && !preg_match('/^[0-9+\-\s().]{7,25}$/', $form["phone"])) $errors[] = "Phone format looks invalid.";
  if (mb_strlen($form["message"]) < 20) $errors[] = "Message is required (min 20 characters).";

  if (!$errors) {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $ua = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";

    $body =
      "New inquiry — {$siteName}\n\n" .
      "Name: {$form["name"]}\n" .
      "Email: {$form["email"]}\n" .
      "Phone: " . ($form["phone"] ?: "-") . "\n" .
      "Interest: " . ($form["interest"] ?: "-") . "\n\n" .
      "Message:\n{$form["message"]}\n\n" .
      "----\nIP: {$ip}\nUser-Agent: {$ua}\n";

    // For best deliverability: set From to a domain mailbox (once available).
    $headers = [
      "MIME-Version: 1.0",
      "Content-Type: text/plain; charset=UTF-8",
      "From: {$siteName} <{$emailTo}>",
      "Reply-To: {$form["name"]} <{$form["email"]}>"
    ];

    $sent = @mail($emailTo, "[Website] Bespoke Shoes Inquiry", $body, implode("\r\n", $headers));

    if ($sent) {
      $success = true;
      $form = ["name"=>"","email"=>"","phone"=>"","interest"=>"","message"=>""];
    } else {
      // If mail() isn't configured, log so you still capture leads.
      $logLine = "[" . date("Y-m-d H:i:s") . "] " . str_replace("\n", " | ", $body) . "\n\n";
      @file_put_contents(__DIR__ . "/contact_submissions.log", $logLine, FILE_APPEND);
      $errors[] = "Message saved, but email delivery is not configured on this server. Please call us.";
    }
  }
}

// -------------------------
// SEO
// -------------------------
$pageTitle = "{$siteName} | Luxury Bespoke Leather Shoes";
$description = "Alan Bespoke Shoes crafts luxury made-to-measure leather shoes with hand-finished details, premium materials, and a precise fitting process in Humble, TX.";
$keywords = "bespoke shoes, made to measure shoes, luxury leather shoes, handwelted shoes, custom shoes Texas, bespoke shoemaker";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />

  <title><?php echo h($pageTitle); ?></title>
  <meta name="description" content="<?php echo h($description); ?>" />
  <meta name="keywords" content="<?php echo h($keywords); ?>" />
  <meta name="author" content="<?php echo h($siteName); ?>" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
  <link rel="canonical" href="<?php echo h($canonicalUrl); ?>" />

  <meta name="theme-color" content="#0f0a07" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo h($siteName); ?>" />
  <meta property="og:title" content="<?php echo h($pageTitle); ?>" />
  <meta property="og:description" content="<?php echo h($description); ?>" />
  <meta property="og:url" content="<?php echo h($canonicalUrl); ?>" />

  <!-- LocalBusiness schema -->
  <script type="application/ld+json">
  <?php
    $schema = [
      "@context" => "https://schema.org",
      "@type" => "LocalBusiness",
      "name" => $siteName,
      "description" => $description,
      "telephone" => $phone,
      "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "13206 Clover Creek Point Ln",
        "addressLocality" => "Humble",
        "addressRegion" => "TX",
        "postalCode" => "77346",
        "addressCountry" => "US"
      ],
      "areaServed" => "Houston, TX Metro",
      "url" => $canonicalUrl
    ];
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>

  <style>
    :root{
      /* Palette: espresso / ivory / brass / oxblood */
      --bg:#0f0a07;
      --panel:#16100c;
      --panel2:#1c140f;
      --text:#f5efe8;
      --muted:#c7b7a7;
      --line:rgba(245,239,232,.12);
      --accent:#caa46a;     /* brass */
      --accent2:#6d1a1a;    /* oxblood */
      --ok:#7dffb2;
      --danger:#ff7b7b;

      --shadow:0 18px 55px rgba(0,0,0,.60);
      --radius:18px;
      --max:1120px;
      --sans: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{
      margin:0;
      font-family:var(--sans);
      color:var(--text);
      background:
        radial-gradient(900px 560px at 18% -10%, rgba(202,164,106,.18), transparent 55%),
        radial-gradient(800px 520px at 90% 10%, rgba(109,26,26,.16), transparent 55%),
        linear-gradient(180deg, rgba(255,255,255,.02), transparent 280px),
        var(--bg);
      line-height:1.65;
    }
    a{color:inherit;text-decoration:none}
    .container{max-width:var(--max); margin:0 auto; padding:0 22px}

    .skip{position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden}
    .skip:focus{
      left:22px; top:18px; width:auto; height:auto;
      padding:10px 12px;
      background:var(--panel);
      border:1px solid var(--line);
      border-radius:12px;
      z-index:9999;
    }

    header{
      position:sticky; top:0; z-index:999;
      background: rgba(15,10,7,.78);
      backdrop-filter: blur(10px);
      border-bottom:1px solid var(--line);
    }
    .topbar{
      display:flex; align-items:center; justify-content:space-between; gap:14px;
      padding:14px 0;
    }
    .brand{display:flex; flex-direction:column; gap:2px}
    .brand strong{font-size:18px; letter-spacing:.35px}
    .brand span{font-size:12px; color:var(--muted); letter-spacing:.18em; text-transform:uppercase}

    nav ul{list-style:none; margin:0; padding:0; display:flex; gap:10px; align-items:center}
    nav a{
      font-size:13px; color:var(--muted);
      padding:10px 12px; border-radius:999px;
      border:1px solid transparent;
      transition:all .15s ease;
      white-space:nowrap;
    }
    nav a:hover{
      color:var(--text);
      border-color:rgba(202,164,106,.30);
      background:rgba(202,164,106,.06);
    }
    nav a.active{
      color:var(--text);
      border-color:rgba(202,164,106,.55);
      background:rgba(202,164,106,.10);
    }

    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      padding:10px 14px;
      border-radius:999px;
      border:1px solid rgba(202,164,106,.55);
      background: linear-gradient(180deg, rgba(202,164,106,.18), rgba(109,26,26,.10));
      color:var(--text);
      font-size:13px;
      cursor:pointer;
      transition:all .15s ease;
      white-space:nowrap;
    }
    .btn:hover{background: linear-gradient(180deg, rgba(202,164,106,.24), rgba(109,26,26,.12))}
    .btn.secondary{
      border-color:rgba(245,239,232,.16);
      background:rgba(255,255,255,.03);
      color:var(--text);
    }

    .menuBtn{
      display:none;
      border:1px solid rgba(245,239,232,.16);
      background:rgba(255,255,255,.03);
      padding:10px 12px;
      border-radius:999px;
      font-size:13px;
      color:var(--text);
      cursor:pointer;
    }

    .mobileNav{
      display:none;
      border-top:1px solid var(--line);
      padding:10px 0 14px;
    }
    .mobileNav a{
      display:block;
      padding:10px 12px;
      border-radius:12px;
      color:var(--muted);
      border:1px solid transparent;
    }
    .mobileNav a:hover{
      color:var(--text);
      border-color:rgba(202,164,106,.30);
      background:rgba(202,164,106,.06);
    }
    .mobileNav a.active{
      color:var(--text);
      border-color:rgba(202,164,106,.55);
      background:rgba(202,164,106,.10);
    }

    section{padding:76px 0}
    .panel{
      background: linear-gradient(180deg, rgba(28,20,15,.92), rgba(22,16,12,.92));
      border:1px solid var(--line);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .pad{padding:18px}

    .hero{padding:0}
    .heroInner{
      padding:86px 0 58px;
      border-bottom:1px solid var(--line);
      background:
        radial-gradient(900px 560px at 12% 10%, rgba(202,164,106,.22), transparent 55%),
        radial-gradient(820px 520px at 88% 24%, rgba(109,26,26,.18), transparent 55%);
    }
    .heroGrid{
      display:grid;
      grid-template-columns: 1.15fr .85fr;
      gap:18px;
      align-items:end;
    }
    .kicker{
      font-size:12px;
      letter-spacing:.22em;
      text-transform:uppercase;
      color:rgba(245,239,232,.82);
    }
    h1{
      margin:10px 0 12px;
      font-size:46px;
      line-height:1.05;
      letter-spacing:-.02em;
    }
    .lead{
      margin:0 0 16px;
      color:rgba(245,239,232,.86);
      font-size:16px;
      max-width:70ch;
    }
    .pillRow{display:flex; flex-wrap:wrap; gap:10px; margin-top:12px}
    .pill{
      border:1px solid rgba(202,164,106,.22);
      background:rgba(202,164,106,.07);
      color:rgba(245,239,232,.90);
      padding:8px 10px;
      border-radius:999px;
      font-size:12px;
      white-space:nowrap;
    }

    .feature{
      display:flex; gap:14px; align-items:flex-start;
      padding:16px;
      border-radius:var(--radius);
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
    }
    .dot{
      min-width:10px; height:10px; margin-top:8px;
      border-radius:999px;
      background:var(--accent);
      box-shadow:0 0 0 3px rgba(202,164,106,.14);
    }
    .feature strong{display:block; font-size:16px; margin-bottom:6px}
    .feature p{margin:0; color:var(--muted); font-size:14px}

    .sectionTitle{
      display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:18px
    }
    .sectionTitle h2{margin:0; font-size:28px; letter-spacing:-.01em}
    .sectionTitle p{margin:0; color:var(--muted); font-size:14px; max-width:680px}

    .grid2{display:grid; grid-template-columns: 1fr 1fr; gap:16px}
    .grid3{display:grid; grid-template-columns: repeat(3, 1fr); gap:16px}

    .mini{margin:0; color:var(--muted); font-size:13px}

    /* Form */
    form{display:grid; gap:12px}
    .row2{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
    label{display:block; margin:0 0 6px; font-size:12px; color:rgba(245,239,232,.78); letter-spacing:.08em; text-transform:uppercase}
    input, textarea, select{
      width:100%;
      padding:12px 12px;
      border-radius:14px;
      border:1px solid rgba(245,239,232,.14);
      background: rgba(15,10,7,.35);
      color:var(--text);
      outline:none;
      font-size:14px;
    }
    textarea{min-height:150px; resize:vertical}
    input:focus, textarea:focus, select:focus{
      border-color: rgba(202,164,106,.70);
      box-shadow: 0 0 0 3px rgba(202,164,106,.14);
    }

    .notice{
      border-radius:16px;
      padding:12px 12px;
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
      font-size:14px;
      margin-bottom:12px;
    }
    .notice.ok{border-color: rgba(125,255,178,.25); background: rgba(125,255,178,.08)}
    .notice.err{border-color: rgba(255,123,123,.25); background: rgba(255,123,123,.08)}
    .notice ul{margin:8px 0 0 18px}

    footer{
      border-top:1px solid var(--line);
      background: rgba(15,10,7,.82);
      padding:26px 0;
    }
    .footerGrid{display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap}
    .footLinks{display:flex; gap:10px; flex-wrap:wrap}
    .footLinks a{
      color:var(--muted);
      padding:8px 10px;
      border-radius:999px;
      border:1px solid transparent;
    }
    .footLinks a:hover{
      color:var(--text);
      border-color:rgba(202,164,106,.30);
      background:rgba(202,164,106,.06);
    }

    @media (max-width: 980px){
      .heroGrid{grid-template-columns: 1fr}
      nav ul{display:none}
      .menuBtn{display:inline-flex}
      .grid3{grid-template-columns: 1fr}
      .grid2{grid-template-columns: 1fr}
      .row2{grid-template-columns: 1fr}
      h1{font-size:38px}
    }
  </style>
</head>

<body>
  <a class="skip" href="#main">Skip to content</a>

  <header>
    <div class="container">
      <div class="topbar">
        <div class="brand">
          <strong><?php echo h($siteName); ?></strong>
          <span>Bespoke · Made-to-Measure · Leather</span>
        </div>

        <nav aria-label="Primary navigation">
          <ul id="desktopNav">
            <li><a href="#home" data-link="home">Home</a></li>
            <li><a href="#craft" data-link="craft">Craft</a></li>
            <li><a href="#collections" data-link="collections">Styles</a></li>
            <li><a href="#process" data-link="process">Process</a></li>
            <li><a href="#care" data-link="care">Care</a></li>
            <li><a href="#faq" data-link="faq">FAQ</a></li>
            <li><a href="#contact" data-link="contact">Contact</a></li>
          </ul>
        </nav>

        <div style="display:flex; gap:10px; align-items:center;">
          <a class="btn" href="#contact">Book a Fitting</a>
          <a class="btn secondary" href="tel:<?php echo h(tel_clean($phone)); ?>">Call</a>
          <button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="mobileNav">Menu</button>
        </div>
      </div>

      <div class="mobileNav" id="mobileNav" aria-label="Mobile navigation">
        <a href="#home" data-link="home">Home</a>
        <a href="#craft" data-link="craft">Craft</a>
        <a href="#collections" data-link="collections">Styles</a>
        <a href="#process" data-link="process">Process</a>
        <a href="#care" data-link="care">Care</a>
        <a href="#faq" data-link="faq">FAQ</a>
        <a href="#contact" data-link="contact">Contact</a>
      </div>
    </div>
  </header>

  <main id="main">

    <!-- HERO -->
    <section class="hero" id="home" aria-label="Hero">
      <div class="heroInner">
        <div class="container">
          <div class="heroGrid">
            <div>
              <div class="kicker">Luxury bespoke footwear · measured, patterned, made, finished</div>
              <h1>Shoes that fit like a signature.</h1>
              <p class="lead">
                <?php echo h($siteName); ?> creates luxury bespoke leather shoes built around your measurements, posture,
                and daily life. This is not “pick a size and hope.” It’s a craft process that delivers comfort,
                elegance, and longevity — with details tailored to you.
              </p>

              <div class="pillRow" aria-label="Highlights">
                <span class="pill">Hand-selected leathers</span>
                <span class="pill">Made-to-measure lasts</span>
                <span class="pill">Hand-finished patina options</span>
                <span class="pill">Repairable construction</span>
              </div>

              <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
                <a class="btn" href="#contact">Request Consultation</a>
                <a class="btn secondary" href="#process">See the Process</a>
              </div>

              <p class="mini" style="margin-top:14px;">
                Based in Humble, TX · Serving the Houston metro.
                <span style="display:inline-block; margin:0 8px; opacity:.55;">•</span>
                Call <a href="tel:<?php echo h(tel_clean($phone)); ?>" style="text-decoration:underline; text-underline-offset:3px;"><?php echo h($phone); ?></a>
              </p>
            </div>

            <aside class="panel" aria-label="At-a-glance details">
              <div class="pad">
                <strong style="display:block; font-size:18px; margin-bottom:10px;">What “bespoke” means here</strong>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Fit engineered to you</strong>
                    <p>Measurements, gait notes, and fit preferences shape the pattern and internal balance.</p>
                  </div>
                </div>

                <div style="height:10px;"></div>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Materials that age beautifully</strong>
                    <p>Premium hides selected for grain, strength, and a finish that improves over time.</p>
                  </div>
                </div>

                <div style="height:10px;"></div>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Built to be maintained</strong>
                    <p>Repairable construction and care guidance designed for years of wear.</p>
                  </div>
                </div>

              </div>
            </aside>

          </div>
        </div>
      </div>
    </section>

    <!-- ABOUT / CRAFT INTRO -->
    <section id="craft" aria-label="Craft">
      <div class="container">
        <div class="sectionTitle">
          <h2>The craft</h2>
          <p>
            Luxury footwear is the meeting point of proportion, comfort, and material honesty.
            We build shoes the slow way — because that’s how you get reliable structure and a refined finish.
          </p>
        </div>

        <div class="grid2">
          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Design with purpose</strong>
            <p class="mini" style="margin-top:10px;">
              We start by understanding where you wear your shoes: office, events, travel, or daily rotation.
              Then we choose the right silhouette, toe shape, and leather type that fits your wardrobe and climate.
              The result is footwear that looks intentional — not generic.
            </p>
            <p class="mini" style="margin-top:10px;">
              Want understated? We keep the lines clean and the shine controlled. Prefer a statement?
              We can explore patina tones, broguing, and contrast stitching while preserving elegance.
            </p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Structure that supports you</strong>
            <p class="mini" style="margin-top:10px;">
              Comfort is not foam. Comfort is balance: heel stability, correct instep volume, toe room, and a sole that flexes
              in the right place. We build around your measurements and fit notes so pressure points don’t show up after a few hours.
            </p>
            <p class="mini" style="margin-top:10px;">
              If you’ve struggled with one foot larger than the other, wide forefoot, high instep, or narrow heel —
              bespoke is where those issues get addressed properly.
            </p>
          </div></div>
        </div>
      </div>
    </section>
