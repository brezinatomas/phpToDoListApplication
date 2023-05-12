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
$categoryId='';
$categoryText='';


$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola textu
    $categoryText=trim(@$_POST['text']);
    if (empty($categoryText)){
        $errors['text']='Musíte zadat název kategorie.';
    }
    #endregion kontrola textu

    if (empty($errors)){
        #region uložení dat

            #region uložení nového příspěvku
            $saveQuery=$db->prepare('INSERT INTO categories (category_id, name) VALUES (NULL, :text);');
            $saveQuery->execute([
                ':text'=>$categoryText,
            ]);
            #endregion uložení nového příspěvku


        #endregion uložení dat
        #region přesměrování
        header('Location: index.php');
        exit();
        #endregion přesměrování
    }
    #endregion zpracování formuláře
}

//vložíme do stránek hlavičku
$pageTitle='Nová kategorie';


include 'header.inc.php';
?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $categoryId;?>" />


        <div class="form-group">
            <label for="text">Název kategorie:</label>
            <textarea name="text" id="text" required class="form-control <?php echo (!empty($errors['text'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($categoryText)?></textarea>
            <?php
            if (!empty($errors['text'])){
                echo '<div class="invalid-feedback">'.$errors['text'].'</div>';
            }
            ?>
        </div>


        <button type="submit" class="btn btn-primary">uložit</button>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'footer.inc.php';