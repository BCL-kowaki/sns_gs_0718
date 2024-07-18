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

// 投稿を削除
$sql = 'DELETE FROM sns_timeline_table WHERE post_id = :post_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();

echo "削除しました。";
?>
