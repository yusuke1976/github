<?php
session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

// 悩みデータ取得
$stmt = $pdo->prepare("SELECT * FROM gs_worry ORDER BY date DESC");
$status = $stmt->execute();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>悩み一覧</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .card-header {
            background-color: #4a5568;
            color: #ffffff;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px;
        }

        .card-header h2 {
            font-size: 1.3rem;
            margin-bottom: 0;
        }

        .card-body {
            padding: 20px;
        }
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
        <h1 class="mb-4">悩み一覧</h1>
        <?php
        if($status==false) {
            sql_error($stmt);
        } else {
            while( $result = $stmt->fetch(PDO::FETCH_ASSOC)){ 
        ?>
        <div class="card">
            <div class="card-header">
                投稿者: <?=$result["username"]?> | 日時: <?=$result["date"]?>
            </div>
            <div class="card-body">
                <p class="card-text"><?=$result["worry"]?></p>
            </div>
        </div>
        <?php
            }
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>