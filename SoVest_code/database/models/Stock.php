<?php

namespace Database\Models; // Change this if you're using a different namespace

use Illuminate\Database\Eloquent\Model;
use Database\Models\Traits\ValidationTrait;

class Stock extends Model {
    use ValidationTrait;

    // Table name (optional if follows Laravel's naming convention)
    protected $table = 'stocks';

    // Primary key (optional, as Eloquent assumes 'id' by default)
    protected $primaryKey = 'stock_id';

    // Disable timestamps if the table does not have `updated_at`
    public $timestamps = false;

    // Allow mass assignment for these columns
    protected $fillable = ['symbol', 'company_name', 'sector', 'created_at'];

    // Set default values
    protected $attributes = [
        'sector' => 'Unknown', // Default value for sector
    ];
    
    /**
     * Validation rules for Stock model
     */
    protected $rules = [
        'symbol' => ['required', 'max:10', 'unique'],
        'company_name' => ['required'],
        'sector' => []
    ];

    /**
     * Custom error messages for validation
     */
    protected $messages = [
        'symbol.required' => 'Stock symbol is required',
        'symbol.max' => 'Stock symbol cannot exceed 10 characters',
        'symbol.unique' => 'This stock symbol is already registered in the system',
        'company_name.required' => 'Company name is required'
    ];

    /**
     * Validate uniqueness of stock symbol in database
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateUnique($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Build query to check for existing records
        $query = self::where($attribute, $value);
        
        // If updating an existing record, exclude the current record
        if ($this->exists) {
            $query->where($this->primaryKey, '!=', $this->{$this->primaryKey});
        }
        
        // If a record with this value exists, validation fails
        if ($query->exists()) {
            $this->addError($attribute, $this->getMessage($attribute, 'unique', "The $attribute has already been taken."));
            return false;
        }
        
        return true;
    }
    
    // Relationships
    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'stock_id');
    }
    
    public function prices()
    {
        return $this->hasMany(StockPrice::class, 'stock_id');
    }
}