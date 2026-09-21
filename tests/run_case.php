<?php
$phone = $argv[1] ?? '';
$_GET['phone'] = $phone;
$_GET['action'] = 'get_chat_history';
require __DIR__ . '/../public/placement_tab.php';
