<?php
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!in_array($role, ["admin", "manager"], true)) { header("Location: dashboard.php"); exit(); }
require_once __DIR__ . "/database.php";
$filter = trim($_GET["filter"] ?? "all");
$sql = "SELECT g.id, g.goal_title, g.goal_description, g.target_date, g.status, e.full_name, e.department FROM employee_goals g LEFT JOIN employees e ON e.id = g.employee_id";
if ($filter === "progress") { $sql .= " WHERE TRIM(g.status) = 'In Progress'"; }
elseif ($filter === "completed") { $sql .= " WHERE TRIM(g.status) = 'Completed'"; }
else { $filter = "all"; }
$sql .= " ORDER BY g.target_date ASC, g.id DESC";
$result = mysqli_query($conn, $sql);
function h($v) { return htmlspecialchars((string)($v ?? ""), ENT_QUOTES, "UTF-8"); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Goals Summary - HRTech</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="css/style.css"><style>
.hrtech-navbar-actions{flex-wrap:nowrap!important;white-space:nowrap}
.hrtech-navbar-actions .btn{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;font-weight:600}
@media(max-width:520px){.hrtech-navbar-actions{gap:4px!important}.hrtech-navbar-actions .btn{font-size:11px;padding:6px 7px}.navbar-brand{font-size:17px}}
</style>
</head><body class="bg-light"><nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container d-flex align-items-center flex-nowrap gap-2">
    <a class="navbar-brand fw-bold" href="dashboard.php">HRTech</a>
    <div class="hrtech-navbar-actions d-flex align-items-center gap-2 ms-auto flex-nowrap">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">← Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav><main class="container py-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2>Employee Goals</h2><p class="text-muted mb-0">View goal details and current status.</p></div></div><div class="d-flex flex-wrap gap-2 my-3"><a class="btn btn-sm <?php echo $filter === "all" ? "btn-primary" : "btn-outline-primary"; ?>" href="goal_summary.php?filter=all">All Goals</a><a class="btn btn-sm <?php echo $filter === "progress" ? "btn-primary" : "btn-outline-primary"; ?>" href="goal_summary.php?filter=progress">In Progress</a><a class="btn btn-sm <?php echo $filter === "completed" ? "btn-primary" : "btn-outline-primary"; ?>" href="goal_summary.php?filter=completed">Completed</a></div><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Employee</th><th>Department</th><th>Goal</th><th>Description</th><th>Target Date</th><th>Status</th></tr></thead><tbody><?php if ($result && mysqli_num_rows($result) > 0) { while ($row = mysqli_fetch_assoc($result)) { ?><tr><td><?php echo h($row["full_name"]); ?></td><td><?php echo h($row["department"] ?: "—"); ?></td><td class="fw-semibold"><?php echo h($row["goal_title"]); ?></td><td><?php echo h($row["goal_description"] ?: "—"); ?></td><td><?php echo h($row["target_date"] ?: "—"); ?></td><td><span class="badge text-bg-<?php echo trim($row["status"] ?? "") === "Completed" ? "success" : (trim($row["status"] ?? "") === "In Progress" ? "primary" : "secondary"); ?>"><?php echo h($row["status"] ?: "Not Started"); ?></span></td></tr><?php } } else { ?><tr><td colspan="6" class="text-center text-muted py-4">No goals found for this filter.</td></tr><?php } ?></tbody></table></div></div></main>
<style id="hrtech-logout-modal-styles">
.hrtech-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.58);display:none;align-items:center;justify-content:center;padding:20px;z-index:99999}
.hrtech-modal-overlay.is-open{display:flex}
.hrtech-modal{width:min(420px,100%);background:#fff;border-radius:18px;padding:26px;box-shadow:0 24px 70px rgba(0,0,0,.28);font-family:Arial,sans-serif;color:#1f2937}
.hrtech-modal h2{font-size:21px;margin:0 0 10px;font-weight:700}
.hrtech-modal p{font-size:15px;line-height:1.55;color:#64748b;margin:0 0 22px}
.hrtech-modal-actions{display:flex;justify-content:flex-end;gap:10px}
.hrtech-modal-actions button{border:0;border-radius:9px;padding:10px 18px;font-weight:700;cursor:pointer;font-size:14px}
.hrtech-modal-no{background:#eef2f7;color:#334155}
.hrtech-modal-yes{background:#dc3545;color:#fff}
.hrtech-modal-yes:hover{background:#bb2d3b}.hrtech-modal-no:hover{background:#e2e8f0}
body.hrtech-modal-active{overflow:hidden}
</style>
<div class="hrtech-modal-overlay" id="hrtechLogoutModal" role="dialog" aria-modal="true" aria-labelledby="hrtechLogoutTitle" aria-describedby="hrtechLogoutMessage">
 <div class="hrtech-modal">
  <h2 id="hrtechLogoutTitle">Log out?</h2>
  <p id="hrtechLogoutMessage">Are you sure you want to log out of HRTech?</p>
  <div class="hrtech-modal-actions">
   <button type="button" class="hrtech-modal-no" id="hrtechLogoutNo">No, stay</button>
   <button type="button" class="hrtech-modal-yes" id="hrtechLogoutYes">Yes, log out</button>
  </div>
 </div>
</div>
<script id="hrtech-logout-modal-script">
(function(){
 const overlay=document.getElementById('hrtechLogoutModal');
 if(!overlay) return;
 let logoutUrl='';
 function closeModal(){overlay.classList.remove('is-open');document.body.classList.remove('hrtech-modal-active');logoutUrl='';}
 document.addEventListener('click',function(event){
   const link=event.target.closest('a[href]');
   if(!link) return;
   let target;
   try{target=new URL(link.href,window.location.href);}catch(e){return;}
   if(!target.pathname.toLowerCase().endsWith('/logout.php') && target.pathname.toLowerCase()!=='logout.php') return;
   event.preventDefault();logoutUrl=link.href;overlay.classList.add('is-open');document.body.classList.add('hrtech-modal-active');document.getElementById('hrtechLogoutNo').focus();
 });
 document.getElementById('hrtechLogoutNo').addEventListener('click',closeModal);
 document.getElementById('hrtechLogoutYes').addEventListener('click',function(){if(logoutUrl) window.location.href=logoutUrl;});
 overlay.addEventListener('click',function(event){if(event.target===overlay) closeModal();});
 document.addEventListener('keydown',function(event){if(event.key==='Escape' && overlay.classList.contains('is-open')) closeModal();});
})();
</script>

</body></html>
