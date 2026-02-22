<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Roles & Permissions</title>
<style>
/* ✅ تنسيق كامل مناسب موبايل / ديزاين نظيف / Scroll عصري */
body {
    font-family: Arial, sans-serif;
    margin: 10px;
    background: #f0f2f5;
    color: #333;
    font-size: 14px;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
.container {
    display: flex;
    gap: 15px;
    justify-content: space-between;
    flex-wrap: wrap;
}
.box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 300px;
}
form.box {
    max-width: 360px;
}
input[type="text"] {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}
.permissions-list {
    flex-grow: 1;
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 6px;
    background: #fafafa;
    overflow-y: auto;
    max-height: 180px;
}
.permissions-list label {
    display: block;
    margin-bottom: 4px;
}
button {
    background: #3498db;
    color: white;
    padding: 10px 18px;
    font-size: 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
}
button:hover {
    background: #217dbb;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th, table td {
    padding: 8px 5px;
    border-bottom: 1px solid #eee;
    font-size: 12px;
}
table th {
    background: #3498db;
    color: white;
}
.action-btn {
    margin-right: 4px;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
}
.btn-edit {
    background: #28a745;
    color: white;
}
.btn-delete {
    background: #e74c3c;
    color: white;
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-overlay.show {
    display: flex;
}
.modal {
    background: white;
    padding: 15px;
    border-radius: 10px;
    width: 400px;
    max-width: 95%;
}
.permissions-list-modal {
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 8px;
    border-radius: 6px;
    background: #fafafa;
    margin-top: 8px;
}
.modal-buttons {
    text-align: right;
    margin-top: 15px;
}
.modal-buttons button {
    padding: 6px 12px;
    margin-left: 6px;
}
.permissions-list::-webkit-scrollbar,
.permissions-list-modal::-webkit-scrollbar {
    width: 6px;
}
.permissions-list::-webkit-scrollbar-thumb,
.permissions-list-modal::-webkit-scrollbar-thumb {
    background-color: #3498db;
    border-radius: 6px;
}
@media (max-width: 768px) {
    .container {
        flex-direction: column;
    }
    .box, form.box {
        width: 100%;
    }
    button {
        width: 100%;
    }
}
</style>
</head>
<body>

<h2>Manage Roles & Permissions</h2>
<div class="container">
    <form id="addRoleForm" class="box">
        <label for="roleName">Role Name:</label>
        <input type="text" id="roleName" name="roleName" required placeholder="Enter new role name" />

        <label>Permissions:</label>
        <div class="permissions-list" id="permissionsList"></div>

        <button type="submit">Add Role</button>
    </form>

    <div class="box">
        <table>
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rolesTableBody"></tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal">
        <h3>Edit Role</h3>
        <input type="hidden" id="editRoleId" />
        <label for="editRoleName">Role Name:</label>
        <input type="text" id="editRoleName" />

        <label>Permissions:</label>
        <div class="permissions-list-modal" id="editPermissionsList"></div>

        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            <button type="button" class="btn-save">Save</button>
        </div>
    </div>
</div>

<script>
let allPermissions = [];

function loadPermissions() {
    fetch('get_permissions.php')
        .then(res => res.json())
        .then(data => {
            allPermissions = data;
            const container = document.getElementById('permissionsList');
            container.innerHTML = '';
            data.forEach(perm => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" value="${perm}"> ${perm}`;
                container.appendChild(label);
            });
        });
}

function loadRoles() {
    fetch('get_roles.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('rolesTableBody');
            tbody.innerHTML = '';
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">No roles found.</td></tr>';
                return;
            }
            data.forEach(role => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${role.role_name}</td>
                    <td>${role.permissions}</td>
                    <td>
                        <button class="action-btn btn-edit" onclick="openEditModal(${role.id}, \`${role.role_name}\`, \`${role.permissions}\`)">Edit</button>
                        <button class="action-btn btn-delete" onclick="deleteRole(${role.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

document.getElementById('addRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const roleName = this.roleName.value.trim();
    const permissions = Array.from(this.querySelectorAll('input[type=checkbox]:checked')).map(cb => cb.value);
    fetch('add_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ roleName, permissions })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Role added successfully!');
            this.reset();
            loadRoles();
        } else {
            alert(data.message || 'Failed to add role.');
        }
    });
});

function openEditModal(id, name, permissionsStr) {
    document.getElementById('editRoleId').value = id;
    document.getElementById('editRoleName').value = name;
    const permsArray = permissionsStr.split(',').map(p => p.trim());
    const container = document.getElementById('editPermissionsList');
    container.innerHTML = '';
    allPermissions.forEach(perm => {
        const label = document.createElement('label');
        label.innerHTML = `<input type="checkbox" value="${perm}" ${permsArray.includes(perm) ? 'checked' : ''}> ${perm}`;
        container.appendChild(label);
    });
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

function submitEditRole() {
    const id = parseInt(document.getElementById('editRoleId').value);
    const roleName = document.getElementById('editRoleName').value.trim();
    const permissions = Array.from(document.querySelectorAll('#editPermissionsList input[type=checkbox]:checked')).map(cb => cb.value);
    fetch('edit_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, roleName, permissions })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Role updated successfully');
            closeEditModal();
            loadRoles();
        } else {
            alert(data.message || 'Failed to update role');
        }
    });
}

document.querySelector('.btn-save').addEventListener('click', function(e) {
    e.preventDefault();
    submitEditRole();
});

function deleteRole(id) {
    if (!confirm('Are you sure you want to delete this role?')) return;
    fetch('delete_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Role deleted successfully');
            loadRoles();
        } else {
            alert(data.message || 'Failed to delete role');
        }
    });
}

window.onload = () => {
    loadPermissions();
    loadRoles();
};
</script>
</body>
</html>
