<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['admin', 'super_admin']);
header('Location: ' . BASE . 'admin/surat-jalan.php');
exit;
