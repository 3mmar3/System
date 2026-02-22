<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Management</title>
  <style>
    /* نفس التنسيقات اللي انت أرسلتها */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 20px auto;
      max-width: 900px;
      background: #f0f4f8;
      color: #333;
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
      text-transform: uppercase;
      letter-spacing: 1.2px;
    }
    form {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 30px;
      align-items: center;
      justify-content: center;
    }
    input, select {
      padding: 10px 12px;
      font-size: 16px;
      border-radius: 6px;
      border: 1.5px solid #bbb;
      transition: border-color 0.3s ease;
      min-width: 180px;
      box-sizing: border-box;
    }
    input:focus, select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 6px rgba(52, 152, 219, 0.5);
    }
    select {
      width: 180px;
    }
    #addUserBtn {
      background: #27ae60;
      color: #fff;
      padding: 12px 22px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      text-transform: uppercase;
      box-shadow: 0 4px 8px rgb(39 174 96 / 0.3);
      min-width: 130px;
      transition: background-color 0.3s ease;
    }
    #addUserBtn:hover {
      background: #219150;
    }
    .table-wrapper {
      overflow-x: auto;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
      background: #fff;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 700px;
    }
    thead tr {
      background: linear-gradient(90deg, #2980b9, #3498db);
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      font-size: 14px;
    }
    th, td {
      padding: 14px 18px;
      border-bottom: 1px solid #e1e7ee;
      text-align: left;
    }
    tbody tr {
      background: #fff;
      transition: background-color 0.25s ease;
      cursor: default;
    }
    tbody tr:hover {
      background-color: #eaf4fc;
    }
    button {
      padding: 8px 14px;
      cursor: pointer;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      transition: background-color 0.3s ease;
      min-width: 110px;
    }
    .btn-delete {
      background: #e74c3c;
      color: #fff;
      box-shadow: 0 3px 6px rgb(231 76 60 / 0.5);
    }
    .btn-delete:hover {
      background: #c0392b;
    }
    .btn-reset {
      background: #f39c12;
      color: #fff;
      box-shadow: 0 3px 6px rgb(243 156 18 / 0.5);
    }
    .btn-reset:hover {
      background: #d68910;
    }
    .btn-role {
      background: #3498db;
      color: #fff;
      box-shadow: 0 3px 6px rgb(52 152 219 / 0.6);
    }
    .btn-role:hover {
      background: #217dbb;
    }
    @media (max-width: 600px) {
      form {
        flex-direction: column;
        align-items: stretch;
      }
      input, select, #addUserBtn {
        min-width: 100%;
      }
      table {
        min-width: 100%;
      }
    }
    #modalOverlay, #roleModalOverlay {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background-color: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    #modal, #roleModal {
      background: #fff;
      border-radius: 8px;
      padding: 25px 30px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      animation: slideDown 0.3s ease forwards;
      position: relative;
      min-height: 180px;
      box-sizing: border-box;
    }
    @keyframes slideDown {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    #modal h3, #roleModal h3 {
      margin-top: 0;
      margin-bottom: 18px;
      font-weight: 700;
      color: #2980b9;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    #modal input[type="password"], #roleModal select {
      width: 100%;
      padding: 12px 14px;
      font-size: 16px;
      border-radius: 6px;
      border: 1.5px solid #bbb;
      box-sizing: border-box;
      margin-bottom: 20px;
      transition: border-color 0.3s ease;
    }
    #modal input[type="password"]:focus, #roleModal select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 6px rgba(52, 152, 219, 0.5);
    }
    #modalButtons, #roleModalButtons {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }
    #modalButtons button, #roleModalButtons button {
      min-width: 100px;
      padding: 10px 16px;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    #modalButtons .cancelBtn, #roleModalButtons .cancelBtn {
      background: #bbb;
      color: #333;
    }
    #modalButtons .cancelBtn:hover, #roleModalButtons .cancelBtn:hover {
      background: #999;
    }
    #modalButtons .updateBtn {
      background: #27ae60;
      color: white;
      box-shadow: 0 4px 8px rgba(39,174,96,0.4);
    }
    #modalButtons .updateBtn:hover {
      background: #219150;
    }
    #roleModalButtons .updateBtn {
      background: #2980b9;
      color: white;
      box-shadow: 0 4px 8px rgba(41,128,185,0.5);
    }
    #roleModalButtons .updateBtn:hover {
      background: #1f6fa1;
    }
    #toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #27ae60;
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      box-shadow: 0 4px 10px rgba(39,174,96,0.4);
      font-weight: 600;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.5s ease;
      z-index: 1100;
      user-select: none;
    }
  </style>
</head>
<body>
  <h2>User Management</h2>

  <form id="userForm">
    <input type="text" name="username" placeholder="Username" required autocomplete="off" />
    <input type="password" name="password" placeholder="Password" required autocomplete="off" />
    <select name="role" required>
      <option value="" disabled selected>Select Role</option>
      <!-- Roles will be loaded dynamically -->
    </select>
    <button type="submit" id="addUserBtn">Add User</button>
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Role</th>
          <th>Reset Password</th>
          <th>Delete User</th>
          <th>Edit Role</th>
        </tr>
      </thead>
      <tbody id="usersTableBody"></tbody>
    </table>
  </div>

  <div id="toast">User added successfully!</div>

  <div id="modalOverlay">
    <div id="modal">
      <h3>Reset Password</h3>
      <input type="password" id="newPasswordInput" placeholder="Enter new password" />
      <div id="modalButtons">
        <button class="cancelBtn" type="button">Cancel</button>
        <button class="updateBtn" type="button">Update</button>
      </div>
    </div>
  </div>

  <div id="roleModalOverlay">
    <div id="roleModal">
      <h3>Edit User Role</h3>
      <select id="roleSelect">
        <!-- Roles will be loaded dynamically -->
      </select>
      <div id="roleModalButtons">
        <button class="cancelBtn" type="button">Cancel</button>
        <button class="updateBtn" type="button">Update</button>
      </div>
    </div>
  </div>

  <audio id="successSound" preload="auto" src="https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg"></audio>
  <audio id="errorSound" preload="auto" src="https://actions.google.com/sounds/v1/cartoon/boing.ogg"></audio>

<script>
let currentUserId = null;

function isPasswordStrong(password) {
  return password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password);
}

function showToast(message, type = 'success') {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  if (type === 'error') {
    toast.style.backgroundColor = '#e74c3c';
    toast.style.boxShadow = '0 4px 10px rgba(231,76,60,0.6)';
    playSound('error');
  } else {
    toast.style.backgroundColor = '#27ae60';
    toast.style.boxShadow = '0 4px 10px rgba(39,174,96,0.4)';
    playSound('success');
  }
  toast.style.opacity = "1";
  toast.style.pointerEvents = "auto";

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.pointerEvents = "none";
  }, 3500);
}

function playSound(type) {
  const sound = document.getElementById(type === 'success' ? 'successSound' : 'errorSound');
  if (!sound) return;
  sound.currentTime = 0;
  sound.play().catch(() => {});
}

async function loadRoles() {
  try {
    const res = await fetch('get_roles.php');
    const roles = await res.json();
    const selects = document.querySelectorAll('select[name="role"], #roleSelect');
    selects.forEach(select => {
      select.innerHTML = '<option value="" disabled selected>Select Role</option>';
      roles.forEach(role => {
        const option = document.createElement('option');
        option.value = role.role_name;
        option.textContent = role.role_name.charAt(0).toUpperCase() + role.role_name.slice(1);
        select.appendChild(option);
      });
    });
  } catch {
    alert('Failed to load roles.');
  }
}

function renderUsers() {
  fetch("get_users.php")
    .then(res => res.json())
    .then(users => {
      const rolePriority = {
        admin: 1,
        accountant: 2,
        employee: 3
      };

      users.sort((a, b) => {
        const roleA = rolePriority[a.role.toLowerCase()] ?? 99;
        const roleB = rolePriority[b.role.toLowerCase()] ?? 99;
        return roleA - roleB;
      });

      const tableBody = document.getElementById("usersTableBody");
      tableBody.innerHTML = "";

      users.forEach(user => {
        const tr = document.createElement("tr");

        const tdUsername = document.createElement("td");
        tdUsername.textContent = user.username;
        tr.appendChild(tdUsername);

        const tdRole = document.createElement("td");
        tdRole.textContent = user.role.toLowerCase().replace(/^./, c => c.toUpperCase());
        tr.appendChild(tdRole);

        const tdReset = document.createElement("td");
        const btnReset = document.createElement("button");
        btnReset.textContent = "Reset Password";
        btnReset.className = "btn-reset";
        btnReset.onclick = () => openResetModal(user.id);
        tdReset.appendChild(btnReset);
        tr.appendChild(tdReset);

        const tdDelete = document.createElement("td");
        const btnDelete = document.createElement("button");
        btnDelete.textContent = "Delete";
        btnDelete.className = "btn-delete";
        btnDelete.onclick = () => deleteUser(user.id);
        tdDelete.appendChild(btnDelete);
        tr.appendChild(tdDelete);

        const tdEditRole = document.createElement("td");
        const btnEditRole = document.createElement("button");
        btnEditRole.textContent = "Edit Role";
        btnEditRole.className = "btn-role";
        btnEditRole.onclick = () => openRoleModal(user.id, user.role);
        tdEditRole.appendChild(btnEditRole);
        tr.appendChild(tdEditRole);

        tableBody.appendChild(tr);
      });
    })
    .catch(() => {
      alert("Failed to load users.");
      showToast("Failed to load users.", "error");
    });
}

document.getElementById("userForm").addEventListener("submit", function(e){
  e.preventDefault();
  const formData = new FormData(this);
  const password = formData.get("password");

  if(!isPasswordStrong(password)){
    showToast("Password too weak! Must be 8+ chars, include uppercase & number.", "error");
    return;
  }

  fetch("add_user.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      showToast("User added successfully!");
      this.reset();
      renderUsers();
    } else {
      showToast(data.message || "Failed to add user.", "error");
    }
  })
  .catch(() => {
    showToast("Network error.", "error");
  });
});

function openResetModal(userId){
  currentUserId = userId;
  document.getElementById("newPasswordInput").value = "";
  document.getElementById("modalOverlay").style.display = "flex";
}

document.querySelector("#modalButtons .cancelBtn").addEventListener("click", () => {
  document.getElementById("modalOverlay").style.display = "none";
});

document.querySelector("#modalButtons .updateBtn").addEventListener("click", () => {
  const newPassword = document.getElementById("newPasswordInput").value.trim();
  if(!isPasswordStrong(newPassword)){
    showToast("Password too weak! Must be 8+ chars, include uppercase & number.", "error");
    return;
  }
  fetch("reset_password.php", {
    method: "POST",
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ userId: currentUserId, newPassword })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      showToast("Password updated successfully!");
      document.getElementById("modalOverlay").style.display = "none";
    } else {
      showToast(data.message || "Failed to update password.", "error");
    }
  })
  .catch(() => {
    showToast("Network error.", "error");
  });
});

function deleteUser(userId){
  if(!confirm("Are you sure you want to delete this user?")) return;
  fetch("delete_user.php", {
    method: "POST",
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ userId })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      showToast("User deleted.");
      renderUsers();
    } else {
      showToast(data.message || "Failed to delete user.", "error");
    }
  })
  .catch(() => {
    showToast("Network error.", "error");
  });
}

function openRoleModal(userId, currentRole){
  currentUserId = userId;
  const roleSelect = document.getElementById("roleSelect");
  roleSelect.value = currentRole;
  document.getElementById("roleModalOverlay").style.display = "flex";
}

document.querySelector("#roleModalButtons .cancelBtn").addEventListener("click", () => {
  document.getElementById("roleModalOverlay").style.display = "none";
});

document.querySelector("#roleModalButtons .updateBtn").addEventListener("click", () => {
  const newRole = document.getElementById("roleSelect").value;
  fetch("update_role.php", {
    method: "POST",
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ userId: currentUserId, newRole })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      showToast("User role updated.");
      document.getElementById("roleModalOverlay").style.display = "none";
      renderUsers();
    } else {
      showToast(data.message || "Failed to update role.", "error");
    }
  })
  .catch(() => {
    showToast("Network error.", "error");
  });
});

window.onload = () => {
  loadRoles();
  renderUsers();
};
</script>
</body>
</html>
