<?php

// Routes
function initDb() {
    try {
        return new PDO('mysql:host=192.168.1.252;dbname=ecolight', 'root', 'password');
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}

$dbh = initDb();


////////////////////   Index   /////////////////////////////////////////////////

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    //
    return $this->renderer->render($response, 'index.phtml', [
                'assets_home' => true,
    ]);
});

///////////////////   Enregistrement   /////////////////////////////////////////

$app->get('/register', function ($request, $response, $args) {
    if (isset($_SESSION['profil']) && !empty($_SESSION['profil'])) {
        $displayTemplate = $response->withRedirect('dashboard');
    } else {
        $error = null;
        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
        }
        $displayTemplate = $this->renderer->render($response, 'register.phtml', [
            'error' => $error,
        ]);
    }
    unset($_SESSION['success']);
    unset($_SESSION['error']);
    unset($_SESSION['hello']);
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
        $result = $response->withRedirect('login');
    } else {
        $result = $response->withRedirect('register');
    }

    return $result;
});

///////////////////   Connexion   //////////////////////////////////////////////

$app->get('/login', function ($request, $response, $args) {
    // Render index view

    if (isset($_SESSION['profil']) && !empty($_SESSION['profil'])) {
        $displayTemplate = $response->withRedirect('dashboard');
    } else {
        $error = null;
        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
        }
        $displayTemplate = $this->renderer->render($response, 'login.phtml', [
            'error' => $error,
        ]);
    }
    unset($_SESSION['success']);
    unset($_SESSION['error']);
    unset($_SESSION['hello']);
    return $displayTemplate;
});

$app->post('/doLogin', function ($request, $response, $args) {

    $profil = [
        ':email' => $request->getParam('email'),
        ':password' => $request->getParam('password'),
    ];
    if (isUserExist($request->getParam('email')) == 0 && isCurrentPassword($request->getParam('password'), $request->getParam('email'))) {
        $_SESSION['error'] = 'Ce login n\'existe pas, merci de procéder à votre inscription';
        return $response->withRedirect('login');
    } else {
        setSessionProfilData(getProfileByUserEmail($request->getParam('email')));
        return $response->withRedirect('dashboard');
    }
});

$app->get('/dashboard', function ($request, $response, $args) {
    if (isset($_SESSION['profil']) && !empty($_SESSION['profil'])) {
        $displayTemplate = $this->renderer->render($response, 'dashboard.phtml', [
            'message' => 'hello',
            'success' => getSessionSuccess('resetPassword'),
            'profil' => getMegaRequeteByUser($_SESSION['profil']['id_util']),
            'maisons' => getMaisonByUSerId($_SESSION['profil']['id_util'])
        ]);
        unset($_SESSION['success']);
        unset($_SESSION['error']);
        unset($_SESSION['hello']);
    } else {
        $displayTemplate = $response->withRedirect('login');
    }
    return $displayTemplate;
});


//////////////////   Changement MDP   //////////////////////////////////////////

$app->get('/new_mdp', function ($request, $response, $args) {
    $displayTemplate = $this->renderer->render($response, 'new_mdp.phtml', [
        'error' => getSessionError('resetPassword'),
    ]);
    return $displayTemplate;
});

$app->post('/doNew_mdp', function ($request, $response, $args) {
    $modif = [
        'current_password' => $request->getParam('current_password'),
        'new_password' => $request->getParam('new_password'),
        'new_password2' => $request->getParam('new_password2'),
    ];
    $doModif = resetPassword($modif, getSessionProfilData('id_util'));

    if ($doModif) {
        setSessionSuccess('resetPassword', 'Votre mot de passe a été mis à jour !');
        $result = $response->withRedirect('dashboard');
    } else {
        setSessionError("resetPassword", 'Ce login n\'existe pas ou le mot de passe est pas identique');
        $result = $response->withRedirect('new_mdp');
    }
    return $result;
});

$app->get('/update_user', function ($request, $response, $args) {
    $displayTemplate = $this->renderer->render($response, 'update_user.phtml', [
    ]);
    return $displayTemplate;
});

$app->post('/doUpdate_user', function ($request, $response, $args) {
    $modif = [
        'nom' => $request->getParam('nom'),
        'prenom' => $request->getParam('prenom'),
        'adresse' => $request->getParam('adresse'),
        'ville' => $request->getParam('ville'),
        'cp' => $request->getParam('cp'),
        'tel' => $request->getParam('tel'),
    ];
    $doModif = setDataUser($modif);

    if ($doModif) {
        setSessionSuccess('updatedata', 'Vos informations ont été mis à jour !');
        $result = $response->withRedirect('dashboard');
    } else {
        setSessionError("updatedata", 'Veuillez recommencer');
        $result = $response->withRedirect('update_user');
    }
    return $result;
});


// WS Android, requeter en JAVA
$app->get('/getprofil[/{id}]', function ($request, $response, $args) {
    $dataAry = getProfileByUseriD($args['id']);
    $response->write(json_encode($dataAry));
    return $response;
});

$app->get('/logout', function ($request, $response, $args) {

    session_destroy();

    $result = $response->withRedirect('dashboard');

    return $result;
});

$app->post('/getPieceByIdMaison', function ($request, $response, $args) {
    $pieces = getPiecesByMaisonId($request->getParam('id'));
    $body = '';
    foreach ($pieces as $piece) {

        $body .= '<li class="list-group-item"><button onclick="selectPiece(' . $piece->id_piece . ');">selectionner  la Piece : ' . $piece->nom_piece . '</button></li>';
    }
    $response->write(json_encode($body));
    return $response;
});

$app->post('/getCaptorByIdPiece', function ($request, $response, $args) {
    $captors = getCaptorsByPieceId($request->getParam('id'));
    $body = '';
    foreach ($captors as $captor) {

        $body .= '<li class="list-group-item"><button onclick="selectCaptor(' . $captor->id_capt . ',\'' . $captor->type_capteur . '\');">selectionner  le capteur de : ' . $captor->type_capteur . '</button></li>';
    }
    $response->write(json_encode($body));
    return $response;
});

$app->post('/getValuesByCaptorId', function ($request, $response, $args) {

    $values = getValuesByCaptorId($request->getParam('id'), $request->getParam('type'));
    $type = $request->getParam('type');

    if (!empty($values)) {
        $body = '<thead class="text-primary"><th>Nom pièce</th><th>Date / Heure</th><th>Valeur</th></thead><tbody>';
        foreach ($values as $value) {

            if ($type == 'L') {
                $body .= '<tr><td>' . $value->nom_capt . '</td><td>' . $value->date_value . '</td><td>' . $value->luminosite_lum . '</td></tr>';
            } else if ($type == 'T') {
                $body .= '<tr><td>' . $value->nom_capt . '</td><td>' . $value->date_value . '</td><td>' . $value->temperature_temp . '</td></tr>';
            }
        }
        $body .= '</tbody>';
    } else {
        $body = '<tbody><tr><td>Il n\'y a pas encore de valeurs pour ce capteur !</td></tr></tbody>';
    }


    $response->write(json_encode($body));

    return $response;
});

/////////////////   Validation email   /////////////////////////////////////////
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
        $error = 1;
    } elseif (validEmail($data[':email'])) {
        $error = 2;
    } elseif ($data[':password'] != $data[':password_confirm']) {
        $error = 3;
    }
    if ($error > 0) {
        if ($error == 1) {
            $_SESSION['error'] = 'email deja existant !';
        } elseif ($error == 2) {
            $_SESSION['error'] = 'email mal formatée !';
        } elseif ($error == 3) {
            $_SESSION['error'] = 'les mots de passes sont différents !';
        }
        $result = false;
    } else {
        unset($data[':password_confirm']);
        $result = createProfil($data);
    }
    return $result;
}

function createProfil($data) {
    $sql = "INSERT INTO ecolight.Utilisateur (`id_util`, `login_util`, `mdp_util`, `mail_util`, `nom_util`, `prenom_util`, `adresse_util`, `ville_util`, `cp_util`, `tel_util`) VALUES (null, :login, :password, :email, :nom, :prenom, :adresse, :ville, :cp, :tel)";
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('data', $data);
        $result = $req->execute();
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

function verifAccount($login) {

    $dbh = initDb();

    $sql = 'select count(*) as count from `Utilisateur` WHERE `login_util`= :login';
    try {
        $res = $dbh->prepare($sql);
        $res->execute([':login' => $login]);
        $res = $r->fetch();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans verifAccount ';
    }
    return $res['count'];
}

function getProfileByUseriD($id) {

    $sql = "select * from ecolight.Utilisateur where id_util = :id";
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam("id", $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_ASSOC);
        $result = reset($result);
        //$id = $db->lastInsertId();
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getProfileByUser';
        $result = false;
    }
    return $result;
}

function getProfileByUserEmail($email) {

    $sql = "select * from ecolight.Utilisateur where mail_util = :email";
    $result = null;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam("email", $email);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_ASSOC);
        $result = reset($result);
        //$id = $db->lastInsertId();
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getProfileByUser';
        $result = false;
    }
    return $result;
}

function getMaisonByUSerId($id) {
    $sql = "select * from ecolight.Maison where id_util= :id";
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('id', $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_OBJ);
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getMaisonByUSerId';
        $result = false;
    }
    return $result;
}

function getPiecesByMaisonId($id) {
    $sql = "select * from ecolight.PIece where id_maison= :id";
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('id', $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_OBJ);
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getPieceByMaisonid';
        $result = false;
    }
    return $result;
}

function getCaptorsByPieceId($id) {

    $sql = "select * from ecolight.Capteur where id_piece = :id";
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('id', $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_OBJ);
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getCaptorsByPieceId';
        $result = false;
    }
    return $result;
}

function getValuesByCaptorId($id, $type) {
    if ($type == 'L') {
        $sql = "select * from ecolight.Date_valeur as dt inner join  ecolight.Capteur as c on c.id_capt = dt.id_capt_lum where id_capt_lum = :id";
    } else if ($type == 'T') {
        $sql = "select * from ecolight.Date_valeur as dt inner join  ecolight.Capteur as c on c.id_capt = dt.id_capt_temp where id_capt_temp = :id";
    }
    $result = true;
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('id', $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_OBJ);
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans gettempdatbycapteur';
        $result = false;
    }
    return $result;
}

function getMegaRequeteByUser($id) {
    $sql = "SELECT
	u.id_util, u.nom_util, u.prenom_util,u.adresse_util, u.tel_util, u.CP_util, u.ville_util,
	m.id_maison,
	p.id_piece, p.nom_piece,
	c.id_capt, c.nom_capt,
    c.type_capteur,
	dt.date_value,
    dt.luminosite_lum, dt.temperature_temp
FROM Utilisateur u
	inner join Preference pr on u.id_util = pr.id_pref
	inner join Maison m on u.id_util = m.id_maison
	inner join PIece p on m.id_maison = p.id_piece
	right join Capteur c on p.id_piece = c.id_piece
	inner join Date_valeur dt on c.id_capt = dt.id_capt_lum or  c.id_capt = dt.id_capt_temp
    where u.id_util = :id";
    try {
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam('id', $id);
        $req->execute();
        $result = $req->fetchAll(PDO::FETCH_OBJ);
        //$id = $db->lastInsertId();
        $req = null;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Il y une erreur dans getMegaRequeteByUser';
        $result = false;
    }
    return $result;
}

function resetPassword($modif, $id) {
    $update = false;
    if ($modif['new_password'] == $modif['new_password2'] && isCurrentPasswordById($modif['current_password'], $id)) {
        $sql = "update ecolight.Utilisateur set mdp_util = :new_password where id_util = :id";
        $dbh = initDb();
        $req = $dbh->prepare($sql);
        $req->bindParam("new_password", $modif['new_password']);
        $req->bindParam("id", $id);
        $req->execute();
        $sInfo = $req->fetch(PDO::FETCH_ASSOC);
        $update = true;
    }
    return $update;
}

function isCurrentPassword($current_password, $email) {
    $sql = "select count(*) as count from ecolight.Utilisateur where mdp_util = :current_password and mail_util = :email;";
    $dbh = initDb();
    $req = $dbh->prepare($sql);
    $req->bindParam("current_password", $current_password);
    $req->bindParam("email", $email);
    $req->execute();
    $result = $req->fetchAll(PDO::FETCH_ASSOC);
}

function isCurrentPasswordById($current_password, $id) {
    $verif = false;
    $sql = "select count(*) as count from ecolight.Utilisateur where mdp_util = :current_password and id_util = :id;";
    $dbh = initDb();
    $req = $dbh->prepare($sql);
    $req->bindParam("current_password", $current_password);
    $req->bindParam("id", $id);
    $req->execute();
    $result = $req->fetchAll(PDO::FETCH_ASSOC);
    $result = reset($result);

    if (intval($result['count']) > 0) {
        $verif = true;
    }
    return $verif;
}

function getSessionProfilData($key) {
    $data = null;
    if (isset($_SESSION['profil'][$key])) {
        $data = $_SESSION['profil'][$key];
    }
    return $data;
}

function setSessionProfilData($data) {
    $_SESSION['profil'] = $data;
}

function getSessionError($key) {
    $data = null;
    if (isset($_SESSION['error'][$key])) {
        $data = $_SESSION['error'][$key];
    }
    return $data;
}

function setSessionError($key, $message) {
    $_SESSION['error'][$key] = $message;
}

function getSessionSuccess($key) {
    $data = null;
    if (isset($_SESSION['success'][$key])) {
        $data = $_SESSION['success'][$key];
    }
    return $data;
}

function setSessionSuccess($key, $message) {
    $_SESSION['success'][$key] = $message;
}

function setDataUser($id) {

    $sql = "update ecolight.Utilisateur set nom_util = :nom, prenom_util = :prenom, adresse_util = :adresse, ville_util = :ville, CP_util = :cp, tel_util = :tel  where id_util = :id";
    $dbh = initDb();
    $req = $dbh->prepare($sql);
    $req->bindParam("nom", $modif['nom']);
    $req->bindParam("prenom", $modif['prenom']);
    $req->bindParam("adresse", $modif['adresse']);
    $req->bindParam("ville", $modif['ville']);
    $req->bindParam("cp", $modif['cp']);
    $req->bindParam("tel", $modif['tel']);
    $req->bindParam("id", $id);
    $req->execute();
    $sInfo = $req->fetch(PDO::FETCH_ASSOC);

}
