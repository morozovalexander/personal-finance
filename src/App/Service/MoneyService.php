<?php

namespace App\Service;

use App\Config\Config;
use App\Model\User;
use App\Model\Wallet;
use App\Model\WalletMapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MoneyService
{
    /** @var User */
    protected $user;

    /** @var Database */
    protected $db;

    /** @var string */
    protected $validationMessage;

    /** @var array */
    protected $availableCurrencies = ['rubles', 'dinars'];

    /** @var Logger */
    protected $logger;

    /**
     * @param User $user
     * @param Database $db
     */
    public function __construct(User $user, Database $db)
    {
        $this->user = $user;
        $this->db = $db;
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler(Config::getConfigs()['logs_path'], Logger::DEBUG));
    }

    /**
     * @return array
     */
    public function pullMoneyWithCheckInTransaction(): array
    {
        $returnArr = [
            'success' => false,
            'message' => 'error'
        ];

        if (!isset($_POST['money-amount'])) {
            $returnArr['message'] = 'Invalid money amount';
            $this->logger->err('Wrong pull money amount argument for username: ' . $this->user->getUsername());
            return $returnArr;
        }

        if (!isset($_POST['currency']) || !\in_array($_POST['currency'], $this->availableCurrencies, true)) {
            $returnArr['message'] = 'Invalid currency value';
            $this->logger->err('Invalid currency argument for username: ' . $this->user->getUsername());
            return $returnArr;
        }
        $currency = $_POST['currency'];

        // begin transaction, lock row while reading current value
        $userId = $this->user->getId();

        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->beginTransaction();

        $walletMapper = new WalletMapper($this->db);

        if(!$wallet = $walletMapper->getWalletWithCurrency($userId, $currency)) {
            $returnArr['message'] = 'Invalid wallet query';
            $this->logger->err('Invalid currency argument for username: ' . $this->user->getUsername());
            return $returnArr;
        }

        $precision = $wallet->getCurrency()->getPrecision();

        try {
            // query to lock writing
            $currentAmount = $walletMapper->selectMoneyAmountWithLock($wallet->getId());

            // money amounts validation
            if (!$this->validateMoneyAmountToPull($currentAmount, $precision, $_POST['money-amount'])) {
                $this->db->rollback();
                $this->logger->err('Invalid money pull params for username: ' . $this->user->getUsername());
                $returnArr['message'] = $this->validationMessage;
                return $returnArr;
            }

            // new amount calculation
            $moneyToPull = $this->parseIntegerMoneyToPull($_POST['money-amount'], $precision);
            $newMoneyAmount = $currentAmount - $moneyToPull;

            // update wallet money amount
            if ($updateRes = $walletMapper->updateWalletMoneyAmount($wallet->getId(), $newMoneyAmount)) {
                $this->db->commit();
                $this->logger->info(
                    'transaction successful for username: ' . $this->user->getUsername(),
                    ['walletId' => $wallet->getId(), 'newAmount' => $newMoneyAmount]
                );
            } else {
                $this->db->rollback();
                $this->logger->err(
                    'transaction error for username: ' . $this->user->getUsername(),
                    ['walletId' => $wallet->getId(), 'newAmount' => $newMoneyAmount, 'err' => $updateRes->errorInfo()]
                );
            }

            $returnArr['success'] = true;
            $returnArr['message'] = 'Successful transaction';
        } catch (\PDOException | \Exception $e) {
            $this->db->rollback();
            $this->logger->err('transaction error: ' . $e->getMessage());
            $returnArr['message'] = 'Database error';
        }

        return $returnArr;
    }

    /**
     * @param int $currentMoneyAmount
     * @param int $precision
     * @param string $stringMoneyToPull
     * @return bool
     */
    protected function validateMoneyAmountToPull(
        int $currentMoneyAmount,
        int $precision = 0,
        string $stringMoneyToPull = ''
    ): bool
    {
        // check fails if precision less than digits quantity after decimal point
        $explodedMoney = explode('.', $stringMoneyToPull);
        if (isset($explodedMoney[1]) && \strlen($explodedMoney[1]) > $precision) {
            $this->validationMessage = 'Invalid decimal symbol quantity';
            return false;
        }

        if (0 === $currentMoneyAmount) {
            $this->validationMessage = 'You have no money available';
            return false;
        }

        if ('' === $stringMoneyToPull) {
            $this->validationMessage = 'Invalid money amount';
            return false;
        }

        $moneyToPull = $this->parseIntegerMoneyToPull($stringMoneyToPull, $precision);

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

    /**
     * @param string $postValue
     * @param int $precision
     * @return int
     */
    protected function parseIntegerMoneyToPull(string $postValue, int $precision): int
    {
        return (int)($postValue * (10 ** $precision));
    }
}
