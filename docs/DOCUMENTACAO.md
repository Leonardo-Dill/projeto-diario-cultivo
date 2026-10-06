# Documentação técnica — Diário de Cultivo

Aplicação web em PHP puro (sem framework) com MySQL/MariaDB, acessada por páginas renderizadas no servidor. Cada arquivo em `public/` é uma página ou um endpoint. Para instalar o projeto, veja o [README](../README.md).

## Sumário

1. [Visão geral](#1-visão-geral)
2. [Banco de dados](#2-banco-de-dados)
3. [Configuração e conexão](#3-configuração-e-conexão)
4. [Mapa de páginas](#4-mapa-de-páginas)
5. [Módulos](#5-módulos)
6. [Fluxos principais](#6-fluxos-principais)
7. [Segurança](#7-segurança)
8. [Limitações conhecidas](#8-limitações-conhecidas)
9. [Como estender](#9-como-estender)

---

## 1. Visão geral

| Item | Detalhe |
|---|---|
| Linguagem | PHP 8.0+ (desenvolvido com 8.2) |
| Banco | MySQL / MariaDB, acessado com PDO |
| Dependência | `vlucas/phpdotenv` (leitura do `.env`), instalada via Composer |
| Servidor | Apache do XAMPP |
| Front-end | HTML renderizado no servidor e um único `style.css` (tema escuro); JavaScript só em `confirm()` |

### Entidades

```
strain 1 ──── N planta 1 ──── N manejo
                      └────── N fotos
```

- **strain:** a variedade cultivada (nome, características, semanas de floração).
- **planta:** um indivíduo cultivado, ligado a uma strain, com as datas das fases e o rendimento.
- **manejo:** uma ação registrada em uma planta em uma data (rega, poda, adubação etc.).
- **fotos:** uma foto de uma planta em uma data. O banco guarda só o nome do arquivo.

### Estrutura de pastas

```
.env / .env.exemple     configuração do banco (o .env não é versionado)
.htaccess               bloqueia o acesso web ao .env
composer.json / .lock   dependências
config/
  db/conexao.php        cria a conexão PDO ($conexao)
  helpers.php           funções h() e dataValida()
database/schema.sql     estrutura do banco
docs/                   esta documentação
imagem/                 pasta sem uso (vazia)
public/                 páginas servidas pelo Apache
  index.php             página inicial
  css/style.css         estilos
  strain/ planta/ manejo/ foto/   módulos
  uploads/              fotos enviadas (conteúdo ignorado pelo git)
vendor/                 dependências do Composer (não versionado)
```

---

## 2. Banco de dados

Definido em [database/schema.sql](../database/schema.sql). Banco `diario_cultivo`, charset `utf8mb4`, engine InnoDB.

### strain

| Coluna | Tipo | Observação |
|---|---|---|
| `id` | int, PK, auto_increment | |
| `nome` | varchar(255), NOT NULL | |
| `caracteristicas` | text | |
| `floracao_semanas` | int | semanas de floração da strain |

### planta

| Coluna | Tipo | Observação |
|---|---|---|
| `id` | int, PK, auto_increment | |
| `strain_id` | int, FK → `strain.id` | sem `ON DELETE`: apagar uma strain com plantas falha |
| `tipo_cultivo` | text, NOT NULL | descrição livre do ambiente e do método |
| `germinacao`, `plantinha`, `vegetativo`, `floracao`, `colheita` | date, NULL | data de início de cada fase |
| `rendimento` | float, NULL | gramas, peso molhado |

### manejo

| Coluna | Tipo | Observação |
|---|---|---|
| `id` | int, PK | |
| `planta_id` | int, FK → `planta.id` | `ON DELETE CASCADE` |
| `data` | date | |
| `tipo` | varchar(255) | texto livre (a tela sugere valores comuns) |
| `observacoes` | text, NULL | |

### fotos

| Coluna | Tipo | Observação |
|---|---|---|
| `id` | int, PK | |
| `planta_id` | int, FK → `planta.id` | `ON DELETE CASCADE` |
| `data` | date | data da foto |
| `imagem` | varchar(255) | nome do arquivo em `public/uploads/` |

O `ON DELETE CASCADE` remove os registros de `manejo` e `fotos`, mas **não** os arquivos de imagem. Quem apaga os arquivos é o código de [delete_planta.php](../public/planta/delete_planta.php).

---

## 3. Configuração e conexão

### `.env`

Copie `.env.exemple` para `.env` e preencha:

| Variável | Significado |
|---|---|
| `db_host` | servidor do banco (`localhost`) |
| `db_name` | nome do banco (`diario_cultivo`) |
| `db_user` | usuário |
| `db_pass` | senha (vazia no XAMPP padrão) |

### [config/db/conexao.php](../config/db/conexao.php)

Carrega o `vendor/autoload.php` e o `.env` (`Dotenv::createImmutable`) e cria a variável global `$conexao` (um `PDO`).
- O charset é `utf8mb4` e `PDO::ERRMODE_EXCEPTION` está ativo, então erros de SQL viram `PDOException`.
- Se a conexão falhar, o script exibe a mensagem do erro e termina (`die`).
- Toda página inclui este arquivo com `require`/`require_once` e usa `$conexao`.

### [config/helpers.php](../config/helpers.php)

| Função | O que faz |
|---|---|
| `h($valor): string` | escapa o valor com `htmlspecialchars`. Use em toda saída em HTML. |
| `dataValida(string $data): bool` | aceita só datas reais no formato `Y-m-d` (rejeita, por exemplo, `2026-02-31`). |

Hoje só os módulos de manejo e a página de detalhe da planta usam esse arquivo. Os outros chamam `htmlspecialchars` direto ou definem `h()` localmente (veja as [limitações](#8-limitações-conhecidas)).

---

## 4. Mapa de páginas

URL base: `http://localhost/Diario/public/`

| Caminho | Método | Função |
|---|---|---|
| `index.php` | GET | página inicial com os atalhos para strains e plantas |
| `strain/listar.php` | GET | lista as strains |
| `strain/create_strain.php` | GET / POST | formulário e criação de strain |
| `strain/update_strain.php?id=` | GET / POST | formulário e edição de strain |
| `strain/delete_strain.php?id=` | GET | apaga a strain e redireciona |
| `planta/read_planta.php` | GET | lista as plantas |
| `planta/create_planta.php` | GET / POST | formulário e criação de planta |
| `planta/ver_planta.php?id=` | GET | detalhe da planta |
| `planta/update_planta.php?id=` | GET / POST | formulário e edição de planta |
| `planta/delete_planta.php?id=` | GET | apaga a planta, os arquivos das fotos e redireciona |
| `manejo/create_manejo.php?planta_id=` | GET / POST | registra um manejo |
| `manejo/update_manejo.php?id=` | GET / POST | edita um manejo |
| `manejo/delete_manejo.php` | POST (`id`, `planta_id`) | apaga um manejo |
| `foto/galeria.php?planta_id=` | GET / POST | galeria e upload de fotos |
| `foto/delete_foto.php` | POST (`id`, `planta_id`) | apaga uma foto |

Páginas que também processam o formulário (GET e POST) seguem o padrão: no POST validam, gravam e redirecionam; se houver erro, mostram o formulário de novo com a mensagem.

---

## 5. Módulos

### 5.1 Página inicial — [public/index.php](../public/index.php)

HTML estático com dois cards: "Gerenciar strains" e "Gerenciar plantas".

### 5.2 Strain — `public/strain/`

| Arquivo | Descrição |
|---|---|
| `listar.php` | `SELECT * FROM strain` e um card por strain, com Editar e Apagar (o Apagar pede `confirm()`). |
| `create_strain.php` | Recebe `nome`, `caracteristicas` e `floracao_semanas` e insere. Redireciona para `listar.php`. |
| `update_strain.php` | No GET busca a strain por `id` e preenche o formulário. No POST atualiza os três campos e redireciona. |
| `delete_strain.php` | `DELETE FROM strain WHERE id = ?` e redireciona. Falha se existirem plantas ligadas (chave estrangeira). |

### 5.3 Planta — `public/planta/`

| Arquivo | Descrição |
|---|---|
| `read_planta.php` | Lista as plantas (`JOIN` com `strain`). Cada card tem Detalhes, Fotos, Editar e Apagar. |
| `create_planta.php` | Formulário com strain (select), tipo de cultivo, datas das fases e rendimento. Campos de data e rendimento vazios são gravados como `NULL`. |
| `ver_planta.php` | Página de detalhe, descrita abaixo. |
| `update_planta.php` | Edita todos os campos, incluindo a strain. Valida datas, strain e rendimento. Campo vazio vira `NULL`, então dá para limpar um valor. |
| `delete_planta.php` | Busca os nomes das fotos, apaga a planta e remove os arquivos do disco. |

**`ver_planta.php`** calcula e mostra:
- **Fase atual:** a fase mais avançada que tem data preenchida, na ordem germinação → plantinha → vegetativo → floração → colheita.
- **Dias de vida:** da germinação até hoje, ou até a data de colheita se a planta já foi colhida. Sem germinação, não mostra.
- **Fotos:** contagem total e prévia das 4 mais recentes (constante `FOTOS_NA_PREVIA`).
- **Manejo:** todos os registros, do mais recente para o mais antigo, com botões de editar e apagar.

### 5.4 Manejo — `public/manejo/`

| Arquivo | Descrição |
|---|---|
| `create_manejo.php` | Valida `data` e `tipo` (obrigatório, até 255 caracteres) e insere para a `planta_id` informada. |
| `update_manejo.php` | Edita data, tipo e observações. A planta vem sempre do registro salvo, nunca do formulário. |
| `delete_manejo.php` | Apaga o manejo por POST. A condição `id = ? AND planta_id = ?` impede apagar o registro de outra planta. |
| `form_manejo.php` | Formulário compartilhado por create e update. Só funciona incluído por eles (acesso direto devolve 404). |
| `tipos_manejo.php` | Constante `TIPOS_MANEJO`, a lista de sugestões do campo "tipo". O campo aceita texto livre. |

### 5.5 Fotos — `public/foto/`

**`galeria.php`** mostra as fotos de uma planta e recebe o upload. Constantes:

| Constante | Valor |
|---|---|
| `PASTA_UPLOADS` | `public/uploads/` |
| `TAMANHO_MAXIMO` | 8 MB |
| `TIPOS_PERMITIDOS` | `image/jpeg` → jpg, `image/png` → png, `image/webp` → webp |

Validações do upload, nesta ordem: arquivo enviado, tamanho, erro de upload do PHP, data válida e tipo real do arquivo (`finfo` mais `getimagesize`, sem confiar na extensão enviada). O nome salvo é aleatório (`bin2hex(random_bytes(16))` mais a extensão). Se o `INSERT` falhar depois de o arquivo ser movido, o arquivo é apagado.

**`delete_foto.php`** apaga o registro e depois o arquivo. Usa `basename()` no nome para nunca sair da pasta de uploads.

### 5.6 Estilos — [public/css/style.css](../public/css/style.css)

Tema escuro definido por variáveis CSS em `:root` (`--cor-fundo`, `--cor-secundaria`, `--cor-texto`, `--cor-destaque`, `--cor-borda`). Classes principais:

| Classe | Uso |
|---|---|
| `.card` | bloco de conteúdo |
| `.btn` | botões e links com aparência de botão |
| `.form` | formulários em coluna |
| `.btn-voltar` | linha centralizada com os botões de navegação |
| `.galeria` | grade de fotos |
| `.erro` | mensagem de erro de validação |
| `.manejo-item`, `.acoes` | item da lista de manejo e seus botões |

---

## 6. Fluxos principais

### Cadastrar e acompanhar uma planta

1. Em **Strains**, cadastre a variedade.
2. Em **Plantas → Cadastrar nova planta**, escolha a strain e informe as datas que já têm.
3. Abra **Detalhes** da planta. A cada rega, poda ou adubação, use **Registrar manejo**.
4. Em **Fotos**, envie fotos de cada fase.
5. Ao mudar de fase, use **Editar** e preencha a data da fase (por exemplo, `floracao`). A fase atual no detalhe acompanha.

### Upload de foto

```
formulário (multipart) → galeria.php
  → valida arquivo, tamanho, data e tipo real
  → move para public/uploads/<nome-aleatório>.<ext>
  → INSERT em fotos (planta_id, data, imagem)
  → redireciona para galeria.php?planta_id=...
```

### Apagar uma planta

```
delete_planta.php?id=N
  → guarda os nomes das fotos
  → DELETE FROM planta  (o CASCADE apaga manejo e fotos no banco)
  → remove os arquivos das fotos do disco
  → redireciona para read_planta.php
```

---

## 7. Segurança

### O que o código faz

- **SQL injection:** todas as consultas usam *prepared statements*.
- **XSS:** as páginas de detalhe, manejo e fotos escapam a saída com `htmlspecialchars`.
- **Upload:** tipo real verificado, nome aleatório, limite de tamanho, e `public/uploads/.htaccess` bloqueia a execução de scripts na pasta.
- **Apagar manejo e foto:** por POST, com checagem de que o registro pertence à planta informada.
- **Segredos:** o `.env` está no `.gitignore` e o `.htaccess` da raiz nega o acesso web a ele.
- **Erros:** nos módulos novos, as exceções do PDO vão para o log do PHP (`error_log`) e o usuário vê uma mensagem genérica.

### Varredura de informação sensível (05/10/2026)

Foram verificados a árvore de arquivos, o histórico de todas as branches e os arquivos ignorados.

| Verificação | Resultado |
|---|---|
| Segredos (senhas, tokens, chaves) nos arquivos e no histórico | Nenhum. Só os valores de exemplo do `.env.exemple`. |
| `.env` versionado em algum momento | Nunca. Está ignorado. |
| Caminhos locais ou dados pessoais no código | Nenhum. |
| Dados reais no `schema.sql` | Nenhum (só estrutura). |
| Fotos reais no git | Nenhuma. `public/uploads/` é ignorado. |

Pontos de atenção:
- **E-mail pessoal nos metadados do git.** Alguns commits foram feitos com o e-mail pessoal do autor, e outros com o endereço `noreply` do GitHub. O e-mail fica visível no histórico de um repositório público. Para evitar isso daqui em diante, configure `git config user.email` com o endereço `noreply` (histórico antigo só muda com reescrita de histórico).
- **Mensagem de erro de conexão.** `conexao.php` exibe `$e->getMessage()` quando a conexão falha, e essa mensagem pode conter o host e o usuário do banco. Para publicar, troque por uma mensagem genérica e registre o detalhe em `error_log`.
- **EXIF das fotos.** As fotos enviadas mantêm os metadados (modelo do aparelho e, se o celular gravar, a localização GPS). Elas ficam só no seu disco, mas se o app for publicado, vale remover o EXIF no upload.
- **Listagem de diretório.** Se o Apache tiver `Indexes` ativo, `public/uploads/` pode listar todos os arquivos. Adicione `Options -Indexes` ao `.htaccess` dessa pasta.

---

## 8. Limitações conhecidas

- **Sem autenticação.** Qualquer pessoa com acesso à URL lê, edita e apaga tudo. Use apenas localmente.
- **Apagar strain e planta é por GET**, sem token CSRF. Um link ou imagem em outra página poderia disparar a exclusão. Manejo e foto já usam POST.
- **Saída sem escape nos módulos antigos:** `strain/listar.php`, `strain/update_strain.php` e `planta/create_planta.php` (as opções do select) imprimem valores sem `htmlspecialchars`.
- **Erros de banco exibidos** nos módulos antigos de strain (`echo $e->getMessage()`).
- **Validação básica** em `create_strain.php`, `update_strain.php` e `create_planta.php`: não conferem se `id` existe nem o formato dos campos.
- **Apagar strain com plantas** falha por causa da chave estrangeira, e o usuário vê só o erro bruto.
- **Funções repetidas:** `h()` está definida em `update_planta.php` e em `config/helpers.php`, e o cabeçalho HTML se repete em cada página. Um `header.php` e um `footer.php` compartilhados reduziriam a duplicação.
- **Pasta `imagem/`** na raiz não é usada.
- **Sem testes automatizados.**
- **Backup:** o dump do banco não inclui as fotos. Copie `public/uploads/` junto.

---

## 9. Como estender

**Novo campo em uma tabela:** altere `database/schema.sql` (para instalações novas) e rode um `ALTER TABLE` no banco existente. Depois ajuste o formulário e as consultas do módulo.

**Novo tipo de manejo sugerido:** adicione o texto em `TIPOS_MANEJO` em [tipos_manejo.php](../public/manejo/tipos_manejo.php).

**Novo módulo (por exemplo, colheita):**
1. Crie a tabela com chave estrangeira para `planta` (e `ON DELETE CASCADE` se o registro não fizer sentido sem a planta).
2. Crie a pasta em `public/` com `create`, `update` e `delete` (delete por POST).
3. Inclua `config/db/conexao.php` e `config/helpers.php`, use *prepared statements* e `h()` em toda saída.
4. Liste os registros em `ver_planta.php`.

**Convenções do projeto:**
- Nomes de arquivos e variáveis em português, e `snake_case` nas colunas.
- Datas sempre em `Y-m-d` no banco e `d/m/Y` na tela.
- Validar no servidor, mostrar o erro no formulário e manter o que o usuário digitou.
- Exclusões pedem confirmação com `confirm()` e usam POST.
