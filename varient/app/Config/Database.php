<?php

namespace config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'db',
        'username'     => 'varient',
        'password'     => 'varient_password',
        'database'     => 'varient',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Check if database configuration is missing
        if (empty($this->default['database']) || empty($this->default['username'])) {
            if (strpos($_SERVER['REQUEST_URI'] ?? '', '/install') === false) {
                $this->redirectToInstaller();
            }
        }
    }

    /**
     * Redirects the user to the installation wizard if the application is not configured
     *
     * @return void
     */
    private function redirectToInstaller(): void
    {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $protocol = $isSecure ? 'https://' : 'http://';

        $host = strip_tags($_SERVER['HTTP_HOST'] ?? 'localhost');

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(dirname($scriptName), '/\\') . '/';

        $redirectUrl = $protocol . $host . $baseDir . 'install';

        header('Location: ' . $redirectUrl, true, 302);
        exit();
    }
}
