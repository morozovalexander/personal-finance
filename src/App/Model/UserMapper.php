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
        $userDataArray = $result->fetch();

        return $userDataArray['password'] ?? null;
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
        $user->setMoneyAmount($userDataArray['money_amount']);

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
        $userDataArray = $result->fetch();

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
        $currentMoneyAmount = $user->getMoneyAmount();
        $newMoneyAmount = $currentMoneyAmount - $moneyToPull;

        //query to lock writing
        $queryParamsArray = [];
        $queryParamsArray[0]['sql'] = 'SELECT * FROM users WHERE id = :id FOR UPDATE;';
        $queryParamsArray[0]['params'] = ['id' => $user->getId()];

        // query to modify ("current_money_amount" check will block duplicating money pull)
        $queryParamsArray[1]['sql'] = 'UPDATE users SET money_amount = :new_money_amount WHERE id = :id AND money_amount = :current_money_amount;';
        $queryParamsArray[1]['params'] = [
            'id' => $user->getId(),
            'new_money_amount' => $newMoneyAmount,
            'current_money_amount' => $currentMoneyAmount
        ];

        if ($this->db->transactionQuery($queryParamsArray)) {
            // update user money amount in object
            $user->setMoneyAmount($newMoneyAmount);
            // any money transfer stuff
            return true;
        }

        return false;
    }
}
