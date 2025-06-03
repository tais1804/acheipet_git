<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
        .h5, h5 {
            font-size: 1.25rem !important;
            font-weight: 500 !important;
        }

        .card {
            margin: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .card img {
            max-width: 100%;
            height: auto;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card-text {
            margin-bottom: 10px;
        }
        .card-img-achei {
            max-width: 100%;
            height: 188px !important;
        }
    </style>
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php
            include "header.php";
            ?>
            
            <div id="webcrumbs">
                <div class="relative col-lg-8 mx-auto min-h-screen">
                    <br/>
                    <h1 class="h1">Lista de pets para adoção</h1>
                    <br/>
                    <div id="listarpet">
                        <?php
                        include "conexao.php";

                        try {
                            $stmt = $conexao->query("SELECT * FROM Pets");
                            $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($pets) > 0) {
                                echo "<div class='row'>";
                                foreach ($pets as $pet) {
                                    echo "<div class='col'>";
                                    echo "<div class='card'>";
                                    $caminho_foto = $pet["foto"];
                                        if (file_exists($caminho_foto)) {
                                            echo "<img class='card-img-top card-img-achei' src='" . $caminho_foto . "' alt='Foto do Pet'>";
                                        } else {
                                            echo "<p>Arquivo de imagem não encontrado: " . $caminho_foto . "</p>"; 
                                        }
                                    echo "<div class='card-body'>";
                                    echo "<h5 class='card-title'>" . $pet["nome"] . "</h5>";
                                    // MODIFICAÇÃO AQUI: Combinando idade_valor e idade_unidade
                                    echo "<div><b>Idade:</b> " . htmlspecialchars($pet["idade_valor"]) . " " . htmlspecialchars($pet["idade_unidade"]) . "</div>";
                                    echo "<div><b>Espécie:</b> " . htmlspecialchars($pet["especie"]) . "</div>";
                                    echo "<div><b>Raça:</b> " . htmlspecialchars($pet["raca"]) . "</div>";
                                    echo "<div><b>Porte:</b> " . htmlspecialchars($pet["porte"]) . "</div>";
                                    echo "<div><b>Genero:</b> " . htmlspecialchars($pet["genero"]) . "</div>";
                                    echo "<div><b>Tel.:</b> " . htmlspecialchars($pet["numero_contato"]) . "</div>";
                                    //echo "<button type='button' class='btn btn-primary me-2'>Adotar</button>";
                                    echo "</div></div></div>";
                                }
                                echo "</div>"; // Fecha a div.row
                            } else {
                                echo "<p>Nenhum pet encontrado.</p>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Erro ao listar pets: " . $e->getMessage() . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>