<?php
// DB接続
include('functions.php');
$pdo = connect_to_db();

// エラーレポートを有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// POSTデータを取得
$post_id = $_POST['post_id'];
$new_content = $_POST['new_content'];

// 投稿を編集
$sql = 'UPDATE sns_timeline_table SET post = :new_content WHERE post_id = :post_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':new_content', $new_content, PDO::PARAM_STR);
$stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();

echo "編集しました。";
?>
