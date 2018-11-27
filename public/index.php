<?php

use App\Views\LoginView;
use App\Views\ProfileView;
use App\Security\Auth;
use App\Model\User;
use App\Service\MoneyService;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

session_start();

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
        $userModel = new User();
        $user = $userModel->getCurrentUser();
        $view = new ProfileView($user);
        break;
    case 'login':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            $view = new LoginView();
        } elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
            $auth = new Auth();
            $user = $auth->authenticateUser();
            $view = $user ? new ProfileView($user) : new LoginView(); // login form or profile if login success
        }
        break;
    case 'pull_money':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            header('Location: /home');
        }
        $userModel = new User();
        $moneyService = new MoneyService($userModel->getCurrentUser());
        $pullMoneyResult = $moneyService->pullMoney();
        $view = new ProfileView($userModel, $pullMoneyResult['message']);
        break;
    case 'logout':
        $auth = new Auth();
        $auth->logoutUser();
        $view = new LoginView();
        break;
}

echo $view->render();
