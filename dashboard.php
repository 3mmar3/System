<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$role = strtolower($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Dashboard - Lavida Travel</title>
<link rel="icon" type="image/png" href="Logo-10K-Px.png">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<style>
  * { box-sizing: border-box; }
  body, html {
    margin: 0; padding: 0;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    height: 100vh;
    overflow: hidden;
    scroll-behavior: smooth;
    overflow-x: hidden !important;
  }
  .container { display: flex; height: 100vh; overflow-x: hidden; }
  .sidebar {
    background: linear-gradient(180deg, #1e3a8a 0%, #1e293b 100%);
    color: white;
    width: 280px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    padding: 20px 15px;
    transition: width 0.3s ease, transform 0.3s ease;
    position: relative;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #60a5fa #1e293b;
    box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    max-height: 100vh;
    overflow-x: hidden !important;
  }
  .sidebar::-webkit-scrollbar { width: 6px; }
  .sidebar::-webkit-scrollbar-thumb {
    background-color: #60a5fa;
    border-radius: 10px;
  }
  .sidebar::-webkit-scrollbar-track { background-color: #1e293b; }
  .sidebar.collapsed { width: 60px; }
  .logo-link {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 7px;
    height: 50px;
  }
  .logo {
    transition: opacity 0.3s ease, width 0.3s ease;
    max-height: 100px;
  }
  .full-logo { width: 190px; }
  .mini-logo { width: 30px; display: none; }
  #toggleBtn {
    position: absolute;
    top: 0px;
    right: -30px;
    background: #60a5fa;
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 18px;
    border-radius: 30%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
  }
  #toggleBtn:hover { background: #3b82f6; }
  .sidebar button, .sidebar .group-btn {
    background: none;
    border: none;
    color: white;
    padding: 12px 15px;
    text-align: left;
    width: 100%;
    cursor: pointer;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
    border-left: 4px solid transparent;
    transition: background 0.3s, border-left-color 0.3s, transform 0.2s;
    border-radius: 6px;
  }
  .sidebar button:hover, .sidebar .group-btn:hover {
    background-color: rgba(255,255,255,0.1);
    border-left-color: #60a5fa;
    transform: translateX(5px);
  }
  .sidebar.collapsed button span.label,
  .sidebar.collapsed .group-btn span.label {
    display: none;
  }
  .group-btn {
    font-weight: 600;
    justify-content: flex-start;
    padding-left: 15px;
  }
  .group-btn .arrow {
    margin-left: auto;
    font-size: 12px;
    transition: transform 0.3s ease;
    color: #bfdbfe;
  }
  .group-btn.open .arrow {
    transform: rotate(90deg);
  }
  .group-content {
    display: none;
    flex-direction: column;
    margin-left: 20px;
    margin-top: 5px;
  }
  .group-content button {
    font-size: 14px;
    padding-left: 25px;
  }
  .group-btn.open + .group-content {
    display: flex;
  }
  .sidebar.collapsed .group-content {
    display: none !important;
  }
  .external-icon {
    margin-left: auto;
    font-size: 12px;
    opacity: 0.6;
  }
  .divider {
    border-top: 1px solid rgba(255,255,255,0.2);
    margin: 15px 0;
  }
  .main {
    flex-grow: 1;
    overflow: hidden;
    background: #ffffff;
    transition: transform 0.3s ease;
  }
  iframe#frame {
    width: 100%;
    height: 100vh;
    border: none;
    display: block;
  }
  @media (max-width: 768px) {
    .container {
      flex-direction: column;
      height: 100vh;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -280px;
      z-index: 9999;
      height: 100vh;
      width: 280px;
      transition: left 0.3s ease;
      padding: 60px 15px 100px 15px;
      box-shadow: none;
      overflow-y: auto;
    }
    .sidebar.open {
      left: 0;
      box-shadow: 4px 0 12px rgba(0,0,0,0.3);
    }
    #toggleBtn {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 10000;
      background: linear-gradient(45deg, #1e3a8a, #60a5fa);
      border: 2px solid #ffffff;
      width: 48px;
      height: 48px;
      font-size: 24px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, background 0.3s ease;
    }
    #toggleBtn:hover {
      background: linear-gradient(45deg, #1e293b, #3b82f6);
      transform: scale(1.1);
    }
    #toggleBtn i {
      transition: transform 0.3s ease;
    }
    .sidebar.open + #toggleBtn i.fa-bars {
      transform: rotate(90deg);
    }
    .main {
      height: calc(100vh - 50px);
      margin-top: 50px;
      background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transform: translateX(0);
      transition: transform 0.3s ease;
    }
    .sidebar.open ~ .main {
      transform: translateX(280px);
    }
    iframe#frame {
      height: 100%;
      border-radius: 12px;
    }
  }
  #eventNotificationsContainer {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
  }
</style>
</head>
<body>
<div class="container">
  <button id="toggleBtn" title="Toggle Sidebar"><i class="fas fa-bars"></i></button>
  <nav class="sidebar" id="sidebar" aria-label="Sidebar navigation">
    <a href="https://employee.lavida-travel.com/dashboard.php" class="logo-link" aria-label="Go to Dashboard">
      <img src="Logo-Site-Png22.png" alt="Full Logo" class="logo full-logo" />
      <img src="Logo-Icon.png" alt="Mini Logo" class="logo mini-logo" />
    </a>
    <?php if ($role === 'accountant'): ?>
      <button onclick="loadInternalPage('sonn.php')"><i class="fas fa-money-bill"></i> <span class="label">Financial Accounts</span></button>
    <?php else: ?>
      <button onclick="loadInternalPage('attendance.php')"><i class="fas fa-calendar-check"></i> <span class="label">Shift Attendance</span></button>
      <button onclick="loadInternalPage('leave_request.php')"><i class="fas fa-file-alt"></i> <span class="label">Leave Requests</span></button>
      <button onclick="loadInternalPage('my-reservations.php')"><i class="fas fa-book"></i> <span class="label">My Reservations</span></button>
      <button onclick="loadInternalPage('to-do-list.php')"><i class="fas fa-list-check"></i> <span class="label">To Do List</span></button>
      <button onclick="loadInternalPage('mailbox/mailbox.php')"><i class="fas fa-envelope"></i> <span class="label">Webmail</span></button>

      <div class="divider"></div>
      <button onclick="window.open('https://lavida-travel.com', '_blank')"><i class="fas fa-globe"></i> <span class="label">Lavida Travel</span> <span class="external-icon"><i class="fas fa-external-link-alt"></i></span></button>
      <button onclick="window.open('https://app.respond.io/space', '_blank')"><i class="fas fa-comment"></i> <span class="label">Respond</span> <span class="external-icon"><i class="fas fa-external-link-alt"></i></span></button>
      <button onclick="window.open('https://occ.ras.yeastar.com', '_blank')"><i class="fas fa-phone"></i> <span class="label">Calls</span> <span class="external-icon"><i class="fas fa-external-link-alt"></i></span></button>
      <button onclick="window.open('https://mail.hostinger.com/', '_blank')"><i class="fas fa-envelope"></i> <span class="label">Mails</span> <span class="external-icon"><i class="fas fa-external-link-alt"></i></span></button>
      <button onclick="window.open('https://sadadpay.net/dashboard', '_blank')"><i class="fas fa-credit-card"></i> <span class="label">Sadad Pay</span> <span class="external-icon"><i class="fas fa-external-link-alt"></i></span></button>
      <div class="group-btn" tabindex="0"><i class="fas fa-plane"></i> <span class="label">Flight</span><span class="arrow"><i class="fas fa-chevron-right"></i></span></div>
      <div class="group-content">
        <button onclick="window.open('https://www.jazeeraairways.com/en-eg?', '_blank')"><i class="fas fa-plane-departure"></i> Jazeera Airways</button>
        <button onclick="window.open('https://www.saudia.com/ar-KW', '_blank')"><i class="fas fa-plane-departure"></i> Saudia</button>
        <button onclick="window.open('https://www.airarabia.com/en', '_blank')"><i class="fas fa-plane-departure"></i> Air Arabia</button>
        <button onclick="window.open('https://www.etihad.com/en-kw/', '_blank')"><i class="fas fa-plane-departure"></i> Etihad Airways</button>
        <button onclick="window.open('https://www.kuwaitairways.com/en', '_blank')"><i class="fas fa-plane-departure"></i> Kuwait Airways</button>
      </div>
      <div class="group-btn" tabindex="0"><i class="fas fa-hotel"></i> <span class="label">Hotels</span><span class="arrow"><i class="fas fa-chevron-right"></i></span></div>
      <div class="group-content">
        <button onclick="window.open('https://smileholidays.info/', '_blank')"><i class="fas fa-bed"></i> Smile Old</button>
        <button onclick="window.open('https://www.smileholidays.com/login/?returnURL=%2Fdefault.aspx', '_blank')"><i class="fas fa-bed"></i> Smile New</button>
        <button onclick="window.open('https://www.magicholidays.net/', '_blank')"><i class="fas fa-bed"></i> Magic Holidays</button>
      </div>
      <div class="group-btn" tabindex="0"><i class="fas fa-ellipsis-h"></i> <span class="label">Other</span><span class="arrow"><i class="fas fa-chevron-right"></i></span></div>
      <div class="group-content">
        <button onclick="window.open('https://www.tboholidays.com/NewDefault.aspx', '_blank')"><i class="fas fa-globe"></i> TBO</button>
      </div>
      <?php if ($role === 'admin'): ?>
        <div class="divider"></div>
        <button onclick="loadInternalPage('soon.php')"><i class="fas fa-money-bill"></i> <span class="label">Financial Accounts</span></button>
        <button onclick="loadInternalPage('admin_reports.php')"><i class="fas fa-chart-bar"></i> <span class="label">Admin Reports</span></button>
        <button onclick="loadInternalPage('user-management.php')"><i class="fas fa-users"></i> <span class="label">User Management</span></button>
        <button onclick="loadInternalPage('roles.php')"><i class="fas fa-cogs"></i> <span class="label">Roles Management</span></button>
        <button onclick="loadInternalPage('soon.php')"><i class="fas fa-cog"></i> <span class="label">Settings</span></button>
      <?php endif; ?>
    <?php endif; ?>
    <div class="divider"></div>
    <button onclick="logout()"><i class="fas fa-sign-out-alt"></i> <span class="label">Logout</span></button>
  </nav>
  <main class="main"><iframe id="frame" src=""></iframe></main>
</div>
<div id="eventNotificationsContainer"></div>
<script>
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleBtn');
  toggleBtn.addEventListener('click', () => {
    if (window.innerWidth > 768) {
      sidebar.classList.toggle('collapsed');
    } else {
      sidebar.classList.toggle('open');
    }
  });
  document.querySelectorAll('.group-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const currentScroll = sidebar.scrollTop;
      const expanded = btn.classList.toggle('open');
      btn.setAttribute('aria-expanded', expanded);
      sidebar.scrollTop = currentScroll;
    });
    btn.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        btn.click();
      }
    });
  });
  function loadInternalPage(url) {
    document.getElementById('frame').src = url;
    if (window.innerWidth <= 768) sidebar.classList.remove('open');
  }
  function logout() {
    window.location.href = "logout.php";
  }
  let lastCheckTime = new Date().toISOString();
  let shownEvents = new Set(JSON.parse(sessionStorage.getItem("shownEvents") || "[]"));
  function checkForNewEvents() {
    fetch(`check_new_events.php?last_check=${encodeURIComponent(lastCheckTime)}`)
      .then(res => res.json())
      .then(data => {
        if (data.new_events && data.new_events.length > 0) {
          lastCheckTime = data.new_events[data.new_events.length - 1].time;
          data.new_events.forEach(ev => {
            const uniqueKey = ev.user + "_" + ev.type + "_" + ev.time;
            if (!shownEvents.has(uniqueKey)) {
              shownEvents.add(uniqueKey);
              showNotification(ev);
            }
          });
          sessionStorage.setItem("shownEvents", JSON.stringify([...shownEvents]));
        }
      });
  }
  function showNotification(ev) {
    const container = document.getElementById("eventNotificationsContainer");
    const notif = document.createElement("div");
    notif.style.cssText = `
      background-color: rgba(0,0,0,0.8);
      color: white;
      padding: 12px 16px;
      border-radius: 8px;
      margin-top: 10px;
      box-shadow: 0 0 8px rgba(0,0,0,0.3);
      font-size: 14px;
      cursor: pointer;
      max-width: 280px;
      white-space: pre-line;
      transition: transform 0.3s ease;
    `;
    notif.addEventListener('mouseenter', () => notif.style.transform = 'translateY(-2px)');
    notif.addEventListener('mouseleave', () => notif.style.transform = 'translateY(0)');
    const timeStr = new Date(ev.time).toLocaleString();
    let typeText = '';
    switch(ev.type) {
      case 'start_work': typeText = 'Started Work'; break;
      case 'end_work': typeText = 'Ended Work'; break;
      case 'break_start': typeText = 'Started Break'; break;
      case 'break_end': typeText = 'Ended Break'; break;
      case 'leave_request': typeText = 'Sent Leave Request'; break;
      default: typeText = ev.type;
    }
    notif.textContent = `${ev.user} ${typeText} at ${timeStr}`;
    notif.onclick = () => container.removeChild(notif);
    container.appendChild(notif);
    const audio = new Audio('https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3');
    audio.play().catch(() => {});
    setTimeout(() => {
      if (notif.parentNode) container.removeChild(notif);
    }, 8000);
  }
  // AJAX function to check user status
  function checkUserStatus() {
    fetch('check_user_status.php')
      .then(res => res.json())
      .then(data => {
        if (!data.is_valid) {
          // User is deleted or session is invalid
          alert('Your session is no longer valid. Please log in again.');
          window.location.href = 'logout.php';
        } else if (data.role !== '<?php echo $role; ?>') {
          // Role has changed
          alert('Your role has been updated. The page will reload.');
          window.location.reload();
        }
      })
      .catch(error => {
        console.error('Error checking user status:', error);
      });
  }
  window.onload = () => {
    const role = "<?php echo $role; ?>";
    if (role === "accountant") loadInternalPage('financial.accounts.php');
    else loadInternalPage('welcome.php');
    checkForNewEvents();
    setInterval(checkForNewEvents, 10000);
    // Check user status every 10 seconds
    setInterval(checkUserStatus, 10000);
  };
</script>
</body>
</html>