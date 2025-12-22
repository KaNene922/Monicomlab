<?php
// Allow requests from any origin (for testing, you can restrict later)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

if ($action === 'list_processes') {
    $output = [];
    // Only show apps in Console session (user applications)
    exec('tasklist /FO CSV /NH /FI "SESSIONNAME eq Console"', $output, $return_var);

    // Whitelist of allowed applications
// Whitelist of allowed applications (common PC apps)
$allowed = [
    // Browsers
    'chrome.exe',
    'msedge.exe',
    'firefox.exe',
    'opera.exe',

    'cmd.exe',
    'powershell.exe',
    'taskmgr.exe',

    'code.exe',        
    'notepad.exe',
    'notepad++.exe',
    'sublime_text.exe',

    'winword.exe',
    'excel.exe',
    'powerpnt.exe',
    'onenote.exe',
    'outlook.exe',


    'mspaint.exe',
    'photoshop.exe',
    'gimp.exe',


    'vlc.exe',
    'wmplayer.exe',
    'spotify.exe',


    'teams.exe',
    'slack.exe',
    'discord.exe',
    'skype.exe',
    'zoom.exe',

    'calculator.exe',
    'snippingtool.exe',
    'snipandsketch.exe',
    '7zFM.exe',    
    'winrar.exe'
];


    $processes = [];
    foreach ($output as $line) {
        $parts = str_getcsv($line);
        if (count($parts) >= 2) {
            $name = strtolower($parts[0]);
            $pid  = $parts[1];

            // Only include apps in whitelist
            if (in_array($name, $allowed)) {
                $processes[$name] = ['name' => $parts[0], 'pid' => $pid]; // overwrite to avoid duplicates
            }
        }
    }

    echo json_encode([
        "action" => "list_processes",
        "status" => $return_var === 0 ? "SUCCESS" : "FAILED",
        "processes" => array_values($processes)
    ]);
    exit;
}

if ($action === 'kill_process') {
    $process = $_GET['process'] ?? '';
    $output = [];
    exec('taskkill /F /IM ' . escapeshellarg($process) . ' 2>&1', $output, $return_var);

    echo json_encode([
        "action" => "kill_process",
        "process" => $process,
        "status" => $return_var === 0 ? "SUCCESS" : "FAILED",
        "output" => $output
    ]);
    exit;
}
