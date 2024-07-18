<?php
session_start();
include('functions.php');
$pdo = connect_to_db();

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// 既にいいねしているか確認
$pressed = $pdo->prepare('SELECT COUNT(*) AS cnt FROM likes WHERE post_id=? AND user_id=?');
$pressed->execute([$post_id, $user_id]);
$my_like_cnt = $pressed->fetch(PDO::FETCH_ASSOC);

if ($my_like_cnt['cnt'] < 1) {
    $press = $pdo->prepare('INSERT INTO likes SET post_id=?, user_id=?, created_at=NOW()');
    $press->execute([$post_id, $user_id]);
    $status = 'liked';
} else {
    $cancel = $pdo->prepare('DELETE FROM likes WHERE post_id=? AND user_id=?');
    $cancel->execute([$post_id, $user_id]);
    $status = 'unliked';
}

// いいねの数を取得
$like_count_stmt = $pdo->prepare('SELECT COUNT(*) AS like_count FROM likes WHERE post_id=?');
$like_count_stmt->execute([$post_id]);
$like_count = $like_count_stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

$response = [
    'status' => $status,
    'like_count' => $like_count
];

header('Content-Type: application/json');
echo json_encode($response);
