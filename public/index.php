<?php

use App\Views\LoginView;
use App\Views\ProfileView;
use App\Security\Auth;
use App\Model\User;
use App\Service\MoneyService;
use App\Model\Database;
use App\Model\UserMapper;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

session_start();
$db = new Database();
$auth = new Auth($db);

// url parsing
$requestUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedUriArray = explode('/', trim($requestUriPath, '/'));

$view = new LoginView();

if (($requestedUriArray[0] !== 'login') && !Auth::checkAuthorisation()) {
    echo $view->render();
    die;
}

switch ($requestedUriArray[0]) {
    case '':
    case 'home':
        $userMapper = new UserMapper($db);
        $view = new ProfileView($userMapper->getCurrentUser());
        break;
    case 'login':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            $view = new LoginView();
        } elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
            $user = $auth->authenticateUser();
            $view = $user ? new ProfileView($user) : new LoginView(); // login form or profile if login success
        }
        break;
    case 'pull_money':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            header('Location: /home');
        }
        $userMapper = new UserMapper($db);
        $moneyService = new MoneyService($userMapper->getCurrentUser(), $db);
        $pullMoneyResult = $moneyService->pullMoney();
        $view = new ProfileView($userMapper->getCurrentUser(), $pullMoneyResult['message']);
        break;
    case 'logout':
        $auth->logoutUser();
        $view = new LoginView();
        break;
}

echo $view->render();
