<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$username = $_SESSION['username'];

// 受信したメッセージを取得
$stmt = $pdo->prepare("SELECT * FROM gs_messages_table WHERE receiver_username = :username ORDER BY created_at DESC");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$received_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 送信したメッセージを取得
$stmt = $pdo->prepare("SELECT * FROM gs_messages_table WHERE sender_username = :username ORDER BY created_at DESC");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$sent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>メッセージ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 60px; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 5px; }
        .received { background-color: #f0f0f0; }
        .sent { background-color: #e6f3ff; text-align: right; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="select.php">ホーム</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">メッセージ</h2>

        <h3>受信したメッセージ</h3>
        <?php foreach ($received_messages as $message): ?>
            <div class="message received">
                <strong>From: <?= h($message['sender_username']) ?></strong>
                <p><?= h($message['message']) ?></p>
                <small><?= h($message['created_at']) ?></small>
            </div>
        <?php endforeach; ?>

        <h3 class="mt-5">送信したメッセージ</h3>
        <?php foreach ($sent_messages as $message): ?>
            <div class="message sent">
                <strong>To: <?= h($message['receiver_username']) ?></strong>
                <p><?= h($message['message']) ?></p>
                <small><?= h($message['created_at']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>