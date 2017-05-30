<?php

// Routes
function initDb() {
    try {
        return new PDO('mysql:host=localhost;dbname=ecolight', 'root', '');
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}

////////////////////   Index   /////////////////////////////////////////////////
$dbh = initDb();
$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

///////////////////   Enregistrement   /////////////////////////////////////////

$app->get('/register', function ($request, $response, $args) {
    // Render index view
    print_r($_SESSION['error']);
    $displayTemplate = $this->renderer->render($response, 'register.phtml', $args);

    unset($_SESSION['error']);
    return $displayTemplate;
});

$app->post('/doRegister', function ($request, $response, $args) {
    // if session redirect to dashboard
    $profil = [
        ':login' => $request->getParam('login'),
        ':password' => $request->getParam('password'),
        ':password_confirm' => $request->getParam('password_confirm'),
        ':email' => $request->getParam('email'),
        ':nom' => $request->getParam('nom'),
        ':prenom' => $request->getParam('prenom'),
        ':adresse' => $request->getParam('addresse'),
        ':ville' => $request->getParam('ville'),
        ':cp' => $request->getParam('cp'),
        ':tel' => $request->getParam('tel')
    ];
    $doRegister = prepareProfil($profil);

    if ($doRegister) {
        $_SESSION['hello'] = 'hello tout va bien';
        $result=  $response->withRedirect('login');
    } else {
        $result =  $response->withRedirect('register');
    }

    return $result;
});

///////////////////   Connexion   //////////////////////////////////////////////

$app->get('/login', function ($request, $response, $args) {
    // Render index view

    $displayTemplate = $this->renderer->render($response, 'login.phtml', [
        'hello' => $_SESSION['hello'],
        'message' => 'Connectez vous avec votre compte'
    ]);
    unset($_SESSION['hello']);
    return $displayTemplate;
});

$app->post('/doLogin', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml', $args);
});

//////////////////   Changement MDP   //////////////////////////////////////////

$app->get('/new_mdp', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'new_mdp.phtml', $args);
});

$app->post('/doNew_mdp', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'new_mdp.phtml', $args);
});

////////////////////////////////////////////////////////////////////////////////
function validEmail($email) {
    // test de l'adresse e-mail
    $result = false;
    $atom = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]'; // caractères autorisés avant l'arobase
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
    $regex = '/^' . $atom . '+' . // Une ou plusieurs fois les caractères autorisés avant l'arobase
            '(\.' . $atom . '+)*' . // Suivis par zéro point ou plus
            // séparés par des caractères autorisés avant l'arobase
            '@' . // Suivis d'un arobase
            '(' . $domain . '{1,63}\.)+' . // Suivis par 1 à 63 caractères autorisés pour le nom de domaine
            // séparés par des points
            $domain . '{2,63}$/i';          // Suivi de 2 à 63 caractères autorisés pour le nom de domaine

    if (!preg_match($regex, $email)) {
        $result = true;
    }

    return $result;
}

function prepareProfil($data) {

    $error = 0;
    if (isUserExist($data[':email'])) {
        var_dump('toto');
        $error = 1;
    } elseif (validEmail($data[':email'])) {
        $error = 2;
    } elseif ($data[':password'] != $data[':password_confirm']) {
        $error = 3;
    } elseif (strlen($data[':password']) < 7) {
        $error = 4;
    }
    if ($error > 0) {
        if ($error == 1) {
            $_SESSION['error'] = 'email deja existant !';
            //echo "email deja existant ! ";
        } elseif ($error == 2) {
            $_SESSION['error'] = 'email mal formatée !';
        } elseif ($error == 3) {
            $_SESSION['error'] = 'les mots de passes sont différents !';
        } elseif ($error == 4) {
            $_SESSION['error'] = 'Mots de passe trop court, il doit faire plus de sept caractères !';
        }
        $result = false;
    } else {
        unset($data[':password_confirm']);
        $result = createProfil($data);
    }
    return $result;
}

function createProfil($data) {
    $sql = "INSERT INTO `Utilisateur` (`id_util`, `login_util`, `mdp_util`, `mail_util`, `nom_util`, `prenom_util`, `adresse_util`, `ville_util`, `cp_util`, `tel_util`) VALUES (null, :login, :password, :email, :nom, :prenom, :adresse, :ville, :cp, :tel)";
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $result = $req->execute($data);
        //$id = $db->lastInsertId();
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans createProfil ';
        $result = false;
    }
    return $result;
}

function isUserExist($email) {
    $sql = "select mail_util FROM Utilisateur where mail_util=:mail";
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam("mail", $email);
        $req->execute();
        $listuser = $req->fetchAll(PDO::FETCH_OBJ);
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans isUserExist ';
    }

    if (empty($listuser)) {
        return false;
    } else {
        return true;
    }
}
