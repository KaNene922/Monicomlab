<?php
$cmd = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -ExecutionPolicy Bypass -Command "gps | where {$_.MainWindowTitle} | select Name,MainWindowTitle | ConvertTo-Json"';
$output = shell_exec($cmd);

// DEBUG: tingnan raw output
// echo "<pre>$output</pre>";

$data = json_decode($output, true);

echo "<h3>Open Applications with Windows</h3>";

if (is_array($data)) {
    echo "<ul>";
    foreach ($data as $app) {
        echo "<li><strong>{$app['Name']}</strong>: {$app['MainWindowTitle']}</li>";
    }
    echo "</ul>";
} elseif (!empty($data)) {
    // Single app open
    echo "<ul><li><strong>{$data['Name']}</strong>: {$data['MainWindowTitle']}</li></ul>";
} else {
    echo "⚠ No open windows found or PowerShell command failed.";
}
?>
