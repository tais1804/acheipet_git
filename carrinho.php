<?php
session_start();
include "conexao.php";
include "verificar_login.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

if (isset($_POST['id_produto']) && isset($_POST['quantidade'])) {
    $id_produto = $_POST['id_produto'];
    $quantidade = $_POST['quantidade'];

    if (isset($_SESSION['carrinho'][$id_produto])) {
        $_SESSION['carrinho'][$id_produto]['quantidade'] += $quantidade;
    } else {
        try {
            $stmt = $conexao->prepare("SELECT id_produto, nome, preco FROM Produtos WHERE id_produto = ?");
            $stmt->execute([$id_produto]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($produto) {
                $_SESSION['carrinho'][$id_produto] = array(
                    'nome' => $produto['nome'],
                    'preco' => $produto['preco'],
                    'quantidade' => $quantidade
                );
            } else {
                echo "Produto não encontrado!";
            }
        } catch (PDOException $e) {
            echo "Erro ao buscar produto: " . $e->getMessage();
        }
    }
}

if (isset($_GET['remover'])) {
    $id_produto = $_GET['remover'];
    unset($_SESSION['carrinho'][$id_produto]);
}

$total_carrinho = 0;
foreach ($_SESSION['carrinho'] as $produto) {
    $total_carrinho += $produto['preco'] * $produto['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <!-- Bootstrap 5.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4 text-center">Carrinho de Compras</h1>
        <div class="mb-4 text-center">
            <a href="index.php" class="btn btn-secondary">Voltar à Home</a>
        </div>

        <?php if (empty($_SESSION['carrinho'])): ?>
            <div class="alert alert-info text-center">Seu carrinho está vazio.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($_SESSION['carrinho'] as $id_produto => $produto): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $produto['nome']; ?></h5>
                                <p class="card-text">Preço: <strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                                <p class="card-text">Quantidade: <?php echo $produto['quantidade']; ?></p>
                                <p class="card-text">Total: <strong>R$ <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?></strong></p>
                                <a href="carrinho.php?remover=<?php echo $id_produto; ?>" class="btn btn-danger btn-sm mt-2">Remover</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-end">
                <h4>Total do Carrinho: <span class="text-success">R$ <?php echo number_format($total_carrinho, 2, ',', '.'); ?></span></h4>
                <a href="finalizar_compra.php" class="btn btn-success mt-3">Finalizar Compra</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5.0 JS (com Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
