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
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        img.fotopet {
            max-width: 100px !important;
            height: auto;
        }
    </style>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
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
                    <h1 class="h1">Listar Pets Perdidos</h1>
                    <br/>
                    <div id="listarpet">
                        <?php
                        include "conexao.php";

                        try {

                            $stmt = $conexao->query("SELECT * FROM PetsPerdidos");
                            $petsPerdidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($petsPerdidos) > 0) {
                                echo "<table border='1' class='table table-hover'>";
                                echo "<tr>
                                    <th>Nome</th>
                                    <th>Espécie</th>
                                    <th>Raça</th>
                                    <th>Gênero</th> <th>Idade</th> <th>Data da Perda</th>
                                    <th>Local Perdido</th>
                                    <th>Descrição</th>
                                    <th>Foto</th>
                                    <th>Telefone Contato</th>
                                    <th>Status Perda</th>
                                </tr>";
                                foreach ($petsPerdidos as $pet_perdido) {
                                    echo "<tr>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["nome"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["especie"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["raca"]) . "</td>";
                                    // Adicionando a célula para Gênero
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["genero"]) . "</td>";
                                    // Modificando para exibir idade_valor e idade_unidade
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["idade_valor"]) . " " . htmlspecialchars($pet_perdido["idade_unidade"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["data_perda"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["local_perdido"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["descricao"]) . "</td>";

                                    echo "<td class='align-middle'>";
                                    $caminho_foto = $pet_perdido["foto"];
                                    if (file_exists($caminho_foto) && !empty($caminho_foto)) {
                                        echo "<img class='fotopet' src='" . htmlspecialchars($caminho_foto) . "' alt='Foto do Pet Perdido'>";
                                    } else {
                                        echo "Sem foto / Arquivo não encontrado";
                                    }
                                    echo "</td>";

                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["telefone_contato"]) . "</td>";
                                    echo "<td class='align-middle'>" . htmlspecialchars($pet_perdido["status_perda"]) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "<p>Nenhum pet perdido encontrado.</p>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Erro ao listar pets perdidos: " . $e->getMessage() . "</p>";
                        }
                        ?>
                    </div>
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
                        xs: ["12px", { lineHeight: "19.200000000000003px" }],
                        sm: ["14px", { lineHeight: "21px" }],
                        base: ["16px", { lineHeight: "25.6px" }],
                        lg: ["18px", { lineHeight: "27px" }],
                        xl: ["20px", { lineHeight: "28px" }],
                        "2xl": ["24px", { lineHeight: "31.200000000000003px" }],
                        "3xl": ["30px", { lineHeight: "36px" }],
                        "4xl": ["36px", { lineHeight: "41.4px" }],
                        "5xl": ["48px", { lineHeight: "52.800000000000004px" }],
                        "6xl": ["60px", { lineHeight: "66px" }],
                        "7xl": ["72px", { lineHeight: "75.60000000000001px" }],
                        "8xl": ["96px", { lineHeight: "100.80000000000001px" }],
                        "9xl": ["128px", { lineHeight: "134.4px" }]
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>