<?php

namespace App\Model;

class User
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
        //record new money amount
        $currentMoneyAmount = $this->getMoneyAmount();
        $newMoneyAmount = $currentMoneyAmount - $moneyToPull;

        //query to lock writing
        $queryParamsArray = [];
        $queryParamsArray[0]['sql'] = 'SELECT * FROM users WHERE id = :id FOR UPDATE;';
        $queryParamsArray[0]['params'] = ['id' => $this->getId()];

        // query to modify ("current_money_amount" check will block duplicating money pull)
        $queryParamsArray[1]['sql'] = 'UPDATE users SET money_amount = :new_money_amount WHERE id = :id AND money_amount = :current_money_amount;';
        $queryParamsArray[1]['params'] = [
            'id' => $this->getId(),
            'new_money_amount' => $newMoneyAmount,
            'current_money_amount' => $currentMoneyAmount
        ];

        if ($this->db->transactionQuery($queryParamsArray)) {
            // update user money amount in object
            $this->setMoneyAmount($newMoneyAmount);
            // any money transfer stuff
            return true;
        }

        return false;
    }
}
