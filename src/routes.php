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
    return $this->renderer->render($response, 'register.phtml', $args);
});

$app->post('/doRegister', function ($request, $response, $args) {
    // if session redirect to dashboard
    $profil = [
        ':login' => $request->getParam('login'),
        ':password' => $request->getParam('password'),
        ':email' => $request->getParam('email'),
        ':nom' => $request->getParam('nom'),
        ':prenom' => $request->getParam('prenom'),
        ':adresse' => $request->getParam('addresse'),
        ':ville' => $request->getParam('ville'),
        ':cp' => $request->getParam('cp'),
        ':tel' => $request->getParam('tel')
    ];
    $dbh = initDb();
    if ($request->getParam('password') === $request->getParam('password_confirm')) {
        $sql = "INSERT INTO `Utilisateur` (`id_util`, `login_util`, `mdp_util`, `mail_util`, `nom_util`, `prenom_util`, `adresse_util`, `ville_util`, `cp_util`, `tel_util`) VALUES (null, :login, :password, :email, :nom, :prenom, :adresse, :ville, :cp, :tel)";
        $req = $dbh->prepare($sql);
        $result = $req->execute($profil);
        $doRegister = true;
    }
    if ($doRegister) {
        $_SESSION['hello'] = 'hello tout va bien';
        return $response->withRedirect('login');
    } else {
       $_SESSION['error'] = 'Il y une erreur';
       return $response->withRedirect('register');
    }
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
