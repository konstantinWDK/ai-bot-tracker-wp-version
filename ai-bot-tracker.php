<?php
/**
 * Plugin Name: AI Bot Tracker
 * Plugin URI: https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/
 * Description: Rastrea y clasifica bots de IA que visitan tu sitio WordPress. Detecta GPTBot, ClaudeBot, Google-Extended y más de 30 bots.
 * Version: 1.2.0
 * Author: Konstantin Koshkarev
 * Author URI: https://webdesignerk.com
 * License: MIT
 * Text Domain: ai-bot-tracker
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class AI_Bot_Tracker {

    /**
     * Base de datos de bots conocidos
     */
    private $bots = array(
        // OpenAI
        'GPTBot' => array('name' => 'GPTBot', 'company' => 'OpenAI', 'category' => 'AI Training'),
        'ChatGPT-User' => array('name' => 'ChatGPT-User', 'company' => 'OpenAI', 'category' => 'AI Assistant'),

        // Anthropic
        'ClaudeBot' => array('name' => 'ClaudeBot', 'company' => 'Anthropic', 'category' => 'AI Training'),
        'anthropic-ai' => array('name' => 'anthropic-ai', 'company' => 'Anthropic', 'category' => 'AI Training'),
        'Claude-Web' => array('name' => 'Claude-Web', 'company' => 'Anthropic', 'category' => 'AI Assistant'),

        // Google
        'Google-Extended' => array('name' => 'Google-Extended', 'company' => 'Google', 'category' => 'AI Training'),
        'Googlebot' => array('name' => 'Googlebot', 'company' => 'Google', 'category' => 'Search Engine'),
        'Google-InspectionTool' => array('name' => 'Google-InspectionTool', 'company' => 'Google', 'category' => 'SEO Tool'),

        // Meta (Facebook)
        'FacebookBot' => array('name' => 'FacebookBot', 'company' => 'Meta', 'category' => 'AI Training'),
        'facebookexternalhit' => array('name' => 'FacebookBot', 'company' => 'Meta', 'category' => 'AI Training'),
        'Meta-ExternalAgent' => array('name' => 'Meta-ExternalAgent', 'company' => 'Meta', 'category' => 'AI Training'),

        // Microsoft
        'bingbot' => array('name' => 'Bingbot', 'company' => 'Microsoft', 'category' => 'Search Engine'),
        'BingPreview' => array('name' => 'BingPreview', 'company' => 'Microsoft', 'category' => 'AI Assistant'),

        // Otros grandes
        'Amazonbot' => array('name' => 'Amazonbot', 'company' => 'Amazon', 'category' => 'AI Training'),
        'Applebot' => array('name' => 'Applebot', 'company' => 'Apple', 'category' => 'Search Engine'),
        'Applebot-Extended' => array('name' => 'Applebot-Extended', 'company' => 'Apple', 'category' => 'AI Training'),

        // AI Assistants
        'PerplexityBot' => array('name' => 'PerplexityBot', 'company' => 'Perplexity AI', 'category' => 'AI Assistant'),
        'YouBot' => array('name' => 'YouBot', 'company' => 'You.com', 'category' => 'AI Assistant'),

        // AI Training
        'cohere-ai' => array('name' => 'cohere-ai', 'company' => 'Cohere', 'category' => 'AI Training'),
        'CCBot' => array('name' => 'CCBot', 'company' => 'Common Crawl', 'category' => 'AI Training'),
        'Bytespider' => array('name' => 'Bytespider', 'company' => 'ByteDance', 'category' => 'AI Training'),
        'AI2Bot' => array('name' => 'AI2Bot', 'company' => 'Allen Institute', 'category' => 'AI Training'),
        'HuggingFaceBot' => array('name' => 'HuggingFaceBot', 'company' => 'Hugging Face', 'category' => 'AI Training'),
        'ImagesiftBot' => array('name' => 'ImagesiftBot', 'company' => 'ImagesiftBot', 'category' => 'AI Training'),

        // SEO Tools
        'AhrefsBot' => array('name' => 'AhrefsBot', 'company' => 'Ahrefs', 'category' => 'SEO Tool'),
        'SemrushBot' => array('name' => 'SemrushBot', 'company' => 'Semrush', 'category' => 'SEO Tool'),
        'rogerbot' => array('name' => 'MozBot', 'company' => 'Moz', 'category' => 'SEO Tool'),
        'dotbot' => array('name' => 'MozBot', 'company' => 'Moz', 'category' => 'SEO Tool'),
        'DataForSeoBot' => array('name' => 'DataForSeoBot', 'company' => 'DataForSEO', 'category' => 'SEO Tool'),

        // Web Scrapers
        'Scrapy' => array('name' => 'Scrapy', 'company' => 'Various', 'category' => 'Web Scraper'),
        'Diffbot' => array('name' => 'Diffbot', 'company' => 'Diffbot', 'category' => 'Web Scraper'),
        'omgili' => array('name' => 'Omgilibot', 'company' => 'Omgili', 'category' => 'Web Scraper'),

        // Otros
        'YandexBot' => array('name' => 'YandexBot', 'company' => 'Yandex', 'category' => 'Search Engine'),
        'PetalBot' => array('name' => 'PetalBot', 'company' => 'Huawei', 'category' => 'Search Engine'),
    );

    private $db_table;
    private $blocked_bots_table;
    private $version = '1.2.0';

    public function __construct() {
        global $wpdb;
        $this->db_table = $wpdb->prefix . 'ai_bot_visits';
        $this->blocked_bots_table = $wpdb->prefix . 'ai_bot_blocked';

        // Hooks de activación/desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Hooks principales
        add_action('init', array($this, 'track_visit'));
        add_action('init', array($this, 'handle_export'));
        add_action('init', array($this, 'handle_block_action'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Activar plugin
     */
    public function activate() {
        $this->create_table();
        $this->create_blocked_table();

        $current_version = get_option('ai_bot_tracker_version', '0');
        if (version_compare($current_version, $this->version, '<')) {
            $this->upgrade_database();
        }

        update_option('ai_bot_tracker_version', $this->version);
    }

    /**
     * Desactivar plugin
     */
    public function deactivate() {
        // No hacemos nada al desactivar, mantenemos los datos
    }

    /**
     * Crear tabla en la base de datos
     */
    private function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->db_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bot_name VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL,
            company VARCHAR(100) NOT NULL,
            user_agent TEXT NOT NULL,
            ip VARCHAR(45) NOT NULL,
            url VARCHAR(500) NOT NULL,
            method VARCHAR(10) NOT NULL,
            referer VARCHAR(500),
            visit_time DATETIME NOT NULL,
            time_since_last_visit INT DEFAULT NULL COMMENT 'Segundos desde última visita del bot',
            INDEX bot_name_idx (bot_name),
            INDEX category_idx (category),
            INDEX visit_time_idx (visit_time)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Crear tabla de bots bloqueados
     */
    private function create_blocked_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->blocked_bots_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bot_name VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(50) NOT NULL,
            company VARCHAR(100) NOT NULL,
            blocked_at DATETIME NOT NULL,
            blocked_by INT NOT NULL,
            reason TEXT,
            INDEX bot_name_idx (bot_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Actualizar base de datos para nuevas versiones
     */
    private function upgrade_database() {
        global $wpdb;

        // Verificar si existe la columna time_since_last_visit
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$this->db_table} LIKE %s",
                'time_since_last_visit'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE {$this->db_table}
                ADD COLUMN time_since_last_visit INT DEFAULT NULL
                COMMENT 'Segundos desde última visita del bot'"
            );
        }
    }

    /**
     * Detectar si el User-Agent es un bot conocido
     */
    private function detect_bot($user_agent) {
        foreach ($this->bots as $pattern => $bot_info) {
            if (stripos($user_agent, $pattern) !== false) {
                return $bot_info;
            }
        }
        return null;
    }

    /**
     * Rastrear visita
     */
    public function track_visit() {
        // No rastrear admin ni AJAX
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Detectar bot
        $bot = $this->detect_bot($user_agent);

        if (!$bot) {
            return; // No es un bot conocido
        }

        global $wpdb;

        // Verificar si el bot está bloqueado
        if ($this->is_bot_blocked($bot['name'])) {
            // Bloquear el acceso
            $this->block_bot_access($bot['name']);
            return;
        }

        // Calcular tiempo desde última visita
        $last_visit = $wpdb->get_row($wpdb->prepare(
            "SELECT visit_time FROM {$this->db_table}
            WHERE bot_name = %s
            ORDER BY visit_time DESC
            LIMIT 1",
            $bot['name']
        ));

        $time_since_last = null;
        if ($last_visit) {
            $last_time = strtotime($last_visit->visit_time);
            $current_time = current_time('timestamp');
            $time_since_last = $current_time - $last_time;
        }

        // Guardar en base de datos
        $wpdb->insert($this->db_table, array(
            'bot_name' => $bot['name'],
            'category' => $bot['category'],
            'company' => $bot['company'],
            'user_agent' => substr($user_agent, 0, 500),
            'ip' => $this->get_client_ip(),
            'url' => substr($_SERVER['REQUEST_URI'] ?? '/', 0, 500),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'referer' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
            'visit_time' => current_time('mysql'),
            'time_since_last_visit' => $time_since_last
        ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'));
    }

    /**
     * Verificar si un bot está bloqueado
     */
    private function is_bot_blocked($bot_name) {
        global $wpdb;
        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->blocked_bots_table} WHERE bot_name = %s",
            $bot_name
        ));
        return $blocked > 0;
    }

    /**
     * Bloquear acceso del bot
     */
    private function block_bot_access($bot_name) {
        // Retornar 403 Forbidden
        status_header(403);
        nocache_headers();

        // Mensaje personalizado
        wp_die(
            '<h1>Acceso Denegado</h1><p>Este bot ha sido bloqueado por el administrador del sitio.</p>',
            'Bot Bloqueado',
            array(
                'response' => 403,
                'back_link' => false
            )
        );
    }

    /**
     * Manejar acciones de bloqueo/desbloqueo
     */
    public function handle_block_action() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        // Acción masiva desde formulario
        if (isset($_POST['ai_bot_bulk_action']) && isset($_POST['selected_bots'])) {
            if (!check_admin_referer('ai_bot_bulk_block')) {
                return;
            }

            $action = sanitize_text_field($_POST['ai_bot_bulk_action']);
            $selected_bots = array_map('sanitize_text_field', $_POST['selected_bots']);

            $count = 0;

            if ($action === 'block') {
                foreach ($selected_bots as $bot_name) {
                    // Verificar si ya está bloqueado
                    if ($this->is_bot_blocked($bot_name)) {
                        continue;
                    }

                    // Obtener información del bot
                    $bot_info = $wpdb->get_row($wpdb->prepare(
                        "SELECT category, company FROM {$this->db_table}
                        WHERE bot_name = %s
                        LIMIT 1",
                        $bot_name
                    ));

                    if ($bot_info) {
                        $result = $wpdb->insert($this->blocked_bots_table, array(
                            'bot_name' => $bot_name,
                            'category' => $bot_info->category,
                            'company' => $bot_info->company,
                            'blocked_at' => current_time('mysql'),
                            'blocked_by' => get_current_user_id(),
                            'reason' => 'Bloqueado masivamente desde el panel de administración'
                        ), array('%s', '%s', '%s', '%s', '%d', '%s'));

                        // Debug: Log si hay error
                        if ($result === false && $wpdb->last_error) {
                            error_log('Error al bloquear bot: ' . $wpdb->last_error);
                        }

                        $count++;
                    }
                }

                wp_redirect(add_query_arg(array(
                    'page' => 'ai-bot-tracker-blocks',
                    'blocked' => $count,
                    'v' => time()
                ), admin_url('admin.php')));
                exit;

            } elseif ($action === 'unblock') {
                foreach ($selected_bots as $bot_name) {
                    $wpdb->delete($this->blocked_bots_table, array('bot_name' => $bot_name), array('%s'));
                    $count++;
                }

                wp_redirect(add_query_arg(array(
                    'page' => 'ai-bot-tracker-blocks',
                    'unblocked' => $count,
                    'v' => time()
                ), admin_url('admin.php')));
                exit;
            }
        }

        // Acción individual desde URL (legacy)
        if (isset($_GET['ai_bot_action'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'ai_bot_block_action')) {
                return;
            }

            $action = sanitize_text_field($_GET['ai_bot_action']);
            $bot_name = isset($_GET['bot_name']) ? sanitize_text_field($_GET['bot_name']) : '';

            if (empty($bot_name)) {
                return;
            }

            if ($action === 'block') {
                $bot_info = $wpdb->get_row($wpdb->prepare(
                    "SELECT category, company FROM {$this->db_table}
                    WHERE bot_name = %s
                    LIMIT 1",
                    $bot_name
                ));

                if ($bot_info) {
                    $result = $wpdb->insert($this->blocked_bots_table, array(
                        'bot_name' => $bot_name,
                        'category' => $bot_info->category,
                        'company' => $bot_info->company,
                        'blocked_at' => current_time('mysql'),
                        'blocked_by' => get_current_user_id(),
                        'reason' => 'Bloqueado manualmente desde el panel de administración'
                    ), array('%s', '%s', '%s', '%s', '%d', '%s'));

                    // Debug: Log si hay error
                    if ($result === false && $wpdb->last_error) {
                        error_log('Error al bloquear bot individual: ' . $wpdb->last_error);
                    }

                    wp_redirect(add_query_arg(array(
                        'page' => 'ai-bot-tracker-blocks',
                        'blocked' => '1',
                        'v' => time()
                    ), admin_url('admin.php')));
                    exit;
                }
            } elseif ($action === 'unblock') {
                $wpdb->delete($this->blocked_bots_table, array('bot_name' => $bot_name), array('%s'));

                wp_redirect(add_query_arg(array(
                    'page' => 'ai-bot-tracker-blocks',
                    'unblocked' => '1',
                    'v' => time()
                ), admin_url('admin.php')));
                exit;
            }
        }
    }

    /**
     * Formatear tiempo de recurrencia
     */
    private function format_recurrence($seconds) {
        if ($seconds === null) {
            return 'Primera visita';
        }

        if ($seconds < 60) {
            return $seconds . ' segundos';
        } elseif ($seconds < 3600) {
            $minutes = round($seconds / 60);
            return $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
        } elseif ($seconds < 86400) {
            $hours = round($seconds / 3600);
            return $hours . ' hora' . ($hours > 1 ? 's' : '');
        } else {
            $days = round($seconds / 86400);
            return $days . ' día' . ($days > 1 ? 's' : '');
        }
    }

    /**
     * Calcular recurrencia promedio de un bot
     */
    private function get_average_recurrence($bot_name) {
        global $wpdb;

        $avg = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(time_since_last_visit) FROM {$this->db_table}
            WHERE bot_name = %s AND time_since_last_visit IS NOT NULL",
            $bot_name
        ));

        return $avg ? round($avg) : null;
    }

    /**
     * Obtener IP del cliente
     */
    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // Limpiar y validar IP
        $ip = trim($ip);

        // Validar que sea una IP válida (IPv4 o IPv6)
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        return substr($ip, 0, 45);
    }

    /**
     * Agregar menú en admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI Bot Tracker',
            'Bot Tracker',
            'manage_options',
            'ai-bot-tracker',
            array($this, 'admin_page'),
            'dashicons-analytics',
            30
        );

        add_submenu_page(
            'ai-bot-tracker',
            'Gestionar Bloqueos',
            'Gestionar Bloqueos',
            'manage_options',
            'ai-bot-tracker-blocks',
            array($this, 'blocks_page')
        );

        add_submenu_page(
            'ai-bot-tracker',
            'Configuración',
            'Configuración',
            'manage_options',
            'ai-bot-tracker-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Estilos para el admin
     */
    public function enqueue_admin_styles($hook) {
        if (strpos($hook, 'ai-bot-tracker') === false) {
            return;
        }

        // Encolar Chart.js desde CDN
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);

        wp_enqueue_style('ai-bot-tracker-admin', plugin_dir_url(__FILE__) . 'admin-style.css', array(), $this->version);
    }

    /**
     * Página principal del admin
     */
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        // Mensajes de notificación
        if (isset($_GET['blocked'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Bot bloqueado exitosamente.</p></div>';
        }
        if (isset($_GET['unblocked'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Bot desbloqueado exitosamente.</p></div>';
        }

        // Manejar acciones
        if (isset($_POST['clear_data']) && check_admin_referer('clear_bot_data')) {
            $wpdb->query("TRUNCATE TABLE {$this->db_table}");
            echo '<div class="notice notice-success"><p>✅ Todos los datos han sido eliminados.</p></div>';
        }

        // Obtener lista de bots bloqueados
        $blocked_bots = $wpdb->get_results("SELECT bot_name FROM {$this->blocked_bots_table}");
        $blocked_bot_names = array();
        foreach ($blocked_bots as $blocked) {
            $blocked_bot_names[] = $blocked->bot_name;
        }

        // Filtros
        $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
        $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

        $where = $wpdb->prepare("WHERE visit_time >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        if ($category) {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }

        // Estadísticas
        $total_visits = $wpdb->get_var("SELECT COUNT(*) FROM {$this->db_table} {$where}");
        $unique_bots = $wpdb->get_var("SELECT COUNT(DISTINCT bot_name) FROM {$this->db_table} {$where}");

        // Top bots
        $top_bots = $wpdb->get_results("
            SELECT bot_name, company, category, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY bot_name, company, category
            ORDER BY visits DESC
            LIMIT 10
        ");

        // Visitas por categoría
        $by_category = $wpdb->get_results("
            SELECT category, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY category
            ORDER BY visits DESC
        ");

        // Últimas visitas
        $recent_visits = $wpdb->get_results("
            SELECT * FROM {$this->db_table}
            {$where}
            ORDER BY visit_time DESC
            LIMIT 50
        ");

        // URLs más visitadas
        $top_urls = $wpdb->get_results("
            SELECT url, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY url
            ORDER BY visits DESC
            LIMIT 10
        ");

        // Visitas por día (últimos 30 días para el gráfico de tendencia)
        $visits_by_day = $wpdb->get_results("
            SELECT DATE(visit_time) as date, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY DATE(visit_time)
            ORDER BY date ASC
        ");

        ?>
        <div class="wrap ai-bot-tracker-admin">
            <h1>🤖 AI Bot Tracker</h1>
            <p class="description">Rastreo de bots de IA que visitan tu sitio WordPress</p>

            <!-- Filtros -->
            <div class="ai-bot-filters">
                <form method="get">
                    <input type="hidden" name="page" value="ai-bot-tracker">
                    <label>Período:
                        <select name="days" onchange="this.form.submit()">
                            <option value="7" <?php selected($days, 7); ?>>Últimos 7 días</option>
                            <option value="30" <?php selected($days, 30); ?>>Últimos 30 días</option>
                            <option value="90" <?php selected($days, 90); ?>>Últimos 90 días</option>
                            <option value="365" <?php selected($days, 365); ?>>Último año</option>
                        </select>
                    </label>
                    <label>Categoría:
                        <select name="category" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <option value="AI Training" <?php selected($category, 'AI Training'); ?>>AI Training</option>
                            <option value="AI Assistant" <?php selected($category, 'AI Assistant'); ?>>AI Assistant</option>
                            <option value="Search Engine" <?php selected($category, 'Search Engine'); ?>>Search Engine</option>
                            <option value="SEO Tool" <?php selected($category, 'SEO Tool'); ?>>SEO Tool</option>
                            <option value="Web Scraper" <?php selected($category, 'Web Scraper'); ?>>Web Scraper</option>
                        </select>
                    </label>
                </form>
            </div>

            <!-- Resumen -->
            <div class="ai-bot-summary">
                <div class="stat-box">
                    <div class="stat-number"><?php echo number_format($total_visits); ?></div>
                    <div class="stat-label">Total de Visitas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo number_format($unique_bots); ?></div>
                    <div class="stat-label">Bots Únicos</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="ai-bot-section">
                <h2>📊 Estadísticas Visuales</h2>
                <div class="charts-grid">
                    <!-- Gráfico de categorías (Dona) -->
                    <div class="chart-container">
                        <h3>Visitas por Categoría</h3>
                        <canvas id="categoryChart"></canvas>
                    </div>

                    <!-- Gráfico de top bots (Barras) -->
                    <div class="chart-container">
                        <h3>Top 10 Bots</h3>
                        <canvas id="botsChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de tendencia temporal (Línea) - Full width -->
                <div class="chart-container-full">
                    <h3>Tendencia de Visitas</h3>
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Top Bots -->
            <div class="ai-bot-section">
                <h2>🏆 Top 10 Bots</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="25%">Bot</th>
                            <th width="20%">Empresa</th>
                            <th width="25%">Categoría</th>
                            <th width="15%">Visitas</th>
                            <th width="15%">Recurrencia Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_bots)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">
                                No hay visitas de bots aún. Los datos aparecerán cuando los bots visiten tu sitio.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($top_bots as $bot):
                                $avg_recurrence = $this->get_average_recurrence($bot->bot_name);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($bot->bot_name); ?></strong></td>
                                <td><?php echo esc_html($bot->company); ?></td>
                                <td><span class="category-badge category-<?php echo sanitize_title($bot->category); ?>">
                                    <?php echo esc_html($bot->category); ?>
                                </span></td>
                                <td><strong><?php echo number_format($bot->visits); ?></strong></td>
                                <td>
                                    <?php if ($avg_recurrence): ?>
                                        <span style="color: #2271b1;">⏱️ <?php echo $this->format_recurrence($avg_recurrence); ?></span>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Visitas por Categoría -->
            <div class="ai-bot-section">
                <h2>📊 Visitas por Categoría</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($by_category)): ?>
                            <tr><td colspan="2" style="text-align: center; padding: 20px;">
                                No hay datos disponibles
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($by_category as $cat): ?>
                            <tr>
                                <td><span class="category-badge category-<?php echo sanitize_title($cat->category); ?>">
                                    <?php echo esc_html($cat->category); ?>
                                </span></td>
                                <td><strong><?php echo number_format($cat->visits); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top URLs -->
            <div class="ai-bot-section">
                <h2>🔗 URLs Más Visitadas</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th width="20%">Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_urls)): ?>
                            <tr><td colspan="2" style="text-align: center; padding: 20px;">
                                No hay datos disponibles
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($top_urls as $url_stat): ?>
                            <tr>
                                <td><code><?php echo esc_html($url_stat->url); ?></code></td>
                                <td><strong><?php echo number_format($url_stat->visits); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Últimas Visitas -->
            <div class="ai-bot-section">
                <h2>🕐 Últimas Visitas</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="12%">Fecha/Hora</th>
                            <th width="15%">Bot</th>
                            <th width="30%">URL</th>
                            <th width="12%">IP</th>
                            <th width="13%">Categoría</th>
                            <th width="18%">Recurrencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_visits)): ?>
                            <tr><td colspan="6" style="text-align: center; padding: 20px;">
                                No hay visitas registradas todavía
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_visits as $visit): ?>
                            <tr>
                                <td><?php echo esc_html(mysql2date('d/m/Y H:i', $visit->visit_time)); ?></td>
                                <td><strong><?php echo esc_html($visit->bot_name); ?></strong></td>
                                <td><code><?php echo esc_html($visit->url); ?></code></td>
                                <td><?php echo esc_html($visit->ip); ?></td>
                                <td><span class="category-badge category-<?php echo sanitize_title($visit->category); ?>">
                                    <?php echo esc_html($visit->category); ?>
                                </span></td>
                                <td>
                                    <small><?php echo $this->format_recurrence($visit->time_since_last_visit); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Exportar Datos -->
            <div class="ai-bot-section">
                <h2>📥 Exportar Datos</h2>
                <p>Descarga los datos en diferentes formatos. Los filtros aplicados arriba (período y categoría) se aplicarán a la exportación.</p>

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <?php
                    $export_url_base = admin_url('admin.php');
                    $export_params = array(
                        'page' => 'ai-bot-tracker',
                        'ai_bot_export' => '1',
                        'days' => $days,
                        'category' => $category,
                        '_wpnonce' => wp_create_nonce('ai_bot_export')
                    );
                    ?>

                    <a href="<?php echo esc_url(add_query_arg(array_merge($export_params, array('format' => 'csv')), $export_url_base)); ?>"
                       class="button button-primary">
                        📊 Descargar CSV
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(array_merge($export_params, array('format' => 'json')), $export_url_base)); ?>"
                       class="button button-primary">
                        📦 Descargar JSON
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(array_merge($export_params, array('format' => 'html')), $export_url_base)); ?>"
                       class="button button-primary">
                        📄 Descargar HTML/PDF
                    </a>
                </div>

                <div style="margin-top: 15px; padding: 10px; background: #f0f6fc; border-left: 4px solid #2271b1;">
                    <strong>Formato actual:</strong>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li><strong>CSV:</strong> Compatible con Excel, Google Sheets. Incluye todos los campos de visitas.</li>
                        <li><strong>JSON:</strong> Para desarrolladores. Incluye estadísticas resumidas y datos completos.</li>
                        <li><strong>HTML/PDF:</strong> Reporte visual completo, listo para imprimir o guardar como PDF.</li>
                    </ul>
                </div>
            </div>

            <!-- Acciones -->
            <div class="ai-bot-section">
                <h2>⚙️ Acciones</h2>
                <form method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar TODOS los datos? Esta acción no se puede deshacer.');">
                    <?php wp_nonce_field('clear_bot_data'); ?>
                    <button type="submit" name="clear_data" class="button button-secondary">
                        🗑️ Limpiar Todos los Datos
                    </button>
                </form>
            </div>

            <style>
                .ai-bot-tracker-admin {
                    max-width: 1400px;
                }
                .ai-bot-filters {
                    background: #fff;
                    padding: 15px 20px;
                    margin: 20px 0;
                    border-left: 4px solid #2271b1;
                }
                .ai-bot-filters label {
                    margin-right: 20px;
                }
                .ai-bot-summary {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                .stat-box {
                    background: #fff;
                    padding: 25px;
                    text-align: center;
                    border-left: 4px solid #2271b1;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .stat-number {
                    font-size: 36px;
                    font-weight: bold;
                    color: #2271b1;
                    margin-bottom: 5px;
                }
                .stat-label {
                    color: #666;
                    font-size: 14px;
                }
                .ai-bot-section {
                    background: #fff;
                    padding: 20px;
                    margin: 20px 0;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .ai-bot-section h2 {
                    margin-top: 0;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #f0f0f0;
                }
                .charts-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                    gap: 30px;
                    margin: 20px 0;
                }
                .chart-container {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                }
                .chart-container h3 {
                    margin-top: 0;
                    margin-bottom: 15px;
                    color: #555;
                    font-size: 16px;
                }
                .chart-container-full {
                    background: #fff;
                    padding: 20px;
                    margin-top: 30px;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                }
                .chart-container-full h3 {
                    margin-top: 0;
                    margin-bottom: 15px;
                    color: #555;
                    font-size: 16px;
                }
                .category-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: 500;
                }
                .category-ai-training {
                    background: #e3f2fd;
                    color: #1976d2;
                }
                .category-ai-assistant {
                    background: #f3e5f5;
                    color: #7b1fa2;
                }
                .category-search-engine {
                    background: #e8f5e9;
                    color: #388e3c;
                }
                .category-seo-tool {
                    background: #fff3e0;
                    color: #f57c00;
                }
                .category-web-scraper {
                    background: #fce4ec;
                    color: #c2185b;
                }
            </style>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Datos para gráfico de categorías
                const categoryData = {
                    labels: [<?php
                        $labels = array();
                        foreach ($by_category as $cat) {
                            $labels[] = "'" . esc_js($cat->category) . "'";
                        }
                        echo implode(',', $labels);
                    ?>],
                    datasets: [{
                        data: [<?php
                            $values = array();
                            foreach ($by_category as $cat) {
                                $values[] = $cat->visits;
                            }
                            echo implode(',', $values);
                        ?>],
                        backgroundColor: [
                            '#1976d2', // AI Training
                            '#7b1fa2', // AI Assistant
                            '#388e3c', // Search Engine
                            '#f57c00', // SEO Tool
                            '#c2185b'  // Web Scraper
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                };

                // Gráfico de categorías (Dona)
                const categoryCtx = document.getElementById('categoryChart');
                if (categoryCtx) {
                    new Chart(categoryCtx, {
                        type: 'doughnut',
                        data: categoryData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Datos para gráfico de top bots
                const botsData = {
                    labels: [<?php
                        $bot_labels = array();
                        foreach ($top_bots as $bot) {
                            $bot_labels[] = "'" . esc_js($bot->bot_name) . "'";
                        }
                        echo implode(',', $bot_labels);
                    ?>],
                    datasets: [{
                        label: 'Visitas',
                        data: [<?php
                            $bot_values = array();
                            foreach ($top_bots as $bot) {
                                $bot_values[] = $bot->visits;
                            }
                            echo implode(',', $bot_values);
                        ?>],
                        backgroundColor: '#2271b1',
                        borderColor: '#135e96',
                        borderWidth: 1
                    }]
                };

                // Gráfico de top bots (Barras horizontales)
                const botsCtx = document.getElementById('botsChart');
                if (botsCtx) {
                    new Chart(botsCtx, {
                        type: 'bar',
                        data: botsData,
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Visitas: ' + context.parsed.x;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }

                // Datos para gráfico de tendencia
                const trendData = {
                    labels: [<?php
                        $trend_labels = array();
                        foreach ($visits_by_day as $day) {
                            $date = new DateTime($day->date);
                            $trend_labels[] = "'" . $date->format('d/m') . "'";
                        }
                        echo implode(',', $trend_labels);
                    ?>],
                    datasets: [{
                        label: 'Visitas diarias',
                        data: [<?php
                            $trend_values = array();
                            foreach ($visits_by_day as $day) {
                                $trend_values[] = $day->visits;
                            }
                            echo implode(',', $trend_values);
                        ?>],
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2271b1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                };

                // Gráfico de tendencia (Línea)
                const trendCtx = document.getElementById('trendChart');
                if (trendCtx) {
                    new Chart(trendCtx, {
                        type: 'line',
                        data: trendData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Visitas: ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            }
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
    }

    /**
     * Manejar exportaciones
     */
    public function handle_export() {
        if (!isset($_GET['ai_bot_export']) || !isset($_GET['format'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para exportar datos.');
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'ai_bot_export')) {
            wp_die('Nonce inválido');
        }

        $format = sanitize_text_field($_GET['format']);
        $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
        $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

        switch ($format) {
            case 'csv':
                $this->export_csv($days, $category);
                break;
            case 'json':
                $this->export_json($days, $category);
                break;
            case 'html':
                $this->export_html($days, $category);
                break;
            default:
                wp_die('Formato no soportado');
        }
    }

    /**
     * Exportar a CSV
     */
    private function export_csv($days, $category) {
        global $wpdb;

        $where = $wpdb->prepare("WHERE visit_time >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        if ($category) {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }

        $results = $wpdb->get_results("SELECT * FROM {$this->db_table} {$where} ORDER BY visit_time DESC");

        // Headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ai-bot-tracker-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, array('Fecha/Hora', 'Bot', 'Empresa', 'Categoría', 'URL', 'IP', 'Método', 'Referer', 'Recurrencia (segundos)', 'User-Agent'));

        // Datos
        foreach ($results as $row) {
            fputcsv($output, array(
                $row->visit_time,
                $row->bot_name,
                $row->company,
                $row->category,
                $row->url,
                $row->ip,
                $row->method,
                $row->referer,
                $row->time_since_last_visit ?? 'N/A',
                $row->user_agent
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Exportar a JSON
     */
    private function export_json($days, $category) {
        global $wpdb;

        $where = $wpdb->prepare("WHERE visit_time >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        if ($category) {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }

        $results = $wpdb->get_results("SELECT * FROM {$this->db_table} {$where} ORDER BY visit_time DESC");

        // Estadísticas resumidas
        $total_visits = count($results);
        $unique_bots = count(array_unique(array_column($results, 'bot_name')));

        $bot_counts = array();
        $category_counts = array();

        foreach ($results as $row) {
            // Contar por bot
            if (!isset($bot_counts[$row->bot_name])) {
                $bot_counts[$row->bot_name] = 0;
            }
            $bot_counts[$row->bot_name]++;

            // Contar por categoría
            if (!isset($category_counts[$row->category])) {
                $category_counts[$row->category] = 0;
            }
            $category_counts[$row->category]++;
        }

        $export_data = array(
            'export_date' => date('Y-m-d H:i:s'),
            'period_days' => $days,
            'filter_category' => $category ? $category : 'all',
            'summary' => array(
                'total_visits' => $total_visits,
                'unique_bots' => $unique_bots,
                'bots_breakdown' => $bot_counts,
                'categories_breakdown' => $category_counts
            ),
            'visits' => $results
        );

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="ai-bot-tracker-' . date('Y-m-d') . '.json"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Exportar a HTML (reporte imprimible/PDF)
     */
    private function export_html($days, $category) {
        global $wpdb;

        $where = $wpdb->prepare("WHERE visit_time >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        if ($category) {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }

        // Estadísticas
        $total_visits = $wpdb->get_var("SELECT COUNT(*) FROM {$this->db_table} {$where}");
        $unique_bots = $wpdb->get_var("SELECT COUNT(DISTINCT bot_name) FROM {$this->db_table} {$where}");

        // Top bots
        $top_bots = $wpdb->get_results("
            SELECT bot_name, company, category, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY bot_name, company, category
            ORDER BY visits DESC
            LIMIT 10
        ");

        // Visitas por categoría
        $by_category = $wpdb->get_results("
            SELECT category, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY category
            ORDER BY visits DESC
        ");

        // Visitas recientes
        $recent_visits = $wpdb->get_results("
            SELECT * FROM {$this->db_table}
            {$where}
            ORDER BY visit_time DESC
            LIMIT 100
        ");

        // Visitas por día para gráfico de tendencia
        $visits_by_day = $wpdb->get_results("
            SELECT DATE(visit_time) as date, COUNT(*) as visits
            FROM {$this->db_table}
            {$where}
            GROUP BY DATE(visit_time)
            ORDER BY date ASC
        ");

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="ai-bot-tracker-' . date('Y-m-d') . '.html"');

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>AI Bot Tracker - Reporte <?php echo date('Y-m-d'); ?></title>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                h1 {
                    color: #2271b1;
                    border-bottom: 3px solid #2271b1;
                    padding-bottom: 10px;
                }
                h2 {
                    color: #555;
                    margin-top: 30px;
                    border-bottom: 2px solid #ddd;
                    padding-bottom: 5px;
                }
                .summary {
                    display: flex;
                    gap: 20px;
                    margin: 20px 0;
                }
                .stat-box {
                    background: #f5f5f5;
                    padding: 20px;
                    border-left: 4px solid #2271b1;
                    flex: 1;
                    text-align: center;
                }
                .stat-number {
                    font-size: 32px;
                    font-weight: bold;
                    color: #2271b1;
                }
                .stat-label {
                    color: #666;
                    margin-top: 5px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th {
                    background: #2271b1;
                    color: white;
                    padding: 12px;
                    text-align: left;
                }
                td {
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
                tr:nth-child(even) {
                    background: #f9f9f9;
                }
                .category-badge {
                    padding: 4px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: 500;
                    display: inline-block;
                }
                .category-ai-training { background: #e3f2fd; color: #1976d2; }
                .category-ai-assistant { background: #f3e5f5; color: #7b1fa2; }
                .category-search-engine { background: #e8f5e9; color: #388e3c; }
                .category-seo-tool { background: #fff3e0; color: #f57c00; }
                .category-web-scraper { background: #fce4ec; color: #c2185b; }
                .charts-section {
                    margin: 30px 0;
                    page-break-inside: avoid;
                }
                .charts-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 30px;
                    margin: 20px 0;
                }
                .chart-container {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                    page-break-inside: avoid;
                }
                .chart-container h3 {
                    margin-top: 0;
                    margin-bottom: 15px;
                    color: #555;
                    font-size: 16px;
                }
                .chart-container-full {
                    background: #fff;
                    padding: 20px;
                    margin-top: 30px;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                    page-break-inside: avoid;
                }
                .chart-container-full h3 {
                    margin-top: 0;
                    margin-bottom: 15px;
                    color: #555;
                    font-size: 16px;
                }
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #ddd;
                    color: #666;
                    font-size: 12px;
                }
                @media print {
                    .summary { page-break-after: avoid; }
                    table { page-break-inside: avoid; }
                    .charts-section { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <h1>🤖 AI Bot Tracker - Reporte</h1>
            <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p><strong>Período:</strong> Últimos <?php echo $days; ?> días</p>
            <?php if ($category): ?>
                <p><strong>Categoría filtrada:</strong> <?php echo esc_html($category); ?></p>
            <?php endif; ?>

            <div class="summary">
                <div class="stat-box">
                    <div class="stat-number"><?php echo number_format($total_visits); ?></div>
                    <div class="stat-label">Total de Visitas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo number_format($unique_bots); ?></div>
                    <div class="stat-label">Bots Únicos</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-section">
                <h2>📊 Estadísticas Visuales</h2>
                <div class="charts-grid">
                    <!-- Gráfico de categorías (Dona) -->
                    <div class="chart-container">
                        <h3>Visitas por Categoría</h3>
                        <canvas id="categoryChart" width="400" height="400"></canvas>
                    </div>

                    <!-- Gráfico de top bots (Barras) -->
                    <div class="chart-container">
                        <h3>Top 10 Bots</h3>
                        <canvas id="botsChart" width="400" height="400"></canvas>
                    </div>
                </div>

                <!-- Gráfico de tendencia temporal (Línea) - Full width -->
                <div class="chart-container-full">
                    <h3>Tendencia de Visitas</h3>
                    <canvas id="trendChart" width="800" height="300"></canvas>
                </div>
            </div>

            <h2>🏆 Top 10 Bots</h2>
            <table>
                <thead>
                    <tr>
                        <th>Bot</th>
                        <th>Empresa</th>
                        <th>Categoría</th>
                        <th>Visitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_bots as $bot): ?>
                    <tr>
                        <td><strong><?php echo esc_html($bot->bot_name); ?></strong></td>
                        <td><?php echo esc_html($bot->company); ?></td>
                        <td><span class="category-badge category-<?php echo sanitize_title($bot->category); ?>">
                            <?php echo esc_html($bot->category); ?>
                        </span></td>
                        <td><strong><?php echo number_format($bot->visits); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>📊 Visitas por Categoría</h2>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Visitas</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($by_category as $cat): ?>
                    <tr>
                        <td><span class="category-badge category-<?php echo sanitize_title($cat->category); ?>">
                            <?php echo esc_html($cat->category); ?>
                        </span></td>
                        <td><strong><?php echo number_format($cat->visits); ?></strong></td>
                        <td><?php echo round(($cat->visits / $total_visits) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>🕐 Últimas 100 Visitas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Bot</th>
                        <th>Categoría</th>
                        <th>URL</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_visits as $visit): ?>
                    <tr>
                        <td><?php echo esc_html($visit->visit_time); ?></td>
                        <td><strong><?php echo esc_html($visit->bot_name); ?></strong></td>
                        <td><span class="category-badge category-<?php echo sanitize_title($visit->category); ?>">
                            <?php echo esc_html($visit->category); ?>
                        </span></td>
                        <td style="font-size: 11px;"><?php echo esc_html($visit->url); ?></td>
                        <td><?php echo esc_html($visit->ip); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="footer">
                <p>Reporte generado por <a href="https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/" target="_blank"><strong>AI Bot Tracker v<?php echo $this->version; ?></strong></a></p>
                <p>Sitio: <?php echo get_bloginfo('name'); ?> (<?php echo get_site_url(); ?>)</p>
                <p>Plugin desarrollado por <strong>Konstantin Koshkarev</strong> - <a href="https://webdesignerk.com" target="_blank">webdesignerk.com</a></p>
            </div>

            <script>
            // Esperar a que Chart.js se cargue
            window.addEventListener('load', function() {
                // Datos para gráfico de categorías
                const categoryData = {
                    labels: [<?php
                        $labels = array();
                        foreach ($by_category as $cat) {
                            $labels[] = "'" . esc_js($cat->category) . "'";
                        }
                        echo implode(',', $labels);
                    ?>],
                    datasets: [{
                        data: [<?php
                            $values = array();
                            foreach ($by_category as $cat) {
                                $values[] = $cat->visits;
                            }
                            echo implode(',', $values);
                        ?>],
                        backgroundColor: [
                            '#1976d2', // AI Training
                            '#7b1fa2', // AI Assistant
                            '#388e3c', // Search Engine
                            '#f57c00', // SEO Tool
                            '#c2185b'  // Web Scraper
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                };

                // Gráfico de categorías (Dona)
                const categoryCtx = document.getElementById('categoryChart');
                if (categoryCtx) {
                    new Chart(categoryCtx, {
                        type: 'doughnut',
                        data: categoryData,
                        options: {
                            responsive: false,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Datos para gráfico de top bots
                const botsData = {
                    labels: [<?php
                        $bot_labels = array();
                        foreach ($top_bots as $bot) {
                            $bot_labels[] = "'" . esc_js($bot->bot_name) . "'";
                        }
                        echo implode(',', $bot_labels);
                    ?>],
                    datasets: [{
                        label: 'Visitas',
                        data: [<?php
                            $bot_values = array();
                            foreach ($top_bots as $bot) {
                                $bot_values[] = $bot->visits;
                            }
                            echo implode(',', $bot_values);
                        ?>],
                        backgroundColor: '#2271b1',
                        borderColor: '#135e96',
                        borderWidth: 1
                    }]
                };

                // Gráfico de top bots (Barras horizontales)
                const botsCtx = document.getElementById('botsChart');
                if (botsCtx) {
                    new Chart(botsCtx, {
                        type: 'bar',
                        data: botsData,
                        options: {
                            indexAxis: 'y',
                            responsive: false,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Visitas: ' + context.parsed.x;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }

                // Datos para gráfico de tendencia
                const trendData = {
                    labels: [<?php
                        $trend_labels = array();
                        foreach ($visits_by_day as $day) {
                            $date = new DateTime($day->date);
                            $trend_labels[] = "'" . $date->format('d/m') . "'";
                        }
                        echo implode(',', $trend_labels);
                    ?>],
                    datasets: [{
                        label: 'Visitas diarias',
                        data: [<?php
                            $trend_values = array();
                            foreach ($visits_by_day as $day) {
                                $trend_values[] = $day->visits;
                            }
                            echo implode(',', $trend_values);
                        ?>],
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2271b1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                };

                // Gráfico de tendencia (Línea)
                const trendCtx = document.getElementById('trendChart');
                if (trendCtx) {
                    new Chart(trendCtx, {
                        type: 'line',
                        data: trendData,
                        options: {
                            responsive: false,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Visitas: ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            }
                        }
                    });
                }
            });
            </script>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Página de gestión de bloqueos
     */
    public function blocks_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        // Mensajes de notificación
        if (isset($_GET['blocked'])) {
            $count = intval($_GET['blocked']);
            echo '<div class="notice notice-success is-dismissible"><p>✅ ' . $count . ' bot(s) bloqueado(s) exitosamente.</p></div>';
        }
        if (isset($_GET['unblocked'])) {
            $count = intval($_GET['unblocked']);
            echo '<div class="notice notice-success is-dismissible"><p>✅ ' . $count . ' bot(s) desbloqueado(s) exitosamente.</p></div>';
        }

        // Obtener todos los bots detectados
        $all_bots = $wpdb->get_results("
            SELECT bot_name, category, company, COUNT(*) as visits, MAX(visit_time) as last_visit
            FROM {$this->db_table}
            GROUP BY bot_name, category, company
            ORDER BY bot_name ASC
        ");

        // Limpiar caché de WordPress si existe
        wp_cache_delete('ai_bot_blocked_list', 'ai_bot_tracker');

        // Obtener bots bloqueados (sin caché)
        $blocked_bots = $wpdb->get_results("SELECT bot_name FROM {$this->blocked_bots_table}", ARRAY_A);
        $blocked_bot_names = array();
        foreach ($blocked_bots as $blocked) {
            $blocked_bot_names[] = $blocked['bot_name'];
        }

        // Debug mode: Activar solo si necesitas diagnosticar problemas
        $debug_mode = false; // Cambia a true si necesitas diagnosticar problemas
        if ($debug_mode && current_user_can('manage_options')) {
            // Verificar si la tabla existe
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->blocked_bots_table}'");

            echo '<div class="notice notice-info"><p><strong>DEBUG:</strong> Bots bloqueados en BD: ' . count($blocked_bot_names) . ' (' . implode(', ', $blocked_bot_names) . ')</p></div>';
            echo '<div class="notice notice-info"><p><strong>DEBUG:</strong> Tabla bloqueados: ' . $this->blocked_bots_table . '</p></div>';
            echo '<div class="notice notice-info"><p><strong>DEBUG:</strong> Query ejecutada correctamente: ' . ($blocked_bots !== false ? 'SÍ' : 'NO') . '</p></div>';
            echo '<div class="notice notice-' . ($table_exists ? 'success' : 'error') . '"><p><strong>DEBUG:</strong> ¿Tabla existe? ' . ($table_exists ? 'SÍ ✅' : 'NO ❌ - LA TABLA NO EXISTE') . '</p></div>';

            if ($wpdb->last_error) {
                echo '<div class="notice notice-error"><p><strong>DEBUG ERROR:</strong> ' . $wpdb->last_error . '</p></div>';
            }
        }

        // Filtro de búsqueda
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';

        // Aplicar filtros
        $filtered_bots = array();
        foreach ($all_bots as $bot) {
            // Filtro de búsqueda
            if ($search && stripos($bot->bot_name, $search) === false && stripos($bot->company, $search) === false) {
                continue;
            }

            // Verificar si está bloqueado
            $is_blocked = in_array($bot->bot_name, $blocked_bot_names, true);

            // Agregar propiedad al objeto
            $bot->is_blocked = $is_blocked;

            // Filtro de estado
            if ($filter_status === 'blocked' && !$is_blocked) {
                continue;
            }
            if ($filter_status === 'active' && $is_blocked) {
                continue;
            }

            $filtered_bots[] = $bot;
        }

        ?>
        <div class="wrap ai-bot-blocks-page">
            <h1>🚫 Gestionar Bloqueos de Bots</h1>
            <p class="description">Selecciona los bots que deseas bloquear o desbloquear. Los bots bloqueados recibirán un error 403 Forbidden.</p>

            <!-- Filtros -->
            <div class="ai-bot-filters" style="display: flex; gap: 15px; align-items: center; margin: 20px 0;">
                <form method="get" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="ai-bot-tracker-blocks">

                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                           placeholder="Buscar bot..." style="width: 250px;">

                    <select name="filter_status">
                        <option value="all" <?php selected($filter_status, 'all'); ?>>Todos los bots</option>
                        <option value="active" <?php selected($filter_status, 'active'); ?>>Solo activos</option>
                        <option value="blocked" <?php selected($filter_status, 'blocked'); ?>>Solo bloqueados</option>
                    </select>

                    <button type="submit" class="button">Filtrar</button>

                    <?php if ($search || $filter_status !== 'all'): ?>
                    <a href="<?php echo admin_url('admin.php?page=ai-bot-tracker-blocks'); ?>" class="button">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Resumen -->
            <div class="ai-bot-summary">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($all_bots); ?></div>
                    <div class="stat-label">Total de Bots Detectados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($blocked_bot_names); ?></div>
                    <div class="stat-label">Bots Bloqueados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($all_bots) - count($blocked_bot_names); ?></div>
                    <div class="stat-label">Bots Activos</div>
                </div>
            </div>

            <!-- Bots Bloqueados Actualmente -->
            <?php
            $blocked_details = $wpdb->get_results("SELECT * FROM {$this->blocked_bots_table} ORDER BY blocked_at DESC");
            if (!empty($blocked_details)):
            ?>
            <div class="ai-bot-section" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                <h2>🚫 Bots Bloqueados Actualmente (<?php echo count($blocked_details); ?>)</h2>
                <p style="margin-bottom: 15px;">
                    <strong>Estos bots están bloqueados y recibirán un error 403 Forbidden al intentar acceder.</strong>
                    Puedes desbloquearlos individualmente o seleccionarlos abajo para desbloqueo masivo.
                </p>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="25%">Bot</th>
                            <th width="20%">Empresa</th>
                            <th width="20%">Categoría</th>
                            <th width="20%">Bloqueado desde</th>
                            <th width="15%">Acción Rápida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocked_details as $blocked): ?>
                        <tr>
                            <td><strong><?php echo esc_html($blocked->bot_name); ?></strong></td>
                            <td><?php echo esc_html($blocked->company); ?></td>
                            <td>
                                <span class="category-badge category-<?php echo sanitize_title($blocked->category); ?>">
                                    <?php echo esc_html($blocked->category); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $blocked->blocked_at)); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(
                                    admin_url('admin.php?page=ai-bot-tracker-blocks&ai_bot_action=unblock&bot_name=' . urlencode($blocked->bot_name)),
                                    'ai_bot_block_action'
                                ); ?>" class="button button-small button-primary"
                                   onclick="return confirm('¿Desbloquear <?php echo esc_js($blocked->bot_name); ?>?');">
                                    ✅ Desbloquear
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 15px; padding: 10px; background: #fff; border-left: 3px solid #2271b1;">
                    <strong>💡 Tip:</strong> También puedes usar los filtros abajo para mostrar solo bots bloqueados y desbloquear varios a la vez.
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario de selección -->
            <form method="post" id="ai-bot-blocks-form">
                <?php wp_nonce_field('ai_bot_bulk_block'); ?>

                <div class="ai-bot-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2 style="margin: 0;">📋 Lista de Bots (<?php echo count($filtered_bots); ?>)</h2>

                        <div style="display: flex; gap: 10px; align-items: center;">
                            <select name="ai_bot_bulk_action" id="ai_bot_bulk_action" required>
                                <option value="">Acción masiva...</option>
                                <option value="block">🚫 Bloquear seleccionados</option>
                                <option value="unblock">✅ Desbloquear seleccionados</option>
                            </select>
                            <button type="submit" class="button button-primary" onclick="return confirmBulkAction();">
                                Aplicar
                            </button>
                        </div>
                    </div>

                    <?php if (empty($filtered_bots)): ?>
                        <p style="text-align: center; padding: 40px; color: #666;">
                            No se encontraron bots con los filtros aplicados.
                        </p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th width="5%" style="padding-left: 15px;">
                                        <input type="checkbox" id="select-all-bots">
                                    </th>
                                    <th width="25%">Bot</th>
                                    <th width="20%">Empresa</th>
                                    <th width="20%">Categoría</th>
                                    <th width="12%">Visitas</th>
                                    <th width="13%">Última Visita</th>
                                    <th width="10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered_bots as $bot): ?>
                                <tr>
                                    <td style="padding-left: 15px;">
                                        <input type="checkbox" name="selected_bots[]"
                                               value="<?php echo esc_attr($bot->bot_name); ?>"
                                               class="bot-checkbox">
                                    </td>
                                    <td><strong><?php echo esc_html($bot->bot_name); ?></strong></td>
                                    <td><?php echo esc_html($bot->company); ?></td>
                                    <td>
                                        <span class="category-badge category-<?php echo sanitize_title($bot->category); ?>">
                                            <?php echo esc_html($bot->category); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($bot->visits); ?></td>
                                    <td><?php echo esc_html(mysql2date('d/m/Y H:i', $bot->last_visit)); ?></td>
                                    <td>
                                        <?php if ($bot->is_blocked): ?>
                                            <span style="color: #d63638; font-weight: bold;">🚫 Bloqueado</span>
                                        <?php else: ?>
                                            <span style="color: #00a32a;">✅ Activo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Atajos rápidos -->
            <div class="ai-bot-section">
                <h2>⚡ Atajos Rápidos</h2>
                <p>Selecciona rápidamente grupos de bots por categoría:</p>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
                    <button type="button" class="button" onclick="selectByCategory('AI Training')">
                        Seleccionar AI Training
                    </button>
                    <button type="button" class="button" onclick="selectByCategory('AI Assistant')">
                        Seleccionar AI Assistant
                    </button>
                    <button type="button" class="button" onclick="selectByCategory('Search Engine')">
                        Seleccionar Search Engines
                    </button>
                    <button type="button" class="button" onclick="selectByCategory('SEO Tool')">
                        Seleccionar SEO Tools
                    </button>
                    <button type="button" class="button" onclick="selectByCategory('Web Scraper')">
                        Seleccionar Web Scrapers
                    </button>
                    <button type="button" class="button button-secondary" onclick="clearSelection()">
                        Limpiar Selección
                    </button>
                </div>
            </div>

            <style>
                .ai-bot-blocks-page .ai-bot-summary {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                .ai-bot-blocks-page .stat-box {
                    background: #fff;
                    padding: 25px;
                    text-align: center;
                    border-left: 4px solid #2271b1;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .ai-bot-blocks-page .stat-number {
                    font-size: 36px;
                    font-weight: bold;
                    color: #2271b1;
                    margin-bottom: 5px;
                }
                .ai-bot-blocks-page .stat-label {
                    color: #666;
                    font-size: 14px;
                }
                .ai-bot-blocks-page .ai-bot-section {
                    background: #fff;
                    padding: 20px;
                    margin: 20px 0;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .ai-bot-blocks-page .ai-bot-section h2 {
                    margin-top: 0;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #f0f0f0;
                }
                .ai-bot-filters {
                    background: #fff;
                    padding: 15px 20px;
                    border-left: 4px solid #2271b1;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .category-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: 500;
                }
                .category-ai-training { background: #e3f2fd; color: #1976d2; }
                .category-ai-assistant { background: #f3e5f5; color: #7b1fa2; }
                .category-search-engine { background: #e8f5e9; color: #388e3c; }
                .category-seo-tool { background: #fff3e0; color: #f57c00; }
                .category-web-scraper { background: #fce4ec; color: #c2185b; }
            </style>

            <script>
            // Seleccionar/deseleccionar todos
            document.getElementById('select-all-bots')?.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.bot-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            // Seleccionar por categoría
            function selectByCategory(category) {
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const categoryBadge = row.querySelector('.category-badge');
                    if (categoryBadge && categoryBadge.textContent.trim() === category) {
                        const checkbox = row.querySelector('.bot-checkbox');
                        if (checkbox) checkbox.checked = true;
                    }
                });
            }

            // Limpiar selección
            function clearSelection() {
                const checkboxes = document.querySelectorAll('.bot-checkbox');
                checkboxes.forEach(cb => cb.checked = false);
                document.getElementById('select-all-bots').checked = false;
            }

            // Confirmar acción masiva
            function confirmBulkAction() {
                const action = document.getElementById('ai_bot_bulk_action').value;
                const checked = document.querySelectorAll('.bot-checkbox:checked');

                if (!action) {
                    alert('Por favor selecciona una acción.');
                    return false;
                }

                if (checked.length === 0) {
                    alert('Por favor selecciona al menos un bot.');
                    return false;
                }

                const actionText = action === 'block' ? 'bloquear' : 'desbloquear';
                return confirm('¿Estás seguro de que quieres ' + actionText + ' ' + checked.length + ' bot(s)?');
            }
            </script>
        </div>
        <?php
    }

    /**
     * Página de configuración
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>⚙️ Configuración de AI Bot Tracker</h1>

            <div class="ai-bot-section">
                <h2>📊 Información del Plugin</h2>
                <table class="form-table">
                    <tr>
                        <th>Versión:</th>
                        <td><?php echo $this->version; ?></td>
                    </tr>
                    <tr>
                        <th>Autor:</th>
                        <td><strong>Konstantin Koshkarev</strong></td>
                    </tr>
                    <tr>
                        <th>Sitio Web:</th>
                        <td><a href="https://webdesignerk.com" target="_blank">webdesignerk.com</a></td>
                    </tr>
                    <tr>
                        <th>Más Info:</th>
                        <td><a href="https://webdesignerk.com/wordpress/plugins/ai-bot-tracker-wordpress/" target="_blank">AI Bot Tracker - Página del Plugin</a></td>
                    </tr>
                    <tr>
                        <th>Bots Detectados:</th>
                        <td><?php echo count($this->bots); ?> bots conocidos</td>
                    </tr>
                    <tr>
                        <th>Tabla de Base de Datos:</th>
                        <td><code><?php echo $this->db_table; ?></code></td>
                    </tr>
                </table>
            </div>

            <div class="ai-bot-section">
                <h2>🤖 Lista de Bots Detectados</h2>
                <p>Este plugin detecta automáticamente los siguientes bots:</p>

                <?php
                $bots_by_category = array();
                foreach ($this->bots as $bot) {
                    $bots_by_category[$bot['category']][] = $bot;
                }

                foreach ($bots_by_category as $category => $bots):
                ?>
                    <h3><?php echo esc_html($category); ?></h3>
                    <ul style="columns: 2; -webkit-columns: 2; -moz-columns: 2;">
                        <?php foreach ($bots as $bot): ?>
                            <li>
                                <strong><?php echo esc_html($bot['name']); ?></strong>
                                (<?php echo esc_html($bot['company']); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>

        </div>
        <?php
    }
}

// Inicializar plugin
new AI_Bot_Tracker();
