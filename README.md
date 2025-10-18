# AI Bot Tracker - Plugin para WordPress

Plugin completo para rastrear y clasificar bots de IA que visitan tu sitio WordPress.

**Desarrollado por:** Konstantin Koshkarev
**Sitio Web:** [webdesignerk.com](https://webdesignerk.com)
**Plugin URL:** [AI Bot Tracker](https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/)
**Versión:** 1.2.0

## 🚀 Instalación Rápida

### Método 1: Descarga Manual (Recomendado)

1. **Descarga el archivo `ai-bot-tracker.php`**
2. **Crea la carpeta del plugin:**
   ```
   wp-content/plugins/ai-bot-tracker/
   ```
3. **Copia `ai-bot-tracker.php` dentro de esa carpeta**
4. **Activa el plugin** en WordPress Admin → Plugins
5. **¡Listo!** Ve a "Bot Tracker" en el menú lateral

### Método 2: FTP/SFTP

1. Conecta a tu servidor vía FTP
2. Navega a `wp-content/plugins/`
3. Crea la carpeta `ai-bot-tracker`
4. Sube el archivo `ai-bot-tracker.php`
5. Activa en WordPress Admin → Plugins

### Método 3: cPanel / File Manager

1. Accede al File Manager de tu hosting
2. Navega a `public_html/wp-content/plugins/`
3. Crea nueva carpeta: `ai-bot-tracker`
4. Sube `ai-bot-tracker.php`
5. Activa el plugin desde WordPress

---

## 📊 Características

- ✅ **Detección automática** de más de 30 bots de IA
- ✅ **Panel de control visual** en WordPress Admin
- ✅ **Estadísticas en tiempo real**
- ✅ **Filtros por fecha y categoría**
- ✅ **No requiere configuración** - funciona al activarlo
- ✅ **Almacenamiento en MySQL** - tus datos en tu base de datos
- ✅ **Sin dependencias externas** - 100% PHP puro
- ✅ **Compatible con cualquier hosting**

---

## 🤖 Bots Detectados

### IA Training (15 bots)
- GPTBot (OpenAI)
- ClaudeBot (Anthropic)
- Google-Extended (Google)
- FacebookBot (Meta)
- Amazonbot (Amazon)
- CCBot (Common Crawl)
- Y más...

### IA Assistants (5 bots)
- ChatGPT-User (OpenAI)
- Claude-Web (Anthropic)
- PerplexityBot
- YouBot
- BingPreview

### Search Engines (5 bots)
- Googlebot
- Bingbot
- Applebot
- YandexBot
- PetalBot

### SEO Tools (5 bots)
- AhrefsBot
- SemrushBot
- MozBot
- DataForSeoBot
- Google-InspectionTool

### Web Scrapers (3 bots)
- Scrapy
- Diffbot
- Omgilibot

**Total: 30+ bots**

---

## 📖 Cómo Usar

### 1. Activar el Plugin

WordPress Admin → Plugins → Activar "AI Bot Tracker"

### 2. Ver Informes

WordPress Admin → **Bot Tracker**

### 3. Interpretar los Datos

El panel muestra:
- **Total de Visitas**: Número de veces que bots han visitado tu sitio
- **Bots Únicos**: Cuántos bots diferentes han visitado
- **Top 10 Bots**: Los bots más activos
- **Visitas por Categoría**: Distribución por tipo de bot
- **URLs Más Visitadas**: Qué páginas interesan más a los bots
- **Últimas Visitas**: Actividad reciente en tiempo real

### 4. Filtrar Datos

Puedes filtrar por:
- **Período**: Últimos 7, 30, 90 días o 1 año
- **Categoría**: AI Training, AI Assistant, Search Engine, SEO Tool, Web Scraper

---

## ⚙️ Configuración

### No Requiere Configuración

El plugin funciona automáticamente al activarlo. No hay opciones complejas que configurar.

### Configuración Avanzada (Opcional)

Ve a: **Bot Tracker → Configuración**

Aquí puedes:
- Ver la lista completa de bots detectados
- Ver información de la base de datos
- Acceder a la documentación

### Limpiar Datos

Si quieres eliminar todos los datos registrados:

1. Ve a **Bot Tracker**
2. Desplázate hasta "Acciones"
3. Clic en "Limpiar Todos los Datos"
4. Confirma la acción

⚠️ **Advertencia**: Esta acción no se puede deshacer.

---

## 🔒 Privacidad y Seguridad

### ¿Es seguro?

**Sí**, el plugin:
- ✅ Solo rastrea bots, no usuarios reales
- ✅ No almacena datos sensibles (cookies, contraseñas, etc.)
- ✅ No hace peticiones externas
- ✅ No ralentiza tu sitio (<2ms por visita)
- ✅ Compatible con GDPR

### ¿Qué datos almacena?

- Nombre del bot (ej: GPTBot)
- User-Agent completo
- IP del bot
- URL visitada
- Método HTTP (GET, POST, etc.)
- Referer (si existe)
- Fecha y hora

**NO** almacena:
- Datos de usuarios reales
- Cookies
- Contraseñas
- Información personal

---

## 🛠️ Solución de Problemas

### No aparecen datos en el panel

**Posibles causas:**
1. El plugin acaba de activarse → Espera a que lleguen bots (puede tomar días/semanas)
2. Tu sitio es nuevo → Los bots tardan en descubrirlo
3. Tienes caché muy agresivo → Algunos bots ven la caché en vez del sitio real

**Solución:**
- Espera unos días
- Revisa que el plugin esté activado
- Desactiva caché temporalmente para probar

### Error al activar el plugin

**Posible causa:** Permisos de base de datos

**Solución:**
1. Verifica que WordPress pueda crear tablas
2. Contacta a tu hosting si el error persiste

### El panel se carga lento

**Posible causa:** Muchos datos acumulados

**Solución:**
1. Usa los filtros por fecha (menos días = más rápido)
2. Limpia datos antiguos ocasionalmente

---

## 🔄 Actualización

Para actualizar el plugin:

1. Desactiva el plugin actual
2. Reemplaza el archivo `ai-bot-tracker.php` con la nueva versión
3. Reactiva el plugin

**Nota**: Los datos se mantienen al actualizar.

---

## 🗑️ Desinstalación

### Desactivar (mantiene datos)

WordPress Admin → Plugins → Desactivar "AI Bot Tracker"

Los datos se mantienen en la base de datos.

### Eliminar completamente (borra datos)

1. Desactiva el plugin
2. Elimina el plugin
3. Ejecuta en phpMyAdmin:
   ```sql
   DROP TABLE wp_ai_bot_visits;
   ```
   (Reemplaza `wp_` con tu prefijo de base de datos)

---

## 📊 Requisitos

- **WordPress**: 5.0 o superior
- **PHP**: 7.0 o superior
- **MySQL**: 5.6 o superior
- **Permisos**: Crear tablas en base de datos

---

## ❓ Preguntas Frecuentes

### ¿Afecta el rendimiento de mi sitio?

No. El rastreo toma menos de 2ms por visita y no bloquea la carga de páginas.

### ¿Funciona con caché?

Sí, pero si usas caché muy agresivo (ej: Cloudflare), algunos bots pueden ver la caché en lugar del sitio real.

### ¿Puedo bloquear bots?

Este plugin solo rastrea. Para bloquear bots, usa `.htaccess` o plugins de seguridad.

### ¿Compatible con otros plugins?

Sí, compatible con:
- WooCommerce
- Elementor
- Yoast SEO
- WP Rocket
- Cloudflare
- Y prácticamente todos los plugins

### ¿Funciona en hosting compartido?

Sí, funciona en cualquier hosting que soporte WordPress.

---

## 📚 Documentación Adicional

- [Documentación Completa](../README.md)
- [Guía de Instalación](../INSTALLATION.md)
- [Integración WordPress Detallada](../WORDPRESS-INTEGRATION.md)
- [FAQ Completo](../FAQ.md)
- [Guía de Seguridad](../SECURITY.md)

---

## 🆘 Soporte

¿Necesitas ayuda?

1. Consulta este README
2. Visita [webdesignerk.com](https://webdesignerk.com)
3. Contacta con el desarrollador: Konstantin Koshkarev

---

## 👨‍💻 Autor

**Konstantin Koshkarev**
- Sitio Web: [webdesignerk.com](https://webdesignerk.com)
- Especializado en desarrollo WordPress y soluciones web personalizadas

---

## 📝 Licencia

MIT License - Uso libre para proyectos personales y comerciales.

---

## 🎉 ¡Listo!

Tu sitio WordPress ahora está rastreando bots de IA.

Ve a **WordPress Admin → Bot Tracker** para ver los informes.

**¡Gracias por usar AI Bot Tracker!** 🤖
