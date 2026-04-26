<?php
require 'HariBorrow_backend/config/database.php';
$db = (new Config\Database())->getConnection();
$stmt = $db->query('SELECT User_ID, first_name, user_role FROM users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
