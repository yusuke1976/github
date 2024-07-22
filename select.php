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

// ユーザーのジャンルを取得
$stmt = $pdo->prepare("SELECT genre FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_genre = $user['genre'];

// 検索キーワードとフィルターオプションを取得
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$filter_different_genre = isset($_GET['filter_different_genre']) ? $_GET['filter_different_genre'] : false;

// 「助かりました」ボタンが押された場合の処理
if(isset($_POST['helpful'])) {
  $book_id = $_POST['book_id'];
  $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = helpful_count + 1 WHERE id = :id");
  $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
  if($stmt->execute()) {
      // 更新後のhelpful_countを取得
      $stmt = $pdo->prepare("SELECT helpful_count FROM gs_bm_table WHERE id = :id");
      $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      echo json_encode(['success' => true, 'newCount' => $result['helpful_count']]);
      exit;
  } else {
      echo json_encode(['success' => false]);
      exit;
  }
}

// データ取得SQL作成
$sql = "SELECT b.*, u.genre FROM gs_bm_table b JOIN gs_user_table5 u ON b.username = u.username WHERE 1=1";
$params = array();

if (!empty($search_keyword)) {
    $sql .= " AND (b.book LIKE :keyword OR b.worry LIKE :keyword OR b.coment LIKE :keyword OR b.username LIKE :keyword)";
    $params[':keyword'] = '%'.$search_keyword.'%';
}

if ($filter_different_genre) {
    $sql .= " AND u.genre != :user_genre";
    $params[':user_genre'] = $user_genre;
}

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
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
        $view .= '<a href="' . h($result['url']) . '" class="btn btn-primary btn-block mb-2" target="_blank"><i class="fas fa-external-link-alt"></i> 詳細を見る</a>';
        $view .= '<button class="btn btn-info btn-block mb-2 send-message-btn" data-username="' . h($result['username']) . '"><i class="far fa-envelope"></i> メッセージを送る</button>';

        // 「助かりました」ボタンとメッセージを追加
        $voted_users = explode(',', $result['voted_users']);
        $isVoted = in_array($_SESSION['username'], $voted_users);

        $view .= '<div id="helpfulMessage_' . h($result['id']) . '" class="alert alert-success mb-2 text-center" style="display:none;">投票ありがとう！</div>';
        $view .= '<button class="btn btn-helpful btn-block mb-2 helpful-button' . ($isVoted ? ' voted' : '') . '" data-id="' . h($result['id']) . '">';
        $view .= '<i class="' . ($isVoted ? 'fas' : 'far') . ' fa-heart mr-2"></i><span class="button-text">' . ($isVoted ? 'キャンセル' : '助かりました') . '</span> <span class="helpful-count">' . h($result['helpful_count']) . '</span>';
        $view .= '</button>';

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

    /* 投票ありがとうメッセージのスタイル */
    .thank-you-message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(45deg, #FF69B4, #FF1493);
        color: white;
        padding: 15px 30px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .thank-you-message.show {
        opacity: 1;
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(0.9); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
        100% { transform: translate(-50%, -50%) scale(1); }
    }

    /* 助かりましたボタンのスタイル */
    .btn-helpful {
        background-color: #FFB6C1; /* 優しいピンク */
        border-color: #FFB6C1;
        color: #fff;
    }
    .btn-helpful:hover {
        background-color: #FF69B4; /* ホバー時少し濃いピンク */
        border-color: #FF69B4;
        color: #fff;
    }

    .filter-container {
        display: flex;
        align-items: center;
        margin-top: 15px;
    }

    .filter-checkbox {
        transform: scale(2);
        margin-right: 15px;
    }

    .filter-label {
        font-size: 1.1em;
        font-weight: bold;
        color: #007bff;
        background-color: #f8f9fa;
        padding: 5px 15px;
        border-radius: 5px;
        border: 2px solid #007bff;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-label:hover {
        background-color: #007bff;
        color: #fff;
    }

    .user-welcome{
        font-size: 1rem;
    }

    .firework {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 0.5vmin;
        aspect-ratio: 1;
        background:
            radial-gradient(circle, #ff0 0.2vmin, #0000 0) 50% 00%,
            radial-gradient(circle, #ff0 0.3vmin, #0000 0) 00% 50%,
            radial-gradient(circle, #ff0 0.5vmin, #0000 0) 50% 99%,
            radial-gradient(circle, #ff0 0.2vmin, #0000 0) 99% 50%,
            radial-gradient(circle, #ff0 0.3vmin, #0000 0) 80% 90%,
            radial-gradient(circle, #ff0 0.5vmin, #0000 0) 95% 90%,
            radial-gradient(circle, #ff0 0.5vmin, #0000 0) 10% 60%,
            radial-gradient(circle, #ff0 0.2vmin, #0000 0) 31% 80%,
            radial-gradient(circle, #ff0 0.3vmin, #0000 0) 80% 10%,
            radial-gradient(circle, #ff0 0.2vmin, #0000 0) 90% 23%,
            radial-gradient(circle, #ff0 0.3vmin, #0000 0) 45% 20%,
            radial-gradient(circle, #ff0 0.5vmin, #0000 0) 13% 24%;
        background-size: 0.5vmin 0.5vmin;
        background-repeat: no-repeat;
        animation: firework 1s infinite;
        opacity: 0;
        z-index: 9999;
    }

    @keyframes firework {
        0% { transform: translate(-50%, -50%) scale(0); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(30); opacity: 0; }
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center">
      <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img mr-2">
      <span class="user-welcome text-dark">
        <?=$_SESSION["username"]?>さん<br>
        の悩み、解決します！
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link text-white" href="index3.php"><i class="fas fa-plus-circle"></i> 悩み登録</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="index10.php"><i class="fas fa-list-ul"></i> 悩み一覧</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="index2.php"><i class="fas fa-database"></i> データ登録</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="gpt.php"><i class="fas fa-search"></i> AI書籍検索</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="user_edit.php"><i class="fa fa-pen"></i> ユーザー情報編集</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="messages.php"><i class="far fa-envelope"></i> メッセージ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h2 class="text-center mb-4 font-weight-bold text-warning"><i class="fas fa-book-open"></i>登録データ一覧</h2>

  <!-- 「助かりました」メッセージの表示 -->
  <?php if (!empty($helpful_message)): ?>
    <div class="alert alert-success" role="alert">
      <?= $helpful_message ?>
    </div>
  <?php endif; ?>

  <!-- 検索フォーム -->
<form action="" method="GET" class="mb-4" id="searchForm">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="キーワードを入力" name="search" id="searchInput" value="<?= h($search_keyword) ?>">
        <div class="input-group-append">
            <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i>検索</button>
            <button class="btn btn-secondary" type="button" id="resetSearch"><i class="fas fa-undo"></i>リセット</button>
        </div>
    </div>
    <div class="filter-container">
        <input class="filter-checkbox" type="checkbox" id="filterDifferentGenre" name="filter_different_genre" value="1" <?= $filter_different_genre ? 'checked' : '' ?>>
        <label class="filter-label" for="filterDifferentGenre">
            <i class="fas fa-filter mr-2"></i> 自分と違うジャンルの投稿のみ表示
        </label>
    </div>
</form>

  <?= $view ?>

</div>

<!-- メッセージ送信用モーダル -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">メッセージを送信</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="messageForm">
          <input type="hidden" id="receiverUsername" name="receiverUsername">
          <div class="form-group">
            <label for="messageText">メッセージ:</label>
            <textarea class="form-control" id="messageText" name="messageText" rows="3" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
        <button type="button" class="btn btn-primary" id="sendMessageBtn">送信</button>
      </div>
    </div>
  </div>
</div>

<div class="firework" style="display: none;"></div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- 検索リセット用のJavaScript -->
<script>
document.getElementById('resetSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterDifferentGenre').checked = false;
    document.getElementById('searchForm').submit();
});

// 助かりましたボタンのAjax処理
$(document).ready(function() {
    $('.helpful-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.data('id');
        var isVoted = button.hasClass('voted');
        $.ajax({
            url: 'update_helpful.php',
            type: 'POST',
            data: { 
                helpful: !isVoted, 
                book_id: id, 
                username: '<?php echo $_SESSION['username']; ?>' 
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var countElement = button.find('.helpful-count');
                    countElement.text(response.newCount);
                    $('#helpfulMessage_' + id).text(response.message).fadeIn().delay(400).fadeOut();
                    button.toggleClass('voted');
                    var icon = button.find('i');
                    var buttonText = button.find('.button-text');
                    if (isVoted) {
                        icon.removeClass('fas').addClass('far');
                        buttonText.text('助かりました');
                    } else {
                        icon.removeClass('far').addClass('fas');
                        buttonText.text('キャンセル');
                    }
                    
                    // 初めての投票の場合、花火アニメーションを表示
                    if (response.isFirstVote) {
                        $('.firework').show().css('opacity', 1);
                        setTimeout(function() {
                            $('.firework').css('opacity', 0);
                            setTimeout(function() {
                                $('.firework').hide();
                            }, 1000);
                        }, 1000);
                    }
                } else {
                    alert(response.message || 'エラーが発生しました。');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
            }
        });
    });
});
</script>

<script>
// メッセージ送信ボタンのクリックイベント
$(document).on('click', '.send-message-btn', function() {
    var receiverUsername = $(this).data('username');
    $('#receiverUsername').val(receiverUsername);
    $('#messageModal').modal('show');
});

// メッセージ送信処理
$('#sendMessageBtn').on('click', function() {
    var receiverUsername = $('#receiverUsername').val();
    var messageText = $('#messageText').val();

    $.ajax({
        url: 'send_message.php',
        type: 'POST',
        data: {
            receiverUsername: receiverUsername,
            messageText: messageText
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#messageModal').modal('hide');
                $('#messageText').val('');
            } else {
                alert('エラー: ' + response.message);
            }
        },
        error: function() {
            alert('通信エラーが発生しました。');
        }
    });
});
</script>

</body>
</html>