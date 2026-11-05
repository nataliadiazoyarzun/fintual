# Uso de IA durante el desarrollo

Este documento describe cómo se utilizó inteligencia artificial en el desarrollo de **Portfolio Rebalancer**, una prueba técnica para Fintual. Se incluye para que quien revise el proyecto sepa qué parte nació de la asistencia y qué parte fue decisión propia.

## Herramientas utilizadas

- **Asistente de IA en modo agente dentro de Visual Studio Code (GitHub Copilot).** Se usó durante todo el proyecto, en una conversación continua. Podía leer y editar archivos del repositorio, ejecutar comandos (PHPUnit, `npm run build`, `curl`) y abrir un navegador para verificar la interfaz.
- **Herramientas de verificación automatizada** que el asistente ejecutó bajo mi dirección: PHPUnit, el build de Vite, `curl` contra la API y Playwright para las capturas y las comprobaciones de la interfaz.

No se usaron otras herramientas de IA en el proyecto.

## Cómo se utilizó

El asistente actuó como un tech lead que acompaña paso a paso, con un rol distinto en cada fase:

1. **Análisis y diseño, sin código.** Revisó mi interpretación del enunciado, señaló supuestos dudosos y casos borde, y evaluó si el alcance era razonable para una prueba de aproximadamente una hora. Propuso el modelo de dominio y la estructura de carpetas antes de escribir nada.
2. **Dominio con TDD.** Trabajamos en ciclos RED → GREEN → REFACTOR. En cada ciclo el asistente escribía primero el test, se ejecutaba PHPUnit para confirmar que fallaba por la razón esperada, luego se implementaba el cambio mínimo y se volvía a ejecutar. Yo confirmaba cada fase y hacía el commit.
3. **API HTTP y frontend.** Se aplicó el mismo ritmo incremental al controller (con tests), al endpoint con CORS y a la interfaz React, que se construyó por capas: cliente de API, estado, formulario, tabla de resultados y estilos.
4. **Revisiones de calidad.** Se le pidió una revisión de accesibilidad y responsive con hallazgos clasificados, la unificación del idioma a español, el ajuste visual y la extracción de la paleta desde mi portafolio.
5. **Despliegue.** Orientó la elección de Vercel con `vercel-php`, preparó los archivos de configuración y diagnosticó con pruebas reales por qué el frontend no podía llamar al backend (la variable `ALLOWED_ORIGINS` no incluía el dominio del frontend).
6. **Documentación.** Redactó el borrador del README y de este documento, que revisé y ajusté.

## Qué fue responsabilidad de la autora

- **Definir el problema y las decisiones de producto.** Entre ellas: permitir fracciones de acciones, tratar los tickers fuera de la distribución como objetivo 0 %, devolver lista vacía para un portafolio vacío y no agregar tolerancia adicional.
- **Fijar el contrato de la API** (formato de solicitud y respuesta, códigos 200, 400 y 422) y el criterio de cuándo un error es de solicitud y cuándo de dominio.
- **Aprobar cada fase antes de continuar** y hacer los commits manualmente. Ningún cambio llegó al repositorio sin mi revisión.
- **Delimitar el alcance.** En varias ocasiones pedí no agregar reglas ni casos nuevos, y cerrar el dominio antes de pasar a la API.
- **La identidad visual y el idioma.** Pedí reducir el protagonismo del color lima y unificar toda la experiencia en español.
- **Probar manualmente la API y la interfaz**, y operar la cuenta de Vercel: crear los proyectos, configurar las variables de entorno y el dominio.
- **Revisar el resultado final**, incluidos los textos, los enlaces y las capturas.

## Límites y verificación

- El asistente propone y yo decido. Cuando una sugerencia no encajaba con el alcance, se descartó.
- Se detectaron y corrigieron errores del propio asistente durante el proceso, por ejemplo un ejemplo de respuesta del README con montos incorrectos, que salió a la luz al ejecutar los `curl` reales.
- Lo que se afirma como verificado se comprobó ejecutando algo: tests, build, `curl` o el navegador. La accesibilidad solo se revisó de forma manual, sin auditorías automáticas ni lectores de pantalla reales.
- Este documento resume el proceso. **No es una transcripción literal**; el historial completo está en [llm-full-transcript.md](llm-full-transcript.md).
