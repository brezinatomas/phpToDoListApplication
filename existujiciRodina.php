<?php
//načteme připojení k databázi a inicializujeme session
require_once 'user.inc.php';

if(!isset($_SESSION["user_id"])){
    //uživatel není přihlášen => přesměrujeme ho na přihlašovací stránku
    header('Location: prihlaseni.php');
    die();
}

if(empty($currentUser) || ($currentUser['family_id']!=NULL)){
    header('Location: index.php');
    die();
}

$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola Id
    $family_id=@$_POST['family_id'];
    if (empty($family_id)){
        $errors['family_id']='Musíte zadat ID rodiny.';
    } else {
        $idQuery = $db->prepare('SELECT * FROM families WHERE family_id=:family_id LIMIT 1;');
        $idQuery->execute([
            ':family_id' => $family_id
        ]);
        if ($idQuery->rowCount() == 0) {
            $errors['family_id'] = 'Rodina s tímto ID bohužel neexistuje.';
        }
    }
    #endregion kontrola jména


    if (empty($errors)){

        $_SESSION['family_id']=$family_id;

        //uživatele rovnou přihlásíme
        $updateQuery=$db->prepare('UPDATE users SET family_id=:family_id WHERE user_id=:user_id LIMIT 1;');
        $updateQuery->execute([
            ':user_id'=>$_SESSION['user_id'],
            ':family_id'=>$_SESSION['family_id']
        ]);


        //přesměrování na homepage
        header('Location: index.php');
        exit();
    }

    #endregion zpracování formuláře
}

//vložíme do stránek patičku
$pageTitle='Připojení k rodině';
include 'header.inc.php';
?>

    <div class="login-dark" data-bs-theme="dark">
        <form method="post" class="big-form">
            <h2 style="padding-bottom: 35px;">Připojit se k existující rodině v TODO</h2>

            <div class="form-group">
                <label for="family_id">ID rodiny:</label>
                <input type="number" name="family_id" id="family_id" required class="form-control <?php echo (!empty($errors['family_id'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['family_id'] ?? ''); ?>"/>
                <?php
                echo (!empty($errors['family_id'])?'<div class="invalid-feedback">'.$errors['family_id'].'</div>':'');
                ?>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Chci se připojit k rodině</button>
                <a href="novaRodina.php" class="btn btn-secondary">Vytvořit novou rodinu</a>
            </div>
        </form>
    </div>

<?php
include 'footer.inc.php';