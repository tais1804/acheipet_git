<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$id_post = null;
$post_data = null;
$tipos_pet_disponiveis = [];
$categorias_produto_disponiveis = [];

try {
    $stmt_tipos_pet = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $tipos_pet_disponiveis = $stmt_tipos_pet->fetchAll(PDO::FETCH_ASSOC);

    $stmt_categorias_produto = $conexao->query("SELECT id_categoria_produto, nome_categoria FROM categoria_produtos ORDER BY nome_categoria");
    $categorias_produto_disponiveis = $stmt_categorias_produto->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Erro ao carregar opções de tipo de pet e categorias: " . $e->getMessage() . "</p>";
}

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
    $id_post_editado = htmlspecialchars($_POST["id_post"]);
    $titulo_editado = htmlspecialchars($_POST["titulo"]);
    $conteudo_editado = htmlspecialchars($_POST["conteudo"]);
    $id_tipo_pet_editado = !empty($_POST["id_tipo_pet"]) ? $_POST["id_tipo_pet"] : NULL;
    $id_categoria_produto_blog_editado = !empty($_POST["id_categoria_produto_blog"]) ? $_POST["id_categoria_produto_blog"] : NULL;
    $id_usuario_logado = $_SESSION["id_usuario"];

    try {
        $stmt_update = $conexao->prepare("UPDATE Blog SET titulo = ?, conteudo = ?, id_tipo_pet = ?, id_categoria_produto_blog = ? WHERE id_post = ? AND id_usuario = ?");
        $stmt_update->execute([$titulo_editado, $conteudo_editado, $id_tipo_pet_editado, $id_categoria_produto_blog_editado, $id_post_editado, $id_usuario_logado]);

        if ($stmt_update->rowCount() > 0) {
            echo "<script>alert('Postagem atualizada com sucesso!'); window.location.href = 'blog.php';</script>";
        } else {
            echo "<script>alert('Nenhuma alteração foi feita ou você não tem permissão para editar esta postagem.'); window.location.href = 'blog.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao atualizar postagem: " . $e->getMessage() . "'); window.location.href = 'blog.php';</script>";
    }
    exit();
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
        xintegrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
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
                        <input class="form-control" type="text" name="titulo" value="<?= htmlspecialchars($post_data['titulo'], ENT_QUOTES, 'UTF-8') ?>" required><br>
                        
                        <label>Conteúdo:</label><br>
                        <textarea class="form-control" name="conteudo" rows="10" required><?= htmlspecialchars($post_data['conteudo'], ENT_QUOTES, 'UTF-8') ?></textarea><br>
                        
                        <label for="edit_tipo_pet">Tipo de Pet (principal):</label><br>
                        <select class="form-control" id="edit_tipo_pet" name="id_tipo_pet">
                            <option value="">Nenhum</option>
                            <?php foreach ($tipos_pet_disponiveis as $tipo_pet): ?>
                                <option value="<?php echo htmlspecialchars($tipo_pet['id_categoria_animal']); ?>"
                                    <?php echo (isset($post_data['id_tipo_pet']) && $post_data['id_tipo_pet'] == $tipo_pet['id_categoria_animal']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo_pet['nome_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br>

                        <label for="edit_categoria_produto_blog">Categoria de Assunto (principal):</label><br>
                        <select class="form-control" id="edit_categoria_produto_blog" name="id_categoria_produto_blog">
                            <option value="">Nenhum</option>
                            <?php foreach ($categorias_produto_disponiveis as $categoria_produto): ?>
                                <option value="<?php echo htmlspecialchars($categoria_produto['id_categoria_produto']); ?>"
                                    <?php echo (isset($post_data['id_categoria_produto_blog']) && $post_data['id_categoria_produto_blog'] == $categoria_produto['id_categoria_produto']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria_produto['nome_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br><br>

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
