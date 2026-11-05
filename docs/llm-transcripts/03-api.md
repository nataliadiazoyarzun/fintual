# 03 · API HTTP

`RebalanceController`, validación de tipos (400 vs 422) y `public/index.php` con CORS.

> Exportación literal de los mensajes de la conversación, en orden cronológico. Las horas están en UTC. No incluye las llamadas a herramientas ni su salida, ni el razonamiento interno del asistente. Volver al [índice](../llm-full-transcript.md).

---

## Turno 28 · Natalia Díaz

_2026-11-05T14:28:11.248Z_

````text
Perfecto.

Doy por cerrado el dominio.

Confirmo las decisiones para la API:

- 400 para JSON malformado o estructura inválida.
- 422 para errores de dominio.
- Request:

{
  "stocks": [
    {
      "ticker": "META",
      "quantity": 10,
      "price": 100
    }
  ],
  "allocation": {
    "META": 100
  }
}

- Response 200:

{
  "totalValue": 1000,
  "recommendations": []
}

- Response 422:

{
  "error": "mensaje de dominio"
}

- Response 400:

{
  "error": "Invalid request."
}

Por favor:

1. Continúa con la fase verde de RebalanceController.
2. Implementa únicamente el código necesario para que los tests actuales pasen.
3. No implementes todavía public/index.php.
4. No agregues nuevos endpoints.
5. Ejecuta PHPUnit y confirma que todos los tests pasan.

Después de eso comenzaremos la integración HTTP y React.
````

### Respuesta del asistente (28.1)

_2026-11-05T14:28:30.732Z · modelo: claude-sonnet-5.5_

````markdown
Fase verde: pasan los 37 tests, con 118 aserciones y ninguno falla. Son los 29 del dominio y los 8 del controller. La implementación está en [RebalanceController.php](c:/xampp8/htdocs/fintual/backend/src/Http/RebalanceController.php). No creé `public/index.php` ni más endpoints.

## Qué hace el controller
`handle(string $rawBody)` devuelve `['status' => int, 'body' => array]` en tres pasos:
1. **Valida la estructura (`decode()`):** si no es JSON válido, no es un objeto, le faltan `stocks` o `allocation`, o algún stock no trae `ticker`, `quantity` o `price`, devuelve **400** con `{"error": "Invalid request."}`.
2. **Construye el dominio:** crea los `Stock` (mapeando `price` a `currentPrice`) y el `Portfolio`.
3. **Responde:** **200** con `totalValue` y `recommendations`, o **422** con el mensaje de cualquier `InvalidArgumentException` del dominio.

El controller no contiene reglas de negocio: solo traduce entre JSON y dominio.

## Riesgo conocido
Los tests no cubren campos con tipo equivocado, por ejemplo `"quantity": "abc"`. Como el archivo usa `strict_types`, eso lanzaría un `TypeError` sin capturar, es decir, un error 500 en lugar del 400 que acordamos para estructuras inválidas. Lo dejé fuera porque pediste solo lo necesario para los tests actuales. Conviene cubrirlo con un test propio, rojo y luego verde, antes de integrar React. La corrección consiste en validar que `quantity` y `price` sean numéricos y `ticker` sea string en `decode()`. También quedan sin cubrir los valores no numéricos en `allocation`.

Mensaje de commit sugerido: `feat: add rebalance controller`.

Para la integración HTTP, el siguiente paso es `public/index.php`: enrutar `POST /rebalance`, CORS con `OPTIONS`, y leer el body con `php://input`. Mi propuesta es cerrar primero el hueco de tipos con TDD y luego crear `index.php`, que no necesita tests unitarios y se verifica con `curl`. Dime si te parece bien ese orden.
````

---

## Turno 29 · Natalia Díaz

_2026-11-05T14:29:58.270Z_

````text
Perfecto.

He realizado el commit de esta fase:

feat: add rebalance controller

Antes de crear public/index.php y comenzar la integración con React, quiero cerrar el último hueco identificado en la capa HTTP.

Objetivo:

Validar correctamente los tipos de entrada y evitar errores 500 provocados por TypeError.

Por favor:

1. Genera los tests necesarios para validar:
   - quantity con tipo inválido.
   - price con tipo inválido.
   - ticker con tipo inválido.
   - valores inválidos en allocation.

2. Todos estos casos deben responder:

{
  "error": "Invalid request."
}

con status 400.

3. Explica claramente por qué estos casos pertenecen a errores de request (400) y no a errores de dominio (422).

4. No modifiques todavía RebalanceController.php.

5. Ejecuta PHPUnit y confirma la fase roja.

6. Después implementa únicamente los cambios mínimos necesarios para que los tests pasen.

7. Ejecuta PHPUnit nuevamente y confirma la fase verde.

8. No agregues nuevas reglas de negocio.

Mi objetivo es declarar cerrada la capa HTTP antes de pasar a index.php y posteriormente a React.
````

### Respuesta del asistente (29.1)

_2026-11-05T14:30:11.806Z · modelo: claude-sonnet-5.5_

````markdown
Añado un test con data provider aparte, para dejar claro que es la frontera de tipos y no la de estructura.
````

### Respuesta del asistente (29.2)

_2026-11-05T14:30:31.950Z · modelo: claude-sonnet-5.5_

````markdown
La capa HTTP queda cerrada: pasan los 50 tests, con 144 aserciones, sin errores ni warnings.

## Fase roja
Añadí `test_a_request_with_wrongly_typed_values_returns_400` en [RebalanceControllerTest.php](c:/xampp8/htdocs/fintual/backend/tests/Http/RebalanceControllerTest.php), con 13 casos:

| Campo | Casos | Resultado antes del fix |
|---|---|---|
| `quantity` | texto, booleano, array | `TypeError` (habría sido un 500) |
| `price` | texto, booleano, array | `TypeError` (habría sido un 500) |
| `ticker` | número, array | `TypeError` (habría sido un 500) |
| valor de `allocation` | texto, booleano, array, `null` | 422 en lugar de 400, más 2 warnings de PHP en `Portfolio.php` |
| item de `stocks` | no es un objeto | ya pasaba en verde (caso de caracterización) |

Todos esperan estado 400 y cuerpo `{"error": "Invalid request."}`.

## Por qué son errores 400 y no 422
- **400 significa que el JSON no tiene la forma del contrato.** Un `quantity` que es texto, o un porcentaje `null`, no representa ninguna cantidad o porcentaje. Nunca llega a ser un dato que el dominio pueda evaluar.
- **422 significa que el JSON es correcto pero los datos violan una regla.** Un precio de `-5` es un número válido que `Stock` rechaza como regla de negocio, y una suma de 90 también. El dominio puede entender el dato y decide que no cumple.
- **Evita un 500.** Sin esta validación, el `TypeError` del dominio escaparía como error interno del servidor, y la interfaz no podría mostrar un mensaje útil.
- **El dominio queda limpio.** Las reglas de negocio no se mezclan con la validación de tipos del transporte.

## Cambio mínimo en [RebalanceController.php](c:/xampp8/htdocs/fintual/backend/src/Http/RebalanceController.php)
`decode()` ahora exige que:
- `ticker` sea string.
- `quantity` y `price` sean `int` o `float`.
- cada valor de `allocation` sea `int` o `float`.

El helper privado `isNumber()` rechaza los booleanos, que en PHP no son números aquí. No añadí reglas de negocio.

## Decisión de diseño que conviene conocer
Los números como texto (`"10"`) se rechazan con 400, porque el contrato pide números JSON. Eso obliga al frontend a enviar `Number(...)` y no cadenas. Lo anoto para cuando construyamos el formulario de React, donde los inputs devuelven texto.

Mensaje de commit sugerido: `feat: validate request value types in rebalance controller`.

La capa HTTP queda lista para `public/index.php`: enrutar `POST /rebalance`, manejar `OPTIONS` y CORS, leer `php://input` y emitir el JSON con el código de estado. Se verifica con `curl` contra `php -S`.
````

---

## Turno 30 · Natalia Díaz

_2026-11-05T14:32:54.973Z_

````text
Perfecto.

He realizado el commit:

feat: validate request value types

Considero cerradas las capas de dominio y controller.

Quiero comenzar la integración HTTP real.

Por favor:

1. Implementa public/index.php.
2. Soporta:
   - POST /rebalance
   - OPTIONS /rebalance
3. Configura CORS para permitir el frontend React durante desarrollo.
4. Utiliza RebalanceController como única dependencia.
5. No agregues lógica de negocio.
6. Explica cómo ejecutar y probar el endpoint localmente usando:

   php -S localhost:8000 -t public

7. Muestra ejemplos de curl para:
   - request válido
   - request inválido
   - error de dominio

8. Verifica manualmente que el endpoint funcione.

Quiero dejar lista la API antes de comenzar React.
````

### Respuesta del asistente (30.1)

_2026-11-05T14:33:04.696Z · modelo: claude-sonnet-5.5_

````markdown
Creo el punto de entrada. Solo enruta, aplica CORS y delega en `RebalanceController`.
````

### Respuesta del asistente (30.2)

_2026-11-05T14:33:11.475Z · modelo: claude-sonnet-5.5_

````markdown
Servidor levantado. Preparo los cuerpos de prueba en archivos para evitar problemas de comillas en PowerShell.
````

### Respuesta del asistente (30.3)

_2026-11-05T14:33:23.359Z · modelo: claude-sonnet-5.5_

````markdown
Todo responde como se esperaba. Falta confirmar que las respuestas POST también llevan las cabeceras CORS, que es lo que valida el navegador. Después detengo el servidor.
````

### Respuesta del asistente (30.4)

_2026-11-05T14:33:27.119Z · modelo: claude-sonnet-5.5_

````markdown
Las dos llamadas corrieron en paralelo y el servidor se detuvo antes del `curl` (código 7, sin conexión). Lo repito en orden.
````

### Respuesta del asistente (30.5)

_2026-11-05T14:33:46.777Z · modelo: claude-sonnet-5.5_

````markdown
La API está lista y la probé de verdad contra `php -S localhost:8000 -t public`. El servidor ya está detenido y los 50 tests siguen en verde.

## Qué hace [public/index.php](c:/xampp8/htdocs/fintual/backend/public/index.php)
Solo enruta, aplica CORS y delega:
- **`POST /rebalance`:** lee `php://input`, llama a `RebalanceController::handle()` y emite el estado y el JSON que devuelve. Es la única dependencia y no tiene lógica de negocio.
- **`OPTIONS /rebalance`:** responde 204, para el preflight del navegador.
- **Otras rutas y métodos:** `404` y `405` con `{"error": ...}`. No estaban pedidos, pero hay que devolver algo ante ellos. `405` incluye la cabecera `Allow`.
- **CORS:** solo permite `http://localhost:5173` y `http://127.0.0.1:5173`, el puerto de Vite. Responde `Access-Control-Allow-Origin` con ese origen, más `Vary: Origin`, métodos `POST, OPTIONS` y la cabecera `Content-Type`. Un origen ajeno no recibe cabeceras CORS.

## Verificación manual

| Prueba | Resultado |
|---|---|
| POST válido (META 10×100, AAPL 10×100, 40/60) | 200 con `totalValue: 2000`, `SELL META` 200/2 acciones y `BUY AAPL` |
| POST con `quantity: "abc"` | 400 `{"error":"Invalid request."}` |
| POST con `{oops` | 400 `{"error":"Invalid request."}` |
| POST con allocation META 90 | 422 `{"error":"Allocation percentages must sum to 100."}` |
| OPTIONS con origen `http://localhost:5173` | 204 con las cabeceras CORS |
| OPTIONS con origen ajeno | 204 sin cabeceras CORS |
| POST con origen del frontend | 200 con `Access-Control-Allow-Origin: http://localhost:5173` |
| `GET /rebalance` | 405 |
| `POST /other` | 404 |

Dos detalles: el estado 422 aparece como "Unknown Status Code" en el servidor de desarrollo de PHP. Es un detalle cosmético del texto, y el código numérico es correcto. Además, `amount` y `shares` salen como `200` y `2` y no `200.0`, porque `json_encode` quita el decimal; para JavaScript es el mismo número.

## Cómo ejecutarlo y probarlo

```powershell
cd backend
composer install
php -S localhost:8000 -t public
```

Con `curl.exe`, en PowerShell las comillas dobles dentro de un JSON se rompen. Lo más fiable es guardar el cuerpo en un archivo:

```powershell
# request válido
curl.exe -i -X POST http://localhost:8000/rebalance -H "Content-Type: application/json" --data-binary "@valid.json"
```

Contenido de cada archivo de ejemplo:
- **`valid.json`** (200): `{"stocks":[{"ticker":"META","quantity":10,"price":100},{"ticker":"AAPL","quantity":10,"price":100}],"allocation":{"META":40,"AAPL":60}}`
- **`invalid.json`** (400): `{"stocks":[{"ticker":"META","quantity":"abc","price":100}],"allocation":{"META":100}}`
- **`domain-error.json`** (422): `{"stocks":[{"ticker":"META","quantity":10,"price":100}],"allocation":{"META":90}}`

En bash o Git Bash se puede enviar inline: `curl -i -X POST http://localhost:8000/rebalance -H 'Content-Type: application/json' -d '{"stocks":[...],"allocation":{...}}'`.

Los archivos temporales que usé para probar los eliminé; no quedan en el proyecto. Eliminé también `backend/public/.gitkeep`, que ya no hacía falta.

Mensaje de commit sugerido: `feat: add HTTP entry point with CORS`.

## Para React
Dos recordatorios del contrato:
- **Números JSON:** el frontend debe enviar `quantity`, `price` y los porcentajes como números (`Number(...)`), no como texto, o recibirá 400.
- **`VITE_API_URL`:** apuntará a `http://localhost:8000`, y el puerto de Vite está fijado en 5173 para coincidir con CORS.

Cuando quieras, empiezo con el frontend. Propongo ir por pasos: primero `api.js` y la estructura de `App.jsx`, luego `PortfolioForm` y por último `RecommendationsTable`.
````

