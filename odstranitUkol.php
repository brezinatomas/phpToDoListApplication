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
    $query = $db->prepare('DELETE FROM posts WHERE post_id=:id');
    $query->execute([
        ":id"=>$_GET['id']
    ]);
}

header('Location: index.php');


