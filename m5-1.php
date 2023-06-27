<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>
    <?php
    // データベース接続の設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    // テーブル作成のクエリ実行
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
        . " ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "date TEXT,"
        . "password char(32)"
        . ");";
    $stmt = $pdo->query($sql);
    
    // 投稿フォームが送信された場合の処理
    if(!empty($_POST['name']) && !empty($_POST["comment"]) && !empty($_POST["password"])){
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $password = $_POST['password'];
        $date = date("Y/m/d/h:i:s");

        // データの挿入
        $sql = "INSERT INTO tbtest (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    // 削除フォームが送信された場合の処理
    if(!empty($_POST['delete'])){
        $delete_id = $_POST['delete_id'];
        $delete_password = $_POST['delete_password'];
        
        // パスワードのチェック
        $sql = "SELECT * FROM tbtest WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if ($row && $row['password'] == $delete_password){
            // データの削除
            $sql = "DELETE FROM tbtest WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    
    // 編集フォームが送信された場合の処理
    if(!empty($_POST['edit'])){
        $edit_id = $_POST['edit_id'];
        $edit_password = $_POST['edit_password'];
        
        // パスワードのチェック
        $sql = "SELECT * FROM tbtest WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $edit_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if ($row && $row['password'] == $edit_password){
            // 編集対象のデータをフォームに表示
            $edit_name = $row['name'];
            $edit_comment = $row['comment'];
        }
    }
    
    // 投稿フォームが送信された場合の処理
    if(!empty($_POST['update'])){
        $update_id = $_POST['update_id'];
        $update_name = $_POST['name'];
        $update_comment = $_POST['comment'];
        $update_password = $_POST['password'];
        
        // パスワードのチェック
        $sql = "SELECT * FROM tbtest WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $update_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if($row && $row['password'] == $update_password){
            // データの更新
            $sql = "UPDATE tbtest SET name = :name, comment = :comment WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $update_name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $update_comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $update_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    
    ?>
    <h2>投稿フォーム</h2>
    <form action="" method="POST">
        <label for="name">名前:</label>
        <input type="text" name="name" id="name" value="<?php echo isset($edit_name) ? $edit_name : ''; ?>"><br>
        <label for="comment">コメント:</label>
        <input type="text" name="comment" id="comment" value="<?php echo isset($edit_comment) ? $edit_comment : ''; ?>"><br>
        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password"><br>
        <input type="submit" name="submit" value="投稿">
        <?php if (isset($edit_name)): ?>
            <input type="hidden" name="update_id" value="<?php echo $edit_id; ?>">
            <input type="submit" name="update" value="更新">
        <?php endif; ?>
    </form>
    
    <h2>削除フォーム</h2>
    <form action="" method="POST">
        <label for="delete_id">削除対象番号:</label>
        <input type="number" name="delete_id" id="delete_id"><br>
        <label for="delete_password">パスワード:</label>
        <input type="password" name="delete_password" id="delete_password"><br>
        <input type="submit" name="delete" value="削除">
    </form>
    
    <h2>編集フォーム</h2>
    <form action="" method="POST">
        <label for="edit_id">編集対象番号:</label>
        <input type="number" name="edit_id" id="edit_id"><br>
        <label for="edit_password">パスワード:</label>
        <input type="password"name="edit_password" id="edit_password"><br>
        <input type="submit" name="edit" value="編集">
    </form>
    
    <?php
    // データの取得と表示
    $sql = "SELECT * FROM tbtest";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(!empty($results)){
        echo "<h2>投稿一覧</h2>";
        echo "<hr>";
        echo "<table>";
        echo "<tr><th>ID</th><th>名前</th><th>コメント</th><th>日付</th></tr>";
        foreach ($results as $row){
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['name']."</td>";
            echo "<td>".$row['comment']."</td>";
            echo "<td>".$row['date']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>
</html>