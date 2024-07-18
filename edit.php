<?php
// DB接続
include('functions.php');
$pdo = connect_to_db();

// エラーレポートを有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ユーザーIDを取得
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo "ユーザーIDが指定されていません。";
    exit();
}

// ユーザー情報を取得
$sql = 'SELECT * FROM sns_regist_table WHERE user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR); // user_idが文字列として格納されている場合
$stmt->execute();
$record = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user.css">
    <title>編集ページ</title>
</head>
<body>
<section id="wrapper">
<section class="header">
    <div class="img"><img src="images/logo.png"></div>
</section>
<section class="box">
    <div class="inner">
        <h1>基本データ</h1>
        <section class="profiles">
        <div class="profilesimg"><img src="data/profiles/<?= $record['profile_img'] ?>" required></div>
        <form action="update_user.php" method="post" enctype="multipart/form-data">
            <dl class="profilesimg">
                <dd><label for="profile_img"></label>
                <input type="file" id="profile_img" name="profile_img"></dd>
            </dl> 
            <dl style="display: none;">
                <dt><label for="name">会員ID</label></dt>
                <dd><input type="hidden" id="newId" name="newId" value="<?= htmlspecialchars($record['user_id'], ENT_QUOTES, 'UTF-8') ?>" required readonly></dd>
            </dl>  
            <dl>
                <dt><label for="name">名前</label></dt>
                <dd><input type="text" id="name" name="name" value="<?= $record['name'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="kana">かな</label></dt>
                <dd><input type="text" id="kana" name="kana" value="<?= $record['kana'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="tel">電話番号</label></dt>
                <dd><input type="text" id="tel" name="tel" value="<?= $record['tel'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="mail">メールアドレス</label></dt>
                <dd><input type="email" id="mail" name="mail" value="<?= $record['mail'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="zipcode">郵便番号</label></dt>
                <dd><input type="text" id="zipcode" name="zipcode" value="<?= $record['zipcode'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address1">都道府県</label></dt>
                <dd><input type="text" id="address1" name="address1" value="<?= $record['address1'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address2">市区町村</label></dt>
                <dd><input type="text" id="address2" name="address2" value="<?= $record['address2'] ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address3">番地</label></dt>
                <dd><input type="text" id="address3" name="address3" value="<?= $record['address3'] ?>"></dd>
            </dl>  
            <input type="submit" value="更新">
        </form>
        </section>
    </div>
</section>
</body>
</html>
