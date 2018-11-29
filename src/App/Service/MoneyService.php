<?php

namespace App\Service;

use App\Model\Database;
use App\Model\User;
use App\Model\UserMapper;

class MoneyService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $validationMessage;

    /**
     * @param User $user
     * @param Database $db
     */
    public function __construct(User $user, Database $db)
    {
        $this->user = $user;
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function pullMoney(): array
    {
        $returnArr = [
            'success' => false,
            'message' => 'success'
        ];


        if (!isset($_POST['money-amount'])) {
            $returnArr['message'] = 'Invalid money amount';
            return $returnArr;
        }

        $moneyToPull = (int)$_POST['money-amount'];

        if (!$this->validateMoneyToPull($moneyToPull)) {
            $returnArr['message'] = $this->validationMessage;
            return $returnArr;
        }

        $userMapper = new UserMapper($this->db);

        if ($userMapper->pullMoney($this->user, $moneyToPull)){
            $returnArr['success'] = true;
            $returnArr['message'] = 'Successful transaction';
        } else {
            $returnArr['message'] = 'Database error';
        }

        return $returnArr;
    }

    /**
     * @param int $moneyToPull
     * @return bool
     */
    protected function validateMoneyToPull(int $moneyToPull): bool
    {
        $currentMoneyAmount = $this->user->getMoneyAmount();

        if (0 === $currentMoneyAmount) {
            $this->validationMessage = 'You have no money available';
            return false;
        }

        if ($moneyToPull <= 0) {
            $this->validationMessage = 'Money amount input is invalid';
            return false;
        }

        if ($moneyToPull > $currentMoneyAmount) {
            $this->validationMessage = 'You have not enough money to pull';
            return false;
        }

        return true;
    }
}
