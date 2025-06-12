<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";


$filtro_nome = '';
$filtro_especie = '';
$filtro_raca = '';
$filtro_genero = '';
$filtro_local = '';
$filtro_telefone_contato = '';
$filtro_idade_valor = '';
$filtro_idade_unidade = '';

// Verifica se o formulário de filtro foi enviado
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['buscar'])) {
    $filtro_nome = isset($_GET['nome']) ? trim($_GET['nome']) : $filtro_nome;
    $filtro_especie = isset($_GET['especie']) ? trim($_GET['especie']) : $filtro_especie;
    $filtro_raca = isset($_GET['raca']) ? trim($_GET['raca']) : $filtro_raca;
    $filtro_genero = isset($_GET['genero']) ? trim($_GET['genero']) : $filtro_genero;
    $filtro_local = isset($_GET['local_perdido']) ? trim($_GET['local_perdido']) : $filtro_local;
    $filtro_telefone_contato = isset($_GET['telefone_contato']) ? trim($_GET['telefone_contato']) : $filtro_telefone_contato;
    $filtro_idade_valor = isset($_GET['idade_valor']) ? trim($_GET['idade_valor']) : $filtro_idade_valor;
    $filtro_idade_unidade = isset($_GET['idade_unidade']) ? trim($_GET['idade_unidade']) : $filtro_idade_unidade;
}

try {
    $sql = "SELECT pp.*, u.nome AS nome_usuario FROM PetsPerdidos pp JOIN usuarios u ON pp.id_usuario = u.id_usuario WHERE 1=1";
    
    $params = [];

    if (!empty($filtro_nome)) {
        $sql .= " AND pp.nome LIKE ?"; // AQUI ESTÁ A CORREÇÃO
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
    if (!empty($filtro_local)) {
        $sql .= " AND local_perdido LIKE ?";
        $params[] = '%' . $filtro_local . '%';
    }
    if (!empty($filtro_telefone_contato)) {
        $sql .= " AND telefone_contato LIKE ?";
        $params[] = '%' . $filtro_telefone_contato . '%';
    }

    if (!empty($filtro_idade_valor) && !empty($filtro_idade_unidade)) {
        $sql .= " AND idade_valor = ? AND idade_unidade = ?";
        $params[] = $filtro_idade_valor;
        $params[] = $filtro_idade_unidade;
    } elseif (!empty($filtro_idade_valor)) {
        $sql .= " AND idade_valor = ?";
        $params[] = $filtro_idade_valor;
    } elseif (!empty($filtro_idade_unidade)) {
        $sql .= " AND idade_unidade = ?";
        $params[] = $filtro_idade_unidade;
    }

    $sql .= " ORDER BY data_perda DESC";

    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $petsPerdidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Erro ao listar pets perdidos: " . $e->getMessage() . "</p>";
    $petsPerdidos = [];
}

try {
    $stmt_especies = $conexao->query("SELECT nome_categoria FROM categoria_animais ORDER BY nome_categoria");
    $especies_disponiveis = $stmt_especies->fetchAll(PDO::FETCH_COLUMN);

    if (empty($especies_disponiveis)) {
        $especies_disponiveis = ['Cachorro', 'Gato', 'Ave', 'Roedor', 'Peixe', 'Repteis', 'Outros'];
    }

    $stmt_generos = $conexao->query("SELECT DISTINCT genero FROM PetsPerdidos WHERE genero IS NOT NULL AND genero != '' ORDER BY genero");
    $generos_disponiveis = $stmt_generos->fetchAll(PDO::FETCH_COLUMN);
    if (empty($generos_disponiveis)) {
        $generos_disponiveis = ['Macho', 'Fêmea', 'Não Informado'];
    }

    // AQUI ESTÁ A MUDANÇA: Removendo 'dias'
    $unidades_idade = ['meses', 'anos'];

} catch (PDOException $e) {
    $especies_disponiveis = ['Cachorro', 'Gato', 'Ave', 'Roedor', 'Peixe', 'Repteis', 'Outros'];
    $generos_disponiveis = ['Macho', 'Fêmea', 'Não Informado'];
    $unidades_idade = ['meses', 'anos']; // Também removido aqui no fallback
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <style>
    @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
    @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);

    img.fotopet {
        max-width: 100px !important;
        height: auto;
    }

    .card {
        margin: 10px;
        padding: 10px;
        border: 0px solid #ddd;
        border-radius: 50px;
        box-shadow: 0px 7px 20px #00000021;
    }

    .card img {
        max-width: 100%;
        height: auto;
        border-radius: 36px !important;
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

    label.form-label {
        margin-bottom: 0;
        font-size: 0.8rem;
        color: #787878;
    }

    .title {
        margin-left: 11px;
    }

    .card-img-achei {
        max-width: 100%;
        height: 267px !important;
        object-fit: cover;
        /* Para garantir que a imagem preencha o espaço sem distorcer */
    }

    .card-pet-perdido {
        color: #7e7e7e;
    }

    .card-pet-perdido div {
        margin: 4px 0 4px 0;
    }
    </style>


</head>

<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php include "header.php"; ?>

            <div id="webcrumbs">
                <div class="relative col-lg-10 mx-auto min-h-screen">
                    <br />

                    <div class="mb-4">
                        <div>
                            <form method="GET" action="listar_pet_perdido.php">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3 form-group">
                                        <label for="nome" class="form-label">Nome do Pet:</label>
                                        <input type="text" class="form-control" id="nome" name="nome"
                                            value="<?php echo htmlspecialchars($filtro_nome); ?>"
                                            placeholder="Nome do pet">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="especie" class="form-label">Espécie:</label>
                                        <select class="form-select" id="especie" name="especie">
                                            <option value="">Todas as Espécies</option>
                                            <?php foreach ($especies_disponiveis as $especie): ?>
                                            <option value="<?php echo htmlspecialchars($especie); ?>"
                                                <?php echo ($filtro_especie == $especie) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($especie); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="raca" class="form-label">Raça:</label>
                                        <input type="text" class="form-control" id="raca" name="raca"
                                            value="<?php echo htmlspecialchars($filtro_raca); ?>"
                                            placeholder="Raça do pet">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="genero" class="form-label">Gênero:</label>
                                        <select class="form-select" id="genero" name="genero">
                                            <option value="">Todos os Gêneros</option>
                                            <?php foreach ($generos_disponiveis as $genero): ?>
                                            <option value="<?php echo htmlspecialchars($genero); ?>"
                                                <?php echo ($filtro_genero == $genero) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($genero); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label for="idade_valor" class="form-label">Idade:</label>
                                        <input type="number" class="form-control" id="idade_valor" name="idade_valor"
                                            value="<?php echo htmlspecialchars($filtro_idade_valor); ?>"
                                            placeholder="Ex: 2">
                                    </div>
                                    <div class="col-md-1">
                                        <label for="idade_unidade" class="form-label">Tipo de Unidade:</label>
                                        <select class="form-select" id="idade_unidade" name="idade_unidade">
                                            <option value="">Ex.: meses</option>
                                            <?php foreach ($unidades_idade as $unidade): ?>
                                            <option value="<?php echo htmlspecialchars($unidade); ?>"
                                                <?php echo ($filtro_idade_unidade == $unidade) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($unidade); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="local_perdido" class="form-label">Local da perda:</label>
                                        <input type="text" class="form-control" id="local_perdido" name="local_perdido"
                                            value="<?php echo htmlspecialchars($filtro_local); ?>"
                                            placeholder="Cidade, bairro, etc.">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="telefone_contato" class="form-label">Telefone para Contato:</label>
                                        <input type="text" class="form-control" id="telefone_contato"
                                            name="telefone_contato"
                                            value="<?php echo htmlspecialchars($filtro_telefone_contato); ?>"
                                            placeholder="(XX) XXXXX-XXXX">
                                    </div>
                                    <div class="col-1 d-grid">
                                        <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
                                    </div>
                                    <div class="col-2 d-grid">
                                        <a href="listar_pet_perdido.php" class="btn btn-secondary">Limpar Filtros</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <h1 class="h4 title">Lista de pets perdidos</h1>

                    <!-- NOVO - CARDS PARA LISTAR PETS PERDIDOS -->
                    <?php if (count($petsPerdidos) > 0): ?>
                    <div id="listarpet">
                        <div class="row justify-content-md-center">

                            <?php foreach ($petsPerdidos as $pet_perdido): ?>
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="card h-100">
                                    <?php
                                                $caminho_foto = $pet_perdido["foto"];
                                                if (file_exists($caminho_foto) && !empty($caminho_foto)) {
                                                    echo "<img style='height: 299px' class='card-img-top card-img-achei object-fit-contain border rounded' src='" . htmlspecialchars($caminho_foto) . "' alt='Foto do Pet Perdido'>";
                                                } else {
                                                    echo "Sem foto";
                                                }
                                                ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($pet_perdido["nome"]); ?>
                                        </h5>

                                        <div class="row card-pet-perdido">
                                            <div class="col-md-12">
                                                <b>Falar com: </b>
                                                <?php echo htmlspecialchars($pet_perdido["nome_usuario"]); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <!--<b>Tel.: </b>-->
                                                &bull;
                                                <?php echo htmlspecialchars($pet_perdido["telefone_contato"]); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <!--<b>Espécie: </b>-->
                                                &bull; <?php echo htmlspecialchars($pet_perdido["especie"]); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <!--<b>Idade:</b>-->
                                                &bull;
                                                <?php echo htmlspecialchars($pet_perdido["idade_valor"]) . " " . htmlspecialchars($pet_perdido["idade_unidade"]); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <!--<b>Raça: </b>-->
                                                &bull; <?php echo htmlspecialchars($pet_perdido["raca"]); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <!--<b>Gênero: </b>-->
                                                &bull; <?php echo htmlspecialchars($pet_perdido["genero"]); ?>
                                            </div>

                                            <div class="col-md-12"><b>Perdi em: </b>
                                                <?php echo date("d/m/Y", strtotime($pet_perdido["data_perda"])); ?>
                                            </div>
                                            <div class="col-md-12"><b>Local da perda: </b>
                                                <?php echo htmlspecialchars($pet_perdido["local_perdido"]); ?>
                                            </div>
                                            <div class="col-md-12"><b>Descrição: </b>
                                                <?php echo htmlspecialchars($pet_perdido["descricao"]); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <p class="alert alert-info">Nenhum pet perdido encontrado com os filtros aplicados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- FIM NOVO -->


                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        content: ["./src/**/*.{html,js}"],
        theme: {
            name: "Bluewave",
            fontFamily: {
                sans: [
                    "Open Sans",
                    "ui-sans-serif",
                    "system-ui",
                    "sans-serif",
                    '"Apple Color Emoji"',
                    '"Segoe UI Emoji"',
                    '"Segoe UI Symbol"',
                    '"Noto Color Emoji"'
                ]
            },
            extend: {
                fontFamily: {
                    title: [
                        "Lato",
                        "ui-sans-serif",
                        "system-ui",
                        "sans-serif",
                        '"Apple Color Emoji"',
                        '"Segoe UI Emoji"',
                        '"Segoe UI Symbol"',
                        '"Noto Color Emoji"'
                    ],
                    body: [
                        "Open Sans",
                        "ui-sans-serif",
                        "system-ui",
                        "sans-serif",
                        '"Apple Color Emoji"',
                        '"Segoe UI Emoji"',
                        '"Segoe UI Symbol"',
                        '"Noto Color Emoji"'
                    ]
                },
                colors: {
                    neutral: {
                        50: "#f7f7f7",
                        100: "#eeeeee",
                        200: "#e0e0e0",
                        300: "#cacaca",
                        400: "#b1b1b1",
                        500: "#999999",
                        600: "#7f7f7f",
                        700: "#676767",
                        800: "#545454",
                        900: "#464646",
                        950: "#282828"
                    },
                    primary: {
                        50: "#f3f1ff",
                        100: "#e9e5ff",
                        200: "#d5cfff",
                        300: "#b7a9ff",
                        400: "#9478ff",
                        500: "#7341ff",
                        600: "#631bff",
                        700: "#611bf8",
                        800: "#4607d0",
                        900: "#3c08aa",
                        950: "#220174",
                        DEFAULT: "#611bf8"
                    }
                },
                fontSize: {
                    xs: ["12px", {
                        lineHeight: "19.200000000000003px"
                    }],
                    sm: ["14px", {
                        lineHeight: "21px"
                    }],
                    base: ["16px", {
                        lineHeight: "25.6px"
                    }],
                    lg: ["18px", {
                        lineHeight: "27px"
                    }],
                    xl: ["20px", {
                        lineHeight: "28px"
                    }],
                    "2xl": ["24px", {
                        lineHeight: "31.200000000000003px"
                    }],
                    "3xl": ["30px", {
                        lineHeight: "36px"
                    }],
                    "4xl": ["36px", {
                        lineHeight: "41.4px"
                    }],
                    "5xl": ["48px", {
                        lineHeight: "52.800000000000004px"
                    }],
                    "6xl": ["60px", {
                        lineHeight: "66px"
                    }],
                    "7xl": ["72px", {
                        lineHeight: "75.60000000000001px"
                    }],
                    "8xl": ["96px", {
                        lineHeight: "100.80000000000001px"
                    }],
                    "9xl": ["128px", {
                        lineHeight: "134.4px"
                    }]
                },
                borderRadius: {
                    none: "0px",
                    sm: "6px",
                    DEFAULT: "12px",
                    md: "18px",
                    lg: "24px",
                    xl: "36px",
                    "2xl": "48px",
                    "3xl": "72px",
                    full: "9999px"
                },
                spacing: {
                    0: "0px",
                    1: "4px",
                    2: "8px",
                    3: "12px",
                    4: "16px",
                    5: "20px",
                    6: "24px",
                    7: "28px",
                    8: "32px",
                    9: "36px",
                    10: "40px",
                    11: "44px",
                    12: "48px",
                    14: "56px",
                    16: "64px",
                    20: "80px",
                    24: "96px",
                    28: "112px",
                    32: "128px",
                    36: "144px",
                    40: "160px",
                    44: "176px",
                    48: "192px",
                    52: "208px",
                    56: "224px",
                    60: "240px",
                    64: "256px",
                    72: "288px",
                    80: "320px",
                    96: "384px",
                    px: "1px",
                    0.5: "2px",
                    1.5: "6px",
                    2.5: "10px",
                    3.5: "14px"
                }
            },
            plugins: [],
            important: "#webcrumbs"
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>