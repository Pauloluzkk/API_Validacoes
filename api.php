<?php

// Permite requisições de qualquer origem (CORS)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST');

// Captura o método HTTP e os parâmetros
$metodo = $_SERVER['REQUEST_METHOD'];
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

// Função para retornar resposta JSON
function retornarResposta($sucesso, $mensagem, $dados = null) {
    $resposta = [
        'sucesso' => $sucesso,
        'mensagem' => $mensagem,
        'dados' => $dados,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// MÉTODO 1: Validar CPF
function validarCPF() {
    $cpf = isset($_GET['cpf']) ? $_GET['cpf'] : null;
    
    if ($cpf === null || $cpf === '') {
        retornarResposta(false, 'Parâmetro obrigatório: cpf');
    }
    
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        retornarResposta(true, 'CPF validado', [
            'cpf_informado' => $_GET['cpf'],
            'cpf_formatado' => $cpf,
            'valido' => false,
            'motivo' => 'CPF deve conter 11 dígitos'
        ]);
    }
    
    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        retornarResposta(true, 'CPF validado', [
            'cpf_informado' => $_GET['cpf'],
            'cpf_formatado' => formatarCPF($cpf),
            'valido' => false,
            'motivo' => 'CPF com todos os dígitos iguais é inválido'
        ]);
    }
    
    // Valida primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Valida segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem
    $valido = ($cpf[9] == $digito1 && $cpf[10] == $digito2);
    
    retornarResposta(true, 'CPF validado', [
        'cpf_informado' => $_GET['cpf'],
        'cpf_formatado' => formatarCPF($cpf),
        'valido' => $valido,
        'motivo' => $valido ? 'CPF válido' : 'Dígitos verificadores incorretos'
    ]);
}

// Função auxiliar para formatar CPF
function formatarCPF($cpf) {
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}

// MÉTODO 2: Validar Email
function validarEmail() {
    $email = isset($_GET['email']) ? $_GET['email'] : null;
    
    if ($email === null || $email === '') {
        retornarResposta(false, 'Parâmetro obrigatório: email');
    }
    
    // Remove espaços em branco
    $email = trim($email);
    
    // Validação básica de formato
    $valido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    
    // Análise adicional
    $partes = explode('@', $email);
    $usuario = isset($partes[0]) ? $partes[0] : '';
    $dominio = isset($partes[1]) ? $partes[1] : '';
    
    $detalhes = [
        'email_informado' => $_GET['email'],
        'email_limpo' => $email,
        'valido' => $valido,
        'usuario' => $usuario,
        'dominio' => $dominio
    ];
    
    if (!$valido) {
        $motivos = [];
        if (!strpos($email, '@')) {
            $motivos[] = 'Falta o símbolo @';
        }
        if (strlen($usuario) < 1) {
            $motivos[] = 'Nome de usuário vazio';
        }
        if (strlen($dominio) < 3) {
            $motivos[] = 'Domínio inválido';
        }
        if (!strpos($dominio, '.')) {
            $motivos[] = 'Domínio sem extensão (.com, .br, etc)';
        }
        $detalhes['motivos'] = $motivos;
    } else {
        $detalhes['motivo'] = 'Email válido';
    }
    
    retornarResposta(true, 'Email validado', $detalhes);
}

// MÉTODO 3: Validar Telefone Brasileiro
function validarTelefone() {
    $telefone = isset($_GET['telefone']) ? $_GET['telefone'] : null;
    
    if ($telefone === null || $telefone === '') {
        retornarResposta(false, 'Parâmetro obrigatório: telefone');
    }
    
    // Remove caracteres não numéricos
    $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
    
    $valido = false;
    $tipo = '';
    $formatado = '';
    $motivo = '';
    
    $tamanho = strlen($telefone_limpo);
    
    // Telefone com DDD (10 ou 11 dígitos)
    if ($tamanho == 11) {
        // Celular com 9 dígitos: (XX) 9XXXX-XXXX
        $valido = true;
        $tipo = 'Celular';
        $formatado = '(' . substr($telefone_limpo, 0, 2) . ') ' . 
                     substr($telefone_limpo, 2, 5) . '-' . 
                     substr($telefone_limpo, 7, 4);
        $motivo = 'Telefone celular válido';
    } elseif ($tamanho == 10) {
        // Fixo com 8 dígitos: (XX) XXXX-XXXX
        $valido = true;
        $tipo = 'Fixo';
        $formatado = '(' . substr($telefone_limpo, 0, 2) . ') ' . 
                     substr($telefone_limpo, 2, 4) . '-' . 
                     substr($telefone_limpo, 6, 4);
        $motivo = 'Telefone fixo válido';
    } else {
        $motivo = 'Telefone deve ter 10 dígitos (fixo) ou 11 dígitos (celular) incluindo DDD';
    }
    
    retornarResposta(true, 'Telefone validado', [
        'telefone_informado' => $_GET['telefone'],
        'telefone_limpo' => $telefone_limpo,
        'valido' => $valido,
        'tipo' => $tipo,
        'formatado' => $formatado,
        'quantidade_digitos' => $tamanho,
        'motivo' => $motivo
    ]);
}

// MÉTODO 4: Validar Força da Senha
function validarSenha() {
    $senha = isset($_GET['senha']) ? $_GET['senha'] : null;
    
    if ($senha === null || $senha === '') {
        retornarResposta(false, 'Parâmetro obrigatório: senha');
    }
    
    $tamanho = strlen($senha);
    $pontos = 0;
    $criterios = [];
    
    // Critério 1: Tamanho (mínimo 8 caracteres)
    if ($tamanho >= 8) {
        $pontos += 25;
        $criterios['tamanho'] = ['atendido' => true, 'mensagem' => 'Possui 8 ou mais caracteres'];
    } else {
        $criterios['tamanho'] = ['atendido' => false, 'mensagem' => 'Precisa ter no mínimo 8 caracteres'];
    }
    
    // Critério 2: Letras maiúsculas
    if (preg_match('/[A-Z]/', $senha)) {
        $pontos += 25;
        $criterios['maiusculas'] = ['atendido' => true, 'mensagem' => 'Contém letras maiúsculas'];
    } else {
        $criterios['maiusculas'] = ['atendido' => false, 'mensagem' => 'Adicione letras maiúsculas'];
    }
    
    // Critério 3: Letras minúsculas
    if (preg_match('/[a-z]/', $senha)) {
        $pontos += 25;
        $criterios['minusculas'] = ['atendido' => true, 'mensagem' => 'Contém letras minúsculas'];
    } else {
        $criterios['minusculas'] = ['atendido' => false, 'mensagem' => 'Adicione letras minúsculas'];
    }
    
    // Critério 4: Números
    if (preg_match('/[0-9]/', $senha)) {
        $pontos += 15;
        $criterios['numeros'] = ['atendido' => true, 'mensagem' => 'Contém números'];
    } else {
        $criterios['numeros'] = ['atendido' => false, 'mensagem' => 'Adicione números'];
    }
    
    // Critério 5: Caracteres especiais
    if (preg_match('/[^A-Za-z0-9]/', $senha)) {
        $pontos += 10;
        $criterios['especiais'] = ['atendido' => true, 'mensagem' => 'Contém caracteres especiais'];
    } else {
        $criterios['especiais'] = ['atendido' => false, 'mensagem' => 'Adicione caracteres especiais (!@#$%&*)'];
    }
    
    // Determina a força da senha
    $forca = '';
    $cor = '';
    if ($pontos < 40) {
        $forca = 'Fraca';
        $cor = 'vermelho';
    } elseif ($pontos < 70) {
        $forca = 'Média';
        $cor = 'amarelo';
    } else {
        $forca = 'Forte';
        $cor = 'verde';
    }
    
    retornarResposta(true, 'Senha analisada', [
        'senha_informada' => str_repeat('*', $tamanho), // Oculta a senha
        'tamanho' => $tamanho,
        'pontuacao' => $pontos,
        'forca' => $forca,
        'cor_indicativa' => $cor,
        'criterios_atendidos' => $criterios,
        'recomendacao' => $pontos >= 70 ? 'Senha adequada para uso' : 'Melhore sua senha seguindo os critérios'
    ]);
}

// Roteamento das requisições
if ($metodo == 'GET') {
    switch($acao) {
        case 'validar-cpf':
            validarCPF();
            break;
        case 'validar-email':
            validarEmail();
            break;
        case 'validar-telefone':
            validarTelefone();
            break;
        case 'validar-senha':
            validarSenha();
            break;
        case '':
            retornarResposta(true, 'API de Validações funcionando!', [
                'metodos_disponiveis' => [
                    'validar-cpf',
                    'validar-email',
                    'validar-telefone',
                    'validar-senha'
                ],
                'documentacao' => 'Use o parâmetro ?acao= para acessar os métodos de validação',
                'exemplo' => 'api.php?acao=validar-email&email=teste@email.com'
            ]);
            break;
        default:
            retornarResposta(false, 'Ação não encontrada. Métodos disponíveis: validar-cpf, validar-email, validar-telefone, validar-senha');
    }
} else {
    retornarResposta(false, 'Método HTTP não suportado. Use GET');
}
?>