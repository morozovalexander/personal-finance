<?php

namespace App\Security;

use App\Service\Database;
use App\Model\User;
use App\Model\UserMapper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Config\Config;

class Auth
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
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

        $userMapper = new UserMapper($this->db);
        $user = $userMapper->findUserByUsername($login);

        if (!$user) {
            $this->logger->err('Login error: user with username "' . $login . '" not found');
            return null;
        }
        $this->logger->info('User with username "' . $login . '" logged in');

        if (!password_verify($password, $userMapper->getUserPassword($user))) {
            return null;
        }

        $_SESSION['username'] = $login;
        session_write_close();

        return $user;
    }

    public function logoutUser(): void
    {
        $this->logger->info('User with username "' . $_SESSION['username'] . '" logged out');
        unset($_SESSION['username']);
    }
}
