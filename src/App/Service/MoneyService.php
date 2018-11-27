<?php

namespace App\Service;

use App\Model\User;

class MoneyService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $validationMessage;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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

        if ($this->user->pullMoney($moneyToPull)){
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
    protected function validateMoneyToPull(int $moneyToPull)
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
