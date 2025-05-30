<?php
include "conexao.php";
include "dados_usuario.php"; 
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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Produtos</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body class="bg-light">
<?php include "header.php"; // Inclua seu cabeçalho aqui ?>
<div class="container mt-5">
    <h1 class="mb-4 text-primary">Lista de Produtos</h1>

    <?php if (empty($produtos)): ?>
        <div class="alert alert-warning" role="alert">
            Nenhum produto cadastrado.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($produtos as $produto): ?>
                <div class="list-group-item list-group-item-action mb-3 shadow-sm rounded">
                    <h5 class="mb-3 h5 text-success"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                    <p class="mb-3">
                        <div class="row">
                            <div class="col-md-auto mb-3">
                                <img width="80" class="img-thumbnail" src="<?php echo htmlspecialchars($produto['imagem']); ?>">    
                            </div>
                            <div class="col">
                                <strong>Descrição:</strong> <?php echo htmlspecialchars($produto['descricao']); ?><br>
                                <strong>Preço:</strong> R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?><br>
                                <strong>Estoque:</strong> <?php echo (int)$produto['estoque']; ?><br>
                            </div>
                        </div>
                        
                    </p>
                    <div class="mb-3 row">
                        <div class="col-md-auto">
                        <a href="listar_produtos.php?deletar=<?php echo $produto['id_produto']; ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Tem certeza que deseja deletar este produto?')">Deletar</a>
            </div>
            <div class="col-md-auto">
                            <form action="editar_produto.php" method="get">
                                        <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">Editar</button>
                                    </form>
            </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="cadastrar_produto.php" class="btn btn-outline-primary mt-4">Cadastrar Produtos</a>
    <a href="loja_virtual.php" class="btn btn-primary mt-4">Loja virtual</a>
    <br/><br/><br/>
</div>

<!-- Bootstrap JS (opcional, apenas se você usar componentes interativos) -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

