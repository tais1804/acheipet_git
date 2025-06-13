<?php
session_start();
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

// Variável para armazenar as mensagens
$mensagem_status = '';
$tipo_mensagem = ''; // 'success', 'warning', 'danger'

function obterProdutos(PDO $conexao, string $nome_pesquisa = '', ?int $id_categoria_animal = null, ?int $id_categoria_produto = null): array
{
    try {
        
        $sql = "SELECT p.*, ca.nome_categoria AS nome_categoria_animal, cp.nome_categoria AS nome_categoria_produto 
                FROM produtos p
                LEFT JOIN categoria_animais ca ON p.id_categoria_animal = ca.id_categoria_animal
                LEFT JOIN categoria_produtos cp ON p.id_categoria_produto = cp.id_categoria_produto
                WHERE 1=1";
        
        $params = [];

        if ($nome_pesquisa) {
            $sql .= " AND p.nome LIKE :nome_pesquisa";
            $params[':nome_pesquisa'] = "%$nome_pesquisa%";
        }
        if ($id_categoria_animal !== null) {
            $sql .= " AND p.id_categoria_animal = :id_categoria_animal";
            $params[':id_categoria_animal'] = $id_categoria_animal;
        }
        if ($id_categoria_produto !== null) {
            $sql .= " AND p.id_categoria_produto = :id_categoria_produto";
            $params[':id_categoria_produto'] = $id_categoria_produto;
        }

        $stmt = $conexao->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Altera a forma de exibir mensagens de erro do PDO para a nova variável
        $GLOBALS['mensagem_status'] = "Erro ao obter produtos: " . $e->getMessage();
        $GLOBALS['tipo_mensagem'] = 'danger';
        error_log("Erro PDO em obterProdutos: " . $e->getMessage());
        return [];
    }
}

function obterCategoriasProdutos(PDO $conexao): array
{
    try {
        $stmt = $conexao->query("SELECT id_categoria_produto, nome_categoria FROM categoria_produtos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter categorias de produtos: " . $e->getMessage());
        return [];
    }
}

function obterCategoriasAnimais(PDO $conexao): array
{
    try {
        $stmt = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter categorias de animais: " . $e->getMessage());
        return [];
    }
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
                        'imagem' => $produto['imagem'] ?? 'caminho/para/imagem_padrao.jpg'
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
}

$nome_pesquisa = $_GET['pesquisa'] ?? '';
$id_categoria_animal_filtro = !empty($_GET['categoria_animal']) ? (int)$_GET['categoria_animal'] : null;
$id_categoria_produto_filtro = !empty($_GET['categoria_produto']) ? (int)$_GET['categoria_produto'] : null;


$produtos = obterProdutos($conexao, $nome_pesquisa, $id_categoria_animal_filtro, $id_categoria_produto_filtro);

$categorias_animais = obterCategoriasAnimais($conexao);
$categorias_produtos = obterCategoriasProdutos($conexao);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <style>
    .row.row-cols-1.row-cols-md-2.row-cols-lg-3.g-4 {
        padding-bottom: 100px;
    }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
    body {
        font-family: 'Open Sans', sans-serif;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: 'Lato', sans-serif;
    }

    .product-card {
        border: 0px solid #dee2e6;
        border-radius: 1.0rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0rem 0.2rem 0.35rem rgba(0, 0, 0, 0.090);
        transition: transform 0.2s ease-in-out;
        background: #ffffff;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-card img {
        max-width: 100%;
        height: 200px;
        object-fit: contain;
        margin-bottom: 1rem;
        border-radius: 0.25rem;
    }

    .product-card .price {
        font-size: 1.25rem;
        font-weight: bold;
        color: #0d6efd;
        margin-bottom: 0.5rem;
    }

    .product-card .stock {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .product-card .btn {
        width: 100%;
    }

    .qtd {
        display: table !important;
        margin-top: 7px;
        height: 100%;
    }

    .form-label {
        font-size: 0.8rem !important;
        color: #7f7f7f;
    }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-3">
        <div class="d-flex justify-content-center"><img src="images/banner.png" class="img-fluid mb-3"
                alt="Banner de Promoção" style="max-width: 600px;"></div>
        <div class="p-4">
            <div class="d-flex d-grid gap-2 d-md-block justify-content-center mb-4">
                <a href="cadastrar_produto.php" class="btn btn-outline-primary btn-sm">Cadastrar novo produto</a>
            </div>

            <form method="get" action="loja_virtual.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="pesquisa" class="form-label">Nome do Produto:</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                        value="<?php echo htmlspecialchars($nome_pesquisa); ?>" placeholder="Pesquisar por nome">
                </div>

                <div class="col-md-3">
                    <label for="categoria_animal" class="form-label">Para qual Animal?</label>
                    <select id="categoria_animal" name="categoria_animal" class="form-select">
                        <option value="">Todas as Raças</option>
                        <?php foreach ($categorias_animais as $cat_animal): ?>
                        <option value="<?php echo $cat_animal['id_categoria_animal']; ?>"
                            <?php echo ($id_categoria_animal_filtro == $cat_animal['id_categoria_animal']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat_animal['nome_categoria']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col">
                    <label for="categoria_produto" class="form-label">Tipo de Produto?</label>
                    <select id="categoria_produto" name="categoria_produto" class="form-select">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categorias_produtos as $cat_produto): ?>
                        <option value="<?php echo $cat_produto['id_categoria_produto']; ?>"
                            <?php echo ($id_categoria_produto_filtro == $cat_produto['id_categoria_produto']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat_produto['nome_categoria']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary h-50 w-100">Buscar/Filtrar</button>
                    <a href="loja_virtual.php" class="btn btn-secondary h-50 w-100">Limpar Filtros</a>
                </div>


            </form>

        </div>

        <?php if (!empty($mensagem_status)) : ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
            <?php echo htmlspecialchars($mensagem_status); ?>
        </div>
        <?php endif; ?>

        <?php if (empty($produtos)) : ?>
        <div class="alert alert-info text-center" role="alert">
            Nenhum produto encontrado com os filtros aplicados.
        </div>
        <?php else : ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($produtos as $produto) : ?>
            <div class="col">
                <div class="product-card d-flex flex-column h-100">
                    <?php 
                                $imagem_produto = !empty($produto["imagem"]) ? htmlspecialchars($produto["imagem"]) : 'assets/img/placeholder.png';
                                if (!file_exists($imagem_produto) && !str_starts_with($imagem_produto, 'http')) {
                                    $imagem_produto = 'assets/img/placeholder.png';
                                }
                            ?>
                    <img src="<?php echo $imagem_produto; ?>" class="card-img-top mx-auto d-block"
                        alt="Imagem do Produto: <?php echo htmlspecialchars($produto['nome']); ?>">
                    <div class="card-body flex-grow-1">
                        <h3 class="card-title h5 text-primary"><?php echo htmlspecialchars($produto["nome"]); ?></h3>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($produto["descricao"]); ?></p>
                        <p class="card-text mb-1"><strong class="price">R$
                                <?php echo number_format($produto["preco"], 2, ',', '.'); ?></strong></p>
                        <p class="card-text stock">Estoque: <?php echo $produto["estoque"]; ?></p>
                        <p class="card-text small">
                            <span
                                class="badge bg-secondary"><?php echo htmlspecialchars($produto['nome_categoria_produto'] ?? 'Não Categorizado'); ?></span>
                            <span
                                class="badge bg-info text-dark"><?php echo htmlspecialchars($produto['nome_categoria_animal'] ?? 'Animal Desconhecido'); ?></span>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <div class="row">
                            <div class="col-9">
                                <form method="post" action="loja_virtual.php" class="d-flex flex-column mb-2">
                                    <input type="hidden" name="id_produto"
                                        value="<?php echo $produto["id_produto"]; ?>">
                                    <div class="row">
                                        <div class="col-5">

                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="w-100 qtd align-middle">Qtd</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="number"
                                                        id="quantidade_<?php echo $produto["id_produto"]; ?>"
                                                        name="quantidade" class="form-control text-center" value="1"
                                                        min="1" max="<?php echo $produto["estoque"]; ?>">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-7">
                                            <?php if ($produto["estoque"] > 0) : ?>
                                            <button type="submit" class="btn btn-success">Add</button>
                                            <?php else : ?>
                                            <button type="button" class="btn btn-danger" disabled>Produto
                                                Esgotado</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-3">
                                <form action="editar_produto.php" method="get">
                                    <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                                    <button type="submit" class="btn btn-warning w-100">Editar</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
</body>

</html>