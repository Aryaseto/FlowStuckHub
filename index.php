<?php
require_once __DIR__ . '/includes/auth.php';
header('Location: ' . (user() ? home_for(user()['role']) : BASE . 'login.php'));
