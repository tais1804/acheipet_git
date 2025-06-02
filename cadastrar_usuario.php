<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha_digitada = $_POST["senha"]; 
    $senha_hash = password_hash($senha_digitada, PASSWORD_DEFAULT); 
    $endereco = $_POST["endereco"];
    $telefone = $_POST["telefone"];
    $tipo_usuario = $_POST["tipo_usuario"];

    try {
       
        $stmt_usuarios = $conexao->prepare("INSERT INTO Usuarios (nome, email, senha, endereco, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_usuarios->execute([$nome, $email, $senha_hash, $endereco, $telefone, $tipo_usuario]);

        
        $id_usuario = $conexao->lastInsertId();

        
        $stmt_login = $conexao->prepare("INSERT INTO login (id_usuario, senha) VALUES (?, ?)");
        $stmt_login->execute([$id_usuario, $senha_hash]);

        echo "<p>Usuário cadastrado com sucesso!</p>";

    } catch (PDOException $e) {
        echo "<p>Erro ao cadastrar usuário: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
    <a href="index.php">Home</a>
    <a href="login.php">Ir para Login</a>
</head>
<body>
    <h1>Cadastrar Usuário</h1>
    <form method="post" action="cadastrar_usuario.php">
        <label>Nome:</label><br>
        <input type="text" name="nome"><br><br>
        <label>Email:</label><br>
        <input type="email" name="email"><br><br>
        <label>Senha:</label><br>
        <input type="password" name="senha"><br><br>
        <label>Endereço:</label><br>
        <input type="text" name="endereco"><br><br>
        <label>Telefone:</label><br>
        <input type="text" name="telefone"><br><br>
        <label>Tipo de Usuário:</label><br>
        <select name="tipo_usuario">
            <option value="tutor">Tutor</option>
            <option value="adotante">Adotante</option>
            <option value="abrigo">Abrigo</option>
            <option value="veterinario">Veterinário</option>
            <option value="petshop">Petshop</option>
            <option value="administrador">Administrador</option>
        </select><br><br>
        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>