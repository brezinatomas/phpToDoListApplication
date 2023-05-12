<?php
//načteme připojení k databázi a inicializujeme session
require_once 'user.inc.php';

if(!isset($_SESSION["user_id"])){
    //uživatel není přihlášen => přesměrujeme ho na přihlašovací stránku
    header('Location: prihlaseni.php');
    die();
}

//ověříme, jestli je uživatel v roli admin - pokud ne, tak mu zabráníme v přístupu
if(empty($currentUser) || ($currentUser['role']!='admin')){
    die('Tato stránka je dostupná pouze administrátorům.');
}

//pomocné proměnné pro přípravu dat do formuláře
$postId='';
$postUserName='';
$postCategory=(!empty($_REQUEST['category'])?intval($_REQUEST['category']):'');
$postText='';
$postPoznamka='';

#region načtení existujícího příspěvku z DB
if (!empty($_REQUEST['id'])){
    $postQuery=$db->prepare('SELECT * FROM posts WHERE post_id=:id LIMIT 1;');
    $postQuery->execute([':id'=>$_REQUEST['id']]);
    if ($post=$postQuery->fetch(PDO::FETCH_ASSOC)){
        //naplníme pomocné proměnné daty příspěvku
        $postId=$post['post_id'];
        $postUserName=$post['user_id'];
        $postCategory=$post['category_id'];
        $postText=$post['text'];
        $postPoznamka=$post['poznamka'];

    }else{
        exit('Tento úkol neexistuje.');//tady by mohl být i lepší výpis chyby :)
    }
}



#endregion načtení existujícího příspěvku z DB

$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola kategorie
    if (!empty($_POST['category'])){

        $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:category LIMIT 1;');
        $categoryQuery->execute([
            ':category'=>$_POST['category']
        ]);
        if ($categoryQuery->rowCount()==0){
            $errors['category']='Zvolená kategorie neexistuje!';
            $postCategory='';
        }else{
            $postCategory=$_POST['category'];
        }

    }else{
        $errors['category']='Musíte vybrat kategorii.';
    }

    if (!empty($_POST['username'])){

        $userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:username LIMIT 1;');
        $userQuery->execute([
            ':username'=>$_POST['username']
        ]);
        if ($userQuery->rowCount()==0){
            $errors['username']='Zvolený uživatel neexistuje!';
            $postUserName='';
        }else{
            $postUserName=$_POST['username'];
        }

    }else{
        $errors['username']='Musíte vybrat uživatele.';
    }
    #endregion kontrola kategorie
    #region kontrola textu
    $postText=trim(@$_POST['text']);
    $postPoznamka=trim(@$_POST['poznamka']);
    if (empty($postText)){
        $errors['text']='Musíte zadat text příspěvku.';
    }
    #endregion kontrola textu

    if (empty($errors)){
        #region uložení dat

        if ($postId){
            #region aktualizace existujícího příspěvku
            $saveQuery=$db->prepare('UPDATE posts SET category_id=:category, text=:text, poznamka=:poznamka, user_id=:username WHERE post_id=:id LIMIT 1;');
            $saveQuery->execute([
                ':category'=>$postCategory,
                ':text'=>$postText,
                ':poznamka'=>$postPoznamka,
                ':id'=>$postId,
                ':username'=>$postUserName
            ]);
            #endregion aktualizace existujícího příspěvku
        }else{
            #region uložení nového příspěvku
            $saveQuery=$db->prepare('INSERT INTO posts (user_id, category_id, text, poznamka) VALUES (:username, :category, :text, :poznamka);');
            $saveQuery->execute([
                ':username'=>$postUserName,
                ':category'=>$postCategory,
                ':text'=>$postText,
                ':poznamka'=>$postPoznamka
            ]);
            #endregion uložení nového příspěvku
        }

        #endregion uložení dat
        #region přesměrování
        header('Location: index.php');
        exit();
        #endregion přesměrování
    }
    #endregion zpracování formuláře
}

//vložíme do stránek hlavičku
if ($postId){
    $pageTitle='Úprava úkolu';
}else{
    $pageTitle='Nový úkol';
}

include 'header.inc.php';
?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $postId;?>" />

        <div class="form-group">
            <label for="category">Kategorie:</label>
            <select name="category" id="category" required class="form-control <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $categoryQuery=$db->prepare('SELECT * FROM categories ORDER BY name;');
                $categoryQuery->execute();
                $categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($categories)){
                    foreach ($categories as $category){
                        echo '<option value="'.$category['category_id'].'" '.($category['category_id']==$postCategory?'selected="selected"':'').'>'.htmlspecialchars($category['name']).'</option>';
                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['category'])){
                echo '<div class="invalid-feedback">'.$errors['category'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="username">Uživatel:</label>
            <select name="username" id="username" required class="form-control <?php echo (!empty($errors['username'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $userQuery=$db->prepare('SELECT * FROM users ORDER BY name;');
                $userQuery->execute();
                $users=$userQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($users)){
                    foreach ($users as $user){
                        echo '<option value="'.$user['user_id'].'" '.($user['user_id']==$postUserName?'selected="selected"':'').'>'.htmlspecialchars($user['name']).'</option>';
                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['username'])){
                echo '<div class="invalid-feedback">'.$errors['username'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="text">Text příspěvku:</label>
            <textarea name="text" id="text" required class="form-control <?php echo (!empty($errors['text'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($postText)?></textarea>
            <?php
            if (!empty($errors['text'])){
                echo '<div class="invalid-feedback">'.$errors['text'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="poznamka">Poznámka k úkolu:</label>
            <textarea name="poznamka" id="poznamka" class="form-control <?php echo (!empty($errors['poznamka'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($postPoznamka)?></textarea>
            <?php
            if (!empty($errors['poznamka'])){
                echo '<div class="invalid-feedback">'.$errors['poznamka'].'</div>';
            }
            ?>
        </div>

        <button type="submit" class="btn btn-primary">uložit</button>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'footer.inc.php';