# belleza-valiente-api

Backend de **Belleza Valiente**: plataforma de servicios de belleza a domicilio en Cúcuta.
API REST para la app Flutter y panel de administración.

## Tecnología

- Laravel 13 · PHP 8.3
- MySQL 8
- Laravel Sanctum (tokens para la app móvil)

## Instalación local (Laragon)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

En `.env`, configurar MySQL:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=belleza_valiente
DB_USERNAME=root
DB_PASSWORD=
```

Crear las bases `belleza_valiente` y `belleza_valiente_test` (utf8mb4) y luego:

```bash
php artisan migrate --seed
```

En el entorno `local`, el seeder crea usuarios de prueba (contraseña `password`):

| Correo | Rol |
|---|---|
| admin@belleza.test | admin |
| cliente@belleza.test | cliente |
| laura@belleza.test | profesional |
| diana@belleza.test | profesional |

## Ejecutar

- Desde el PC: `http://belleza-valiente-api.test` (Laragon).
- Desde el emulador o un teléfono en la misma red: `php artisan serve --host=0.0.0.0`
  - Emulador Android: `http://10.0.2.2:8000/api/v1`
  - Teléfono: `http://<IP-del-PC>:8000/api/v1`

## Pruebas

```bash
php artisan test
```

Usan la base MySQL `belleza_valiente_test`.

## Configuración propia

| Variable | Uso | Por defecto |
|---|---|---|
| `BELLEZA_COMISION_PORCENTAJE` | Comisión de la plataforma (solo para reportes en el MVP) | 15 |

## Pendiente de confirmar

- Lista definitiva de zonas (`database/seeders/ZonaSeeder.php`).
- Precios y duraciones de referencia (`database/seeders/CatalogoSeeder.php`).
