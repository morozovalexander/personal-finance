<?php

namespace App\Security;

use App\Model\Database;
use App\Model\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Config\Config;

class Auth
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        $configs = Config::getConfigs();
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler($configs['logs_path'], Logger::DEBUG));
    }

    /**
     * @return bool
     */
    public static function checkAuthorisation(): bool
    {
        return (isset($_SESSION['username']) && (trim($_SESSION['username']) !== ''));
    }

    /**
     * @return User|null
     */
    public function authenticateUser(): ?User
    {
        if (!$_POST['password'] || !$_POST['login']) {
            return null;
        }

        $login = $_POST['login'];
        $password = $_POST['password'];

        $db = new Database();

        $userArray = $db->findUserArrayByUsername($login);
        if (!$userArray) {
            return null;
        }
        $this->logger->info('User with username "' . $login . '" logged in');

        if (!password_verify($password, $userArray['password'])) {
            return null;
        }

        $_SESSION['username'] = $login;
        session_write_close();

        $userModel = new User();

        return $userModel->getCurrentUser();
    }

    public function logoutUser(): void
    {
        $this->logger->info('User with username "' . $_SESSION['username'] . '" logged out');
        unset($_SESSION['username']);
    }
}
