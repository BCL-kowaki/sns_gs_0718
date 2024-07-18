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

// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit();
// }

$loggedInUserId = $_SESSION['user_id'];
$profileUserId = isset($_GET['id']) ? $_GET['id'] : $loggedInUserId; // URLパラメータからユーザーIDを取得、なければログインユーザー

// プロフィールユーザー情報を取得するSQLクエリ
$sql = 'SELECT * FROM sns_regist_table WHERE user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $profileUserId, PDO::PARAM_STR);
$stmt->execute();
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profileUser) {
echo "ユーザーが見つかりません。";
exit();
}

// タイムラインデータを読み込む
$sql = 'SELECT * FROM sns_timeline_table WHERE user_id = :user_id ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $profileUserId, PDO::PARAM_STR);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// フォローボタンの処理
if (isset($_REQUEST['follow'])) {
    $contributor = $pdo->prepare('SELECT followed_id FROM sns_follow_table WHERE followed_id=?');
    $contributor->execute(array($_REQUEST['follow']));
    $pressed_message = $contributor->fetch();

    if ($_SESSION['user_id'] != $pressed_message['followed_id']) {
        $pressed = $pdo->prepare('SELECT COUNT(*) AS cnt FROM sns_follow_table WHERE followed_id=? AND followed_id=?');
        $pressed->execute(array($_REQUEST['follow'], $_SESSION['user_id']));
        $my_follow_cnt = $pressed->fetch();

        if ($my_follow_cnt['cnt'] < 1) {
            $press = $pdo->prepare('INSERT INTO sns_follow_table SET followed_id=?, followed_id=?, created_at=NOW()');
            $press->execute(array($_REQUEST['follow'], $_SESSION['followed_id']));
        } else {
            $cancel = $pdo->prepare('DELETE FROM sns_follow_table WHERE followed_id=? AND followed_id=?');
            $cancel->execute(array($_REQUEST['follow'], $_SESSION['followed_id']));
        }
        header('Location: profile.php?id=' . $profileUserId);
        exit();
    }
}

// ログインユーザーが「フォロー」したユーザーIDを取得
$follow = $pdo->prepare('SELECT followed_id FROM sns_follow_table WHERE followed_id=?');
$follow->execute(array($_SESSION['user_id']));
$my_follow = $follow->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/reset.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/user.css">
<title>プロフィールページ</title>
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
    <div class="profilesimg"><img src="data/profiles/<?php echo htmlspecialchars($profileUser['profile_img']); ?>" required></div>
    <?php if ($profileUserId === $loggedInUserId): ?>
        <form action="update_user.php" method="post" enctype="multipart/form-data">
            <dl class="profilesimg">
                <dd><label for="profile_img"></label>
                <input type="file" id="profile_img" name="profile_img"></dd>
            </dl> 
            <dl>
                <dt><label for="name">会員ID</label></dt>
                <dd><input type="text" id="newId" name="newId" value="<?php echo htmlspecialchars($profileUser['user_id']); ?>" required readonly></dd>
            </dl>  
            <dl>
                <dt><label for="password">パスワード</label></dt>
                <dd><input type="password" id="password" name="password" value="<?php echo htmlspecialchars($profileUser['password']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="name">名前</label></dt>
                <dd><input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profileUser['name']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="kana">かな</label></dt>
                <dd><input type="text" id="kana" name="kana" value="<?php echo htmlspecialchars($profileUser['kana']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="tel">電話番号</label></dt>
                <dd><input type="text" id="tel" name="tel" value="<?php echo htmlspecialchars($profileUser['tel']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="mail">メールアドレス</label></dt>
                <dd><input type="email" id="mail" name="mail" value="<?php echo htmlspecialchars($profileUser['mail']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="zipcode">郵便番号</label></dt>
                <dd><input type="text" id="zipcode" name="zipcode" value="<?php echo htmlspecialchars($profileUser['zipcode']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address1">都道府県</label></dt>
                <dd><input type="text" id="address1" name="address1" value="<?php echo htmlspecialchars($profileUser['address1']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address2">市区町村</label></dt>
                <dd><input type="text" id="address2" name="address2" value="<?php echo htmlspecialchars($profileUser['address2']); ?>" required></dd>
            </dl>  
            <dl>
                <dt><label for="address3">番地</label></dt>
                <dd><input type="text" id="address3" name="address3" value="<?php echo htmlspecialchars($profileUser['address3']); ?>"></dd>
            </dl>  
            <input type="submit" value="更新">
        </form>
    <?php else: ?>
        <dl>   
            <dt>名前</dt>
            <dd><?php echo htmlspecialchars($profileUser['name']); ?></dd>
            </dl>
        <dl>   
            <dt>住まい</dt>
            <dd><?php echo htmlspecialchars($profileUser['address1']); ?></dd>
    </dl> 

    <?php endif; ?>
    </section>
    <div class="logout"><a href="logout.php">ログアウト</a></div>
    <div class="timeline"><a href="timeline.php">全体のタイムラインを見る</a></div>
</div>

</section> 
        <!-- ユーザーの投稿表示 -->
    <div class="timelineUser">
        <?php foreach ($posts as $post): ?>
            <section class='box'>
                <div class='post'>
                <?php if ($post['profile_img']): ?>
                        <dl>
                            <dt><img src='data/profiles/<?php echo htmlspecialchars($post['profile_img'], ENT_QUOTES, 'UTF-8'); ?>' alt='プロフィール画像' width='50'></dt>
                    <?php endif; ?>
                    <dd>
                        <ul>
                            <li><a class="" href='profile.php?id=<?php echo htmlspecialchars($post['user_id'], ENT_QUOTES, 'UTF-8'); ?>'><strong><?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?></strong></a></li>
                            <li class='timestamp'><?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?></li>
                        </ul>
                    </dd>

                    </dl>
                    <div class='postarea'><p><?php echo htmlspecialchars($post['post']); ?></p></div>
                    <?php if ($post['post_img']): ?>
                        <img src='data/uploads/<?php echo htmlspecialchars($post['post_img'], ENT_QUOTES, 'UTF-8'); ?>' alt='投稿画像'>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</body>
</html>
