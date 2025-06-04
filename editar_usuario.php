<?php
session_start();
include "conexao.php";
include "dados_usuario.php";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $cpf = $_POST["cpf"];
    $endereco = $_POST["endereco"];

    // Atualiza a foto se uma nova for enviada
    if ($_FILES["foto"]["name"]) {
        $foto_nome = basename($_FILES["foto"]["name"]);
        $foto_caminho = "uploads/" . $foto_nome;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $foto_caminho);
    } else {
        $foto_caminho = $usuario["foto"]; // mantém a foto atual
    }

    $id_usuario = $_SESSION["id_usuario"];

    // Atualiza os dados no banco
$sql = "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, cpf = :cpf, endereco = :endereco, foto = :foto WHERE id_usuario = :id";
$stmt = $conexao->prepare($sql);
$sucesso = $stmt->execute([
    ':nome' => $nome,
    ':email' => $email,
    ':telefone' => $telefone,
    ':cpf' => $cpf,
    ':endereco' => $endereco,
    ':foto' => $foto_caminho,
    ':id' => $_SESSION["id_usuario"]
]);

if ($sucesso) {
    header("Location: perfil_usuario.php");
    exit();
} else {
    echo "Erro ao atualizar os dados.";
}

}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Perfil - Achei Pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-5">

        <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm"
            style="max-width: 600px; margin: auto;">
            <h2 class="h3 text-center">Editar Perfil</h2>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome"
                    value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone:</label>
                <input type="text" class="form-control" id="telefone" name="telefone"
                    value="<?= htmlspecialchars($usuario['telefone']) ?>">
            </div>

            <div class="mb-3">
                <label for="cpf" class="form-label">CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf"
                    value="<?= htmlspecialchars($usuario['cpf']) ?>">
            </div>

            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço:</label>
                <input type="text" class="form-control" id="endereco" name="endereco"
                    value="<?= htmlspecialchars($usuario['endereco']) ?>">
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto de Perfil:</label>
                <input type="file" class="form-control" id="foto" name="foto">
                <?php if ($usuario['foto']): ?>
                <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto atual" class="img-thumbnail mt-2"
                    style="width: 100px;">
                <?php endif; ?>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="perfil_usuario.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
</body>

</html>