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
$postUserName=array();
$postCategory=(!empty($_REQUEST['category'])?intval($_REQUEST['category']):'');
$postText='';
$postPoznamka='';
$postDate='';

#region načtení existujícího příspěvku z DB
if (!empty($_REQUEST['id'])){
    $postQuery=$db->prepare('SELECT * FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) WHERE post_id=:id LIMIT 1');
    $postQuery->execute([':id'=>$_REQUEST['id']]);
    if ($post=$postQuery->fetch(PDO::FETCH_ASSOC)){
        if($post['family_id']!=$_SESSION['family_id']) {
            die('Tento úkol nebyl nalezen.');
        }
        if($post['splneno']!= 1) {
            header('Location: index.php');
            die();
        }
        //naplníme pomocné proměnné daty příspěvku
        $postId=$post['post_id'];
        $postCategory=$post['category_id'];
        $postText=$post['text'];
        $postPoznamka=$post['poznamka'];

        $userNamesQuery=$db->prepare('SELECT user_id FROM posts JOIN users_posts USING (post_id) JOIN users USING (user_id) WHERE post_id=:id');
        $userNamesQuery->execute([':id'=>$_REQUEST['id']]);
        $userNameForSelect=$userNamesQuery->fetchAll(PDO::FETCH_ASSOC);
        $ids = array_column($userNameForSelect, 'user_id');

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

        foreach ($_POST['username'] as  $selectedUsernames) {
            $userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:username');
            $userQuery->execute([
                ':username'=>$selectedUsernames
            ]);
            if ($userQuery->rowCount()==0){
                $errors['username']='Zvolený uživatel neexistuje!';
                $postUserName='';
            }else{
                $postUserName[]=$selectedUsernames;
            }
        }

    }else{
        $errors['username']='Musíte vybrat uživatele.';
    }
    #endregion kontrola kategorie
    #region kontrola textu
    $postText=trim(@$_POST['text']);
    $postPoznamka=trim(@$_POST['poznamka']);
    $postDate=trim(@$_POST['updated']);
    if (empty($postText)){
        $errors['text']='Musíte zadat text příspěvku.';
    }
    if (empty($postDate)){
        $errors['updated']='Musíte zadat datum splnění úkolu.';
    }
    #endregion kontrola textu

    if (empty($errors)){
        #region uložení dat

        if ($postId){
            #region aktualizace existujícího příspěvku
            $saveQuery=$db->prepare('UPDATE posts SET category_id=:category, text=:text, poznamka=:poznamka, updated=:updated WHERE post_id=:id LIMIT 1;');
            $saveQuery->execute([
                ':category'=>$postCategory,
                ':text'=>$postText,
                ':poznamka'=>$postPoznamka,
                ':id'=>$postId,
                ':updated'=>$postDate
            ]);

            $deleteUserQuery=$db->prepare('DELETE FROM users_posts WHERE post_id =:post_id');
            $deleteUserQuery->execute([
                ':post_id'=>$postId
            ]);

            foreach ($postUserName as $newUserName) {
                $saveUserQuery=$db->prepare('INSERT INTO users_posts (user_id, post_id) VALUES (:user_id,:post_id )');
                $saveUserQuery->execute([
                    ':user_id'=>$newUserName,
                    ':post_id'=>$postId
                ]);
            }
            #endregion aktualizace existujícího příspěvku
        }else{
            #region uložení nového příspěvku
            $saveQuery=$db->prepare('INSERT INTO posts (category_id, text, poznamka, updated) VALUES (:category, :text, :poznamka, :updated);');
            $saveQuery->execute([
                ':category'=>$postCategory,
                ':text'=>$postText,
                ':poznamka'=>$postPoznamka,
                ':updated'=>$postDate
            ]);
            $lastInsertIdPost=$db->lastInsertId();

            foreach ($postUserName as $newUserName) {
                $saveUserQuery=$db->prepare('INSERT INTO users_posts (user_id, post_id) VALUES (:user_id,:post_id )');
                $saveUserQuery->execute([
                    ':user_id'=>$newUserName,
                    ':post_id'=>$lastInsertIdPost
                ]);
            }
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
    <div class="login-dark" data-bs-theme="dark">
    <form method="post" class="big-form">
        <h2 style="padding-bottom: 35px;">Vyplňte údaje o úkolu</h2>
        <input type="hidden" name="id" value="<?php echo $postId;?>" />

        <div class="form-group">
            <label for="category">Kategorie:</label>
            <select name="category" id="category" required class="form-control <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $categoryQuery=$db->prepare('SELECT * FROM categories WHERE family_id=:family_id ORDER BY name;');
                $categoryQuery->execute([
                        ':family_id'=>$_SESSION['family_id']
                ]);
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
            <select name="username[]" multiple id="username" required class="form-control <?php echo (!empty($errors['username'])?'is-invalid':''); ?>">
                <?php
                $userQuery=$db->prepare('SELECT * FROM users WHERE family_id=:family_id ORDER BY name;');
                $userQuery->execute([
                    ':family_id'=>$_SESSION['family_id']
                ]);
                $users=$userQuery->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($users)){

                    foreach ($users as $user){
                      if ($postId) {
                          echo '<option value="'.$user['user_id'].'" '.(in_array($user['user_id'],$ids)?'selected="selected"':'').'>'.htmlspecialchars($user['name']).'</option>';
                      } else {
                          echo '<option value="'.$user['user_id'].'">'.htmlspecialchars($user['name']).'</option>';
                      }
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
            <label for="updated">Datum splnění:</label>
            <input type="date" name="updated" id="updated" required class="form-control <?php echo (!empty($errors['updated'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($postDate)?></input>
            <?php
            if (!empty($errors['updated'])){
                echo '<div class="invalid-feedback">'.$errors['updated'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="text">Náplň úkolu:</label>
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
        <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Uložit</button>
        <a href="index.php" class="btn btn-outline-danger">Zrušit</a>
        </div>
    </form>
    </div>
<?php
//vložíme do stránek patičku
include 'footer.inc.php';