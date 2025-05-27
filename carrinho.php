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
<html>
<head>
    <title>Carrinho de Compras</title>
    <style>
        .produto {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Carrinho de Compras</h1>
    <a href="index.php">Home</a>
    <?php if (empty($_SESSION['carrinho'])): ?>
        <p>Seu carrinho está vazio.</p>
    <?php else: ?>
        <?php foreach ($_SESSION['carrinho'] as $id_produto => $produto): ?>
            <div class="produto">
                <h2><?php echo $produto['nome']; ?></h2>
                <p>Preço: R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                <p>Quantidade: <?php echo $produto['quantidade']; ?></p>
                <p>Total: R$ <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?></p>
                <a href="carrinho.php?remover=<?php echo $id_produto; ?>">Remover</a>
            </div>
        <?php endforeach; ?>
        <p>Total do Carrinho: R$ <?php echo number_format($total_carrinho, 2, ',', '.'); ?></p>
        <a href="finalizar_compra.php">Finalizar Compra</a>
    <?php endif; ?>
</body>
</html>
