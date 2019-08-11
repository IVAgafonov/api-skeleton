<?php

namespace App\System\DataProvider\Mysql;
/**
 * Class DataProvider
 * @package IVAgafonov\System
 */
class DataProvider implements DataProviderInterface
{
    /**
     * PDO object
     *
     * @var \PDO
     */
    private $pdo;
    /**
     * PDO Statement object
     *
     * @var \PDOStatement
     */
    private $statement;

    /**
     * DataProvider constructor.
     *
     * @param array $config Config PDO
     *  $config = [
     *      'user' => (string) DB host
     *      'password' => (string) DB name
     *      'host' => (string) DB user
     *      'db' => (string) DB password
     *  ]
     *
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        if (empty($config['user']) || empty($config['host']) || !isset($config['db'])) {
            throw new \Exception("Invalid mysql database params");
        }
        $this->pdo = new \PDO("mysql:dbname=" . $config['db'] . ";host=" . $config['host'] . ";charset=utf8", $config['user'], $config['password']);
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Get objects by sql query
     *
     * @param string $query  Sql query
     * @param string $object Object name
     *
     * @return array|null
     */
    public function getObjects($query, $object)
    {
        $this->statement = $this->pdo->query($query);
        if ($this->statement) {
            $this->statement->setFetchMode(\PDO::FETCH_CLASS, $object);
            $objects =  $this->statement->fetchAll();
            if ($objects) {
                return $objects;
            }
        }
        return null;
    }
    /**
     * Get object by sql query
     *
     * @param string $query  Sql query
     * @param string $object Object name
     *
     * @return array|null
     */
    public function getObject($query, $object)
    {
        $this->statement = $this->pdo->query($query);
        if ($this->statement) {
            $this->statement->setFetchMode(\PDO::FETCH_CLASS, $object);
            return $this->statement->fetch();
        }
        return null;
    }
    /**
     * Get arrays by sql query
     *
     * @param string $query Sql query
     *
     * @return array|null
     */
    public function getArrays($query)
    {
        $this->statement = $this->pdo->query($query);

        if ($this->statement) {
            $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }
        }
        return null;
    }
    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     *
     * @return array|null
     */
    public function getArray($query)
    {
        $this->statement = $this->pdo->query($query);
        if ($this->statement) {
            return $this->statement->fetch(\PDO::FETCH_ASSOC);
        }
        return null;
    }
    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     *
     * @return array|null
     */
    public function getValue($query)
    {
        $this->statement = $this->pdo->query($query);
        if ($this->statement) {
            $value = $this->statement->fetch(\PDO::FETCH_BOTH);
            if (!empty($value[0])) {
                return $value[0];
            }
        }
        return null;
    }
    /**
     * Execute sql query
     *
     * @param string $query Sql query
     *
     * @return int|null
     */
    public function doQuery($query)
    {
        $this->statement = $this->pdo->query($query);
        if ($this->statement && $this->statement->rowCount()) {
            return $this->statement->rowCount();
        }
        return null;
    }
    /**
     * Quote string
     *
     * @param string $str Text string
     *
     * @return string
     */
    public function quote($str)
    {
        return $this->pdo->quote($str);
    }

    /**
     * @return int|null
     */
    public function getAffectedRows()
    {
        if ($this->statement) {
            return $this->statement->rowCount();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @return array
     */
    public function getLastError()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @return mixed
     */
    public function getLastErrno()
    {
        return $this->pdo->errorCode();
    }
}