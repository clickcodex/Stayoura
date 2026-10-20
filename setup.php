<?php
/**
 * Stayora - Automated Local Environment Setup Script
 * Run: php setup.php
 */

date_default_timezone_set('Asia/Kolkata');

echo "\n=======================================================\n";
echo "            STAYORA - LOCAL SETUP WIZARD              \n";
echo "=======================================================\n\n";

// 1. PHP Version Check
echo "[1/6] Checking PHP version...\n";
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    echo "  [ERROR] PHP 8.1.0 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "  [OK] PHP Version: " . PHP_VERSION . "\n\n";

// 2. Check Required Extensions
echo "[2/6] Checking required PHP extensions...\n";
$requiredExts = ['mysqli', 'pdo_mysql', 'curl', 'json', 'mbstring', 'fileinfo', 'openssl'];
$missingExts = [];
foreach ($requiredExts as $ext) {
    if (!extension_loaded($ext)) {
        $missingExts[] = $ext;
    }
}
if (!empty($missingExts)) {
    echo "  [WARNING] Missing extensions: " . implode(', ', $missingExts) . "\n";
    echo "  Please enable them in your php.ini file (e.g. C:\\xampp\\php\\php.ini).\n\n";
} else {
    echo "  [OK] All required extensions are active.\n\n";
}

// 3. Setup .env file
echo "[3/6] Configuring environment (.env)...\n";
$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

if (!file_exists($envFile)) {
    if (file_exists($envExample)) {
        copy($envExample, $envFile);
        echo "  [CREATED] .env created from .env.example\n";
    } else {
        file_put_contents($envFile, "DB_HOST=localhost\nDB_PORT=3306\nDB_NAME=farmlelo\nDB_USER=root\nDB_PASS=\nAPP_ENV=local\nAPP_DEBUG=true\nAPP_URL=http://localhost:8000\n");
        echo "  [CREATED] Default .env created.\n";
    }
} else {
    echo "  [OK] .env file already exists.\n";
}

// Load .env manually for setup script
$envVars = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $envVars[$key] = $value;
        }
    }
}

$dbHost = $envVars['DB_HOST'] ?? 'localhost';
$dbPort = (int)($envVars['DB_PORT'] ?? 3306);
$dbUser = $envVars['DB_USER'] ?? 'root';
$dbPass = $envVars['DB_PASS'] ?? '';
$dbName = $envVars['DB_NAME'] ?? 'Stayora';

echo "  Database config: Host={$dbHost}:{$dbPort}, User={$dbUser}, DB={$dbName}\n\n";

// 4. Ensure Upload Folders Exist
echo "[4/6] Checking upload & storage directories...\n";
$uploadDirs = [
    __DIR__ . '/assets/images/uploads',
    __DIR__ . '/assets/images/uploads/avatars',
    __DIR__ . '/assets/images/uploads/farmhouses',
    __DIR__ . '/assets/images/uploads/profiles',
    __DIR__ . '/uploads/profiles',
    __DIR__ . '/uploads/farmhouses',
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "  [CREATED] Directory: " . str_replace(__DIR__, '', $dir) . "\n";
        } else {
            echo "  [FAILED] Could not create: " . str_replace(__DIR__, '', $dir) . "\n";
        }
    }
}
echo "  [OK] Upload directories verified.\n\n";

// 5. Database Connection & Schema Setup
echo "[5/6] Checking MySQL database & tables...\n";
if (extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($dbHost, $dbUser, $dbPass, '', $dbPort);
    
    if ($conn->connect_errno) {
        echo "  [ERROR] Cannot connect to MySQL server at {$dbHost}:{$dbPort} - " . $conn->connect_error . "\n";
        echo "  Please make sure MySQL is started in XAMPP Control Panel.\n\n";
    } else {
        // Create database if not exists
        if ($conn->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            echo "  [OK] Database '{$dbName}' is ready.\n";
        }
        
        $conn->select_db($dbName);
        $res = $conn->query("SHOW TABLES");
        $tableCount = $res ? $res->num_rows : 0;
        
        echo "  Found {$tableCount} table(s) in '{$dbName}'.\n";
        
        $sqlFile = __DIR__ . '/database/stayora.sql';
        if ($tableCount === 0 && file_exists($sqlFile)) {
            echo "  [IMPORTING] Importing initial database schema from database/stayora.sql...\n";
            $sqlContent = file_get_contents($sqlFile);
            if ($conn->multi_query($sqlContent)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                echo "  [OK] Database schema imported successfully!\n";
            } else {
                echo "  [WARNING] SQL Import reported: " . $conn->error . "\n";
            }
        } elseif ($tableCount > 0) {
            echo "  [OK] Tables already populated.\n";
        }
        $conn->close();
        echo "\n";
    }
} else {
    echo "  [SKIP] mysqli extension not loaded in CLI. Skipping DB check.\n\n";
}

// 6. Composer Autoload Check
echo "[6/6] Checking Composer autoloader...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "  [OK] Vendor autoloader exists.\n\n";
} else {
    echo "  [WARNING] 'vendor/autoload.php' not found. Run 'composer install' to install dependencies.\n\n";
}

echo "=======================================================\n";
echo "           SETUP COMPLETED SUCCESSFULLY! 🎉           \n";
echo "=======================================================\n\n";
echo "How to start working on Stayora locally:\n\n";
echo "Option 1 (Fastest / Built-in Server):\n";
echo "  Run: composer dev\n";
echo "  Or:  php -S localhost:8000 router.php\n";
echo "  Or:  Double-click 'serve.bat'\n";
echo "  Then open: http://localhost:8000\n\n";
echo "Option 2 (XAMPP Apache):\n";
echo "  Make sure Apache is started in XAMPP Control Panel\n";
echo "  Open: http://localhost/Stayora/\n\n";
