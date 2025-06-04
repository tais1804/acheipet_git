<?php
include "conexao.php";
include "dados_usuario.php"; // Este arquivo deve iniciar a sessão e definir $_SESSION["id_usuario"]
include "verificar_login.php"; // Este arquivo deve redirecionar se o usuário não estiver logado


// Verificação se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    // Redirecionar para a página de login ou exibir uma mensagem de erro
    header("Location: login.php"); // Altere para a sua página de login
    exit();
}

try {
    // Juntar as tabelas Blog e usuarios para obter os dados do usuário
    // Alterado U.id para U.id_usuario conforme a correção anterior
    $stmt = $conexao->query("SELECT B.*, U.foto AS foto_usuario, U.nome AS nome_usuario FROM Blog B JOIN usuarios U ON B.id_usuario = U.id_usuario ORDER BY B.data_publicacao DESC"); // Adicionado ORDER BY para posts mais recentes primeiro
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Erro ao obter posts do blog: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["acao"]) && $_POST["acao"] == "publicar_post") { // Adicionado um campo 'acao' para diferenciar o formulário
    $titulo = $_POST["titulo"];
    $conteudo = $_POST["conteudo"];
    $data_publicacao = date("Y-m-d");
    $id_usuario = $_POST["id_usuario"]; // Este deve vir do campo hidden, que é o id do usuário logado

    try {
        $stmt = $conexao->prepare("INSERT INTO Blog (titulo, conteudo, data_publicacao, id_usuario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $conteudo, $data_publicacao, $id_usuario]);
        echo "<script>alert('Post publicado com sucesso!'); window.location.href = 'blog.php';</script>"; // Redireciona para evitar reenvio do formulário
    } catch (PDOException $e) {
        echo "<p>Erro ao publicar post: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet - Blog</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="../css/estilo-achei-pet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
    .post-actions {
        margin-top: 10px;
    }

    .post-actions .btn {
        margin-right: 5px;
    }

    .card-blog {
        margin-bottom: 25px;
    }

    .card-title {
        margin-bottom: 0 !important;
    }
    </style>
</head>

<body class="align-items-center bg-body-tertiary">
    <?php
            include "header.php";
            ?>
    <br>
    <div class="relative w-full min-h-screen row justify-content-md-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h1 class="h3">Blog Achei Pet!</h1><br>

                    <div class="row">
                        <div class="col-md-3">
                            <h2 class="h5">Novo Post</h2>
                            <form method="post" action="blog.php">
                                <input type="hidden" name="acao" value="publicar_post">
                                <label>Título:</label><br>
                                <input class="form-control" type="text" name="titulo" required><br>
                                <label>Conteúdo:</label><br>
                                <textarea class="form-control" name="conteudo" rows="5" required></textarea><br>
                                <input class="form-control" type="hidden" name="id_usuario"
                                    value="<?php echo htmlspecialchars($_SESSION["id_usuario"]); ?>">
                                <input class="btn btn-primary" type="submit" value="Publicar">
                            </form>
                        </div>
                        <div class="col-md-9">
                            <h3>Últimas Publicações</h3>
                            <?php if (empty($posts)): ?>
                            <p>Nenhuma postagem encontrada ainda.</p>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($posts as $post): ?>
                                <div class="col-md-6  card-blog">
                                    <div class="card mb-4 h-100">
                                        <div class="card-body">
                                            <h2 style="display: flex; align-items: center; margin-bottom: 10px; row">
                                                <div class="col-md-auto">
                                                    <?php if (!empty($post["foto_usuario"])): ?>
                                                    <img src="<?php echo htmlspecialchars($post["foto_usuario"]); ?>"
                                                        alt="Foto do usuário"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                                </div>
                                                <div class="col-md-10">
                                                    <?php else: ?>
                                                    <img src="images/default_profile.png" alt="Foto padrão"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                                    <?php endif; ?>
                                                    <h5 class="card-title">
                                                        <?php echo htmlspecialchars($post["titulo"]); ?>
                                                    </h5>
                                                    <?php if (!empty($post["nome_usuario"])): ?>
                                                    <small style="font-size: 0.8em; color: #666;">por:
                                                        <?php echo htmlspecialchars($post["nome_usuario"]); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </h2>
                                            <br>
                                            <p><?php echo nl2br(htmlspecialchars($post["conteudo"])); ?></p>
                                            <p class="text-muted"><small>Publicado em:
                                                    <?php echo date("d/m/Y", strtotime($post["data_publicacao"])); ?></small>
                                            </p>

                                            <?php
                                // Verifica se o ID do usuário logado é igual ao ID do autor da postagem
                                if (isset($_SESSION["id_usuario"], $post["id_usuario"]) && $_SESSION["id_usuario"] == $post["id_usuario"]):
                                ?>
                                            <div class="post-actions">
                                                <a href="editar_post.php?id_post=<?= htmlspecialchars($post['id_post']) ?>"
                                                    class="btn btn-warning btn-sm">Editar</a>
                                                <a href="excluir_post.php?id=<?= htmlspecialchars($post['id_post']) ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Tem certeza que deseja excluir esta postagem?');">Excluir</a>

                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>