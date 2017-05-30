<?php
// Routes


$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});



$app->get('/register', function ($request, $response, $args) {
    // Render index view
    print_r($_SESSION['error']);
    return $this->renderer->render($response, 'register.phtml', $args);
});

$app->get('/doRegister', function ($request, $response, $args) {
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
    /*if ($app->request->post('password') === $app->request->post('password_confirm')) {
        $sql = "INSERT INTO `Utilisateur` (`id_util`, `login_util`, `mdp_util`, `mail_util`, `nom_util`, `prenom_util`, `adresse_util`, `ville_util`, `cp_util`, `tel_util`) VALUES (null, :login, :password, :email, :nom, :prenom, :adresse, :ville, :cp, :tel)";
        $req = $dbh->prepare($sql);
        $result = $req->execute($tab);
        $doRegister = true;
    }*/
    $doRegister = true;
    if ($doRegister) {
        $_SESSION['hello'] = 'hello tout va bien';
        return $response->withRedirect('login');
    } else {
       $_SESSION['error'] = 'Il y une erreur';
       return $response->withRedirect('register');
    }
});



$app->get('/login', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml', $args);
});

$app->post('/doLogin', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml', $args);
});





$app->get('/new_mdp', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'new_mdp.phtml', $args);
});

$app->post('/doNew_mdp', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'new_mdp.phtml', $args);
});
