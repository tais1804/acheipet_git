<?php
session_start();
include "conexao.php";
include "verificar_login.php";
include "dados_usuario.php";

$id_produto = $_GET['id_produto'] ?? null;

if (!$id_produto) {
    echo "<p class='alert alert-danger'>ID do produto não fornecido.</p>";
    exit;
}

function obterCategoriasAnimais(PDO $conexao): array {
    $stmt = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function obterCategoriasProdutos(PDO $conexao): array {
    $stmt = $conexao->query("SELECT id_categoria_produto, nome_categoria FROM categoria_produtos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $stmt = $conexao->prepare("SELECT * FROM produtos WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        echo "<p class='alert alert-warning'>Produto não encontrado.</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='alert alert-danger'>Erro ao buscar produto: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $estoque = (int)$_POST['estoque'];
    $id_categoria_animal = $_POST['id_categoria_animal'];
    $id_categoria_produto = $_POST['id_categoria_produto'];
    $imagem = $produto['imagem']; 

if (!empty($_FILES['foto']['name'])) {
    $nome_arquivo = uniqid() . "_" . basename($_FILES['foto']['name']);
    $caminho_destino = "images/" . $nome_arquivo;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
        $imagem = $caminho_destino;
    } else {
        echo "<p class='alert alert-warning'>Erro ao fazer upload da imagem.</p>";
    }
}


    try {
        $stmt = $conexao->prepare("UPDATE produtos 
            SET nome = ?, descricao = ?, preco = ?, estoque = ?, id_categoria_animal = ?, id_categoria_produto = ?, imagem = ? 
            WHERE id_produto = ?");
        $stmt->execute([
            $nome, $descricao, $preco, $estoque, $id_categoria_animal, $id_categoria_produto, $imagem, $id_produto
        ]);
        echo "<p class='alert alert-success'>Produto atualizado com sucesso!</p>";
        $produto = array_merge($produto, $_POST);
    } catch (PDOException $e) {
        echo "<p class='alert alert-danger'>Erro ao atualizar: " . $e->getMessage() . "</p>";
    }
}

$categorias_animais = obterCategoriasAnimais($conexao);
$categorias_produtos = obterCategoriasProdutos($conexao);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body class="bg-light">
    
<div class="container mt-5">

    <h1 class="mb-4 text-primary">Editar Produto</h1>
    <div class="d-flex justify-content-center mb-4">
            <a href="index.php" class="btn btn-outline-secondary me-2">Página Inicial</a>
            <a href="loja_virtual.php" class="btn  btn-primary me-2">Voltar a loja</a>
            <a href="carrinho.php" class="btn btn-outline-secondary">Ver Carrinho</a>
        </div>

    <form method="post" enctype="multipart/form-data"  class="card p-4 shadow-sm bg-white">
        
        <div class="mb-3">
            <label for="nome" class="form-label">Nome:</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($produto['nome']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição:</label>
            <textarea name="descricao" id="descricao" class="form-control" required><?= htmlspecialchars($produto['descricao']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="preco" class="form-label">Preço (R$):</label>
            <input type="text" name="preco" id="preco" class="form-control" value="<?= number_format($produto['preco'], 2, ',', '.') ?>" required>
        </div>

        <div class="mb-3">
            <label for="estoque" class="form-label">Estoque:</label>
            <input type="number" name="estoque" id="estoque" class="form-control" value="<?= $produto['estoque'] ?>" min="0" required>
        </div>

        <div class="mb-3">
            <label for="foto" class="form-label">Foto do Produto:</label>
            <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
            <?php if (!empty($produto['imagem'])): ?>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="Imagem atual" width="150">
                </div>
            <?php endif; ?>
        </div>


        <div class="mb-3">
            <label for="id_categoria_animal" class="form-label">Categoria de Animal:</label>
            <select name="id_categoria_animal" id="id_categoria_animal" class="form-select" required>
                <?php foreach ($categorias_animais as $cat): ?>
                    <option value="<?= $cat['id_categoria_animal'] ?>" <?= ($produto['id_categoria_animal'] == $cat['id_categoria_animal']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome_categoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="id_categoria_produto" class="form-label">Categoria de Produto:</label>
            <select name="id_categoria_produto" id="id_categoria_produto" class="form-select" required>
                <?php foreach ($categorias_produtos as $cat): ?>
                    <option value="<?= $cat['id_categoria_produto'] ?>" <?= ($produto['id_categoria_produto'] == $cat['id_categoria_produto']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome_categoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="loja_virtual.php" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
    </form>
    <script src="https://cdn.tailwindcss.com"></script>
</div>

</body>
</html>
