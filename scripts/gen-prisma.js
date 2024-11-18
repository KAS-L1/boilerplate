const fs = require("fs");
const path = require("path");

// Define file paths
const schemaFile = "./prisma/schema.prisma"; // Correct relative path to schema.prisma
const outputFile = "./app/Models/Prisma.php"; // Correct relative path to Prisma.php

// Ensure the output directory exists
const outputDir = path.dirname(outputFile);
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}
// Read and parse schema models
const schemaContent = fs.readFileSync(schemaFile, "utf-8");
const modelRegex = /model\s+(\w+)\s*{[\s\S]*?}/g;
const models = [...schemaContent.matchAll(modelRegex)].map((match) => match[1]);

// Generate CRUD methods dynamically
const generateMethods = (model) => `
    /**
     * Fetch multiple records from the ${model} table.
     */
    public function findMany${model}(): array {
        return $this->findMany("${model}");
    }

    /**
     * Create a new record in the ${model} table.
     */
    public function create${model}(array $data): int {
        return $this->create("${model}", $data);
    }

    /**
     * Update records in the ${model} table based on conditions.
     */
    public function update${model}(array $data, array $where): int {
        return $this->update("${model}", $data, $where);
    }

    /**
     * Delete records from the ${model} table based on conditions.
     */
    public function delete${model}(array $where): int {
        return $this->delete("${model}", $where);
    }
`;

// Generate the full Prisma class
const generatedClass = `<?php

declare(strict_types=1);

class Prisma {
    private PDO $pdo;

    /**
     * Prisma constructor.
     */
    public function __construct(private string $dsn, private string $user, private string $password) {
        $this->connect();
    }

    /**
     * Establishes a connection to the database.
     */
    private function connect(): void {
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
    private function validateTableName(string $table): void {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new InvalidArgumentException("Invalid table name: {$table}");
        }
    }

    /**
     * Fetches multiple records from the specified table.
     */
    public function findMany(string $table): array {
        $this->validateTableName($table);
        $stmt = $this->pdo->query("SELECT * FROM " . $table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserts a new record into the specified table.
     */
    public function create(string $table, array $data): int {
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
    public function update(string $table, array $data, array $where): int {
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
    public function delete(string $table, array $where): int {
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

${models.map((model) => generateMethods(model)).join("\n")}
}
`;

// Write the generated PHP class to a file
fs.writeFileSync(outputFile, generatedClass, "utf-8");
console.log("Prisma.php generated successfully!");
