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

if (!empty($_GET['category'])){

    $postQuery= $db->prepare('SELECT * FROM categories WHERE category_id=:category LIMIT 1;');
    $postQuery->execute([':category'=>$_GET['category']]);
    if ($post=$postQuery->fetch(PDO::FETCH_ASSOC)){
        if($post['family_id']!=$_SESSION['family_id']) {
            die('Tato kategorie nebyla nalezena.');
        }
        $query = $db->prepare('DELETE FROM categories WHERE category_id=:category');
        $query->execute([
            ":category"=>$_GET['category']
        ]);
    }else{
        exit('Tato kategorie neexistuje.');//tady by mohl být i lepší výpis chyby :)
    }
}


header('Location: index.php');


