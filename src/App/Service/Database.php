<?php

namespace App\Service;

use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Config\Config;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $configs;

    public function __construct()
    {
        $this->configs = Config::getConfigs();
        $this->pdo = new PDO('mysql:host=' . $this->configs['dbhost'] . ';dbname=' . $this->configs['dbname'],
            $this->configs['dbuser'],
            $this->configs['dbpass']
        );
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler($this->configs['logs_path'], Logger::DEBUG));
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool|\PDOStatement
     */
    public function query($sql, $params = [])
    {
        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $val) {
            if (\is_int($val)) {
                $type = PDO::PARAM_INT;
            } else {
                $type = PDO::PARAM_STR;
            }
            $statement->bindValue(':' . $key, $val, $type);
        }

        $statement->execute();
        return $statement;
    }

    /**
     * @param array //  sql + params
     * @return bool
     */
    public function transactionQuery(array $queryParamsArray): bool
    {
        $success = false;

        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo->beginTransaction();
            $debugParams = [];

            foreach ($queryParamsArray as $queryParams) {
                /** @var PDOStatement $statement */
                $statement = $this->pdo->prepare($queryParams['sql']);
                foreach ($queryParams['params'] as $key => $val) {
                    if (\is_int($val)) {
                        $type = PDO::PARAM_INT;
                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    $statement->bindValue(':' . $key, $val, $type);
                }

                $statement->execute();
                $debugParams[] = $queryParams['params'];
            }

            $success = $this->pdo->commit();
            $this->logger->info('transaction successful', $debugParams);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->err('transaction error:' . $e->getMessage());
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param int $attr
     * @param mixed $value
     * @return bool
     */
    public function setAttribute(int $attr, $value): bool
    {
        return $this->pdo->setAttribute($attr, $value);
    }
}
