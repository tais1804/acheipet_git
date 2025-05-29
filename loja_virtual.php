

<?php
session_start();
include "conexao.php"; 
include "dados_usuario.php"; 
include "verificar_login.php"; 


if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

/**
 * Função para obter produtos do banco de dados, com filtros.
 * @param PDO $conexao Objeto de conexão PDO.
 * @param string $nome_pesquisa Termo de pesquisa para o nome do produto.
 * @param int|null $id_categoria_animal ID da categoria de animal para filtro.
 * @param int|null $id_categoria_produto ID da categoria de produto para filtro.
 * @return array Lista de produtos.
 */
function obterProdutos(PDO $conexao, string $nome_pesquisa = '', ?int $id_categoria_animal = null, ?int $id_categoria_produto = null): array
{
    try {
        $sql = "SELECT p.*, ca.nome_categoria AS nome_categoria_animal, cp.nome_categoria AS nome_categoria_produto 
                FROM produtos p
                LEFT JOIN categoria_animais ca ON p.id_categoria_animal = ca.id_categoria_animal
                LEFT JOIN categoria_produtos cp ON p.id_categoria_produto = cp.id_categoria_produto
                WHERE 1=1"; // Cláusula WHERE inicial para facilitar a adição de condições
        
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
        echo "<p class='text-danger'>Erro ao obter produtos: " . $e->getMessage() . "</p>";
        error_log("Erro PDO em obterProdutos: " . $e->getMessage());
        return [];
    }
}

/**
 * Função para obter categorias de produtos.
 * @param PDO $conexao Objeto de conexão PDO.
 * @return array Lista de categorias de produtos.
 */
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

/**
 * Função para obter categorias de animais.
 * @param PDO $conexao Objeto de conexão PDO.
 * @return array Lista de categorias de animais.
 */
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

// Processamento do formulário de adicionar ao carrinho
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produto = $_POST["id_produto"] ?? null;
    $quantidade = $_POST["quantidade"] ?? 0;

    if ($id_produto && $quantidade > 0) {
        try {
            $stmt = $conexao->prepare("SELECT id_produto, nome, preco, estoque FROM produtos WHERE id_produto = ?");
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
                        'imagem' => $produto['imagem'] ?? 'caminho/para/imagem_padrao.jpg' // Adicionar imagem ao carrinho
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

// Obter parâmetros de filtro da URL
$nome_pesquisa = $_GET['pesquisa'] ?? '';
$id_categoria_animal_filtro = isset($_GET['categoria_animal']) ? (int)$_GET['categoria_animal'] : null;
$id_categoria_produto_filtro = isset($_GET['categoria_produto']) ? (int)$_GET['categoria_produto'] : null;

// Obter produtos com filtros
$produtos = obterProdutos($conexao, $nome_pesquisa, $id_categoria_animal_filtro, $id_categoria_produto_filtro);

// Obter categorias para os filtros do formulário
$categorias_animais = obterCategoriasAnimais($conexao);
$categorias_produtos = obterCategoriasProdutos($conexao);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja Virtual Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Lato', sans-serif; }
        .product-card {
            border: 1px solid #dee2e6; /* light gray border */
            border-radius: 0.5rem; /* rounded corners */
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* subtle shadow */
            transition: transform 0.2s ease-in-out;
        }
        .product-card:hover {
            transform: translateY(-5px); /* slight lift on hover */
        }
        .product-card img {
            max-width: 100%;
            height: 200px; /* Fixed height for product images */
            object-fit: contain; /* Ensures the image fits without cropping, maintains aspect ratio */
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .product-card .price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d6efd; /* Bootstrap primary blue */
            margin-bottom: 0.5rem;
        }
        .product-card .stock {
            font-size: 0.875rem;
            color: #6c757d; /* Bootstrap secondary gray */
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
    </style>
</head>
<body>
    <?php include "header.php"; // Inclua seu cabeçalho aqui ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4 text-primary">Loja Virtual Petshop</h1>

        <div class="d-flex justify-content-center mb-4">
            <a href="index.php" class="btn btn-outline-secondary me-2">Página Inicial</a>
            <a href="carrinho.php" class="btn btn-primary">Ver Carrinho</a>
        </div>

        <div class="card mb-4 p-4 shadow-sm">
            <h5 class="card-title mb-3">Filtrar Produtos</h5>
            <form method="get" action="loja_virtual.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="pesquisa" class="form-label">Nome do Produto:</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control" value="<?php echo htmlspecialchars($nome_pesquisa); ?>" placeholder="Pesquisar por nome">
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

                <div class="col-md-3">
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
                
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-info">Buscar/Filtrar</button>
                </div>
            </form>
        </div>

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
                                $imagem_produto = !empty($produto["imagem"]) ? htmlspecialchars($produto["imagem"]) : 'assets/img/placeholder.png'; // Imagem padrão
                                if (!file_exists($imagem_produto) && !str_starts_with($imagem_produto, 'http')) {
                                    $imagem_produto = 'assets/img/placeholder.png'; 
                                }
                            ?>
                            <img src="<?php echo $imagem_produto; ?>" class="card-img-top mx-auto d-block" alt="Imagem do Produto: <?php echo htmlspecialchars($produto['nome']); ?>">
                            <div class="card-body flex-grow-1">
                                <h3 class="card-title h5 text-primary"><?php echo htmlspecialchars($produto["nome"]); ?></h3>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($produto["descricao"]); ?></p>
                                <p class="card-text mb-1"><strong class="price">R$ <?php echo number_format($produto["preco"], 2, ',', '.'); ?></strong></p>
                                <p class="card-text stock">Estoque: <?php echo $produto["estoque"]; ?></p>
                                <p class="card-text small">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($produto['nome_categoria_produto'] ?? 'Não Categorizado'); ?></span>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($produto['nome_categoria_animal'] ?? 'Animal Desconhecido'); ?></span>
                                </p>
                            </div>
                            <!--<div class="card-footer bg-transparent border-0 pt-0">
                                <form method="post" action="loja_virtual.php" class="d-flex flex-column">
                                    <input type="hidden" name="id_produto" value="<?php echo $produto["id_produto"]; ?>">
                                    <div class="mb-3">
                                        <label for="quantidade_<?php echo $produto["id_produto"]; ?>" class="form-label visually-hidden">Quantidade:</label>
                                        <input type="number" id="quantidade_<?php echo $produto["id_produto"]; ?>" name="quantidade" class="form-control text-center" value="1" min="1" max="<?php echo $produto["estoque"]; ?>">
                                    </div>
                                    <?php if ($produto["estoque"] > 0) : ?>
                                        <button type="submit" class="btn btn-success">Adicionar ao Carrinho</button>
                                    <?php else : ?>
                                        <button type="button" class="btn btn-danger" disabled>Produto Esgotado</button>
                                    <?php endif; ?>
                                </form>
                            </div>-->
                            <div class="card-footer bg-transparent border-0 pt-0">
                            <!-- Formulário de adicionar ao carrinho -->
                             <div class="row">
                                <div class="col-9">
                                    <form method="post" action="loja_virtual.php" class="d-flex flex-column mb-2">
                                        <input type="hidden" name="id_produto" value="<?php echo $produto["id_produto"]; ?>">
                                        <div class="row">
                                        <div class="col-4">

                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="w-100 qtd align-middle">Qtd</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="number" id="quantidade_<?php echo $produto["id_produto"]; ?>" name="quantidade" class="form-control text-center" value="1" min="1" max="<?php echo $produto["estoque"]; ?>">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-8">
                                        <?php if ($produto["estoque"] > 0) : ?>
                                            <button type="submit" class="btn btn-success">Add ao Carrinho</button>
                                        <?php else : ?>
                                            <button type="button" class="btn btn-danger" disabled>Produto Esgotado</button>
                                        <?php endif; ?>
                                        </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-3">
                                    <!-- Formulário de editar produto (separado) -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>