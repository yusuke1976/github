<?php

session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <title>AI書籍検索</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

        <meta name="theme-color" content="#7952b3">
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
                border-radius: 50%;
                object-fit: cover;
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

            .container {
                max-width: 1100px;
            }
            textarea {
                width:100%;
                height:100px;
            }
            #outputText{
                width: 100%;
                height: 100%;
                background-color: #f8f9fa;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                padding: 15px;
                margin-top: 15px;
                white-space: pre-wrap;
                word-wrap: break-word;
                font-size: 1rem;
                line-height: 1.5;
                text-align: left;
            }

            .input-group {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
            }

            #formText {
                flex-grow: 1;
            }

            @media (max-width: 768px) {
                .container {
                    padding-left: 20px;
                    padding-right: 20px;
                }
                .input-group {
                    flex-direction: column;
                }
                #formText, #btn, #resetBtn {
                    width: 100%;
                    margin-bottom: 10px;
                }
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
    
        <main>
            <section class="py-5 text-center container">
                <div class="col-lg-8 col-md-10 mx-auto py-2">
                    <h1 class="mb-3 fw-medium">本で悩み解決サービス</h1>
                    
                    <div>
                        <textarea
                        id="inputText"
                        class="mt-3"
                        placeholder="ここに悩みを入力してください"
                        ></textarea>
                        <button class="mt-3 btn btn-primary" onclick="submitPrompt()">悩みを入力したらクリック</button>
                        <div id="outputText" class="mt-3 mb-3"></div>
                    </div>

                    <div class="input-group mt-4">
                        <input type="text" id="formText" name="myFormText" class="form-control" placeholder="キーワード（本のタイトルや内容、著者等）を入力" aria-label="books" aria-describedby="btn">
                        <button id="btn" class="btn btn-primary"><i class="fas fa-search"></i>検索</button>
                        <button id="resetBtn" class="btn btn-secondary"><i class="fas fa-undo"></i>リセット</button>
                    </div>                            
                </div>
            </section>

            <div id="bookItem" class="container">
                <div class="row row-cols-1 row-cols-md-3 g-4"></div>
            </div>
        </main>
    </body>
    <script src="gpt.js"></script>
</html>