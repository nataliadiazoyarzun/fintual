# Conversaciones completas de IA

Este documento es el índice de la transcripción completa de las conversaciones con el asistente de IA durante el desarrollo de **Portfolio Rebalancer**. Para una explicación del proceso y de las responsabilidades de la autora, ver el resumen en [llm-conversations.md](llm-conversations.md).

## Cómo se obtuvo

Se exportó del registro de eventos de la sesión de trabajo, sin editar el contenido. Son 47 mensajes de la autora y 132 respuestas del asistente, en orden cronológico, divididos en siete archivos por tema.

## Partes

| # | Archivo | Contenido |
|---|---|---|
| 1 | [01-design.md](llm-transcripts/01-design.md) | Revisión del problema, decisiones de diseño, modelo de dominio, stack y estructura de carpetas |
| 2 | [02-domain-tdd.md](llm-transcripts/02-domain-tdd.md) | `Stock` y `Portfolio` con TDD (RED → GREEN → REFACTOR), validaciones, normalización, épsilon y orden determinista |
| 3 | [03-api.md](llm-transcripts/03-api.md) | `RebalanceController`, validación de tipos (400 vs 422) y `public/index.php` con CORS |
| 4 | [04-frontend.md](llm-transcripts/04-frontend.md) | React, formulario, tabla de resultados, español, Bootstrap, accesibilidad y branding |
| 5 | [05-readme.md](llm-transcripts/05-readme.md) | Redacción del README final |
| 6 | [06-deployment.md](llm-transcripts/06-deployment.md) | Estrategia de despliegue, `vercel-php`, despliegue en Vercel y diagnóstico de CORS |
| 7 | [07-closing-docs.md](llm-transcripts/07-closing-docs.md) | Capturas reales, README final y documentación del uso de IA |

## Qué incluye y qué no

- **Incluye** el texto literal de cada mensaje de la autora y de cada respuesta del asistente, con su hora (UTC) y el modelo que respondió.
- **No incluye** las llamadas a herramientas (lectura y edición de archivos, comandos, navegador) ni su salida, ni el razonamiento interno del asistente. Los resultados relevantes, como las salidas de PHPUnit y de `curl`, aparecen citados en las respuestas del asistente.
- Los mensajes del asistente van dentro de bloques de código para conservar su formato original sin mezclarse con los títulos de este documento.
- El último turno (7) es el que originó esta exportación, así que su respuesta final no está incluida.
- El turno 46 está casi vacío (solo un `+`) porque corresponde a un mensaje enviado con adjuntos del editor.
- Tras la exportación se revisó que no hubiera credenciales, claves ni rutas personales.
