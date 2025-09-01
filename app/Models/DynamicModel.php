<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Traits\Filterable;

class DynamicModel extends Model
{
    use Filterable;
    protected $guarded = [];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    /**
     * Set the table name dynamically
     */
    public function setTableName(string $tableName): self
    {
        $this->table = $tableName;
        return $this;
    }
    
    /**
     * Ensure table exists and add new columns
     */
    public function ensureTableAndColumns(string $table, array $data): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $tableDef) use ($data) {
                $tableDef->id();
                foreach ($data as $field => $value) {
                    $tableDef->string($field)->nullable();
                }
                $tableDef->timestamps();
            });
        } else {
            Schema::table($table, function (Blueprint $tableDef) use ($data, $table) {
                foreach ($data as $field => $value) {
                    if (!Schema::hasColumn($table, $field)) {
                        $tableDef->string($field)->nullable();
                    }
                }
            });
        }
    }
    
    /**
     * Create a new instance for a specific table
     */
    public static function forTable(string $tableName): self
    {
        $instance = new static();
        $instance->setTableName($tableName);
        return $instance;
    }
    
    /**
     * Create a record with table creation if needed
     */
    public function createWithTable(array $data): self
    {
        $this->ensureTableAndColumns($this->getTable(), $data);
        return $this->create($data);
    }
    
    /**
     * Get all records from the table
     */
    public function getAllRecords()
    {
        return $this->all();
    }
    
    /**
     * Find record by ID
     */
    public function findRecord($id)
    {
        return $this->find($id);
    }
    
    /**
     * Update record by ID
     */
    public function updateRecord($id, array $data): bool
    {
        $this->ensureTableAndColumns($this->getTable(), $data);
        $record = $this->find($id);
        if ($record) {
            return $record->update($data);
        }
        return false;
    }
    
    /**
     * Delete record by ID
     */
    public function deleteRecord($id): bool
    {
        $record = $this->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
}
