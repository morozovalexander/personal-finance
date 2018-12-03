<?php

use App\Views\LoginView;
use App\Views\ProfileView;
use App\Security\Auth;
use App\Service\MoneyService;
use App\Service\Database;
use App\Model\UserMapper;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

session_start();
$db = new Database();
$userMapper = new UserMapper($db);
$auth = new Auth($db);

// url parsing
$requestUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedUriArray = explode('/', trim($requestUriPath, '/'));

if (($requestedUriArray[0] !== 'login') && !Auth::checkAuthorisation()) {
    header('Location: /login');
    exit;
}

$view = new LoginView();

switch ($requestedUriArray[0]) {
    case '':
    case 'home':
        $view = new ProfileView(
            $userMapper->getCurrentUser(),
            $userMapper->getUserRublesWallet($userMapper->getCurrentUser())
        );
        break;
    case 'login':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            session_write_close();
            $view = new LoginView();
        } elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
            $user = $auth->authenticateUser();
            $view = $user ? new ProfileView($user, $userMapper->getUserRublesWallet($user)) : new LoginView();
        }
        break;
    case 'pull_money':
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            header('Location: /home');
        }
        $moneyService = new MoneyService($userMapper->getCurrentUser(), $db);
        $pullMoneyResult = $moneyService->pullMoneyWithCheckInTransaction();
        $rublesAmount = $userMapper->getUserRublesWallet($userMapper->getCurrentUser());
        $view = new ProfileView($userMapper->getCurrentUser(), $rublesAmount, $pullMoneyResult['message']);
        break;
    case 'logout':
        $auth->logoutUser();
        header('Location: /login');
        exit;
        break;
}

echo $view->render();
