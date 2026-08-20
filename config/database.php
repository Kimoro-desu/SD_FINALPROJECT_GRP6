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

    // CONSTANT_VAR_ROLE: 'admin', 'student', 'faculty', 'staff', 'researcher'
    const ROLE_ADMIN = 'Admin';
    const ROLE_STUDENT = 'Student';
    const ROLE_FACULTY = 'Faculty';
    const ROLE_STAFF = 'Staff';
    const ROLE_RESEARCHER = 'Researcher';
    const ROLE_LENDER = 'Lender';

    // CONSTANT_VAR_AVAILABILITY (legacy uppercase kept for backward compat)
    const AVAILABILITY_AVAILABLE = 'available';
    const AVAILABILITY_UNAVAILABLE = 'unavailable';
    const AVAILABILITY_BORROWED = 'Borrowed';
    const AVAILABILITY_MAINTENANCE = 'Maintenance';
    const AVAILABILITY_PENDING = 'Pending';

    // CONSTANT_VAR_REQUEST_STATUS: 'Pending', 'Confirmed', 'Approved', 'Rejected', 'Returned'
    const STATUS_PENDING = 'Pending';
    const STATUS_CONFIRMED = 'Confirmed';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_RETURNED = 'Returned';

    public function getConnection()
    {
        // Bootstrap once per request (before strtotime/date on borrow flows). Campus default: Philippines.
        static $tzBootstrapped = false;
        if (!$tzBootstrapped && function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Asia/Manila');
            $tzBootstrapped = true;
        }

        $this->conn = null;
        try {
            $this->conn = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            try {
                $this->conn->exec("SET time_zone = '+08:00'");
            } catch (\PDOException $e) {
                // Ignore if server disallows custom time_zone
            }
        } catch (\PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }
        return $this->conn;
    }
}