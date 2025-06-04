<?php
session_start();
include "conexao.php";
include "dados_usuario.php";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    a {
        color: rgb(235 89 91) !important;
        text-decoration: underline;
    }

    .btn-primary {
        color: #fff !important;
    }
    </style>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-center">
            <div class="card p-4 shadow-sm " style="width: 50%;">
                <div class="row">
                    <div class="col">
                        <img class="img-thumbnail" src="<?php echo htmlspecialchars($usuario["foto"]); ?>"
                            alt="Foto do Usuário">
                    </div>
                    <div class="col">

                        <h1 class="h6">Perfil do Usuário</h1>
                        <p class="h3"><strong><?php echo htmlspecialchars($usuario["nome"]); ?></strong></p>
                        <p><small><a href="mailto:<?php echo htmlspecialchars($usuario['email']); ?>"></p>
                        <p><?php echo htmlspecialchars($usuario["email"]); ?></a></small></p>
                        <p><small><?php echo htmlspecialchars($usuario["endereco"]); ?></small></p>
                        <p><small><b>Tel.: </b><?php echo htmlspecialchars($usuario["telefone"]); ?></p>
                        <p><b>CPF:</b> <?php echo htmlspecialchars($usuario["cpf"]); ?></samll>
                        </p>
                        <p><span
                                class="badge bg-success"><?php echo htmlspecialchars($usuario["tipo_usuario"]); ?></span>
                        </p>
                        <br><a href="editar_usuario.php" class="btn btn-primary">Editar Perfil</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="mt-4 d-flex">
            <form method="post" action="perfil_usuario.php" class="me-2">
                <button type="submit" name="deslogar" class="btn btn-danger">Deslogar</button>
            </form>
            <a href="meus_pets.php" class="btn btn-info me-2">Ver Meus Pets</a>
            <a href="loja_virtual.php" class="btn btn-success">Ir para a Loja</a>
        </div>-->

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>