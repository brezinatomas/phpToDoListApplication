<?php

require_once 'user.inc.php';

if(!isset($_SESSION["user_id"])){
    //uživatel není přihlášen => přesměrujeme ho na přihlašovací stránku
    header('Location: prihlaseni.php');
    die();
}

if(empty($currentUser) || ($currentUser['role']!='admin')){
    die('Tato stránka je dostupná pouze administrátorům.');
}

if (!empty($_GET['id'])){

    $postQuery= $db->prepare('SELECT * FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) WHERE post_id=:id LIMIT 1;');
    $postQuery->execute([':id'=>$_GET['id']]);
    if ($post=$postQuery->fetch(PDO::FETCH_ASSOC)){
        if($post['family_id']!=$_SESSION['family_id']) {
            die('Tento úkol nebyl nalezen.');
        }
        $query = $db->prepare('DELETE FROM posts WHERE post_id=:id');
        $query->execute([
            ":id"=>$_GET['id']
        ]);
    }else{
        exit('Tento úkol neexistuje.');//tady by mohl být i lepší výpis chyby :)
    }
}

header('Location: index.php');


