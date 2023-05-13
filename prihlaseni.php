<?php
//načteme připojení k databázi a inicializujeme session
require_once 'user.inc.php';

if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: index.php');
    exit();
}

$errors=false;
if (!empty($_POST)){
    #region zpracování formuláře
    $userQuery=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
    $userQuery->execute([
        ':email'=>trim($_POST['email'])
    ]);
    if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)){

        if (password_verify($_POST['password'],$user['password'])){
            //heslo je platné => přihlásíme uživatele
            $_SESSION['user_id']=$user['user_id'];
            $_SESSION['user_name']=$user['name'];
            $_SESSION['family_id']=$user['family_id'];
            header('Location: index.php');
            exit();
        }else{
            $errors=true;
        }

    }else{
        $errors=true;
    }
    #endregion zpracování formuláře
}

//vložíme do stránek patičku
$pageTitle='Vítejte';
include 'header.inc.php';
?>

    <div class="login-dark" data-bs-theme="dark">
        <form method="post" class="big-form">
            <h2>Vítejte v aplikaci TODO</h2>
            <div class="form-icon">
                <i class="bi bi-lock"></i>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['email'] ?? ''); ?>"/>
                <?php
                echo ($errors?'<div class="invalid-feedback">Neplatná kombinace přihlašovacího e-mailu a hesla.</div>':'');
                ?>
            </div>
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" name="password" id="password" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" />
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Přihlásit se</button>
                <a href="registrace.php" class="btn btn-secondary">Registrace zde</a>
            </div>
            <a href="#" class="forgot">Zapomenuté uživatelské jméno či heslo?</a>
        </form>
    </div>

<?php
include 'footer.inc.php';
