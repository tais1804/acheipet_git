<?php
session_start();
include "conexao.php";
include "verificar_login.php";
include "dados_usuario.php";


if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produto = $_POST["id_produto"] ?? null;
    $quantidade = $_POST["quantidade"] ?? 0;

    if ($id_produto && $quantidade > 0) {
        try {
            $stmt = $conexao->prepare("SELECT id_produto, nome, preco, estoque, imagem FROM produtos WHERE id_produto = ?");
            $stmt->execute([$id_produto]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                echo "<p class='alert alert-warning'>Produto não encontrado!</p>";
            } elseif ($quantidade > $produto["estoque"]) {
                echo "<p class='alert alert-warning'>Quantidade indisponível no estoque! (Disponível: " . $produto["estoque"] . ")</p>";
            } else {
                if (isset($_SESSION['carrinho'][$id_produto])) {
                    // Verifica se a adição excede o estoque
                    if (($_SESSION['carrinho'][$id_produto]['quantidade'] + $quantidade) > $produto['estoque']) {
                        echo "<p class='alert alert-warning'>Não é possível adicionar mais. Quantidade no carrinho excederia o estoque!</p>";
                    } else {
                        $_SESSION['carrinho'][$id_produto]['quantidade'] += $quantidade;
                        echo "<p class='alert alert-success'>Quantidade atualizada no carrinho!</p>";
                    }
                } else {
                    $_SESSION['carrinho'][$id_produto] = array(
                    'nome' => $produto['nome'],
                    'preco' => $produto['preco'],
                    'quantidade' => $quantidade,
                    'imagem' => !empty($produto['imagem']) ? $produto['imagem'] : 'images/imagem_padrao.jpg'

                );

                    echo "<p class='alert alert-success'>Produto adicionado ao carrinho com sucesso!</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p class='alert alert-danger'>Erro ao adicionar ao carrinho: " . $e->getMessage() . "</p>";
            error_log("Erro PDO ao adicionar ao carrinho: " . $e->getMessage());
        }
    } else {
        echo "<p class='alert alert-danger'>Dados inválidos para adicionar ao carrinho.</p>";
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
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body class="bg-light">
    
    <div id="webcrumbs">
            <div class="relative w-full min-h-screen">
                <?php
                include "header.php";
                ?>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Carrinho de Compras</h1>
        <div class="mb-4 text-center">
            <a href="index.php" class="btn btn-secondary">Voltar à Home</a>
            <a href="loja_virtual.php" class="btn btn-primary">Voltar a Loja</a>
        </div>

        <?php if (empty($_SESSION['carrinho'])): ?>
            <div class="alert alert-info text-center">Seu carrinho está vazio.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($_SESSION['carrinho'] as $id_produto => $produto): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                
                                <img width="80" class="img-thumbnail" src="<?php echo ltrim($produto['imagem'], '/'); ?>">

                                
                            
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
                <form method="POST" action="banco/gerador.php">
                    <input type="hidden" name="dados_carrinho" value='<?php echo json_encode($_SESSION["carrinho"], JSON_UNESCAPED_UNICODE); ?>'>
                    <button name="sendAp" type="submit" class="btn btn-warning mt-3">Gerar Boleto</button>
                </form>

            </div>
        <?php endif; ?>
    </div>
                </div>
                </div>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap 5.0 JS (com Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    
</body>
</html>
