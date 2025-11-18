<?php
namespace Page\Fetch;

require "/home/flora/Projects/www/wdv341/99-db/db-new.php";

use PDO, PDOStatement, PDOException;

class Fetch {
    public null | array | PDOException $current = null;
    public int $currentRow = 0;

    private PDOStatement | PDOException $op;

    public function __construct(string $sql, array $args = []) {
        $this->op = $this->prepare($sql, $args);
    }

    private function prepare(
        string $sql,
        array $args = [],
    ): PDOStatement | PDOException {
        $db = \Database::get();
        if ($db instanceof PDOException) return $db;

        $prepared = $db->connection->prepare($sql);
        if ($prepared instanceof PDOException) return $prepared;

        $prepared->execute(\count($args) > 0 ? $args : null);
        return $prepared;
    }

    public function next(): bool | PDOException {
        if ($this->op instanceof PDOException) return $this->op;
        if ($this->op->rowCount() == 0)
            return new PDOException("No items found!");

        $this->currentRow += 1;
        if ($this->currentRow > $this->op->rowCount()) {
            return false;
        }

        $this->current = $this->op->fetch(PDO::FETCH_ASSOC);
        return true;
    }
}
?>