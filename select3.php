<?php
session_start();

include "funcs.php";

//１. DB接続します
$pdo = db_conn();

sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

// 検索キーワードを取得
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// 「助かった」ボタンが押された場合の処理
if(isset($_POST['helpful'])) {
    $book_id = $_POST['book_id'];
    $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = helpful_count + 1 WHERE id = :id");
    $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
    $stmt->execute();
}

//２．データ取得SQL作成
if (!empty($search_keyword)) {
    $stmt = $pdo->prepare("SELECT * FROM gs_bm_table WHERE book LIKE :keyword OR worry LIKE :keyword OR coment LIKE :keyword");
    $stmt->bindValue(':keyword', '%'.$search_keyword.'%', PDO::PARAM_STR);
} else {
    $stmt = $pdo->prepare("SELECT * FROM gs_bm_table");
}
$status = $stmt->execute();

//３．データ表示
$view = "";
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorQuery:".$error[2]);
} else {
    $view .= '<div class="row">';
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $view .= '<div class="col-md-4 mb-4">';
        $view .= '<div class="card h-100">';
        $view .= '<div class="card-body">';
        $view .= '<h5 class="card-title">' . h($result['book']) . '</h5>';
        $view .= '<h6 class="card-subtitle mb-2 text-muted">' . h($result['date']) . '</h6>';
        $view .= '<p class="card-text"><strong>投稿者：</strong>' . h($result['username']) . '</p>';
        $view .= '<p class="card-text"><strong>悩み：</strong>' . h($result['worry']) . '</p>';
        $view .= '<p class="card-text"><strong>コメント：</strong>' . h($result['coment']) . '</p>';
        $view .= '<a href="' . h($result['url']) . '" class="btn btn-primary btn-block mb-2" target="_blank">詳細を見る</a>';

        // 「助かった」ボタンを追加
        $view .= '<form method="post" action="">';
        $view .= '<input type="hidden" name="book_id" value="' . h($result['id']) . '">';
        $view .= '<button type="submit" name="helpful" class="btn btn-helpful btn-block mb-2">助かった！ (' . h($result['helpful_count']) . ')</button>';
        $view .= '</form>';

        if ($result['username'] === $_SESSION['username'] || $_SESSION['username'] === 'admin') {
            $view .= '<div class="d-flex justify-content-between">';
            $view .= '<a href="detail.php?id=' . h($result['id']) . '" class="btn btn-success flex-grow-1 mr-2">更新</a>';
            $view .= '<a href="delete.php?id=' . h($result['id']) . '" class="btn btn-danger flex-grow-1" onclick="return confirm(\'本当に削除しますか？\');">削除</a>';
            $view .= '</div>';
        }
        $view .= '</div></div></div>';
    }
    $view .= '</div>';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>登録データ表示</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

<style>
    body {
        background-image: url('./img/background2.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #f8f9fa;
    }
    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;   /* 真円 */
        object-fit: cover;    /* 枠に合わせて切り取る */
    }

    .navbar { background-color: #007bff; }
    .navbar-brand { color: white !important; }
    .navbar-brand:hover { text-decoration: underline; }
    .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: 0.3s; }
    .card:hover { box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
    .btn-primary { background-color: #007bff; border-color: #007bff; }
    .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }

    /* 助かったボタンのスタイル */
    .btn-helpful {
        background-color: #FFB6C1;
        border-color: #FFB6C1;
        color: #fff;
        position: relative;
        padding-right: 50px;
        transition: all 0.3s ease;
    }
    .btn-helpful:hover {
        background-color: #FF69B4;
        border-color: #FF69B4;
        color: #fff;
    }
    .helpful-count {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-helpful:hover .helpful-count {
        background-color: rgba(255, 255, 255, 0.5);
    }
    @keyframes pulse {
        0% { transform: translateY(-50%) scale(1); }
        50% { transform: translateY(-50%) scale(1.1); }
        100% { transform: translateY(-50%) scale(1); }
    }
    .helpful-count.animate {
        animation: pulse 0.5s;
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
    <a class="navbar-brand" href="index3.php"><i class="fas fa-plus-circle"></i>悩み登録</a>
    <a class="navbar-brand" href="index2.php"><i class="fas fa-database"></i>データ登録</a>
    <a class="navbar-brand" href="gpt.php"><i class="fas fa-search"></i>AI書籍検索</a>
    <a class="navbar-brand" href="user_edit.php"><i class="fa fa-pen"></i>ユーザー情報編集</a>
    <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
  </div>
</nav>

<div class="container">
  <h2 class="text-center mb-4 font-weight-bold text-warning"><i class="fas fa-book-open"></i>登録データ一覧</h2>
  
  <!-- 検索フォーム -->
  <form action="" method="GET" class="mb-4"  id="searchForm">
    <div class="input-group">
      <input type="text" class="form-control" placeholder="キーワードを入力" name="search" id="searchInput" value="<?= h($search_keyword) ?>">
      <div class="input-group-append">
        <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i>検索</button>
        <button class="btn btn-secondary" type="button" id="resetSearch"><i class="fas fa-undo"></i>リセット</button>
      </div>
    </div>
  </form>

  <?= $view ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- 検索リセット用のJavaScript -->
<script>
document.getElementById('resetSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchForm').submit();
});

// 助かったボタンの初期化
$('.btn-helpful').each(function() {
    var count = $(this).text().match(/\d+/)[0];
    $(this).html('助かった！ <span class="helpful-count">' + count + '</span>');
});

// 助かったボタンのクリックイベント
$(document).on('click', '.btn-helpful', function(e) {
    e.preventDefault();
    var button = $(this);
    var form = button.closest('form');
    var countSpan = button.find('.helpful-count');
    var currentCount = parseInt(countSpan.text());
    var newCount = currentCount + 1;
    
    countSpan.text(newCount).addClass('animate');
    setTimeout(function() {
        countSpan.removeClass('animate');
    }, 500);
});
</script>

</body>
</html>