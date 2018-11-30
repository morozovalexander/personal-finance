<?php

namespace App\Model;

use App\Service\Database;

class UserMapper
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @return User $this
     */
    public function getCurrentUser(): User
    {
        return $this->findUserByUsername($_SESSION['username']);
    }

    /**
     * @param User $user
     * @return string
     */
    public function getUserPassword(User $user): string
    {
        $queryString = 'SELECT password FROM users WHERE id = :id';
        $result = $this->db->query($queryString, ['id' => $user->getId()]);
        $userDataArray = $result->fetch(\PDO::FETCH_ASSOC);

        return $userDataArray['password'] ?? null;
    }

    /**
     * @param User $user
     * @return array|null
     */
    public function getUserRublesWallet(User $user): ?array
    {
        $queryString = 'SELECT w.money_amount, c.prec FROM wallet AS w '
            . 'INNER JOIN currency AS c ON c.id = w.currency_id '
            . 'WHERE user_id = :id AND c.name = :currency';

        $result = $this->db->query($queryString, ['id' => $user->getId(), 'currency' => 'rubles']);
        $walletArray = $result->fetch(\PDO::FETCH_ASSOC);

        return $walletArray['money_amount'] ? $walletArray :  null;
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function findUserByUsername(string $username): ?User
    {
        $userDataArray = $this->findUserArrayByUsername($username);

        if (!$userDataArray) {
            return null;
        }

        $user = new User();

        $user->setId($userDataArray['id']);
        $user->setUsername($userDataArray['username']);
        $user->setFirstName($userDataArray['firstname']);
        $user->setLastName($userDataArray['lastname']);

        return $user;
    }

    /**
     * @param string $username
     * @return array|null
     */
    public function findUserArrayByUsername(string $username): ?array
    {
        $queryString = 'SELECT * FROM users WHERE username = :username';
        $result = $this->db->query($queryString, ['username' => $username]);
        $userDataArray = $result->fetch(\PDO::FETCH_ASSOC);

        return $userDataArray ?: null;
    }

    /**
     * @param User $user
     * @param int $moneyToPull
     * @return bool|\PDOStatement
     */
    public function pullMoney(User $user, int $moneyToPull): bool
    {
        //record new money amount
        $rublesWallet = $this->getUserRublesWallet($user);
        $currentMoneyAmount = $rublesWallet['money_amount'] ?? 0;
        $newMoneyAmount = $currentMoneyAmount - $moneyToPull;
        $userId = $user->getId();

        //query to lock writing
        $queryParamsArray = [];

        $queryParamsArray[0]['sql'] = 'SELECT * FROM ruble_wallet WHERE user_id = :id FOR UPDATE;';
        $queryParamsArray[0]['params'] = ['id' => $userId];

        // query to modify ("current_money_amount" check will block duplicating money pull)
        $queryParamsArray[1]['sql'] = 'UPDATE ruble_wallet SET money_amount = :new_money_amount '
            . 'WHERE user_id = :id AND money_amount = :current_money_amount;';
        $queryParamsArray[1]['params'] = [
            'id' => $userId,
            'new_money_amount' => $newMoneyAmount,
            'current_money_amount' => $currentMoneyAmount
        ];

        if ($this->db->transactionQuery($queryParamsArray)) {
            return true;
        }

        return false;
    }
}
