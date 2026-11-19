<?php
// ── Database Configuration ─────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change if your MySQL user differs
define('DB_PASS', '');           // Change if your MySQL password is set
define('DB_NAME', 'ecommerce_db');
define('DB_CHARSET', 'utf8mb4');

// ── PDO Connection ─────────────────────────────────────────────
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Show friendly error in production; log in development
    error_log('DB Connection Error: ' . $e->getMessage());
    http_response_code(500);
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Error</title>
        <style>
            body { background: #0a0a14; color: #f1f5f9; font-family: system-ui, sans-serif;
                   display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
                   border-radius: 12px; padding: 2rem 3rem; text-align: center; max-width: 500px; }
            h2 { color: #ef4444; margin-bottom: 0.5rem; }
            p  { color: #94a3b8; }
            code { background: rgba(255,255,255,0.05); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>&#9888; Database Connection Failed</h2>
            <p>Could not connect to <code>ecommerce_db</code>. Please verify your MySQL server is running
            and that the credentials in <code>includes/db.php</code> are correct.</p>
            <p style="margin-top:1rem;font-size:0.85rem;color:#64748b;">Import <code>db/ecommerce.sql</code> via phpMyAdmin first.</p>
        </div>
    </body>
    </html>
    ');
}
