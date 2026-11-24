<?php
date_default_timezone_set('America/Jamaica');

// Temporary sample data. Replace with real database queries later.
$residents = [
    '620100001' => ['first' => 'Jane', 'last' => 'Doe',   'room' => '5C7',  'phone' => '876-111-2222'],
    '620100002' => ['first' => 'John', 'last' => 'Smith', 'room' => '4B12', 'phone' => '876-333-4444'],
    '620100003' => ['first' => 'Amy',  'last' => 'Brown', 'room' => '3C17', 'phone' => '876-555-6666'],
];

$today = date('Y-m-d');
$visits = [
    ['guest' => 'Kyle Brown', 'rid' => '620100001', 'in' => date('Y-m-d H:i', strtotime('-3 days 14:00')), 'out' => date('Y-m-d H:i', strtotime('-3 days 16:00')), 'phone' => '876-777-8888'],
    ['guest' => 'Mia Clarke', 'rid' => '620100002', 'in' => "$today 09:00", 'out' => null, 'phone' => ''],
    ['guest' => 'Tom Reid',   'rid' => '620100001', 'in' => "$today 09:30", 'out' => null, 'phone' => '876-999-0000'],
];

// Removals are kept in the session so you can test without a database.
$removed = $_SESSION['removed'] ?? [];

function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function fullname($r) { return $r['first'] . ' ' . $r['last']; }