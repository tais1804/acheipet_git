<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "conexao.php";           
include "dados_usuario.php";     
include "verificar_login.php";   

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$message = ''; 
$message_type = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_clinica = $_POST["nome_clinica"] ?? '';
    $endereco = $_POST["endereco"] ?? '';
    $telefone = $_POST["telefone"] ?? '';
    $latitude = $_POST["latitude"] ?? null;
    $longitude = $_POST["longitude"] ?? null;
    $id_usuario = $_SESSION['id_usuario']; 

    if (empty($nome_clinica) || empty($endereco) || empty($telefone) || $latitude === null || $longitude === null) {
        $message = "Todos os campos obrigatórios (Nome da Clínica, Endereço, Telefone) devem ser preenchidos, e as coordenadas devem ser geradas automaticamente. Houve um problema na geocodificação.";
        $message_type = 'error';
    } else {
        try {

            $stmt = $conexao->prepare("INSERT INTO veterinarios (nome_clinica, endereco, telefone, latitude, longitude, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->execute([$nome_clinica, $endereco, $telefone, $latitude, $longitude, $id_usuario]);

            $message = "Veterinário cadastrado com sucesso!";
            $message_type = 'success';
            

        } catch (PDOException $e) {
            $message = "Erro ao cadastrar veterinário: " . $e->getMessage();
            $message_type = 'error';
            error_log("Erro PDO no cadastro de veterinário: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achei pet</title>
    <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
    
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        
        
        .alert-success { background-color: #D1FAE5; border-color: #34D399; color: #065F46; } /* Tailwind green-100, green-400, green-800 */
        .alert-error { background-color: #FEE2E2; border-color: #EF4444; color: #991B1B; } /* Tailwind red-100, red-400, red-800 */
        .alert-info { background-color: #DBEAFE; border-color: #60A5FA; color: #1E40AF; } /* Tailwind blue-100, blue-400, blue-800 */
    </style>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=SUA_CHAVE_AQUI&libraries=places"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    
    
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php include "header.php"; ?>
            <br>
            <div class="container">
                <div class="card">
                    <div class="card-body">
                <h4 class="h4 text-center">Cadastrar Novo Veterinário</h4>

                <?php if ($message): // Exibe a mensagem de status se houver uma ?>
                    <div class="mb-4 px-4 py-3 rounded relative border <?php 
                        if ($message_type == 'success') echo 'alert-success';
                        elseif ($message_type == 'error') echo 'alert-error';
                        else echo 'alert-info';
                    ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form id="cadastroForm" method="POST">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="nome_clinica" class="block text-gray-700 text-sm font-bold mb-2">Nome da Clínica:</label>
                            <input type="text" id="nome_clinica" name="nome_clinica" class="form-control" required value="<?php echo htmlspecialchars($nome_clinica ?? ''); ?>">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="endereco" class="block text-gray-700 text-sm font-bold mb-2">Endereço (Rua, Número, Bairro):</label>
                            <input type="text" id="endereco" name="endereco" class="form-control" required value="<?php echo htmlspecialchars($endereco ?? ''); ?>">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                    <div class="form-group col-md-6">
        
                        <label for="cep" class="block text-gray-700 text-sm font-bold mb-2">CEP:</label>
                        <input type="text" id="cep" name="cep" class="form-control" required value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" class="form-control" required value="<?php echo htmlspecialchars($telefone ?? ''); ?>">
                    </div>
                    </div>
                    <br>
                   <div class="row">
                        <div class="form-group col-md-6">
                            <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Latitude:</label>
                        <input type="text" id="latitude" name="latitude" class="form-control"  required  value="<?php echo htmlspecialchars($latitude ?? ''); ?>">
                        </div>
                        
                        <div class="form-group col-md-6">
                        <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Longitude:</label>
                        <input type="text" id="longitude" name="longitude" class="form-control"  required  value="<?php echo htmlspecialchars($longitude ?? ''); ?>">
                        </div>
                    </div>
                    <br>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="btn btn-primary">
                            Cadastrar Veterinário
                        </button>
                        <a class="btn btn-secondary" href="mapa_veterinarios.php">
                            Cancelar
                </a>
                    </div>
                </div>
                </form>
            </div>
            
        </div>
        </div>
        </div>
    </div>

    <script>
        document.getElementById('cadastroForm').addEventListener('submit', function(event) {
            
            event.preventDefault(); 

            const endereco = document.getElementById('endereco').value;
            const cep = document.getElementById('cep').value;
            const fullAddress = `${endereco}, CEP ${cep}, Brasil`; 

            const statusMessageDiv = document.querySelector('.container > div[role="alert"]'); 
            let statusMessageP = statusMessageDiv ? statusMessageDiv.querySelector('p') : null;

            if (!statusMessageP) { 
                const newDiv = document.createElement('div');
                newDiv.className = "mb-4 px-4 py-3 rounded relative border alert-info";
                newDiv.setAttribute('role', 'alert');
                newDiv.innerHTML = '<p></p>';
                document.querySelector('.container').insertBefore(newDiv, document.getElementById('cadastroForm'));
                statusMessageP = newDiv.querySelector('p');
            } else {
                statusMessageDiv.className = "mb-4 px-4 py-3 rounded relative border alert-info";
            }
            statusMessageP.textContent = 'Buscando coordenadas do endereço...';

            if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.Geocoder === 'undefined') {
                statusMessageDiv.className = "mb-4 px-4 py-3 rounded relative border alert-error";
                statusMessageP.textContent = 'Erro: Google Maps API não carregada corretamente. Verifique sua chave e conexão.';
                console.error('Google Maps API ou Geocoder não disponível.');
                return;
            }

            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({ 'address': fullAddress }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const latitude = results[0].geometry