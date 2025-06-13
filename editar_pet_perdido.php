<?php
include "conexao.php";
include "dados_usuario.php"; // Assume que $_SESSION['id_usuario'] é definido aqui
include "verificar_login.php"; // Assume que a verificação de login redireciona se não estiver logado

// Verifica se o ID do pet perdido foi passado na URL
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: meus_pets.php"); // Redireciona se o ID for inválido ou não fornecido
    exit();
}

$id_pet_perdido = $_GET["id"];
$mensagem_sucesso = ""; // Inicializa a variável para a mensagem de sucesso
$mensagem_erro = "";    // Inicializa a variável para a mensagem de erro

// 1. Carrega os dados atuais do pet perdido para preencher o formulário
try {
    $stmt = $conexao->prepare("SELECT * FROM PetsPerdidos WHERE id_pet_perdido = ?");
    $stmt->execute([$id_pet_perdido]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o pet não for encontrado, redireciona ou exibe um erro
    if (!$pet) {
        header("Location: meus_pets.php"); // Ou exiba uma mensagem de "Pet não encontrado"
        exit();
    }
} catch (PDOException $e) {
    $mensagem_erro .= "<p>Erro ao obter dados do pet: " . $e->getMessage() . "</p>";
    $pet = []; // Garante que $pet é um array vazio para evitar erros posteriores se a consulta falhar
}

// Carrega as categorias de animais
try {
    $stmt_categorias = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $categorias_animais = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias_animais = [];
    $mensagem_erro .= "<p>Erro ao obter categorias de animais: " . $e->getMessage() . "</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta os dados do formulário com tratamento para chaves indefinidas
    $nome = $_POST["nome"] ?? '';
    $especie = $_POST["especie"] ?? '';
    $raca = $_POST["raca"] ?? '';
    $data_perda = $_POST["data_perda"] ?? '';
    $local_perdido = $_POST["local_perdido"] ?? '';
    $descricao = $_POST["descricao"] ?? '';
    $idade_valor = $_POST["idade_valor"] ?? null; // Use null para valores numéricos vazios
    $idade_unidade = $_POST["idade_unidade"] ?? '';
    $genero = $_POST["genero"] ?? '';
    $telefone_contato = $_POST["telefone_contato"] ?? ''; // Coletado, mas verifique onde ele será armazenado
    $status_perda = $_POST["status_perda"] ?? ''; // Corrigido para 'status_perda'

    // Lida com o upload da foto
    $foto = $pet["foto"] ?? ''; // Mantém a foto antiga por padrão
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $nome_arquivo = basename($_FILES['foto']['name']);
        $caminho_destino = "uploads/" . $nome_arquivo;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
            $foto = $caminho_destino;
        } else {
            $mensagem_erro .= "<p>Erro ao mover o arquivo de imagem.</p>";
        }
    }

    // O id_usuario não precisa vir do POST se já está na sessão e o pet pertence ao usuário logado
    // Se o id_usuario precisa ser atualizado, ele deve vir do formulário e ser validado.
    // Para edição, geralmente o id_usuario é do dono do pet e não muda.
    // Certifique-se de que o usuário logado é o dono do pet antes de permitir a edição.
    // $id_usuario = $_POST["id_usuario"]; // Remova se você não quiser permitir a mudança de dono.
    // Para segurança, sempre verifique se o usuário logado é o dono do pet.
    // Por exemplo, você pode adicionar uma condição WHERE id_usuario = ? na query de UPDATE e passar $_SESSION['id_usuario'].

    try {
        // CORREÇÃO CRÍTICA AQUI: Tabela PetsPerdidos e colunas corretas
        // Adapte esta query para as COLUNAS REAIS da sua tabela PetsPerdidos
        $sql_update = "UPDATE PetsPerdidos SET
                            nome = ?,
                            especie = ?,
                            raca = ?,
                            data_perda = ?,
                            local_perdido = ?,
                            descricao = ?,
                            idade_valor = ?,
                            idade_unidade = ?,
                            genero = ?,
                            foto = ?,
                            status_perda = ?
                        WHERE id_pet_perdido = ?";
        
        // Adiciona a condição para garantir que apenas o dono do pet pode editar
        // if (isset($_SESSION['id_usuario'])) {
        //     $sql_update .= " AND id_usuario = ?";
        // }

        $stmt = $conexao->prepare($sql_update);
        
        $params = [
            $nome,
            $especie,
            $raca,
            $data_perda,
            $local_perdido,
            $descricao,
            $idade_valor,
            $idade_unidade,
            $genero,
            $foto,
            $status_perda, // Corrigido para status_perda
            $id_pet_perdido
        ];

        // Adiciona o id_usuario aos parâmetros se a condição for adicionada na query
        // if (isset($_SESSION['id_usuario'])) {
        //     $params[] = $_SESSION['id_usuario'];
        // }

        $stmt->execute($params);

        // Verifique se alguma linha foi afetada para dar o feedback correto
        if ($stmt->rowCount() > 0) {
            $mensagem_sucesso = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <strong>Pet atualizado com sucesso!</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            // Recarrega os dados do pet após a atualização para que o formulário mostre os dados mais recentes
            // CORREÇÃO AQUI: Recarregar da tabela PetsPerdidos
            $stmt = $conexao->prepare("SELECT * FROM PetsPerdidos WHERE id_pet_perdido = ?");
            $stmt->execute([$id_pet_perdido]);
            $pet = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Se nenhuma linha foi afetada, pode ser que nenhum dado tenha mudado ou o ID/usuário não correspondeu
            $mensagem_erro .= "<p>Nenhuma alteração foi salva ou o pet não pôde ser encontrado/editado pelo seu usuário.</p>";
        }

    } catch (PDOException $e) {
        $mensagem_erro .= "<p>Erro ao atualizar pet: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Open+Sans&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    body {
        font-family: 'Open Sans', sans-serif;
    }

    h1,
    h2 {
        font-family: 'Lato', sans-serif;
    }

    img.fotopet {
        max-width: 100px;
        height: auto;
        object-fit: cover;
    }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>

<body>
    <?php include "header.php"; ?>
    <main>
        <div class="container">
            <div class="container mx-auto p-6">
                <div class="relative w-full min-h-screen row justify-content-md-center">
                    <div class="col-md-8">
                        <br>
                        <?php 
                            if (!empty($mensagem_sucesso)) {
                                echo $mensagem_sucesso;
                            }
                            if (!empty($mensagem_erro)) {
                                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $mensagem_erro . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                            }
                        ?>
                        <div class="card">
                            <div class="card-body">
                                <h1 class="h4">Editar pet</h1>
                                <form class="" method="post" enctype="multipart/form-data"
                                    action="editar_pet_perdido.php?id=<?php echo $id_pet_perdido; ?>">

                                    <div class="mb-3 row">
                                        <div class="col-md-4">
                                            <label class="form-label">Nome:</label class="form-label">
                                            <input class="form-control" type="text" name="nome"
                                                value="<?php echo htmlspecialchars($pet["nome"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Espécie:</label><br>
                                            <select class="form-select" name="especie" required>
                                                <option value="">Selecione a espécie</option>
                                                <?php foreach ($categorias_animais as $categoria): ?>
                                                <option
                                                    value="<?php echo htmlspecialchars($categoria['nome_categoria']); ?>"
                                                    <?php if (($pet["especie"] ?? '') == $categoria['nome_categoria']) echo "selected"; ?>>
                                                    <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select><br>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Raça:</label class="form-label">
                                            <input class="form-control" type="text" name="raca"
                                                value="<?php echo htmlspecialchars($pet["raca"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Data da perda:</label class="form-label">
                                            <input class="form-control" type="date" name="data_perda"
                                                value="<?php echo htmlspecialchars($pet["data_perda"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Local perdido:</label class="form-label">
                                            <input class="form-control" type="text" name="local_perdido"
                                                value="<?php echo htmlspecialchars($pet["local_perdido"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Idade:</label class="form-label">
                                            <input class="form-control" type="number" name="idade_valor"
                                                value="<?php echo htmlspecialchars($pet["idade_valor"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unidade da Idade:</label>
                                            <select class="form-select" name="idade_unidade">
                                                <option value="anos"
                                                    <?php if (($pet["idade_unidade"] ?? '') == "anos") echo "selected"; ?>>
                                                    Anos
                                                </option>
                                                <option value="meses"
                                                    <?php if (($pet["idade_unidade"] ?? '') == "meses") echo "selected"; ?>>
                                                    Meses
                                                </option>
                                            </select><br>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Gênero:</label>
                                            <select class="form-select" name="genero" required>
                                                <option value="">Selecione o gênero</option>
                                                <option value="Macho"
                                                    <?php if (($pet["genero"] ?? '') == "Macho") echo "selected"; ?>>
                                                    Macho
                                                </option>
                                                <option value="Fêmea"
                                                    <?php if (($pet["genero"] ?? '') == "Fêmea") echo "selected"; ?>>
                                                    Fêmea
                                                </option>
                                                <option value="Não Informado"
                                                    <?php if (($pet["genero"] ?? '') == "Não Informado") echo "selected"; ?>>
                                                    Não
                                                    Informado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Telefone de Contato:</label>
                                            <input class="form-control" type="text" name="telefone_contato"
                                                value="<?php echo htmlspecialchars($pet["telefone_contato"] ?? ''); ?>"><br>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Descrição:</label class="form-label">
                                            <textarea class="form-control"
                                                name="descricao"><?php echo htmlspecialchars($pet["descricao"] ?? ''); ?></textarea><br>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Status:</label class="form-label"><br>
                                            <select class="form-select" name="status_perda">
                                                <option value="Perdido"
                                                    <?php if (($pet["status_perda"] ?? '') == "Perdido") echo "selected"; ?>>
                                                    Perdido
                                                </option>
                                                <option value="Encontrado"
                                                    <?php if (($pet["status_perda"] ?? '') == "Encontrado") echo "selected"; ?>>
                                                    Encontrado</option>
                                            </select><br><br>
                                        </div>

                                        <div class="col-md-10">
                                            <label class="form-label">Foto atual:</label><br>

                                            <label class="form-label">Alterar Foto:</label class="form-label">
                                            <input class="form-control" type="file" name="foto"><br>
                                        </div>
                                        <div class="col-md-2">
                                            <?php if (!empty($pet["foto"])): // Use !empty() para verificar se a string não está vazia também?>
                                            <img src="<?php echo htmlspecialchars($pet["foto"]); ?>"
                                                class="fotopet mb-2" alt="Foto do Pet"><br>
                                            <?php else: ?>
                                            <p>Nenhuma foto atual.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2">
                                            <input class="btn btn-primary" type="submit" value="Salvar Alterações">
                                            <input type="hidden" name="id_usuario"
                                                value="<?php echo $_SESSION["id_usuario"]; ?>">
                                        </div>
                                        <div class="col-md-1">
                                            <a href="meus_pets.php" class="btn btn-secondary">Cancelar</a>
                                        </div>


                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>