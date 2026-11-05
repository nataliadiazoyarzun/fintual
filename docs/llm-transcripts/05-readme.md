# 05 · README

Redacción del README final en español.

> Exportación literal de los mensajes de la conversación, en orden cronológico. Las horas están en UTC. No incluye las llamadas a herramientas ni su salida, ni el razonamiento interno del asistente. Volver al [índice](../llm-full-transcript.md).

---

## Turno 39 · Natalia Díaz

_2026-11-05T16:18:53.331Z_

````text
Quiero escribir el README final del proyecto.

Contexto:

El proyecto ya está terminado.

Incluye:

- Dominio PHP modelado mediante TDD.
- PHPUnit.
- Arquitectura por capas.
- API HTTP.
- Frontend React.
- Bootstrap.
- Accesibilidad básica.
- Responsive móvil.
- Integración completa frontend/backend.

Repositorio:

https://github.com/nataliadiazoyarzun/portfolio-rebalancer

La aplicación forma parte de mi portafolio profesional:

https://www.ndiaz.dev/

Quiero un README profesional, claro y completo, escrito completamente en español.

Por favor:

1. Genera un README.md completo listo para copiar.

2. Incluye las siguientes secciones:

   - Título.
   - Descripción breve.
   - Captura o espacio reservado para screenshots.
   - Características principales.
   - Arquitectura.
   - Stack tecnológico.
   - Estructura del proyecto.
   - Cómo ejecutar el backend.
   - Cómo ejecutar el frontend.
   - Cómo ejecutar los tests.
   - Ejemplos de uso de la API.
   - Decisiones de diseño.
   - Accesibilidad y experiencia de usuario.
   - Próximos pasos o posibles mejoras.
   - Autora.

3. Explica brevemente:

   - Por qué se utilizó TDD.
   - Separación entre dominio, controller y frontend.
   - Distinción entre errores 400 y 422.

4. Menciona que fue desarrollado como prueba técnica para Fintual.

5. Mantén un tono profesional y técnico.

6. No inventes funcionalidades que no existen.

7. No exageres métricas.

8. Incluye enlaces a:

   - Portafolio: https://www.ndiaz.dev
   - Repositorio: https://github.com/nataliadiazoyarzun/portfolio-rebalancer

9. El README debe sentirse como el README de un proyecto profesional de portafolio, no como un trabajo académico.

10. Al final agrega una sección breve:

    "Sobre la autora"

    con:

    Natalia Díaz
    Senior Software Engineer
    https://www.ndiaz.dev
````

### Respuesta del asistente (39.1)

_2026-11-05T16:18:58.495Z · modelo: claude-sonnet-5.5_

````markdown
Primero verifico los datos reales del proyecto (estructura, scripts, tests, README actual) para no inventar nada.
````

### Respuesta del asistente (39.2)

_2026-11-05T16:19:57.920Z · modelo: claude-sonnet-5.5_

````markdown
Tengo todo lo necesario. Escribo el README (reemplazo el borrador actual).
````

### Respuesta del asistente (39.3)

_2026-11-05T16:20:04.403Z · modelo: claude-sonnet-5.5_

````markdown
Verifico que los ejemplos del README realmente respondan como documento.
````

### Respuesta del asistente (39.4)

_2026-11-05T16:20:13.761Z · modelo: claude-sonnet-5.5_

````markdown
Mi ejemplo de respuesta estaba mal: con ese request el monto real es 200 y no 400. Lo corrijo.
````

### Respuesta del asistente (39.5)

_2026-11-05T16:20:22.337Z · modelo: claude-sonnet-5.5_

````markdown
El README está escrito en [README.md](c:/xampp8/htdocs/fintual/README.md), en español y con todas las secciones que pediste. Ejecuté los tres ejemplos de `curl` contra la API real, y respondieron 200, 400 y 422 como indica el documento. Corrí la suite una vez más y terminó en `OK (50 tests, 144 assertions)`.

## Qué incluye
- **Presentación:** título, descripción, mención de Fintual y enlaces a [ndiaz.dev](https://www.ndiaz.dev) y al [repositorio](https://github.com/nataliadiazoyarzun/portfolio-rebalancer).
- **Capturas:** una tabla Escritorio/Móvil con marcadores de posición, porque todavía no hay imágenes en el repo.
- **Contenido técnico:** características, arquitectura en capas con diagrama, stack, estructura de carpetas, requisitos y cómo ejecutar backend, frontend y tests.
- **API:** la solicitud, la respuesta 200, los ejemplos con `curl` y los demás códigos (204, 404, 405).
- **Decisiones de diseño:** por qué TDD, la separación entre dominio, controller y frontend, la diferencia entre 400 y 422, y los supuestos y reglas de negocio.
- **Cierre:** accesibilidad y UX, próximos pasos y "Sobre la autora".

## Cosas a tener en cuenta
- **Un error mío.** Mi primer ejemplo de respuesta decía `amount: 400` y la API devuelve 200 para esos datos. Al ejecutar los `curl` lo vi y lo corregí.
- **Accesibilidad.** Aclaré que la verificación fue manual, con el navegador y el árbol de accesibilidad. No se usaron axe, Lighthouse ni lectores de pantalla reales, y no quise sugerir lo contrario.
- **Próximos pasos.** Solo propone mejoras que no existen hoy (tests de frontend, Docker, CI, acciones enteras, CORS por variables de entorno). No describe nada como ya hecho.
- **Métricas.** La única cifra es la de la suite de tests. Cambiará si agregas tests, así que actualízala entonces.
- **Escala de porcentajes.** El README indica que van de 0 a 100, que es como funciona la API hoy.

## Pendiente antes de publicar
1. Tomar las capturas, guardarlas por ejemplo en `docs/screenshots/` y reemplazar el marcador de la sección "Capturas".
2. Confirmar que el repositorio sea público y que la URL exista, porque el README y la cabecera de la app la enlazan.
3. Hay archivos `.gitkeep` en carpetas que ya tienen contenido (`src/Domain`, `src/Http`, `tests/Domain`, `tests/Http`). Son inofensivos, pero puedes borrarlos.

Mensaje de commit sugerido: `docs: write final README`.
````

