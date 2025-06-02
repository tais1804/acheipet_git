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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - Achei Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa; /* Light gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            font-family: 'Lato', sans-serif;
            font-weight: 700;
            color: #0d6efd; /* Bootstrap primary color */
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .alert {
            margin-top: 20px;
        }
        .links-container {
            margin-top: 20px;
            text-align: center;
        }
        .links-container a {
            margin: 0 10px;
            color: #0d6efd;
            text-decoration: none;
        }
        .links-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="card-title text-center">Cadastrar Usuário</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="cadastrar_usuario.php">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço:</label>
                <input type="text" class="form-control" id="endereco" name="endereco" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone:</label>
                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(XX) XXXXX-XXXX" required>
            </div>
            <div class="mb-3">
                <label for="tipo_usuario" class="form-label">Tipo de Usuário:</label>
                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                    <option value="">Selecione um tipo</option>
                    <option value="tutor">Tutor</option>
                    <option value="adotante">Adotante</option>
                    <option value="abrigo">Abrigo</option>
                    <option value="veterinario">Veterinário</option>
                    <option value="petshop">Petshop</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Cadastrar</button>
            </div>
        </form>

        <div class="links-container">
            <a href="index.php">Página Inicial</a>
            <a href="login.php">Já tem conta? Faça Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>