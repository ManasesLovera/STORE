<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "VISANA";

$conn = new mysqli($servername, $username, $password, $dbname);

session_start();
require_once '../library/configServer.php';
require_once '../library/consulSQL.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/VISANA/logic/helpers.php'; // Para usar funciones comunes como limpiar cadenas.

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = consultasSQL::clean_string($_POST['nombre-login'] ?? '');
    $clave = $_POST['clave-login'] ?? '';
    $radio = consultasSQL::clean_string($_POST['optionsRadios'] ?? '');

    if (!empty($nombre) && !empty($clave)) {
        if ($radio === "option2") { // Login para administrador
            $query = "SELECT * FROM administrador WHERE Nombre = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $nombre);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if ($clave == $admin['Clave']) {
                    $_SESSION['nombreAdmin'] = $admin['Nombre'];
                    $_SESSION['UserType'] = "Admin";
                    $_SESSION['adminID'] = $admin['id'];
                    header("Location: ../index.php");
                    exit();
                } else {
                    $_SESSION['login_error'] = "Contraseña incorrecta para administrador.";
                }
            } else {
                $_SESSION['login_error'] = "Administrador no encontrado.";
            }
        } elseif ($radio === "option1") { // Login para cliente
            $query = "SELECT * FROM cliente WHERE Usuario = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $nombre);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($clave, $user['Clave'])) {
                    $_SESSION['nombreUser'] = $user['Usuario'];
                    $_SESSION['UserType'] = "User";
                    $_SESSION['UserNIT'] = $user['NIT'];
                    header("Location: ../index.php");
                    exit();
                } else {
                    $_SESSION['login_error'] = "Contraseña incorrecta para cliente.";
                }
            } else {
                $_SESSION['login_error'] = "Usuario no encontrado.";
            }
        } else {
            $_SESSION['login_error'] = "Opción de inicio de sesión no válida.";
        }
    } else {
        $_SESSION['login_error'] = "Por favor, complete todos los campos.";
    }

    // Si llega aquí, hubo un error
    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>