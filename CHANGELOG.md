# Changelog - AI Bot Tracker

Todos los cambios notables en este proyecto serán documentados en este archivo.

---

## [1.2.0] - 2025-10-18

### 🎨 Mejoras de UI

#### **Gráficos en Exportación HTML**
- ✅ Chart.js v4.4.0 integrado en exportación HTML
- ✅ Gráfico de dona de categorías en HTML exportado
- ✅ Gráfico de barras de Top 10 Bots en HTML exportado
- ✅ Gráfico de línea de tendencia temporal en HTML exportado
- ✅ Renderizado client-side (no requiere servidor)
- ✅ CSS optimizado para impresión/PDF
- ✅ Tooltips interactivos en HTML exportado

#### **Limpieza de Interfaz**
- ✅ Eliminada sección "Documentación y Soporte" de la página de configuración
- ✅ Eliminada sección "Sobre el Desarrollador" de la página de configuración
- ✅ Interfaz más limpia y profesional en ajustes

### 🔧 Cambios Técnicos

- ✅ Query optimizado para tendencia temporal (`visits_by_day`)
- ✅ Canvas elements con dimensiones fijas para consistencia
- ✅ JavaScript con event listener `window.load` para garantizar carga de Chart.js
- ✅ Grid CSS responsive para layout de gráficos (2 columnas + 1 full width)
- ✅ Colores consistentes entre dashboard y exportación
- ✅ Plugin URI actualizado a URL completa: `https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/`
- ✅ Enlace agregado al nombre del plugin en footer de exportación HTML

### 📊 Estadísticas

- **Versión**: 1.1.0 → 1.2.0
- **Líneas de código**: 2,294 (-18 desde v1.1.0)
- **Mejoras visuales**: 3 gráficos agregados a exportación HTML
- **Secciones eliminadas**: 2 (Documentación y Sobre el Desarrollador)

---

## [1.1.0] - 2025-10-18

### 🎉 Nuevas Características

#### **Sistema de Recurrencia de Visitas**
- ✅ Cálculo automático del tiempo entre visitas de cada bot
- ✅ Nueva columna `time_since_last_visit` en la base de datos
- ✅ Visualización de recurrencia promedio en Top 10 Bots
- ✅ Recurrencia individual en tabla de Últimas Visitas
- ✅ Formato inteligente: segundos → minutos → horas → días
- ✅ Exportaciones incluyen datos de recurrencia

#### **Sistema Completo de Bloqueo de Bots**
- ✅ Nueva tabla en BD: `wp_ai_bot_blocked`
- ✅ Bloqueo con respuesta HTTP 403 Forbidden
- ✅ Registro de fecha y usuario que bloqueó
- ✅ Verificación automática antes de rastrear visitas
- ✅ Los bots bloqueados no pueden acceder al sitio

#### **Página de Gestión de Bloqueos**
- ✅ Nueva subpágina: "Gestionar Bloqueos"
- ✅ Lista completa de TODOS los bots detectados (no solo 10)
- ✅ Checkboxes para selección individual y masiva
- ✅ Checkbox "Seleccionar todos" en el header
- ✅ Acciones masivas (bloquear/desbloquear múltiples bots)
- ✅ Sección destacada con bots bloqueados actualmente
- ✅ Desbloqueo rápido con botón individual
- ✅ Contador en tiempo real de bots bloqueados

#### **Búsqueda y Filtros Avanzados**
- ✅ Buscador de texto (busca en nombre y empresa)
- ✅ Filtro por estado (Todos / Solo activos / Solo bloqueados)
- ✅ Botón "Limpiar" para resetear filtros
- ✅ Conteo de resultados filtrados

#### **Atajos Rápidos por Categoría**
- ✅ Botón: Seleccionar AI Training
- ✅ Botón: Seleccionar AI Assistant
- ✅ Botón: Seleccionar Search Engines
- ✅ Botón: Seleccionar SEO Tools
- ✅ Botón: Seleccionar Web Scrapers
- ✅ Botón: Limpiar Selección

#### **Gráficos Visuales con Chart.js**
- ✅ Gráfico de dona: Visitas por Categoría
- ✅ Gráfico de barras horizontales: Top 10 Bots
- ✅ Gráfico de línea: Tendencia temporal de visitas
- ✅ Tooltips interactivos con información detallada
- ✅ Colores distintivos por categoría
- ✅ Responsive y adaptable a dispositivos móviles

#### **Exportación de Datos Mejorada**
- ✅ Exportación a CSV con recurrencia
- ✅ Exportación a JSON con estadísticas resumidas
- ✅ Exportación a HTML/PDF con gráficos visuales
- ✅ Formato UTF-8 con BOM para compatibilidad con Excel
- ✅ Nombres de archivo con fecha: `ai-bot-tracker-2025-10-18.csv`

### 🔧 Mejoras Técnicas

#### **Base de Datos**
- ✅ Sistema de migración automática para actualizar tablas existentes
- ✅ Nueva columna `time_since_last_visit` (INT, nullable)
- ✅ Nueva tabla `wp_ai_bot_blocked` con índices optimizados
- ✅ Método `upgrade_database()` para actualizaciones sin pérdida de datos
- ✅ Verificación de existencia de columnas antes de agregar

#### **Seguridad**
- ✅ Nonces de seguridad en todas las acciones de bloqueo
- ✅ Validación de capacidades (`manage_options`)
- ✅ Sanitización de todos los inputs del usuario
- ✅ Queries preparados con `$wpdb->prepare()`
- ✅ Validación de IPs con `filter_var(FILTER_VALIDATE_IP)`
- ✅ Corrección de SQL injection en filtros (línea 256)

#### **Performance**
- ✅ Limpieza de caché con `wp_cache_delete()`
- ✅ Cache busting en URLs con parámetro `v=timestamp`
- ✅ Queries optimizados con agrupación
- ✅ Índices en columnas de búsqueda frecuente
- ✅ Carga condicional de Chart.js solo en páginas del plugin

#### **UI/UX**
- ✅ Notificaciones con contadores ("3 bot(s) bloqueado(s)")
- ✅ Confirmaciones antes de acciones críticas
- ✅ Badges de categoría con colores distintivos
- ✅ Sección amarilla destacada para bots bloqueados
- ✅ Estados visuales: 🚫 Bloqueado / ✅ Activo
- ✅ Diseño responsive con CSS Grid

#### **Código**
- ✅ Modo debug integrado para diagnóstico
- ✅ Logging de errores en operaciones de BD
- ✅ Validación de existencia de tablas
- ✅ Manejo de errores en inserts/deletes
- ✅ Comentarios de código mejorados

### 🔄 Cambios en la Estructura

#### **Menú de WordPress Admin**
```
Bot Tracker
├── Bot Tracker (Dashboard)
├── Gestionar Bloqueos ← NUEVO
└── Configuración
```

#### **Dashboard Principal (Limpieza)**
- ❌ Eliminadas columnas "Estado" y "Acción" de Top 10 Bots
- ✅ Mantenida columna "Recurrencia Promedio"
- ✅ Gráficos agregados en sección dedicada
- ✅ Enfoque en estadísticas y análisis

#### **Archivos Modificados**
- `ai-bot-tracker.php` - Core del plugin (1,933 líneas)
- `README.md` - Documentación actualizada
- `CHANGELOG.md` - Este archivo

### 📊 Estadísticas

- **Bots detectados**: 30+ bots conocidos
- **Categorías**: 5 (AI Training, AI Assistant, Search Engine, SEO Tool, Web Scraper)
- **Líneas de código**: 1,933 (+601 desde v1.0.0)
- **Gráficos**: 3 visualizaciones interactivas
- **Formatos de exportación**: 3 (CSV, JSON, HTML)

### 🐛 Correcciones de Bugs

- ✅ Corregido SQL injection en filtro de días (línea 256)
- ✅ Corregida validación de IPs (método `get_client_ip()`)
- ✅ Corregido problema de actualización de contadores
- ✅ Corregido problema de caché en navegador
- ✅ Corregida detección de estado de bloqueo

### 📝 Actualizaciones de Metadatos

- **Versión**: 1.0.0 → 1.1.0
- **Autor**: Konstantin Koshkarev
- **Web**: https://webdesignerk.com
- **Plugin URI**: https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/

---

## [1.0.0] - 2025-10-15

### 🎉 Lanzamiento Inicial

#### **Características Principales**
- ✅ Detección automática de 30+ bots de IA
- ✅ Registro de visitas en base de datos MySQL
- ✅ Dashboard con estadísticas visuales
- ✅ Top 10 bots más activos
- ✅ Visitas por categoría
- ✅ URLs más visitadas
- ✅ Últimas 50 visitas registradas
- ✅ Filtros por período (7, 30, 90, 365 días)
- ✅ Filtros por categoría
- ✅ Página de configuración
- ✅ Lista de bots detectados
- ✅ Acción de limpieza de datos

#### **Bots Detectados**
- **AI Training**: GPTBot, ClaudeBot, Google-Extended, CCBot, etc.
- **AI Assistants**: ChatGPT-User, Claude-Web, PerplexityBot, etc.
- **Search Engines**: Googlebot, Bingbot, Applebot, etc.
- **SEO Tools**: AhrefsBot, SemrushBot, MozBot, etc.
- **Web Scrapers**: Scrapy, Diffbot, Omgilibot

#### **Tecnologías**
- PHP 7.0+
- WordPress 5.0+
- MySQL 5.6+
- Tailwind CSS (inline)
- JavaScript vanilla

#### **Seguridad**
- Protección contra acceso directo
- Nonces en formularios
- Sanitización de outputs
- Capability checks

---

## Próximas Versiones (Roadmap)

### [1.3.0] - Planificado
- [ ] Rate limiting automático basado en recurrencia
- [ ] Alertas por email cuando bot bloqueado intenta acceder
- [ ] Whitelist de IPs específicas
- [ ] Logs de intentos de acceso de bots bloqueados
- [ ] Gráfico de mapa de calor (horas del día con más visitas)
- [ ] Gráfico de distribución por compañías
- [ ] Comparación temporal (mes actual vs anterior)
- [ ] Exportación de gráficos como imagen PNG
- [ ] Programación de bloqueos temporales
- [ ] API REST para integración externa

### [2.0.0] - Futuro
- [ ] Integración con Google Analytics
- [ ] Machine learning para detectar nuevos bots
- [ ] Dashboard personalizable con widgets
- [ ] Múltiples usuarios con roles diferentes
- [ ] Integración con CloudFlare
- [ ] Modo SaaS multi-sitio

---

## Notas de Actualización

### Desde 1.1.0 → 1.2.0

#### **Cambios Visuales**
La actualización mejora la exportación HTML y limpia la interfaz de configuración:
1. Gráficos Chart.js ahora se incluyen en exportación HTML
2. Secciones de documentación eliminadas de la configuración

**No se pierden datos**. Sin cambios en la estructura de base de datos.

#### **Compatibilidad**
- ✅ Compatible con WordPress 5.0+
- ✅ Compatible con PHP 7.0+
- ✅ Compatible con todos los navegadores modernos
- ✅ Compatible con instalaciones existentes de v1.0.0 y v1.1.0

#### **Instrucciones de Actualización**
1. Descargar nueva versión
2. Reemplazar archivos del plugin
3. No es necesario desactivar/activar
4. Sin cambios en BD

### Desde 1.0.0 → 1.1.0

#### **Base de Datos**
La actualización agrega automáticamente:
1. Nueva columna `time_since_last_visit` a la tabla existente
2. Nueva tabla `wp_ai_bot_blocked`

**No se pierden datos**. La migración es automática al reactivar el plugin.

#### **Compatibilidad**
- ✅ Compatible con WordPress 5.0+
- ✅ Compatible con PHP 7.0+
- ✅ Compatible con todos los navegadores modernos
- ✅ Compatible con instalaciones existentes de v1.0.0

#### **Instrucciones de Actualización**
1. Descargar nueva versión
2. Desactivar plugin actual (opcional)
3. Reemplazar archivos del plugin
4. Activar plugin
5. La migración de BD se ejecuta automáticamente

---

## Soporte

**Desarrollador**: Konstantin Koshkarev
**Web**: [webdesignerk.com](https://webdesignerk.com)
**Contacto**: A través de webdesignerk.com

---

## Licencia

MIT License - Uso libre para proyectos personales y comerciales.

---

**¡Gracias por usar AI Bot Tracker!** 🤖
