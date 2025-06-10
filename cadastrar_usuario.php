<?php
// Inclui o arquivo de conexão. É CRÍTICO que 'conexao.php' DEFENDA $conexao como um objeto PDO válido.
include "conexao.php"; 

// Inicializa as variáveis de mensagem para o HTML
$mensagem = "";
$tipo_mensagem = "";

// Inicializa $email_existe para evitar o warning se o formulário não for submetido via POST
$email_existe = 0; 

/**
 * Função para validar CPF.
 * Adaptada de diversas fontes, é uma implementação padrão para validação de CPF no Brasil.
 *
 * @param string $cpf O número do CPF a ser validado.
 * @return bool Retorna true se o CPF for válido, false caso contrário.
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);

    // Verifica se o número de dígitos é 11
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}


// A maioria do código de processamento DEVE estar DENTRO deste bloco POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // É uma boa prática verificar se os campos POST existem antes de usá-los,
    // especialmente se eles não são obrigatórios ou podem não vir.
    // No seu caso, a maioria é 'required' no HTML, mas a validação no PHP é mais segura.
    $nome = $_POST["nome"] ?? ''; // Usa o operador null coalescing para atribuir vazio se não existir
    $email = $_POST["email"] ?? '';
    $senha_digitada = $_POST["senha"] ?? '';
    $senha_hash = password_hash($senha_digitada, PASSWORD_DEFAULT); 
    $endereco = $_POST["endereco"] ?? '';
    $telefone = $_POST["telefone"] ?? '';
    $cpf = $_POST["cpf"] ?? '';
    $data_nascimento = $_POST["data_nascimento"] ?? null; 
    $tipo_usuario = $_POST["tipo_usuario"] ?? '';

    // Início da validação do CPF
    if (!validarCPF($cpf)) {
        $mensagem = "O CPF informado é inválido. Por favor, digite um CPF válido.";
        $tipo_mensagem = "danger";
    }
    // Fim da validação do CPF
    
    // Se o CPF é válido e não houve erro no upload/conexão
    if (empty($mensagem)) { // Verifica se $mensagem ainda está vazia
        // Verifica se $conexao é um objeto PDO antes de tentar usá-lo
        if (!isset($conexao) || !$conexao instanceof PDO) {
            $mensagem = "Erro interno: Conexão com o banco de dados não estabelecida.";
            $tipo_mensagem = "danger";
        } else {
            // **1. Tratamento do upload da foto**
            $foto_caminho = null; 

            if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
                $diretorio_uploads = "uploads/fotos_perfil/"; 
                
                if (!is_dir($diretorio_uploads)) {
                    mkdir($diretorio_uploads, 0777, true); 
                }

                $nome_arquivo_original = basename($_FILES["foto"]["name"]);
                $extensao_arquivo = pathinfo($nome_arquivo_original, PATHINFO_EXTENSION);
                $nome_arquivo_unico = uniqid() . "." . $extensao_arquivo; 
                $caminho_destino = $diretorio_uploads . $nome_arquivo_unico;

                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $caminho_destino)) {
                    $foto_caminho = $caminho_destino; 
                } else {
                    $mensagem = "Erro ao mover o arquivo de foto para o servidor.";
                    $tipo_mensagem = "danger";
                }
            } elseif (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_NO_FILE) {
                $foto_caminho = null; 
            } else if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] != UPLOAD_ERR_NO_FILE) {
                $mensagem = "Erro no upload da foto: Código " . $_FILES["foto"]["error"];
                $tipo_mensagem = "danger";
            }

            // Se ainda não houve erro, prossegue com a inserção
            if (empty($mensagem)) { 
                try {
                    // **2. Verificação de e-mail duplicado**
                    $stmt_check_email = $conexao->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ?");
                    $stmt_check_email->execute([$email]);
                    $email_existe = $stmt_check_email->fetchColumn();

                    // **3. Verificação de CPF duplicado (Adicionado)**
                    $stmt_check_cpf = $conexao->prepare("SELECT COUNT(*) FROM Usuarios WHERE cpf = ?");
                    $stmt_check_cpf->execute([$cpf]);
                    $cpf_existe = $stmt_check_cpf->fetchColumn();


                    if ($email_existe > 0) {
                        $mensagem = "Este e-mail já está cadastrado. Por favor, use outro e-mail ou faça login.";
                        $tipo_mensagem = "warning";
                    } elseif ($cpf_existe > 0) { // Adicionado: Mensagem para CPF duplicado
                        $mensagem = "Este CPF já está cadastrado. Por favor, verifique seus dados ou faça login.";
                        $tipo_mensagem = "warning";
                    } else {
                        // Inserção na tabela Usuarios
                        $stmt_usuarios = $conexao->prepare("INSERT INTO Usuarios (nome, email, senha, endereco, telefone, cpf, data_nascimento, tipo_usuario, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt_usuarios->execute([$nome, $email, $senha_hash, $endereco, $telefone, $cpf, $data_nascimento, $tipo_usuario, $foto_caminho]);

                        $id_usuario = $conexao->lastInsertId();

                        // Inserção na tabela login
                        $stmt_login = $conexao->prepare("INSERT INTO login (id_usuario, senha) VALUES (?, ?)");
                        $stmt_login->execute([$id_usuario, $senha_hash]); 

                        $mensagem = "Usuário cadastrado com sucesso!";
                        $tipo_mensagem = "success";
                    }

                } catch (PDOException $e) {
                    $mensagem = "Erro ao cadastrar usuário: " . $e->getMessage();
                    $tipo_mensagem = "danger";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - Achei Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <style>
    body {
        font-family: 'Open Sans', sans-serif;
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .card {
        max-width: 500px;
        width: 100%;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    .alert {
        margin-top: 20px;
    }

    .links-container {
        margin-top: 20px;
        text-align: center;
    }

    .links-container a {
        margin: 0 10px;
        color: #0d6efd;
        text-decoration: none;
    }

    .links-container a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="card">
        <h1 class="card-title text-center">Cadastrar Usuário</h1>

        <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <form method="post" action="cadastrar_usuario.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="cpf" class="form-label">CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço:</label>
                <input type="text" class="form-control" id="endereco" name="endereco" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone:</label>
                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(XX) XXXXX-XXXX"
                    required>
            </div>
            <div class="mb-3">
                <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto do perfil:</label>
                <input type="file" class="form-control" id="foto" name="foto">
            </div>
            <div class="mb-3">
                <label for="tipo_usuario" class="form-label">Tipo de Usuário:</label>
                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                    <option value="">Selecione um tipo</option>
                    <option value="tutor">Tutor</option>
                    <option value="adotante">Adotante</option>
                    <option value="abrigo">Abrigo</option>
                    <option value="veterinario">Veterinário</option>
                    <option value="petshop">Petshop</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Cadastrar</button>
            </div>
        </form>

        <div class="links-container">
            <a href="index.php">Página Inicial</a>
            <a href="login.php">Já tem conta? Faça Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cpfInput = document.getElementById('cpf');
        const telefoneInput = document.getElementById('telefone');

        // Máscara de CPF
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });

        // Validação visual simples ao sair do campo (onblur) para CPF
        cpfInput.addEventListener('blur', function(e) {
            const cpf = e.target.value;
            if (cpf) { // Só valida se o campo não estiver vazio
                if (validarCPF(cpf)) { // Usando a função JS de validação de CPF
                    cpfInput.classList.remove('is-invalid');
                    cpfInput.classList.add('is-valid');
                } else {
                    cpfInput.classList.add('is-invalid');
                    cpfInput.classList.remove('is-valid');
                }
            } else {
                cpfInput.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Função JavaScript para validar CPF (pode ser mais simples que a do PHP, mas útil para feedback imediato)
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            let soma = 0;
            let resto;
            for (let i = 1; i <= 9; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }
            resto = (soma * 10) % 11;
            if ((resto === 10) || (resto === 11)) {
                resto = 0;
            }
            if (resto !== parseInt(cpf.substring(9, 10))) {
                return false;
            }
            soma = 0;
            for (let i = 1; i <= 10; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }
            resto = (soma * 10) % 11;
            if ((resto === 10) || (resto === 11)) {
                resto = 0;
            }
            if (resto !== parseInt(cpf.substring(10, 11))) {
                return false;
            }
            return true;
        }

        // --- Máscara de Telefone ---
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            let formattedValue = '';

            if (value.length > 0) {
                formattedValue = '(' + value.substring(0, 2);
            }
            if (value.length > 2) {
                formattedValue += ') ';
                if (value.length <= 10) { // Telefone com 8 dígitos
                    formattedValue += value.substring(2, 6) + '-' + value.substring(6, 10);
                } else { // Telefone com 9 dígitos (celular)
                    formattedValue += value.substring(2, 7) + '-' + value.substring(7, 11);
                }
            }
            e.target.value = formattedValue;
        });
        // --- Fim da Máscara de Telefone ---
    });
    </script>
</body>

</html>