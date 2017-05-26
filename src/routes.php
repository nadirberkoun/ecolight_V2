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
    return $this->renderer->render($response, 'register.phtml', $args);
});


$app->get('/login', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml', $args);
});


$app->post('/doRegister', function ($request, $response, $args) {
    // Render index view
    return $this->renderer->render($response, 'register.phtml', $args);
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
