<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

$id_pet = $_GET["id"];

try {
    $stmt = $conexao->prepare("SELECT * FROM Pets WHERE id_pet = ?");
    $stmt->execute([$id_pet]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Erro ao obter dados do pet: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $especie = $_POST["especie"];
    $genero = $_POST["genero"];
    $raca = $_POST["raca"];
    $idade = $_POST["idade"];
    $unidade = $_POST["idade_unidade"];
    $porte = $_POST["porte"];
    $temperamento = $_POST["temperamento"];
    $vacinas = $_POST["vacinas"];
    $historico_saude = $_POST["historico_saude"];
    // Verifica se o usuário enviou um novo arquivo
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $nome_arquivo = basename($_FILES['foto']['name']);
    $caminho_destino = "uploads/" . $nome_arquivo;
    
    // Move o arquivo para a pasta uploads
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
        $foto = $caminho_destino;
    } else {
        echo "<p>Erro ao mover o arquivo de imagem.</p>";
        $foto = $pet["foto"]; // mantém a antiga
    }
} else {
    $foto = $pet["foto"]; // mantém a antiga
}

    $status = $_POST["status"];
    $id_usuario = $_POST["id_usuario"];

    try {
        $stmt = $conexao->prepare("UPDATE Pets SET nome = ?, especie = ?, genero = ?, raca = ?, idade_valor = ?, idade_unidade = ?, porte = ?, temperamento = ?, vacinas = ?, historico_saude = ?, foto = ?, status = ?, id_usuario = ? WHERE id_pet = ?");
        $stmt->execute([$nome, $especie, $genero, $raca, $idade, $unidade, $porte, $temperamento, $vacinas, $historico_saude, $foto, $status, $id_usuario, $id_pet]);
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
  <strong>Pet atualizado com sucesso!<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    } catch (PDOException $e) {
        echo "<p>Erro ao atualizar pet: " . $e->getMessage() . "</p>";
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
                <h1>Editar Pet</h1>
                <form class="" method="post" enctype="multipart/form-data"
                    action="editar_pet.php?id=<?php echo $id_pet; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nome:</label class="form-label">
                        <input class="form-control" type="text" name="nome" value="<?php echo $pet["nome"]; ?>"><br>
                        <label class="form-label">Espécie:</label class="form-label">
                        <input class="form-control" type="text" name="especie"
                            value="<?php echo $pet["especie"]; ?>"><br>
                        <label class="form-label">Raça:</label class="form-label">
                        <input class="form-control" type="text" name="raca" value="<?php echo $pet["raca"]; ?>"><br>
                        <label class="form-label">Idade:</label class="form-label">
                        <input class="form-control" type="number" name="idade"
                            value="<?php echo $pet["idade_valor"]; ?>"><br>
                        <br>

                        <label class="form-label">Idade:</label>
                        <select class="form-select" name="idade_unidade" required>
                            <option value="anos" <?php if ($pet["idade_unidade"] == "anos") echo "selected"; ?>>Anos
                            </option>
                            <option value="meses" <?php if ($pet["idade_unidade"] == "meses") echo "selected"; ?>>Meses
                            </option>
                        </select><br>

                        <label class="form-label">Gênero:</label>
                        <select class="form-select" name="genero" required>
                            <option value="">Selecione o gênero</option>
                            <option value="Macho">Macho</option>
                            <option value="Fêmea">Fêmea</option>
                            <option value="Não Informado">Não Informado</option>
                        </select><br>

                        <label class="form-label">Porte:</label class="form-label">
                        <input class="form-control" type="text" name="porte" value="<?php echo $pet["porte"]; ?>"><br>
                        <label class="form-label">Temperamento:</label class="form-label">
                        <textarea class="form-control"
                            name="temperamento"><?php echo $pet["temperamento"]; ?></textarea><br>
                        <label class="form-label">Vacinas:</label class="form-label">
                        <textarea class="form-control" name="vacinas"><?php echo $pet["vacinas"]; ?></textarea><br>
                        <label class="form-label">Histórico de Saúde:</label class="form-label">
                        <textarea class="form-control"
                            name="historico_saude"><?php echo $pet["historico_saude"]; ?></textarea><br>
                        <label class="form-label">Foto:</label class="form-label">
                        <input class="form-control" type="file" name="foto" value="<?php echo $pet["foto"]; ?>"><br>
                        <label class="form-label">Status:</label class="form-label"><br>
                        <select class="form-select" name="status">
                            <option value="perdido" <?php if ($pet["status"] == "perdido") echo "selected"; ?>>Perdido
                            </option>
                            <option value="adocao" <?php if ($pet["status"] == "adocao") echo "selected"; ?>>Adoção
                            </option>
                            <option value="encontrado" <?php if ($pet["status"] == "encontrado") echo "selected"; ?>>
                                Encontrado</option>
                        </select><br><br>
                        <input class="btn btn-primary" type="submit" value="Salvar Alterações">
                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION["id_usuario"]; ?>">

                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>