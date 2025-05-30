<?php
session_start();
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";
include "config_efi.php";

require __DIR__ . '/vendor/autoload.php';

use EfiPay\EfiPay;

$efi = new EfiPay($options);


$erro_dados_cliente = false;
$mensagem_erro_cliente = "";

if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    $_SESSION['mensagem_loja'] = "<p class='alert alert-warning'>Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.</p>";
    header("Location: loja_virtual.php");
    exit();
}


if (!isset($usuario) || empty($usuario['id_usuario'])) {
    $erro_dados_cliente = true;
    $mensagem_erro_cliente = "Usuário não logado ou dados de cadastro incompletos. Por favor, faça login ou complete seu perfil.";
} else {
    $id_usuario = $usuario['id_usuario'];
    $nome_cliente = $usuario['nome'] ?? '';
    // ...
    $cpf_cliente = $usuario['cpf'] ?? ''; 
    $email_cliente = $usuario['email'] ?? '';
    $telefone_cliente = $usuario['telefone'] ?? '';
    $endereco_cliente = $usuario['endereco'] ?? ''; 

    // Validação de campos essenciais para a Efí
    // Ajustado para usar o campo 'endereco' unificado
    if (empty($nome_cliente) || empty($email_cliente) || empty($telefone_cliente) || empty($endereco_cliente)) {
        $erro_dados_cliente = true;
        $mensagem_erro_cliente = "Seus dados de cadastro (nome, email, telefone, endereço) estão incompletos. Por favor, preencha-os no seu perfil.";
    }

    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_cliente);
    if (strlen($cpf_limpo) != 11) {
        $erro_dados_cliente = true;
        $mensagem_erro_cliente = "CPF do cliente inválido. Por favor, verifique seus dados cadastrais no perfil.";
    }

    $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone_cliente);
    if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
        $erro_dados_cliente = true;
        $mensagem_erro_cliente = "Telefone do cliente inválido. Por favor, verifique seus dados cadastrais no perfil.";
    }
}

// Variáveis para armazenar o link do boleto e PIX
$link_boleto = null;
$barcode_boleto = null;
$qr_code_pix_image = null;
$qr_code_pix_text = null;
$valor_pix = null;
$data_expiracao_pix = null;


if (!$erro_dados_cliente) {

    $total_compra = 0;
    $items = [];
    foreach ($_SESSION['carrinho'] as $id_produto => $item) {
        $subtotal_item = $item['preco'] * $item['quantidade'];
        $total_compra += $subtotal_item;

        $items[] = [
            'name'      => $item['nome'],
            'amount'    => $item['quantidade'],
            'value'     => round($item['preco'] * 100),
        ];
    }

    
    $customer = [
        'name'          => $nome_cliente,
        'cpf'           => $cpf_limpo,
        'email'         => $email_cliente,
        'phone_number'  => $telefone_limpo,
        'address'       => $endereco_cliente, 
        'number'        => '', // Não disponível no DB de usuário
        'neighborhood'  => '', // Não disponível no DB de usuário
        'zipcode'       => '', // Não disponível no DB de usuário
        'city'          => '', // Não disponível no DB de usuário
        'state'         => '', // Não disponível no DB de usuário
    ];

    $options = [
        'client_id' => EFI_CLIENT_ID,
        'client_secret' => EFI_CLIENT_SECRET,
        'sandbox' => EFI_SANDBOX,
    ];

    if (!EFI_SANDBOX && defined('EFI_CERTIFICATE') && file_exists(EFI_CERTIFICATE)) {
        $options['certificate'] = EFI_CERTIFICATE;
    }

    try {
        $efi = new EfiPay($options);


        $body_boleto = [
            'items' => $items,
            'shippings' => [
                [
                    'name'  => 'Frete',
                    'value' => 0,
                ]
            ],
            'customer' => $customer,
            'configurations' => [
                'due_date' => date('Y-m-d', strtotime('+3 days')),
                'fine' => 0,
                'interest' => 0,
            ],
            'metadata' => [
                'custom_id' => $id_usuario . '_BOLETO_' . uniqid(),
            ]
        ];

        $response_boleto = $efi->createCharge($body_boleto);

        if (isset($response_boleto['code']) && $response_boleto['code'] == 200) {
            $data_boleto = $response_boleto['data'];
            $link_boleto = $data_boleto['link'];
            $barcode_boleto = $data_boleto['barcode'];
            $status_boleto = $data_boleto['status'];
            $charge_id_boleto = $data_boleto['charge_id'];

            try {
                $conexao->beginTransaction();
                $stmt_pedido = $conexao->prepare("INSERT INTO pedidos (id_usuario, valor_total, data_pedido, status_pedido, efi_charge_id, efi_link_boleto, efi_barcode, efi_status_boleto, tipo_pagamento) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
                $stmt_pedido->execute([
                    $id_usuario,
                    $total_compra,
                    'pendente',
                    $charge_id_boleto,
                    $link_boleto,
                    $barcode_boleto,
                    $status_boleto,
                    'boleto'
                ]);
                $id_pedido = $conexao->lastInsertId();

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
                $conexao->commit();
                unset($_SESSION['carrinho']);
                $_SESSION['mensagem_loja'] = "<p class='alert alert-success'>Pedido realizado com sucesso! Abaixo, as opções de pagamento.</p>";

            } catch (PDOException $e) {
                $conexao->rollBack();
                error_log("Erro ao salvar pedido com boleto no DB: " . $e->getMessage());
                $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Erro ao salvar pedido (boleto). Tente novamente mais tarde.</p>";
            }
        } else {
            $mensagem_erro_efi = $response_boleto['error_description'] ?? 'Erro desconhecido ao gerar boleto.';
            error_log("Erro Efí API (Boleto): " . $mensagem_erro_efi . " - Detalhes: " . json_encode($response_boleto));
            $_SESSION['mensagem_loja'] = "<p class='alert alert-warning'>Não foi possível gerar o boleto: " . htmlspecialchars($mensagem_erro_efi) . ".</p>";
        }

        // Tentar gerar PIX
        $valor_pix = number_format($total_compra, 2, '.', '');
        $expiracao_pix_segundos = 3600;

        $body_pix = [
            'calendario' => [
                'expiracao' => $expiracao_pix_segundos
            ],
            'devedor' => [
                'cpf' => $cpf_limpo,
                'nome' => $nome_cliente
            ],
            'valor' => [
                'original' => $valor_pix
            ],
            'chave' => 'SUA_CHAVE_PIX_AQUI', // Sua chave Pix cadastrada na Efí
            'solicitacaoPagador' => 'Pedido em Achei Pet - ID: ' . $id_usuario . '_PIX_' . uniqid(),
            'infoAdicionais' => [
                [
                    'nome' => 'ID do Pedido',
                    'valor' => $id_usuario . '_PIX_' . uniqid()
                ]
            ]
        ];

        $response_pix = $efi->pixCreateCharge($body_pix);

        if (isset($response_pix['code']) && $response_pix['code'] == 200) {
            $data_pix = $response_pix['data'];
            $loc_id = $data_pix['loc']['id'];
            $emv = $data_pix['pixCopiaECola'];
            $qr_code_pix_image = $data_pix['imagemQrcode'];

            $data_expiracao_pix_timestamp = time() + $expiracao_pix_segundos;
            $data_expiracao_pix = date('H:i:s d/m/Y', $data_expiracao_pix_timestamp);

            $qr_code_pix_text = $emv;
            if (!isset($id_pedido) || empty($id_pedido)) {
                try {
                    $conexao->beginTransaction();
                    $stmt_pedido_pix = $conexao->prepare("INSERT INTO pedidos (id_usuario, valor_total, data_pedido, status_pedido, efi_charge_id, tipo_pagamento, efi_pix_loc_id, efi_pix_emv, efi_pix_qrcode_image) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
                    $stmt_pedido_pix->execute([
                        $id_usuario,
                        $total_compra,
                        'pendente_pix',
                        $loc_id,
                        'pix',
                        $loc_id,
                        $emv,
                        $qr_code_pix_image
                    ]);
                    $id_pedido_pix = $conexao->lastInsertId();

                    $stmt_itens_pix = $conexao->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
                    foreach ($_SESSION['carrinho'] as $id_produto_carrinho => $item_carrinho) {
                        $stmt_itens_pix->execute([
                            $id_pedido_pix,
                            $id_produto_carrinho,
                            $item_carrinho['nome'],
                            $item_carrinho['quantidade'],
                            $item_carrinho['preco']
                        ]);
                    }
                    $conexao->commit();
                    unset($_SESSION['carrinho']);
                    $_SESSION['mensagem_loja'] = "<p class='alert alert-success'>Pedido realizado com sucesso! Abaixo, as opções de pagamento.</p>";

                } catch (PDOException $e) {
                    $conexao->rollBack();
                    error_log("Erro ao salvar pedido com Pix no DB: " . $e->getMessage());
                    $_SESSION['mensagem_loja'] .= "<p class='alert alert-danger'>Erro ao salvar pedido (Pix). Tente novamente mais tarde.</p>";
                }
            } else {
                $_SESSION['mensagem_loja'] .= "<p class='alert alert-info'>Opção de pagamento PIX também disponível.</p>";
            }


        } else {
            $mensagem_erro_efi = $response_pix['error_description'] ?? 'Erro desconhecido ao gerar Pix.';
            error_log("Erro Efí API (Pix): " . $mensagem_erro_efi . " - Detalhes: " . json_encode($response_pix));
            $_SESSION['mensagem_loja'] .= "<p class='alert alert-warning'>Não foi possível gerar o Pix: " . htmlspecialchars($mensagem_erro_efi) . ".</p>";
        }

    } catch (EfiException $e) {
        error_log("EfiException ao gerar pagamento: " . $e->getMessage() . " - Código: " . $e->code . " - Detalhes: " . json_encode($e->error));
        $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Houve um problema com a integração de pagamento: Código " . htmlspecialchars($e->code) . ". Tente novamente.</p>";
    } catch (Exception $e) {
        error_log("Erro inesperado ao gerar pagamento: " . $e->getMessage());
        $_SESSION['mensagem_loja'] = "<p class='alert alert-danger'>Ocorreu um erro inesperado ao gerar pagamento. Por favor, tente novamente mais tarde.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Pagamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <?php include "header.php"; ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4 text-primary">Finalizar Compra</h1>

        <?php
        if (isset($_SESSION['mensagem_loja'])) {
            echo $_SESSION['mensagem_loja'];
            unset($_SESSION['mensagem_loja']);
        }
        ?>

        <?php if ($erro_dados_cliente): ?>
            <div class="alert alert-danger text-center">
                <p><?php echo htmlspecialchars($mensagem_erro_cliente); ?></p>
                <p>Por favor, <a href="perfil_usuario.php" class="alert-link">clique aqui para completar/corrigir seus dados no perfil</a>.</p>
            </div>
        <?php else: ?>
            <div class="card p-4 shadow-sm mb-4">
                <h5 class="card-title text-center mb-3">Opções de Pagamento</h5>
                <p class="text-center">Valor total do pedido: **R$ <?php echo number_format($total_compra, 2, ',', '.'); ?>**</p>

                <?php if ($link_boleto): ?>
                    <hr>
                    <div class="mb-4 text-center">
                        <h4>Pagar com Boleto Bancário</h4>
                        <p>Seu boleto foi gerado com sucesso!</p>
                        <p class="lead">
                            <a href="<?php echo htmlspecialchars($link_boleto); ?>" target="_blank" class="btn btn-primary btn-lg">Visualizar Boleto</a>
                        </p>
                        <?php if ($barcode_boleto): ?>
                            <p class="mt-3">Ou copie o código de barras:</p>
                            <div class="input-group mb-3 justify-content-center">
                                <input type="text" class="form-control text-center" value="<?php echo htmlspecialchars($barcode_boleto); ?>" readonly aria-label="Código de Barras" style="max-width: 400px;">
                                <button class="btn btn-outline-secondary" type="button" id="copyBarcode">Copiar Código de Barras</button>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Lembre-se de pagar antes do vencimento para evitar cancelamento do pedido.</small>
                    </div>
                <?php endif; ?>

                <?php if ($qr_code_pix_image): ?>
                    <hr>
                    <div class="mb-4 text-center">
                        <h4>Pagar com PIX</h4>
                        <p>Escaneie o QR Code abaixo com o aplicativo do seu banco para pagar:</p>
                        <img src="<?php echo htmlspecialchars($qr_code_pix_image); ?>" alt="QR Code Pix" class="img-fluid" style="max-width: 250px;">
                        <p class="mt-3">Valor: R$ <?php echo number_format($valor_pix, 2, ',', '.'); ?></p>
                        <p class="mt-3">Chave copia e cola:</p>
                        <div class="input-group mb-3 justify-content-center">
                            <textarea class="form-control text-center" rows="4" readonly aria-label="Chave Pix Copia e Cola" style="max-width: 400px;"><?php echo htmlspecialchars($qr_code_pix_text); ?></textarea>
                            <button class="btn btn-outline-secondary" type="button" id="copyPixCode">Copiar Chave Pix</button>
                        </div>
                        <?php if ($data_expiracao_pix): ?>
                             <small class="text-muted">O Pix expira em: <?php echo htmlspecialchars($data_expiracao_pix); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$link_boleto && !$qr_code_pix_image): ?>
                    <div class="alert alert-warning text-center">
                        <p>Nenhuma opção de pagamento foi gerada com sucesso. Por favor, tente novamente.</p>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="loja_virtual.php" class="btn btn-secondary">Continuar Comprando</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var copyBarcodeBtn = document.getElementById('copyBarcode');
            if (copyBarcodeBtn) {
                copyBarcodeBtn.addEventListener('click', function() {
                    var barcodeInput = this.previousElementSibling;
                    barcodeInput.select();
                    barcodeInput.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                    alert("Código de barras copiado!");
                });
            }

            var copyPixCodeBtn = document.getElementById('copyPixCode');
            if (copyPixCodeBtn) {
                copyPixCodeBtn.addEventListener('click', function() {
                    var pixInput = this.previousElementSibling;
                    pixInput.select();
                    pixInput.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                    alert("Chave Pix copiada!");
                });
            }
        });
    </script>
</body>
</html>
