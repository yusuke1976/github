<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$username = $_SESSION['username'];

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';


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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('./img/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Noto Sans JP', sans-serif;
            font-size: 16px;
        }

        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;   /* 真円 */
            object-fit: cover;    /* 枠に合わせて切り取る */
        }

        .navbar {
            background-color: #ff9800;
            padding: 15px 15px;
        }
        
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 350;
            font-size: 1.2rem;
            margin-left: 10px; 
        }

        .navbar-brand:hover {
            text-decoration: underline;
        }        .message { margin-bottom: 20px; padding: 10px; border-radius: 5px; }
        .received { background-color: #f0f0f0; }
        .sent { background-color: #e6f3ff; text-align: right; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a>
            <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img">
            &thinsp;
            <?=$_SESSION["username"]?>さんの悩み、解決します！
            </a>
            <a class="navbar-brand" href="select.php"><i class="fa fa-table"></i>登録データ一覧</a>
            <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
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