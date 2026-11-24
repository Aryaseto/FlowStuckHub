<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['super_admin']);
header('Location: ' . BASE . 'super_admin/barang.php');
exit;
