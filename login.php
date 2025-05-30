<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$mensagem_erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once "conexao.php";

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql_usuarios = "SELECT id_usuario FROM usuarios WHERE email = :email";
    $stmt_usuarios = $conexao->prepare($sql_usuarios);
    $stmt_usuarios->bindParam(':email', $email);

    try {
        $stmt_usuarios->execute();
        $resultado_usuario = $stmt_usuarios->fetch(PDO::FETCH_ASSOC);

        if ($resultado_usuario) {
            $id_usuario = $resultado_usuario['id_usuario'];

            $sql_login = "SELECT senha FROM login WHERE id_usuario = :id_usuario";
            $stmt_login = $conexao->prepare($sql_login);
            $stmt_login->bindParam(':id_usuario', $id_usuario);
            $stmt_login->execute();
            $resultado_login = $stmt_login->fetch(PDO::FETCH_ASSOC);

            if ($resultado_login) {
                if (password_verify($senha, $resultado_login['senha'])) {
                    $_SESSION['id_usuario'] = $id_usuario;
                    header("Location: index.php");
                    exit();
                } else {
                    $mensagem_erro = "Senha incorreta.";
                }
            } else {
                $mensagem_erro = "Credenciais de login não encontradas para este usuário.";
            }
        } else {
            $mensagem_erro = "Usuário com este e-mail não encontrado.";
        }
    } catch (PDOException $e) {
        $mensagem_erro = "Erro ao executar a consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <style>
            @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
            @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
    </style>

    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  
</head>
    <title>Login</title>
    <style>
        
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
<div class="container text-center">
<main class="form-signin w-100 m-auto">
    <div class="relative w-full min-h-screen">
                   
            <div class="row">
                <div class="col">
                    <h2>Acesso o sistema</h2>
                    <?php if (!empty($mensagem_erro)): ?>
                        <p class="error-message"><?php echo $mensagem_erro; ?></p>
                    <?php endif; ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group mb-3">
                            <input class="form-control" type="email" id="email" name="email" placeholder="e-mail" required>

                        </div>
                        <div class="form-group mb-3">
                            <input class="form-control" type="password" id="senha" name="senha" placeholder="senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </form>
                    <p style="margin-top: 15px;">Ainda não tem uma conta? <a href="cadastrar_usuario.php">Cadastre-se</a></p>
                </div>
             </div>
    </div>
</main>
</div>
</body>

</html>