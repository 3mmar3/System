<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: /dashboard.php");
    exit;
}

require 'db.php';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $site_name = $_POST['site_name'];
    $site_title = $_POST['site_title'];
    $timezone = $_POST['timezone'];
    $shift_reset = isset($_POST['shift_reset']) ? 'on' : 'off';
    $notification_email = $_POST['notification_email'];
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 'on' : 'off';
    $default_shift_hours = $_POST['default_shift_hours'];
    $language = $_POST['language'];

    $logo_path = $settings['site_logo'] ?? null;
    if (!empty($_FILES['site_logo']['name'])) {
        $target_dir = "../Uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $logo_path = $target_dir . basename($_FILES["site_logo"]["name"]);
        move_uploaded_file($_FILES["site_logo"]["tmp_name"], $logo_path);
    }

    $favicon_path = $settings['favicon'] ?? null;
    if (!empty($_FILES['favicon']['name'])) {
        $target_dir = "../Uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $favicon_path = $target_dir . basename($_FILES["favicon"]["name"]);
        move_uploaded_file($_FILES["favicon"]["tmp_name"], $favicon_path);
    }

    $sql = "UPDATE settings SET 
                site_name=?, 
                timezone=?, 
                shift_reset=?, 
                notification_email=?, 
                maintenance_mode=?, 
                default_shift_hours=?, 
                language=?,
                site_title=?"
                . ($logo_path ? ", site_logo=?" : "")
                . ($favicon_path ? ", favicon=?" : "") . " 
            WHERE id=1";

    $stmt = $conn->prepare($sql);
    $params = [$site_name, $timezone, $shift_reset, $notification_email, $maintenance_mode, $default_shift_hours, $language, $site_title];
    if ($logo_path) $params[] = $logo_path;
    if ($favicon_path) $params[] = $favicon_path;
    $stmt->execute($params);

    echo "<script>alert('Settings Updated Successfully.');window.location='settings.php';</script>";
}

// Handle tab addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tab'])) {
    $name = $_POST['tab_name'];
    $icon = $_POST['tab_icon'];
    $url = $_POST['tab_url'];
    $is_external = isset($_POST['is_external']) ? 1 : 0;
    $role = $_POST['tab_role'];
    $parent_id = $_POST['parent_id'] ?: null;
    $display_order = $_POST['display_order'] ?: 0;

    $sql = "INSERT INTO tabs (name, icon, url, is_external, role, parent_id, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $icon, $url, $is_external, $role, $parent_id, $display_order]);

    echo "<script>alert('Tab Added Successfully.');window.location='settings.php';</script>";
}

// Handle tab deletion
if (isset($_GET['delete_tab'])) {
    $tab_id = $_GET['delete_tab'];
    $sql = "DELETE FROM tabs WHERE id = ? OR parent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tab_id, $tab_id]);
    echo "<script>alert('Tab Deleted Successfully.');window.location='settings.php';</script>";
}

$settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$tabs = $conn->query("SELECT * FROM tabs ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$parent_tabs = $conn->query("SELECT id, name FROM tabs WHERE parent_id IS NULL ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - System Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
    <div class="container bg-white p-4 rounded shadow">
        <h3 class="mb-4"><i class="fas fa-cog"></i> System Settings</h3>
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="update_settings" value="1">
            <div class="col-md-6">
                <label>Site Name</label>
                <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Site Title (Tab Title)</label>
                <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Notification Email</label>
                <input type="email" name="notification_email" value="<?= htmlspecialchars($settings['notification_email']) ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Timezone</label>
                <select name="timezone" class="form-select">
                    <option value="Africa/Cairo" <?= $settings['timezone'] == 'Africa/Cairo' ? 'selected' : '' ?>>Africa/Cairo</option>
                    <option value="Asia/Dubai" <?= $settings['timezone'] == 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai</option>
                    <option value="Europe/London" <?= $settings['timezone'] == 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>Default Shift Hours</label>
                <input type="number" name="default_shift_hours" value="<?= (int)$settings['default_shift_hours'] ?>" class="form-control" min="1" max="24">
            </div>
            <div class="col-md-6">
                <label>Language</label>
                <select name="language" class="form-select">
                    <option value="en" <?= $settings['language'] == 'en' ? 'selected' : '' ?>>English</option>
                    <option value="ar" <?= $settings['language'] == 'ar' ? 'selected' : '' ?>>Arabic</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>Upload Site Logo</label>
                <input type="file" name="site_logo" class="form-control">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="<?= $settings['site_logo'] ?>" width="100" class="mt-2 border p-1 rounded">
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label>Upload Favicon</label>
                <input type="file" name="favicon" class="form-control">
                <?php if (!empty($settings['favicon'])): ?>
                    <img src="<?= $settings['favicon'] ?>" width="32" class="mt-2 border p-1 rounded" alt="Favicon">
                <?php endif; ?>
            </div>
            <div class="col-md-3 form-check">
                <input type="checkbox" name="shift_reset" class="form-check-input" <?= $settings['shift_reset'] == 'on' ? 'checked' : '' ?>>
                <label class="form-check-label">Enable Shift Reset Daily</label>
            </div>
            <div class="col-md-3 form-check">
                <input type="checkbox" name="maintenance_mode" class="form-check-input" <?= $settings['maintenance_mode'] == 'on' ? 'checked' : '' ?>>
                <label class="form-check-label">Maintenance Mode</label>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4">Save Settings</button>
            </div>
        </form>

        <hr class="my-5">

        <h3 class="mb-4"><i class="fas fa-list"></i> Manage Sidebar Tabs</h3>
        <form method="POST" class="row g-3 mb-4">
            <input type="hidden" name="add_tab" value="1">
            <div class="col-md-4">
                <label>Tab Name</label>
                <input type="text" name="tab_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Icon (Font Awesome Class)</label>
                <input type="text" name="tab_icon" class="form-control" placeholder="e.g., fas fa-plane">
            </div>
            <div class="col-md-4">
                <label>URL</label>
                <input type="text" name="tab_url" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Role</label>
                <select name="tab_role" class="form-select">
                    <option value="all">All</option>
                    <option value="admin">Admin</option>
                    <option value="accountant">Accountant</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Parent Group</label>
                <select name="parent_id" class="form-select">
                    <option value="">None (Top-Level)</option>
                    <?php foreach ($parent_tabs as $parent): ?>
                        <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Display Order</label>
                <input type="number" name="display_order" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3 form-check d-flex align-items-center">
                <input type="checkbox" name="is_external" class="form-check-input">
                <label class="form-check-label ms-2">External Link</label>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success px-4">Add Tab</button>
            </div>
        </form>

        <h4>Existing Tabs</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Icon</th>
                    <th>URL</th>
                    <th>Role</th>
                    <th>Parent</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tabs as $tab): ?>
                    <tr>
                        <td><?= htmlspecialchars($tab['name']) ?></td>
                        <td><i class="<?= htmlspecialchars($tab['icon']) ?>"></i> <?= htmlspecialchars($tab['icon']) ?></td>
                        <td><?= htmlspecialchars($tab['url']) ?></td>
                        <td><?= htmlspecialchars($tab['role']) ?></td>
                        <td>
                            <?php
                            if ($tab['parent_id']) {
                                $parent = $conn->query("SELECT name FROM tabs WHERE id = ?", [$tab['parent_id']])->fetch(PDO::FETCH_ASSOC);
                                echo htmlspecialchars($parent['name'] ?? 'None');
                            } else {
                                echo 'None';
                            }
                            ?>
                        </td>
                        <td><?= $tab['display_order'] ?></td>
                        <td>
                            <a href="edit_tab.php?id=<?= $tab['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="settings.php?delete_tab=<?= $tab['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tab?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>