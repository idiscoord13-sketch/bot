<?php
$file = 'index.php';
$search = 'GP_SUPPORT';
$lines = file($file);
echo "<h3>🔍 Extracting Logic from index.php</h3>";

foreach ($lines as $line_num => $line) {
    if (strpos($line, $search) !== false) {
        echo "<b>Line " . ($line_num + 1) . ":</b><br>";
        echo "<pre style='background:#f4f4f4; padding:10px;'>";
        // نمایش ۵ خط قبل و بعد برای درک منطق
        for ($i = $line_num - 10; $i <= $line_num + 10; $i++) {
            if (isset($lines[$i])) {
                echo ($i == $line_num ? "👉 " : "   ") . htmlspecialchars($lines[$i]);
            }
        }
        echo "</pre><hr>";
    }
}