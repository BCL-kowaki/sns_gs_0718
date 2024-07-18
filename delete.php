<?php
// DB接続
include('functions.php');
$pdo = connect_to_db();

// エラーレポートを有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ユーザーIDを取得
$id = $_GET['user_id'];

// ユーザーを削除
$sql = 'DELETE FROM sns_regist_table WHERE user_id=:user_id';

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $id, PDO::PARAM_STR);

try {
    $status = $stmt->execute();
  } catch (PDOException $e) {
    echo json_encode(["sql error" => "{$e->getMessage()}"]);
    exit();
  }

// 削除後のリダイレクト
header('Location: regist_confirm.php');
exit();
?>
