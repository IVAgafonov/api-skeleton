<?php

namespace App\System\DataProvider\Mysql;

interface DataProviderInterface {

    /**
     * @return \PDO
     */
    public function getPdo();

    /**
     * @return string
     */
    public function getDb();

    /**
     * Get objects by sql query
     *
     * @param string $query  Sql query
     * @param string $object Object name
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function getObjects($query, $object, $params);

    /**
     * Get object by sql query
     *
     * @param string $query  Sql query
     * @param string $object Object name
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function getObject($query, $object, $params);

    /**
     * Get arrays by sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function getArrays($query, $params);

    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function getArray($query, $params);

    /**
     * Get array by sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function getValue($query, $params);

    /**
     * Execute sql query
     *
     * @param string $query Sql query
     * @param array  $params Sql query
     *
     * @return array|bool
     */
    public function query($query, $params);

    /**
     * Quote string
     *
     * @param string $str Text string
     *
     * @return string
     */
    public function quote($str);

    /**
     * @return int
     */
    public function getAffectedRows();

    /**
     * @return string
     */
    public function getLastInsertId();

    /**
     * @return array
     */
    public function getLastError();

    /**
     * @return mixed
     */
    public function getLastErrno();
}
