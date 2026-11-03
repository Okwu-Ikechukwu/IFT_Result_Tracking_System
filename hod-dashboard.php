<?php require_once __DIR__.'/includes/auth.php'; require_login(['hod']); $pdo=db();$stats=$pdo->query("SELECT COUNT(*) total,SUM(s.stage_name NOT LIKE 'Released%') pending,SUM(s.stage_name LIKE 'Released%') released,SUM(s.stage_name='Sent to Dean') dean FROM result_records r JOIN status_stages s ON s.id=r.current_stage_id")->fetch();$delayed=(int)$pdo->query("SELECT COUNT(*) FROM result_records r JOIN status_stages s ON s.id=r.current_stage_id WHERE s.delay_days>0 AND TIMESTAMPDIFF(DAY,r.current_since,NOW())>s.delay_days")->fetchColumn();$rows=$pdo->query("SELECT r.public_token,r.current_since,c.course_code,c.course_title,c.level,GROUP_CONCAT(l.full_name ORDER BY l.id SEPARATOR ', ') lecturer_name,s.stage_name FROM result_records r JOIN courses c ON c.id=r.course_id JOIN course_lecturers cl ON cl.course_id=c.id JOIN lecturers l ON l.id=cl.lecturer_id JOIN status_stages s ON s.id=r.current_stage_id WHERE s.stage_order<7 GROUP BY r.id ORDER BY r.current_since ASC LIMIT 50")->fetchAll();$pageTitle='HOD Dashboard | IFT';require __DIR__.'/includes/header.php';?>
<style>
/* HOD dashboard header: mobile-only alignment correction */
@media (max-width:760px){
  .portal-header{
    display:grid;
    grid-template-columns:42px minmax(0,1fr) 42px;
    align-items:center;
    justify-content:initial;
    gap:0;
    padding:0 12px;
  }
  .portal-header .hod-menu{
    grid-column:1;
    grid-row:1;
    justify-self:start;
    margin:0;
    width:42px;
    height:42px;
    padding:0;
    display:grid;
    place-items:center;
    line-height:1;
  }
  .portal-header .portal-brand{
    grid-column:2;
    grid-row:1;
    justify-self:center;
    min-width:0;
  }
  .portal-header .portal-brand img{flex:0 0 auto;}
  .portal-header .portal-brand > div{min-width:0;}
  .portal-header .portal-brand strong,
  .portal-header .portal-brand span{
    white-space:nowrap;
  }
  .portal-header .portal-user{
    grid-column:3;
    grid-row:1;
    justify-self:end;
    margin:0;
  }
  .portal-header .portal-user .avatar{
    display:grid;
    margin:0;
  }
}

/* Restore HOD mobile menu toggle without changing dashboard content */
@media (max-width:760px){
  .portal-header .hod-mobile-menu{
    display:none;
    position:absolute;
    top:100%;
    left:12px;
    right:12px;
    z-index:1000;
  }
  .portal-header .hod-mobile-menu.is-open{
    display:flex;
  }
}

</style>
<body class="portal-page"><header class="portal-header"><div class="portal-brand"><img src="assets/naits-logo.png"><div><strong>IFT Result Tracking</strong><span>HOD View-Only Dashboard</span></div></div><button class="portal-menu hod-menu" type="button" aria-expanded="false" aria-controls="hodMobileMenu" aria-label="Open menu">☰</button><div class="portal-user"><span class="avatar">HD</span><div><strong><?=e(current_user()['name'])?></strong><small>View only</small></div><div class="hod-desktop-links"><a href="index.php">Public Site</a><a href="logout.php">Sign Out</a></div></div><div class="hod-mobile-menu" id="hodMobileMenu"><a href="index.php">Public Site</a><a href="logout.php">Sign Out</a></div></header><main class="portal-main hod-main"><div class="page-title"><div><span class="kicker">HOD VIEW</span><h1>Department Result Overview</h1><p>Read-only monitoring of result movement and pending items.</p></div><span class="readonly-badge">VIEW ONLY</span></div><section class="stat-grid"><article class="stat-card"><span>Pending</span><strong><?=e($stats['pending']??0)?></strong><small>Not yet released</small></article><article class="stat-card warning"><span>At Dean</span><strong><?=e($stats['dean']??0)?></strong><small>Awaiting action</small></article><article class="stat-card success"><span>Released</span><strong><?=e($stats['released']??0)?></strong><small>Completed results</small></article><article class="stat-card"><span>Delayed</span><strong><?=e($delayed)?></strong><small>Beyond threshold</small></article></section><section class="panel"><div class="panel-head"><div><span class="kicker">PENDING RESULTS</span><h2>Current movement</h2></div></div><div class="table-wrap"><table><thead><tr><th>Course</th><th>Lecturer</th><th>Level</th><th>Current Stage</th><th>Since</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><strong><?=e($r['course_code'])?></strong><small><?=e($r['course_title'])?></small></td><td><?=e($r['lecturer_name'])?></td><td><?=e($r['level'])?></td><td><span class="table-status <?=e(stage_badge_class($r['stage_name']))?>"><?=e($r['stage_name'])?></span></td><td><?=e(date('d M Y',strtotime($r['current_since'])))?></td></tr><?php endforeach;?></tbody></table></div></section></main>
<script>
(function () {
  var menuButton = document.querySelector('.hod-menu');
  var mobileMenu = document.getElementById('hodMobileMenu');

  if (!menuButton || !mobileMenu) return;

  menuButton.addEventListener('click', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var isOpen = mobileMenu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    menuButton.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
  });

  mobileMenu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      mobileMenu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.setAttribute('aria-label', 'Open menu');
    });
  });

  document.addEventListener('click', function (event) {
    if (!mobileMenu.contains(event.target) && !menuButton.contains(event.target)) {
      mobileMenu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.setAttribute('aria-label', 'Open menu');
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 760) {
      mobileMenu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.setAttribute('aria-label', 'Open menu');
    }
  });
})();
</script>

<?php require __DIR__.'/includes/footer.php'; ?>
