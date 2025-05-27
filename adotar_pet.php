<?php
include "conexao.php";

include "verificar_login.php";

$id_pet = $_GET["id"];


try {
    $stmt = $conexao->prepare("SELECT * FROM Pets WHERE id_pet = ?");
    $stmt->execute([$id_pet]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

   
    if (!$pet) {
        echo "<p>Pet não encontrado.</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p>Erro ao obter dados do pet: " . $e->getMessage() . "</p>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_adotante = $_POST["id_adotante"];
    $data_adocao = date("Y-m-d"); 
    $status = "em_andamento";

    try {
        $stmt = $conexao->prepare("INSERT INTO Adoções (id_pet, id_adotante, data_adocao, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_pet, $id_adotante, $data_adocao, $status]);

        
        $stmt = $conexao->prepare("UPDATE Pets SET status = 'adotado' WHERE id_pet = ?");
        $stmt->execute([$id_pet]);

        echo "<p>Adoção realizada com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p>Erro ao realizar adoção: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adotar Pet</title>
</head>
<body>
    <h1>Adotar Pet</h1>
    <a href="index.php">Home</a>
    <p>Nome: <?php echo $pet["nome"]; ?></p>
    <p>Espécie: <?php echo $pet["especie"]; ?></p>
    <p>Raça: <?php echo $pet["raca"]; ?></p>
    <form method="post" action="adotar_pet.php?id=<?php echo $id_pet; ?>">
        <label>ID do Adotante:</label><br>
        <input type="number" name="id_adotante"><br><br>
        <input type="submit" value="Confirmar Adoção">
    </form>
</body>
</html>