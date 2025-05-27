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
    $raca = $_POST["raca"];
    $idade = $_POST["idade"];
    $porte = $_POST["porte"];
    $temperamento = $_POST["temperamento"];
    $vacinas = $_POST["vacinas"];
    $historico_saude = $_POST["historico_saude"];
    $foto = $_POST["foto"];
    $status = $_POST["status"];
    $id_usuario = $_POST["id_usuario"];

    try {
        $stmt = $conexao->prepare("UPDATE Pets SET nome = ?, especie = ?, raca = ?, idade = ?, porte = ?, temperamento = ?, vacinas = ?, historico_saude = ?, foto = ?, status = ?, id_usuario = ? WHERE id_pet = ?");
        $stmt->execute([$nome, $especie, $raca, $idade, $porte, $temperamento, $vacinas, $historico_saude, $foto, $status, $id_usuario, $id_pet]);
        echo "<p>Pet atualizado com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p>Erro ao atualizar pet: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Pet</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Open+Sans&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
        h1, h2 {
            font-family: 'Lato', sans-serif;
        }
        img.fotopet {
            max-width: 100px;
            height: auto;
            object-fit: cover;
        }
    </style>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main>
        <div class="container">
    <div class="container mx-auto p-6">
    <h1>Editar Pet</h1>
    <form class="" method="post" action="editar_pet.php?id=<?php echo $id_pet; ?>">
        <div class="mb-3">
        <label class="form-label">Nome:</label class="form-label">
        <input class="form-control" type="text" name="nome" value="<?php echo $pet["nome"]; ?>"><br>
        <label class="form-label">Espécie:</label class="form-label">
        <input class="form-control" type="text" name="especie" value="<?php echo $pet["especie"]; ?>"><br>
        <label class="form-label">Raça:</label class="form-label">
        <input class="form-control" type="text" name="raca" value="<?php echo $pet["raca"]; ?>"><br>
        <label class="form-label">Idade:</label class="form-label">
        <input class="form-control" type="number" name="idade" value="<?php echo $pet["idade"]; ?>"><br>
        <label class="form-label">Porte:</label class="form-label">
        <input class="form-control" type="text" name="porte" value="<?php echo $pet["porte"]; ?>"><br>
        <label class="form-label">Temperamento:</label class="form-label">
        <textarea  class="form-control" name="temperamento"><?php echo $pet["temperamento"]; ?></textarea><br>
        <label class="form-label">Vacinas:</label class="form-label">
        <textarea  class="form-control" name="vacinas"><?php echo $pet["vacinas"]; ?></textarea><br>
        <label class="form-label">Histórico de Saúde:</label class="form-label">
        <textarea  class="form-control" name="historico_saude"><?php echo $pet["historico_saude"]; ?></textarea><br>
        <label class="form-label">Foto:</label class="form-label">
        <input class="form-control" type="text" name="foto" value="<?php echo $pet["foto"]; ?>"><br>
        <label class="form-label">Status:</label class="form-label"><br>
        <select class="form-select"  name="status">
            <option value="perdido" <?php if ($pet["status"] == "perdido") echo "selected"; ?>>Perdido</option>
            <option value="adocao" <?php if ($pet["status"] == "adocao") echo "selected"; ?>>Adoção</option>
            <option value="encontrado" <?php if ($pet["status"] == "encontrado") echo "selected"; ?>>Encontrado</option>
        </select><br><br>
        <input class="btn btn-primary" type="submit" value="Salvar Alterações">
    </div>
    </form>
    </div>
    </div>
    </main>
</body>
</html>