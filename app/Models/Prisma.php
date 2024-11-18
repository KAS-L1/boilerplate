<?php

declare(strict_types=1);

class Prisma
{
    private PDO $pdo;

    /**
     * Prisma constructor.
     */
    public function __construct(private string $dsn, private string $user, private string $password)
    {
        $this->connect();
    }

    /**
     * Establishes a connection to the database.
     */
    private function connect(): void
    {
        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Validates the table name to prevent SQL injection.
     */
    private function validateTableName(string $table): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new InvalidArgumentException("Invalid table name: {$table}");
        }
    }

    /**
     * Fetches multiple records from the specified table.
     */
    public function findMany(string $table): array
    {
        $this->validateTableName($table);
        $stmt = $this->pdo->query("SELECT * FROM " . $table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserts a new record into the specified table.
     */
    public function create(string $table, array $data): int
    {
        $this->validateTableName($table);
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $stmt = $this->pdo->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Updates records in the specified table based on conditions.
     */
    public function update(string $table, array $data, array $where): int
    {
        $this->validateTableName($table);
        $setClause = implode(", ", array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        $whereClause = implode(" AND ", array_map(fn($key) => "{$key} = :where_{$key}", array_keys($where)));

        $params = array_merge($data, array_combine(
            array_map(fn($key) => "where_{$key}", array_keys($where)),
            array_values($where)
        ));

        $stmt = $this->pdo->prepare("UPDATE {$table} SET {$setClause} WHERE {$whereClause}");
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Deletes records from the specified table based on conditions.
     */
    public function delete(string $table, array $where): int
    {
        $this->validateTableName($table);
        $whereClause = implode(" AND ", array_map(fn($key) => "{$key} = :{$key}", array_keys($where)));

        $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE {$whereClause}");
        $stmt->execute($where);

        return $stmt->rowCount();
    }

    /**
     * Performs a join query.
     *
     * @param string $baseTable
     * @param string $joinType
     * @param string $joinTable
     * @param string $onCondition
     * @param array $fields
     * @return array
     */
    public function join(
        string $baseTable,
        string $joinType,
        string $joinTable,
        string $onCondition,
        array $fields = ['*']
    ): array {
        $this->validateTableName($baseTable);
        $this->validateTableName($joinTable);

        $joinType = strtoupper($joinType);
        if (!in_array($joinType, ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL OUTER JOIN', 'CROSS JOIN'], true)) {
            throw new InvalidArgumentException("Invalid join type: {$joinType}");
        }

        $fieldList = implode(", ", $fields);
        $query = "SELECT {$fieldList} FROM {$baseTable} {$joinType} {$joinTable} ON {$onCondition}";

        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Fetch multiple records from the User table.
     */
    public function findManyUser(): array
    {
        return $this->findMany("User");
    }

    /**
     * Create a new record in the User table.
     */
    public function createUser(array $data): int
    {
        return $this->create("User", $data);
    }

    /**
     * Update records in the User table based on conditions.
     */
    public function updateUser(array $data, array $where): int
    {
        return $this->update("User", $data, $where);
    }

    /**
     * Delete records from the User table based on conditions.
     */
    public function deleteUser(array $where): int
    {
        return $this->delete("User", $where);
    }


    /**
     * Fetch multiple records from the Role table.
     */
    public function findManyRole(): array
    {
        return $this->findMany("Role");
    }

    /**
     * Create a new record in the Role table.
     */
    public function createRole(array $data): int
    {
        return $this->create("Role", $data);
    }

    /**
     * Update records in the Role table based on conditions.
     */
    public function updateRole(array $data, array $where): int
    {
        return $this->update("Role", $data, $where);
    }

    /**
     * Delete records from the Role table based on conditions.
     */
    public function deleteRole(array $where): int
    {
        return $this->delete("Role", $where);
    }


    /**
     * Fetch multiple records from the Product table.
     */
    public function findManyProduct(): array
    {
        return $this->findMany("Product");
    }

    /**
     * Create a new record in the Product table.
     */
    public function createProduct(array $data): int
    {
        return $this->create("Product", $data);
    }

    /**
     * Update records in the Product table based on conditions.
     */
    public function updateProduct(array $data, array $where): int
    {
        return $this->update("Product", $data, $where);
    }

    /**
     * Delete records from the Product table based on conditions.
     */
    public function deleteProduct(array $where): int
    {
        return $this->delete("Product", $where);
    }


    /**
     * Fetch multiple records from the Order table.
     */
    public function findManyOrder(): array
    {
        return $this->findMany("Order");
    }

    /**
     * Create a new record in the Order table.
     */
    public function createOrder(array $data): int
    {
        return $this->create("Order", $data);
    }

    /**
     * Update records in the Order table based on conditions.
     */
    public function updateOrder(array $data, array $where): int
    {
        return $this->update("Order", $data, $where);
    }

    /**
     * Delete records from the Order table based on conditions.
     */
    public function deleteOrder(array $where): int
    {
        return $this->delete("Order", $where);
    }
}
