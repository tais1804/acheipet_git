<?php
session_start();
include "conexao.php";
include "verificar_login.php";
include "dados_usuario.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

// Variáveis para armazenar as mensagens
$mensagem_status = '';
$tipo_mensagem = ''; // 'success', 'warning', 'danger'

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
                    $mensagem_status = "Produto não encontrado!";
                    $tipo_mensagem = 'warning';
                } elseif ($quantidade > $produto["estoque"]) {
                    $mensagem_status = "Quantidade indisponível no estoque! (Disponível: " . $produto["estoque"] . ")";
                    $tipo_mensagem = 'warning';
                } else {
                    if (isset($_SESSION['carrinho'][$id_produto])) {
                        if (($_SESSION['carrinho'][$id_produto]['quantidade'] + $quantidade) > $produto['estoque']) {
                            $mensagem_status = "Não é possível adicionar mais. Quantidade no carrinho excederia o estoque!";
                            $tipo_mensagem = 'warning';
                        } else {
                            $_SESSION['carrinho'][$id_produto]['quantidade'] += $quantidade;
                            $mensagem_status = "Quantidade atualizada no carrinho!";
                            $tipo_mensagem = 'success';
                        }
                    } else {
                        $_SESSION['carrinho'][$id_produto] = array(
                            'nome' => $produto['nome'],
                            'preco' => $produto['preco'],
                            'quantidade' => $quantidade,
                            'imagem' => !empty($produto['imagem']) ? $produto['imagem'] : 'images/imagem_padrao.jpg'
                        );
                        $mensagem_status = "Produto adicionado ao carrinho com sucesso!";
                        $tipo_mensagem = 'success';
                    }
                }
            } catch (PDOException $e) {
                $mensagem_status = "Erro ao adicionar ao carrinho: " . $e->getMessage();
                $tipo_mensagem = 'danger';
                error_log("Erro PDO ao adicionar ao carrinho: " . $e->getMessage());
            }
        } else {
            $mensagem_status = "Dados inválidos para adicionar ao carrinho.";
            $tipo_mensagem = 'danger';
        }
    } elseif ($acao == "remover_qtd") {
        $id_produto = $_POST['id_produto'] ?? null;

        if ($id_produto && isset($_SESSION['carrinho'][$id_produto])) {
            if ($_SESSION['carrinho'][$id_produto]['quantidade'] > 1) {
                $_SESSION['carrinho'][$id_produto]['quantidade']--;
                $mensagem_status = "Uma unidade de " . $_SESSION['carrinho'][$id_produto]['nome'] . " removida do carrinho!";
                $tipo_mensagem = 'success';
            } else {
                unset($_SESSION['carrinho'][$id_produto]);
                $mensagem_status = "Produto removido completamente do carrinho!";
                $tipo_mensagem = 'success';
            }
        } else {
            $mensagem_status = "Erro ao remover quantidade do carrinho.";
            $tipo_mensagem = 'danger';
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
                    $mensagem_status = "Uma unidade de " . $_SESSION['carrinho'][$id_produto]['nome'] . " adicionada ao carrinho!";
                    $tipo_mensagem = 'success';
                } elseif ($produto_bd && $_SESSION['carrinho'][$id_produto]['quantidade'] >= $produto_bd['estoque']) {
                    $mensagem_status = "Não é possível adicionar mais. Quantidade máxima em estoque atingida!";
                    $tipo_mensagem = 'warning';
                } else {
                    $mensagem_status = "Produto não encontrado no banco de dados.";
                    $tipo_mensagem = 'danger';
                }
            } catch (PDOException $e) {
                $mensagem_status = "Erro ao verificar estoque: " . $e->getMessage();
                $tipo_mensagem = 'danger';
                error_log("Erro PDO ao verificar estoque: " . $e->getMessage());
            }
        } else {
            $mensagem_status = "Erro ao adicionar quantidade ao carrinho.";
            $tipo_mensagem = 'danger';
        }
    } elseif ($acao == "remover_completo") {
        $id_produto = $_POST['id_produto'] ?? null;
        if ($id_produto && isset($_SESSION['carrinho'][$id_produto])) {
            unset($_SESSION['carrinho'][$id_produto]);
            $mensagem_status = "Produto removido completamente do carrinho!";
            $tipo_mensagem = 'success';
        } else {
            $mensagem_status = "Erro ao remover o produto do carrinho.";
            $tipo_mensagem = 'danger';
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
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
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

                <?php if (!empty($mensagem_status)) : ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
                    <?php echo htmlspecialchars($mensagem_status); ?>
                </div>
                <?php endif; ?>

                <?php if (empty($_SESSION['carrinho'])): ?>
                <div class="alert alert-info text-center">Seu carrinho está vazio.</div>
                <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($_SESSION['carrinho'] as $id_produto => $produto): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">


                                <img width="80" class="img-thumbnail"
                                    src="/acheipet_git/<?php echo htmlspecialchars($produto['imagem']); ?>"
                                    alt="Imagem do Produto">
                                <h5 class="card-title"><?php echo $produto['nome']; ?></h5>
                                <p class="card-text">Preço: <strong>R$
                                        <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                                <p class="card-text">Quantidade: <?php echo $produto['quantidade']; ?></p>
                                <p class="card-text">Total: <strong>R$
                                        <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?></strong>
                                </p>

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
                    <h4>Total do Carrinho: <span class="text-success">R$
                            <?php echo number_format($total_carrinho, 2, ',', '.'); ?></span></h4>
                    <form method="POST" action="banco/gerador.php">
                        <input type="hidden" name="dados_carrinho"
                            value='<?php echo json_encode($_SESSION["carrinho"], JSON_UNESCAPED_UNICODE); ?>'>
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