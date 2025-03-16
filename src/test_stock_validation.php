<?php

// Initialize database and autoloader first
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/database.php';

use Database\Models\Stock;

echo "===== TESTING STOCK VALIDATION =====\n\n";

// Test Case 1: Valid stock
$validStock = new Stock();
$validStock->symbol = "AAPL";
$validStock->company_name = "Apple Inc.";
$validStock->sector = "Technology";

if ($validStock->validate()) {
    echo "Test 1 PASSED: Valid stock validated successfully\n";
} else {
    echo "Test 1 FAILED: Valid stock should validate\n";
    print_r($validStock->getErrors());
}

// Test Case 2: Missing symbol
$invalidStock1 = new Stock();
$invalidStock1->company_name = "Missing Symbol Company";
$invalidStock1->sector = "Finance";

if (!$invalidStock1->validate()) {
    echo "Test 2 PASSED: Caught missing symbol\n";
    echo "  Errors: " . implode(", ", $invalidStock1->getErrorsFor('symbol')) . "\n";
} else {
    echo "Test 2 FAILED: Should not validate without a symbol\n";
}

// Test Case 3: Missing company name
$invalidStock2 = new Stock();
$invalidStock2->symbol = "MSNG";
$invalidStock2->sector = "Energy";

if (!$invalidStock2->validate()) {
    echo "Test 3 PASSED: Caught missing company name\n";
    echo "  Errors: " . implode(", ", $invalidStock2->getErrorsFor('company_name')) . "\n";
} else {
    echo "Test 3 FAILED: Should not validate without a company name\n";
}

// Test Case 4: Symbol too long
$invalidStock3 = new Stock();
$invalidStock3->symbol = "WAAAYTOOLONG";
$invalidStock3->company_name = "Long Symbol Corp";
$invalidStock3->sector = "Technology";

if (!$invalidStock3->validate()) {
    echo "Test 4 PASSED: Caught too long symbol\n";
    echo "  Errors: " . implode(", ", $invalidStock3->getErrorsFor('symbol')) . "\n";
} else {
    echo "Test 4 FAILED: Should not validate with too long symbol\n";
}

// Test Case 5: Test symbol uniqueness
echo "\nSetting up uniqueness test with database query...\n";

// Attempt to simulate a duplicate condition
// First, create a mock method to simulate the database query
// This is a simplification since we can't create actual DB records in a test
echo "Note: The uniqueness test is simulating DB behavior. In real operation, it would query the actual database.\n";

class StockWithMockDB extends Stock {
    public function validateUnique($attribute, $value, $parameters = []) {
        // Mock implementation to simulate a duplicate symbol
        if ($value === 'DUPL') {
            $this->addError($attribute, "The $attribute has already been taken.");
            return false;
        }
        return true;
    }
}

$duplicateStock = new StockWithMockDB();
$duplicateStock->symbol = "DUPL";  // This will trigger our mock duplicate condition
$duplicateStock->company_name = "Duplicate Inc.";
$duplicateStock->sector = "Technology";

if (!$duplicateStock->validate()) {
    echo "Test 5 PASSED: Caught duplicate symbol\n";
    echo "  Errors: " . implode(", ", $duplicateStock->getErrorsFor('symbol')) . "\n";
} else {
    echo "Test 5 FAILED: Should not validate with duplicate symbol\n";
}

// Test Case 6: Empty sector is valid (sector is optional)
$validStockWithoutSector = new Stock();
$validStockWithoutSector->symbol = "NOSEC";
$validStockWithoutSector->company_name = "No Sector Company";
$validStockWithoutSector->sector = null; // explicitly set to null to override default

if ($validStockWithoutSector->validate()) {
    echo "Test 6 PASSED: Stock validates with empty sector (sector is optional)\n";
} else {
    echo "Test 6 FAILED: Stock should validate with empty sector\n";
    print_r($validStockWithoutSector->getErrors());
}

echo "\n===== STOCK VALIDATION TESTING COMPLETE =====\n";