<?php
date_default_timezone_set('Africa/Cairo');
file_put_contents('cron_log.txt', "Cron Executed at: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
echo "✅ Cron Executed at: " . date('Y-m-d H:i:s');
?>