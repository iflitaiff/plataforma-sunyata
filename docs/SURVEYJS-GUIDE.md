# Guia Completo: SurveyJS para Plataforma Sunyata

## Visão Geral

Este guia explica como usar SurveyJS Form Library (MIT, gratuito) para criar Canvas dinâmicos na Plataforma Sunyata.

## Arquivos Criados

### 1. JSON Schemas de Canvas

#### `/config/canvas-templates/juridico-geral.json`
Canvas completo para análise jurídica com:
- ✅ 6 campos do Canvas original (tarefa, contexto, entradas, restrições, saída, critérios)
- ✅ Upload de múltiplos arquivos (PDF, DOCX)
- ✅ Validações (required, maxLength)
- ✅ Placeholders com exemplos
- ✅ Descrições/hints
- ✅ Textos em português
- ✅ Bootstrap styling compatível

#### `/config/canvas-templates/docencia-plano-aula.json`
Exemplo de outro vertical (Docência) com:
- ✅ Campos diferentes (disciplina, série, duração)
- ✅ Dropdowns e checkboxes
- ✅ Lógica condicional (visibleIf)
- ✅ Upload de materiais pedagógicos
- ✅ Demonstra reutilização do padrão

### 2. Exemplo de Implementação

#### `/public/areas/juridico/console-example.php`
Interface completa demonstrando:
- ✅ Carregamento de JSON do Canvas
- ✅ Renderização com SurveyJS
- ✅ Upload de arquivos para servidor
- ✅ Chat interativo com Claude
- ✅ Sistema de perguntas/respostas
- ✅ Export de conversas
- ✅ Sidebar com biblioteca de arquivos
- ✅ Lista de conversas

## Estrutura do JSON Schema

### Estrutura Básica

```json
{
  "title": "Nome do Canvas",
  "description": "Descrição breve",
  "logoPosition": "right",
  "pages": [ /* páginas */ ],
  "showQuestionNumbers": "off",
  "questionsOnPageMode": "singlePage",
  "completeText": "Texto do Botão Submit",
  "widthMode": "responsive"
}
```

### Propriedades Principais

| Propriedade | Descrição | Valores |
|-------------|-----------|---------|
| `title` | Título exibido no topo | String |
| `description` | Descrição do formulário | String |
| `pages` | Array de páginas | Array de objetos |
| `showQuestionNumbers` | Mostrar numeração | "on", "off", "onPage" |
| `questionsOnPageMode` | Layout das questões | "singlePage", "questionPerPage" |
| `completeText` | Texto do botão submit | String |
| `widthMode` | Largura responsiva | "static", "responsive" |
| `showProgressBar` | Barra de progresso | "off", "top", "bottom" |
| `progressBarType` | Tipo de progresso | "pages", "questions" |

## Tipos de Campos (Question Types)

### 1. Text Input

```json
{
  "type": "text",
  "name": "nome_campo",
  "title": "Título do Campo",
  "description": "Texto de ajuda",
  "isRequired": true,
  "maxLength": 500,
  "placeholder": "Exemplo de preenchimento",
  "inputType": "text"
}
```

**inputType options:**
- `"text"` - Texto normal
- `"email"` - Email com validação
- `"tel"` - Telefone
- `"url"` - URL com validação
- `"number"` - Numérico
- `"password"` - Senha (oculta)

### 2. Textarea (Comment)

```json
{
  "type": "comment",
  "name": "descricao",
  "title": "Descrição Detalhada",
  "isRequired": false,
  "rows": 5,
  "maxLength": 2000,
  "placeholder": "Digite aqui..."
}
```

### 3. File Upload

```json
{
  "type": "file",
  "name": "documentos",
  "title": "Upload de Arquivos",
  "description": "Anexe documentos relevantes",
  "allowMultiple": true,
  "acceptedTypes": "application/pdf,.pdf,.docx",
  "maxSize": 10485760,
  "storeDataAsText": false,
  "waitForUpload": true,
  "filePlaceholder": "Arraste arquivos aqui"
}
```

**Propriedades importantes:**
- `allowMultiple` - Múltiplos arquivos
- `acceptedTypes` - MIME types + extensões
- `maxSize` - Tamanho em bytes (10MB = 10485760)
- `storeDataAsText` - `false` = usar servidor (recomendado)
- `waitForUpload` - Aguardar upload antes de submit

**MIME Types Comuns:**
```
application/pdf                    → .pdf
application/msword                 → .doc
application/vnd.openxmlformats...  → .docx
application/vnd.ms-excel           → .xls
application/vnd.openxmlformats...  → .xlsx
application/vnd.ms-powerpoint      → .ppt
application/vnd.openxmlformats...  → .pptx
image/jpeg                         → .jpg, .jpeg
image/png                          → .png
```

### 4. Dropdown

```json
{
  "type": "dropdown",
  "name": "categoria",
  "title": "Selecione uma categoria",
  "isRequired": true,
  "choices": [
    { "value": "opt1", "text": "Opção 1" },
    { "value": "opt2", "text": "Opção 2" },
    { "value": "opt3", "text": "Opção 3" }
  ]
}
```

**Formato simplificado:**
```json
"choices": ["Opção 1", "Opção 2", "Opção 3"]
```

### 5. Radio Button Group

```json
{
  "type": "radiogroup",
  "name": "nivel_urgencia",
  "title": "Nível de Urgência",
  "choices": [
    "Baixa",
    "Média",
    "Alta",
    "Urgente"
  ],
  "isRequired": true
}
```

### 6. Checkboxes

```json
{
  "type": "checkbox",
  "name": "areas_interesse",
  "title": "Áreas de Interesse",
  "choices": [
    "Direito Civil",
    "Direito Penal",
    "Direito Trabalhista",
    "Direito Tributário"
  ]
}
```

### 7. Rating

```json
{
  "type": "rating",
  "name": "satisfacao",
  "title": "Satisfação com o serviço",
  "rateMin": 1,
  "rateMax": 5,
  "minRateDescription": "Péssimo",
  "maxRateDescription": "Excelente"
}
```

### 8. Boolean

```json
{
  "type": "boolean",
  "name": "aceite_termos",
  "title": "Aceito os termos de uso",
  "isRequired": true,
  "label": "Li e concordo com os termos"
}
```

### 9. HTML (Informational)

```json
{
  "type": "html",
  "name": "info",
  "html": "<div class='alert alert-info'>Texto informativo com <strong>HTML</strong></div>"
}
```

## Lógica Condicional (Conditional Logic)

### visibleIf - Mostrar/Ocultar Campo

```json
{
  "type": "text",
  "name": "outro_motivo",
  "title": "Especifique o motivo",
  "visibleIf": "{motivo} = 'outro'"
}
```

### enableIf - Habilitar/Desabilitar

```json
{
  "type": "text",
  "name": "cpf_conjuge",
  "title": "CPF do Cônjuge",
  "enableIf": "{estado_civil} = 'casado'"
}
```

### requiredIf - Tornar Obrigatório

```json
{
  "type": "text",
  "name": "nome_empresa",
  "title": "Nome da Empresa",
  "requiredIf": "{tipo_pessoa} = 'juridica'"
}
```

### Operadores de Comparação

```javascript
{age} >= 18           // Maior ou igual
{age} < 65            // Menor que
{name} = 'João'       // Igual
{name} != 'João'      // Diferente
{name} notempty       // Tem valor
{name} empty          // Vazio
{languages} contains 'Português'  // Contém
```

### Operadores Lógicos

```javascript
{age} >= 18 and {aceite_termos} = true
{categoria} = 'A' or {categoria} = 'B'
({age} >= 18 and {age} < 65) or {possui_autorizacao} = true
```

### Funções Built-in

```javascript
age({birthdate})                    // Calcular idade
iif({score} > 7, 'Aprovado', 'Reprovado')  // Condicional
today()                             // Data atual
```

## Validação

### Validações Built-in

```json
{
  "type": "text",
  "name": "email",
  "inputType": "email",
  "isRequired": true,
  "validators": [
    {
      "type": "email",
      "text": "Por favor, insira um email válido"
    }
  ]
}
```

### Tipos de Validadores

#### 1. Numeric Validator

```json
{
  "type": "text",
  "name": "idade",
  "inputType": "number",
  "validators": [
    {
      "type": "numeric",
      "minValue": 18,
      "maxValue": 100,
      "text": "Idade deve estar entre 18 e 100 anos"
    }
  ]
}
```

#### 2. Text Validator (min/max length)

```json
{
  "validators": [
    {
      "type": "text",
      "minLength": 10,
      "maxLength": 500,
      "text": "Texto deve ter entre 10 e 500 caracteres"
    }
  ]
}
```

#### 3. Regex Validator

```json
{
  "validators": [
    {
      "type": "regex",
      "regex": "^[0-9]{3}\\.[0-9]{3}\\.[0-9]{3}-[0-9]{2}$",
      "text": "CPF deve estar no formato 000.000.000-00"
    }
  ]
}
```

#### 4. Expression Validator (custom logic)

```json
{
  "validators": [
    {
      "type": "expression",
      "expression": "{idade} >= 18 or {possui_autorizacao} = true",
      "text": "Você deve ter 18+ anos ou autorização"
    }
  ]
}
```

## Integração com PHP Backend

### 1. Carregar JSON do Banco

```php
// Em produção, carregar de canvas_templates
$stmt = $pdo->prepare("
    SELECT form_config
    FROM canvas_templates
    WHERE slug = ? AND is_active = 1
");
$stmt->execute(['juridico-geral']);
$canvas = $stmt->fetch(PDO::FETCH_ASSOC);

$canvasConfig = json_decode($canvas['form_config'], true);
```

### 2. Processar Upload de Arquivos

JavaScript (survey.onUploadFiles):
```javascript
survey.onUploadFiles.add((_, options) => {
    const formData = new FormData();
    options.files.forEach((file) => {
        formData.append('files[]', file);
    });

    fetch('/api/upload-file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        options.callback(
            options.files.map((file, index) => {
                return {
                    file: file,
                    content: data.files[index].url // URL do arquivo
                };
            })
        );
    });
});
```

PHP (/api/upload-file.php):
```php
<?php
$uploadDir = '/var/www/uploads/user_' . $_SESSION['user_id'] . '/';

$uploadedFiles = [];
foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
    // Validar arquivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);

    // Gerar nome único
    $filename = uniqid() . '_' . basename($_FILES['files']['name'][$key]);
    $filepath = $uploadDir . $filename;

    // Mover arquivo
    if (move_uploaded_file($tmpName, $filepath)) {
        // Salvar no banco
        $stmt = $pdo->prepare("
            INSERT INTO user_files (user_id, filename, filepath, mime_type, size)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_FILES['files']['name'][$key],
            $filepath,
            $mimeType,
            $_FILES['files']['size'][$key]
        ]);

        $uploadedFiles[] = [
            'id' => $pdo->lastInsertId(),
            'url' => '/uploads/user_' . $_SESSION['user_id'] . '/' . $filename,
            'name' => $_FILES['files']['name'][$key],
            'size' => $_FILES['files']['size'][$key]
        ];
    }
}

echo json_encode(['success' => true, 'files' => $uploadedFiles]);
?>
```

### 3. Processar Dados do Formulário

```php
// Receber dados do survey
$formData = json_decode(file_get_contents('php://input'), true);

// Extrair campos
$tarefa = $formData['tarefa'];
$contexto = $formData['contexto'];
$documentos = $formData['documentos']; // Array de URLs

// Processar documentos (extrair texto)
$documentosTexto = [];
foreach ($documentos as $doc) {
    $filepath = $_SERVER['DOCUMENT_ROOT'] . parse_url($doc, PHP_URL_PATH);

    if (pathinfo($filepath, PATHINFO_EXTENSION) === 'pdf') {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filepath);
        $documentosTexto[] = $pdf->getText();
    }
}

// Montar prompt para Claude
$systemPrompt = "Você é um advogado sênior...";
$userPrompt = "TAREFA: {$tarefa}\n\nCONTEXTO: {$contexto}\n\n";
$userPrompt .= "DOCUMENTOS:\n" . implode("\n\n---\n\n", $documentosTexto);

// Enviar para Claude (via ClaudeService)
```

## Customização de Estilo (CSS)

### Variáveis CSS do SurveyJS

```css
/* Sobrescrever cores padrão */
.sd-root-modern {
    --primary: #0d6efd;           /* Cor primária (botões) */
    --primary-light: #e7f3ff;     /* Hover states */
    --background: #ffffff;        /* Background */
    --background-dim: #f8f9fa;    /* Background alternativo */
    --foreground: #212529;        /* Texto principal */
    --foreground-light: #6c757d;  /* Texto secundário */
    --border: #dee2e6;            /* Bordas */
}

/* Customizar botão de submit */
.sd-btn--action {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    font-weight: 600;
    padding: 12px 24px;
}

/* Customizar file upload */
.sd-file__decorator {
    border: 2px dashed #0d6efd;
    border-radius: 8px;
}

/* Customizar textarea */
.sd-input {
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.sd-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
```

## Exemplos Práticos

### Exemplo 1: Canvas com Condicional

```json
{
  "elements": [
    {
      "type": "radiogroup",
      "name": "tipo_acao",
      "title": "Tipo de Ação Jurídica",
      "choices": [
        "Consultoria",
        "Parecer",
        "Petição",
        "Recurso",
        "Outra"
      ],
      "isRequired": true
    },
    {
      "type": "text",
      "name": "tipo_acao_outra",
      "title": "Especifique o tipo de ação",
      "visibleIf": "{tipo_acao} = 'Outra'",
      "isRequired": true
    },
    {
      "type": "text",
      "name": "numero_processo",
      "title": "Número do Processo",
      "visibleIf": "{tipo_acao} = 'Recurso' or {tipo_acao} = 'Petição'",
      "isRequired": true,
      "placeholder": "0000000-00.0000.0.00.0000"
    }
  ]
}
```

### Exemplo 2: Upload com Preview

```json
{
  "type": "file",
  "name": "imagens",
  "title": "Fotos do Local",
  "allowMultiple": true,
  "acceptedTypes": "image/*",
  "maxSize": 5242880,
  "allowImagesPreview": true,
  "imageWidth": "200px",
  "imageHeight": "150px",
  "storeDataAsText": false,
  "waitForUpload": true
}
```

### Exemplo 3: Validação Complexa

```json
{
  "type": "text",
  "name": "valor_causa",
  "title": "Valor da Causa (R$)",
  "inputType": "number",
  "isRequired": true,
  "validators": [
    {
      "type": "numeric",
      "minValue": 0.01,
      "text": "Valor deve ser maior que zero"
    },
    {
      "type": "expression",
      "expression": "{valor_causa} <= 100000 or {possui_aprovacao_diretoria} = true",
      "text": "Valores acima de R$ 100.000 requerem aprovação da diretoria"
    }
  ]
}
```

## Próximos Passos

### Para MVP:

1. **Armazenar JSON no Banco:**
   ```sql
   INSERT INTO canvas_templates (slug, name, vertical, form_config, system_prompt, user_prompt_template)
   VALUES (
       'juridico-geral',
       'Canvas Jurídico Geral',
       'juridico',
       '{ ... JSON do canvas ... }',
       'Você é um advogado sênior...',
       'TAREFA: {{tarefa}}\nCONTEXTO: {{contexto}}\n...'
   );
   ```

2. **Criar Outros Canvas:**
   - Análise de Contrato
   - Elaboração de Petição
   - Recurso Judicial
   - Parecer Jurídico

3. **Implementar APIs:**
   - `/api/upload-file.php` - Upload de arquivos
   - `/api/chat.php` - Conversação com Claude
   - `/api/export-conversation.php` - Export TXT

### Para Futuro (com SurveyJS Creator):

Se conseguir licença do Creator ($579/ano):
- Interface visual drag-drop para criar Canvas
- Salvar JSON automaticamente no banco
- Admin pode criar Canvas sem programar

Alternativas se não conseguir licença:
- CRUD manual (formulário HTML para editar JSON)
- Monaco Editor (editor de JSON com syntax highlighting)
- Continuar criando JSON manualmente

## Links Úteis

- **SurveyJS Documentation:** https://surveyjs.io/form-library/documentation/overview
- **Exemplos Interativos:** https://surveyjs.io/form-library/examples/overview
- **API Reference:** https://surveyjs.io/form-library/documentation/api-reference
- **Conditional Logic:** https://surveyjs.io/form-library/documentation/design-survey/conditional-logic
- **File Upload:** https://surveyjs.io/form-library/examples/file-upload/jquery

## Conclusão

Com estes JSON schemas e o guia, você pode:

✅ Renderizar Canvas dinâmicos com SurveyJS (MIT, gratuito)
✅ Upload de arquivos para servidor
✅ Validação robusta client-side
✅ Lógica condicional complexa
✅ Criar novos Canvas copiando o padrão
✅ Implementar MVP sem SurveyJS Creator
✅ Migrar para Creator se conseguir licença

**Os JSONs criados estão prontos para produção!**
