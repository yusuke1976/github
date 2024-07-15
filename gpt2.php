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
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

        <meta name="theme-color" content="#7952b3">
        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Noto Sans JP', sans-serif;
                font-size: 16px;
                color: #333;
            }

            .profile-img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #fff;
            }

            .navbar {
                background-color: #6c5ce7;
                padding: 15px 15px;
                box-shadow: 0 2px 4px rgba(0,0,0,.1);
            }
            
            .navbar-brand {
                color: #ffffff !important;
                font-weight: 350;
                font-size: 1.2rem;
                margin-left: 10px; 
                transition: all 0.3s ease;
            }

            .navbar-brand:hover {
                text-decoration: none;
                opacity: 0.8;
            }

            .container {
                max-width: 1200px;
            }

            h1 {
                font-family: 'Playfair Display', serif;
                color: #2c3e50;
            }

            textarea, #formText {
                width: 100%;
                border: none;
                border-radius: 8px;
                padding: 15px;
                background-color: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,.1);
                transition: all 0.3s ease;
            }

            textarea:focus, #formText:focus {
                outline: none;
                box-shadow: 0 2px 15px rgba(108,92,231,.2);
            }

            #outputText{
                width: 100%;
                background-color: #fff;
                border: none;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
                font-size: 1rem;
                line-height: 1.6;
                box-shadow: 0 2px 10px rgba(0,0,0,.1);
                display: none;
            }

            .btn {
                border-radius: 25px;
                padding: 10px 20px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-primary {
                background-color: #6c5ce7;
                border-color: #6c5ce7;
            }

            .btn-primary:hover {
                background-color: #5b4cdb;
                border-color: #5b4cdb;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(108,92,231,.3);
            }

            .btn-secondary {
                background-color: #95a5a6;
                border-color: #95a5a6;
            }

            .btn-secondary:hover {
                background-color: #7f8c8d;
                border-color: #7f8c8d;
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
                <a class="navbar-brand">
                    <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img">
                    &thinsp;
                    <?=$_SESSION["username"]?>さんの悩み、解決します！
                </a>
                <div>
                    <a class="navbar-brand" href="select.php"><i class="fa fa-table"></i> 登録データ一覧</a>
                    <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
                </div>
            </div>
        </nav>    
    
        <main>
            <section class="py-5 text-center container">
                <div class="col-lg-8 col-md-10 mx-auto py-2">
                    <h1 class="mb-4 fw-bold">本で悩み解決サービス</h1>
                    
                    <div class="mb-4">
                        <textarea
                        id="inputText"
                        class="mb-3"
                        placeholder="ここに悩みを入力してください"
                        rows="4"
                        ></textarea>
                        <button class="btn btn-primary btn-lg" onclick="submitPrompt()">
                            <i class="fas fa-magic mr-2"></i> AI選書！悩みを解決
                        </button>
                        <div id="outputText" class="mt-4"></div>
                    </div>

                    <div class="input-group mt-5">
                        <input type="text" id="formText" name="myFormText" class="form-control" placeholder="キーワード（本のタイトルや内容、著者等）を入力" aria-label="books" aria-describedby="btn">
                        <button id="btn" class="btn btn-primary"><i class="fas fa-search"></i> 検索</button>
                        <button id="resetBtn" class="btn btn-secondary"><i class="fas fa-undo"></i> リセット</button>
                    </div>                            
                </div>
            </section>

            <div id="bookItem" class="container mt-5">
                <div class="row row-cols-1 row-cols-md-3 g-4"></div>
            </div>
        </main>
    </body>
    <script src="gpt.js"></script>
</html>