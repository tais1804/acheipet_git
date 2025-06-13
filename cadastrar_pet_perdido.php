<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

// Inicializa variáveis para armazenar as mensagens
$mensagem_sucesso = "";
$mensagem_erro = "";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt_categorias = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $categorias_animais = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias_animais = [];
    $mensagem_erro .= "<p>Erro ao obter categorias de animais: " . $e->getMessage() . "</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $especie = $_POST["especie"];
    $raca = $_POST["raca"];
    $genero = $_POST["genero"]; // Novo campo
    $idade_valor = $_POST["idade_valor"];
    $idade_unidade = $_POST["idade_unidade"];
    $data_perdido = $_POST["data_perdido"];
    $local_perdido = $_POST["local_perdido"];
    $descricao = $_POST["descricao"];
    $telefone_contato = $_POST["telefone_contato"];
    $status_perda = isset($_POST["status_perda"]) ? $_POST["status_perda"] : 'Perdido';
    $foto = $_FILES["foto"];
    $foto_nome = $foto["name"];
    $foto_temp = $foto["tmp_name"];
    $foto_erro = $foto["error"];
    $foto_destino = "";

    if ($foto_erro == 0) {
        $extensao = pathinfo($foto_nome, PATHINFO_EXTENSION);
        $nome_unico = uniqid("pet_perdido_") . "." . $extensao;
        $upload_dir = "uploads/";
        $foto_destino = $upload_dir . $nome_unico;

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $mensagem_erro .= "<p>Erro: Não foi possível criar o diretório de uploads.</p>";
            }
        } elseif (!is_writable($upload_dir)) {
            $mensagem_erro .= "<p>Erro: O diretório de uploads não tem permissão de escrita.</p>";
        }

        if (empty($mensagem_erro)) { // Só tenta mover o arquivo se não houve erro anterior
            if (move_uploaded_file($foto_temp, $foto_destino)) {
                try {
                    $stmt = $conexao->prepare("INSERT INTO PetsPerdidos (nome, especie, raca, genero, idade_valor, idade_unidade, data_perda, local_perdido, descricao, foto, telefone_contato, status_perda, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nome, $especie, $raca, $genero, $idade_valor, $idade_unidade, $data_perdido, $local_perdido, $descricao, $foto_destino, $telefone_contato, $status_perda, $id_usuario]);
                    $mensagem_sucesso = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong>Animal perdido cadastrado com sucesso!</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
                    // Remova o redirecionamento imediato para exibir a mensagem na página atual
                    // header("Location: listar_pet_perdido.php");
                    // exit();
                } catch (PDOException $e) {
                    $mensagem_erro .= "<p>Erro ao cadastrar animal perdido: " . $e->getMessage() . "</p>";
                }
            } else {
                $mensagem_erro .= "<p>Erro ao mover o arquivo da foto.</p>";
            }
        }
    } else {
        $mensagem_erro .= "<p>Erro no upload da foto: Código de erro " . $foto_erro . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
    .card-text {
        margin: 10px !important;
    }
    </style>

    <script>
    $(document).ready(function() {
        $('#telefone').mask('(00) 00000-0000');
    });
    </script>

</head>

<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php
            include "header.php";
            ?>
            <div class="container py-5 ">
                <?php
                    if (!empty($mensagem_sucesso)) {
                        echo $mensagem_sucesso;
                    }
                    if (!empty($mensagem_erro)) {
                        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $mensagem_erro . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                    }
                ?>
                <div class="relative w-full min-h-screen row justify-content-md-center">
                    <div class="col-md-8">
                        <figure>
                            <blockquote class="blockquote">
                                <p>Sentimos muito pela perda do seu pet!</p>
                            </blockquote>
                            <figcaption class="blockquote-footer">
                                Mas vamos ajudar a encontra-lo. Cadastre o máximo de informações sobre ele neste
                                formulário e deixe o resto conosco!
                            </figcaption>
                        </figure>
                        <div class="card ">
                            <div class="card-body card-text">
                                <form method="post" action="cadastrar_pet_perdido.php" enctype="multipart/form-data">
                                    <div class="mb-30 row">
                                        <legend>Cadastrar de pet perdido</legend>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Nome do Animal</label>
                                                <input class="form-control" type="text" name="nome" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Espécie</label>
                                                <select class="form-select" name="especie" required>
                                                    <option value="">Selecione a espécie</option>
                                                    <?php foreach ($categorias_animais as $categoria): ?>
                                                    <option
                                                        value="<?php echo htmlspecialchars($categoria['nome_categoria']); ?>">
                                                        <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>


                                            <div class="col-md-3">
                                                <label class="form-label">Raça:</label>
                                                <input class="form-control" type="text" name="raca" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Idade:</label>
                                                <input class="form-control" type="number" name="idade_valor" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unidade da Idade:</label>
                                                <select class="form-select" name="idade_unidade" required>
                                                    <option value="anos">Anos</option>
                                                    <option value="meses">Meses</option>
                                                </select>
                                            </div>


                                            <div class="col-md-3">
                                                <label class="form-label">Gênero:</label>
                                                <select class="form-select" name="genero" required>
                                                    <option value="">Selecione o gênero</option>
                                                    <option value="Macho">Macho</option>
                                                    <option value="Fêmea">Fêmea</option>
                                                    <option value="Não Informado">Não Informado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Data em que se perdeu</label>
                                                <input class="form-control" type="date" name="data_perdido" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Telefone:</label>
                                                <input class="form-control" type="text" id="telefone"
                                                    name="telefone_contato" required>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Local onde se perdeu</label>
                                                <input class="form-control" type="text" name="local_perdido" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Descrição</label>
                                                <textarea class="form-control" name="descricao"></textarea>
                                            </div>
                                            <div class="col-md-10">
                                                <label class="form-label">Foto</label>
                                                <input class="form-control" type="file" name="foto" accept="image/*"
                                                    required>
                                            </div>
                                            <div class="col-2">
                                                <input class="form-control btn btn-primary" type="submit"
                                                    value="Cadastrar">
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
</script>

</html>