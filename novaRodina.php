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
    #region kontrola jména
    $family_name=trim(@$_POST['family_name']);
    if (empty($family_name)){
        $errors['family_name']='Musíte zadat název pro svoji rodinu.';
    }
    #endregion kontrola jména


    if (empty($errors)){
        //zaregistrování rodiny

        $query=$db->prepare('INSERT INTO families (family_name) VALUES (:family_name);');
        $query->execute([
            ':family_name'=>$family_name
        ]);

        $_SESSION['family_id']=$db->lastInsertId();
        $_SESSION['family_name']=$family_name;

        //uživatele rovnou přihlásíme
        $updateQuery=$db->prepare('UPDATE users SET family_id=:family_id, role="admin" WHERE user_id=:user_id LIMIT 1;');
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
$pageTitle='Vytvoření nové rodiny';
include 'header.inc.php';
?>

    <div class="login-dark" data-bs-theme="dark">
        <form method="post" class="big-form">
            <h2 style="padding-bottom: 35px;">Vytvořit novou rodinu v TODO</h2>

            <div class="form-group">
                <label for="family_name">Název rodiny:</label>
                <input type="text" name="family_name" id="family_name" required class="form-control <?php echo (!empty($errors['family_name'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['family_name'] ?? ''); ?>"/>
                <?php
                echo (!empty($errors['family_name'])?'<div class="invalid-feedback">'.$errors['family_name'].'</div>':'');
                ?>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Vytvořit rodinu</button>
                <a href="existujiciRodina.php" class="btn btn-secondary">Chci se připojit k rodině</a>
            </div>
        </form>
    </div>

<?php
include 'footer.inc.php';