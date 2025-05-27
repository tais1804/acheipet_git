<?php
include "conexao.php";

include "verificar_login.php";

$id_pet = $_GET["id"];

try {
    $stmt = $conexao->prepare("DELETE FROM Pets WHERE id_pet = ?");
    $stmt->execute([$id_pet]);
    echo "<p>Pet excluído com sucesso!</p>";
} catch (PDOException $e) {
    echo "<p>Erro ao excluir pet: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Excluir Pet</title>
</head>
<body>
    <h1>Excluir Pet</h1>
    <p><a href="listar_pets.php">Voltar para a lista de pets</a></p>
</body>
</html>