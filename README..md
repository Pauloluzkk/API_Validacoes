# Documentação da API de Validações

## Informações Gerais

- **URL Base**: `http://localhost:8000/api.php`
- **Método HTTP**: GET
- **Formato de Resposta**: JSON
- **Codificação**: UTF-8
- **Tema**: Validação de dados brasileiros (CPF, Email, Telefone e Senha)

## Estrutura de Resposta Padrão

Todas as respostas da API seguem o seguinte formato:

```json
{
  "sucesso": true/false,
  "mensagem": "Descrição do resultado",
  "dados": { ... },
  "timestamp": "2024-11-19 15:30:00"
}
```

---

## Métodos Disponíveis

### 1. Validar CPF

Valida se um CPF brasileiro é válido através do algoritmo de verificação de dígitos.

**Endpoint**: `?acao=validar-cpf`

**Parâmetros**:
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| cpf | string | Sim | CPF a ser validado (com ou sem formatação) |

**Exemplo de Chamada**:
```
GET /api.php?acao=validar-cpf&cpf=123.456.789-09
```

**Resposta de Sucesso (CPF Válido)**:
```json
{
  "sucesso": true,
  "mensagem": "CPF validado",
  "dados": {
    "cpf_informado": "123.456.789-09",
    "cpf_formatado": "123.456.789-09",
    "valido": true,
    "motivo": "CPF válido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Resposta de Sucesso (CPF Inválido)**:
```json
{
  "sucesso": true,
  "mensagem": "CPF validado",
  "dados": {
    "cpf_informado": "111.111.111-11",
    "cpf_formatado": "111.111.111-11",
    "valido": false,
    "motivo": "CPF com todos os dígitos iguais é inválido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Validações realizadas**:
- Verifica se tem 11 dígitos
- Valida os dois dígitos verificadores
- Recusa CPFs com todos os dígitos iguais
- Aceita CPF com ou sem formatação

**Exemplos de teste**:
- CPF Válido: `123.456.789-09` ou `12345678909`
- CPF Inválido: `111.111.111-11`
- CPF Inválido: `123.456.789-00`

---

### 2. Validar Email

Valida se um endereço de email possui formato válido.

**Endpoint**: `?acao=validar-email`

**Parâmetros**:
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| email | string | Sim | Endereço de email a ser validado |

**Exemplo de Chamada**:
```
GET /api.php?acao=validar-email&email=usuario@exemplo.com.br
```

**Resposta de Sucesso (Email Válido)**:
```json
{
  "sucesso": true,
  "mensagem": "Email validado",
  "dados": {
    "email_informado": "usuario@exemplo.com.br",
    "email_limpo": "usuario@exemplo.com.br",
    "valido": true,
    "usuario": "usuario",
    "dominio": "exemplo.com.br",
    "motivo": "Email válido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Resposta de Sucesso (Email Inválido)**:
```json
{
  "sucesso": true,
  "mensagem": "Email validado",
  "dados": {
    "email_informado": "emailinvalido",
    "email_limpo": "emailinvalido",
    "valido": false,
    "usuario": "",
    "dominio": "",
    "motivos": [
      "Falta o símbolo @",
      "Domínio inválido"
    ]
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Validações realizadas**:
- Verifica presença do símbolo @
- Valida nome de usuário
- Valida domínio
- Verifica extensão do domínio (.com, .br, etc)

**Exemplos de teste**:
- Email Válido: `teste@email.com`
- Email Válido: `joao.silva@empresa.com.br`
- Email Inválido: `email.sem.arroba.com`
- Email Inválido: `@semUsuario.com`

---

### 3. Validar Telefone Brasileiro

Valida telefones brasileiros (fixos e celulares) com DDD.

**Endpoint**: `?acao=validar-telefone`

**Parâmetros**:
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| telefone | string | Sim | Número de telefone (com ou sem formatação) |

**Exemplo de Chamada**:
```
GET /api.php?acao=validar-telefone&telefone=(11)98765-4321
```

**Resposta de Sucesso (Celular)**:
```json
{
  "sucesso": true,
  "mensagem": "Telefone validado",
  "dados": {
    "telefone_informado": "(11)98765-4321",
    "telefone_limpo": "11987654321",
    "valido": true,
    "tipo": "Celular",
    "formatado": "(11) 98765-4321",
    "quantidade_digitos": 11,
    "motivo": "Telefone celular válido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Resposta de Sucesso (Fixo)**:
```json
{
  "sucesso": true,
  "mensagem": "Telefone validado",
  "dados": {
    "telefone_informado": "1133334444",
    "telefone_limpo": "1133334444",
    "valido": true,
    "tipo": "Fixo",
    "formatado": "(11) 3333-4444",
    "quantidade_digitos": 10,
    "motivo": "Telefone fixo válido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Validações realizadas**:
- Identifica telefone fixo (10 dígitos) ou celular (11 dígitos)
- Formata automaticamente o número
- Aceita telefone com ou sem formatação

**Formatos aceitos**:
- Celular: `(11) 98765-4321`, `11987654321`, `(11)98765-4321`
- Fixo: `(11) 3333-4444`, `1133334444`, `(11)3333-4444`

**Exemplos de teste**:
- Celular Válido: `11987654321`
- Fixo Válido: `1133334444`
- Inválido: `119876` (poucos dígitos)

---

### 4. Validar Força da Senha

Analisa a força de uma senha baseada em critérios de segurança.

**Endpoint**: `?acao=validar-senha`

**Parâmetros**:
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| senha | string | Sim | Senha a ser analisada |

**Exemplo de Chamada**:
```
GET /api.php?acao=validar-senha&senha=Senha@Forte123
```

**Resposta de Sucesso**:
```json
{
  "sucesso": true,
  "mensagem": "Senha analisada",
  "dados": {
    "senha_informada": "*************",
    "tamanho": 13,
    "pontuacao": 100,
    "forca": "Forte",
    "cor_indicativa": "verde",
    "criterios_atendidos": {
      "tamanho": {
        "atendido": true,
        "mensagem": "Possui 8 ou mais caracteres"
      },
      "maiusculas": {
        "atendido": true,
        "mensagem": "Contém letras maiúsculas"
      },
      "minusculas": {
        "atendido": true,
        "mensagem": "Contém letras minúsculas"
      },
      "numeros": {
        "atendido": true,
        "mensagem": "Contém números"
      },
      "especiais": {
        "atendido": true,
        "mensagem": "Contém caracteres especiais"
      }
    },
    "recomendacao": "Senha adequada para uso"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

**Critérios de pontuação**:
| Critério | Pontos | Descrição |
|----------|--------|-----------|
| Tamanho (≥8 caracteres) | 25 | Mínimo recomendado de caracteres |
| Letras maiúsculas | 25 | Pelo menos uma letra maiúscula (A-Z) |
| Letras minúsculas | 25 | Pelo menos uma letra minúscula (a-z) |
| Números | 15 | Pelo menos um número (0-9) |
| Caracteres especiais | 10 | Símbolos como !@#$%&* |

**Classificação da força**:
- **Fraca** (0-39 pontos): Vermelho
- **Média** (40-69 pontos): Amarelo
- **Forte** (70-100 pontos): Verde

**Exemplos de teste**:
- Senha Fraca: `senha123`
- Senha Média: `Senha123`
- Senha Forte: `Senha@Forte123`

---

## Como Testar a API

### Passo 1: Iniciar o Servidor

Abra o terminal na pasta onde salvou o arquivo `api.php` e execute:

```bash
php -S localhost:8000
```

### Passo 2: Testar no Navegador

Cole as URLs diretamente no navegador:

**Teste 1 - Validar CPF**:
```
http://localhost:8000/api.php?acao=validar-cpf&cpf=123.456.789-09
```

**Teste 2 - Validar Email**:
```
http://localhost:8000/api.php?acao=validar-email&email=teste@email.com
```

**Teste 3 - Validar Telefone**:
```
http://localhost:8000/api.php?acao=validar-telefone&telefone=11987654321
```

**Teste 4 - Validar Senha**:
```
http://localhost:8000/api.php?acao=validar-senha&senha=Senha@123
```

### Passo 3: Usando cURL (Terminal)

```bash
curl "http://localhost:8000/api.php?acao=validar-cpf&cpf=12345678909"
```

### Passo 4: Usando JavaScript

```javascript
fetch('http://localhost:8000/api.php?acao=validar-email&email=teste@email.com')
  .then(response => response.json())
  .then(data => {
    console.log('Email válido?', data.dados.valido);
  });
```

---

## Casos de Uso Práticos

### 1. Formulário de Cadastro
Use a API para validar dados em tempo real enquanto o usuário preenche um formulário:
- CPF no campo de documento
- Email no campo de contato
- Telefone no campo de celular
- Senha ao criar conta

### 2. Sistema de Login
Valide a força da senha durante o cadastro de novos usuários.

### 3. Sistema de CRM
Valide dados de clientes antes de salvar no banco de dados.

---

## Códigos de Resposta

A API sempre retorna status **200 OK**, mas o campo `sucesso` indica se a operação foi bem-sucedida:

- `"sucesso": true` - Requisição processada (validação realizada)
- `"sucesso": false` - Erro nos parâmetros (falta parâmetro obrigatório)

---

## Possíveis Erros

### Erro 1: Parâmetro faltando
```json
{
  "sucesso": false,
  "mensagem": "Parâmetro obrigatório: cpf",
  "dados": null,
  "timestamp": "2024-11-19 15:30:00"
}
```

### Erro 2: Ação inválida
```json
{
  "sucesso": false,
  "mensagem": "Ação não encontrada. Métodos disponíveis: validar-cpf, validar-email, validar-telefone, validar-senha",
  "dados": null,
  "timestamp": "2024-11-19 15:30:00"
}
```

---

## Limitações e Observações

- **Segurança**: A senha é ocultada na resposta para proteção
- **CPF**: Aceita formato com ou sem pontuação
- **Telefone**: Apenas telefones brasileiros com DDD
- **Email**: Validação de formato, não verifica se o email existe
- **Método HTTP**: Apenas GET é suportado
- **Autenticação**: Não há autenticação (API pública para fins educacionais)

---

## Instalação Rápida

**Requisitos**:
- PHP 7.4 ou superior

**Passos**:
1. Salve o código em um arquivo chamado `api.php`
2. Abra o terminal na pasta do arquivo
3. Execute: `php -S localhost:8000`
4. Acesse: `http://localhost:8000/api.php`

---

## Exemplos Completos de JSON

### CPF Válido
```json
{
  "sucesso": true,
  "mensagem": "CPF validado",
  "dados": {
    "cpf_informado": "123.456.789-09",
    "cpf_formatado": "123.456.789-09",
    "valido": true,
    "motivo": "CPF válido"
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

### Email Inválido
```json
{
  "sucesso": true,
  "mensagem": "Email validado",
  "dados": {
    "email_informado": "emailinvalido",
    "email_limpo": "emailinvalido",
    "valido": false,
    "usuario": "",
    "dominio": "",
    "motivos": ["Falta o símbolo @"]
  },
  "timestamp": "2024-11-19 15:30:00"
}
```

---

**Versão**: 1.0  
**Data**: 19/11/2024  
**Linguagem**: PHP  
**Autor**: [Seu Nome]

---

## Suporte e Dúvidas

- Verifique se o servidor está rodando: `php -S localhost:8000`
- Teste a URL base primeiro: `http://localhost:8000/api.php`
- Confira se os parâmetros estão corretos
- Use a documentação como referência