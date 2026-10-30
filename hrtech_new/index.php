<?php
$portals = [
    'admin' => ['title' => 'Admin', 'subtitle' => 'Manage people, access and HR operations', 'icon' => '⚙', 'color' => 'blue'],
    'manager' => ['title' => 'Manager', 'subtitle' => 'Lead your team and review performance', 'icon' => '▣', 'color' => 'purple'],
    'employee' => ['title' => 'Employee', 'subtitle' => 'View your tasks, goals and progress', 'icon' => '♙', 'color' => 'green'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HRTech | People & Performance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{--navy:#101b32;--blue:#58b8f4;--ink:#17233a;--muted:#64748b;--line:#e4eaf2;--pale:#f5f8fc}
    *{box-sizing:border-box} html{scroll-behavior:smooth} body{margin:0;font-family:Inter,Segoe UI,Arial,sans-serif;color:var(--ink);background:#fff}
    .topbar{height:78px;display:flex;align-items:center;border-bottom:1px solid var(--line);background:rgba(255,255,255,.96)}
    .navwrap{width:min(1160px,92%);margin:auto;display:flex;align-items:center;justify-content:space-between;gap:20px}
    .brand{display:flex;align-items:center;gap:11px;text-decoration:none;color:var(--navy);font-size:25px;font-weight:800;letter-spacing:-.7px}
    .brandmark{height:42px;width:42px;border-radius:13px;background:var(--navy);display:grid;place-items:center;color:var(--blue);font-size:22px;font-weight:900}
    .navlinks{display:flex;align-items:center;gap:10px}.navlinks a{font-size:14px;text-decoration:none;color:#526078;font-weight:700;padding:10px 13px;border-radius:9px;transition:background .2s,color .2s}.navlinks a:hover{color:#147cc0;background:#eef7ff}.navlinks .nav-cta{margin-left:5px}
    .nav-cta{padding:11px 17px;border-radius:10px;background:var(--navy);color:#fff!important}
    .hero{background:radial-gradient(circle at 83% 20%,rgba(88,184,244,.16),transparent 28%),linear-gradient(135deg,#f7fbff,#fff 60%);padding:78px 0 66px}
    .wrap{width:min(1160px,92%);margin:auto}.eyebrow{display:inline-flex;gap:8px;align-items:center;background:#eaf6ff;color:#176ca7;padding:8px 13px;border-radius:30px;font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase}
    .hero h1{font-size:clamp(38px,5.2vw,64px);line-height:1.08;letter-spacing:-2.5px;font-weight:850;max-width:720px;margin:22px 0 18px;color:var(--navy)}
    .hero h1 span{color:#168bd0}.hero p{font-size:17px;line-height:1.8;color:var(--muted);max-width:650px;margin:0}
    .section-label{font-size:12px;letter-spacing:1.4px;text-transform:uppercase;font-weight:800;color:#77849a;margin:34px 0 15px}
    .portal-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}
    .portal{display:flex;flex-direction:column;min-height:190px;padding:23px;border:1px solid var(--line);border-radius:18px;background:#fff;text-decoration:none;color:inherit;box-shadow:0 10px 30px rgba(16,27,50,.045);transition:transform .2s,box-shadow .2s,border-color .2s}
    .portal:hover{transform:translateY(-5px);box-shadow:0 18px 38px rgba(16,27,50,.1);border-color:#a8dafa;color:inherit}
    .portal-top{display:flex;align-items:center;justify-content:space-between}.portal-icon{width:47px;height:47px;border-radius:14px;display:grid;place-items:center;font-size:23px;font-weight:800}.admin .portal-icon{background:#eaf5ff;color:#167bc0}.manager .portal-icon{background:#f0edff;color:#6d56c8}.employee .portal-icon{background:#e8f9f0;color:#19845b}
    .portal h3{font-size:20px;font-weight:800;margin:18px 0 6px}.portal p{font-size:13px;line-height:1.6;color:var(--muted);margin:0 0 15px}.portal-link{margin-top:auto;font-size:14px;font-weight:800;color:#167bc0}.portal-link span{padding-left:5px}
    .section{padding:72px 0}.section.alt{background:var(--pale)}.section-head{text-align:center;max-width:700px;margin:0 auto 34px}.section-head h2{font-size:clamp(28px,3vw,38px);font-weight:850;letter-spacing:-1px;color:var(--navy);margin-bottom:12px}.section-head p{color:var(--muted);line-height:1.7;margin:0}
    .feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}.feature{background:#fff;border:1px solid var(--line);border-radius:16px;padding:23px}.feature .ficon{font-size:24px;margin-bottom:13px}.feature h3{font-size:17px;font-weight:800}.feature p{font-size:14px;color:var(--muted);line-height:1.7;margin:0}
    .about-row{display:grid;grid-template-columns:1fr 1fr;gap:45px;align-items:center}.about-row h2{font-size:35px;letter-spacing:-1px;font-weight:850;color:var(--navy)}.about-row p{color:var(--muted);line-height:1.8}.about-points{display:grid;grid-template-columns:1fr 1fr;gap:12px}.about-point{padding:17px;border:1px solid var(--line);border-radius:13px;background:white;font-size:14px;font-weight:750}.about-point span{color:#168bd0;margin-right:7px}
    .workflow-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}.workflow-card{border:1px solid var(--line);border-radius:16px;padding:22px;background:#fff}.workflow-number{height:38px;width:38px;display:grid;place-items:center;border-radius:12px;background:#eaf6ff;color:#167bc0;font-weight:850;margin-bottom:14px}.workflow-card h3{font-size:17px;font-weight:800}.workflow-card p{font-size:14px;color:var(--muted);line-height:1.7;margin:0}.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;max-width:850px;margin:auto;text-align:left}.contact-card{border:1px solid var(--line);background:#fff;border-radius:16px;padding:24px}.contact-card h3{font-size:17px;font-weight:800}.contact-card p{color:var(--muted);line-height:1.7;margin-bottom:0}.back-top{position:fixed;right:22px;bottom:22px;z-index:1000;border:0;border-radius:50px;padding:12px 16px;background:var(--navy);color:#fff;font-weight:750;box-shadow:0 8px 24px #101b3230;cursor:pointer}.footer{padding:25px 0;background:var(--navy);color:#b7c3d7;font-size:13px}.footer .wrap{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}.footer strong{color:#fff}
    @media(max-width:760px){.navwrap{align-items:flex-start;padding:12px 0;flex-direction:column}.topbar{height:auto}.navlinks{width:100%;gap:4px;flex-wrap:wrap}.navlinks a{padding:8px 10px;font-size:13px}.portal-grid,.feature-grid,.workflow-grid{grid-template-columns:1fr}.portal{min-height:160px}.section{padding:52px 0}.about-row,.contact-grid{grid-template-columns:1fr;gap:25px}.hero{padding:54px 0 45px}.hero h1{letter-spacing:-1.5px}.nav-cta{padding:9px 12px}}
  </style>
</head>
<body>
<header class="topbar"><div class="navwrap">
  <a class="brand" href="index.php"><span class="brandmark">H</span><span>HRTech</span></a>
  <nav class="navlinks" aria-label="Main navigation"><a href="#home">Home</a><a href="#about">About</a><a href="#features">Features</a><a href="#how-it-works">How It Works</a><a href="#contact">Contact</a><a class="nav-cta" href="#portals">Open Portal ↗</a></nav>
</div></header>
<main>
<section class="hero" id="home"><div class="wrap">
  <span class="eyebrow">✦ Employee Management Platform</span>
  <h1>People first.<br><span>Performance forward.</span></h1>
  <p>One simple workspace to manage employees, assign work, track goals, review performance and keep your team moving forward.</p>
  <div id="portals" class="section-label">Choose your portal to continue</div>
  <div class="portal-grid">
    <a class="portal admin" href="login.php?role=admin"><div class="portal-top"><span class="portal-icon">⚙</span><span aria-hidden="true">↗</span></div><h3>Admin Portal</h3><p>Manage employees, HR operations and system activities.</p><span class="portal-link">Continue as Admin <span>→</span></span></a>
    <a class="portal manager" href="login.php?role=manager"><div class="portal-top"><span class="portal-icon">▣</span><span aria-hidden="true">↗</span></div><h3>Manager Portal</h3><p>Coordinate team tasks, review goals and support performance.</p><span class="portal-link">Continue as Manager <span>→</span></span></a>
    <a class="portal employee" href="login.php?role=employee"><div class="portal-top"><span class="portal-icon">♙</span><span aria-hidden="true">↗</span></div><h3>Employee Portal</h3><p>View assigned tasks, personal goals and performance progress.</p><span class="portal-link">Continue as Employee <span>→</span></span></a>
  </div>
</div></section>
<section class="section" id="about"><div class="wrap about-row"><div><span class="eyebrow">About HRTech</span><h2 class="mt-3">Make everyday HR work simpler.</h2><p>HRTech brings common employee and performance activities into one organized place. It helps teams understand responsibilities, follow progress and keep important work visible.</p><p>Designed for Admins, Managers and Employees, with role-based access to the relevant workspace.</p></div><div class="about-points"><div class="about-point"><span>✓</span> Clear responsibilities</div><div class="about-point"><span>✓</span> Goal tracking</div><div class="about-point"><span>✓</span> Performance reviews</div><div class="about-point"><span>✓</span> Centralized reports</div></div></div></section>
<section class="section alt" id="features"><div class="wrap"><div class="section-head"><h2>Everything your team needs</h2><p>Useful tools for daily employee management and performance workflows.</p></div><div class="feature-grid"><article class="feature"><div class="ficon">👥</div><h3>Employee Management</h3><p>Keep employee information organized and accessible to authorized users.</p></article><article class="feature"><div class="ficon">✓</div><h3>Tasks & Goals</h3><p>Assign responsibilities and follow the progress of employee goals.</p></article><article class="feature"><div class="ficon">↗</div><h3>Performance & Appraisal</h3><p>Review progress, record feedback and support appraisal discussions.</p></article><article class="feature"><div class="ficon">▤</div><h3>Reports</h3><p>See key summaries that help teams make informed decisions.</p></article><article class="feature"><div class="ficon">🔒</div><h3>Role-Based Access</h3><p>Separate entry points for Admin, Manager and Employee accounts.</p></article><article class="feature"><div class="ficon">☑</div><h3>Work Tracking</h3><p>Keep task status and follow-ups easier to manage.</p></article></div></div></section>
<section class="section alt" id="how-it-works"><div class="wrap"><div class="section-head"><h2>How HRTech works</h2><p>Get to the right workspace in three simple steps.</p></div><div class="workflow-grid"><article class="workflow-card"><div class="workflow-number">01</div><h3>Choose your role</h3><p>Select Admin, Manager or Employee based on the role assigned to your account.</p></article><article class="workflow-card"><div class="workflow-number">02</div><h3>Sign in securely</h3><p>Use your registered email and password. Your account role controls the workspace you can access.</p></article><article class="workflow-card"><div class="workflow-number">03</div><h3>Get work done</h3><p>Open the tools available to you, update tasks, review progress and return to the dashboard whenever needed.</p></article></div></div></section>
<section class="section" id="contact"><div class="wrap"><div class="section-head"><h2>Help & Support</h2><p>Need help using HRTech? Start with these useful tips.</p></div><div class="contact-grid"><article class="contact-card"><h3>Login help</h3><p>Choose the portal matching your account role. If sign-in fails, check your email and password or contact your project administrator.</p></article><article class="contact-card"><h3>Using the dashboard</h3><p>Use Quick Actions to open a feature. Each workspace has a Back to Dashboard option so you can return easily.</p></article><article class="contact-card"><h3>Role access</h3><p>Admin, Manager and Employee accounts have different permissions. Use only the portal assigned to your account.</p></article><article class="contact-card"><h3>Project information</h3><p>HRTech is a centralized employee and performance management project for organizing team activities and progress.</p></article></div><div class="text-center mt-4"><a href="#portals" class="btn btn-primary px-4 py-2">Choose a Portal →</a></div></div></section>
</main>
<footer class="footer"><div class="wrap"><span><strong>HRTech</strong> · Employee & Performance Management</span><span>© 2026 HRTech. All rights reserved.</span></div></footer>
<button class="back-top" type="button" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top">↑ Back to top</button>
</body></html>
