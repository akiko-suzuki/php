<?php

$dsn='mysql:dbname=board;host=localhost;charset=utf8';
$user='root';
$password='';

date_default_timezone_set('Asia/Tokyo');

$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message_array = array();
$error_message = array();
$clean = array();

session_start();

if( !empty($_POST['btn_submit']) ) {

    if( empty($_POST['view_name']) ){
        $error_message[] = '表示名を入力してください。';
    } else {
        $clean['view_name'] = htmlspecialchars($_POST['view_name'],ENT_QUOTES);
        $_SESSION['view_name'] = $clean['view_name'];
    }

    if( empty($_POST['message']) ){
        $error_message[] = 'ひと言メッセージを入力してください。';
    }else {
        $clean['message'] = htmlspecialchars($_POST['message'],ENT_QUOTES);
    }

    if( empty($error_message) ) {
        try {        
            $dbh=new PDO($dsn,$user,$password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $now_date = date("Y-m-d H:i:s");
            $sql = "INSERT INTO message (view_name, message, post_date) VALUES ( '$clean[view_name]', '$clean[message]', '$now_date')";
            $stmt=$dbh->prepare($sql);
            $stmt->execute();
            if( $stmt ) {
                $_SESSION['success_message'] = 'メッセージを書き込みました。';
            }else {
                $error_message[] = '書き込みに失敗しました。';
            }
            $dbh=null;
        }catch (Exception $e) {
            $error_message[] = '書き込みに失敗しました。エラー：'.$e->getMessage();
        }
        header('Location: ./');	
    }
    
}

try {
    $dbh=new PDO($dsn,$user,$password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT view_name, message, post_date FROM message ORDER BY post_date DESC";
    $stmt=$dbh->prepare($sql);
    $stmt->execute();
    if( $stmt ){
        $message_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}catch (Exception $e) {
    $error_message[] = 'データの読み込みに失敗しました。エラー：'.$e->getMessage();
}
$dbh=null;//ここの位置でいいのかしら

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>ひと言掲示板</h1>
<?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
    <p class="success_message"><?php echo $_SESSION['success_message']; ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if( !empty($error_message) ): ?>
    <ul class="error_message">
        <?php foreach( $error_message as $value ): ?>
            <li>・<?php echo $value; ?></li>
        <?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post">
	<div>
		<label for="view_name">表示名</label>
		<input id="view_name" type="text" name="view_name" value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
	</div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"></textarea>
    </div>
    <div class="flex">
        <input type="submit" name="btn_submit" value="書き込む">
        <div class="to_admin"><a href="admin.php">管理画面へ</a></div>
    </div>
</form>

<hr>

<section>
<?php if( !empty($message_array) ){ ?>
<?php foreach( $message_array as $value ){ ?>
<article>
    <div class="info">
        <h2><?php echo $value['view_name']; ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
    </div>
    <p><?php echo nl2br($value['message']); ?></p>
</article>

<?php } ?>
<?php } ?>
</section>
</body>
</html>