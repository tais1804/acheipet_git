<?php
// Garante que a sessão esteja iniciada.
// dados_usuario.php já faz isso, mas é bom ter aqui também por segurança.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "conexao.php"; // Inclui a conexão com o banco de dados.
include "verificar_login.php"; // Verifica se o usuário está logado.
include "dados_usuario.php"; // Carrega os dados do usuário logado e define $id_usuario e $usuario.

// --- Lógica para determinar o id_petshop ---
$id_petshop = null;
if (isset($usuario['tipo_usuario']) && isset($usuario['id_usuario'])) { // Verifica se os dados do usuário foram carregados.
    // Somente permite cadastrar produtos se o tipo de usuário for 'petshop' ou 'administrador'.
    if ($usuario['tipo_usuario'] === 'petshop' || $usuario['tipo_usuario'] === 'administrador') {
        $id_petshop = $usuario['id_usuario']; // O id_usuario é o id_petshop neste contexto.
    } else {
        // Redireciona ou exibe uma mensagem de erro se o usuário não tiver permissão.
        echo "<p class='alert alert-danger'>Você não tem permissão para cadastrar produtos. Apenas Petshops e Administradores podem fazê-lo.</p>";
        // Opcional: header("Location: index.php"); exit();
    }
} else {
    // Caso os dados do usuário não tenham sido carregados, redireciona para login.
    header("Location: login.php");
    exit();
}
// --- Fim da lógica para determinar o id_petshop ---


function obterCategoriasProdutos(PDO $conexao): array
{
    try {
        $stmt = $conexao->query("SELECT id_categoria_produto, nome_categoria FROM categorias_produtos"); //
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter categorias de produtos: " . $e->getMessage());
        return [];
    }
}

function obterCategoriasAnimais(PDO $conexao): array
{
    try {
        $stmt = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais"); //
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter categorias de animais: " . $e->getMessage());
        return [];
    }
}


function cadastrarProduto($conexao, $id_petshop, $nome, $descricao, $preco, $estoque, $imagem, $id_categoria_animal, $id_categoria_produto)
{
    try {
        // Adiciona id_petshop na sua query de INSERT para a tabela 'produtos'
        $stmt = $conexao->prepare("INSERT INTO produtos (id_petshop, nome, descricao, preco, estoque, imagem, id_categoria_animal, id_categoria_produto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_petshop, $nome, $descricao, $preco, $estoque, $imagem, $id_categoria_animal, $id_categoria_produto]);
        return true;
    } catch (PDOException $e) {
        echo "Erro ao cadastrar produto: " . $e->getMessage();
        error_log("Erro PDO ao cadastrar produto: " . $e->getMessage());
        return false;
    }
}

function deletarProduto($conexao, $id_produto) {
    try {
        $stmt = $conexao->prepare("DELETE FROM produtos WHERE id_produto = ?"); //
        $stmt->execute([$id_produto]);
        return true;
    } catch (PDOException $e) {
        echo "Erro ao deletar produto: " . $e->getMessage();
        error_log("Erro PDO ao deletar produto: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se o id_petshop foi corretamente definido antes de prosseguir
    if ($id_petshop === null) {
        echo "<p class='alert alert-danger'>Erro: ID do petshop não definido. Verifique suas permissões de acesso.</p>";
    } else {
        $nome = $_POST["nome"] ?? '';
        $descricao = $_POST["descricao"] ?? '';
        $preco = $_POST["preco"] ?? 0;
        $estoque = $_POST["estoque"] ?? 0;
        $id_categoria_animal = $_POST["id_categoria_animal"] ?? null;
        $id_categoria_produto = $_POST["id_categoria_produto"] ?? null;
        
        $foto_destino = ''; 
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
            $diretorio_uploads = "uploads/";
            
            if (!is_dir($diretorio_uploads)) {
                mkdir($diretorio_uploads, 0777, true);
            }
            $foto_temp = $_FILES["foto"]["tmp_name"];
            $foto_nome = basename($_FILES["foto"]["name"]); 
            $foto_destino = $diretorio_uploads . uniqid() . "_" . $foto_nome; 

            if (!move_uploaded_file($foto_temp, $foto_destino)) {
                echo "<p class='alert alert-danger'>Erro ao mover arquivo de foto.</p>";
                $foto_destino = ''; 
            }
        }

        if (cadastrarProduto($conexao, $id_petshop, $nome, $descricao, $preco, $estoque, $foto_destino, $id_categoria_animal, $id_categoria_produto)) {
            echo "<p class='alert alert-success'>Produto cadastrado com sucesso!</p>";
        } else {
            echo "<p class='alert alert-danger'>Erro ao cadastrar produto.</p>";
        }
    }
}


if (isset($_GET['deletar'])) {
    $id_produto = $_GET['deletar'];
    if (deletarProduto($conexao, $id_produto)) {
        echo "<p class='alert alert-success'>Produto deletado com sucesso!</p>";
        header("Location: listar_produto.php?status=deleted");
        exit();
    } else {
        echo "<p class='alert alert-danger'>Erro ao deletar produto.</p>";
    }
}


$categorias_animais = obterCategoriasAnimais($conexao); //
$categorias_produtos = obterCategoriasProdutos($conexao); //

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: 'Open Sans', sans-serif;
        background-color: #f8f9fa;
    }

    h1,
    h2 {
        font-family: 'Lato', sans-serif;
    }

    .container {
        max-width: 600px;
        margin-top: 50px;
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container">
        <h1 class="text-center h3 mb-4 text-primary">Cadastrar Novo Produto</h1>

        <form method="post" action="cadastrar_produto.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Produto:</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="preco" class="form-label">Preço:</label>
                    <input type="number" id="preco" name="preco" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label for="estoque" class="form-label">Quantidade em Estoque:</label>
                    <input type="number" id="estoque" name="estoque" class="form-control" min="0" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="id_categoria_animal" class="form-label">Para qual Animal?</label>
                <select id="id_categoria_animal" name="id_categoria_animal" class="form-select" required>
                    <option value="">Selecione uma categoria de animal</option>
                    <?php foreach ($categorias_animais as $cat_animal): ?>
                    <option value="<?php echo $cat_animal['id_categoria_animal']; ?>">
                        <?php echo htmlspecialchars($cat_animal['nome_categoria']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_categoria_produto" class="form-label">Tipo de Produto?</label>
                <select id="id_categoria_produto" name="id_categoria_produto" class="form-select" required>
                    <option value="">Selecione uma categoria de produto</option>
                    <?php foreach ($categorias_produtos as $cat_produto): ?>
                    <option value="<?php echo $cat_produto['id_categoria_produto']; ?>">
                        <?php echo htmlspecialchars($cat_produto['nome_categoria']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto do Produto:</label>
                <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
            </div>

            <div class="d-grid gap-2 d-md-block">
                <button type="submit" class="btn btn-success">Cadastrar novo produto</button>
                <a href="listar_produto.php" class="btn btn-outline-secondary">Lista de Produtos cadastrados</a>
                <a href="loja_virtual.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
            <br />
        </form>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>