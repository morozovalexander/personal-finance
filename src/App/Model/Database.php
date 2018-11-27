<?php

namespace App\Model;

use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Config\Config;

class Database
{
    /**
     * @var PDO
     */
    protected $db;

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
        $this->db = new PDO('mysql:host=' . $this->configs['dbhost'] . ';dbname=' . $this->configs['dbname'],
            $this->configs['dbuser'],
            $this->configs['dbpass']
        );
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler($this->configs['logs_path'], Logger::WARNING));
    }

    /**
     * @param string $username
     * @return array
     */
    public function findUserArrayByUsername(string $username): ?array
    {
        $queryString = 'SELECT * FROM users WHERE username = :username';
        $result = $this->query($queryString, ['username' => $username]);
        return $result->fetch() ?: null;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool|\PDOStatement
     */
    public function query($sql, $params = [])
    {
        $statement = $this->db->prepare($sql);
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                if (\is_int($val)) {
                    $type = PDO::PARAM_INT;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $statement->bindValue(':' . $key, $val, $type);
            }
        }
        $statement->execute();
        return $statement;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function transactionQuery($sql, $params = []): bool
    {
        $success = false;

        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->db->beginTransaction();
            /**
             * @var \PDOStatement $statement
             */
            $statement = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                if (\is_int($val)) {
                    $type = PDO::PARAM_INT;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $statement->bindValue(':' . $key, $val, $type);
            }
            $statement->execute();
            $success = $this->db->commit();
            $this->logger->info('transaction successful');
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->logger->err('transaction error:' . $e->getMessage());
        }

        return $success;
    }
}
