<?php
session_start();
include "funcs.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = db_conn();
    
    $sender_username = $_SESSION['username'];
    $receiver_username = $_POST['receiverUsername'];
    $message = $_POST['messageText'];
    
    $stmt = $pdo->prepare("INSERT INTO gs_messages_table (sender_username, receiver_username, message) VALUES (:sender, :receiver, :message)");
    $stmt->bindValue(':sender', $sender_username, PDO::PARAM_STR);
    $stmt->bindValue(':receiver', $receiver_username, PDO::PARAM_STR);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'メッセージが送信されました。']);
    } else {
        echo json_encode(['success' => false, 'message' => 'メッセージの送信に失敗しました。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '不正なリクエストです。']);
}