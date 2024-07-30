<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE recipient_username = :username");
$stmt->execute([':username' => $_SESSION['username']]);

header('Location: select.php');