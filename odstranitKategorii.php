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
    $query = $db->prepare('DELETE FROM categories WHERE category_id=:category');
    $query->execute([
        ":category"=>$_GET['category']
    ]);
}

header('Location: index.php');


