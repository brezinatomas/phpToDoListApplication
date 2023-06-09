<?php
//načteme připojení k databázi a inicializujeme session
require_once 'user.inc.php';

if(!isset($_SESSION["user_id"])){
    //uživatel není přihlášen => přesměrujeme ho na přihlašovací stránku
    header('Location: prihlaseni.php');
    die();
}

if(!empty($currentUser) && ($currentUser['family_id']==NULL)){
    header('Location: novaRodina.php');
    die();
}

$pageTitle='Úkoly pro dnešní den';
//vložíme do stránek hlavičku
include 'header.inc.php';

if (!empty($_GET['category'])){
    #region výběr příspěvků z konkrétní kategorie
    $query = $db->prepare('SELECT DISTINCT posts.*, categories.name AS category_name FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) JOIN categories USING (category_id) WHERE posts.category_id=:category AND users.family_id=:family_id ORDER BY updated DESC;');
    $query->execute([
        ':category'=>$_GET['category'],
        ':family_id'=>$_SESSION['family_id']
    ]);
    #endregion výběr příspěvků z konkrétní kategorie
}else{
    #region výběr příspěvků bez ohledu na kategorii
    $query = $db->prepare('SELECT DISTINCT posts.*, categories.name AS category_name FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) JOIN categories USING (category_id) WHERE users.family_id=:family_id ORDER BY updated DESC;');
    $query->execute([
        ':family_id'=>$_SESSION['family_id']
    ]);
    #region výběr příspěvků bez ohledu na kategorii
}

if (!empty($_GET['mojeukoly'])){
    #region výběr příspěvků z konkrétní kategorie
    $query = $db->prepare('SELECT DISTINCT posts.*, categories.name AS category_name FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) JOIN categories USING (category_id) WHERE user_id=:user_id ORDER BY updated DESC;');
    $query->execute([
        ':user_id'=>$_SESSION['user_id']
    ]);
}

echo '<div class="login-dark">
    <div class="container">
        <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
            <div class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-light">
                <span><i class="bi bi-bookmark-heart fs-3"></i></span>
                <span class="fs-3 ms-2">TODO Aplikace</span>
            </div>
            <a href="index.php" class="col-12 col-md-auto mb-2 mb-md-0 text-light text-decoration-none text-center">';
echo  '<span class="fs-3">';
echo  htmlspecialchars($_SESSION['family_name']). ' ID:'.htmlspecialchars($_SESSION['family_id']).'- ' .htmlspecialchars($_SESSION['user_name']).'</span>
            </a>
            <div class="col-md-3 text-end">';
if(!empty($currentUser) && ($currentUser['role']=='admin')){
    echo '<a href="zmenaUkolu.php'.(!empty($_GET['category'])?'?category='.htmlspecialchars($_GET['category']):'').'" class="btn btn-light mb-1">Přidat úkol</a>';
}
echo  '<form style="display: inline-block" method="get">
            <input type="submit" name="mojeukoly" id="mojeukoly" class="button btn btn-success ms-1 mb-1" value="Moje úkoly" />
        </form>
                <a href="odhlasit.php" class="btn btn-secondary mb-1">Odhlásit se</a>
            </div>
        </header>
    </div>

    <div class="container ukoly-container">

        <form method="get" id="categoryFilterForm" class="text-light pt-2" data-bs-theme="dark">
            <div class="form-group row" style="max-width: 500px;">
                <label class="col-2" for="category">Kategorie:</label>
                <div class="col-4">
                    <select name="category" id="category" onchange="document.getElementById(\'categoryFilterForm\').submit();" class="form-select form-select-sm">
                        <option value="">Nerozhoduje</option>';
$categoriesQuery=$db->prepare('SELECT * FROM categories WHERE family_id=:family_id ORDER BY name;');
$categoriesQuery->execute([':family_id'=>$_SESSION['family_id']]);
$categories=$categoriesQuery->fetchAll(PDO::FETCH_ASSOC);
if (!empty($categories)){
    foreach ($categories as $category){
        echo '<option value="'.$category['category_id'].'"';//u category_id nemusí být ošetření speciálních znaků, protože jde o číslo
        if ($category['category_id']==@$_GET['category']){
            echo ' selected="selected" ';
        }
        echo '>'.htmlspecialchars($category['name']).'</option>';
    }
}

echo  '</select>
                <input type="submit" value="OK" class="d-none" />
                </div>';
if(!empty($currentUser) && ($currentUser['role']=='admin')){
    echo '<a href="odstranitKategorii.php?category='.@$_GET['category'].'" class="btn btn-outline-danger btn-sm col-2 me-1">Smazat</a>';
    echo '<a href="zmenaKategorie.php" class="btn btn-outline-light btn-sm col-3 ms-1">Nová kategorie</a>';
}
echo   '</div>
        </form>';

$posts = $query->fetchAll(PDO::FETCH_ASSOC);
if (!empty($posts)){
    #region výpis příspěvků
    echo '<div class="row justify-content-evenly mt-3 pb-3">';
    foreach ($posts as $post) {
        if ($post['splneno'] != 1) {
            echo '<article class="col-10 col-md-5 col-lg-3 col-xxl-3 border-0 mx-1 my-1 px-2 py-1 bg-light rounded" style="background-color: chartreuse !important;">';
        } else {
            echo '<article class="col-10 col-md-5 col-lg-3 col-xxl-3 border-0 mx-1 my-1 px-2 py-1 bg-light rounded">';
        }
        echo '<div class="d-flex justify-content-between"><strong>';
        $userNameQuery=$db->prepare('SELECT name FROM users JOIN users_posts USING (user_id) JOIN posts USING (post_id) WHERE post_id=:post_id ORDER BY name;');
        $userNameQuery->execute([':post_id'=>$post['post_id']]);
        $userNames=$userNameQuery->fetchAll(PDO::FETCH_ASSOC);
        foreach ($userNames as $userName) {
            echo htmlspecialchars($userName['name']);
            echo ' ';
        }
        echo ' - ';
        echo  date('d.m.Y', strtotime($post['updated'])) . '</strong>';
        echo '<span class="badge rounded-pill text-bg-warning mt-1">' . htmlspecialchars($post['category_name']) . '</span>
                </div>';
        echo '<div>' . nl2br(htmlspecialchars($post['text'])) . '</div>';
        echo '<div class="small text-muted">' . nl2br(htmlspecialchars($post['poznamka'])) . '</div>';
        if($post['splneno'] == 1){
            echo '<a href="splnitUkol.php?id='.$post['post_id'].'" class="btn btn-outline-primary btn-sm m-1">Splnit</a>';
            echo '<a href="prevzitUkol.php?id='.$post['post_id'].'" class="btn btn-outline-success btn-sm m-1">Převzít</a>';
            if(!empty($currentUser) && ($currentUser['role']=='admin')){
                echo '<a href="zmenaUkolu.php?id='.$post['post_id'].'" class="btn btn-outline-secondary btn-sm m-1">Upravit</a>';
            }
        }
        if(!empty($currentUser) && ($currentUser['role']=='admin')){
            echo '<a href="odstranitUkol.php?id='.$post['post_id'].'" class="btn btn-outline-danger btn-sm m-1">Smazat</a>';
        }

        echo '</article>';
    }
    echo '</div>';
}else{
    echo '<div class="alert alert-info mt-4 text-center mx-auto" style="max-width: 600px">V tuto chvíli tu nejsou žádné úkoly.</div>';
}
echo '</div>';
echo '</div>';

include 'footer.inc.php';