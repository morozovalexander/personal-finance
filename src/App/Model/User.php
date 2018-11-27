<?php

namespace App\Model;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var integer
     */
    protected $moneyAmount;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return int
     */
    public function getMoneyAmount(): int
    {
        return $this->moneyAmount;
    }

    /**
     * @param int $moneyAmount
     */
    public function setMoneyAmount(int $moneyAmount): void
    {
        $this->moneyAmount = $moneyAmount;
    }

    /**
     * @return User $this
     */
    public function getCurrentUser(): User
    {
        $userDataArray = $this->db->findUserArrayByUsername($_SESSION['username']);

        $this->id = $userDataArray['id'];
        $this->username = $userDataArray['username'];
        $this->firstName = $userDataArray['firstname'];
        $this->lastName = $userDataArray['lastname'];
        $this->moneyAmount = $userDataArray['money_amount'];

        return $this;
    }

    /**
     * @param int $moneyToPull
     * @return bool|\PDOStatement
     */
    public function pullMoney(int $moneyToPull): bool
    {
        // any money transfer stuff

        //record new money amount
        $newMoneyAmount = $this->getMoneyAmount() - $moneyToPull;

        $sql = 'UPDATE users SET money_amount = :money WHERE id = :id';
        $params = [
            'id' => $this->getId(),
            'money' => $newMoneyAmount
        ];

        if ($this->db->transactionQuery($sql, $params)) {
            // update user money amount in object
            $this->setMoneyAmount($newMoneyAmount);
            return true;
        }

        return false;
    }
}
