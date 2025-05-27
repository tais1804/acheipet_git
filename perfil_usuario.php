<?php
session_start();
include "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

try {
    $stmt = $conexao->prepare("SELECT id_usuario, nome, email, endereco, telefone, tipo_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Erro ao buscar usuário: " . $e->getMessage();
    exit();
}

function deslogar() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_POST["deslogar"])) {
    deslogar();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Perfil do Usuário</title>
</head>
<body>
    <h1>Perfil do Usuário</h1>
    <p>ID: <?php echo $usuario["id_usuario"]; ?></p>
    <p>Nome: <?php echo $usuario["nome"]; ?></p>
    <p>Email: <?php echo $usuario["email"]; ?></p>
    <p>Endereço: <?php echo $usuario["endereco"]; ?></p>
    <p>Telefone: <?php echo $usuario["telefone"]; ?></p>
    <p>Tipo de Usuário: <?php echo $usuario["tipo_usuario"]; ?></p>
    <form method="post" action="perfil_usuario.php">
        <input type="submit" name="deslogar" value="Deslogar">
    </form>
    <a href="meus_pets.php">Ver Meus Pets</a>
</body>
</html>
