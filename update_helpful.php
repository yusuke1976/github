<?php
session_start();
include "funcs.php";

header('Content-Type: application/json');

if(isset($_POST['helpful'])) {
    $pdo = db_conn();
    $book_id = $_POST['book_id'];
    $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = helpful_count + 1 WHERE id = :id");
    $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
    if($stmt->execute()) {
        $stmt = $pdo->prepare("SELECT helpful_count FROM gs_bm_table WHERE id = :id");
        $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'newCount' => $result['helpful_count']]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}