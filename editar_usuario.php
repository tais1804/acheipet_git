<?php
session_start();
include "conexao.php";
include "dados_usuario.php"; // Presume que este arquivo define a variável $usuario com os dados atuais do usuário logado.

// Inicializa as variáveis de mensagem para o HTML
$mensagem = "";
$tipo_mensagem = "";

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


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // É uma boa prática usar o operador null coalescing para atribuir vazio se o POST não existir
    $nome = $_POST["nome"] ?? '';
    $email = $_POST["email"] ?? '';
    $telefone = $_POST["telefone"] ?? '';
    $cpf = $_POST["cpf"] ?? '';
    $endereco = $_POST["endereco"] ?? '';

    $id_usuario = $_SESSION["id_usuario"] ?? null; // Garante que $id_usuario existe

    // Validação inicial: ID do usuário deve existir na sessão
    if (is_null($id_usuario)) {
        $mensagem = "Erro: ID do usuário não encontrado na sessão.";
        $tipo_mensagem = "danger";
    }

    // Validação do CPF no lado do servidor
    if (empty($mensagem) && !validarCPF($cpf)) {
        $mensagem = "O CPF informado é inválido. Por favor, digite um CPF válido.";
        $tipo_mensagem = "danger";
    }

    // Se o CPF é válido e não há mensagens de erro anteriores
    if (empty($mensagem)) {
        // --- Tratamento do upload da foto ---
        $foto_caminho = $usuario["foto"] ?? null; // Mantém a foto atual por padrão ou null

        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
            $diretorio_uploads = "uploads/fotos_perfil/"; // Diretório mais específico, se você tiver um para perfis
            
            // Cria o diretório se não existir
            if (!is_dir($diretorio_uploads)) {
                mkdir($diretorio_uploads, 0777, true); 
            }

            $nome_arquivo_original = basename($_FILES["foto"]["name"]);
            $extensao_arquivo = pathinfo($nome_arquivo_original, PATHINFO_EXTENSION);
            $nome_arquivo_unico = uniqid() . "." . $extensao_arquivo; // Nome único para evitar conflitos
            $caminho_destino = $diretorio_uploads . $nome_arquivo_unico;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $caminho_destino)) {
                $foto_caminho = $caminho_destino; 
            } else {
                $mensagem = "Erro ao mover o arquivo de foto para o servidor.";
                $tipo_mensagem = "danger";
            }
        } elseif (isset($_FILES["foto"]) && $_FILES["foto"]["error"] != UPLOAD_ERR_NO_FILE) {
            $mensagem = "Erro no upload da foto: Código " . $_FILES["foto"]["error"];
            $tipo_mensagem = "danger";
        }
        // --- Fim do Tratamento do upload da foto ---
    }


    // Se não houver mensagens de erro até agora, procede com a atualização
    if (empty($mensagem)) {
        try {
            // **Verificação de e-mail duplicado**
            // IMPORTANTE: Ao editar, o e-mail pode ser o mesmo do usuário atual.
            // A consulta deve verificar se o e-mail já existe *para outro usuário*.
            $stmt_check_email = $conexao->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ? AND id_usuario != ?");
            $stmt_check_email->execute([$email, $id_usuario]);
            $email_existe = $stmt_check_email->fetchColumn();

            // **Verificação de CPF duplicado**
            // IMPORTANTE: O mesmo vale para o CPF.
            $stmt_check_cpf = $conexao->prepare("SELECT COUNT(*) FROM Usuarios WHERE cpf = ? AND id_usuario != ?");
            $stmt_check_cpf->execute([$cpf, $id_usuario]);
            $cpf_existe = $stmt_check_cpf->fetchColumn();

            if ($email_existe > 0) {
                $mensagem = "Este e-mail já está cadastrado por outro usuário. Por favor, use outro e-mail.";
                $tipo_mensagem = "warning";
            } elseif ($cpf_existe > 0) {
                $mensagem = "Este CPF já está cadastrado por outro usuário. Por favor, verifique seus dados.";
                $tipo_mensagem = "warning";
            } else {
                // Atualiza os dados no banco
                $sql = "UPDATE Usuarios SET nome = :nome, email = :email, telefone = :telefone, cpf = :cpf, endereco = :endereco, foto = :foto WHERE id_usuario = :id";
                $stmt = $conexao->prepare($sql);
                $sucesso = $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':telefone' => $telefone,
                    ':cpf' => $cpf,
                    ':endereco' => $endereco,
                    ':foto' => $foto_caminho,
                    ':id' => $id_usuario // Usa a variável $id_usuario já validada
                ]);

                if ($sucesso) {
                    // Após a atualização, redireciona e sai.
                    // Para garantir que os dados atualizados sejam exibidos,
                    // pode ser necessário recarregar $usuario do banco ou
                    // passar uma mensagem de sucesso via sessão.
                    $_SESSION['mensagem_sucesso'] = "Dados atualizados com sucesso!";
                    header("Location: perfil_usuario.php");
                    exit();
                } else {
                    $mensagem = "Erro ao atualizar os dados no banco de dados.";
                    $tipo_mensagem = "danger";
                }
            }
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar o perfil: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Se houver uma mensagem de sucesso na sessão, exibe-a e a limpa
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = $_SESSION['mensagem_sucesso'];
    $tipo_mensagem = "success";
    unset($_SESSION['mensagem_sucesso']);
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Perfil - Achei Pet</title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-5">
        <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center" role="alert">
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm"
            style="max-width: 600px; margin: auto;">
            <h2 class="h3 text-center">Editar Perfil</h2>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome"
                    value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone:</label>
                <input type="text" class="form-control" id="telefone" name="telefone"
                    value="<?= htmlspecialchars($usuario['telefone']) ?>"
                    placeholder="(XX) XXXX-XXXX ou (XX) XXXXX-XXXX">
            </div>

            <div class="mb-3">
                <label for="cpf" class="form-label">CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf"
                    value="<?= htmlspecialchars($usuario['cpf']) ?>">
            </div>

            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço:</label>
                <input type="text" class="form-control" id="endereco" name="endereco"
                    value="<?= htmlspecialchars($usuario['endereco']) ?>">
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto de Perfil:</label>
                <input type="file" class="form-control" id="foto" name="foto">
                <?php if (!empty($usuario['foto'])): ?>
                <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto atual" class="img-thumbnail mt-2"
                    style="width: 100px;">
                <?php endif; ?>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="perfil_usuario.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cpfInput = document.getElementById('cpf');
        const telefoneInput = document.getElementById('telefone');

        // --- Máscara de CPF ---
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });

        // --- Validação visual simples ao sair do campo (onblur) para CPF ---
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

        // --- Função JavaScript para validar CPF ---
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
                // Se o 9º dígito for o primeiro do número (ex: 9XXXX-XXXX)
                if (value.length <= 10) { // Telefone com 8 dígitos
                    formattedValue += value.substring(2, 6) + '-' + value.substring(6, 10);
                } else { // Telefone com 9 dígitos (celular)
                    formattedValue += value.substring(2, 7) + '-' + value.substring(7, 11);
                }
            }
            e.target.value = formattedValue;
        });
        // --- Fim da Máscara de Telefone ---

        // --- Aplicar máscaras no carregamento da página para valores pré-preenchidos ---
        // Isso é importante para que o CPF e telefone já carregados do banco apareçam formatados
        // simula um evento 'input' para aplicar a máscara nos valores existentes
        cpfInput.dispatchEvent(new Event('input'));
        telefoneInput.dispatchEvent(new Event('input'));
    });
    </script>
</body>

</html>