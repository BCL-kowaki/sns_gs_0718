<?php
ob_start(); // 出力バッファリングを開始
session_start();

// DB接続
include('functions.php');
$pdo = connect_to_db();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// エラーレポートを有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'];

// ユーザー情報を取得するSQLクエリ
$sql = 'SELECT * FROM sns_regist_table WHERE user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('ユーザーが見つかりません。');
}

// コメントデータを読み込む関数
function getComments($postId, $pdo) {
    $sql = 'SELECT * FROM sns_timeline_table WHERE post_id = :post_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// いいねボタンの処理
if (isset($_REQUEST['like'])) {
    $contributor = $pdo->prepare('SELECT user_id FROM sns_timeline_table WHERE post_id=?');
    $contributor->execute(array($_REQUEST['like']));
    $pressed_message = $contributor->fetch();

    if ($_SESSION['user_id'] != $pressed_message['user_id']) {
        $pressed = $pdo->prepare('SELECT COUNT(*) AS cnt FROM sns_like_table WHERE post_id=? AND user_id=?');
        $pressed->execute(array($_REQUEST['like'], $_SESSION['user_id']));
        $my_like_cnt = $pressed->fetch();

        if ($my_like_cnt['cnt'] < 1) {
            $press = $pdo->prepare('INSERT INTO sns_like_table SET post_id=?, user_id=?, created_at=NOW()');
            $press->execute(array($_REQUEST['like'], $_SESSION['user_id']));
        } else {
            $cancel = $pdo->prepare('DELETE FROM sns_like_table WHERE post_id=? AND user_id=?');
            $cancel->execute(array($_REQUEST['like'], $_SESSION['user_id']));
        }
        header("Location: timeline.php?page={$page}");
        exit();
    }
}

// ログインユーザーが「いいね」した投稿IDを取得
$like = $pdo->prepare('SELECT post_id FROM sns_like_table WHERE user_id=?');
$like->execute(array($_SESSION['user_id']));
$my_like = $like->fetchAll(PDO::FETCH_COLUMN);


?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/reset.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/timeline.css">
<script src="https://kit.fontawesome.com/204cd3d405.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <title>FREInet</title>
</head>
<body>
<section id="wrapper">
<section class="header">
    <div class=img><img src="images/logo.png"></div>
</section>
<section class="timelinebox">
    <div class="inner">
    <form action="post_timeline.php" method="post" enctype="multipart/form-data">
    <dl> 
        <dd>
            <div class="flex">
            <div class="postimg">

        <?php
            // ログインしているユーザーのアイコンを表示
            if (!empty($user['profile_img'])) {
                echo "<img src='data/profiles/" . htmlspecialchars($user['profile_img'], ENT_QUOTES, 'UTF-8') . "' alt='プロフィール画像' width='50' style='vertical-align: middle;'>";
            } else {
                echo "<img src='images/default_icon.png' alt='デフォルトアイコン' width='50' style='vertical-align: middle;'>";
            }
            ?>  
            </div>             
        <textarea name="post_content" rows="4" cols="50" placeholder="今の気持ちを投稿しよう" required></textarea></div><dd>
        <dd>
            <ul>
                <li>
            <input type="file" id="post_img" name="post_img"></li>
            <li><input type="submit" value="投稿"></li>
</dd>
    </form>
    </div>
</section>    

    <div class="timeline">
    <?php
// 投稿データを取得するSQLクエリ
$sql = 'SELECT p.*, 
            (SELECT COUNT(*) FROM sns_like_table WHERE post_id = p.post_id) AS like_cnt 
        FROM sns_timeline_table p 
        ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 逆順にした行を表示する
foreach ($posts as $post) {
    echo "
    <section class='box'>
    <div class='post'>";
    if ($post['profile_img']) {
        echo "<dl><dt><img src='data/profiles/" . htmlspecialchars($post['profile_img'], ENT_QUOTES, 'UTF-8') . "' alt='プロフィール画像' width='50'></dt>";
    }
    echo "<dd><ul><li><a href='profile.php?id=" . htmlspecialchars($post['user_id'], ENT_QUOTES, 'UTF-8') . "'><strong>" . htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8') . "</strong></a></li>";
    echo "<li class='timestamp'>" . htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') . "</li></ul></dd></dl>";
    echo "<div class='postarea'><p>" . htmlspecialchars($post['post'], ENT_QUOTES, 'UTF-8') . "</p></div>";
    if ($post['post_img']) {
        echo "<img src='data/uploads/" . htmlspecialchars($post['post_img'], ENT_QUOTES, 'UTF-8') . "' alt='投稿画像'>";
    }

    $post_id = $post['post_id'] ?? '';
    $page = $page ?? '';
    $is_liked = in_array($post['post_id'], $my_like);
    $like_cnt = $post['like_cnt'] ?? 0; // null の場合は 0 を設定

    // いいねボタンの表示（いいね済みの場合は赤色ハート）
    echo '<div class="like"><a class="like-button heart' . ($is_liked ? ' red' : '') . '" href="timeline.php?like=' . htmlspecialchars($post['post_id'], ENT_QUOTES, 'UTF-8') . '&page=' . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . '">' . ($is_liked ? '<span class="material-symbols-outlined">
    thumb_up</span>' : '<span class="material-symbols-outlined">favorite</span>') . ' いいね </a>';
    // いいねの数を表示
    echo '<span>' . htmlspecialchars($like_cnt, ENT_QUOTES, 'UTF-8') . '</span></div><div class="comment">';


    // コメント表示
    $sql_comments = 'SELECT * FROM sns_comment_table WHERE post_id = :post_id ORDER BY created_at DESC';
    $stmt_comments = $pdo->prepare($sql_comments);
    $stmt_comments->bindValue(':post_id', $post['post_id'], PDO::PARAM_STR);
    $stmt_comments->execute();
    $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($comments as $comment) {
        echo '<div class="flex">';
        if ($comment['profile_img']) {
            echo "<div class='img'><img src='data/profiles/" . htmlspecialchars($comment['profile_img'], ENT_QUOTES, 'UTF-8') . "' alt=''></div>";
        }
        echo "<div class='commentarea'><div class='fukidashi'><a href='profile.php?id=" . htmlspecialchars($comment['user_id'], ENT_QUOTES, 'UTF-8') . "'><strong>" . htmlspecialchars($comment['name'], ENT_QUOTES, 'UTF-8') . "</strong></a>";
        echo "<p>" . htmlspecialchars($comment['text'], ENT_QUOTES, 'UTF-8') . "</p></div>";
        echo "<span class='timestamp'>" . htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') . "</span></div>";
        if ($comment['text_img']) {
            echo "<img src='data/uploads/" . htmlspecialchars($comment['text_img'], ENT_QUOTES, 'UTF-8') . "' alt='コメント画像' width='100'>";
        }
        echo "</div>";
    }


    // コメントフォームの追加
    if ($user['profile_img']) {
        echo "<div class='input_comment'><dl><dt><img src='data/profiles/" . htmlspecialchars($user['profile_img'], ENT_QUOTES, 'UTF-8') . "' alt='' width='50'></dt>";
    }
    echo '<dd><form action="post_comment.php" method="POST">';
    echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post['post_id'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div class="flex"><textarea name="comment_text" placeholder="'. htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') .'として入力"></textarea>';
    // echo '<input type="file" name="text_img">';
    echo '<button type="submit"><span class="material-icons">send</span></button></div>';
    echo '</form></dd></dl></div>';

    
    echo"</section>";
}
?>

    </div>
    </div>
</section>
</section>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// コンテキストメニューを無効にする
$(document).on("contextmenu", function(e) {
    e.preventDefault();
});

// 編集と削除のイベントハンドラ
$(document).ready(function() {
    $(".post").on("contextmenu", function(e) {
        var postId = $(this).find('.post-actions').data('post-id');
        var contextMenu = $("<div class='custom-context-menu'><ul><li class='edit' data-post-id='" + postId + "'>編集</li><li class='delete' data-post-id='" + postId + "'>削除</li></ul></div>");
        $("body").append(contextMenu);
        contextMenu.css({ top: e.pageY, left: e.pageX });
        contextMenu.show();
    });

    $(document).on("click", function() {
        $(".custom-context-menu").remove();
    });

    $(document).on("click", ".custom-context-menu .edit", function() {
        var postId = $(this).data('post-id');
        var postContent = $(".post-actions[data-post-id='" + postId + "']").closest('.post').find('.postarea p').text();
        $(".post-actions[data-post-id='" + postId + "']").closest('.post').find('.postarea').html("<textarea class='edit-area'>" + postContent + "</textarea><button class='save-edit-btn'>保存</button>");
        $(".custom-context-menu").remove();
    });

    $(document).on("click", ".custom-context-menu .delete", function() {
        var postId = $(this).data('post-id');
        if (confirm("本当に削除しますか？")) {
            $.post("timeline_delete_post.php", { post_id: postId }, function(response) {
                location.reload();
            });
        }
        $(".custom-context-menu").remove();
    });

    $(document).on("click", ".save-edit-btn", function() {
        var postId = $(this).closest('.post').find('.post-actions').data('post-id');
        var newContent = $(this).siblings('.edit-area').val();
        $.post("timeline_edit_post.php", { post_id: postId, new_content: newContent }, function(response) {
            location.reload();
        });
    });
});
</script>
</body>
</html>
