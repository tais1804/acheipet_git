<?php
include "conexao.php";
include "dados_usuario.php"; // Assume que $_SESSION['id_usuario'] é definido aqui
include "verificar_login.php"; // Garante que o usuário está logado

// Inicializa variáveis para armazenar as mensagens
$mensagem_sucesso = "";
$mensagem_erro = "";

// Verifica se o ID do pet e o tipo foram passados
if (!isset($_GET["id"]) || !isset($_GET["tipo"])) {
    $mensagem_erro = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        <strong>Erro:</strong> ID do pet ou tipo de postagem não fornecidos para exclusão.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
} else {
    $id_pet = $_GET["id"];
    $tipo_postagem = urldecode($_GET["tipo"]); // Decodifica o tipo de postagem

    // Obtém o ID do usuário logado para segurança
    $id_usuario_logado = $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario_logado) {
        $mensagem_erro = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <strong>Erro:</strong> Usuário não autenticado. Faça login novamente.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    } else {
        $tabela = "";
        $coluna_id = "";

        // Determina a tabela e a coluna de ID com base no tipo de postagem
        if ($tipo_postagem === 'Doar Pet') {
            $tabela = 'pets';
            $coluna_id = 'id_pet';
        } elseif ($tipo_postagem === 'Perdi Meu Pet') {
            $tabela = 'PetsPerdidos';
            $coluna_id = 'id_pet_perdido';
        } else {
            $mensagem_erro = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <strong>Erro:</strong> Tipo de postagem inválido.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }

        if (empty($mensagem_erro)) { // Se não houve erro na determinação da tabela/coluna
            try {
                // Prepara a query de exclusão com verificação de id_usuario para segurança
                $stmt = $conexao->prepare("DELETE FROM " . $tabela . " WHERE " . $coluna_id . " = ? AND id_usuario = ?");
                $stmt->execute([$id_pet, $id_usuario_logado]);

                // Verifica se alguma linha foi afetada (se o pet foi realmente excluído)
                if ($stmt->rowCount() > 0) {
                    $mensagem_sucesso = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong>Pet excluído com sucesso!</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
                } else {
                    $mensagem_erro = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        <strong>Atenção:</strong> Pet não encontrado ou você não tem permissão para excluí-lo.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
                }
            } catch (PDOException $e) {
                $mensagem_erro = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Erro ao excluir pet:</strong> " . $e->getMessage() . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
    .card-text {
        margin: 10px !important;
    }
    </style>

</head>

<body>
    <?php include "header.php"; ?>
    <div class="relative w-full min-h-screen row justify-content-md-center">
        <div class="col-md-6">
            <div class="container py-5">
                <div class="card">
                    <div class="card-body card-text">
                        <h1 class="h2">Excluir Pet</h1>
                        <?php
                        if (!empty($mensagem_sucesso)) {
                            echo $mensagem_sucesso;
                            // Adiciona um script para redirecionar após 4 segundos
                            echo "<script>
                                    setTimeout(function() {
                                        window.location.href = 'meus_pets.php';
                                    }, 4000); // 4 segundos
                                  </script>";
                        }
                        if (!empty($mensagem_erro)) {
                            echo $mensagem_erro;
                        }
                        ?>
                        <p><a class="btn btn-primary" href="meus_pets.php">Voltar para a lista de pets</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24