<?php
require_once __DIR__ . '/includes/auth.php';
require_login(['user', 'admin', 'super_admin']);
require_once __DIR__ . '/user/profile.php';
