<?php
/**
 * Plugin Name: AI Bot Tracker
 * Plugin URI: https://github.com/konstantinwdk/ai-bot-tracker
 * Description: Rastrea y clasifica bots de IA que visitan tu sitio WordPress. Detecta GPTBot, ClaudeBot, Google-Extended y más de 30 bots.
 * Version: 1.5.0
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
    private $version = '1.0.0';

    public function __construct() {
        global $wpdb;
        $this->db_table = $wpdb->prefix . 'ai_bot_visits';

        // Hooks de activación/desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Hooks principales
        add_action('init', array($this, 'track_visit'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Activar plugin
     */
    public function activate() {
        $this->create_table();
        add_option('ai_bot_tracker_version', $this->version);
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
            INDEX bot_name_idx (bot_name),
            INDEX category_idx (category),
            INDEX visit_time_idx (visit_time)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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

        // Guardar en base de datos
        global $wpdb;
        $wpdb->insert($this->db_table, array(
            'bot_name' => $bot['name'],
            'category' => $bot['category'],
            'company' => $bot['company'],
            'user_agent' => substr($user_agent, 0, 500),
            'ip' => $this->get_client_ip(),
            'url' => substr($_SERVER['REQUEST_URI'] ?? '/', 0, 500),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'referer' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
            'visit_time' => current_time('mysql')
        ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
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

        // Manejar acciones
        if (isset($_POST['clear_data']) && check_admin_referer('clear_bot_data')) {
            $wpdb->query("TRUNCATE TABLE {$this->db_table}");
            echo '<div class="notice notice-success"><p>✅ Todos los datos han sido eliminados.</p></div>';
        }

        // Filtros
        $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
        $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

        $where = "WHERE visit_time >= DATE_SUB(NOW(), INTERVAL {$days} DAY)";
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

            <!-- Top Bots -->
            <div class="ai-bot-section">
                <h2>🏆 Top 10 Bots</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="30%">Bot</th>
                            <th width="25%">Empresa</th>
                            <th width="25%">Categoría</th>
                            <th width="20%">Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_bots)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 20px;">
                                No hay visitas de bots aún. Los datos aparecerán cuando los bots visiten tu sitio.
                            </td></tr>
                        <?php else: ?>
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
                            <th width="15%">Fecha/Hora</th>
                            <th width="20%">Bot</th>
                            <th width="35%">URL</th>
                            <th width="15%">IP</th>
                            <th width="15%">Categoría</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_visits)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">
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
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

            <div class="ai-bot-section">
                <h2>📖 Documentación</h2>
                <p>Para más información sobre cómo usar este plugin, visita:</p>
                <ul>
                    <li><a href="https://github.com/yourusername/ai-bot-tracker" target="_blank">Repositorio en GitHub</a></li>
                    <li><a href="https://github.com/yourusername/ai-bot-tracker/blob/main/README.md" target="_blank">Documentación Completa</a></li>
                    <li><a href="https://github.com/yourusername/ai-bot-tracker/blob/main/WORDPRESS-INTEGRATION.md" target="_blank">Guía de WordPress</a></li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Inicializar plugin
new AI_Bot_Tracker();
