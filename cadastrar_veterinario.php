<?php
// Inicia a sessão para acessar o ID do usuário (se não já estiver iniciada por verificar_login.php)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos essenciais do seu sistema
include "conexao.php";           // Sua conexão com o banco de dados ($conexao ou $pdo)
include "dados_usuario.php";     // Para carregar dados do usuário logado (se necessário)
include "verificar_login.php";   // Para verificar se o usuário está logado e garantir $_SESSION['id_usuario']

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$message = ''; // Variável para armazenar mensagens de status
$message_type = ''; // 'success', 'error', 'info'

// Processamento do formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_clinica = $_POST["nome_clinica"] ?? '';
    $endereco = $_POST["endereco"] ?? '';
    $telefone = $_POST["telefone"] ?? '';
    $latitude = $_POST["latitude"] ?? null;
    $longitude = $_POST["longitude"] ?? null;
    $id_usuario = $_SESSION['id_usuario']; // Pega o ID do usuário da sessão

    // Validação básica
    if (empty($nome_clinica) || empty($endereco) || empty($telefone) || $latitude === null || $longitude === null) {
        $message = "Todos os campos obrigatórios (Nome da Clínica, Endereço, Telefone) devem ser preenchidos, e as coordenadas devem ser geradas automaticamente. Houve um problema na geocodificação.";
        $message_type = 'error';
    } else {
        try {
            // Assumindo que sua conexão PDO é $conexao (como no seu código de pet)
            $stmt = $conexao->prepare("INSERT INTO veterinarios (nome_clinica, endereco, telefone, latitude, longitude, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->execute([$nome_clinica, $endereco, $telefone, $latitude, $longitude, $id_usuario]);

            $message = "Veterinário cadastrado com sucesso!";
            $message_type = 'success';
            // Opcional: Redirecionar após o sucesso para evitar reenvio do formulário
            // header("Location: lista_veterinarios.php?status=success");
            // exit();

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
    <title>Cadastrar Veterinário</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        body { font-family: 'Open Sans', sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
        h1, h2 { font-family: 'Lato', sans-serif; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 0.75rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .alert-success { background-color: #D1FAE5; border-color: #34D399; color: #065F46; } /* Tailwind green-100, green-400, green-800 */
        .alert-error { background-color: #FEE2E2; border-color: #EF4444; color: #991B1B; } /* Tailwind red-100, red-400, red-800 */
        .alert-info { background-color: #DBEAFE; border-color: #60A5FA; color: #1E40AF; } /* Tailwind blue-100, blue-400, blue-800 */
    </style>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=SUA_CHAVE_AQUI&libraries=places"></script>
    
</head>
<body>
    <div id="webcrumbs">
        <div class="relative w-full min-h-screen">
            <?php include "header.php"; ?>
            
            <div class="container">
                <h1 class="text-3xl font-bold text-center mb-6 text-blue-700">Cadastrar Novo Veterinário</h1>

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
                    <div class="mb-4">
                        <label for="nome_clinica" class="block text-gray-700 text-sm font-bold mb-2">Nome da Clínica:</label>
                        <input type="text" id="nome_clinica" name="nome_clinica" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($nome_clinica ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="endereco" class="block text-gray-700 text-sm font-bold mb-2">Endereço (Rua, Número, Bairro):</label>
                        <input type="text" id="endereco" name="endereco" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($endereco ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="cep" class="block text-gray-700 text-sm font-bold mb-2">CEP:</label>
                        <input type="text" id="cep" name="cep" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($telefone ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Latitude:</label>
                    <input type="text" id="latitude" name="latitude" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"  required  value="<?php echo htmlspecialchars($latitude ?? ''); ?>">
                    <label for="telefone" class="block text-gray-700 text-sm font-bold mb-2">Longitude:</label>
                    <input type="text" id="longitude" name="longitude" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"  required  value="<?php echo htmlspecialchars($longitude ?? ''); ?>">
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cadastrar Veterinário
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>

    <script>
        document.getElementById('cadastroForm').addEventListener('submit', function(event) {
            // Impede o envio padrão do formulário para fazer a geocodificação primeiro
            event.preventDefault(); 

            const endereco = document.getElementById('endereco').value;
            const cep = document.getElementById('cep').value;
            // É uma boa prática incluir o país para geocodificação mais precisa
            const fullAddress = `${endereco}, CEP ${cep}, Brasil`; 

            const statusMessageDiv = document.querySelector('.container > div[role="alert"]'); // Busca o div de mensagem existente
            let statusMessageP = statusMessageDiv ? statusMessageDiv.querySelector('p') : null;

            if (!statusMessageP) { // Se não existir, cria um novo div de mensagem
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


            // Verifica se a API do Google Maps está carregada
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