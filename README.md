# Portfolio Rebalancer

Módulo de rebalanceo de portafolios de inversión: dado un portafolio y una distribución objetivo, indica qué acciones comprar y cuáles vender para alcanzarla.

Desarrollado como **prueba técnica para Fintual** por [Natalia Díaz](https://www.ndiaz.dev).

- Portafolio: <https://www.ndiaz.dev>
- Repositorio: <https://github.com/nataliadiazoyarzun/portfolio-rebalancer>

## Capturas

<!-- Reemplazar con capturas reales, por ejemplo en docs/screenshots/ -->

| Escritorio | Móvil |
|---|---|
| _Pendiente: `docs/screenshots/desktop.png`_ | _Pendiente: `docs/screenshots/mobile.png`_ |

## El problema

Un `Portfolio` tiene una colección de `Stock` (cada uno con su precio actual) y una distribución objetivo (por ejemplo, 40 % META y 60 % AAPL). El método de rebalanceo debe indicar qué acciones se deben vender y cuáles comprar para que el portafolio quede balanceado según esa distribución.

El rebalanceo **solo genera recomendaciones**: no ejecuta órdenes ni persiste datos.

## Características principales

- Cálculo del valor total del portafolio y del monto objetivo por acción.
- Recomendaciones `BUY` / `SELL` con monto y cantidad de acciones (se permiten fracciones).
- Orden determinista: primero todas las ventas, luego todas las compras, y alfabético por ticker dentro de cada grupo.
- Una acción que está en el portafolio pero no en la distribución objetivo se trata como objetivo 0 % y se recomienda venderla por completo.
- Una acción que está en la distribución pero no en el portafolio se declara con cantidad 0 y su precio, y se recomienda comprarla.
- Un portafolio sin valor devuelve una lista vacía de recomendaciones.
- Validaciones de dominio fail-fast al construir el `Portfolio`, con mensajes en español.
- Tolerancia de punto flotante (`1e-6`) al validar que la distribución suma 100.
- API HTTP con un único endpoint, `POST /rebalance`.
- Interfaz React con formulario editable y tabla de resultados.

## Arquitectura

El proyecto está separado en tres capas, y cada una solo conoce a la que tiene debajo.

```
┌──────────────┐   JSON    ┌───────────────────┐   objetos   ┌──────────────┐
│ React (UI)   │ ────────► │ RebalanceController│ ──────────► │ Dominio (PHP)│
│ presentación │ ◄──────── │ adaptador HTTP     │ ◄────────── │ reglas       │
└──────────────┘  200/400/ └───────────────────┘             └──────────────┘
                  422
```

- **Dominio** (`backend/src/Domain`): `Stock`, `Portfolio` y el enum `Action`. Contiene todas las reglas de negocio y no sabe nada de HTTP ni de JSON.
- **Controller** (`backend/src/Http/RebalanceController.php`): recibe el cuerpo JSON, valida su estructura y tipos, construye los objetos de dominio y traduce el resultado (o la excepción) a un código de estado y un cuerpo.
- **Entrada HTTP** (`backend/public/index.php`): enrutamiento mínimo (`POST` y `OPTIONS` en `/rebalance`), CORS y delegación al controller. No contiene lógica de negocio.
- **Frontend** (`frontend/`): solo presentación. Convierte el texto del formulario en números, arma la `allocation` y muestra la respuesta. **No recalcula ni valida reglas de negocio**; todo lo decide el backend.

### Modelo de dominio

| Clase | Responsabilidad |
|---|---|
| `Stock` | Posición inmutable: ticker (normalizado con `trim` y mayúsculas), cantidad y precio actual. Calcula su valor (`value()`). |
| `Portfolio` | Agrega las posiciones y la distribución objetivo, valida la consistencia y calcula `totalValue()` y `rebalance()`. |
| `Action` | Enum `BUY` / `SELL`. |

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3, Composer, sin framework |
| Tests | PHPUnit 11 |
| Frontend | React 18, Vite 6, JavaScript |
| Estilos | Bootstrap 5 (única dependencia visual) |

## Estructura del proyecto

```
portfolio-rebalancer/
├── backend/
│   ├── public/
│   │   └── index.php                  # Entrada HTTP, CORS y enrutamiento
│   ├── src/
│   │   ├── Domain/
│   │   │   ├── Action.php
│   │   │   ├── Portfolio.php
│   │   │   └── Stock.php
│   │   └── Http/
│   │       └── RebalanceController.php
│   ├── tests/
│   │   ├── Domain/
│   │   │   ├── PortfolioTest.php
│   │   │   └── StockTest.php
│   │   └── Http/
│   │       └── RebalanceControllerTest.php
│   ├── composer.json
│   └── phpunit.xml
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── PortfolioForm.jsx
│   │   │   └── RecommendationsTable.jsx
│   │   ├── api.js                     # Único lugar que llama a fetch
│   │   ├── App.jsx                    # Estado y conexión con la API
│   │   ├── main.jsx
│   │   └── styles.css
│   ├── .env.example
│   ├── index.html
│   ├── package.json
│   └── vite.config.js
└── README.md
```

## Requisitos

- PHP 8.3 o superior y Composer.
- Node.js 18 o superior y npm.

## Cómo ejecutar el backend

```bash
cd backend
composer install
php -S localhost:8000 -t public
```

La API queda disponible en `http://localhost:8000/rebalance`.

## Cómo ejecutar el frontend

```bash
cd frontend
npm install
cp .env.example .env     # en Windows: copy .env.example .env
npm run dev
```

Abre <http://localhost:5173>. La URL de la API se configura con `VITE_API_URL` (por defecto `http://localhost:8000`).

Para generar la versión de producción:

```bash
npm run build
```

> El CORS del backend solo permite los orígenes `http://localhost:5173` y `http://127.0.0.1:5173`, pensados para desarrollo. Para desplegar el frontend en otro dominio hay que ajustar la lista `ALLOWED_ORIGINS` en `backend/public/index.php`.

## Cómo ejecutar los tests

```bash
cd backend
composer install
./vendor/bin/phpunit     # en Windows: .\vendor\bin\phpunit
```

La suite incluye pruebas del dominio (`Stock` y `Portfolio`) y de la capa HTTP (`RebalanceController`). Con los datos de prueba del proyecto, la última ejecución terminó en `OK (50 tests, 144 assertions)`.

## API

### `POST /rebalance`

**Solicitud**

```json
{
  "stocks": [
    { "ticker": "META", "quantity": 10, "price": 100 },
    { "ticker": "AAPL", "quantity": 10, "price": 100 }
  ],
  "allocation": { "META": 40, "AAPL": 60 }
}
```

- `quantity`, `price` y los valores de `allocation` deben ser números JSON (no texto). `ticker` debe ser texto.
- Los porcentajes se expresan en escala 0–100 y deben sumar 100.

**Respuesta 200**

```json
{
  "totalValue": 2000,
  "recommendations": [
    { "ticker": "META", "action": "SELL", "amount": 200, "shares": 2 },
    { "ticker": "AAPL", "action": "BUY", "amount": 200, "shares": 2 }
  ]
}
```

`amount` está redondeado a 2 decimales; `shares` es `amount / price` sin redondear.

### Ejemplos con `curl`

Solicitud válida:

```bash
curl -X POST http://localhost:8000/rebalance \
  -H "Content-Type: application/json" \
  -d '{"stocks":[{"ticker":"META","quantity":10,"price":100},{"ticker":"AAPL","quantity":10,"price":100}],"allocation":{"META":40,"AAPL":60}}'
```

Solicitud con estructura inválida (400):

```bash
curl -i -X POST http://localhost:8000/rebalance \
  -H "Content-Type: application/json" \
  -d '{"stocks":"no-es-una-lista"}'
# {"error":"Solicitud inválida."}
```

Error de dominio (422), porcentajes que no suman 100:

```bash
curl -i -X POST http://localhost:8000/rebalance \
  -H "Content-Type: application/json" \
  -d '{"stocks":[{"ticker":"META","quantity":10,"price":100}],"allocation":{"META":90}}'
# {"error":"Los porcentajes de la distribución objetivo deben sumar 100."}
```

> En PowerShell, el JSON en línea pierde las comillas con `curl.exe`. Conviene guardarlo en un archivo y usar `--data-binary "@archivo.json"`.

### Otros códigos

| Código | Cuándo |
|---|---|
| `204` | `OPTIONS /rebalance` (preflight de CORS) |
| `404` | Cualquier ruta distinta de `/rebalance` |
| `405` | Método distinto de `POST` u `OPTIONS` en `/rebalance` |

## Decisiones de diseño

### Por qué TDD

El dominio se construyó con ciclos cortos de RED → GREEN → REFACTOR, haciendo un commit por fase. Las reglas de un rebalanceo (redondeos, tickers ausentes, validaciones) son fáciles de romper sin querer, y los tests permiten refactorizar con seguridad. Cada validación nació de un test que fallaba primero, y por eso el comportamiento esperado queda documentado como ejemplos ejecutables.

### Separación dominio / controller / frontend

- El **dominio** no depende de HTTP, así que se puede probar sin servidor y reutilizar desde otro punto de entrada.
- El **controller** es un adaptador: traduce entre JSON y objetos de dominio, y entre excepciones y códigos de estado. Sus tests no levantan un servidor.
- El **frontend** no contiene reglas de negocio. Si una regla cambia, se modifica en un único lugar (PHP) y la interfaz muestra el mensaje del backend tal cual.

### Distinción entre 400 y 422

- **400 Bad Request**: la solicitud no se puede interpretar. JSON malformado, campos faltantes o tipos incorrectos (por ejemplo, `quantity` como texto). El cliente envió algo mal formado y el dominio ni siquiera llega a ejecutarse.
- **422 Unprocessable Content**: la solicitud está bien formada, pero viola una regla de negocio (porcentajes que no suman 100, ticker duplicado, precio menor o igual a cero…). El mensaje es el del dominio.

Esta distinción evita errores 500 por `TypeError` y deja claro quién debe corregir qué.

### Supuestos y reglas de negocio

- Se permiten fracciones de acciones.
- `Stock` modela una **posición**, no el instrumento: un ticker nuevo se representa con cantidad 0 y su precio.
- Todo ticker de la distribución objetivo debe estar presente en `stocks`, y su precio debe ser mayor que cero.
- Los tickers se normalizan (`trim` y mayúsculas), también las claves de `allocation`. `aapl` y `AAPL` son el mismo ticker.
- Se rechazan: tickers duplicados en `stocks`, porcentajes negativos, distribución que no suma 100 (con tolerancia `1e-6`) y distribución vacía si el portafolio tiene valor.
- Una distribución vacía con portafolio vacío es válida y devuelve `[]`.
- Cada diferencia se redondea a 2 decimales; las diferencias que quedan en 0,00 no generan recomendación. No hay otra tolerancia configurable.
- Las recomendaciones **no ejecutan órdenes**, y no hay autenticación ni persistencia.
- Los mensajes de error del backend están en español y la interfaz los muestra tal cual.

## Accesibilidad y experiencia de usuario

- Diseño responsive con Bootstrap: en móvil cada fila del formulario se apila en una columna con etiquetas visibles; en escritorio se muestra en una línea con encabezados.
- Cada campo y botón tiene un nombre accesible específico (por ejemplo, `Cantidad, fila 2` y `Eliminar fila 2`).
- Navegación completa por teclado, con orden de tabulación lógico, enlace «Saltar al contenido» y foco visible. Al agregar una fila, el foco pasa a su campo de ticker.
- Estados anunciados a lectores de pantalla: carga con `role="status"`, errores con `role="alert"` y resultado en una región `aria-live`.
- Tabla de resultados semántica (`caption`, `th scope`) y jerarquía de encabezados coherente.
- Contraste de texto alto en toda la interfaz (por ejemplo, 16,6:1 en el texto principal sobre el fondo).
- Se verificó manualmente con el navegador y el árbol de accesibilidad. No se ejecutaron auditorías automáticas (axe, Lighthouse) ni pruebas con lectores de pantalla reales.

## Próximos pasos

- Tests automatizados del frontend (por ejemplo, Vitest y Testing Library).
- Pruebas de integración end-to-end del flujo completo.
- Tolerancia mínima configurable para evitar recomendaciones de importes insignificantes.
- Opción de operar solo con acciones enteras.
- Configurar los orígenes CORS mediante variables de entorno.
- Contenerizar backend y frontend con Docker.
- Integración continua que ejecute PHPUnit y `npm run build`.
- Auditorías automáticas de accesibilidad.
- Subir capturas reales y un despliegue de demostración.

## Sobre la autora

**Natalia Díaz**
Senior Software Engineer
<https://www.ndiaz.dev>
