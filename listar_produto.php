<?php
include "conexao.php";

include "verificar_login.php";

function deletarProduto($conexao, $id_produto) {
    try {
        $stmt = $conexao->prepare("DELETE FROM Produtos WHERE id_produto = ?");
        $stmt->execute([$id_produto]);
        return true;
    } catch (PDOException $e) {
        echo "Erro ao deletar produto: " . $e->getMessage();
        return false;
    }
}

if (isset($_GET['deletar'])) {
    $id_produto = $_GET['deletar'];
    if (deletarProduto($conexao, $id_produto)) {
        echo "<p style='color:green;'>Produto deletado com sucesso!</p>"; // Feedback de sucesso
    } else {
        echo "<p style='color:red;'>Erro ao deletar produto.</p>"; // Feedback de erro
    }
}

try {
    $stmt = $conexao->prepare("SELECT id_produto, nome, descricao, preco, estoque, imagem FROM Produtos");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar produtos: " . $e->getMessage();
    $produtos = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Produtos</title>
</head>
<body>
    <h1>Lista de Produtos</h1>
    <?php if (empty($produtos)): ?>
        <p>Nenhum produto cadastrado.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($produtos as $produto): ?>
                <li>
                    Nome: <?php echo $produto['nome']; ?> |
                    Descrição: <?php echo $produto['descricao']; ?> |
                    Preço: <?php echo $produto['preco']; ?> |
                    Estoque: <?php echo $produto['estoque']; ?> |
                    Imagem: <?php echo $produto['imagem']; ?> |
                    <a href="listar_produtos.php?deletar=<?php echo $produto['id_produto']; ?>" onclick="return confirm('Tem certeza que deseja deletar este produto?')">Deletar</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="cadastrar_produto.php">Voltar para Cadastrar Produtos</a>
</body>
</html>
