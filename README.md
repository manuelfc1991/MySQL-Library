# mysqli_rc PHP Class

The `mysqli_rc` class provides a wrapper for MySQLi database interactions in PHP. It offers methods for connecting to a database, executing queries, fetching results, and securely handling data input and output.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
- [Methods](#methods)
- [License](#license)

## Prerequisites

Before using the `mysqli_rc` class, make sure you have:

- PHP installed on your server.
- A MySQL database set up with appropriate credentials.

## Installation

1. Download the `mysqli_rc.php` file.
2. Include the file in your PHP project.

```php
require_once('mysqli_rc.php');
```

## Usage

### Creating a Database Connection

```php
$config = array(
    'db_host' => 'localhost',
    'db_name' => 'database_name',
    'db_user' => 'username',
    'db_password' => 'password',
    'port' => 3306, // MySQL port number (default is 3306)
    'socket' => '/path/to/mysql.sock', // MySQL socket (optional)
    'debug' => true // Enable debug mode (optional)
);

$db = new mysqli_rc($config);
$db->open();
```

### Executing Queries

```php
// Execute a query
$result = $db->query('SELECT * FROM table_name');

// Fetch results
$data = $db->fetch($result);
```

### Secure Data Handling

```php
// Clean user input
$user_input = $db->full_filter($_POST['user_input']);

// Insert data into the database securely
$db->secure_insert(array('table' => 'table_name', 'values' => array('column_name' => $user_input)));
```

## Methods

- `open()`: Establishes a connection to the database.
- `close()`: Closes the database connection.
- `query($sql)`: Executes a SQL query.
- `fetch($result)`: Fetches a single row of the result set.
- `secure_insert($params)`: Inserts data into the database securely.
- `secure_update($params)`: Updates data in the database securely.
- `secure_select($params)`: Executes a secure SELECT query.
- ... (and more)

Refer to the class code for a full list of available methods and their descriptions.

## Example

```php
// Example code demonstrating how to use the mysqli_rc class
// ...
include('config.php');
include('class.mysqli.php');
$db->open();
$db->insert("INSERT INTO `table_name` SET
            `field_name` = '" . $db->basic_filter($field_value) . "',
            `field_name` = '" . $db->full_filter($field_value) . "',
            `field_name` = '" . $db->full_filter($field_value) . "',
            `field_name` = '" . $db->full_filter($field_value) . "',
            `field_name` = '" . $db->full_filter($field_value) . "',
            `field_name` = '" . $db->full_filter($field_value) . "'
            ");
```

## License

This project is licensed under the [MIT License](LICENSE).

---

## Donate link

https://www.buymeacoffee.com/rabbitcreators
