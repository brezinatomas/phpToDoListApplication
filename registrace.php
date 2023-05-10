<?php
//načteme připojení k databázi a inicializujeme session
require_once 'user.inc.php';

if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se registroval
    header('Location: index.php');
    exit();
}

$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola jména
    $name=trim(@$_POST['name']);
    if (empty($name)){
        $errors['name']='Musíte zadat své jméno či přezdívku.';
    }
    #endregion kontrola jména

    #region kontrola emailu
    $email=trim(@$_POST['email']);
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $errors['email']='Musíte zadat platnou e-mailovou adresu.';
    }else{
        //kontrola, jestli již není e-mail registrovaný
        $mailQuery=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
        $mailQuery->execute([
            ':email'=>$email
        ]);
        if ($mailQuery->rowCount()>0){
            $errors['email']='Uživatelský účet s touto e-mailovou adresou již existuje.';
        }
    }
    #endregion kontrola emailu

    #region kontrola hesla
    if (empty($_POST['password']) || (strlen($_POST['password'])<5)){
        $errors['password']='Musíte zadat heslo o délce alespoň 5 znaků.';
    }
    if ($_POST['password']!=$_POST['password2']){
        $errors['password2']='Zadaná hesla se neshodují.';
    }
    #endregion kontrola hesla

    if (empty($errors)){
        //zaregistrování uživatele
        $password=password_hash($_POST['password'],PASSWORD_DEFAULT);

        $query=$db->prepare('INSERT INTO users (name, email, password, active) VALUES (:name, :email, :password, 1);');
        $query->execute([
            ':name'=>$name,
            ':email'=>$email,
            ':password'=>$password
        ]);

        //uživatele rovnou přihlásíme
        $_SESSION['user_id']=$db->lastInsertId();
        $_SESSION['user_name']=$name;

        //přesměrování na homepage
        header('Location: index.php');
        exit();
    }

    #endregion zpracování formuláře
}

//vložíme do stránek patičku
$pageTitle='Registrace';
include 'header.inc.php';
?>

    <div class="login-dark" data-bs-theme="dark">
        <form method="post" class="big-form">
            <h2>Vytvořit nový účet TODO</h2>
            <div class="form-icon">
                <i class="bi bi-lock"></i>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="form-control <?php echo (!empty($errors['email'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['email'] ?? ''); ?>"/>
                <?php
                echo (!empty($errors['email'])?'<div class="invalid-feedback">'.$errors['email'].'</div>':'');
                ?>
            </div>
            <div class="form-group">
                <label for="name">Přezdívka:</label>
                <input type="text" name="name" id="name" required class="form-control <?php echo (!empty($errors['name'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['name'] ?? ''); ?>"/>
                <?php
                echo (!empty($errors['name'])?'<div class="invalid-feedback">'.$errors['name'].'</div>':'');
                ?>
            </div>
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" name="password" id="password" required class="form-control <?php echo (!empty($errors['password'])?'is-invalid':''); ?>" />
                <?php
                echo (!empty($errors['password'])?'<div class="invalid-feedback">'.$errors['password'].'</div>':'');
                ?>
            </div>
            <div class="form-group">
                <label for="password2">Potvrzení hesla:</label>
                <input type="password" name="password2" id="password2" required class="form-control <?php echo (!empty($errors['password2'])?'is-invalid':''); ?>" />
                <?php
                echo (!empty($errors['password2'])?'<div class="invalid-feedback">'.$errors['password2'].'</div>':'');
                ?>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Registrovat se</button>
                <a href="prihlaseni.php" class="btn btn-secondary">Již mám učet</a>
            </div>
        </form>
    </div>

<?php
include 'footer.inc.php';