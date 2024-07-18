<?php
session_start();
$_SESSION = array();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="0;url=/sns_kai/login.php">    
<title>ログアウト</title>
</head>
<body>
<h1>
<font size='5'>ログアウトしました</font>
</h1>
<p><a href='login.php'>ログインページに戻る</a></p>
</body>
</html>
