# 06 · Despliegue en Vercel

Estrategia de despliegue, spike con `vercel-php`, despliegue de backend y frontend, diagnóstico de CORS.

> Exportación literal de los mensajes de la conversación, en orden cronológico. Las horas están en UTC. No incluye las llamadas a herramientas ni su salida, ni el razonamiento interno del asistente. Volver al [índice](../llm-full-transcript.md).

---

## Turno 40 · Natalia Díaz

_2026-11-05T16:23:41.483Z_

````text
Necesito revisar la estrategia de despliegue antes de continuar.

Contexto:

Repositorio:
https://github.com/nataliadiazoyarzun/portfolio-rebalancer

Estructura actual:

portfolio-rebalancer
├── backend
│   ├── public
│   ├── src
│   ├── tests
│   ├── vendor
│   └── composer.json
│
├── frontend
│   ├── src
│   ├── package.json
│   └── vite.config.js
│
└── README.md

Actualmente:

- Frontend React + Vite.
- Backend PHP.
- El frontend consume la API PHP.
- Mi portafolio ya está desplegado en Vercel.
- Quiero desplegar este proyecto y posteriormente usar un subdominio como:

  rebalancer.ndiaz.dev

Objetivo:

Determinar la forma correcta de desplegar la aplicación completa.

Por favor:

1. Analiza la estructura actual del repositorio.

2. Evalúa si el backend PHP actual puede desplegarse directamente en Vercel.

3. Explica las alternativas posibles:

   - Frontend y backend juntos.
   - Frontend y backend separados.
   - Funciones serverless.
   - Configuración mediante vercel.json.
   - Cualquier otra opción realista.

4. Indica cuál recomendarías para este proyecto.

5. Si hace falta reorganizar carpetas o mover archivos, explica exactamente qué habría que hacer.

6. No modifiques ningún archivo todavía.

7. No despliegues nada todavía.

8. El objetivo es terminar con:

   - una URL pública funcional
   - una arquitectura simple
   - el menor mantenimiento posible

9. Explica ventajas y desventajas de cada alternativa.

Primero quiero tomar una decisión de despliegue informada antes de crear el subdominio rebalancer.ndiaz.dev.
````

### Respuesta del asistente (40.1)

_2026-11-05T16:24:10.090Z · modelo: claude-sonnet-5.5_

````markdown
# Estrategia de despliegue de Portfolio Rebalancer

Esto sale de lo que ya sé de la estructura y de cómo funciona Vercel. No toqué ni desplegué nada.

## 1. Qué hay hoy en el repo
- **Frontend:** es un build estático de Vite. Lee `VITE_API_URL` al compilar y, si no existe, usa `http://localhost:8000`.
- **Backend:** es PHP sin framework. [index.php](c:/xampp8/htdocs/fintual/backend/public/index.php) hace de router y asume que lo sirve `php -S` o un servidor PHP tradicional. No tiene estado ni base de datos.
- **CORS:** está fijo a `localhost:5173` y `127.0.0.1:5173`. En producción hay que cambiarlo, así que esto es **trabajo obligatorio** sea cual sea la opción.

## 2. ¿Puede el backend PHP ir directo a Vercel?
**No de forma oficial.** Vercel no tiene runtime de PHP propio. Solo existe el runtime comunitario `vercel-php` (juicyfx), que sí permite PHP 8.x como función serverless. El riesgo es que lo mantiene la comunidad: puede quedar atrasado en versiones de PHP o romperse con cambios de Vercel. Antes de decidir hay que confirmar que soporte PHP 8.3 y que resuelva el `composer install`. Eso se prueba en minutos, pero no puedo asegurarlo desde aquí.

## 3. Alternativas

| Opción | Ventajas | Desventajas |
|---|---|---|
| **A. Vercel solo, dos proyectos del mismo repo** (root `frontend` y root `backend` con `vercel-php`) | Una sola plataforma, gratis, sin cold starts largos, sin mover carpetas, despliegue automático con cada push | Depende de un runtime comunitario. Necesita CORS y dos subdominios |
| **B. Un solo proyecto Vercel** (frontend estático + `api/` con `vercel-php`, con `vercel.json`) | Mismo origen, sin CORS, un solo dominio | Exige reorganizar el repo (`api/` en la raíz o `builds` legacy) y mezcla dos builds. Es más frágil y la misma dependencia comunitaria |
| **C. Frontend en Vercel + backend en Docker** (Render, Fly.io o Railway) | PHP real y estable, sin depender de runtimes comunitarios, portable | Dos plataformas y un `Dockerfile`. El plan gratuito de Render duerme el servicio, con 30–60 s de arranque en frío, lo que es mala impresión en una demo |
| **D. Todo en un contenedor** (PHP sirve el build de React y la API) | Una sola URL, sin CORS | Pierdes Vercel para el frontend, el `Dockerfile` es más complejo y el subdominio apunta por CNAME al otro proveedor |
| **E. Hosting PHP compartido o VPS** | PHP nativo | Mantenimiento, SSL y despliegue manuales. No cumple "mínimo mantenimiento" |
| **F. Reescribir la API en Node serverless** | Encaja nativamente con Vercel | Descarta el dominio PHP y TDD, que son el núcleo de la prueba. **No recomendado** |

## 4. Recomendación
**Opción A, con la opción C como plan de respaldo.**

- Es la que mejor cumple tus criterios: una URL pública, la menor cantidad de piezas y cero mantenimiento. Además no hay que mover ni reorganizar carpetas.
- Primero haría un spike de 15 minutos con `vercel-php` y el endpoint `/rebalance`. Si funciona con PHP 8.3, sigo con A. Si falla o es inestable, paso a C. Para un proyecto que representa tu marca, la estabilidad importa más que usar una sola plataforma.
- Descarto B porque obliga a reestructurar a cambio de eliminar CORS, y el CORS fijo se arregla con una variable de entorno. Descarto F porque contradice el sentido de la prueba.

## 5. Qué habría que cambiar (opción A, sin mover carpetas)

**Backend (proyecto Vercel con root `backend`):**
- Crear `backend/api/index.php`, un punto de entrada mínimo que cargue la lógica de `public/index.php`.
- Crear `backend/vercel.json` con el runtime `vercel-php` y una regla que redirija todo a `api/index.php`.
- Hacer que los orígenes de CORS salgan de una variable de entorno (por ejemplo `ALLOWED_ORIGINS`) con `https://rebalancer.ndiaz.dev` en producción. Hoy están fijos. La lista local se mantiene para desarrollo.
- Verificar que `vendor/` se instale en el build (`composer install --no-dev`) y que `REQUEST_URI` llegue intacto al router.

**Frontend (proyecto Vercel con root `frontend`):**
- No requiere cambios de código. Solo definir `VITE_API_URL=https://api-rebalancer.ndiaz.dev`. El nombre del subdominio de la API queda a tu elección. Como la variable se inyecta al compilar, hay que redeployar si la cambias.

**DNS:**
- `rebalancer.ndiaz.dev` apunta al proyecto del frontend.
- `api-rebalancer.ndiaz.dev` (o el que elijas) apunta al proyecto del backend.

**Extras:**
- Actualizar el README con la URL pública.
- Antes de mostrar el proyecto, confirmar que el repositorio sea público.

## 6. Decisiones que necesito de ti
1. ¿Aceptas hacer el spike con `vercel-php` para decidir entre A y C?
2. ¿Qué nombre prefieres para el subdominio de la API?

Cuando me confirmes, empiezo por el spike en local, sin desplegar nada.
````

---

## Turno 41 · Natalia Díaz

_2026-11-05T16:26:00.075Z_

````text
Sí.

Acepto realizar el spike con vercel-php.

Objetivo:

Determinar si el backend PHP actual puede ejecutarse correctamente en Vercel sin modificar la arquitectura existente.

Por favor:

1. Implementa únicamente los cambios mínimos necesarios para ejecutar el backend mediante vercel-php.
2. No modifiques el dominio.
3. No modifiques los tests.
4. No cambies el contrato de la API.
5. Mantén la estructura actual del repositorio.
6. Configura el backend para desplegarse como un proyecto independiente en Vercel (root: backend).
7. Configura los orígenes permitidos mediante variables de entorno y elimina la dependencia de localhost hardcodeado para producción.
8. Explica exactamente qué archivos se agregan y por qué.
9. Explica cómo realizar el despliegue paso a paso en Vercel.
10. Si encuentras limitaciones relevantes de vercel-php durante el spike, documenta claramente los riesgos.

El objetivo no es desplegar todavía, sino verificar si esta estrategia es viable antes de crear los subdominios definitivos.
````

### Respuesta del asistente (41.1)

_2026-11-05T16:26:22.186Z · modelo: claude-sonnet-5.5_

````markdown
Ahora aplico los cambios mínimos: CORS por variable de entorno y los archivos de Vercel.
````

### Respuesta del asistente (41.2)

_2026-11-05T16:26:35.199Z · modelo: claude-sonnet-5.5_

````markdown
Pruebo localmente imitando a Vercel (router único `api/index.php`) con `ALLOWED_ORIGINS` definido.
````

### Respuesta del asistente (41.3)

_2026-11-05T16:26:53.076Z · modelo: claude-sonnet-5.5_

````markdown
El spike local funciona, pero la viabilidad real en Vercel solo se confirma desplegando. Lo que verifiqué es que el router sigue funcionando detrás de un único punto de entrada, como hace Vercel, y que CORS ahora sale de la variable de entorno. Los 50 tests siguen en verde (`OK (50 tests, 144 assertions)`).

## Hallazgo importante
El README oficial de `vercel-php` dice que es un runtime **mantenido por la comunidad, sin soporte de Vercel**. Para proyectos nuevos recomienda desplegar PHP con **contenedor Docker**. Esto refuerza que la opción C (Docker) debe quedar como respaldo. Por otro lado, la versión actual `0.9.0` usa PHP 8.5, lo que es compatible con tu `"php": ">=8.3"`, y soporta Composer.

## Archivos
Todo está en `backend/`. No toqué dominio, tests, controller ni el contrato de la API.

| Archivo | Cambio | Por qué |
|---|---|---|
| [public/index.php](c:/xampp8/htdocs/fintual/backend/public/index.php) | modificado | La constante con localhost fijo pasó a la función `allowedOrigins()`, que lee `ALLOWED_ORIGINS` (lista separada por comas). Sin la variable, usa los orígenes locales, así que tu desarrollo no cambia. |
| [api/index.php](c:/xampp8/htdocs/fintual/backend/api/index.php) | nuevo | Es el punto de entrada que exige Vercel. Solo hace `require` de `public/index.php`, sin duplicar lógica. |
| [vercel.json](c:/xampp8/htdocs/fintual/backend/vercel.json) | nuevo | Registra `vercel-php@0.9.0` para `api/index.php` y redirige todas las rutas a él. |
| [.vercelignore](c:/xampp8/htdocs/fintual/backend/.vercelignore) | nuevo | Excluye `vendor`, `tests` y cachés. El runtime ejecuta `composer install` por su cuenta. |

## Despliegue paso a paso (cuando decidas hacerlo)
1. Sube estos cambios a GitHub.
2. En Vercel, **Add New → Project** e importa `portfolio-rebalancer`.
3. Configura **Root Directory = `backend`** y **Framework Preset = Other**. Deja vacíos los comandos de build.
4. En **Environment Variables**, agrega `ALLOWED_ORIGINS=https://rebalancer.ndiaz.dev`. Si quieres probar antes del dominio, agrega también la URL temporal del frontend, separada por coma.
5. Haz Deploy y prueba:
   - `curl -i -X POST https://<proyecto>.vercel.app/rebalance -H "Content-Type: application/json" --data-binary "@payload.json"`
   - `curl -i https://<proyecto>.vercel.app/otra`, que debe devolver 404.
6. Cuando el backend responda, crea el proyecto del frontend (root `frontend`) con `VITE_API_URL=https://<url-del-backend>`. Después asigna los dominios.

## Riesgos
- **Sin soporte oficial.** Si el runtime se rompe o queda abandonado, el backend deja de desplegarse. El plan de salida es un `Dockerfile` pequeño.
- **No verificado en Vercel.** No pude comprobar que `REQUEST_URI` llegue intacto como `/rebalance` ni que el `composer install` del runtime funcione. Si el primer deploy da 404 o error 500, empezamos por ahí.
- **Lectura de la variable.** Asumo que `getenv('ALLOWED_ORIGINS')` ve las variables de entorno del proyecto. Es lo normal, pero también depende del deploy real.
- **Cold start.** El README del runtime indica unos 250 ms en frío y unos 5 ms en caliente, aceptable para una demo.
- **Variables y despliegue.** Cambiar `ALLOWED_ORIGINS` requiere redeploy del backend. Lo mismo vale para `VITE_API_URL` en el frontend.
- **PHP 8.5 en producción.** Tus tests corren en PHP 8.3 local. Conviene confirmar que la API se comporta igual en producción.

Sugerencia de commit: `chore: prepare backend for Vercel (vercel-php)`.

Siguiente paso, cuando quieras: hacer el primer deploy del backend y probarlo. Si falla, preparo el `Dockerfile` de respaldo.
````

---

## Turno 42 · Natalia Díaz

_2026-11-05T16:29:00.110Z_

````text
Perfecto.

He realizado el commit:

chore: prepare backend for Vercel

Ahora quiero realizar el primer despliegue real del backend en Vercel.

Objetivo:

Validar que la estrategia basada en vercel-php es realmente viable antes de desplegar el frontend y crear los subdominios definitivos.

Por favor:

1. Guíame paso a paso para crear el proyecto backend en Vercel.

2. Considera la estructura actual:

portfolio-rebalancer
├── backend
├── frontend
└── README.md

3. Indícame exactamente:

   - Project Name recomendado.
   - Root Directory.
   - Framework Preset.
   - Variables de entorno necesarias.
   - Cualquier configuración adicional.

4. No asumas que Vercel detectará todo automáticamente.

5. Después del despliegue:

   - Explícame cómo probar el endpoint.
   - Qué URL debo abrir.
   - Qué request debo enviar.
   - Qué respuesta esperar.

6. Si el despliegue falla:

   - Ayúdame a diagnosticar el error.
   - Explica cómo obtener logs relevantes.

7. No despliegues el frontend todavía.

8. No configuremos todavía los subdominios:

   - rebalancer.ndiaz.dev
   - api-rebalancer.ndiaz.dev

   Primero quiero verificar que el backend funcione correctamente con la URL temporal de Vercel.

Quiero avanzar paso a paso y validar el backend en producción antes de continuar.
````

### Respuesta del asistente (42.1)

_2026-11-05T16:29:21.396Z · modelo: claude-sonnet-5.5_

````markdown
# Primer despliegue del backend en Vercel

Esta guía es solo orientación. Yo no puedo operar tu cuenta de Vercel, así que los pasos los haces tú en el panel.

## 0. Antes de empezar
- Confirma que el commit `chore: prepare backend for Vercel` esté **pusheado a GitHub** (rama `main`).
- Confirma que `backend/vercel.json`, `backend/api/index.php` y `backend/.vercelignore` aparezcan en GitHub.
- **No** necesitas `vercel.json` en la raíz ni en `frontend`.

## 1. Crear el proyecto
1. Ve a vercel.com/new, elige **Import Git Repository** y selecciona `nataliadiazoyarzun/portfolio-rebalancer`. Si no aparece, ve a *Adjust GitHub App Permissions* y dale acceso al repo.
2. En la pantalla **Configure Project**, completa esto:

| Campo | Valor |
|---|---|
| **Project Name** | `portfolio-rebalancer-api` |
| **Framework Preset** | **Other** |
| **Root Directory** | `backend` (pulsa *Edit* y elige la carpeta) |
| **Build Command** | vacío (no activar *Override*) |
| **Output Directory** | vacío |
| **Install Command** | vacío |
| **Node.js Version** | el que venga por defecto |

Con esto no dependes de la autodetección. El nombre define la URL temporal, que será algo como `portfolio-rebalancer-api.vercel.app`. Si el nombre ya está tomado, Vercel agregará un sufijo y tendrás que usar la URL real que te muestre.

3. En **Environment Variables**, agrega esta variable. Todavía no tengo la URL del frontend, así que por ahora déjala con el dominio final:

| Name | Value |
|---|---|
| `ALLOWED_ORIGINS` | `https://rebalancer.ndiaz.dev` |

Más adelante, cuando tengas la URL temporal del frontend, se añade separada por coma y se hace un redeploy. Para probar el backend con `curl` no hace falta CORS.

4. Pulsa **Deploy**.

## 2. Qué esperar durante el build
- Debe aparecer la instalación del runtime `vercel-php` y la ejecución de `composer install`.
- El estado final debe ser **Ready**.
- Si el build termina en segundos y sin mencionar PHP, es mala señal: puede que no se haya tomado `vercel.json`. Mira la sección 4.

## 3. Probar el endpoint
La URL base es la que Vercel te muestra en **Domains**, por ejemplo `https://portfolio-rebalancer-api.vercel.app`.

**a) Desde el navegador (GET).** Abre `https://<tu-url>/rebalance`. Esperado: **405** con `{"error":"Método no permitido."}`. Esto ya demuestra que PHP corre y que el router recibe la ruta correcta. Un 404 de Vercel (la página genérica con "NOT_FOUND") significa que PHP no se ejecutó. La variante `{"error":"No encontrado."}` es de tu propio router.

**b) POST válido.** Guarda este contenido en `payload.json`:

```json
{"stocks":[{"ticker":"META","quantity":10,"price":100},{"ticker":"AAPL","quantity":0,"price":50}],"allocation":{"META":50,"AAPL":50}}
```

```powershell
curl.exe -i -X POST https://<tu-url>/rebalance -H "Content-Type: application/json" --data-binary "@payload.json"
```

Esperado: **200** con

```json
{"totalValue":1000,"recommendations":[{"ticker":"META","action":"SELL","amount":500,"shares":5},{"ticker":"AAPL","action":"BUY","amount":500,"shares":10}]}
```

**c) Pruebas de contrato** (deben coincidir con lo local):
- Cuerpo `{}` o JSON roto devuelve **400** con `{"error":"Solicitud inválida."}`.
- Porcentajes que no suman 100 devuelven **422** con `{"error":"Los porcentajes de la distribución objetivo deben sumar 100."}`.
- `GET /otra` devuelve **404** con `{"error":"No encontrado."}`.

**d) CORS.**
```powershell
curl.exe -i -X OPTIONS https://<tu-url>/rebalance -H "Origin: https://rebalancer.ndiaz.dev"
```
Esperado: **204** con `Access-Control-Allow-Origin: https://rebalancer.ndiaz.dev`. Con otro origen, la respuesta es 204 sin esa cabecera. Eso confirma que la variable de entorno se está leyendo. Si falta la cabecera con el origen correcto, la variable no llegó a PHP.

**e) Protección de despliegues.** Si en vez de tu JSON recibes una página de login de Vercel o un 401, activa *Deployment Protection*. Ve a **Settings → Deployment Protection** y pon *Vercel Authentication* en **Disabled** para el entorno Production. Es muy común en proyectos nuevos y no es un error de PHP.

## 4. Si falla: diagnóstico
**Dónde ver los logs**
- **Build:** en **Deployments**, abre el despliegue y entra a **Build Logs**.
- **Runtime:** en el proyecto, abre **Logs** y filtra por la ruta `/rebalance`. Allí aparecen los errores de PHP en ejecución.
- **CLI:** `vercel logs <url-del-deploy>` si tienes la CLI.
- **Cabeceras:** `curl.exe -i` y busca `x-vercel-id` y `x-vercel-error`.

**Síntomas frecuentes**

| Síntoma | Causa probable | Qué hacer |
|---|---|---|
| Build falla: "runtime not found" o `vercel-php` | Versión del runtime inexistente | Revisa en npm `vercel-php` la última versión y cámbiala en `vercel.json` |
| Build OK, pero 404 genérico de Vercel | Root Directory incorrecto o `vercel.json` ignorado | Confirma que Root Directory sea `backend` y redeploya |
| 500 con `FUNCTION_INVOCATION_FAILED` | Error de PHP, por ejemplo falta `vendor/autoload.php` | Mira los Runtime Logs |
| Error `autoload.php` no encontrado | `composer install` no corrió o `.vercelignore` excluye algo necesario | Revisa los Build Logs de Composer |
| 404 con `{"error":"No encontrado."}` en `/rebalance` | `REQUEST_URI` llega distinto | Dime el valor y ajustamos el router |
| 405 siempre | Cambio en el método o redirección 308 | Comprueba que uses `https` y sin `/` final |
| Respuesta sin cabeceras CORS | `ALLOWED_ORIGINS` mal escrita (espacios, `/` final, `http` en vez de `https`) | Corrígela y **redeploya** |

Un detalle sobre las variables: las nuevas solo se aplican a despliegues posteriores. Después de cambiarlas, usa **Redeploy**.

## 5. Dime esto cuando termines
Pégame lo siguiente y decidimos juntos:
1. La URL temporal.
2. El resultado de GET, POST válido, 400, 422 y OPTIONS.
3. Si algo falló, el fragmento del log de build o de runtime.

Si todo responde como arriba, la estrategia `vercel-php` es viable y pasamos al frontend. Si no logramos estabilizarla en un par de intentos, preparo el `Dockerfile` de respaldo.
````

---

## Turno 43 · Natalia Díaz

_2026-11-05T16:41:37.974Z_

````text
La URL temporal del backend es:

https://portfolio-rebalancer-backend.vercel.app

Al abrir:

https://portfolio-rebalancer-backend.vercel.app/rebalance

obtengo:

{"error":"Método no permitido."}

Por lo tanto:

- El despliegue parece exitoso.
- PHP está ejecutándose.
- El router responde correctamente.
- La ruta /rebalance funciona en Vercel.

Quiero continuar con las pruebas funcionales.

Por favor, guíame paso a paso para:

1. Verificar el POST válido (200).
2. Verificar el request inválido (400).
3. Verificar el error de dominio (422).
4. Verificar los encabezados CORS.
5. Si todo funciona correctamente, continuar con el despliegue del frontend configurando VITE_API_URL para apuntar a:

https://portfolio-rebalancer-backend.vercel.app

Todavía no quiero configurar los subdominios definitivos.
Primero quiero validar que toda la aplicación funcione usando las URLs temporales de Vercel.
````

### Respuesta del asistente (43.1)

_2026-11-05T16:41:44.525Z · modelo: claude-sonnet-5.5_

````markdown
Puedo ejecutar yo mismo las pruebas contra tu URL. Las corro ahora.
````

### Respuesta del asistente (43.2)

_2026-11-05T16:42:04.231Z · modelo: claude-sonnet-5.5_

````markdown
# Backend validado en producción

Probé yo mismo la URL real y el backend funciona como en local.

| Prueba | Resultado |
|---|---|
| POST válido | **200** con `totalValue: 1000`, vender META 500 y comprar AAPL 500 |
| JSON roto | **400** con `Solicitud inválida.` |
| Porcentajes que no suman 100 | **422** con `Los porcentajes de la distribución objetivo deben sumar 100.` |
| OPTIONS desde `https://rebalancer.ndiaz.dev` | **204** con `Access-Control-Allow-Origin` correcto, `Vary: Origin` y los métodos permitidos |
| OPTIONS desde un origen ajeno | **204 sin** cabeceras CORS, que es lo esperado |
| GET `/otra` | **404** con `No encontrado.` |

Con esto la estrategia `vercel-php` es viable. La variable `ALLOWED_ORIGINS` se lee correctamente en producción, y el contrato de la API es idéntico al local.

## Dos observaciones
- **PHP 8.5.12 en producción**, mientras que local usas 8.3.20. Los resultados son idénticos y no vi avisos de deprecación en las respuestas, pero conviene tenerlo presente.
- **`Cache-Control: public, max-age=0, must-revalidate`** viene de Vercel. No causa problemas en POST, así que no hace falta cambiar nada.

## Paso siguiente: desplegar el frontend
Antes de empezar, hay un detalle importante. El backend solo acepta CORS de `https://rebalancer.ndiaz.dev`. La URL temporal del frontend será distinta, así que el navegador bloqueará las llamadas hasta que la agregues.

1. Ve a vercel.com/new, importa de nuevo `portfolio-rebalancer` y crea un **segundo proyecto**.
2. Completa así:

| Campo | Valor |
|---|---|
| **Project Name** | `portfolio-rebalancer` (o `portfolio-rebalancer-frontend`) |
| **Framework Preset** | **Vite** |
| **Root Directory** | `frontend` |
| **Build Command** | `npm run build` (el valor por defecto) |
| **Output Directory** | `dist` (el valor por defecto) |
| **Install Command** | `npm install` (el valor por defecto) |

3. En **Environment Variables** agrega:

| Name | Value |
|---|---|
| `VITE_API_URL` | `https://portfolio-rebalancer-backend.vercel.app` |

   Sin barra final, porque el código concatena `/rebalance`. Vite incrusta esta variable al compilar, así que si la cambias después debes hacer redeploy.
4. Pulsa **Deploy** y anota la URL que te muestre, por ejemplo `https://portfolio-rebalancer.vercel.app`.
5. Ahora **actualiza el backend**. Entra al proyecto `portfolio-rebalancer-backend` y ve a **Settings → Environment Variables**. Edita `ALLOWED_ORIGINS` y pon las dos URLs separadas por coma, sin espacios ni barra final:

   `https://rebalancer.ndiaz.dev,https://portfolio-rebalancer.vercel.app`

   Luego haz **Redeploy** del backend. Sin el redeploy no surte efecto.
6. Si Vercel pone *Deployment Protection* en el frontend, desactívala en Production. De lo contrario, tus visitas verán una pantalla de login.

## Pruebas del frontend
1. Abre la URL del frontend y recarga con Ctrl+F5.
2. Pulsa **Rebalancear** con los datos de ejemplo. Debe aparecer la tabla de recomendaciones.
3. Pon porcentajes que no sumen 100. Debe aparecer el mensaje de error del backend en español.
4. Abre DevTools → Network. Debe verse un `OPTIONS` 204 y luego un `POST` 200 hacia `portfolio-rebalancer-backend.vercel.app`.

## Si falla
- **"Failed to fetch" o error CORS en consola:** casi siempre es `ALLOWED_ORIGINS` sin la URL del frontend, con barra final o sin redeploy.
- **La app llama a `localhost:8000`:** `VITE_API_URL` no estaba definida al compilar. Agrégala y haz Redeploy.
- **404 al recargar:** no debería ocurrir, porque la app es de una sola página sin rutas.

Cuando tengas la URL del frontend, pásamela y repito las pruebas de CORS contra ella. Después de eso, los subdominios quedan para el paso final.
````

---

## Turno 44 · Natalia Díaz

_2026-11-05T17:35:25.840Z_

````text
El backend parece funcionar correctamente.

Validé:

- GET /rebalance -> {"error":"Método no permitido."}
- POST válido -> 200
- Request inválido -> 400
- Error de dominio -> 422
- CORS -> OK

Sin embargo, al abrir el frontend en:

https://frontend-delta-puce-71.vercel.app/

y presionar "Rebalancear", aparece:

"Error: No se pudo conectar con el servidor."

Por favor:

1. Analiza la causa más probable.
2. Verifica si el frontend fue compilado con la variable:

   VITE_API_URL=https://portfolio-rebalancer-backend.vercel.app

3. Indícame cómo comprobar desde el navegador qué URL está intentando consumir React.
4. Verifica si ALLOWED_ORIGINS debe incluir:

   https://frontend-delta-puce-71.vercel.app

5. Si es necesario, indícame exactamente qué variable debo modificar y si debo hacer redeploy del frontend, del backend o de ambos.

No asumas que el problema es CORS.
Primero quiero identificar exactamente qué URL está intentando consumir React.
````

### Respuesta del asistente (44.1)

_2026-11-05T17:35:32.825Z · modelo: claude-sonnet-5.5_

````markdown
Voy a inspeccionar el bundle desplegado para ver con qué URL se compiló.
````

### Respuesta del asistente (44.2)

_2026-11-05T17:35:53.227Z · modelo: claude-sonnet-5.5_

````markdown
## Diagnóstico

El problema **no es `VITE_API_URL`, es CORS**. Lo comprobé en tu despliegue real.

**1. URL con la que se compiló React.** Descargué el bundle `/assets/index-C1m5MDzd.js` de tu frontend y contiene `https://portfolio-rebalancer-backend.vercel.app`. No aparece `localhost` ni `8000`. La variable llegó bien, así que no hace falta redeploy del frontend por este motivo.

**2. Preflight desde tu frontend.** Pedí un `OPTIONS` al backend con `Origin: https://frontend-delta-puce-71.vercel.app`. El backend respondió **204 sin ninguna cabecera `Access-Control-Allow-*`**. Sin esa cabecera, el navegador bloquea el `POST` antes de enviarlo. React captura el fallo de red y muestra "No se pudo conectar con el servidor". Es la señal clásica de un bloqueo CORS.

**3. Causa.** `ALLOWED_ORIGINS` en el backend todavía solo contiene `https://rebalancer.ndiaz.dev`. Falta la URL de tu frontend, que es distinta a la que yo supuse.

## Qué hacer
Solo hay que cambiar el **backend**. El frontend queda como está.

1. Entra al proyecto `portfolio-rebalancer-backend` y ve a **Settings → Environment Variables**.
2. Edita `ALLOWED_ORIGINS` y deja este valor exacto, sin espacios ni barra final:

   `https://rebalancer.ndiaz.dev,https://frontend-delta-puce-71.vercel.app`

3. Haz **Redeploy** del backend. Sin redeploy, la variable no se aplica.
4. Recarga el frontend con Ctrl+F5 y vuelve a probar.

## Cómo comprobarlo tú mismo
**En el navegador (el paso que pediste):**
1. Abre el frontend y pulsa F12 → pestaña **Network**.
2. Pulsa **Rebalancear**.
3. Busca la petición `rebalance`. Dos señales:
   - Si es `rebalance` con estado **(failed)** o **CORS error**, y la columna *Domain* muestra `portfolio-rebalancer-backend.vercel.app`, la URL es correcta y falla CORS.
   - Si apuntara a `localhost:8000`, sería un problema de la variable. No es tu caso.
4. En **Console** verás un mensaje del tipo *"blocked by CORS policy: No 'Access-Control-Allow-Origin' header"*.

**Verificación después del redeploy:**
```powershell
curl.exe -s -i -X OPTIONS https://portfolio-rebalancer-backend.vercel.app/rebalance -H "Origin: https://frontend-delta-puce-71.vercel.app" | Select-String "HTTP|Access-Control"
```
Debe aparecer `Access-Control-Allow-Origin: https://frontend-delta-puce-71.vercel.app`. Si quieres, avísame cuando hagas el redeploy y lo vuelvo a verificar yo.

## Nota
Esa URL `frontend-delta-puce-71.vercel.app` es el dominio de producción asignado por Vercel. Las URL de preview (una por cada commit o rama) tienen otro formato y no estarán en la lista, y es lo deseable. Cuando crees `rebalancer.ndiaz.dev`, quedará cubierto porque ya está en la variable.
````

