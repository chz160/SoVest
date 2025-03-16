# SoVest Windows Installation Guide

This guide provides comprehensive instructions for installing and configuring the SoVest stock prediction application on Windows systems.

## 1. Prerequisites and System Requirements

### Hardware Requirements
- **Processor**: 2.0 GHz dual-core processor or better
- **Memory**: Minimum 4GB RAM (8GB recommended)
- **Disk Space**: At least 2GB free space for application and dependencies

### Software Requirements
- **Operating System**: Windows 10/11 (64-bit) or Windows Server 2016/2019/2022
- **PHP**: Version 8.4.x or higher
- **MySQL**: Version 5.7 or higher
- **NGINX**: Latest stable version
- **Composer**: Latest version

### Required Privileges
- Administrative access to install software
- Ability to create and configure Windows scheduled tasks
- Permission to configure firewall settings (if needed)

## 2. Installing PHP on Windows

### Download PHP
1. Visit the PHP for Windows download page: https://windows.php.net/download/
2. Download the **PHP 8.4+** (or newer) **x64 Non-Thread Safe** zip package
   - The Non-Thread Safe version is recommended for use with NGINX

### Install PHP
1. Create a directory for PHP at `C:\\php`
2. Extract the downloaded zip file to this directory
3. Rename `php.ini-development` to `php.ini` (for development environments) or `php.ini-production` to `php.ini` (for production environments)

### Configure PHP
1. Open `php.ini` in a text editor
2. Enable required extensions by removing the semicolon (`;`) from the beginning of these lines:
   ```ini
   extension=php_curl.dll
   extension=php_fileinfo.dll
   extension=php_mbstring.dll
   extension=php_openssl.dll
   extension=php_pdo_mysql.dll
   extension=php_exif.dll
   ```
   Note: If you have php located anywhere other than C:\php than you will need to use the full path to the files above.
3. Set the timezone by locating and updating the following line:
   ```ini
   date.timezone = "America/Chicago"
   ```
   Replace with your appropriate timezone from the [PHP timezone list](https://www.php.net/manual/en/timezones.php)

4. Configure error reporting (for production):
   ```ini
   display_errors = Off
   log_errors = On
   error_log = "C:\\php\\logs\\php_errors.log"
   ```

5. Create the log directory:
   ```
   mkdir C:\\php\\logs
   ```

### Add PHP to System PATH
1. Right-click on "This PC" or "Computer" and select "Properties"
2. Click on "Advanced system settings"
3. Click on the "Environment Variables" button
4. Under "System variables", find and select the "Path" variable, then click "Edit"
5. Click "New" and add `C:\\php`
6. Click "OK" to close all dialogs

### Verify PHP Installation
1. Open Command Prompt
2. Run the command:
   ```
   php -v
   ```
3. You should see output showing the PHP version, which confirms successful installation

## 3. Installing MySQL on Windows

### Download MySQL
1. Visit the MySQL Downloads page: https://dev.mysql.com/downloads/installer/
2. Download the MySQL Installer for Windows (mysql-installer-web-community)

### Install MySQL
1. Run the downloaded installer
2. Choose "Custom" installation type
3. Select the following products:
   - MySQL Server 5.7+ (or newer)
   - MySQL Workbench
   - MySQL Shell
   - Connector/NET, Connector/ODBC, and Connector/J (optional)
4. Click "Next" and proceed with the installation
5. During configuration, set the following:
   - Choose "Development Computer" configuration type
   - Set a strong root password and record it securely
   - Create a MySQL user account if prompted
   - Configure MySQL as a Windows service to start automatically

### Create SoVest Database and User
1. Open MySQL Workbench
2. Connect to your MySQL instance using the root credentials
3. Create the SoVest database with the following SQL commands:
   ```sql
   CREATE DATABASE sovest;
   
   CREATE USER 'sovest_user'@'localhost' IDENTIFIED BY 'sovest_is_dope';
   
   GRANT ALL PRIVILEGES ON sovest.* TO 'sovest_user'@'localhost';
   
   FLUSH PRIVILEGES;
   ```
   Note: Replace 'sovest_is_dope' with a stronger password for production environments

### MySQL Performance Configuration
1. Locate your MySQL configuration file (`my.ini`, typically in `C:\\ProgramData\\MySQL\\MySQL Server 5.7\\`)
2. Add or modify the following settings for basic optimization:
   ```ini
   [mysqld]
   # Memory settings
   innodb_buffer_pool_size = 512M
   
   # Performance settings
   innodb_flush_log_at_trx_commit = 2
   innodb_flush_method = O_DIRECT
   
   # Character set
   character-set-server = utf8mb4
   collation-server = utf8mb4_unicode_ci
   ```
3. Restart MySQL service:
   - Open Command Prompt as Administrator
   - Run the command:
     ```
     net stop mysql57 && net start mysql57
     ```
     (Replace `mysql57` with your MySQL service name if different)

## 4. Installing and Configuring NGINX

### Download NGINX
1. Visit the NGINX Windows download page: http://nginx.org/en/download.html
2. Download the latest stable version (Mainline version)

### Install NGINX
1. Create a directory at `C:\\nginx`
2. Extract the downloaded zip file contents to this directory

### Configure NGINX as a Windows Service
1. Download the NSSM (Non-Sucking Service Manager) from https://nssm.cc/download
2. Extract NSSM to a folder (e.g., `C:\\tools\\nssm`)
3. Open Command Prompt as Administrator
4. Navigate to the NSSM directory and run:
   ```
   nssm install nginx
   ```
5. In the GUI that appears, configure the following:
   - Path: `C:\\nginx\\nginx.exe`
   - Startup directory: `C:\\nginx`
   - Service name: nginx
6. Click "Install service"

## 5. Detailed NGINX Configuration for SoVest

### Creating a Complete NGINX Server Block Configuration

1. Create a new configuration file at `C:\\nginx\\conf\\conf.d\\sovest.conf` with the following content:

```nginx
# SoVest NGINX Server Configuration

server {
    # Listen on port 80 for HTTP connections
    listen 80;
    
    # Server name (domain) - change to your actual domain or use 'sovest.local' for local development
    server_name sovest.local;
    
    # Document root - change to your actual SoVest installation location
    root C:/path/to/SoVest/SoVest_code/public;
    
    # Default index files
    index index.php index.html;
    
    # Character encoding
    charset utf-8;
    
    # Logs - customize locations if needed
    access_log C:/nginx/logs/sovest.access.log;
    error_log C:/nginx/logs/sovest.error.log;
    
    # Handle page that doesn't exist (404)
    error_page 404 /index.php;
    
    # Deny access to hidden files (starting with a dot)
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Main location block - this handles URL rewriting for the application's router
    location / {
        # Try to serve the file directly, then directory, then fall back to index.php
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Process PHP files through PHP-FPM
    location ~ \.php$ {
        # Ensure file exists before processing
        try_files $uri =404;
        
        # PHP-FPM connection settings
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        
        # Include standard FastCGI parameters
        include fastcgi_params;
        
        # Set script filename parameter for PHP-FPM
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # FastCGI performance settings
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_busy_buffers_size 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }
    
    # Deny access to specific file types
    location ~* \.(htaccess|htpasswd|ini|log|sh|sql)$ {
        deny all;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

### Understanding the NGINX Configuration Directives

- **`listen 80`**: Specifies that NGINX should listen on port 80 for HTTP connections.
- **`server_name`**: Defines which domain names the server block applies to.
- **`root`**: Specifies the document root directory where NGINX should look for files.
- **`index`**: Defines the default files to look for when a directory is requested.
- **`error_page`**: Specifies a custom page to display for HTTP errors (like 404).
- **`try_files`**: Tries to serve files in order and falls back to index.php, essential for PHP routing.
- **`fastcgi_pass`**: Tells NGINX to pass PHP requests to PHP-FPM running on 127.0.0.1:9000.
- **`include fastcgi_params`**: Includes predefined FastCGI parameters for PHP.
- **`fastcgi_param`**: Sets specific parameters for PHP-FPM, especially SCRIPT_FILENAME which tells PHP which file to execute.
- **`expires`**: Sets cache expiration for static files to improve performance.

### Include Custom Configuration in Main NGINX Config

1. Edit the main NGINX configuration file at `C:\\nginx\\conf\\nginx.conf`:
2. Find the `http` block and add the following line inside it:
   ```nginx
   # Include custom site configurations
   include conf.d/*.conf;
   ```

3. Create the conf.d directory if it doesn't exist:
   ```
   mkdir C:\\nginx\\conf\\conf.d
   ```

4. Test the NGINX configuration for syntax errors:
   ```
   C:\\nginx\\nginx.exe -t
   ```

## 6. PHP-FPM Windows Service Setup

### Installing PHP-FPM on Windows

1. If not already done, download the Non-Thread Safe PHP version as described in Section 2.
2. Create a dedicated directory for PHP-FPM at `C:\\php-fpm`
3. Copy all files from your PHP installation directory to the PHP-FPM directory
4. Create a PHP-FPM configuration file:
   
   ```
   copy C:\\php-fpm\\php-fpm.conf.default C:\\php-fpm\\php-fpm.conf
   ```

5. Edit `C:\\php-fpm\\php-fpm.conf` to configure PHP-FPM:

```ini
[global]
; Error log file
error_log = C:/php-fpm/logs/php-fpm.log

; Log level
log_level = notice

[www]
; The address on which to accept FastCGI requests.
listen = 127.0.0.1:9000

; Choose how the process manager will control the number of child processes.
; Possible values: static, dynamic, ondemand
pm = dynamic

; The number of child processes created on startup.
pm.start_servers = 2

; The desired minimum number of idle server processes.
pm.min_spare_servers = 1

; The desired maximum number of idle server processes.
pm.max_spare_servers = 3

; The number of child processes to be created when pm is set to 'dynamic'
pm.max_children = 5

; The number of seconds after which an idle process will be killed.
pm.process_idle_timeout = 10s

; The number of requests each child process should execute before respawning.
pm.max_requests = 200

; Make specific environment variables available to PHP
env[HOSTNAME] = $HOSTNAME
env[PATH] = $PATH
env[TMP] = $TMP
env[TEMP] = $TEMP
env[TMPDIR] = $TMPDIR

; Additional php.ini settings
php_admin_value[memory_limit] = 128M
php_admin_value[max_execution_time] = 300
php_admin_value[post_max_size] = 20M
php_admin_value[upload_max_filesize] = 20M
```

### Creating Required Directories

1. Create a logs directory for PHP-FPM:
   ```
   mkdir C:\\php-fpm\\logs
   ```

### Installing PHP-FPM as a Windows Service

1. Using NSSM to install PHP-FPM as a Windows service:
   
   ```
   nssm install php-fpm
   ```

2. In the NSSM GUI, configure the following:

   - **Path**: `C:\\php-fpm\\php-cgi.exe`
   - **Arguments**: `-b 127.0.0.1:9000`
   - **Startup directory**: `C:\\php-fpm`
   - **Service name**: `php-fpm`

3. In the Details tab (click on it after setting up the path):
   - **Display name**: PHP-FPM Service
   - **Description**: PHP FastCGI Process Manager

4. In the I/O tab:
   - **Output (stdout)**: `C:\\php-fpm\\logs\\stdout.log`
   - **Error (stderr)**: `C:\\php-fpm\\logs\\stderr.log`

5. In the Exit actions tab:
   - Check "Restart the service if it stops"
   - Set "Delay restart if the service stops" to 5000 milliseconds

6. Click "Install service"

### Starting and Configuring the PHP-FPM Service

1. Start the PHP-FPM service:
   ```
   nssm start php-fpm
   ```

2. Configure the service to start automatically:
   ```
   sc config php-fpm start= auto
   ```

3. Verify the service is running:
   ```
   sc query php-fpm
   ```
   This should show "RUNNING" as the STATE if the service started correctly.

### Common PHP-FPM Windows Configuration Issues

1. **Issue**: PHP-FPM not starting
   - **Solution**: Check Windows Event Viewer for errors
   - **Check**: Look for path issues, missing DLLs, or permission problems
   - **Resolution**: Install any missing Visual C++ Redistributables that PHP depends on

2. **Issue**: NGINX reports "502 Bad Gateway" errors
   - **Solution**: Verify PHP-FPM is running and configured correctly
   - **Check**: Run `netstat -ano | findstr :9000` to verify PHP-FPM is listening
   - **Resolution**: Restart PHP-FPM service and check logs

## 7. Performance Fine-Tuning for Windows Environments

### NGINX Performance Optimizations

1. Edit `C:\\nginx\\conf\\nginx.conf` and modify the http section:

```nginx
http {
    # Basic settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Buffer sizes
    client_body_buffer_size 10K;
    client_header_buffer_size 1k;
    client_max_body_size 8m;
    large_client_header_buffers 2 1k;
    
    # File cache settings
    open_file_cache max=1000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # Worker processes settings
    # This is included in the main nginx.conf section, not http section
    # worker_processes auto;
    # worker_connections 1024;
    
    # Gzip compression
    gzip on;
    gzip_disable "msie6";
    gzip_comp_level 6;
    gzip_min_length 1100;
    gzip_buffers 16 8k;
    gzip_proxied any;
    gzip_types
        text/plain
        text/css
        text/js
        text/xml
        text/javascript
        application/javascript
        application/json
        application/xml
        application/rss+xml
        image/svg+xml;
    
    # Include other configuration files
    include mime.types;
    default_type application/octet-stream;
    include conf.d/*.conf;
}
```

2. Add worker processes settings to the main context in `nginx.conf` (outside the http block):

```nginx
worker_processes auto; # Automatically set based on CPU cores
worker_rlimit_nofile 8192; # Maximum number of open files
events {
    worker_connections 1024;
    multi_accept on;
    use select; # Windows doesn't support epoll or kqueue
}
```

### PHP-FPM Memory Optimizations

1. Edit the PHP-FPM configuration based on your server specifications:

#### For Low-Resource Servers (4GB RAM or less)
```ini
[www]
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
php_admin_value[memory_limit] = 128M
```

#### For Medium-Resource Servers (8GB RAM)
```ini
[www]
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 1000
php_admin_value[memory_limit] = 256M
```

#### For High-Resource Servers (16GB+ RAM)
```ini
[www]
pm = dynamic
pm.max_children = 50
pm.start_servers = 8
pm.min_spare_servers = 5
pm.max_spare_servers = 15
pm.max_requests = 5000
php_admin_value[memory_limit] = 512M
```

### PHP OpCache Configuration

1. Edit your PHP-FPM php.ini file to optimize the OpCache:

```ini
[opcache]
; Enable Zend OPCache extension
zend_extension=opcache

; Enable opcache
opcache.enable=1

; Enable opcache for CLI 
opcache.enable_cli=0

; Memory consumption
opcache.memory_consumption=128

; Used to store interned strings
opcache.interned_strings_buffer=8

; Maximum number of files that can be stored in the cache
opcache.max_accelerated_files=10000

; Validate timestamps and check for changes
opcache.validate_timestamps=1
opcache.revalidate_freq=60

; Enable fast shutdown
opcache.fast_shutdown=1

; Enable file content caching
opcache.file_cache=C:\\php-fpm\\opcache
```

2. Create the opcache directory:
```
mkdir C:\\php-fpm\\opcache
```

### Optimizing Windows TCP/IP Settings for Web Servers

1. Open Command Prompt as Administrator

2. Run the following commands to optimize TCP/IP stack:

```
netsh interface tcp set global autotuninglevel=normal
netsh interface tcp set global rss=enabled
netsh interface tcp set global chimney=enabled
netsh interface tcp set global ecncapability=enabled
netsh interface tcp set global timestamps=disabled
```

3. Increase TCP/IP ports for better connection handling:

```
netsh int ipv4 set dynamicport tcp start=10000 num=55535
```

4. Modify registry for performance (create a .reg file with the following content and run it):

```reg
Windows Registry Editor Version 5.00

[HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters]
"TcpTimedWaitDelay"=dword:0000001e
"MaxUserPort"=dword:0000fffe
"TcpMaxDataRetransmissions"=dword:00000005
"SackOpts"=dword:00000001
"DefaultTTL"=dword:00000040
"EnablePMTUDiscovery"=dword:00000001
"TcpMaxDupAcks"=dword:00000002
```

### MySQL Caching Optimization

1. Edit your MySQL configuration file (`my.ini`) to add query cache settings:

```ini
[mysqld]
# Query cache configuration
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# InnoDB buffer pool (adjust based on available memory)
# For 4GB RAM servers
innodb_buffer_pool_size = 1G
# For 8GB RAM servers
# innodb_buffer_pool_size = 2G
# For 16GB+ RAM servers
# innodb_buffer_pool_size = 4G

# Other performance settings
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_read_io_threads = 4
innodb_write_io_threads = 4
```

2. Restart MySQL after changing configuration:
```
net stop mysql57 && net start mysql57
```

### Windows System Performance Settings

1. Disable unnecessary Windows services:
   - Open Services console (services.msc)
   - Set the following services to Manual or Disabled if not needed:
     - Windows Search
     - Windows Update
     - Print Spooler (if not using printer)
     - Superfetch/SysMain
     - Indexing Service

2. Configure System Performance Options:
   - Right-click on "This PC" or "Computer" → Properties → Advanced system settings
   - Under Performance, click "Settings"
   - Select "Adjust for best performance" or customize settings

3. Virtual Memory configuration:
   - In the same Performance Options dialog, go to the "Advanced" tab
   - Under Virtual Memory, click "Change"
   - Uncheck "Automatically manage paging file size"
   - Select "Custom size"
   - Set Initial size and Maximum size to 1.5 times your physical RAM
   - Click "Set" and then "OK"

## 8. Installing Composer and SoVest Dependencies

### Install Composer
1. Download the Composer installer from https://getcomposer.org/Composer-Setup.exe
2. Run the installer and follow the prompts
   - Ensure it can find your PHP installation
   - Allow the installer to add Composer to your PATH

### Verify Composer Installation
1. Open Command Prompt
2. Run:
   ```
   composer --version
   ```
3. You should see output displaying the Composer version

### Install SoVest Dependencies
1. Open Command Prompt
2. Navigate to your SoVest application root directory:
   ```
   cd C:\\path\\to\\SoVest\\SoVest_code
   ```
3. Install required dependencies:
   ```
   composer install
   ```
4. This process may take some time as it downloads and installs all dependencies defined in `composer.json`

## 9. SoVest Application Setup

### Obtain and Place Source Code
1. Clone or download the SoVest repository
2. Place the code in your desired location (e.g., `C:\\SoVest`)
3. Ensure proper folder structure with `SoVest_code` as the main application directory

### Environment Configuration
1. Create a `.env` file in the root of the `SoVest_code` directory:
   ```
   cd C:\\path\\to\\SoVest\\SoVest_code
   copy .env.example .env
   ```
   If `.env.example` doesn't exist, create a new `.env` file with the following content:
   ```
   DB_SERVER=localhost
   DB_USERNAME=sovest_user
   DB_PASSWORD=sovest_is_dope
   DB_NAME=sovest
   ALPHA_VANTAGE_API_KEY=your_api_key_here
   ```
   
2. Obtain an Alpha Vantage API key:
   - Visit https://www.alphavantage.co/support/#api-key
   - Register for a free API key
   - Replace `your_api_key_here` in the `.env` file with your actual API key
   - See Section 10 for detailed instructions on Alpha Vantage API configuration

### Run Database Migrations
1. Open Command Prompt
2. Navigate to your SoVest application directory:
   ```
   cd C:\\path\\to\\SoVest\\SoVest_code
   ```
3. Run the migration script:
   ```
   php migrate.php
   ```
4. Verify that all migrations completed successfully

### Set Application Permissions
1. Ensure the web server has read access to all application files
2. Ensure write permissions for:
   - `storage` directory and all subdirectories
   - `bootstrap/cache` directory
   - Any other directories that need write access

## 10. Alpha Vantage API Configuration

The Alpha Vantage API is essential for SoVest's stock data functionality. This section provides detailed instructions for obtaining, configuring, and optimizing your API usage.

### Getting an Alpha Vantage API Key

1. **Register for an API Key**:
   - Visit the Alpha Vantage API key registration page: https://www.alphavantage.co/support/#api-key
   - Complete the registration form with your name and email address
   - Click "Get Free API Key" to receive your unique API key
   - Check your email for any verification steps if required

2. **Understanding API Key Tiers**:
   - **Free Tier**: 5 API requests per minute, 500 requests per day
   - **Premium Tiers**: Available for higher volume needs (starts at $29.99/month)
   - Alpha Vantage offers multiple premium plans with varying request limits
   - For most SoVest development purposes, the free tier is sufficient

3. **API Key Management Best Practices**:
   - Store your API key securely, never share it publicly
   - Use different API keys for development and production environments
   - Monitor your usage to avoid hitting rate limits
   - Consider upgrading to a premium tier when going to production

### Configuring the API Key in SoVest

1. **Adding the API Key to Your .env File**:
   - Open your `.env` file in the SoVest_code directory
   - Find or add the line for Alpha Vantage API key:
     ```
     ALPHA_VANTAGE_API_KEY=your_api_key_here
     ```
   - Replace `your_api_key_here` with your actual Alpha Vantage API key
   - Save the file

2. **Setting as a System Environment Variable** (alternative method):
   - Open Command Prompt as Administrator
   - Set the environment variable with the following command:
     ```
     setx ALPHA_VANTAGE_API_KEY "your_api_key_here" /M
     ```
   - The `/M` flag sets it as a system-wide environment variable
   - Restart your command prompt or system for the change to take effect

3. **Testing the API Connection**:
   - Create a test file named `test_alpha_vantage.php` in your SoVest_code directory:
     ```php
     <?php
     // Get the API key from environment variable or .env file
     $apiKey = getenv('ALPHA_VANTAGE_API_KEY');
     if (!$apiKey) {
         // Try to load from .env file if it exists
         if (file_exists('.env')) {
             $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
             foreach ($lines as $line) {
                 if (strpos($line, 'ALPHA_VANTAGE_API_KEY=') === 0) {
                     $apiKey = substr($line, strlen('ALPHA_VANTAGE_API_KEY='));
                     break;
                 }
             }
         }
     }
     
     if (!$apiKey) {
         die("API key not found in environment variables or .env file");
     }
     
     // Test a simple API call
     $symbol = 'MSFT';
     $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$symbol}&apikey={$apiKey}";
     
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     
     $response = curl_exec($ch);
     
     if (curl_errno($ch)) {
         echo "cURL Error: " . curl_error($ch);
     } else {
         $data = json_decode($response, true);
         
         if (isset($data['Error Message'])) {
             echo "API Error: " . $data['Error Message'];
         } elseif (isset($data['Note']) && strpos($data['Note'], 'Thank you for using Alpha Vantage!') !== false) {
             echo "API Limit Reached: " . $data['Note'];
         } elseif (isset($data['Global Quote']) && !empty($data['Global Quote'])) {
             echo "API Test Successful!\n";
             echo "Symbol: " . $data['Global Quote']['01. symbol'] . "\n";
             echo "Price: " . $data['Global Quote']['05. price'] . "\n";
             echo "Volume: " . $data['Global Quote']['06. volume'] . "\n";
         } else {
             echo "Unexpected API Response: ";
             print_r($data);
         }
     }
     
     curl_close($ch);
     ?>
     ```
   - Run the test file through command line:
     ```
     cd C:\path\to\SoVest\SoVest_code
     php test_alpha_vantage.php
     ```
   - Verify that you get a successful response with stock data
   - Delete this file after confirming the API works correctly

### Handling API Rate Limits in Windows Environment

1. **Configuring the Stock Update Scheduled Task**:
   - The free API tier allows 5 requests per minute, so spacing requests is crucial
   - Modify the update_stock_prices.php script to include delays between API calls
   - Use exponential backoff if rate limit errors are detected:
     ```php
     // Example backoff code to add to update_stock_prices.php
     $retries = 0;
     $maxRetries = 3;
     
     while ($retries < $maxRetries) {
         // Make API call
         $response = makeApiCall($symbol, $apiKey);
         
         if (isRateLimitError($response)) {
             // Wait with exponential backoff: 30s, 60s, 120s
             $waitTime = 30 * pow(2, $retries);
             echo "Rate limit hit, waiting {$waitTime} seconds...\n";
             sleep($waitTime);
             $retries++;
         } else {
             // Process successful response
             processStockData($response);
             break;
         }
     }
     ```

2. **Optimizing Scheduled Tasks for API Usage**:
   - Configure your Windows scheduled task for stock updates to run less frequently:
     - During market hours: Every 2 hours instead of hourly
     - Consider updating only active stocks (those with recent predictions)
   - Stagger updates across multiple runs to stay within rate limits:
     - Create multiple scheduled tasks that run at different times
     - Each task handles a subset of stocks (e.g., A-F, G-M, N-Z)

3. **Best Practices for Windows API Usage**:
   - Use a caching mechanism to store API responses:
     ```php
     // Example caching approach
     $cacheFile = "cache/stock_{$symbol}.json";
     $cacheExpiry = 3600; // 1 hour in seconds
     
     if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
         // Use cached data
         $data = json_decode(file_get_contents($cacheFile), true);
     } else {
         // Fetch fresh data
         $data = fetchFromAlphaVantage($symbol, $apiKey);
         
         // Cache the result
         if (!is_dir('cache')) {
             mkdir('cache', 0755, true);
         }
         file_put_contents($cacheFile, json_encode($data));
     }
     ```
   - Log API requests to monitor usage:
     - Create a Windows Event Log source for SoVest API calls
     - Or use a simple file-based logging system:
       ```php
       function logApiCall($symbol) {
           $logFile = "logs/api_calls.log";
           $timestamp = date('Y-m-d H:i:s');
           $logEntry = "{$timestamp} - API call for symbol: {$symbol}\n";
           
           if (!is_dir('logs')) {
               mkdir('logs', 0755, true);
           }
           file_put_contents($logFile, $logEntry, FILE_APPEND);
       }
       ```
   - Implement graceful handling of API outages:
     - Add timeout settings to API calls
     - Include fallback data sources when possible
     - Notify administrators of persistent API issues

4. **Windows-Specific API Usage Tips**:
   - Use Windows Performance Monitor to track API-related resource usage
   - Consider creating a dedicated service account for API-related tasks
   - Encrypt your API key in the Windows registry for added security:
     ```powershell
     # PowerShell commands to encrypt API key in registry
     $apiKey = "your_api_key_here"
     $encryptedKey = ConvertTo-SecureString $apiKey -AsPlainText -Force | ConvertFrom-SecureString
     New-ItemProperty -Path "HKLM:\SOFTWARE\SoVest" -Name "AlphaVantageApiKey" -Value $encryptedKey -PropertyType String -Force
     ```
   - Add error notifications to the Windows Event Log:
     ```php
     if (isApiError($response)) {
         // Log to Windows Event Log
         if (function_exists('win_event_log')) {
             win_event_log(4, "Alpha Vantage API error: " . getApiError($response));
         }
         // Or use the system log
         error_log("Alpha Vantage API error: " . getApiError($response));
     }
     ```

## 11. Windows Scheduled Tasks Setup

### Create Stock Update Task
1. Open Task Scheduler (search for "Task Scheduler" in the Start menu)
2. Click "Create Basic Task" in the right panel
3. Name: "SoVest Stock Update"
4. Description: "Updates stock prices hourly during market hours"
5. Trigger: Daily
6. Start time: 9:30 AM
7. Advanced settings:
   - Check "Repeat task every: 1 hour"
   - For a duration of: 7 hours
   - Check "Stop task if it runs longer than: 10 minutes"
8. Action: Start a program
9. Program/script: `C:\\php\\php.exe`
10. Add arguments: `C:\\path\\to\\SoVest\\SoVest_code\\cron\\update_stock_prices.php`
11. Complete the wizard
12. Right-click the created task and select "Properties"
13. Go to the "Conditions" tab
    - Uncheck "Start the task only if the computer is on AC power"
14. Go to the "Settings" tab
    - Check "Run task as soon as possible after a scheduled start is missed"
    - Check "If the task fails, restart every: 5 minutes"
    - Check "Attempt to restart up to: 3 times"
15. Click "OK" to save changes

### Create Prediction Evaluation Task
1. Open Task Scheduler
2. Click "Create Basic Task"
3. Name: "SoVest Prediction Evaluation"
4. Description: "Evaluates stock predictions daily after market close"
5. Trigger: Daily
6. Start time: 5:00 PM (after market close)
7. Action: Start a program
8. Program/script: `C:\\php\\php.exe`
9. Add arguments: `C:\\path\\to\\SoVest\\SoVest_code\\cron\\evaluate_predictions.php`
10. Complete the wizard
11. Right-click the created task and select "Properties"
12. Go to the "Conditions" tab
    - Uncheck "Start the task only if the computer is on AC power"
13. Go to the "Settings" tab
    - Check "Run task as soon as possible after a scheduled start is missed"
    - Check "If the task fails, restart every: 15 minutes"
    - Check "Attempt to restart up to: 3 times"
14. Click "OK" to save changes

## 12. Testing the Installation

### Verify Web Server Setup
1. Open a web browser
2. Navigate to http://sovest.local/
3. You should see the SoVest homepage
4. If you see a PHP error or a blank page, check the NGINX and PHP error logs

### Test Database Connection
1. Create a test PHP file in the web root named `dbtest.php`:
   ```php
   <?php
   require_once 'bootstrap/database.php';
   
   try {
       $db = \\Database\\DatabaseConnection::getInstance();
       echo "Database connection successful!";
   } catch (Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ```
2. Access this file in your browser: http://sovest.local/dbtest.php
3. You should see "Database connection successful!"
4. Remember to delete this file after testing

### Test Scheduled Tasks
1. Right-click on your created tasks in Task Scheduler
2. Select "Run" to manually execute each task
3. Check the application logs for any errors in task execution

## 13. Troubleshooting

### Windows-specific Permission Issues
- **UAC-related Problems**: When running services, Windows User Account Control (UAC) may block certain operations. 
  - **Error Message**: "This app has been blocked for your protection"
  - **Solution**: Run the command prompt or PowerShell as administrator when installing or configuring services
  - **Solution**: Right-click on installation files and select "Run as administrator"
  - **Solution**: For persistent issues, adjust UAC settings via Control Panel → User Accounts → Change User Account Control Settings

- **Web Server Folder Permissions**: Ensure the NGINX or Apache user has full access to the SoVest application directory:
  - **Error Message**: "Permission denied: access.log" or "Unable to create/write to file"
  - **Solution**:
    1. Right-click on the SoVest folder
    2. Select Properties → Security → Edit
    3. Add the SYSTEM user and the user running the web server
    4. Grant Full control or at least Read & Execute, List folder contents, and Read permissions
    5. Apply to all subfolders and files by checking "Replace all child object permissions"

- **File Locking Issues**: Windows often locks files that are in use, which can cause problems when updating the application:
  - **Error Message**: "Cannot delete file: Access is denied" or "The process cannot access the file because it is being used by another process"
  - **Solution**:
    1. Stop the web server service before updating files: `net stop nginx`
    2. If you encounter lock errors, identify the process using tools like Process Explorer
    3. Use `taskkill /F /IM process_name.exe` to forcefully terminate processes locking files
    4. For configuration files, make a copy and rename rather than trying to edit in place

### Windows Firewall Configuration
- **Allowing Services Through Firewall**:
  - **Error Message**: "Connection refused" or timeouts when accessing the application
  - **Solution**:
    1. Open Windows Defender Firewall with Advanced Security (Run → `wf.msc`)
    2. Select "Inbound Rules" → "New Rule"
    3. Select "Port" → Enter the ports used by your services:
       - NGINX/Apache: typically port 80 (HTTP) and 443 (HTTPS)
       - PHP-FPM: typically port 9000
       - MySQL: typically port 3306
    4. Complete the wizard, name the rule (e.g., "SoVest NGINX"), and enable it

- **Troubleshooting Connection Issues**:
  - **Error Message**: "This site can't be reached" or "Connection timed out"
  - **Solution**:
    1. Test if the service is running: `netstat -ano | findstr "80"` (for web server)
    2. Confirm that your firewall is allowing the connections:
       ```
       netsh advfirewall firewall show rule name=all | findstr "NGINX"
       ```
    3. Temporarily disable the firewall to verify it's the source of the problem:
       ```
       netsh advfirewall set allprofiles state off
       ```
       (Remember to turn it back on after testing: `netsh advfirewall set allprofiles state on`)

- **Using Windows Event Viewer to Identify Blocked Connections**:
  - **Where to Look**: Press Win+R, type `eventvwr.msc`, and press Enter
  - **Navigation Path**: Windows Logs → Security
  - **What to Look For**: 
    - Filter for Event ID 5157 (blocked connections)
    - Look for details mentioning your application ports (80, 443, 9000, 3306)
  - **Solution**: Once identified, create specific firewall rules for the blocked connections

### Windows Services Management
- **Restarting Services**:
  - **Error Message**: "Service X has stopped working" or erratic application behavior
  - **Solution**:
    1. Open Command Prompt as administrator
    2. Use these commands to restart services:
       ```
       net stop nginx
       net start nginx
       
       net stop php-fpm
       net start php-fpm
       
       net stop mysql57
       net start mysql57
       ```
    3. Alternatively, use PowerShell commands:
       ```powershell
       Restart-Service -Name "nginx"
       Restart-Service -Name "php-fpm"
       Restart-Service -Name "mysql57"
       ```
    4. For GUI management: Run → `services.msc`, find the service, right-click and select "Restart"

- **Debugging Service Startup Failures**:
  - **Error Message**: "Windows could not start the X service on Local Computer. Error 1067: The process terminated unexpectedly"
  - **Solution**:
    1. Check service status: `sc query nginx`
    2. Review service dependencies: `sc qc nginx`
    3. Look for error details in Windows Event Viewer
    4. Common error codes:
       - 1067: The process terminated unexpectedly (check logs for details)
       - 1069: The service did not start due to a logon failure (credential issues)
       - 5: Access denied (run as administrator or check permissions)
       - 1060: The specified service does not exist (service name typo)
       - 1056: An instance of the service is already running (stop it first)

- **Using Windows Event Logs for Service Diagnostics**:
  - **Where to Look**: Event Viewer (eventvwr.msc)
  - **Navigation Path**: Windows Logs → Application and System
  - **Finding Relevant Events**:
    1. Right-click on "Application" log and select "Filter Current Log"
    2. Enter the service name (e.g., "nginx", "php", "mysql") in the search box
    3. Look for Error or Warning events with detailed error messages
  - **Common Fixes**:
    - Path issues: Ensure paths in service configuration are correct and use double backslashes
    - Permission issues: Verify service account has necessary permissions
    - Missing dependencies: Install required DLLs or Visual C++ Redistributables

### Antivirus Interference
- **PHP Execution Blocking**: Common antivirus software may block PHP scripts or interpret them as threats:
  - **Error Message**: "Access denied" or script execution stops without error
  - **Solution**:
    1. Temporarily disable real-time scanning to verify if antivirus is causing the issue
    2. If confirmed, add exceptions rather than disabling protection entirely
    3. Common antivirus products with known issues:
       - Windows Defender: Often flags PHP scripts as potentially unwanted
       - Avast/AVG: May block scripts with file system manipulation
       - Norton: Can interfere with file creation operations
       - McAfee: May block network connections from PHP

- **Configuring Exclusions**:
  - **Windows Defender Steps**:
    1. Open Windows Security (Start → Settings → Update & Security → Windows Security)
    2. Select "Virus & threat protection"
    3. Under "Virus & threat protection settings", click "Manage settings"
    4. Scroll down to "Exclusions" and click "Add or remove exclusions"
    5. Click "Add an exclusion" and select "Folder"
    6. Add the following paths to exclusions:
       - SoVest application directory (e.g., `C:\SoVest`)
       - PHP installation directory (e.g., `C:\php`)
       - NGINX installation directory (e.g., `C:\nginx`)
       - MySQL data directory (e.g., `C:\ProgramData\MySQL\MySQL Server 5.7\Data`)
    7. Also exclude file extensions: `.php`, `.sql`, `.log`, `.ini`

  - **Third-Party Antivirus Steps**:
    - Similar process in antivirus settings panel
    - Look for terms like "Exclusions", "Exceptions", or "Trusted Locations"
    - Add both folder paths and file extensions

- **Performance Impact**: If SoVest runs slowly despite adequate hardware:
  - **Symptoms**: Slow page loads, delayed database operations, high CPU usage
  - **Solution**:
    1. Check if real-time scanning is causing high CPU usage using Task Manager
    2. Monitor system performance using Task Manager (Ctrl+Shift+Esc) or Resource Monitor
    3. Configure antivirus to exclude high-traffic directories from real-time scanning
    4. Schedule thorough scans during non-peak hours (nights/weekends)
    5. Consider using Windows Defender instead of third-party solutions for lower impact

### IIS and NGINX Coexistence
- **Port Conflicts**:
  - **Error Message**: "Only one usage of each socket address is normally permitted" or "Failed to bind to address: port is already in use"
  - **Solution**:
    1. Identify if IIS is using ports needed by NGINX/Apache:
       ```
       netstat -aon | findstr "80"
       netstat -aon | findstr "443"
       ```
    2. Find which process is using the port:
       ```
       tasklist /fi "pid eq <PID_FROM_NETSTAT>"
       ```
    3. Change port for one of the servers:
       - For NGINX: Edit `nginx.conf` and change the `listen` directive to another port (e.g., 8080)
       - For IIS: Use IIS Manager to change binding ports through the "Bindings" option
    4. Alternatively, stop IIS when using NGINX:
       ```
       net stop W3SVC
       ```

- **Switching Between Web Servers**:
  - **Solution**:
    1. Create a batch file for switching to NGINX:
       ```batch
       @echo off
       echo Stopping IIS...
       net stop W3SVC
       echo Starting NGINX...
       net start nginx
       net start php-fpm
       echo Done! NGINX is now active on port 80.
       pause
       ```
    2. Create a batch file for switching back to IIS:
       ```batch
       @echo off
       echo Stopping NGINX...
       net stop nginx
       net stop php-fpm
       echo Starting IIS...
       net start W3SVC
       echo Done! IIS is now active on port 80.
       pause
       ```
    3. Run these batch files as administrator when needed

- **Shared Configuration Issues**:
  - **Error Message**: Various errors about handlers, modules, or configuration
  - **Solution**:
    1. PHP handler conflicts: 
       - For NGINX: Use PHP-FPM as shown earlier
       - For IIS: Use the dedicated PHP module for IIS (php_isapi.dll)
    2. FastCGI configuration: If both servers use FastCGI, configure them to use different ports:
       - NGINX: Default is 9000
       - IIS: Use 9001 or another available port
    3. Document root conflicts: Configure distinct document roots to avoid file locking issues:
       - NGINX: `C:\nginx\html\SoVest`
       - IIS: `C:\inetpub\wwwroot\SoVest`

### Common Error Messages and Solutions

| Error Message | Possible Cause | Solution |
|---------------|----------------|----------|
| "Access is denied" when starting a service | Insufficient privileges | Run Command Prompt as administrator |
| "The system cannot find the file specified" | Incorrect path in service configuration | Verify and correct paths in service configuration; use double backslashes in paths |
| "Port 80 is already in use" | Another web server (IIS, Apache) is using the port | Change the port in NGINX configuration or stop the other service |
| "PHP Fatal error: Uncaught Error: Failed opening required" | File permission issues or incorrect path | Check file permissions and paths in PHP configuration; verify include_path setting |
| "MySQL server has gone away" | MySQL service stopped or connection timeout | Restart MySQL service and check configuration; increase wait_timeout in my.ini |
| "No connection could be made because the target machine actively refused it" | Service not running or firewall blocking connection | Ensure service is running and check firewall rules |
| "Error 1045: Access denied for user" | Incorrect MySQL credentials | Verify username and password in .env file; check user permissions in MySQL |
| "The FastCGI process exited unexpectedly" | PHP-FPM crashed or misconfigured | Check PHP-FPM logs; increase memory_limit; install missing extensions |
| "Warning: file_put_contents(): failed to open stream: Permission denied" | Insufficient write permissions | Grant write permissions to web server user for the directory |
| "Unable to start service: The service did not respond in a timely fashion" | Service startup timeout | Increase service timeout; check for resource-intensive startup operations |
| "The service did not respond to the start or control request in a timely fashion" | Slow startup or resource issues | Check logs; increase memory; reduce startup dependencies |
| "Composer detected issues in your platform: Your Composer dependencies require a PHP version" | PHP version mismatch | Install the required PHP version; update composer.json requirements |

### Windows-specific PHP Configuration Issues

1. **PHP Extensions Not Loading**:
   - **Error Message**: "Call to undefined function mysqli_connect()"
   - **Solution**: 
     - Ensure the extension is uncommented in php.ini
     - Verify extension_dir path is correct (use absolute path with double backslashes)
     - Install the Visual C++ Redistributable that PHP requires
     - Check that the extension DLL exists in the specified directory

2. **Path Formatting Problems**:
   - **Error Message**: "Failed opening required 'C:path\to\file.php'"
   - **Solution**: 
     - Always use double backslashes in php.ini and configuration files: `C:\\path\\to\\file.php`
     - Or use forward slashes instead: `C:/path/to/file.php`
     - Avoid spaces in path names or enclose paths in quotes

3. **Windows-specific PHP.ini Settings**:
   - Required windows-specific settings:
     ```ini
     ; Windows-specific settings
     fastcgi.impersonate = 1
     cgi.force_redirect = 0
     cgi.fix_pathinfo = 1
     
     ; Adjust temp directory paths
     upload_tmp_dir = "C:\\php\\temp"
     session.save_path = "C:\\php\\temp"
     ```

4. **Resolving "Could not determine current directory" Errors**:
   - **Error Message**: "Warning: chdir(): No such file or directory"
   - **Solution**:
     - Set a valid `cwd` in your php-fpm.conf
     - For NSSM services, explicitly set the working directory
     - Add a startup directory parameter when creating the service