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
        
        $sql = "SELECT
                    p.id_pet AS id_pet_principal,
                    p.nome,
                    p.especie,
                    p.raca,
                    p.idade,
                    p.porte,
                    p.status,
                    p.foto,
                    'Doar Pet' as tipo_postagem
                FROM pets p
                WHERE p.id_usuario = ? AND p.id_usuario 

                UNION ALL

                SELECT
                    pp.id_pet_perdido AS id_pet_principal,
                    pp.nome,
                    pp.especie,
                    pp.raca,
                    NULL AS idade, 
                    NULL AS porte, 
                    pp.status_perda AS status, 
                    pp.foto,
                    'Perdi Meu Pet' as tipo_postagem
                FROM PetsPerdidos pp
                WHERE pp.id_usuario = ? AND pp.status_perda IN ('Perdido', 'Encontrado')
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
        $sql = "SELECT
                    SUM(CASE WHEN pp.id_pet_perdido IS NOT NULL THEN 1 ELSE 0 END) as perdidos,
                    SUM(CASE WHEN p.status = 'Disponível' THEN 1 ELSE 0 END) as doados,
                    SUM(CASE WHEN pp.status_perda = 'Encontrado' THEN 1 ELSE 0 END) as encontrados,
                    SUM(CASE WHEN p.status = 'Adotado' THEN 1 ELSE 0 END) as adotados
                FROM pets p
                LEFT JOIN PetsPerdidos pp ON p.id_pet = pp.id_pet 
                WHERE p.id_usuario = ?"; 

        $stmt = $conexao->prepare($sql);
        $stmt->execute([$id_usuario]);
        $contagem = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
    
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
                            <th>Tipo de Postagem</th>
                            <th>Status</th>
                            <th>Foto</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meus_pets as $pet): ?>
                            <tr>
                                <td class="align-middle"><?php echo $pet['id_pet_principal']; ?></td>
                                <td class="align-middle"><?php echo $pet['nome']; ?></td>
                                <td class="align-middle"><?php echo $pet['especie']; ?></td>
                                <td class="align-middle"><?php echo $pet['raca']; ?></td>
                                <td class="align-middle"><?php echo $pet['tipo_postagem']; ?></td>
                                <td class="align-middle"><?php echo $pet['status']; ?></td>
                                <td class="align-middle">
                                    <?php
                                    $caminho_foto = $pet["foto"];
                                    if (file_exists($caminho_foto) && !empty($caminho_foto)) {
                                        echo "<img class='fotopet' src='" . $caminho_foto . "' alt='Foto do Pet'>";
                                    } else {
                                        echo "Sem foto";
                                    }
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <a href="editar_pet.php?id=<?php echo $pet['id_pet_principal']; ?>&tipo=<?php echo urlencode($pet['tipo_postagem']); ?>" class="btn btn-warning btn-sm me-2">Editar</a>
                                    <a href="excluir_pet.php?id=<?php echo $pet['id_pet_principal']; ?>&tipo=<?php echo urlencode($pet['tipo_postagem']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este pet?');">Excluir</a>
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
