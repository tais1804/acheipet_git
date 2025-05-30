<?php
session_start();
include "conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

try {
    // Na-update ti query tapno iramanan ti 'cpf'
    $stmt = $conexao->prepare("SELECT id_usuario, nome, email, endereco, telefone, cpf, tipo_usuario FROM usuarios WHERE id_usuario = ?");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body class="container mt-5">
    <h1 class="mb-4 text-primary">Perfil do Usuário</h1>
    <div class="card p-4 shadow-sm">
        <p><strong>ID:</strong> <?php echo htmlspecialchars($usuario["id_usuario"]); ?></p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario["nome"]); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario["email"]); ?></p>
        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($usuario["endereco"]); ?></p>
        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($usuario["telefone"]); ?></p>
        <p><strong>CPF:</strong> <?php echo htmlspecialchars($usuario["cpf"]); ?></p> <p><strong>Tipo de Usuário:</strong> <?php echo htmlspecialchars($usuario["tipo_usuario"]); ?></p>
    </div>

    <div class="mt-4 d-flex">
        <form method="post" action="perfil_usuario.php" class="me-2">
            <button type="submit" name="deslogar" class="btn btn-danger">Deslogar</button>
        </form>
        <a href="meus_pets.php" class="btn btn-info me-2">Ver Meus Pets</a>
        <a href="loja_virtual.php" class="btn btn-success">Ir para a Loja</a>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
