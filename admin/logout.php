<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
logoutAdmin();
setFlash('info', 'You have been logged out of the admin panel.');
redirect('/ecommerce/admin/login.php');
