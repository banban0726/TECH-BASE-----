<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>m6-1_mypage</title>
<style>
    h1 {
        text-align: center;
        /*background-color: skyblue;*/
    }
    
    h2 {
        text-align: center;
        /*background-color: skyblue;*/
    }
    
    div {
        text-align: center;
    }
    
    form {
        padding: 5px;
        text-align: center;
    }
    
    p {
        text-align: center;
        /*background-color: white;*/
    }
    
    span {
        text-align: center;
        padding: 5px 150px;
    }
    
    /*li {
        text-align: center;
    }*/
    
    body {
        background-color: #98fb98; /* palegreenのコードを指定 */
    }
</style>
</head>
<body>
    <?php
        // セッションの有効期限を設定
        $timeout = 30 * 60; // タイムアウト時間（秒）
        
        // セッションの設定
        ini_set('session.gc_maxlifetime', $timeout);
        session_set_cookie_params($timeout);
        
        // セッションを開始
        session_start();
        
        // タイムアウトチェック
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout){
            // セッションがタイムアウトした場合の処理
            session_unset(); // セッションの変数をクリア
            session_destroy(); // セッションを破棄
            
            // ログアウト後のリダイレクトなどの処理を追加
            header('Location: m6-1.php');
            exit;
        } else {
            // セッションがタイムアウトしていない場合は、タイムスタンプを更新
            $_SESSION['last_activity'] = time();
        }
        
        // セッションにユーザーIDが保存されているか確認
        if (!isset($_SESSION['user_id'])){
            // ユーザーIDが保存されていない場合はログインしていないので、ログインページにリダイレクト
            header('Location: m6-1.php');
            exit;
        }
        
        // ユーザーIDを取得
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        
        // データベース接続
        $dsn = 'mysql:dbname=データベース名;host=localhost';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        // ユーザーIDとユーザーの書き込みとその日時を記録するテーブルを作る
        $sql = "CREATE TABLE IF NOT EXISTS mypage"
        ."("
        .   "text_id INT AUTO_INCREMENT PRIMARY KEY,"
        .   "id INT,"
        .   "comment TEXT,"
        .   "date TEXT"
        .");";
        $pdo->exec($sql);
        
        // 日記投稿処理
        if (isset($_POST['post'])) {
            // 重複投稿のチェック
            //if (!isset($_SESSION['last_post']) || $_SESSION['last_post'] !== $_POST['comment']) {
                $comment = $_POST['comment'];
                $date = date("Y/m/d/h:i:s");
                
                // データベースに投稿を保存
                $stmt = $pdo->prepare("INSERT INTO mypage (id, comment, date) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $comment, $date]);
                
                // 最後の投稿をセッションに保存
                $_SESSION['last_post'] = $_POST['comment'];
                
                // リダイレクト
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            //}
            
            //else {
                // 重複投稿の場合は何もしない
                //echo "重複投稿です。";
            //}
        }
        
        // ユーザーの投稿を取得する
        //$stmt = $pdo->prepare("SELECT * FROM mypage WHERE id = ?");
        //$stmt->execute([$user_id]);
        //$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 投稿を日付順に入れ替えて取得
        $stmt = $pdo->prepare("SELECT * FROM mypage WHERE id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ログアウト処理
        if (isset($_POST['logout'])){
            // セッションを破棄してログアウト
            session_destroy();
            
            // リダイレクト
            header('Location: m6-1.php');
            exit;
        }
        
        //削除処理
        if (isset($_POST['delete']) && isset($_POST['delete_post_id'])) {
            $deletePostId = $_POST['delete_post_id'];
            
            // 削除の処理を実行
            $stmt = $pdo->prepare("DELETE FROM mypage WHERE text_id = ? AND id = ?");
            $stmt->execute([$deletePostId, $user_id]);
            
            // 削除後にリダイレクト
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    ?>
    
    <h1><span style="background-color: white">選手用ページ</span></h1>
    <!-- ユーザー固有のコンテンツを追加 -->
    <p><?php echo $user_name; ?>のサッカーノート</p>
    
    <!-- 日記投稿フォーム -->
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <textarea name="comment" placeholder="今日の練習内容を記入" required></textarea><br>
        <input type="submit" name="post" value="投稿">
    </form>
    
    <h2><span style="background-color: skyblue">投稿一覧</span></h2>
    <div style="background-color: white">
        <?php
            foreach ($posts as $post){
                echo "<p>" . nl2br($post['comment']) . "</p>"; // 改行を<br>に変換して表示
                echo "<p>" . $post['date'] . "</p>";
                //echo $post['text_id'];
                // 削除ボタン
                echo "<form method='POST' action=''>"; // 削除フォーム
                echo "<input type='hidden' name='delete_post_id' value='" . $post['text_id'] . "'>";
                echo "<input type='submit' name='delete' value='削除'>";
                echo "</form>";
            }
        ?>
    </div>
    
    <!-- ログアウトリンク -->
    <form method="POST" action="">
        <input type="submit" name="logout" value="ログアウト">
    </form>
</body>
</html>