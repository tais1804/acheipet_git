<?php
session_start();
include "conexao.php";
include "verificar_login.php";
include "dados_usuario.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST["acao"] ?? null;

    if ($acao == "adicionar_carrinho") {
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
    } elseif ($acao == "remover_qtd") {
        $id_produto = $_POST['id_produto'] ?? null;

        if ($id_produto && isset($_SESSION['carrinho'][$id_produto])) {
            if ($_SESSION['carrinho'][$id_produto]['quantidade'] > 1) {
                $_SESSION['carrinho'][$id_produto]['quantidade']--;
                echo "<p class='alert alert-success'>Uma unidade de " . $_SESSION['carrinho'][$id_produto]['nome'] . " removida do carrinho!</p>";
            } else {
                unset($_SESSION['carrinho'][$id_produto]);
                echo "<p class='alert alert-success'>Produto removido completamente do carrinho!</p>";
            }
        } else {
            echo "<p class='alert alert-danger'>Erro ao remover quantidade do carrinho.</p>";
        }
    } elseif ($acao == "adicionar_qtd") {
        $id_produto = $_POST['id_produto'] ?? null;

        if ($id_produto && isset($_SESSION['carrinho'][$id_produto])) {
            try {
                $stmt = $conexao->prepare("SELECT estoque FROM produtos WHERE id_produto = ?");
                $stmt->execute([$id_produto]);
                $produto_bd = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($produto_bd && $_SESSION['carrinho'][$id_produto]['quantidade'] < $produto_bd['estoque']) {
                    $_SESSION['carrinho'][$id_produto]['quantidade']++;
                    echo "<p class='alert alert-success'>Uma unidade de " . $_SESSION['carrinho'][$id_produto]['nome'] . " adicionada ao carrinho!</p>";
                } elseif ($produto_bd && $_SESSION['carrinho'][$id_produto]['quantidade'] >= $produto_bd['estoque']) {
                    echo "<p class='alert alert-warning'>Não é possível adicionar mais. Quantidade máxima em estoque atingida!</p>";
                } else {
                    echo "<p class='alert alert-danger'>Produto não encontrado no banco de dados.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='alert alert-danger'>Erro ao verificar estoque: " . $e->getMessage() . "</p>";
                error_log("Erro PDO ao verificar estoque: " . $e->getMessage());
            }
        } else {
            echo "<p class='alert alert-danger'>Erro ao adicionar quantidade ao carrinho.</p>";
        }
    } elseif ($acao == "remover_completo") {
        $id_produto = $_POST['id_produto'] ?? null;
        if ($id_produto && isset($_SESSION['carrinho'][$id_produto])) {
            unset($_SESSION['carrinho'][$id_produto]);
            echo "<p class='alert alert-success'>Produto removido completamente do carrinho!</p>";
        } else {
            echo "<p class='alert alert-danger'>Erro ao remover o produto do carrinho.</p>";
        }
    }
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
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
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
                                
                                
                                <img width="80" class="img-thumbnail" src="/acheipet_git/<?php echo htmlspecialchars($produto['imagem']); ?>" alt="Imagem do Produto">
                                <h5 class="card-title"><?php echo $produto['nome']; ?></h5>
                                <p class="card-text">Preço: <strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                                <p class="card-text">Quantidade: <?php echo $produto['quantidade']; ?></p>
                                <p class="card-text">Total: <strong>R$ <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?></strong></p>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <form method="post" action="carrinho.php" class="d-inline">
                                        <input type="hidden" name="acao" value="remover_qtd">
                                        <input type="hidden" name="id_produto" value="<?php echo $id_produto; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger me-2">-</button>
                                    </form>
                                    <span class="fs-5"><?php echo $produto['quantidade']; ?></span>
                                    <form method="post" action="carrinho.php" class="d-inline">
                                        <input type="hidden" name="acao" value="adicionar_qtd">
                                        <input type="hidden" name="id_produto" value="<?php echo $id_produto; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success ms-2">+</button>
                                    </form>
                                </div>

                                <form method="post" action="carrinho.php" class="mt-2">
                                    <input type="hidden" name="acao" value="remover_completo">
                                    <input type="hidden" name="id_produto" value="<?php echo $id_produto; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir Item</button>
                                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    
</body>
</html>