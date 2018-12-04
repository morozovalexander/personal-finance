<?php

namespace App\Model;

class Wallet
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var integer
     */
    protected $moneyAmount;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        if (\is_object($this->currency)) {
            return $this->currency;
        }
//        else {
//            find currency in database, set currency and return
//        }
        // probably can replace get currency method in mapper
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }
}
