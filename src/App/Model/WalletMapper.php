<?php

namespace App\Model;

use App\Service\Database;

class WalletMapper
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
     * @param int $userId
     * @param string $currencyName
     * @return Wallet
     */
    public function getWalletWithCurrency(int $userId, string $currencyName): ?Wallet
    {
        $sql = 'SELECT w.id AS wallet_id, w.money_amount, '
            . 'c.id AS currency_id, c.name AS currency_name, c.prec AS currency_precision '
            . 'FROM wallet w '
            . 'INNER JOIN users u ON w.user_id = u.id '
            . 'INNER JOIN currency c ON w.currency_id = c.id '
            . 'WHERE u.id = :user_id AND c.name = :currency_name;';
        $params = ['user_id' => $userId, 'currency_name' => $currencyName];

        if (!$walletDataArray = $this->db->query($sql, $params)->fetch(\PDO::FETCH_ASSOC)) {
            return null;
        }

        $wallet = new Wallet();

        $wallet->setId($walletDataArray['wallet_id']);
        $wallet->setMoneyAmount($walletDataArray['money_amount']);

        $currency = new Currency();
        $currency->setId($walletDataArray['currency_id']);
        $currency->setName($walletDataArray['currency_name']);
        $currency->setPrecision($walletDataArray['currency_precision']);

        $wallet->setCurrency($currency);

        return $wallet;
    }

    /**
     * @param int $walletId
     * @return int
     */
    public function selectMoneyAmountWithLock(int $walletId): int
    {
        $sql = 'SELECT w.money_amount FROM wallet w WHERE w.id = :wallet_id FOR UPDATE;';
        $params = ['wallet_id' => $walletId];

        if (!$currMoneyArray = $this->db->query($sql, $params)->fetch(\PDO::FETCH_ASSOC)) {
            return 0;
        }

        return (int)($currMoneyArray['money_amount'] ?? 0);
    }

    /**
     * @param int $walletId
     * @param int $newMoneyAmount
     * @return bool|\PDOStatement
     */
    public function updateWalletMoneyAmount(int $walletId, int $newMoneyAmount)
    {
        $sql = 'UPDATE wallet w SET w.money_amount = :new_money_amount WHERE w.id = :wallet_id;';
        $params = [
            'new_money_amount' => $newMoneyAmount,
            'wallet_id' => $walletId
        ];

        return $this->db->query($sql, $params);
    }
}
