<?php

declare(strict_types=1);

namespace Models;

include __DIR__ . '../../../autoload.php';

use Exception;
use myPDO;
use PDO;

class Model {

    protected PDO $pdo;
    protected string $table = '';
    protected array $attributes = [];
    protected array $fillable = [];

    public function __construct(array $attributes = [], bool $stringsToNumeric = false)
    {
        $this->pdo = new MyPDO();
        $this->fill($attributes, $stringsToNumeric);
    }

    public function get(array $data): array
    {
        $keys = $this->prepareKeys($data);
        $query = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE ' . $keys);
        $query->execute(array_values(array_values($data)));
        $collection = [];
        $class = get_class($this);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $collection[] = new $class($row);       
        }
    
        return $collection;
    }

    public function getAttribute($attribute): string|int|float|bool
    {
        return $this->attributes[$attribute];
    }

    public function fill(array $attributes, bool $stringsToNumeric = false): self
    {
        foreach ($attributes as $attribute => $value) {
            $type = $this->fillable[$attribute];
            if(empty($type)) {
                continue;
            }
            $valueType = gettype($value);
            if($valueType === $type
                || $type === 'bool' && $valueType === 'boolean'
                || $type === 'int' && $valueType === 'integer'
                || $type === 'float' && $valueType === 'double'
            ) {
                $this->attributes[$attribute] = $value;
            } elseif ($stringsToNumeric && is_numeric($value)) {
                if (($type === 'int' || $type === 'integer') && (int)$value == $value) {
                        $this->attributes[$attribute] = intval($value);
                } elseif ($type === 'float' && (float)$value == $value) {
                    $this->attributes[$attribute] = floatval($value);
                }
            } elseif (($value === 1 || $value === 0) && ($type === 'bool' || $type === 'boolean')) {
                $this->attributes[$attribute] = (bool)$value;
            } elseif ($valueType !== $type) {
                throw new Exception('Wrong type of attribute value: type of value ' . $attribute . ' is ' . $valueType . ', ' . ($type === 'double' ? 'float' : $type) . ' expected');
            }
        }

        return $this;
    }

    public function save(): bool
    {
        if(empty($this->attributes)) {
            return false;
        }
        $values = $this->prepareEmptyValues($this->attributes);
        $keys = implode(', ', array_keys($this->attributes));
        $query = $this->pdo->prepare('INSERT INTO ' . $this->table . ' (' . $keys . ') VALUES (' . $values . ');');
        return $query->execute(array_values($this->attributes));
    }


    public function setAttribute(string $attribute, string|int|float|bool $value): bool
    {
        return $this->attributes[$attribute] = $value;
    }

    private function prepareKeys(array $data): string
    {
        $cols = [];
        foreach($data as $key => $value) {
            $cols[] = $key . '=?';
        }
        $cols = implode(' AND ', $cols);
        return $cols;
    }

    private function prepareEmptyValues($data): string
    {
        $cols = [];
        for($i = 0; $i<count($this->attributes); $i++) {
            $cols[] = '?';
        }
        $cols = implode(', ', $cols);
        return $cols;
    }

}