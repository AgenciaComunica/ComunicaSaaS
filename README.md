# Comunica SaaS (Laravel)

Aplicação web para upload de dados de Meta Ads + Intelbras, geração de KPIs, relatório web e PDF, e export de remarketing.

## Requisitos

- PHP >= 8.2
- Composer
- MySQL
- Extensões PHP: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo, bcmath, gd

## Instalação local (passo a passo)

1) Instale dependências PHP
```
composer install
```

2) Crie o `.env`
```
cp .env.example .env
```
Edite o `.env` e configure o MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clone_sports
DB_USERNAME=root
DB_PASSWORD=suasenha
```

3) Gere a chave
```
php artisan key:generate
```

4) Rode as migrations
```
php artisan migrate
```

5) Crie o usuário admin (opção A - comando)
```
php artisan app:create-admin admin@clonesports.com.br --name="Admin" --password="SenhaForte123"
```

Ou (opção B - seeder via .env):
- Preencha no `.env`:
```
ADMIN_NAME="Admin"
ADMIN_EMAIL=admin@clonesports.com.br
ADMIN_PASSWORD=SenhaForte123
```
- Rode:
```
php artisan db:seed
```

6) Suba o servidor local
```
php artisan serve
```

Acesse: http://localhost:8000

## Como usar

1) Faça login com o admin.
2) Vá em **Uploads** → **Novo upload**.
3) Envie os arquivos:
   - Meta Ads (CSV)
   - Intelbras (XLSX)
4) Acesse **Relatórios** para ver o relatório web, baixar PDF e exportar remarketing CSV.

## Deploy na Locaweb (passo a passo)

1) **DocumentRoot**
- Ideal: configurar o DocumentRoot para `/public`.
- Alternativa (sem acesso): mover o `index.php` de `/public` para a raiz e ajustar paths internos (não recomendado).

2) **Upload de arquivos**
- Envie todo o projeto via FTP/SSH (incluindo `vendor` se não puder rodar composer em produção).

3) **Composer em produção**
- Se tiver acesso SSH:
```
composer install --no-dev --optimize-autoloader
```

4) **.env de produção**
- Configure `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` correto e credenciais do banco.

5) **Chave da aplicação**
- Se ainda não existir:
```
php artisan key:generate
```

6) **Migrations**
```
php artisan migrate --force
```

7) **Permissões**
- Garanta permissões de escrita em:
  - `storage/`
  - `bootstrap/cache/`

8) **Cache (opcional)**
```
php artisan config:cache
php artisan route:cache
```

9) **Dompdf + QuickChart**
- O PDF usa imagens remotas do QuickChart. Certifique-se de que o servidor permite acesso externo e que `allow_url_fopen=On`.

## Observações técnicas

- Tráfego pago é identificado **exclusivamente** quando a coluna “1º Mensagem” contém URL (`http`/`https`).
- Temperatura é normalizada para: FRIO, QUENTE, MUITO_QUENTE, SEM_TEMPERATURA.
- Venda concluída é determinada por **Valor Venda > 0**.

## Rotas principais

- `/login`
- `/dashboard`
- `/admin/users`
- `/uploads`
- `/uploads/create`
- `/reports`
- `/reports/{batch}`
- `/reports/{batch}/pdf`
- `/reports/{batch}/remarketing.csv`

