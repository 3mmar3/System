<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Reports</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f9f9f9;
      padding: 24px;
      line-height: 1.5;
      color: #2c3e50;
    }

    h2, h3 {
      text-align: center;
      margin-bottom: 24px;
      font-weight: 600;
      color: #2c3e50;
    }

    h2 {
      font-size: 28px;
    }

    h3 {
      font-size: 22px;
    }

    .filter-bar {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      background: #fff;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    select, input[type="date"] {
      padding: 10px 16px;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      background: #fff;
      transition: border-color 0.2s ease;
    }

    select:focus, input[type="date"]:focus {
      outline: none;
      border-color: #3498db;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .online-box {
      background: #fff;
      padding: 20px;
      margin-bottom: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      text-align: center;
      transition: transform 0.2s ease;
    }

    .online-box:hover {
      transform: translateY(-4px);
    }

    .online-status {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      margin: 6px;
      font-weight: 500;
      transition: background 0.2s ease;
    }

    .online  { background: #d4edda; color: #155724; }
    .break   { background: #fff3cd; color: #856404; }
    .offline { background: #f8d7da; color: #721c24; }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 24px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 14px;
      text-align: center;
      font-size: 14px;
      border-bottom: 1px solid #eee;
    }

    th {
      background: #3498db;
      color: #fff;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    tr {
      transition: background 0.2s ease;
    }

    tr:nth-child(even) {
      background: #f9f9f9;
    }

    tr:hover {
      background: #f1f5f9;
    }

    .leave-requests table td button {
      padding: 8px 16px;
      margin: 4px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      transition: transform 0.2s ease, background 0.2s ease;
    }

    .leave-requests table td button:hover {
      transform: translateY(-2px);
    }

    .approve { background: #2ecc71; color: #fff; }
    .reject  { background: #e74c3c; color: #fff; }

    .approved-row { background: #e8f5e9 !important; color: #2ecc71 !important; font-weight: 600; }
    .rejected-row { background: #fdecea !important; color: #e74c3c !important; font-weight: 600; }

    .export-btn {
      background: #2c3e50;
      color: #fff;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin: 8px;
      transition: background 0.2s ease, transform 0.2s ease;
    }

    .export-btn:hover {
      background: #34495e;
      transform: translateY(-2px);
    }

    #netTotalRow {
      font-weight: 600;
      background: #ecf0f1;
      color: #2c3e50;
    }

    .button-group {
      display: flex;
      justify-content: center;
      gap: 12px;
    }

    @media (max-width: 768px) {
      body {
        padding: 16px;
      }

      .filter-bar {
        flex-direction: column;
        padding: 12px;
      }

      select, input[type="date"] {
        width: 100%;
        font-size: 14px;
      }

      table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }

      th, td {
        font-size: 12px;
        padding: 10px;
      }

      .online-box {
        padding: 12px;
      }

      .online-status {
        font-size: 12px;
        padding: 6px 12px;
      }

      .export-btn {
        width: 100%;
        padding: 12px;
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {
      h2 {
        font-size: 24px;
      }

      h3 {
        font-size: 18px;
      }

      .leave-requests table td button {
        width: 100%;
        margin: 4px 0;
      }
    }
  </style>
</head>
<body>

<h2>Admin Reports</h2>

<div class="online-box" id="onlineStatus">
  <strong>Live Status</strong><br><br>
</div>

<div class="filter-bar">
  <label for="userFilter">User:</label>
  <select id="userFilter" onchange="loadAttendance()">
    <option value="">-- All --</option>
  </select>

  <label for="dateRange">Date:</label>
  <select id="dateRange" onchange="loadAttendance()">
    <option value="today">Today</option>
    <option value="lastWeek">Last Week</option>
    <option value="thisMonth">This Month</option>
    <option value="thisYear">This Year</option>
    <option value="custom">Custom</option>
  </select>

  <input type="date" id="customDate" style="display:none" onchange="loadAttendance()" />
</div>

<table id="shiftTable">
  <thead>
    <tr>
      <th>Username</th>
      <th>Date</th>
      <th>Start</th>
      <th>End</th>
      <th>Break (min)</th>
      <th>Net Time</th>
    </tr>
  </thead>
  <tbody id="reportBody"></tbody>
  <tfoot>
    <tr id="netTotalRow">
      <td colspan="6" style="text-align: right; padding: 14px;">
        Total Net Time: <span id="totalNetTime">-</span>
      </td>
    </tr>
  </tfoot>
</table>

<div class="leave-requests">
  <h3>Leave Requests</h3>
  <table id="leaveTable">
    <thead>
      <tr>
        <th>User</th>
        <th>Date</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Action By</th>
        <th>Action</th>

      </tr>
    </thead>
    <tbody id="leaveBody"></tbody>
  </table>
</div>


<div class="button-group">
  <button class="export-btn" onclick="exportToExcelFull()">Export All to Excel</button>
  <button class="export-btn" onclick="exportToPDF()">Export All to PDF</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportToExcelFull() {
  const allTables = document.querySelectorAll("table");
  let csv = '';

  allTables.forEach(table => {
    const rows = table.querySelectorAll("tr");
    rows.forEach(row => {
      const cells = row.querySelectorAll("th, td");
      const rowData = Array.from(cells).map(cell => `"${cell.innerText}"`).join(",");
      csv += rowData + "\n";
    });
    csv += "\n\n";
  });

  const blob = new Blob([csv], { type: 'text/csv' });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "full_report.csv";
  link.click();
}

function exportToPDF() {
  const element = document.body.cloneNode(true);
  element.querySelectorAll(".export-btn").forEach(btn => btn.remove());
  html2pdf().from(element).save("full_report.pdf");
}

function loadStatus() {
  const selectedUser = document.getElementById("userFilter").value;

  fetch("get_user_status.php")
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("onlineStatus");
      container.innerHTML = "<strong>Live Status</strong><br><br>";

      const userSelect = document.getElementById("userFilter");
      userSelect.innerHTML = '<option value="">-- All --</option>';

      const order = { online: 0, break: 1, offline: 2 };
      data.sort((a, b) => order[a.status] - order[b.status]);

      data.forEach(user => {
        const div = document.createElement("span");
        div.className = `online-status ${user.status}`;
        div.textContent = user.username.charAt(0).toUpperCase() + user.username.slice(1);

        container.appendChild(div);

        const opt = document.createElement("option");
        opt.value = user.username;
        opt.textContent = user.username.charAt(0).toUpperCase() + user.username.slice(1);
        userSelect.appendChild(opt);
      });

      userSelect.value = selectedUser;
    });
}

function loadAttendance() {
  const username = document.getElementById("userFilter").value;
  const range = document.getElementById("dateRange").value;
  const customDate = document.getElementById("customDate");
  customDate.style.display = range === 'custom' ? 'inline-block' : 'none';
  const selectedDate = customDate.value;

  let url = `get_attendance_data.php?range=${encodeURIComponent(range)}`;
  if (username) url += `&user=${encodeURIComponent(username)}`;
  if (range === 'custom' && selectedDate) url += `&date=${encodeURIComponent(selectedDate)}`;

  fetch(url)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("reportBody");
      tbody.innerHTML = "";
      let totalMinutes = 0;

      const grouped = {};
      data.forEach(row => {
        const d = row.action_time.split(" ")[0];
        const user = row.username;
        grouped[user] = grouped[user] || {};
        grouped[user][d] = grouped[user][d] || {
          start: null, end: null, break_start: null, break_end: null
        };
        const action = row.action_type;
        grouped[user][d][action] = row.action_time;
      });

      for (let user in grouped) {
        for (let date in grouped[user]) {
          const row = grouped[user][date];
          const start = row.start ? new Date(row.start) : null;
          const end = row.end ? new Date(row.end) : null;
          const breakMs = (row.break_start && row.break_end)
            ? new Date(row.break_end) - new Date(row.break_start) : 0;
          const netMs = (start && end) ? (end - start - breakMs) : 0;
          const netH = Math.floor(netMs / 3600000);
          const netM = Math.floor((netMs % 3600000) / 60000);
          const netFormatted = (start && end) ? `${netH}h ${netM}m` : "-";

          totalMinutes += (start && end) ? Math.floor(netMs / 60000) : 0;

          tbody.innerHTML += `
            <tr>
              <td>${user}</td>
              <td>${date}</td>
              <td>${start ? start.toLocaleTimeString() : '-'}</td>
              <td>${end ? end.toLocaleTimeString() : '-'}</td>
              <td>${Math.floor(breakMs / 60000)}</td>
              <td>${netFormatted}</td>
            </tr>`;
        }
      }

      const totalH = Math.floor(totalMinutes / 60);
      const totalM = totalMinutes % 60;
      document.getElementById("totalNetTime").textContent = `${totalH}h ${totalM}m`;

      loadLeaves(username);
    });
}

function updateLeave(id, status, btn) {
  fetch("update_leave_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&status=${status}`
  }).then(() => {
    // احذف الصف من الجدول مباشرة بدل ما تعمل Refresh كله
    const row = btn.closest('tr');
    row.querySelector('td:nth-child(6)').innerHTML = ''; // فاضي بدل الزراير
    row.classList.remove('pending-row');
    row.classList.add(status + '-row');
    row.querySelector('td:nth-child(5)').textContent = '<?php echo $_SESSION["username"]; ?>';
  });
}


function loadLeaves(username = '') {
  let url = "get_leave_requests.php?filter=week";
  if (username) url += `&user=${encodeURIComponent(username)}`;

  fetch(url)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("leaveBody");
      tbody.innerHTML = "";
      data.forEach(req => {
        const row = document.createElement("tr");
        row.className = req.status + '-row';

        const actionButtons = (req.status === 'pending') ? `
            <button class="approve" onclick="updateLeave(${req.id}, 'approved', this)">Approve</button>
            <button class="reject" onclick="updateLeave(${req.id}, 'rejected', this)">Reject</button>
        ` : '';

        row.innerHTML = `
          <td>${req.username}</td>
          <td>${req.leave_date}</td>
          <td>${req.leave_type}</td>
          <td>${req.reason || '-'}</td>
          <td>${req.admin || '-'}</td>
          <td>${actionButtons}</td>
        `;
        tbody.appendChild(row);
      });
    });
}

function updateLeave(id, status) {
  fetch("update_leave_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&status=${status}`
  }).then(res => res.text())
    .then(data => {
      if (data.trim() === "OK") {
        loadLeaves(); // تحديث الجدول بس
      }
    });
}



loadStatus();
loadAttendance();
setInterval(loadStatus, 5000);
</script>

</body>
</html>