<?php
// attendance_en.php – Smart Shift Tracker (v2.0)
// Requirements: full English labels, calendar at right with no weekday names, styled dropdowns, popup on date click.

session_start();
date_default_timezone_set('Africa/Cairo');
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$today    = date('Y-m-d');
$conn     = new mysqli('localhost', 'u125244766_system', 'Com@1212', 'u125244766_system');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}

$selYear  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$selMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

/**
 * Return shift/break status for a given user & date.
 */
function getStatus(mysqli $c, string $u, string $d): array {
    $q = "SELECT action_type, action_time
          FROM attendance
          WHERE username = ? AND DATE(action_time) = ?
          ORDER BY action_time ASC";
    $s = $c->prepare($q);
    $s->bind_param('ss', $u, $d);
    $s->execute();
    $r = $s->get_result();

    $start = $end = $breakStart = null;
    $breakTotal = 0;
    $breaks = [];
    while ($row = $r->fetch_assoc()) {
        $t = strtotime($row['action_time']);
        switch ($row['action_type']) {
            case 'start':       $start      = $t; break;
            case 'end':         $end        = $t; break;
            case 'break_start': $breakStart = $t; break;
            case 'break_end':
                if ($breakStart) {
                    $breaks[] = ['start' => $breakStart, 'end' => $t];
                    $breakTotal += $t - $breakStart;
                    $breakStart = null;
                }
                break;
        }
    }
    // still on break
    if ($breakStart && !$end) {
        $breakTotal += time() - $breakStart;
        $breaks[] = ['start' => $breakStart, 'end' => null];
    }
    return [
        'start'      => $start,
        'end'        => $end,
        'breakStart' => $breakStart,
        'totalBreak' => $breakTotal,
        'breaks'     => $breaks
    ];
}

// ── AJAX handler for attendance actions ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $action = $_POST['action'] ?? '';
    if (in_array($action, ['start','end','break_start','break_end'], true)) {
        $now = date('Y-m-d H:i:s');
        $s = $conn->prepare('INSERT INTO attendance (username, action_type, action_time) VALUES (?,?,?)');
        $s->bind_param('sss', $username, $action, $now);
        $s->execute();
        $s->close();
    }
    echo json_encode(getStatus($conn, $username, $today));
    exit;
}

// ── AJAX handler for date details ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_details'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $date = $_POST['date'] ?? '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(getStatus($conn, $username, $date));
    } else {
        echo json_encode(['error' => 'Invalid date']);
    }
    exit;
}

// ── Data for page ───────────────────────────────────────────────────────────
$data = getStatus($conn, $username, $today);

// leave requests for month
$leaveRequests = [];
$sql = "SELECT leave_date, status FROM leave_requests WHERE username=? AND YEAR(leave_date)=? AND MONTH(leave_date)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $username, $selYear, $selMonth);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $leaveRequests[$row['leave_date']] = strtolower($row['status']);
}
$stmt->close();

$firstDay      = new DateTime("{$selYear}-{$selMonth}-01");
$daysInMonth   = (int)$firstDay->format('t');
$totalWorked   = 0; // seconds

function jsVal($v) { return is_null($v) ? 'null' : $v; }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shift Tracker System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --ring: 140px;
      --thick: 12px;
      --shiftA: #34d399;
      --shiftB: #059669;
      --breakA: #fde047;
      --breakB: #facc15;
      --bg1: #1e293b;
      --bg2: #3b0764;
      --card-bg: rgba(255,255,255,0.06);
      --card-border: rgba(255,255,255,0.12);
      --text-light: #fff;
      --accent1: #34d399;
      --accent2: #fde047;
    }
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 94vh;
      background: linear-gradient(135deg, #4b5fd3, #5a3b91);
      font-family: 'Inter', sans-serif;
      color: var(--text-light);
      flex-wrap: wrap;
      gap: 20px;
      padding: 20px;
      padding-right: 70px;

    }
    .card, .calendar-card {
      backdrop-filter: blur(20px);
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 24px;
      padding: 40px;
      width: 420px;
      max-width: 100%;
    }
    .calendar-card { margin-left: 0; height: 460px; }
    h1    { font-size: 1.6rem; margin-bottom: 10px; text-align: center; }
    .user { opacity:.85; margin-bottom:20px; font-weight:600; text-align:center; }
    .timers {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 30px;
      margin-bottom: 12px;
    }
    .ring { position:relative; width:var(--ring); height:var(--ring); }
    .ring-bg, .ring-pr { position:absolute; inset:0; border-radius:50%; mask:radial-gradient(farthest-side,transparent calc(100% - var(--thick)),#000 0); }
    .ring-bg { background:rgba(255,255,255,0.08); }
    #shiftPr { background:conic-gradient(var(--shiftA) 0deg,var(--shiftB) 0deg); }
    #breakPr { background:conic-gradient(var(--breakA) 0deg,var(--breakB) 0deg); }
    .inner { position:absolute; inset:var(--thick); border-radius:50%; background:rgba(0,0,0,0.15); display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .inner .time  { font-size:1.3rem; font-weight:700; }
    .inner .label { text-transform:uppercase; opacity:.8; font-size:.75rem; margin-top:5px; }
    .btn-row { display:flex; gap:10px; margin-bottom:20px; }
    .btn { flex:1; padding:12px; border:none; border-radius:28px; font-size:1rem; font-weight:600; cursor:pointer; transition:transform .15s; }
    .btn:active { transform:scale(.97); }
    .start     { background:#10b981; color:#fff; }
    .end       { background:#dc2626; color:#fff; }
    .secondary { background:var(--accent2); color:#000; }
    .disabled  { opacity:.5; cursor:not-allowed; }
    .info-frames { display:flex; justify-content:space-between; margin-bottom:10px; }
    .frame,.net-frame { flex:1; margin:0 5px; padding:12px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); border-radius:12px; backdrop-filter:blur(10px); text-align:center; }
    .frame .label, .net-frame .label { font-size:.75rem; opacity:.8; text-transform:uppercase; }
    .frame .value { font-size:1.2rem; font-weight:700; margin-top:5px; }
    .net-frame .value { font-size:1.3rem; font-weight:700; margin-top:5px; }
    .calendar-selects { display:flex; gap:10px; margin-bottom:20px; justify-content:center; }
    .calendar-selects select {
      padding: 12px 16px;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 12px;
      background-color: rgba(255,255,255,0.2);
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      min-width: 130px;
      appearance: none;
      text-align: center;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg fill='%23fff' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 18px 18px;
    }
    select {
      border-radius: 12px;
      overflow: hidden;
      background-color: rgba(255,255,255,0.1);
      color: #fff;
      padding: 10px 16px;
      border: 1px solid rgba(255,255,255,0.3);
      font-weight: 600;
      font-size: 1rem;
      appearance: none;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg fill='%23fff' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 18px 18px;
    }
    select option {
      background-color: #2a2a3b;
      color: #fff;
      padding: 10px;
    }
    .calendar-selects select:hover { background:rgba(255,255,255,0.2); }
    .calendar-table { width:100%; border-collapse:separate; border-spacing:4px; }
    .calendar-table td { width:14.28%; padding:10px 0; text-align:center; border-radius:8px; font-weight:600; background:rgba(255,255,255,0.05); color:var(--text-light); border:2px solid rgba(255,255,255,0.1); cursor: pointer; }
    .calendar-table td:hover { background:rgba(255,255,255,0.15); }
    .present { background:var(--accent1)!important; border-color:var(--accent1)!important; }
    .absent  { background:#dc2626!important; border-color:#dc2626!important; }
    .leave   { background:var(--accent2)!important; border-color:var(--accent2)!important; }
    .summary {
      margin: 30px auto 0 auto;
      padding: 15px 30px;
      border-radius: 12px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      color: var(--text-light);
      max-width: 380px;
      text-align: center;
    }
    .summary .label { opacity:.8; text-transform:uppercase; font-size:.75rem; }
    .summary .value { font-size:1.2rem; font-weight:700; }
    .summary .green { color:var(--accent1); }
    .summary .red   { color:#dc2626; }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(10px);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-content {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 12px;
      padding: 20px;
      width: 300px;
      max-width: 90%;
      color: var(--text-light);
      position: relative;
      backdrop-filter: blur(20px);
    }
    .modal-content h2 {
      font-size: 1.4rem;
      margin-bottom: 15px;
      text-align: center;
    }
    .modal-content .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
    }
    .modal-content .detail {
      margin: 10px 0;
      font-size: 0.95rem;
    }
    .modal-content .detail span {
      font-weight: 600;
    }
    .modal-content .net-time {
      margin-top: 20px;
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--accent1);
      text-align: center;
    }
@media(max-width:1000px){
  body {
    flex-direction:column;
    padding-bottom: 100px;
  }
  
  @media (min-width: 1000px) {
  html, body {
    overflow: hidden;
  }
  .card,.calendar-card {
    width:90%;
    max-width:420px;
    margin-left:0;
  }
  .calendar-selects {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
  }
  .calendar-selects select {
    flex: 1;
    width: 120px;
    min-width: 100px;
    padding: 10px 12px;
    font-size: 0.9rem;
  }
:root {
    --ring: 120px; /* زيادة حجم الرينج إلى 120px على الموبايل */
  }
  .ring {
    width: var(--ring);
    height: var(--ring);
    min-width: 90px; /* تحديث الحد الأدنى للعرض */
    min-height: 90px;
  }
}
  </style>
</head>
<body>
  <!-- Shift card -->
  <div class="card">
    <h1>Attendance</h1>
    <div class="user"><?php echo htmlspecialchars($username); ?></div>
    <div class="timers">
      <div class="ring">
        <div class="ring-bg"></div>
        <div class="ring-pr" id="shiftPr"></div>
        <div class="inner">
          <div class="time" id="shiftT">00:00:00</div>
          <div class="label">Shift</div>
        </div>
      </div>
      <div class="ring">
        <div class="ring-bg"></div>
        <div class="ring-pr" id="breakPr"></div>
        <div class="inner">
          <div class="time" id="breakT">00:00:00</div>
          <div class="label">Break</div>
        </div>
      </div>
    </div>
    <div class="btn-row">
      <button id="shiftBtn" class="btn start"></button>
      <button id="breakBtn" class="btn secondary"></button>
    </div>
    <div class="info-frames">
      <div class="frame">
        <div class="label">Start Time</div>
        <div class="value" id="startVal">--:--</div>
      </div>
      <div class="frame">
        <div class="label">End Time</div>
        <div class="value" id="endVal">--:--</div>
      </div>
    </div>
    <div class="net-frame">
      <div class="label">Net Time</div>
      <div class="value" id="netVal">00:00:00</div>
    </div>
  </div>

  <!-- Calendar Card -->
  <div class="calendar-card">
    <h1>Calendar</h1>
    <div class="calendar-selects">
      <form method="get">
        <select name="year" onchange="this.form.submit()">
          <?php for($y=2025; $y<=2030; $y++): ?>
            <option value="<?=$y?>" <?php if($y===$selYear) echo 'selected'; ?>><?=$y?></option>
          <?php endfor; ?>
        </select>
        <select name="month" onchange="this.form.submit()">
          <?php
            $months = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
            for($m=1; $m<=12; $m++):
          ?>
            <option value="<?=$m?>" <?php if($m===$selMonth) echo 'selected'; ?>><?=$months[$m-1]?></option>
          <?php endfor; ?>
        </select>
      </form>
    </div>
    <table class="calendar-table" style="border-top-width: 10px; margin-top: -16px;">
      <tr>
        <?php
          for ($day=1; $day<=$daysInMonth; $day++):
            $date = sprintf('%04d-%02d-%02d', $selYear, $selMonth, $day);
            $cls = '';
            $info = getStatus($conn, $username, $date);
            if ($info['start']!==null && $info['end']!==null) {
              $cls = 'present';
              $totalWorked += ($info['end'] - $info['start'] - $info['totalBreak']);
            } elseif (isset($leaveRequests[$date]) && $leaveRequests[$date]==='approved') {
              $cls = 'leave';
            } elseif ($date<$today) {
              $cls = 'absent';
            }
            echo "<td class='$cls' data-date='$date'>$day</td>";
            if (($day%7)===0) echo '</tr><tr>';
          endfor;
          while (($daysInMonth%7)!==0) {
            echo '<td></td>';
            $daysInMonth++;
          }
        ?>
      </tr>
    </table>
    <div style="text-align:center; font-size: 0.9rem; margin-top: 0px; margin-bottom: -20px; padding-top: 10px;">
      🟢 Present | 🟡 Excused Absence | 🔴 Unexcused Absence
    </div>
    <div class="summary" style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
      <?php
        $required = 8 * ($daysInMonth - 4);
        $workedH  = round($totalWorked / 3600, 2);
        $clsW     = $workedH >= $required ? 'green' : 'red';
      ?>
      <div>
        <div class="label">Total working hours</div>
        <div class="value <?=$clsW?>"><?=$workedH?> H</div>
      </div>
      <div>
        <div class="label">Required hours</div>
        <div class="value"><?=$required?> H</div>
      </div>
    </div>
  </div>

  <!-- Modal Popup -->
  <div id="dateModal" class="modal">
    <div class="modal-content">
      <span class="close">×</span>
      <h2 id="modalDate"></h2>
      <div id="modalDetails"></div>
      <div id="modalNetTime" class="net-time"></div>
    </div>
  </div>

  <audio id="clickSound" src="https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3" preload="auto"></audio>
  <script>
    const shiftPr    = document.getElementById('shiftPr'),
          breakPr    = document.getElementById('breakPr'),
          shiftT     = document.getElementById('shiftT'),
          breakT     = document.getElementById('breakT'),
          startVal   = document.getElementById('startVal'),
          endVal     = document.getElementById('endVal'),
          netVal     = document.getElementById('netVal'),
          shiftBtn   = document.getElementById('shiftBtn'),
          breakBtn   = document.getElementById('breakBtn'),
          clickSound = document.getElementById('clickSound'),
          modal      = document.getElementById('dateModal'),
          modalDate  = document.getElementById('modalDate'),
          modalDetails = document.getElementById('modalDetails'),
          modalNetTime = document.getElementById('modalNetTime'),
          closeModal = document.querySelector('.modal .close');

    let shiftStart = <?=jsVal($data['start'])?>,
        shiftEnd   = <?=jsVal($data['end'])?>,
        breakStart = <?=jsVal($data['breakStart'])?>,
        totalBreak = <?=jsVal($data['totalBreak'])?>;

    const H         = 3600,
          shiftGoal = 8 * H,
          breakGoal = 1 * H;

    const pad = s => String(s).padStart(2,'0'),
          fmt = s => `${pad(s/3600|0)}:${pad(s%3600/60|0)}:${pad(s%60)}`;

    function ring(el, perc, a, b){
      const angle = perc * 360;
      el.style.background = `conic-gradient(${a} 0deg ${angle}deg, transparent ${angle}deg 360deg)`;
    }

    function setButtons(){
      if (!shiftStart)           configBtn(shiftBtn,'Start Shift','start',()=>send('start'));
      else if (!shiftEnd)        configBtn(shiftBtn,'End Shift','end',()=>send('end'));
      else                       configBtn(shiftBtn,'Shift Done','disabled',null);
      if (!shiftStart||shiftEnd) configBtn(breakBtn,'Break Disabled','disabled',null);
      else if (!breakStart)      configBtn(breakBtn,'Start Break','secondary',()=>send('break_start'));
      else                       configBtn(breakBtn,'End Break','secondary',()=>send('break_end'));
    }

    function render(){
      const now      = Math.floor(Date.now()/1000);
      const worked   = shiftStart
                      ? ((shiftEnd||now) - shiftStart)
                        - (totalBreak + (breakStart&&!shiftEnd?(now-breakStart):0))
                      : 0;
      const elapsed  = Math.max(0, worked);
      const brkElap  = totalBreak + (breakStart&&!shiftEnd?(now-breakStart):0);

      shiftT.textContent = fmt(elapsed);
      breakT.textContent = fmt(brkElap);

      const root = getComputedStyle(document.documentElement);
      ring(shiftPr, Math.min(elapsed/shiftGoal,1),
           root.getPropertyValue('--shiftA').trim(),
           root.getPropertyValue('--shiftB').trim());
      ring(breakPr, Math.min(brkElap/breakGoal,1),
           root.getPropertyValue('--breakA').trim(),
           root.getPropertyValue('--breakB').trim());

      startVal.textContent = shiftStart
        ? new Date(shiftStart*1000).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})
        : '--:--';
      endVal.textContent   = shiftEnd
        ? new Date(shiftEnd*1000).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})
        : '--:--';
      netVal.textContent   = fmt(elapsed);

      setButtons();
    }

    function configBtn(el,text,cls,fn){
      el.textContent = text;
      el.className   = `btn ${cls}`;
      el.disabled    = cls==='disabled';
      el.onclick     = fn;
    }

    function send(action=''){
      if (action) {
        clickSound.currentTime = 0;
        clickSound.play().catch(()=>{});
      }
      fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        cache: 'no-store',
        body: new URLSearchParams(action?{ action }:{})
      })
      .then(r => r.json())
      .then(d => {
        shiftStart = d.start;
        shiftEnd   = d.end;
        breakStart = d.breakStart;
        totalBreak = d.totalBreak;
        render();
      });
    }

    // Handle date click and show modal
    document.querySelectorAll('.calendar-table td[data-date]').forEach(td => {
      td.addEventListener('click', () => {
        const date = td.getAttribute('data-date');
        fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          cache: 'no-store',
          body: new URLSearchParams({ get_details: '1', date })
        })
        .then(r => r.json())
        .then(d => {
          modalDate.textContent = new Date(date).toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
          });
          let details = '';
          let netTime = '';
          const isMobile = window.innerWidth <= 1000;
          const timeOptions = isMobile
            ? { hour: '2-digit', minute: '2-digit', hour12: true }
            : { hour: '2-digit', minute: '2-digit', hour12: false };
          if (d.error) {
            details = '<div class="detail">No data available for this date.</div>';
          } else {
            const start = d.start
              ? new Date(d.start*1000).toLocaleTimeString([], timeOptions)
              : '--:--';
            const end = d.end
              ? new Date(d.end*1000).toLocaleTimeString([], timeOptions)
              : '--:--';
            const shiftDuration = d.start && d.end
              ? fmt(d.end - d.start - d.totalBreak)
              : '00:00:00';
            const totalBreak = fmt(d.totalBreak);
            details = `
              <div class="detail"><span>Shift Start:</span> ${start}</div>
              <div class="detail"><span>Shift End:</span> ${end}</div>
              <div class="detail"><span>Shift Duration:</span> ${shiftDuration}</div>
              <div class="detail"><span>Total Break:</span> ${totalBreak}</div>
            `;
            netTime = `Net Time: ${shiftDuration}`;
            if (d.breaks && d.breaks.length > 0) {
              details += '<div class="detail"><span>Breaks:</span></div>';
              d.breaks.forEach((b, i) => {
                const bStart = b.start
                  ? new Date(b.start*1000).toLocaleTimeString([], timeOptions)
                  : '--:--';
                const bEnd = b.end
                  ? new Date(b.end*1000).toLocaleTimeString([], timeOptions)
                  : 'Ongoing';
                details += `<div class="detail">Break ${i+1}: ${bStart} - ${bEnd}</div>`;
              });
            }
          }
          modalDetails.innerHTML = details;
          modalNetTime.textContent = netTime;
          modal.style.display = 'flex';
        });
      });
    });

    // Close modal
    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    // Close modal when clicking outside
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });

    render();
    setInterval(render, 1000);
    setInterval(()=>send(), 5000);
  </script>
</body>
</html> 
<?php $conn->close(); ?>