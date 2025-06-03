<?php
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

function buscarMeusPets($conexao, $id_usuario) {
    try {
        // Consulta para Pets (Doar Pet)
        $sql = "SELECT
                    p.id_pet AS id_pet_principal,
                    p.nome,
                    p.especie,
                    p.raca,
                    p.genero,               -- Adicionado genero
                    p.idade_valor,          -- Adicionado idade_valor
                    p.idade_unidade,        -- Adicionado idade_unidade
                    p.porte,
                    p.status,
                    p.foto,
                    'Doar Pet' as tipo_postagem
                FROM pets p
                WHERE p.id_usuario = ? 

                UNION ALL

                -- Consulta para PetsPerdidos (Perdi Meu Pet)
                SELECT
                    pp.id_pet_perdido AS id_pet_principal,
                    pp.nome,
                    pp.especie,
                    pp.raca,
                    pp.genero,              -- Adicionado genero
                    pp.idade_valor,         -- Adicionado idade_valor
                    pp.idade_unidade,       -- Adicionado idade_unidade
                    NULL AS porte,          -- Porte pode ser NULL para PetsPerdidos se não houver
                    pp.status_perda AS status, 
                    pp.foto,
                    'Perdi Meu Pet' as tipo_postagem
                FROM PetsPerdidos pp
                WHERE pp.id_usuario = ? 
                ORDER BY id_pet_principal DESC";

        $stmt = $conexao->prepare($sql);
        $stmt->execute([$id_usuario, $id_usuario]); 
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pets;
    } catch (PDOException $e) {
        echo "Erro ao buscar pets: " . $e->getMessage();
        return [];
    }
}

function contarPostagens($conexao, $id_usuario) {
    try {
        // Contagem para pets de doação
        $sql_doacao = "SELECT
                            COUNT(*) as total_doacao,
                            SUM(CASE WHEN status = 'Adotado' THEN 1 ELSE 0 END) as adotados
                        FROM pets
                        WHERE id_usuario = ?";
        $stmt_doacao = $conexao->prepare($sql_doacao);
        $stmt_doacao->execute([$id_usuario]);
        $contagem_doacao = $stmt_doacao->fetch(PDO::FETCH_ASSOC);

        // Contagem para pets perdidos/encontrados
        $sql_perdidos = "SELECT
                            COUNT(*) as total_perdidos,
                            SUM(CASE WHEN status_perda = 'Encontrado' THEN 1 ELSE 0 END) as encontrados
                        FROM PetsPerdidos
                        WHERE id_usuario = ?";
        $stmt_perdidos = $conexao->prepare($sql_perdidos);
        $stmt_perdidos->execute([$id_usuario]);
        $contagem_perdidos = $stmt_perdidos->fetch(PDO::FETCH_ASSOC);

        $contagem = [
            'perdidos' => ($contagem_perdidos['total_perdidos'] ?? 0) - ($contagem_perdidos['encontrados'] ?? 0), // Pets perdidos e não encontrados
            'doados' => ($contagem_doacao['total_doacao'] ?? 0) - ($contagem_doacao['adotados'] ?? 0), // Pets para doação (não adotados)
            'encontrados' => $contagem_perdidos['encontrados'] ?? 0,
            'adotados' => $contagem_doacao['adotados'] ?? 0
        ];

        return $contagem;
    } catch (PDOException $e) {
        echo "Erro ao contar postagens: " . $e->getMessage();
        return [];
    }
}

$meus_pets = buscarMeusPets($conexao, $id_usuario);
$contagem_postagens = contarPostagens($conexao, $id_usuario); 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
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
    
</head>

<body class="bg-gray-100">
    <?php include "header.php"; ?>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-red-600 mb-4">Meus Pets</h1>

        <?php if (empty($meus_pets)): ?>
            <p class="text-gray-600">Você não cadastrou nenhum pet.</p>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg p-6">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Espécie</th>
                            <th>Raça</th>
                            <th>Gênero</th>        <th>Idade</th>         <th>Porte</th>         <th>Tipo de Postagem</th>
                            <th>Status</th>
                            <th>Foto</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meus_pets as $pet): ?>
                            <tr>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['id_pet_principal']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['nome']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['especie']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['raca']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['genero']); ?></td> <td class="align-middle">
                                    <?php 
                                    // Exibe a idade combinando valor e unidade, se existirem
                                    if (!empty($pet['idade_valor']) && !empty($pet['idade_unidade'])) {
                                        echo htmlspecialchars($pet['idade_valor']) . " " . htmlspecialchars($pet['idade_unidade']);
                                    } else {
                                        echo "N/A"; // Ou alguma outra indicação se a idade não for aplicável
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['porte']); ?></td> <td class="align-middle"><?php echo htmlspecialchars($pet['tipo_postagem']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($pet['status']); ?></td>
                                <td class="align-middle">
                                    <?php
                                    $caminho_foto = $pet["foto"];
                                    if (file_exists($caminho_foto) && !empty($caminho_foto)) {
                                        echo "<img class='fotopet' src='" . htmlspecialchars($caminho_foto) . "' alt='Foto do Pet'>";
                                    } else {
                                        echo "Sem foto";
                                    }
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <a href="editar_pet.php?id=<?php echo htmlspecialchars($pet['id_pet_principal']); ?>&tipo=<?php echo urlencode($pet['tipo_postagem']); ?>" class="btn btn-warning btn-sm me-2">Editar</a>
                                    <a href="excluir_pet.php?id=<?php echo htmlspecialchars($pet['id_pet_principal']); ?>&tipo=<?php echo urlencode($pet['tipo_postagem']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este pet?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-6 flex justify-center space-x-4">
            <a href="cadastrar_pet.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Cadastrar Pet para Doação</a>
            <a href="cadastrar_pet_perdido.php" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Cadastrar Pet Perdido</a>
            <a href="perfil_usuario.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Voltar ao Perfil</a>
        </div>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'lato': ['Lato', 'sans-serif'],
                        'open-sans': ['Open Sans', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

</body>
</html>