<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>mission_6-1</title>
<style>
    form {
        text-align: center;
    }
    
    body {
        background-color: #98fb98; /* palegreenのコードを指定 */
    }
    
    h2 {
        text-align: center;
    }
    
    h1 {
        text-align: center;
    }
    
    span {
        background-color: white;
        padding: 10px 150px;
    }
    
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        /*height: 100vh;*/
    }

    .form-container {
        padding: 20px;
        margin: 10px;
        width: 300px;
    }
    
    .message.success {
        text-align: center;
        background-color: lightgreen;
        margin: 30px 500px;
    }
    
    .message.failure {
        text-align: center;
        background-color: red;
        padding: 10px 10px;
    }
</style>
</head>
<body>
    <?php
        session_start(); // Start the session
        // データベース接続
        $dsn = 'mysql:dbname=データベース名;host=localhost';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        // ユーザー名とパスワードを保存するテーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS users"
        ."("
        .   "id INT AUTO_INCREMENT PRIMARY KEY,"
        .   "username VARCHAR(32) NOT NULL UNIQUE,"
        .   "password VARCHAR(255) NOT NULL"
        .");";
        $pdo->exec($sql);
        
        // ユーザーの登録処理
        if(isset($_POST['register'])){
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            // パスワードをハッシュ化する
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashedPassword]);
            $_SESSION['message'] = array(
                'text' => "ユーザーが登録されました。",
                'type' => 'success'
            );
            
            // リダイレクト
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // ログイン処理
        if(isset($_POST['login'])){
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            // ユーザー名をもとにデータベースからユーザー情報を取得します
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // パスワードの照合を行います
            if($user && password_verify($password, $user['password'])){
                // ログイン成功時の処理
                echo "ログインに成功しました。";
                // セッションを開始
                session_start();
                
                // ログイン成功時の処理
                $_SESSION['user_id'] = $user['id']; // ユーザーIDなどの情報をセッションに保存
                $_SESSION['user_name'] = $user['username'];
                
                // リダイレクト
                header('Location: m6-1_mypage.php');
                exit;
            }
            
            else{
                // ログイン失敗時の処理
                $_SESSION['message'] = array(
                    'text' => "ユーザー名またはパスワードが正しくありません。",
                    'type' => 'failure'
                );
                
                // リダイレクト
                header('Location: m6-1.php');
                exit;
            }
        }
    ?>
    
    <h1><span>Web版　サッカーノート</span></h1>
    
    <?php
        // メッセージの表示
        if(isset($_SESSION['message'])){
            echo "<p class=\"message {$_SESSION['message']['type']}\">{$_SESSION['message']['text']}</p>";
            unset($_SESSION['message']); // メッセージをクリア
        }
    ?>
    
    <div class="container">
        <div class="form-container">
            <!-- ユーザーの登録フォーム -->
            <div style="background-color: skyblue"><h2>選手登録</h2></div>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="text" name="username" placeholder="選手名" required><br>
                <input type="password" name="password" placeholder="パスワード" required><br>
                <input type="submit" name="register" value="登録">
            </form>
        </div>
        <div class="form-container">
            <!-- ログインフォーム -->
            <div style="background-color: skyblue"><h2>ログイン</h2></div>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="text" name="username" placeholder="選手名" required><br>
                <input type="password" name="password" placeholder="パスワード" required><br>
                <input type="submit" name="login" value="ログイン">
            </form>
        </div>
    </div>
</body>
</html>
