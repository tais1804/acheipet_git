<?php
include "conexao.php";

include "verificar_login.php";

try {
    $stmt = $conexao->query("SELECT * FROM Usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr><td>" . $usuario["id_usuario"] . "</td><td>" . $usuario["nome"] . "</td><td>" . $usuario["email"] . "</td><td>" . $usuario["tipo_usuario"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário encontrado.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Erro ao listar usuários: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
</head>
<body>
    <h1>Listar Usuários</h1>
</body>
</html>