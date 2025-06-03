<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

// Inicializa variáveis de filtro
$filtro_nome = '';
$filtro_especie = '';
$filtro_raca = '';
$filtro_genero = '';
$filtro_porte = '';
$filtro_idade_valor = '';
$filtro_idade_unidade = '';
// REMOVIDO: $filtro_local = ''; // Para cidade/estado do pet

// Verifica se o formulário de filtro foi enviado
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['buscar'])) {
    $filtro_nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
    $filtro_especie = isset($_GET['especie']) ? trim($_GET['especie']) : '';
    $filtro_raca = isset($_GET['raca']) ? trim($_GET['raca']) : '';
    $filtro_genero = isset($_GET['genero']) ? trim($_GET['genero']) : '';
    $filtro_porte = isset($_GET['porte']) ? trim($_GET['porte']) : '';
    $filtro_idade_valor = isset($_GET['idade_valor']) ? trim($_GET['idade_valor']) : '';
    $filtro_idade_unidade = isset($_GET['idade_unidade']) ? trim($_GET['idade_unidade']) : '';
    // REMOVIDO: $filtro_local = isset($_GET['local_pet']) ? trim($_GET['local_pet']) : '';
}

try {
    // Monta a consulta SQL base
    $sql = "SELECT * FROM Pets WHERE 1=1"; // 1=1 para facilitar a adição de condições

    $params = [];

    // Adiciona condições de filtro se os valores forem fornecidos
    if (!empty($filtro_nome)) {
        $sql .= " AND nome LIKE ?";
        $params[] = '%' . $filtro_nome . '%';
    }
    if (!empty($filtro_especie)) {
        $sql .= " AND especie = ?";
        $params[] = $filtro_especie;
    }
    if (!empty($filtro_raca)) {
        $sql .= " AND raca LIKE ?";
        $params[] = '%' . $filtro_raca . '%';
    }
    if (!empty($filtro_genero)) {
        $sql .= " AND genero = ?";
        $params[] = $filtro_genero;
    }
    if (!empty($filtro_porte)) {
        $sql .= " AND porte = ?";
        $params[] = $filtro_porte;
    }
    // Filtro de idade: Se ambos, valor e unidade, forem preenchidos
    if (!empty($filtro_idade_valor) && !empty($filtro_idade_unidade)) {
        $sql .= " AND idade_valor = ? AND idade_unidade = ?";
        $params[] = $filtro_idade_valor;
        $params[] = $filtro_idade_unidade;
    } elseif (!empty($filtro_idade_valor)) { // Se apenas o valor for preenchido
        $sql .= " AND idade_valor = ?";
        $params[] = $filtro_idade_valor;
    } elseif (!empty($filtro_idade_unidade)) { // Se apenas a unidade for preenchida
        $sql .= " AND idade_unidade = ?";
        $params[] = $filtro_idade_unidade;
    }

    // REMOVIDO: Condição para filtro de localização
    // if (!empty($filtro_local)) {
    //     $sql .= " AND local_pet LIKE ?";
    //     $params[] = '%' . $filtro_local . '%';
    // }

    // Ordena os resultados (assumindo que 'id_pet' é a coluna de ID)
    $sql .= " ORDER BY id_pet DESC"; 

    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Erro ao listar pets: " . $e->getMessage() . "</p>";
    $pets = []; // Garante que $pets seja um array vazio em caso de erro
}

// Para popular os selects de filtro
try {
    $stmt_especies = $conexao->query("SELECT DISTINCT especie FROM Pets ORDER BY especie");
    $especies_disponiveis = $stmt_especies->fetchAll(PDO::FETCH_COLUMN);

    $stmt_generos = $conexao->query("SELECT DISTINCT genero FROM Pets WHERE genero IS NOT NULL AND genero != '' ORDER BY genero");
    $generos_disponiveis = $stmt_generos->fetchAll(PDO::FETCH_COLUMN);

    $stmt_portes = $conexao->query("SELECT DISTINCT porte FROM Pets WHERE porte IS NOT NULL AND porte != '' ORDER BY porte");
    $portes_disponiveis = $stmt_portes->fetchAll(PDO::FETCH_COLUMN);

    $unidades_idade = ['dias', 'meses', 'anos']; // Opções fixas para unidade de idade
} catch (PDOException $e) {
    $especies_disponiveis = [];
    $generos_disponiveis = ['Macho', 'Fêmea', 'Não Informado']; // Opções padrão em caso de erro ou vazio
    $portes_disponiveis = ['Pequeno', 'Médio', 'Grande']; // Opções padrão
    $unidades_idade = ['dias', 'meses', 'anos'];
}
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
            height: 267px !important;
            object-fit: cover; /* Para garantir que a imagem preencha o espaço sem distorcer */
        }
    </style>
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php include "header.php"; ?>
            
            <div id="webcrumbs">
                <div class="relative col-lg-10 mx-auto min-h-screen"> 
                    <br/>
                    <h1 class="h1">Lista de pets para adoção</h1>
                    <br/>

                    <div class="card mb-4">
                        <div class="card-header">
                            Filtros de Pesquisa
                        </div>
                        <div class="card-body">
                            <form method="GET" action="listar_pet.php">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="nome" class="form-label">Nome do Pet:</label>
                                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Nome do pet">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="especie" class="form-label">Espécie:</label>
                                        <select class="form-select" id="especie" name="especie">
                                            <option value="">Todas as Espécies</option>
                                            <?php foreach ($especies_disponiveis as $especie): ?>
                                                <option value="<?php echo htmlspecialchars($especie); ?>" <?php echo ($filtro_especie == $especie) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($especie); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="raca" class="form-label">Raça:</label>
                                        <input type="text" class="form-control" id="raca" name="raca" value="<?php echo htmlspecialchars($filtro_raca); ?>" placeholder="Raça do pet">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="genero" class="form-label">Gênero:</label>
                                        <select class="form-select" id="genero" name="genero">
                                            <option value="">Todos</option>
                                            <?php foreach ($generos_disponiveis as $genero): ?>
                                                <option value="<?php echo htmlspecialchars($genero); ?>" <?php echo ($filtro_genero == $genero) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($genero); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="porte" class="form-label">Porte:</label>
                                        <select class="form-select" id="porte" name="porte">
                                            <option value="">Todos os Portes</option>
                                            <?php foreach ($portes_disponiveis as $porte): ?>
                                                <option value="<?php echo htmlspecialchars($porte); ?>" <?php echo ($filtro_porte == $porte) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($porte); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="idade_valor" class="form-label">Idade (valor):</label>
                                        <input type="number" class="form-control" id="idade_valor" name="idade_valor" value="<?php echo htmlspecialchars($filtro_idade_valor); ?>" placeholder="Ex: 2">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="idade_unidade" class="form-label">Idade (unidade):</label>
                                        <select class="form-select" id="idade_unidade" name="idade_unidade">
                                            <option value="">Unidade</option>
                                            <?php foreach ($unidades_idade as $unidade): ?>
                                                <option value="<?php echo htmlspecialchars($unidade); ?>" <?php echo ($filtro_idade_unidade == $unidade) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($unidade); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
                                        <a href="listar_pet.php" class="btn btn-secondary">Limpar Filtros</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="listarpet">
                        <?php if (count($pets) > 0): ?>
                            <div class="row justify-content-md-center">
                                <?php foreach ($pets as $pet): ?>
                                    <div class="col-md-4 col-sm-6 mb-4"> 
                                        <div class="card h-100"> 
                                            <?php
                                            $caminho_foto = $pet["foto"];
                                            if (file_exists($caminho_foto) && !empty($caminho_foto)) {
                                                echo "<img class='card-img-top card-img-achei object-fit-cover border rounded' src='" . htmlspecialchars($caminho_foto) . "' alt='Foto do Pet'>";
                                            } else {
                                                echo "<img class='card-img-top card-img-achei object-fit-cover border rounded' src='images/placeholder-pet.png' alt='Sem foto'>"; // Placeholder
                                            }
                                            ?>
                                            <div class="card-body d-flex flex-column"> 
                                                <h5 class="card-title"><?php echo htmlspecialchars($pet["nome"]); ?></h5>
                                                <div><b>Idade:</b> <?php echo htmlspecialchars($pet["idade_valor"]) . " " . htmlspecialchars($pet["idade_unidade"]); ?></div>
                                                <div><b>Espécie:</b> <?php echo htmlspecialchars($pet["especie"]); ?></div>
                                                <div><b>Raça:</b> <?php echo htmlspecialchars($pet["raca"]); ?></div>
                                                <div><b>Porte:</b> <?php echo htmlspecialchars($pet["porte"]); ?></div>
                                                <div><b>Gênero:</b> <?php echo htmlspecialchars($pet["genero"]); ?></div>
                                                <div><b>Tel.:</b> <?php echo htmlspecialchars($pet["numero_contato"]); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="alert alert-info">Nenhum pet para adoção encontrado com os filtros aplicados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>