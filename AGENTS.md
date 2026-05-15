# Project Instructions

Estas instrucciones aplican al desarrollo de este tema de WordPress.

## WordPress Development

- No concentres toda la logica en `functions.php`. Este archivo debe funcionar como entry point principal para cargar directivas, hooks, includes y configuracion del tema.
- Modulariza de forma logica todo lo que implementes, tanto en codigo como en la estructura de directorios.
- Usa `template-parts/` para overrides o piezas reutilizables de templates cuando aplique.
- Implementa clases, integraciones y logica de negocio pura dentro de `includes/`.
- Implementa Custom Blocks de Gutenberg dentro de `custom-blocks/` o sigue la estructura existente del proyecto si ya hay un directorio equivalente.
- Sigue las convenciones de desarrollo de la comunidad de WordPress.
- No uses emojis en codigo, documentacion ni interfaz. Si hace falta representar algo visualmente, usa iconos o recursos graficos profesionales.
- Para frontend, prioriza responsive design y accesibilidad. Toda implementacion debe verse bien en desktop y mobile, y debe ser usable para personas con discapacidades.
- Documenta el codigo de forma clara y concisa. Agrega comentarios solo cuando ayuden a explicar logica o decisiones que no sean evidentes.
- Actualiza `README.md` cuando hagas cambios significativos en el tema, incluyendo nuevas funcionalidades, cambios de estructura de archivos o informacion relevante para otros desarrolladores o usuarios.

