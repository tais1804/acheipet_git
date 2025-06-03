<?php
include "conexao.php"; 

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha_digitada = $_POST["senha"]; 
    $senha_hash = password_hash($senha_digitada, PASSWORD_DEFAULT); 
    $endereco = $_POST["endereco"];
    $telefone = $_POST["telefone"];
    $cpf = $_POST["cpf"];
    
    // VERIFIQUE SE O CAMPO "data_nascimento" EXISTE ANTES DE ACESSÁ-LO
    // SE VOCÊ ADICIONOU O CAMPO NO HTML COMO SUGERIDO, ELE SEMPRE EXISTIRÁ.
    // MAS É UMA BOA PRÁTICA VERIFICAR PARA EVITAR WARNINGS SE O CAMPO PUDER SER OMITIDO.
    $data_nascimento = isset($_POST["data_nascimento"]) ? $_POST["data_nascimento"] : null; 
    
    $tipo_usuario = $_POST["tipo_usuario"];
    // REMOVA A LINHA ABAIXO, VOCÊ NÃO ACESSA FOTO VIA $_POST
    // $foto = $_POST["foto"]; // <-- ESTA LINHA CAUSAVA O ERRO DA FOTO

    // **1. Tratamento do upload da foto**
    $foto_caminho = null; 

    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
        $diretorio_uploads = "uploads/fotos_perfil/"; 
        
        if (!is_dir($diretorio_uploads)) {
            mkdir($diretorio_uploads, 0777, true); 
        }

        $nome_arquivo_original = basename($_FILES["foto"]["name"]);
        $extensao_arquivo = pathinfo($nome_arquivo_original, PATHINFO_EXTENSION);
        $nome_arquivo_unico = uniqid() . "." . $extensao_arquivo; 
        $caminho_destino = $diretorio_uploads . $nome_arquivo_unico;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $caminho_destino)) {
            $foto_caminho = $caminho_destino; 
        } else {
            $mensagem = "Erro ao mover o arquivo de foto para o servidor.";
            $tipo_mensagem = "danger";
        }
    } elseif (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_NO_FILE) {
        $foto_caminho = null; 
    } else if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] != UPLOAD_ERR_NO_FILE) {
        $mensagem = "Erro no upload da foto: Código " . $_FILES["foto"]["error"];
        $tipo_mensagem = "danger";
    }

    if (empty($mensagem)) { 
        try {
            $stmt_check_email = $conexao->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ?");
            $stmt_check_email->execute([$email]);
            $email_existe = $stmt_check_email->fetchColumn();

            if ($email_existe > 0) {
                $mensagem = "Este e-mail já está cadastrado. Por favor, use outro e-mail ou faça login.";
                $tipo_mensagem = "warning";
            } else {
                // Certifique-se de incluir data_nascimento na sua query SQL, se ela for para ser armazenada no banco.
                // A query abaixo não inclui data_nascimento, se você quiser armazená-la, adicione-a.
                // Exemplo se você quiser adicionar data_nascimento:
                // $stmt_usuarios = $conexao->prepare("INSERT INTO Usuarios (nome, email, senha, endereco, telefone, tipo_usuario, foto, data_nascimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                // $stmt_usuarios->execute([$nome, $email, $senha_hash, $endereco, $telefone, $tipo_usuario, $foto_caminho, $data_nascimento]);

                // Mantendo sua query original (sem data_nascimento no INSERT):
                $stmt_usuarios = $conexao->prepare("INSERT INTO Usuarios (nome, email, senha, endereco, telefone, tipo_usuario, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_usuarios->execute([$nome, $email, $senha_hash, $endereco, $telefone, $tipo_usuario, $foto_caminho]);

                $id_usuario = $conexao->lastInsertId();

                $stmt_login = $conexao->prepare("INSERT INTO login (id_usuario, senha) VALUES (?, ?)");
                $stmt_login->execute([$id_usuario, $senha_hash]); 

                $mensagem = "Usuário cadastrado com sucesso!";
                $tipo_mensagem = "success";
            }

        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar usuário: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
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

        <form method="post" action="cadastrar_usuario.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
            </div>
            <div class="mb-3">
                <label for="nome" class="form-label">CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf" required>
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
                <label for="foto" class="form-label">Foto do perfil:</label>
                <input type="file" class="form-control" id="foto" name="foto"> 
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