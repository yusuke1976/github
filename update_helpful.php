<?php
session_start();
include "funcs.php";

header('Content-Type: application/json');

if(isset($_POST['helpful']) && isset($_POST['username'])) {
    $pdo = db_conn();
    $book_id = $_POST['book_id'];
    $username = $_POST['username'];

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        // 現在の投票状況を確認
        $stmt = $pdo->prepare("SELECT voted_users, helpful_count FROM gs_bm_table WHERE id = :id");
        $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $voted_users = $result['voted_users'] ? explode(',', $result['voted_users']) : [];

        if (!in_array($username, $voted_users)) {
            // 新しい投票を追加
            $voted_users[] = $username;
            $new_voted_users = implode(',', $voted_users);

            $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = helpful_count + 1, voted_users = :voted_users WHERE id = :id");
            $stmt->bindValue(':voted_users', $new_voted_users, PDO::PARAM_STR);
            $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();

            echo json_encode(['success' => true, 'newCount' => $result['helpful_count'] + 1]);
        } else {
            // 既に投票済み
            echo json_encode(['success' => false, 'message' => '既に投票済みです。']);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => '処理中にエラーが発生しました。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '無効なリクエストです。']);
}