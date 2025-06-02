<?php
include "conexao.php";

include "verificar_login.php";

try {
    $stmt = $conexao->query("SELECT * FROM Blog");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Erro ao obter posts do blog: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST["titulo"];
    $conteudo = $_POST["conteudo"];
    $data_publicacao = date("Y-m-d");
    $id_usuario = $_POST["id_usuario"];

    try {
        $stmt = $conexao->prepare("INSERT INTO Blog (titulo, conteudo, data_publicacao, id_usuario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $conteudo, $data_publicacao, $id_usuario]);
        echo "<p>Post publicado com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p>Erro ao publicar post: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
</head>
<body>
    <h1>Blog</h1>
    <?php foreach ($posts as $post): ?>
        <div>
            <h2><?php echo $post["titulo"]; ?></h2>
            <p><?php echo $post["conteudo"]; ?></p>
            <p>Data de publicação: <?php echo $post["data_publicacao"]; ?></p>
        </div>
    <?php endforeach; ?>

    <h2>Novo Post</h2>
    <form method="post" action="blog.php">
        <label>Título:</label><br>
        <input type="text" name="titulo"><br><br>
        <label>Conteúdo:</label><br>
        <textarea name="conteudo"></textarea><br><br>
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION["id_usuario"]; ?>">
        <input type="submit" value="Publicar">
    </form>
</body>
</html>