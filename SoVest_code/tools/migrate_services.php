<?php
/**
 * SoVest - Service Migration Tool
 *
 * This script analyzes existing service files in the services/ directory
 * and creates corresponding interface files in app/Services/Interfaces/
 * and implementation files in app/Services/ with proper namespace and 
 * interface implementations.
 * 
 * Usage: php migrate_services.php
 */

// Define paths
define('ROOT_DIR', dirname(__DIR__));
define('SERVICES_DIR', ROOT_DIR . '/services');
define('INTERFACES_DIR', ROOT_DIR . '/app/Services/Interfaces');
define('NEW_SERVICES_DIR', ROOT_DIR . '/app/Services');

// Create output buffer for logging
$report = [];
$pendingTasks = [];

/**
 * Main function to run the migration script
 */
function runMigration() {
    global $report, $pendingTasks;
    
    // Make sure the necessary directories exist
    checkAndCreateDirectories();
    
    // Get all service files
    $serviceFiles = glob(SERVICES_DIR . '/*.php');
    
    echo "===================================================\n";
    echo "SoVest Service Migration Tool\n";
    echo "===================================================\n";
    echo "Starting service migration...\n";
    echo "Found " . count($serviceFiles) . " service files to process.\n";
    
    foreach ($serviceFiles as $serviceFile) {
        $serviceName = basename($serviceFile, '.php');
        echo "\nProcessing $serviceName...\n";
        
        // Parse the service file to extract information
        $serviceInfo = parseServiceFile($serviceFile);
        
        if (!$serviceInfo) {
            $report[] = "ERROR: Could not parse $serviceName";
            continue;
        }
        
        // Check if interface already exists
        $interfaceName = $serviceName . 'Interface';
        $interfaceFile = INTERFACES_DIR . '/' . $interfaceName . '.php';
        
        if (!file_exists($interfaceFile)) {
            // Create interface file
            createInterfaceFile($serviceInfo, $interfaceName);
            $report[] = "Created new interface: $interfaceName";
        } else {
            $report[] = "Interface $interfaceName already exists, skipping";
            // Check if interface has all methods from the service
            $pendingTasks[] = "Manual task: Verify that $interfaceName contains all required methods from $serviceName";
        }
        
        // Create implementation file
        $implementationFile = NEW_SERVICES_DIR . '/' . $serviceName . '.php';
        
        if (!file_exists($implementationFile)) {
            createImplementationFile($serviceInfo, $serviceName, $interfaceName);
            $report[] = "Created new implementation: $serviceName in app/Services/";
        } else {
            $report[] = "Implementation $serviceName already exists in app/Services/, skipping";
            $pendingTasks[] = "Manual task: Update $serviceName implementation with latest methods from original service";
        }
    }
    
    // Generate final report
    generateReport();
}

/**
 * Check if directories exist and create them if needed
 */
function checkAndCreateDirectories() {
    if (!is_dir(INTERFACES_DIR)) {
        mkdir(INTERFACES_DIR, 0755, true);
        echo "Created directory: " . INTERFACES_DIR . "\n";
    }
    
    if (!is_dir(NEW_SERVICES_DIR)) {
        mkdir(NEW_SERVICES_DIR, 0755, true);
        echo "Created directory: " . NEW_SERVICES_DIR . "\n";
    }
}

/**
 * Parse a service file to extract class, namespace, methods, and dependencies
 * 
 * @param string $filePath Path to the service file
 * @return array|false Information about the service or false on failure
 */
function parseServiceFile($filePath) {
    $content = file_get_contents($filePath);
    if (!$content) {
        return false;
    }
    
    $serviceInfo = [
        'name' => basename($filePath, '.php'),
        'namespace' => 'Services', // Default namespace
        'methods' => [],
        'dependencies' => [],
        'implements' => [],
        'docblock' => '',
        'content' => $content
    ];
    
    // Extract namespace
    if (preg_match('/namespace\s+([^;]+);/m', $content, $matches)) {
        $serviceInfo['namespace'] = trim($matches[1]);
    }
    
    // Extract class name and implemented interfaces
    if (preg_match('/class\s+([^\s]+)(?:\s+implements\s+([^{]+))?/', $content, $matches)) {
        $serviceInfo['className'] = trim($matches[1]);
        
        if (isset($matches[2])) {
            $interfaces = explode(',', $matches[2]);
            foreach ($interfaces as $interface) {
                $interface = trim($interface);
                // Extract just the interface name without the namespace
                if (strpos($interface, '\\') !== false) {
                    $parts = explode('\\', $interface);
                    $interface = end($parts);
                }
                $serviceInfo['implements'][] = $interface;
            }
        }
    }
    
    // Extract class docblock
    if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
        $serviceInfo['docblock'] = $matches[0];
    }
    
    // Extract constructor dependencies
    if (preg_match('/public\s+function\s+__construct\s*\((.*?)\)/s', $content, $matches)) {
        $params = $matches[1];
        
        // Parse parameters with type hints
        if (!empty($params)) {
            $parameters = explode(',', $params);
            foreach ($parameters as $param) {
                $param = trim($param);
                if (empty($param)) continue;
                
                // Extract type hint and parameter name
                if (preg_match('/^(?:([^\$]+))?\s*\$([^\s=]+)(?:\s*=\s*(.+))?$/', $param, $paramMatches)) {
                    $type = isset($paramMatches[1]) ? trim($paramMatches[1]) : null;
                    $name = $paramMatches[2];
                    $default = isset($paramMatches[3]) ? trim($paramMatches[3]) : null;
                    
                    $serviceInfo['dependencies'][] = [
                        'type' => $type,
                        'name' => $name,
                        'default' => $default
                    ];
                }
            }
        }
    }
    
    // Extract public methods
    preg_match_all('/public\s+function\s+([^_\s(]+)\s*\((.*?)\)(?:\s*:\s*([^{]+))?/s', $content, $methodMatches, PREG_SET_ORDER);
    
    foreach ($methodMatches as $match) {
        $methodName = $match[1];
        $parameters = $match[2];
        $returnType = isset($match[3]) ? trim($match[3]) : null;
        
        // Skip getInstance method for interfaces
        if ($methodName === 'getInstance') {
            continue;
        }
        
        // Parse parameters
        $parsedParams = [];
        if (!empty($parameters)) {
            $params = explode(',', $parameters);
            foreach ($params as $param) {
                $param = trim($param);
                if (empty($param)) continue;
                
                // Extract type hint and parameter name
                if (preg_match('/^(?:([^\$]+))?\s*\$([^\s=]+)(?:\s*=\s*(.+))?$/', $param, $paramMatches)) {
                    $type = isset($paramMatches[1]) ? trim($paramMatches[1]) : null;
                    $name = $paramMatches[2];
                    $default = isset($paramMatches[3]) ? trim($paramMatches[3]) : null;
                    
                    $parsedParams[] = [
                        'type' => $type,
                        'name' => $name,
                        'default' => $default
                    ];
                }
            }
        }
        
        // Extract method docblock
        $docblock = '';
        $methodPos = strpos($content, $match[0]);
        $beforeMethod = substr($content, 0, $methodPos);
        if (preg_match('/\/\*\*(.*?)\*\//s', strrev($beforeMethod), $docMatches)) {
            $docblock = strrev($docMatches[0]);
        }
        
        $serviceInfo['methods'][$methodName] = [
            'name' => $methodName,
            'parameters' => $parsedParams,
            'returnType' => $returnType,
            'docblock' => $docblock
        ];
    }
    
    return $serviceInfo;
}

/**
 * Create an interface file based on service information
 * 
 * @param array $serviceInfo Service information
 * @param string $interfaceName Interface name
 * @return bool Success status
 */
function createInterfaceFile($serviceInfo, $interfaceName) {
    $interfaceContent = "<?php\n/**\n * SoVest - " . $serviceInfo['name'] . " Interface\n *\n * This interface defines the contract for " . strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $serviceInfo['name'])) . " in the SoVest application.\n */\n\nnamespace App\\Services\\Interfaces;\n\ninterface $interfaceName\n{\n";
    
    // Add method declarations
    foreach ($serviceInfo['methods'] as $method) {
        // Add method docblock if available
        if (!empty($method['docblock'])) {
            $interfaceContent .= "    " . str_replace("\n", "\n    ", $method['docblock']) . "\n";
        }
        
        // Add method signature
        $interfaceContent .= "    public function " . $method['name'] . "(";
        
        // Add parameters
        $params = [];
        foreach ($method['parameters'] as $param) {
            $paramStr = '';
            if (!empty($param['type'])) {
                $paramStr .= $param['type'] . ' ';
            }
            $paramStr .= '$' . $param['name'];
            if (isset($param['default'])) {
                $paramStr .= ' = ' . $param['default'];
            }
            $params[] = $paramStr;
        }
        $interfaceContent .= implode(", ", $params);
        
        // Add return type if available
        if (!empty($method['returnType'])) {
            $interfaceContent .= "): " . $method['returnType'];
        } else {
            $interfaceContent .= ")";
        }
        
        $interfaceContent .= ";\n\n";
    }
    
    $interfaceContent .= "}";
    
    // Write the interface file
    file_put_contents(INTERFACES_DIR . '/' . $interfaceName . '.php', $interfaceContent);
    return true;
}

/**
 * Create an implementation file based on service information
 * 
 * @param array $serviceInfo Service information
 * @param string $serviceName Service name
 * @param string $interfaceName Interface name
 * @return bool Success status
 */
function createImplementationFile($serviceInfo, $serviceName, $interfaceName) {
    // Start with the basic structure
    $content = "<?php\n";
    
    // Add docblock if available
    if (!empty($serviceInfo['docblock'])) {
        $content .= str_replace('SoVest - ', 'SoVest - New ', $serviceInfo['docblock']) . "\n";
    } else {
        $content .= "/**\n * SoVest - New $serviceName\n *\n * This service provides " . strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $serviceName)) . " functionality\n * following the new service architecture in the application.\n */\n";
    }
    
    // Add namespace and use statements
    $content .= "\nnamespace App\\Services;\n\n";
    
    // Collect use statements to avoid duplicates
    $useStatements = ["App\\Services\\Interfaces\\$interfaceName" => true];
    
    // Add use statements for existing namespace classes if needed
    if ($serviceInfo['namespace'] != 'App\\Services') {
        // Check for use statements in the original file
        preg_match_all('/use\s+([^;]+);/', $serviceInfo['content'], $useMatches);
        if (!empty($useMatches[1])) {
            foreach ($useMatches[1] as $useStatement) {
                // Skip if already added or if it's the interface we're implementing
                if (!isset($useStatements[$useStatement])) {
                    $useStatements[$useStatement] = true;
                }
            }
        }
    }
    
    // Add exception use statement if needed
    if (strpos($serviceInfo['content'], 'Exception') !== false) {
        $useStatements['Exception'] = true;
    }
    
    // Add all use statements in sorted order
    foreach (array_keys($useStatements) as $useStatement) {
        $content .= "use $useStatement;\n";
    }
    
    // Add exception use statement if not already present
    if (strpos($content, 'use Exception;') === false && strpos($serviceInfo['content'], 'Exception') !== false) {
        $content .= "use Exception;\n";
    }
    
    $content .= "\nclass $serviceName implements $interfaceName\n{\n";
    
    // Add singleton implementation if the original has getInstance
    if (strpos($serviceInfo['content'], 'getInstance') !== false) {
        $content .= "    /**\n     * @var $serviceName|null Singleton instance of the service\n     */\n    private static \$instance = null;\n\n";
        $content .= "    /**\n     * Get the singleton instance of $serviceName\n     *\n     * @return $serviceName\n     */\n    public static function getInstance()\n    {\n        if (self::\$instance === null) {\n            self::\$instance = new self();\n        }\n\n        return self::\$instance;\n    }\n\n";
    }
    
    // Add constructor with dependency injection
    $content .= "    /**\n     * Constructor - now public to support dependency injection\n     * while maintaining backward compatibility with singleton pattern\n     */\n    public function __construct(";
    
    // Add constructor parameters
    $params = [];
    foreach ($serviceInfo['dependencies'] as $dependency) {
        $paramStr = '';
        if (!empty($dependency['type'])) {
            // Update type hint to interface if available
            if (in_array($dependency['type'], ['AuthService', 'DatabaseService', 'SearchService', 'StockDataService', 'PredictionScoringService', 'ValidationService', 'ResponseFormatter'])) {
                $paramStr .= "App\\Services\\Interfaces\\" . $dependency['type'] . "Interface";
            } else {
                $paramStr .= $dependency['type'];
            }
            $paramStr .= ' ';
        }
        $paramStr .= '$' . $dependency['name'];
        if (isset($dependency['default'])) {
            $paramStr .= ' = ' . $dependency['default'];
        }
        $params[] = $paramStr;
    }
    $content .= implode(", ", $params);
    $content .= ")\n    {\n";
    
    // Add implementation notes for constructor
    $content .= "        // TODO: Implement constructor with proper dependency injection\n";
    $content .= "        // This is a generated stub, you may need to customize it\n\n";
    
    // Add session initialization if present in original
    if (strpos($serviceInfo['content'], 'session_start') !== false) {
        $content .= "        // Ensure sessions are started\n";
        $content .= "        if (session_status() === PHP_SESSION_NONE) {\n";
        $content .= "            session_start();\n";
        $content .= "        }\n";
    }
    
    $content .= "    }\n\n";
    
    // Add method implementations
    foreach ($serviceInfo['methods'] as $method) {
        // Skip getInstance method as we already added it
        if ($method['name'] === 'getInstance') {
            continue;
        }
        
        // Add method docblock
        if (!empty($method['docblock'])) {
            $content .= "    " . str_replace("\n", "\n    ", $method['docblock']) . "\n";
        } else {
            // Generate a basic docblock if none is available
            $content .= "    /**\n";
            $content .= "     * " . ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $method['name'])) . "\n";
            $content .= "     *\n";
            
            // Add parameter documentation
            foreach ($method['parameters'] as $param) {
                $paramType = !empty($param['type']) ? $param['type'] : "mixed";
                $paramDesc = ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $param['name']));
                $content .= "     * @param " . $paramType . " \$" . $param['name'] . " " . $paramDesc . "\n";
            }
            
            // Add return type documentation
            if (!empty($method['returnType'])) {
                $returnDesc = "";
                if (strpos($method['returnType'], 'bool') !== false) {
                    $returnDesc = "True on success, false on failure";
                } elseif (strpos($method['returnType'], 'int') !== false) {
                    $returnDesc = "Numeric result";
                } elseif (strpos($method['returnType'], 'string') !== false) {
                    $returnDesc = "String result";
                } elseif (strpos($method['returnType'], 'array') !== false) {
                    $returnDesc = "Array of results";
                } elseif ($method['returnType'] === 'void') {
                    $returnDesc = "No return value";
                } else {
                    $returnDesc = "Result of the operation";
                }
                $content .= "     * @return " . $method['returnType'] . " " . $returnDesc . "\n";
            } else {
                $content .= "     * @return mixed Result of the operation\n";
            }
            
            $content .= "     */\n";
        }
        
        // Add method signature
        $content .= "    public function " . $method['name'] . "(";
        
        // Add parameters
        $params = [];
        foreach ($method['parameters'] as $param) {
            $paramStr = '';
            if (!empty($param['type'])) {
                $paramStr .= $param['type'] . ' ';
            }
            $paramStr .= '$' . $param['name'];
            if (isset($param['default'])) {
                $paramStr .= ' = ' . $param['default'];
            }
            $params[] = $paramStr;
        }
        $content .= implode(", ", $params);
        
        // Add return type if available
        if (!empty($method['returnType'])) {
            $content .= "): " . $method['returnType'];
        } else {
            $content .= ")";
        }
        
        $content .= "\n    {\n";
        $content .= "        // TODO: Implement " . $method['name'] . " method\n";
        $content .= "        // This is a generated stub, you need to copy the implementation from the original service\n";
        $content .= "        // See: services/" . $serviceName . ".php for the original implementation\n\n";
        
        // Add return statement based on return type
        if (!empty($method['returnType'])) {
            if (strpos($method['returnType'], 'bool') !== false) {
                $content .= "        return false;\n";
            } elseif (strpos($method['returnType'], 'int') !== false) {
                $content .= "        return 0;\n";
            } elseif (strpos($method['returnType'], 'string') !== false) {
                $content .= "        return '';\n";
            } elseif (strpos($method['returnType'], 'array') !== false) {
                $content .= "        return [];\n";
            } elseif ($method['returnType'] === 'void') {
                // No return needed for void
            } else {
                $content .= "        return null;\n";
            }
        } else {
            $content .= "        return null;\n";
        }
        
        $content .= "    }\n\n";
    }
    
    // Close the class
    $content = rtrim($content) . "\n}";
    
    // Write the implementation file
    file_put_contents(NEW_SERVICES_DIR . '/' . $serviceName . '.php', $content);
    return true;
}

/**
 * Generate a report of the migration results
 */
function generateReport() {
    global $report, $pendingTasks;
    
    echo "\n\n=======================================\n";
    echo "SERVICE MIGRATION REPORT\n";
    echo "=======================================\n\n";
    
    echo "Actions Taken:\n";
    echo "-------------\n";
    foreach ($report as $action) {
        echo "- $action\n";
    }
    
    echo "\n\nPending Manual Tasks:\n";
    echo "------------------\n";
    foreach ($pendingTasks as $task) {
        echo "- $task\n";
    }
    
    echo "\n\nNext Steps:\n";
    echo "----------\n";
    echo "1. Review the generated interface and implementation files\n";
    echo "2. Copy method implementations from original services to the new ones\n";
    echo "3. Update service implementations to use dependency injection\n";
    echo "4. Add dependency injection properties and parameters to constructors\n";
    echo "5. Register the new services in bootstrap/container.php\n";
    echo "6. Update the ServiceFactory to use the new service implementations\n";
    echo "7. Gradually update controllers to use interfaces instead of concrete implementations\n";
    echo "8. Replace direct getInstance() calls with DI where possible\n";
    
    // Save report to file
    $reportContent = "SERVICE MIGRATION REPORT\n\n";
    $reportContent .= "Actions Taken:\n-------------\n";
    foreach ($report as $action) {
        $reportContent .= "- $action\n";
    }
    
    $reportContent .= "\n\nPending Manual Tasks:\n------------------\n";
    foreach ($pendingTasks as $task) {
        $reportContent .= "- $task\n";
    }
    
    $reportContent .= "\n\nNext Steps:\n----------\n";
    $reportContent .= "1. Review the generated interface and implementation files\n";
    $reportContent .= "2. Copy method implementations from original services to the new ones\n";
    $reportContent .= "3. Update service implementations to use dependency injection\n";
    $reportContent .= "4. Add dependency injection properties and parameters to constructors\n";
    $reportContent .= "5. Register the new services in bootstrap/container.php\n";
    $reportContent .= "6. Update the ServiceFactory to use the new service implementations\n";
    $reportContent .= "7. Gradually update controllers to use interfaces instead of concrete implementations\n";
    $reportContent .= "8. Replace direct getInstance() calls with DI where possible\n";
    
    file_put_contents(ROOT_DIR . '/service_migration_report.txt', $reportContent);
    echo "\nReport saved to service_migration_report.txt\n";
}

// Run the migration
runMigration();