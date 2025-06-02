<?php
include "conexao.php";
include "dados_usuario.php"; 
include "verificar_login.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $especie = $_POST["especie"];
    $raca = $_POST["raca"];
    $data_perdido = $_POST["data_perdido"];
    $local_perdido = $_POST["local_perdido"];
    $descricao = $_POST["descricao"];
    $telefone_contato = $_POST["telefone_contato"]; //isset($_POST["telefone_contato"]) ? $_POST["telefone_contato"] : ''; 
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
                echo "<p>Erro: Não foi possível criar o diretório de uploads.</p>";
                exit;
            }
        } elseif (!is_writable($upload_dir)) {
            echo "<p>Erro: O diretório de uploads não tem permissão de escrita.</p>";
            exit;
        }

        if (move_uploaded_file($foto_temp, $foto_destino)) {
            try {
                $stmt = $conexao->prepare("INSERT INTO PetsPerdidos (nome, especie, raca, data_perda, local_perdido, descricao, foto, telefone_contato, status_perda, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $especie, $raca, $data_perdido, $local_perdido, $descricao, $foto_destino, $telefone_contato, $status_perda, $id_usuario]);
                echo "<p>Animal perdido cadastrado com sucesso!</p>";
                header("Location: listar_pet_perdido.php");
                exit();
            } catch (PDOException $e) {
                echo "<p>Erro ao cadastrar animal perdido: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>Erro ao mover o arquivo da foto.</p>";
        }
    } else {
        echo "<p>Erro no upload da foto: Código de erro " . $foto_erro . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php
            include "header.php";
            ?> 
            <div class="container py-5 ">
            <div class="row">
                <div class="col-md-12">
                    <!-- <h2 class="h2">Cadastrar Animal Perdido</h2> -->
                     <figure>
  <blockquote class="blockquote">
    <p>Sentimos muito pela perda do seu pet!</p>
  </blockquote>
  <figcaption class="blockquote-footer">
    Mas vamos ajudar a encontra-lo. Cadastre o máximo de informações sobre ele neste formulário e deixe o resto conosco!
  </figcaption>
</figure>
                    <form method="post" action="cadastrar_pet_perdido.php" enctype="multipart/form-data"> <div class="mb-30 row">
                        <legend>Cadastrar de pet perdido</legend>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Animal</label><br>
                                    <input class="form-control" type="text" name="nome" required><br><br>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Espécie</label><br>
                                    <input class="form-control" type="text" name="especie" required><br><br>
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Raça:</label><br>
                                <input class="form-control" type="text" name="raca" required><br><br>
                            </div>
                            <div class="col-md-6">    
                                <label class="form-label">Data em que se perdeu</label><br>
                                <input class="form-control" type="date" name="data_perdido" required><br><br>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">      
                                <label class="form-label">Local onde se perdeu</label><br>
                                <input class="form-control" type="text" name="local_perdido" required><br><br>
</div>
                            <div class="col-md-6"> 
                                <label class="form-label">Telefone:</label><br>
                                <input class="form-control" type="text" name="telefone_contato" required><br><br>
</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"> 
                                <label class="form-label">Descrição</label><br>
                                <textarea class="form-control" name="descricao"></textarea><br><br>
</div>
                            <div class="col-md-6">
                                <label class="form-label">Foto</label><br>
                                <input class="form-control" type="file" name="foto" accept="image/*" required><br><br> 
</div>
                        </div>

                            <div class="col-md-auto d-grid gap-2 justify-content-md-end">
                                    <input class="form-control btn btn-primary" type="submit" value="Cadastrar">
                            </div>

                            

                        </div>
</div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</html>
