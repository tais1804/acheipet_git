<?php
session_start();
include "conexao.php";
include "dados_usuario.php";
include "verificar_login.php";

$link_boleto = $_SESSION['link_boleto'] ?? null;
$barcode_boleto = $_SESSION['barcode_boleto'] ?? null;
$mensagem_sucesso = $_SESSION['mensagem_loja'] ?? null;

// Limpar mensagens da sessão para não exibir novamente
unset($_SESSION['link_boleto']);
unset($_SESSION['barcode_boleto']);
unset($_SESSION['mensagem_loja']);

if (!$link_boleto) {
    // Se não houver link do boleto na sessão, redireciona para a loja ou carrinho
    header("Location: loja_virtual.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Realizada com Sucesso!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
</head>
<body>
    <?php include "header.php"; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4 text-success">Compra Realizada com Sucesso!</h1>

        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success text-center">
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm text-center">
            <h5 class="card-title">Seu Boleto Bancário</h5>
            <p>Clique no link abaixo para visualizar e pagar seu boleto:</p>
            <p class="lead"><a href="<?php echo htmlspecialchars($link_boleto); ?>" target="_blank" class="btn btn-primary btn-lg">Visualizar Boleto</a></p>

            <?php if ($barcode_boleto): ?>
                <p class="mt-3">Ou copie o código de barras para pagar:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control text-center" value="<?php echo htmlspecialchars($barcode_boleto); ?>" readonly aria-label="Código de Barras">
                    <button class="btn btn-outline-secondary" type="button" id="copyBarcode">Copiar</button>
                </div>
            <?php endif; ?>

            <p class="mt-4">Você também pode acompanhar o status do seu pedido na sua área de cliente.</p>
            <a href="index.php" class="btn btn-outline-secondary mt-3">Voltar à Página Inicial</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('copyBarcode').addEventListener('click', function() {
            var barcodeInput = this.previousElementSibling;
            barcodeInput.select();
            barcodeInput.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("Código de barras copiado!");
        });
    </script>
</body>
</html>