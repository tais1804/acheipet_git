<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt_categorias = $conexao->query("SELECT id_categoria_animal, nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $categorias_animais = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias_animais = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $especie = $_POST["especie"];
    $raca = $_POST["raca"];
    $idade_valor = $_POST["idade_valor"];
    $idade_unidade = $_POST["idade_unidade"];
    $genero = $_POST["genero"];
    $porte = $_POST["porte"]; // Pegando o valor do select
    $numero_contato = $_POST["telefone_contato"];
    $temperamento = $_POST["temperamento"];
    $vacinas = $_POST["vacinas"];
    $historico_saude = $_POST["historico_saude"];
    $id_usuario = $_SESSION['id_usuario'];

    $status = "Adoção"; 

    $foto = $_FILES["foto"];
    $foto_nome = $foto["name"];
    $foto_temp = $foto["tmp_name"];
    $foto_erro = $foto["error"];
    $foto_destino = "";

    if ($foto_erro == 0) {
        $extensao = pathinfo($foto_nome, PATHINFO_EXTENSION);
        $nome_unico = uniqid("pet_") . "." . $extensao;
        $upload_dir = "uploads/"; 
        $foto_destino = $upload_dir . $nome_unico;

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) { 
                echo "<p>Erro ao criar o diretório de uploads.</p>";
                exit; 
            }
        } elseif (!is_writable($upload_dir)) {
            echo "<p>Diretório de uploads sem permissão de escrita.</p>";
            exit; 
        }

        if (move_uploaded_file($foto_temp, $foto_destino)) {
            try {
                $stmt = $conexao->prepare("INSERT INTO Pets (nome, especie, raca, idade_valor, idade_unidade, genero, porte, temperamento, vacinas, numero_contato, historico_saude, foto, status, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([$nome, $especie, $raca, $idade_valor, $idade_unidade, $genero, $porte, $temperamento, $vacinas, $numero_contato, $historico_saude, $foto_destino, $status, $id_usuario]);
                echo "<p>Pet cadastrado com sucesso!</p>";
                header("Location: meus_pets.php");
                exit();
            } catch (PDOException $e) {
                echo "<p>Erro ao cadastrar pet: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>Erro ao mover o arquivo da foto.</p>";
        }
    } else {
        echo "<p>Erro ao fazer upload da foto.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        img {
            max-width: 100px;
            height: auto;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="../css/estilo-achei-pet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php
            include "header.php";
            ?>
            
            <div class="container">
                <div id="webcrumbs">
                    <div class="relative w-full min-h-screen row justify-content-md-center">
                        <div class="col-md-10">
                            <h1 class="h4"><br>Cadastrar Pet para doação</h1>

                            <form method="post" action="cadastrar_pet.php" enctype="multipart/form-data">
                                <div class="g-2 row">
                                    <div class="col-md-8">
                                        <label class="form-label">Nome:</label><br>
                                        <input class="form-control" type="text" name="nome" required> 
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Espécie:</label><br>
                                        <select class="form-select" name="especie" required>
                                            <option value="">Selecione a espécie</option>
                                            <?php foreach ($categorias_animais as $categoria): ?>
                                                <option value="<?php echo htmlspecialchars($categoria['nome_categoria']); ?>">
                                                    <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <label class="form-label">Idade:</label><br>
                                            <input class="form-control" type="number" name="idade_valor" min="0" required>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Anos:</label><br>
                                        <select class="form-select" name="idade_unidade" required>
                                            <option value="anos">Anos</option>
                                            <option value="meses">Meses</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Raça:</label><br>
                                        <input class="form-control" type="text" name="raca" required>
                                    </div> 
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Tel para Contato:</label><br>
                                        <input class="form-control" type="text" name="telefone_contato" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Porte:</label><br>
                                        <select class="form-select" name="porte" required>
                                            <option value="">Selecione o porte</option>
                                            <option value="Pequeno">Pequeno</option>
                                            <option value="Médio">Médio</option>
                                            <option value="Grande">Grande</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Temperamento:</label><br>
                                        <textarea class="form-control" name="temperamento"></textarea> 
                                    </div>

                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Vacinas:</label><br>
                                        <textarea class="form-control" name="vacinas"></textarea> 
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Histórico de Saúde:</label><br>
                                        <textarea class="form-control" name="historico_saude"></textarea> 
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Foto:</label><br>
                                        <input class="form-control" type="file" name="foto" accept="image/*" required>
                                    
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <input class="btn btn-primary" type="submit" value="Cadastrar">
                                    </div>
                                </div>
                                
                                <br/><br/>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</html>