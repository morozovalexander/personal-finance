<?php

namespace App\Security;

use App\Model\Database;
use App\Model\User;

class Auth
{
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
        unset($_SESSION['username']);
    }
}
