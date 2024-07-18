<?php
session_start();
include('functions.php');
$pdo = connect_to_db();

if (!isset($_SESSION['user_id'])) {
    echo "ログインしてください。";
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$profileUserId = $_POST['profileUserId'];
$action = $_POST['action'];

if ($action == 'follow') {
    $stmt = $pdo->prepare('INSERT INTO sns_follow_table (follower_id, followed_id, created_at) VALUES (:follower_id, :followed_id, NOW())');
    $stmt->bindValue(':follower_id', $loggedInUserId, PDO::PARAM_INT);
    $stmt->bindValue(':followed_id', $profileUserId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "フォローしました。";
    } else {
        echo "フォローに失敗しました。";
    }
} elseif ($action == 'unfollow') {
    $stmt = $pdo->prepare('DELETE FROM sns_follow_table WHERE follower_id = :follower_id AND followed_id = :followed_id');
    $stmt->bindValue(':follower_id', $loggedInUserId, PDO::PARAM_INT);
    $stmt->bindValue(':followed_id', $profileUserId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "フォロー解除しました。";
    } else {
        echo "フォロー解除に失敗しました。";
    }
}

header('Location: profile.php?id=' . $profileUserId);
exit();
?>
