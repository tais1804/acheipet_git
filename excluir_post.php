<?php
include "conexao.php";
include "dados_usuario.php"; // Para acessar $_SESSION["id_usuario"]
include "verificar_login.php"; // Para garantir que o usuário esteja logado

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php"); // Redireciona se não estiver logado
    exit();
}

if (isset($_GET['id'])) {
    $id_post = (int) $_GET['id'];
    $id_usuario_logado = $_SESSION["id_usuario"];

    try {
        // Primeiro, verifique se a postagem pertence ao usuário logado
        $stmt_check = $conexao->prepare("SELECT id_usuario FROM Blog WHERE id_post = ?");
        $stmt_check->execute([$id_post]);
        $post = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($post && $post["id_usuario"] == $id_usuario_logado) {
            // Se a postagem pertence ao usuário logado, proceda com a exclusão
            $stmt_delete = $conexao->prepare("DELETE FROM Blog WHERE id_post = ?");
            $stmt_delete->execute([$id_post]);
            echo "<script>alert('Postagem excluída com sucesso!'); window.location.href = 'blog.php';</script>";
        } else {
            // Postagem não encontrada ou não pertence ao usuário logado
            echo "<script>alert('Você não tem permissão para excluir esta postagem ou ela não existe.'); window.location.href = 'blog.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir postagem: " . $e->getMessage() . "'); window.location.href = 'blog.php';</script>";
    }
} else {
    echo "<script>alert('ID da postagem não fornecido.'); window.location.href = 'blog.php';</script>";
}
?>