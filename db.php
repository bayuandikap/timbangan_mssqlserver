<?php
$serverName = "localhost"; // Atau "localhost\SQLEXPRESS" / "127.0.0.1,1433"
$database   = "db_timbangan";
$username   = "sa";        // Sesuaikan dengan user login SQL Server Anda
$password   = "bayubayubayu"; // Sesuaikan dengan password SQL Server Anda

try {
    $dsn = "sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "status"  => "error",
        "message" => "Gagal terhubung ke Microsoft SQL Server: " . $e->getMessage()
    ]);
    exit;
}
?>