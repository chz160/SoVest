<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Query\Builder;
use PDO;
use Exception;

/**
 * DatabaseServiceInterface
 *
 * This interface defines the contract for database operations in the SoVest application.
 */
interface DatabaseServiceInterface
{
    /**
     * Get a database connection
     *
     * @return PDO Database connection object
     * @throws Exception If connection fails
     */
    public function getConnection();

    /**
     * Execute a raw database query with error handling
     *
     * @param string $sql SQL query to execute
     * @param array $bindings Optional. Prepared statement bindings
     * @return mixed Query result (array for SELECT, bool for others)
     * @throws Exception If query fails
     */
    public function executeQuery($sql, array $bindings = []);

    /**
     * Sanitize input for database queries (using bindings instead is preferred)
     *
     * @param string $data Data to sanitize
     * @return string Sanitized data
     */
    public function sanitizeInput($data);

    /**
     * Get a query builder instance for a table
     *
     * @param string $table Table name
     * @return Builder Query builder instance
     */
    public function table($table);

    /**
     * Insert records into a table
     *
     * @param string $table Table name
     * @param array $data Data to insert (associative array)
     * @return int|bool Last insert ID or false
     */
    public function insert($table, array $data);

    /**
     * Update records in a table
     *
     * @param string $table Table name
     * @param array $data Data to update (associative array)
     * @param array $conditions Where conditions (associative array)
     * @return int Number of affected rows
     */
    public function update($table, array $data, array $conditions);

    /**
     * Delete records from a table
     *
     * @param string $table Table name
     * @param array $conditions Where conditions (associative array)
     * @return int Number of affected rows
     */
    public function delete($table, array $conditions);

    /**
     * Fetch all results from a SELECT query
     *
     * @param string $sql SQL query to execute
     * @param array $bindings Optional. Prepared statement bindings
     * @return array Array of rows
     */
    public function fetchAll($sql, array $bindings = []);

    /**
     * Fetch a single row from a SELECT query
     *
     * @param string $sql SQL query to execute
     * @param array $bindings Optional. Prepared statement bindings
     * @return array|null Single row or null if not found
     */
    public function fetchOne($sql, array $bindings = []);

    /**
     * Begin a database transaction
     *
     * @return bool Success
     */
    public function beginTransaction();

    /**
     * Commit a database transaction
     *
     * @return bool Success
     */
    public function commitTransaction();

    /**
     * Rollback a database transaction
     *
     * @return bool Success
     */
    public function rollbackTransaction();
}