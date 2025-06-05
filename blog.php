<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$filtro_titulo = '';
$filtro_tipo_pet = '';
$filtro_categoria_produto_blog = '';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['buscar'])) {
    $filtro_titulo = isset($_GET['titulo']) ? trim($_GET['titulo']) : '';
    $filtro_tipo_pet = isset($_GET['tipo_pet']) ? trim($_GET['tipo_pet']) : '';
    $filtro_categoria_produto_blog = isset($_GET['categoria_produto_blog']) ? trim($_GET['categoria_produto_blog']) : '';
}

try {
    $sql = "SELECT B.*, U.foto AS foto_usuario, U.nome AS nome_usuario, ";
    $sql .= "CA.nome_categoria AS nome_tipo_pet, ";
    $sql .= "CP.nome_categoria AS nome_categoria_produto_blog ";
    $sql .= "FROM Blog B ";
    $sql .= "JOIN usuarios U ON B.id_usuario = U.id_usuario ";
    $sql .= "LEFT JOIN categoria_animais CA ON B.id_tipo_pet = CA.id_categoria_animal ";
    $sql .= "LEFT JOIN categoria_produtos CP ON B.id_categoria_produto_blog = CP.id_categoria_produto ";
    $sql .= "WHERE 1=1";

    $params = [];

    if (!empty($filtro_titulo)) {
        $sql .= " AND B.titulo LIKE ?";
        $params[] = '%' . $filtro_titulo . '%';
    }
    if (!empty($filtro_tipo_pet)) {
        $sql .= " AND CA.nome_categoria = ?";
        $params[] = $filtro_tipo_pet;
    }
    if (!empty($filtro_categoria_produto_blog)) {
        $sql .= " AND CP.nome_categoria = ?";
        $params[] = $filtro_categoria_produto_blog;
    }

    $sql .= " ORDER BY B.data_publicacao DESC";

    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Erro ao obter posts do blog: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["acao"]) && $_POST["acao"] == "publicar_post") {
    $titulo = $_POST["titulo"];
    $conteudo = $_POST["conteudo"];
    $id_tipo_pet = !empty($_POST["id_tipo_pet"]) ? $_POST["id_tipo_pet"] : NULL;
    $id_categoria_produto_blog = !empty($_POST["id_categoria_produto_blog"]) ? $_POST["id_categoria_produto_blog"] : NULL;
    $data_publicacao = date("Y-m-d H:i:s");
    $id_usuario = $_POST["id_usuario"];

    try {
        $stmt = $conexao->prepare("INSERT INTO Blog (titulo, conteudo, data_publicacao, id_usuario, id_tipo_pet, id_categoria_produto_blog) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $conteudo, $data_publicacao, $id_usuario, $id_tipo_pet, $id_categoria_produto_blog]);
        echo "<script>alert('Post publicado com sucesso!'); window.location.href = 'blog.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao publicar post: " . $e->getMessage() . "');</script>";
    }
}

try {
    $stmt_tipos_pet = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $tipos_pet_disponiveis = $stmt_tipos_pet->fetchAll(PDO::FETCH_ASSOC);

    $stmt_categorias_produto = $conexao->query("SELECT id_categoria_produto, nome_categoria FROM categoria_produtos ORDER BY nome_categoria");
    $categorias_produto_disponiveis = $stmt_categorias_produto->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $tipos_pet_disponiveis = [];
    $categorias_produto_disponiveis = [];
    echo "<p>Erro ao carregar opções de filtro/seleção: " . $e->getMessage() . "</p>";
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
        xintegrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
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
    label.form-label {
        margin-bottom: 0;
        font-size: 0.8rem;
        color: #787878;
    }
    .title {
        margin-left: 11px;
    }
    </style>
</head>

<body class="align-items-center bg-body-tertiary">
    <?php include "header.php"; ?>
    <br>
    <div class="relative w-full min-h-screen row justify-content-md-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h1 class="h3">Blog Achei Pet!</h1><br>

                    <div class="card mb-4">
                        <div class="card-header">
                            Filtros do Blog
                        </div>
                        <div class="card-body">
                            <form method="GET" action="blog.php">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="titulo" class="form-label">Título do Post:</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($filtro_titulo); ?>" placeholder="Buscar por título">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tipo_pet" class="form-label">Tipo de Pet:</label>
                                        <select class="form-select" id="tipo_pet" name="tipo_pet">
                                            <option value="">Todos os Pets</option>
                                            <?php foreach ($tipos_pet_disponiveis as $tipo_pet): ?>
                                                <option value="<?php echo htmlspecialchars($tipo_pet['nome_categoria']); ?>" <?php echo ($filtro_tipo_pet == $tipo_pet['nome_categoria']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tipo_pet['nome_categoria']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="categoria_produto_blog" class="form-label">Categoria de Assunto:</label>
                                        <select class="form-select" id="categoria_produto_blog" name="categoria_produto_blog">
                                            <option value="">Todas as Categorias</option>
                                            <?php foreach ($categorias_produto_disponiveis as $categoria_produto): ?>
                                                <option value="<?php echo htmlspecialchars($categoria_produto['nome_categoria']); ?>" <?php echo ($filtro_categoria_produto_blog == $categoria_produto['nome_categoria']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria_produto['nome_categoria']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
                                        <a href="blog.php" class="btn btn-secondary">Limpar Filtros</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <h2 class="h5">Novo Post</h2>
                            <form method="post" action="blog.php">
                                <input type="hidden" name="acao" value="publicar_post">
                                <label>Título:</label><br>
                                <input class="form-control" type="text" name="titulo" required><br>
                                <label>Conteúdo:</label><br>
                                <textarea class="form-control" name="conteudo" rows="5" required></textarea><br>

                                <label for="new_post_tipo_pet">Tipo de Pet (principal):</label><br>
                                <select class="form-control" id="new_post_tipo_pet" name="id_tipo_pet">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($tipos_pet_disponiveis as $tipo_pet): ?>
                                        <option value="<?php echo htmlspecialchars($tipo_pet['id_categoria_animal']); ?>">
                                            <?php echo htmlspecialchars($tipo_pet['nome_categoria']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select><br>

                                <label for="new_post_categoria_produto_blog">Categoria de Assunto (principal):</label><br>
                                <select class="form-control" id="new_post_categoria_produto_blog" name="id_categoria_produto_blog">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($categorias_produto_disponiveis as $categoria_produto): ?>
                                        <option value="<?php echo htmlspecialchars($categoria_produto['id_categoria_produto']); ?>">
                                            <?php echo htmlspecialchars($categoria_produto['nome_categoria']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select><br>

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
                                <div class="col-md-6 card-blog">
                                    <div class="card mb-4 h-100">
                                        <div class="card-body">
                                            <h2 style="display: flex; align-items: center; margin-bottom: 10px; row">
                                                <div class="col-md-auto">
                                                    <?php if (!empty($post["foto_usuario"])): ?>
                                                    <img src="<?php echo htmlspecialchars($post["foto_usuario"]); ?>"
                                                        alt="Foto do usuário"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                                    <?php else: ?>
                                                    <img src="images/default_profile.png" alt="Foto padrão"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-10">
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

                                            <?php if (!empty($post["nome_tipo_pet"])): ?>
                                                <p><small>Tipo de Pet: <b><?php echo htmlspecialchars($post["nome_tipo_pet"]); ?></b></small></p>
                                            <?php endif; ?>
                                            <?php if (!empty($post["nome_categoria_produto_blog"])): ?>
                                                <p><small>Categoria: <b><?php echo htmlspecialchars($post["nome_categoria_produto_blog"]); ?></b></small></p>
                                            <?php endif; ?>

                                            <p class="text-muted"><small>Publicado em:
                                                    <?php echo date("d/m/Y", strtotime($post["data_publicacao"])); ?></small>
                                            </p>

                                            <?php
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
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    xintegrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
</script>
</html>
