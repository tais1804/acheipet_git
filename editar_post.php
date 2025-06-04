<?php
include "conexao.php";
include "dados_usuario.php"; // Para acessar $_SESSION["id_usuario"]
include "verificar_login.php"; // Para garantir que o usuário esteja logado

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$id_post = null;
$post_data = null;

if (isset($_GET['id_post'])) {
    $id_post = (int) $_GET['id_post'];
    $id_usuario_logado = $_SESSION["id_usuario"];

    try {
        $stmt = $conexao->prepare("SELECT * FROM Blog WHERE id_post = ? AND id_usuario = ?");
        $stmt->execute([$id_post, $id_usuario_logado]);
        $post_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post_data) {
            echo "<script>alert('Postagem não encontrada ou você não tem permissão para editá-la.'); window.location.href = 'blog.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<p>Erro ao carregar postagem para edição: " . $e->getMessage() . "</p>";
        exit;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["acao"]) && $_POST["acao"] == "salvar_edicao") {
    // Processar o formulário de edição
    $id_post_editado = htmlspecialchars($_POST["id_post"]);
    $titulo_editado = htmlspecialchars($_POST["titulo"]);
    $conteudo_editado = htmlspecialchars($_POST["conteudo"]);
    $id_usuario_logado = $_SESSION["id_usuario"]; // Garante que o usuário logado é o autor

    try {
        // Atualize a postagem, garantindo que o ID do usuário logado corresponda ao autor
        $stmt_update = $conexao->prepare("UPDATE Blog SET titulo = ?, conteudo = ? WHERE id_post = ? AND id_usuario = ?");
        $stmt_update->execute([$titulo_editado, $conteudo_editado, $id_post_editado, $id_usuario_logado]);

        if ($stmt_update->rowCount() > 0) {
            echo "<script>alert('Postagem atualizada com sucesso!'); window.location.href = 'blog.php';</script>";
        } else {
            echo "<script>alert('Nenhuma alteração foi feita ou você não tem permissão para editar esta postagem.'); window.location.href = 'blog.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao atualizar postagem: " . $e->getMessage() . "'); window.location.href = 'blog.php';</script>";
    }
    exit(); // Importante para parar a execução após o POST
} else {
    echo "<script>alert('ID da postagem não fornecido para edição.'); window.location.href = 'blog.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet - Editar Post</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="../css/estilo-achei-pet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>

<body class="align-items-center bg-body-tertiary">
    <?php include "header.php"; ?>
    <br>
    <div class="relative w-full min-h-screen row justify-content-md-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h1>Editar Postagem</h1>
                    <?php if ($post_data): ?>
                    <form method="post" action="editar_post.php">
                        <input type="hidden" name="acao" value="salvar_edicao">
                        <input type="hidden" name="id_post" value="<?= htmlspecialchars($post_data['id_post'] ?? '', ENT_QUOTES, 'UTF-8') ?>">


                        <label>Título:</label><br>
                        <input class="form-control" type="text" name="titulo" value="<?= htmlspecialchars($post_data['titulo'], ENT_QUOTES, 'UTF-8') ?>" required><br><br>
                        <label>Conteúdo:</label><br>
                        <textarea class="form-control" name="conteudo" rows="10" required><?= htmlspecialchars($post_data['conteudo'], ENT_QUOTES, 'UTF-8') ?></textarea><br><br>
                        <input class="btn btn-success" type="submit" value="Salvar Edição">
                        <a href="blog.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                    <?php else: ?>
                    <p>Postagem não encontrada ou não permitida para edição.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>