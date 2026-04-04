<?php
namespace Config;

class Database
{
    // Database credentials
    private $host = "127.0.0.1";
    private $db_name = "hariborrow_db";
    private $username = "root";
    private $password = ""; // Default XAMPP password is empty
    public $conn;

    // CONSTANT_VAR_ROLE: 'Admin', 'Borrower', 'Lender', 'Staff', 'Researcher'
    const ROLE_ADMIN = 'Admin';
    const ROLE_BORROWER = 'Borrower';
    const ROLE_LENDER = 'Lender';
    const ROLE_STAFF = 'Staff';
    const ROLE_RESEARCHER = 'Researcher';

    // CONSTANT_VAR_AVAILABILITY: 'Available', 'Borrowed', 'Maintenance', 'Pending'
    const AVAILABILITY_AVAILABLE = 'Available';
    const AVAILABILITY_BORROWED = 'Borrowed';
    const AVAILABILITY_MAINTENANCE = 'Maintenance';
    const AVAILABILITY_PENDING = 'Pending';

    // CONSTANT_VAR_REQUEST_STATUS: 'Pending', 'Approved', 'Rejected', 'Returned'
    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_RETURNED = 'Returned';

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (\PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }
        return $this->conn;
    }
}
?>