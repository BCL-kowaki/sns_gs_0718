<?php
ob_start(); // 出力バッファリングを開始
session_start();

// DB接続
include('functions.php');
$pdo = connect_to_db();

// エラーレポートを有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// POSTデータを取得
if (!isset($_POST['identifier']) || !isset($_POST['password'])) {
    echo "ログインデータが不足しています。";
    exit();
}

$identifier = $_POST['identifier'];
$password = $_POST['password'];

// 認証関数
function authenticate($identifier, $password, $pdo) {
    // SQLクエリを準備
    $sql = 'SELECT * FROM sns_regist_table WHERE (user_id = :identifier OR mail = :identifier) AND password = :password';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    // 結果を取得
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        return $user;
    } else {
        return false;
    }
}

// 認証処理
$user = authenticate($identifier, $password, $pdo);
if ($user) {
    echo "ログイン成功: ユーザーID " . $_SESSION['user_id']; // ここでデバッグ情報を表示
    if ($user['user_id'] === 'admin') {
        header("Location: regist_confirm.php");
    } else {
        header("Location: user_page.php");
    }
    exit();
} else {
    echo "ログインに失敗しました。";
}
?>
