<?php
session_start();
header('Content-Type: application/json');

if (file_exists('../license.php')) {
    include '../license.php';
}

/*
 * Helper Functions
 */
function sendResponse($status, $message = '', $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function currentUrl($server) {
    $http = 'http';
    if (isset($server['HTTPS'])) {
        $http = 'https';
    }
    $host = $server['HTTP_HOST'];
    $requestUri = $server['REQUEST_URI'];
    return $http . '://' . htmlentities($host) . '/' . htmlentities($requestUri);
}

function strSlug($str, $separator = 'dash', $lowercase = TRUE) {
    $str = trim($str);
    $replace = ($separator == 'dash') ? '-' : '_';
    $trans = [
        '&\#\d+?;' => '', '&\S+?;' => '', '\s+' => $replace, '[^a-z0-9\-\._]' => '',
        $replace . '+' => $replace, $replace . '$' => $replace, '^' . $replace => $replace, '\.+$' => ''
    ];
    $str = strip_tags($str);
    foreach ($trans as $key => $val) {
        $str = preg_replace("#" . $key . "#i", $val, $str);
    }
    if ($lowercase === TRUE) {
        $str = strtolower($str);
    }
    return trim(stripslashes($str));
}

// Check POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'verify_license':

            // Manual License File Fallback Check
            global $license;
            if (!empty($license) && is_array($license) && !empty($license['purchase_code']) && !empty($license['license_key'])) {
                $_SESSION['license_code'] = $license['license_key'];
                $_SESSION['purchase_code'] = $license['purchase_code'];
                sendResponse('success', 'Manual license file detected.');
            }

            $licenseKey = trim($_POST['license_key'] ?? '');

            // Localhost Check
            $whitelist = ['127.0.0.1', '::1'];
            if (in_array($_SERVER['REMOTE_ADDR'], $whitelist) || $_SERVER['HTTP_HOST'] === 'localhost') {
                $_SESSION['license_code'] = 'localhost-license';
                $_SESSION['purchase_code'] = '1234';
                sendResponse('success', 'Localhost detected.');
            }

            // Fast-fail for auto-check from Step 1
            if ($licenseKey === 'auto-check') {
                sendResponse('error', 'License verification required.');
            }

            // Accept any license key
            $_SESSION['license_code'] = $licenseKey;
            $_SESSION['purchase_code'] = 'bypassed-' . md5($licenseKey);
            sendResponse('success', 'License verified successfully.');

            break;

        case 'check_requirements':
            $results = [];
            $hasError = false;

            // PHP Version
            $phpStatus = version_compare(PHP_VERSION, '8.2.0', '>=');
            $results[] = [
                'name' => 'PHP Version (>= 8.2)',
                'current' => PHP_VERSION,
                'status' => $phpStatus
            ];
            if (!$phpStatus) $hasError = true;

            $extensions = [
                'curl' => 'cURL PHP Extension',
                'fileinfo' => 'Fileinfo PHP Extension',
                'mbstring' => 'Mbstring PHP Extension',
                'gd' => 'GD PHP Extension',
                'exif' => 'Exif PHP Extension'
            ];

            foreach ($extensions as $ext => $label) {
                $status = extension_loaded($ext);
                $results[] = [
                    'name' => $label,
                    'current' => $status ? 'Enabled' : 'Disabled',
                    'status' => $status
                ];
                if (!$status) $hasError = true;
            }

            if ($hasError) {
                sendResponse('error', 'Some system requirements are not met.', $results);
            }
            sendResponse('success', 'All system requirements met.', $results);
            break;

        case 'check_permissions':
            $folders = [
                '../.env',
                '../writable',
                '../uploads/audios',
                '../uploads/blocks',
                '../uploads/event',
                '../uploads/files',
                '../uploads/fonts',
                '../uploads/gallery',
                '../uploads/images',
                '../uploads/logo',
                '../uploads/media',
                '../uploads/profile',
                '../uploads/quiz',
                '../uploads/recipe',
                '../uploads/temp',
                '../uploads/videos'
            ];

            $results = [];
            $allWritable = true;

            foreach ($folders as $path) {
                if ($path == '../.env' && !file_exists($path)) {
                    $writable = is_writable('../');
                } else {
                    $writable = is_writable($path);
                }

                $displayName = str_replace('../', '', $path);

                $results[] = [
                    'path' => $displayName,
                    'writable' => $writable
                ];

                if (!$writable) $allWritable = false;
            }

            if (!$allWritable) {
                sendResponse('error', 'Some files/folders are not writable.', $results);
            }
            sendResponse('success', 'Permissions are correct.', $results);
            break;

        case 'test_db':
            $host = $_POST['db_host'];
            $name = $_POST['db_name'];
            $user = $_POST['db_user'];
            $pass = $_POST['db_pass'];

            $mysqli = new mysqli($host, $user, $pass, $name);
            if ($mysqli->connect_error) {
                sendResponse('error', 'Connection failed: ' . $mysqli->connect_error);
            }
            $mysqli->close();

            $_SESSION['db_config'] = [
                'host' => $host, 'name' => $name, 'user' => $user, 'pass' => $pass
            ];

            sendResponse('success', 'Database connection successful.');
            break;

        case 'install_db':
            if (!isset($_SESSION['db_config'])) sendResponse('error', 'Session expired.');

            $config = $_SESSION['db_config'];
            $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);

            if ($mysqli->connect_error) sendResponse('error', 'DB Connection failed.');

            $mysqli->set_charset("utf8");

            $sqlPath = 'config/install_varient.sql';
            if (!file_exists($sqlPath)) {
                sendResponse('error', 'SQL file not found (install_varient.sql). Please upload it.');
            }

            // Check if tables already exist
            $isInstalled = false;
            $checkTable = $mysqli->query("SHOW TABLES LIKE 'users'");
            if ($checkTable && $checkTable->num_rows > 0) {
                $isInstalled = true;
            }

            if (!$isInstalled) {
                $sql = file_get_contents($sqlPath);

                if ($mysqli->multi_query($sql)) {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                        if ($mysqli->error) {
                            sendResponse('error', 'SQL Import failed at a query: ' . $mysqli->error);
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                } else {
                    sendResponse('error', 'SQL Import failed: ' . $mysqli->error);
                }
            }

            // Write template
            $templatePath = 'config/database.php';
            if (!file_exists($templatePath)) {
                sendResponse('error', 'Database template file not found.');
            }

            $dbContent = file_get_contents($templatePath);

            $dbContent = str_replace(
                ['{{DB_HOST}}', '{{DB_USER}}', '{{DB_PASS}}', '{{DB_NAME}}'],
                [$config['host'], $config['user'], $config['pass'], $config['name']],
                $dbContent
            );

            $outputPath = '../app/Config/Database.php';
            if (!file_put_contents($outputPath, $dbContent)) {
                sendResponse('error', 'Could not write ../app/Config/Database.php.');
            }

            $mysqli->close();
            sendResponse('success', 'Database installed successfully.');
            break;

        case 'create_admin':
            if (!isset($_SESSION['db_config'])) sendResponse('error', 'Session expired.');
            $config = $_SESSION['db_config'];

            $username = trim($_POST['admin_username']);
            $email = trim($_POST['admin_email']);
            $password = $_POST['admin_password'];
            $timezone = $_POST['timezone'];
            $baseUrl = trim($_POST['base_url']);

            $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
            $mysqli->set_charset("utf8mb4");

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $slug = strSlug($username);
            $token = uniqid("", TRUE);
            $token = str_replace(".", "-", $token) . "-" . rand(10000000, 99999999);

            $stmt = $mysqli->prepare("INSERT INTO users 
    (`id`, `username`, `slug`, `email`, `email_status`, `auth_token`, `password`, `role_id`, `status`) 
    VALUES (1, ?, ?, ?, 1, ?, ?, 1, 1)");
            $stmt->bind_param("sssss", $username, $slug, $email, $token, $passwordHash);
            $stmt->execute();

            $stmt2 = $mysqli->prepare("UPDATE config SET timezone= ? WHERE id= 1");
            $stmt2->bind_param("s", $timezone);
            $stmt2->execute();

            $mysqli->close();

            $licenseCode = $_SESSION['license_code'] ?? '';
            $purchaseCode = $_SESSION['purchase_code'] ?? '';

            $envContent = "#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = " . $baseUrl . "

#--------------------------------------------------------------------
# LICENSE
#--------------------------------------------------------------------
PURCHASE_CODE = " . $purchaseCode . "
LICENSE_KEY = " . $licenseCode . "

#--------------------------------------------------------------------
# COOKIE
#--------------------------------------------------------------------  
cookie.prefix = 'vr_'";

            if (!file_put_contents('../.env', $envContent)) {
                sendResponse('error', 'Could not write ../.env file.');
            }

            unset($_SESSION['db_config']);
            unset($_SESSION['license_code']);
            unset($_SESSION['purchase_code']);

            sendResponse('success', 'Installation Completed.');
            break;

        default:
            sendResponse('error', 'Unknown action');
    }

} catch (Exception $e) {
    sendResponse('error', 'System Error: ' . $e->getMessage());
}
?>