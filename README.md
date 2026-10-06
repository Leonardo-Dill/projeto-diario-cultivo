# Diário de Cultivo

Aplicação web em PHP para registrar o cultivo de plantas: cadastro de strains, acompanhamento de cada planta (fases, rendimento), fotos e um diário de manejo (rega, poda, adubação etc.).

## Funcionalidades

- **Strains:** criar, listar, editar e apagar.
- **Plantas:** criar, listar, editar e apagar, com as datas de cada fase (germinação, plantinha, vegetativo, floração, colheita) e o rendimento.
- **Detalhe da planta:** fase atual, dias de vida, prévia das fotos e linha do tempo do manejo.
- **Manejo:** registrar, editar e apagar ações por planta.
- **Fotos:** enviar e apagar fotos por planta (JPG, PNG ou WEBP, até 8 MB).

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL/MariaDB) com PHP 8.0 ou superior
- [Composer](https://getcomposer.org/)

## Instalação

1. Coloque o projeto em `C:\xampp\htdocs\Diario` (ou na pasta `htdocs` equivalente).
2. Instale as dependências na pasta do projeto:

   ```bash
   composer install
   ```

3. Inicie **Apache** e **MySQL** no painel do XAMPP.
4. Crie o banco importando o schema, de uma das duas formas:
   - pelo phpMyAdmin (`http://localhost/phpmyadmin`), na aba **Importar**, escolhendo `database/schema.sql`; ou
   - pelo terminal, na pasta do projeto:

     ```bash
     C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql
     ```

   O arquivo cria o banco `diario_cultivo` e as tabelas `strain`, `planta`, `manejo` e `fotos`.
5. Crie o arquivo de configuração copiando o exemplo:

   ```bash
   copy .env.exemple .env
   ```

   Edite o `.env` com os dados do seu banco. No XAMPP padrão:

   ```
   db_host=localhost
   db_name=diario_cultivo
   db_user=root
   db_pass=
   ```

6. Acesse `http://localhost/Diario/public/`.

## Estrutura

```
config/
  db/conexao.php      conexão PDO com o banco (lê o .env)
  helpers.php         funções de apoio (escape de HTML, validação de data)
database/
  schema.sql          estrutura do banco, sem dados
public/               páginas da aplicação
  index.php           página inicial
  strain/             CRUD de strains
  planta/             CRUD de plantas e tela de detalhe
  manejo/             registro de manejo
  foto/               galeria e upload de fotos
  uploads/            fotos enviadas (não versionadas)
  css/style.css       estilos
```

## Observações

- O `.env` contém credenciais e **não deve ser versionado** (já está no `.gitignore`).
- As fotos ficam na pasta `public/uploads/`; o banco guarda apenas o nome do arquivo. Um backup do banco **não** inclui as imagens, então copie a pasta `uploads/` junto.
- Apagar uma planta apaga também o manejo e as fotos dela.
- O projeto não tem login. Use apenas em ambiente local ou proteja o acesso antes de publicar.

## Documentação

A descrição técnica de cada módulo, do banco, dos fluxos e da segurança está em [docs/DOCUMENTACAO.md](docs/DOCUMENTACAO.md).
