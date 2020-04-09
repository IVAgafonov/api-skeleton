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
    private $pdo = null;

    /**
     * PDO Statement object
     *
     * @var \PDOStatement
     */
    private $statement;

    /**
     * @var string
     */
    private $db;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

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
        if (empty($config['user']) || empty($config['host']) || !isset($config['db']) || !isset($config['password'])) {
            throw new \Exception("Invalid mysql database params");
        }
        $this->db = $config['db'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->host = $config['host'];
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        if (!$this->pdo) {
            $this->pdo = new \PDO(
                "mysql:dbname=" . $this->db . ";host=" . $this->host . ";charset=utf8",
                $this->user,
                $this->password
            );

        }
        return $this->pdo;
    }

    /**
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get objects by sql query
     *
     * @param string $query Sql query
     * @param string $object Object name
     * @param array  $params Sql query
     *
     * @return array|null
     */
    public function getObjects($query, $object, $params = [])
    {
        $this->getStatement($query, $params);
        if ($this->statement) {
            $this->statement->setFetchMode(\PDO::FETCH_CLASS, $object);
            $objects = $this->statement->fetchAll();
            if ($objects) {
                return $objects;
            }
        }
        return [];
    }

    /**
     * Get object by sql query
     *
     * @param string $query Sql query
     * @param string $object Object name
     * @param array  $params Sql query
     *
     * @return array|null
     */
    public function getObject($query, $object, $params = [])
    {
        $this->getStatement($query, $params);
        $this->statement = $this->getPdo()->query($query);
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
     * @param array  $params Sql query
     *
     * @return array|null
     */
    public function getArrays($query, $params = [])
    {
        $this->getStatement($query, $params);
        if ($this->statement) {
            $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }
        }
        return [];
    }

    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|null
     */
    public function getArray($query, $params = [])
    {
        $this->getStatement($query, $params);
        if ($this->statement) {
            return $this->statement->fetch(\PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|null
     */
    public function getValue($query, $params = [])
    {
        $this->getStatement($query, $params);
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
     * @param array  $params Sql query
     *
     * @return int|null
     */
    public function query($query, $params = [])
    {
        $this->getStatement($query, $params);
        if ($this->statement && $this->statement->rowCount()) {
            return $this->statement->rowCount();
        }
        return null;
    }

    /**
     * @param $query
     * @param array $params
     * @return bool|false|\PDOStatement
     */
    private function getStatement($query, $params = []) {
        if (count($params)) {
            $this->statement = $this->pdo->prepare($query);
            foreach ($params as $name => $param) {
                if (in_array($name, [':offset', ':limit'])) {
                    $this->statement->bindValue($name, $param, \PDO::PARAM_INT);
                } else {
                    $this->statement->bindValue($name, $param);
                }
            }
            $this->statement->execute();
        } else {
            $this->statement = $this->getPdo()->query($query);
        }
        return $this->statement;
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
        return $this->getPdo()->quote($str);
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
        return $this->getPdo()->lastInsertId();
    }

    /**
     * @return array
     */
    public function getLastError()
    {
        return $this->getPdo()->errorInfo();
    }

    /**
     * @return mixed
     */
    public function getLastErrno()
    {
        return $this->getPdo()->errorCode();
    }
}
