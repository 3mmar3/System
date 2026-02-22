<!DOCTYPE html> 
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Leave Request</title>
  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #4b5fd3, #5a3b91);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .page-wrapper {
      max-width: 1000px;
      width: 100%;
    }

    h2, h3 {
      color: #fff;
      margin-top: 0;
      text-align: center;
    }

    .container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 40px;
    }

    .form-wrapper,
    .leave-list-wrapper {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      padding: 30px;
      flex: 1 1 450px;
      min-width: 320px;
      color: #fff;
    }

    form select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      background-color: rgba(255, 255, 255, 0.1);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      backdrop-filter: blur(10px);
      appearance: none;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg fill='%23fff' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 18px 18px;
    }

    textarea,
    select {
      background-color: rgba(255,255,255,0.1);
      color: white;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 10px;
      padding: 12px;
    }

    textarea::placeholder,
    select:invalid {
      color: #ffffff;
      opacity: 0.8;
    }

    select option {
      background-color: #1f1f2e;
      color: #fff;
    }

    form button {
      background: linear-gradient(to right, #00c6ff, #0072ff);
      color: white;
      border: none;
      cursor: pointer;
      transition: background 0.3s ease;
      font-size: 16px;
      font-weight: bold;
      padding: 14px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      width: 100%;
      margin-top: 20px;
    }

    form button:hover {
      background: linear-gradient(to right, #00aaff, #005bbb);
      transform: scale(1.02);
    }

    .success-msg {
      color: #4ee44e;
      font-weight: bold;
      text-align: center;
      margin-top: 10px;
      transition: opacity 0.5s ease;
    }

    .leave-list {
      max-height: 600px;
      overflow-y: auto;
    }

    textarea#leaveReason {
      resize: none;
      color: rgba(255, 255, 255, 0.85);
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 10px;
      padding: 12px;
      font-size: 15px;
      font-family: inherit;
      appearance: textfield;
      width: 100%;
    }

    .leave-item {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #ddd;
      padding: 8px 0;
      text-transform: capitalize;
    }

    .leave-status.pending { color: #ffffff; font-weight: bold; }
    .leave-status.approved { color: #00e676; font-weight: bold; }
    .leave-status.rejected { color: #ff6b6b; font-weight: bold; }

    .date-selectors {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
      flex-wrap: wrap;
    }

    @media (max-width: 800px) {
      body {
    padding-top: 80px;
    padding-bottom: 80px;
      }

      .container {
        flex-direction: column;
        align-items: center;
      }

      .form-wrapper,
      .leave-list-wrapper {
        flex: 1 1 100%;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="page-wrapper">
    <h2>Submit Leave Request & View History</h2>
    <div class="container">
      <div class="form-wrapper">
        <form id="leaveForm">
          <select id="leaveType" required>
            <option value="">-- Select Leave Type --</option>
            <option value="weekly">Weekly Leave</option>
            <option value="sick">Sick Leave</option>
            <option value="vacation">Recreational Leave</option>
            <option value="early">Early Leave</option>
          </select>

          <div class="date-selectors">
            <select id="leaveYear" required></select>
            <select id="leaveMonth" required></select>
            <select id="leaveDay" required></select>
          </div>

          <textarea id="leaveReason" placeholder="Reason (optional)"></textarea>
          <button type="submit">Submit Request</button>
          <p class="success-msg" id="successMsg"></p>
        </form>
      </div>

      <div class="leave-list-wrapper">
        <h3>My Leave History</h3>
        <div class="leave-list" id="leaveHistory"></div>
        <div id="pagination" style="text-align:center; margin-top:15px;"></div>
      </div>
    </div>
  </div>

  <audio id="submitSound" src="https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3" preload="auto"></audio>

  <script>
    const leaveForm = document.getElementById('leaveForm');
    const successMsg = document.getElementById('successMsg');
    const leaveHistory = document.getElementById('leaveHistory');
    const leaveYear = document.getElementById('leaveYear');
    const leaveMonth = document.getElementById('leaveMonth');
    const leaveDay = document.getElementById('leaveDay');

    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;
    const currentDay = today.getDate();

    function populateYears() {
      leaveYear.innerHTML = '<option value="">Year</option>';
      for (let y = currentYear; y <= currentYear + 5; y++) {
        leaveYear.innerHTML += `<option value="${y}">${y}</option>`;
      }
    }

    function populateMonths(selectedYear) {
      leaveMonth.innerHTML = '<option value="">Month</option>';
      let startMonth = (selectedYear == currentYear) ? currentMonth : 1;
      for (let m = startMonth; m <= 12; m++) {
        leaveMonth.innerHTML += `<option value="${m}">${m.toString().padStart(2, '0')}</option>`;
      }
    }

    function populateDays(selectedYear, selectedMonth) {
      leaveDay.innerHTML = '<option value="">Day</option>';
      if (!selectedYear || !selectedMonth) return;
      let startDay = (selectedYear == currentYear && selectedMonth == currentMonth) ? currentDay : 1;
      const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
      for (let d = startDay; d <= daysInMonth; d++) {
        leaveDay.innerHTML += `<option value="${d}">${d.toString().padStart(2, '0')}</option>`;
      }
    }

    leaveYear.addEventListener('change', () => {
      const y = parseInt(leaveYear.value);
      if (!y) {
        leaveMonth.innerHTML = '<option value="">Month</option>';
        leaveDay.innerHTML = '<option value="">Day</option>';
        return;
      }
      populateMonths(y);
      leaveMonth.value = "";
      leaveDay.innerHTML = '<option value="">Day</option>';
    });

    leaveMonth.addEventListener('change', () => {
      const y = parseInt(leaveYear.value);
      const m = parseInt(leaveMonth.value);
      if (!y || !m) {
        leaveDay.innerHTML = '<option value="">Day</option>';
        return;
      }
      populateDays(y, m);
      leaveDay.value = "";
    });

    populateYears();

    leaveForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const leaveType = document.getElementById('leaveType').value;
      const year = leaveYear.value;
      const month = leaveMonth.value;
      const day = leaveDay.value;
      if (!leaveType || !year || !month || !day) {
        alert("Please select all required fields.");
        return;
      }

      const leaveDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
      const selectedDate = new Date(leaveDate);
      const now = new Date(); now.setHours(0,0,0,0);
      if (selectedDate < now) {
        alert("Leave date cannot be earlier than today.");
        return;
      }

      const leaveReason = document.getElementById('leaveReason').value.trim();
      try {
        const formData = new FormData();
        formData.append('leave_type', leaveType);
        formData.append('leave_date', leaveDate);
        formData.append('leave_reason', leaveReason);
        const res = await fetch('leave_request_api.php', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });

        const text = await res.text();
        if (res.ok && text === 'OK') {
          document.getElementById('submitSound').play();
          successMsg.textContent = "Leave request submitted!";
          leaveForm.reset();
          leaveMonth.innerHTML = '<option value="">Month</option>';
          leaveDay.innerHTML = '<option value="">Day</option>';
          loadLeaveHistory();
          setTimeout(() => successMsg.textContent = '', 4000);
        } else {
          alert('Error: ' + text);
        }
      } catch (error) {
        alert('Error: ' + error.message);
      }
    });

    async function loadLeaveHistory() {
      try {
        const res = await fetch('leave_request_api.php', {
          method: 'GET',
          credentials: 'include'
        });
        if (!res.ok) throw new Error('Failed to load leave history');
        const data = await res.json();

        if (data.length === 0) {
          leaveHistory.innerHTML = '<p>No previous leave requests.</p>';
          return;
        }

window.leaveData = data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
renderPage(window.leaveData, 1);
      } catch (e) {
        leaveHistory.innerHTML = '<p>Error loading leave history.</p>';
      }
    }

    let currentPage = 1;
    const itemsPerPage = 8;

    function renderPage(data, page) {
      currentPage = page;
      const startIndex = (page - 1) * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;
      const pageItems = data.slice(startIndex, endIndex);

      leaveHistory.innerHTML = '';
      pageItems.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('leave-item');
        const statusClass = item.status || 'pending';
        div.innerHTML = `
          <span>${item.leave_date} - ${capitalize(item.leave_type)}</span>
          <span class="leave-status ${statusClass}">${capitalize(item.status)}</span>
        `;
        leaveHistory.appendChild(div);
      });

      renderPagination(data.length);
    }

    function renderPagination(totalItems) {
      const pageCount = Math.ceil(totalItems / itemsPerPage);
      const paginationDiv = document.getElementById('pagination');
      paginationDiv.innerHTML = '';

      for (let i = 1; i <= pageCount; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.style.margin = '0 5px';
        btn.style.padding = '6px 12px';
        btn.style.borderRadius = '8px';
        btn.style.border = 'none';
        btn.style.cursor = 'pointer';
        btn.style.background = (i === currentPage) ? '#00c6ff' : '#ffffff33';
        btn.style.color = '#fff';
        btn.style.fontWeight = 'bold';

        btn.addEventListener('click', () => {
          renderPage(window.leaveData, i);
        });

        paginationDiv.appendChild(btn);
      }
    }

    function capitalize(str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    loadLeaveHistory();
  </script>
</body>
</html>