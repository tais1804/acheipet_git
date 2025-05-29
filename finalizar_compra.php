<?php
session_start();
include "conexao.php"; // Inclui seu arquivo de conexão PDO
include "dados_usuario.php"; // Inclui seu arquivo para obter dados do usuário logado
include "verificar_login.php"; // Inclui seu arquivo de verificação de login
include "config_efi.php"; // Inclua seu arquivo de configuração da Efí

// Inclua o autoload do Composer se estiver usando o SDK da Efí
require __DIR__ . '/vendor/autoload.php';

use Efi\Exception\EfiException;
use Efi\EfiPay;

// --- 1. Verificar se o carrinho está vazio ---
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    $_SESSION['mensagem_loja'] = "<p class='alert alert-warning'>Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.</p>";
    header("Location: loja_virtual.php");
    exit();
}

// --- 2. Obter dados do usuário logado (assumindo que 'dados_usuario.php' popula $usuario) ---
// Certifique-se de que $usuario['id_usuario'], $usuario['nome'], $usuario['cpf'],
// $usuario['email'], $usuario['telefone'], $usuario['cep'], $usuario['rua'],
// $usuario['numero'], $usuario['bairro'], $usuario['cidade'], $usuario['estado']
// estão disponíveis e corretos.
// Você pode precisar de um formulário de checkout para coletar/confirmar esses dados.

// Simulação de dados do usuário logado (substitua pela sua lógica de 'dados_usuario.php')
// Se o usuário não tiver esses dados, você precisará de um formulário antes de chegar aqui.
if (!isset($usuario) || empty($usuario['id_usuario'])) {
    // Redirecionar para uma página de perfil ou formulário de checkout para coletar dados
    $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Por favor, complete seus dados de cadastro antes de finalizar a compra.</p>";
    header("Location: perfil.php?redirect=finalizar_compra"); // Exemplo de redirecionamento
    exit();
}

// Supondo que dados_usuario.php já tenha carregado $usuario com esses campos
$id_usuario = $usuario['id_usuario'];
$nome_cliente = $usuario['nome'] ?? ''; // Nome completo do cliente
$cpf_cliente = $usuario['cpf'] ?? '';   // CPF do cliente (apenas números)
$email_cliente = $usuario['email'] ?? '';
$telefone_cliente = $usuario['telefone'] ?? ''; // Telefone do cliente (apenas números)
$cep_cliente = $usuario['cep'] ?? ''; // Apenas números
$rua_cliente = $usuario['rua'] ?? '';
$numero_cliente = $usuario['numero'] ?? '';
$bairro_cliente = $usuario['bairro'] ?? '';
$cidade_cliente = $usuario['cidade'] ?? '';
$estado_cliente = $usuario['estado'] ?? ''; // Sigla do estado (Ex: SP, RJ)


// --- 3. Calcular o total da compra e formatar itens para a API da Efí ---
$total_compra = 0;
$items = [];
foreach ($_SESSION['carrinho'] as $id_produto => $item) {
    $subtotal_item = $item['preco'] * $item['quantidade'];
    $total_compra += $subtotal_item;

    $items[] = [
        'name'      => $item['nome'],
        'amount'    => $item['quantidade'],
        'value'     => round($item['preco'] * 100), // Preço em centavos para a API
    ];
}

// --- 4. Preparar dados para a API da Efí ---

// Ajustar o CPF: a API da Efí exige CPF no formato 000.000.000-00 ou 00000000000
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_cliente);
if (strlen($cpf_limpo) != 11) {
    $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>CPF do cliente inválido. Por favor, verifique seus dados cadastrais.</p>";
    header("Location: perfil.php?redirect=finalizar_compra");
    exit();
}

// Ajustar o telefone: a API da Efí exige telefone no formato 00900000000 (DD + número)
$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone_cliente);
if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
    $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Telefone do cliente inválido. Por favor, verifique seus dados cadastrais.</p>";
    header("Location: perfil.php?redirect=finalizar_compra");
    exit();
}

// Dados do pagador (customer)
$customer = [
    'name'          => $nome_cliente,
    'cpf'           => $cpf_limpo,
    'email'         => $email_cliente,
    'phone_number'  => $telefone_limpo,
    'address'       => $rua_cliente,
    'number'        => $numero_cliente,
    'neighborhood'  => $bairro_cliente,
    'zipcode'       => preg_replace('/[^0-9]/', '', $cep_cliente), // Apenas números
    'city'          => $cidade_cliente,
    'state'         => $estado_cliente, // Ex: 'SP'
];

// Configuração da Efí Pay SDK
$options = [
    'client_id' => EFI_CLIENT_ID,
    'client_secret' => EFI_CLIENT_SECRET,
    'sandbox' => EFI_SANDBOX,
];

// Se estiver em produção e usando certificado
if (!EFI_SANDBOX && file_exists(EFI_CERTIFICATE)) {
    $options['certificate'] = EFI_CERTIFICATE;
}

try {
    $efi = new EfiPay($options);

    // Corpo da requisição para gerar o boleto
    $body = [
        'items' => $items,
        'shippings' => [ // Exemplo: frete, se aplicável
            [
                'name'  => 'Frete',
                'value' => 0, // Ajuste se você tiver custo de frete
            ]
        ],
        'customer' => $customer,
        'configurations' => [ // Configurações adicionais para o boleto
            'due_date' => date('Y-m-d', strtotime('+3 days')), // Vencimento em 3 dias
            'fine' => 0, // Multa por atraso (em porcentagem)
            'interest' => 0, // Juros por dia de atraso (em porcentagem)
        ],
        // Opcional: mensagens no boleto
        'metadata' => [
            'custom_id' => $id_usuario . '_' . uniqid(), // ID personalizado para rastrear o pedido
        ]
    ];

    $response = $efi->createCharge($body);

    // --- 5. Processar a resposta da API ---
    if (isset($response['code']) && $response['code'] == 200) {
        $data_boleto = $response['data'];
        $link_boleto = $data_boleto['link'];
        $barcode = $data_boleto['barcode'];
        $status_boleto = $data_boleto['status']; // Ex: 'waiting', 'paid'
        $charge_id = $data_boleto['charge_id']; // ID da transação na Efí

        // --- 6. Salvar os detalhes do pedido e boleto no banco de dados ---
        // Você precisará de uma tabela para 'pedidos' e 'itens_pedido'
        // Exemplo de estrutura de tabela 'pedidos':
        // id_pedido (PK), id_usuario (FK), valor_total, data_pedido, status_pedido,
        // efi_charge_id, efi_link_boleto, efi_barcode, efi_status_boleto

        try {
            $conexao->beginTransaction();

            $stmt_pedido = $conexao->prepare("INSERT INTO pedidos (id_usuario, valor_total, data_pedido, status_pedido, efi_charge_id, efi_link_boleto, efi_barcode, efi_status_boleto) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt_pedido->execute([
                $id_usuario,
                $total_compra,
                'pendente', // Status inicial do pedido
                $charge_id,
                $link_boleto,
                $barcode,
                $status_boleto
            ]);
            $id_pedido = $conexao->lastInsertId();

            // Inserir itens do pedido
            $stmt_itens = $conexao->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
            foreach ($_SESSION['carrinho'] as $id_produto_carrinho => $item_carrinho) {
                $stmt_itens->execute([
                    $id_pedido,
                    $id_produto_carrinho,
                    $item_carrinho['nome'],
                    $item_carrinho['quantidade'],
                    $item_carrinho['preco']
                ]);
            }

            // Opcional: Limpar o carrinho da sessão após a compra bem-sucedida
            unset($_SESSION['carrinho']);
            $_SESSION['mensagem_loja'] = "<p class='alert alert-success'>Pedido realizado com sucesso! Abaixo, o link para o seu boleto.</p>";

            $conexao->commit();

            // --- 7. Exibir o link do boleto para o usuário ---
            // Em vez de redirecionar imediatamente, você pode exibir o boleto aqui.
            // Para isso, remova o header() e inclua o HTML abaixo.

            // Ou armazene na sessão para exibir na página de "Sucesso"
            $_SESSION['link_boleto'] = $link_boleto;
            $_SESSION['barcode_boleto'] = $barcode;
            header("Location: compra_sucesso.php"); // Redireciona para uma página de sucesso
            exit();

        } catch (PDOException $e) {
            $conexao->rollBack();
            error_log("Erro ao salvar pedido no DB: " . $e->getMessage());
            $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Erro ao processar seu pedido. Tente novamente mais tarde.</p>";
            header("Location: carrinho.php"); // Volta para o carrinho ou página de erro
            exit();
        }

    } else {
        // Erro na resposta da API da Efí
        $mensagem_erro_efi = $response['error_description'] ?? 'Erro desconhecido ao gerar boleto.';
        error_log("Erro Efí API: " . $mensagem_erro_efi . " - Detalhes: " . json_encode($response));
        $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Erro ao gerar boleto: " . htmlspecialchars($mensagem_erro_efi) . ". Por favor, tente novamente.</p>";
        header("Location: carrinho.php"); // Volta para o carrinho ou página de erro
        exit();
    }

} catch (EfiException $e) {
    // Erro do SDK da Efí (ex: credenciais inválidas, falha de conexão)
    error_log("EfiException: " . $e->getMessage() . " - Código: " . $e->code . " - Detalhes: " . json_encode($e->error));
    $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Houve um problema com a integração de pagamento. Código: " . htmlspecialchars($e->code) . ". Tente novamente.</p>";
    header("Location: carrinho.php");
    exit();
} catch (Exception $e) {
    // Outros erros
    error_log("Erro inesperado: " . $e->getMessage());
    $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>";
    header("Location: carrinho.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processando Pagamento...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <?php include "header.php"; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4 text-primary">Processando seu Pedido...</h1>
        <div class="d-flex justify-content-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
        <p class="text-center mt-3">Você será redirecionado em breve.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>