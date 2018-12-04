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
}
