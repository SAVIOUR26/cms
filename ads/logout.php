<?php
require_once __DIR__ . '/shared/auth.php';
ads_logout();
header('Location: /login.php');
exit;
