<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ValidationTrait;

class Prediction extends Model {
    use ValidationTrait;

    // Table name
    protected $table = 'predictions';

    // Primary key
    protected $primaryKey = 'prediction_id';

    // Timestamps (using prediction_date instead of created_at)
    public $timestamps = false;

    // Allow mass assignment for these columns
    protected $fillable = [
        'user_id',
        'stock_id',
        'prediction_type',
        'target_price',
        'prediction_date',
        'end_date',
        'is_active',
        'accuracy',
        'reasoning'
    ];

    /**
     * Validation rules for Prediction model
     */
    protected $rules = [
        'user_id' => ['required', 'exists'],
        'stock_id' => ['required', 'exists'],
        'prediction_type' => ['required', 'in:Bullish,Bearish'],
        'target_price' => ['numeric', 'nullable'],
        'end_date' => ['required', 'date', 'futureDate'],
        'reasoning' => ['required']
    ];

    /**
     * Custom error messages for validation
     */
    protected $messages = [
        'user_id.required' => 'User ID is required',
        'user_id.exists' => 'The selected user does not exist',
        'stock_id.required' => 'Stock ID is required',
        'stock_id.exists' => 'The selected stock does not exist',
        'prediction_type.required' => 'Prediction type is required',
        'prediction_type.in' => 'Prediction type must be either Bullish or Bearish',
        'target_price.numeric' => 'Target price must be a numeric value',
        'end_date.required' => 'End date is required',
        'end_date.date' => 'End date must be a valid date',
        'end_date.futureDate' => 'End date must be a future date',
        'reasoning.required' => 'Reasoning for your prediction is required'
    ];

    /**
     * Validate if a record exists in the database
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateExists($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Determine the table and column to check
        $table = null;
        if ($attribute === 'user_id') {
            $model = new User();
            $table = $model->getTable();
            $column = $model->getKeyName();
        } elseif ($attribute === 'stock_id') {
            $model = new Stock();
            $table = $model->getTable();
            $column = $model->getKeyName();
        } else {
            $this->addError($attribute, "Cannot validate existence for $attribute");
            return false;
        }

        // Check if the record exists
        $exists = $model->where($column, $value)->exists();
        
        if (!$exists) {
            $this->addError($attribute, $this->getMessage($attribute, 'exists', "The selected $attribute does not exist."));
            return false;
        }
        
        return true;
    }

    /**
     * Validate that a date is in the future
     * 
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return boolean
     */
    public function validateFutureDate($attribute, $value, $parameters = [])
    {
        if (empty($value)) {
            return true;
        }

        // Parse the date
        $date = date_parse($value);
        if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
            // This is already checked by the date validator
            return true;
        }

        // Convert to a DateTime object
        $dateObj = new \DateTime($value);
        $now = new \DateTime();

        // Check if the date is in the future
        if ($dateObj <= $now) {
            $this->addError($attribute, $this->getMessage($attribute, 'futureDate', "The $attribute must be a date in the future."));
            return false;
        }
        
        return true;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function votes()
    {
        return $this->hasMany(PredictionVote::class, 'prediction_id');
    }
}