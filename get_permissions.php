<?php
$dashboardFile = __DIR__ . '/dashboard.php'; // عدّل المسار حسب مكان الملف

$permissions = [];

if (file_exists($dashboardFile)) {
    $content = file_get_contents($dashboardFile);

    // استخراج كل loadInternalPage('filename.php')
    preg_match_all("/loadInternalPage\\('([^']+)'\\)/", $content, $matches);

    if (!empty($matches[1])) {
        foreach ($matches[1] as $file) {
            // تحويل الملف إلى اسم صلاحية مقروءة
            $name = pathinfo($file, PATHINFO_FILENAME);
            $name = ucwords(str_replace(['_', '-', '.'], ' ', $name));
            $permissions[] = $name;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($permissions);
