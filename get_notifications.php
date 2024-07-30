<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_username = :username ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':username' => $_SESSION['username']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_username = :username AND is_read = FALSE");
$stmt->execute([':username' => $_SESSION['username']]);
$unread = $stmt->fetchColumn();

echo json_encode([
    'notifications' => $notifications,
    'unread' => $unread
]);