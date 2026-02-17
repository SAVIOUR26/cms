<?php
if (!defined('ABSPATH')) exit;

/* ----------------------------- Brand config ----------------------------- */
function kxn_brand_config() {
    return [
        'brand'   => 'KandaNews',
        'country' => 'Uganda',
        'flag'    => '🇺🇬',
        'email'   => 'info@kandanews.africa',
        'links'   => [
            'switch_country' => 'https://kandanews.africa/#countries',
            'login'          => add_query_arg(['next' => '/user-dashboard/'], home_url('/login/')),
        ],
        'colors' => [
            'primary' => '#1e2b42',
            'accent'  => '#f05a1a',
            'light'   => '#f9f9f9',
            'ink'     => '#0f172a',
            'border'  => '#e5e7eb',
        ],
    ];
}

/* ----------------------------- Theme setup ------------------------------ */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['script', 'style', 'gallery', 'caption']);
    register_nav_menus(['primary' => __('Primary Menu', 'kandanews')]);
});

/* -------------------------- Helper: user phone -------------------------- */
function kxn_get_user_phone($user_id) {
    $phone = get_user_meta($user_id, 'phone', true);
    if (empty($phone)) $phone = get_user_meta($user_id, 'kanda_whatsapp', true);
    if (empty($phone)) $phone = get_user_meta($user_id, 'billing_phone', true);
    return $phone ?: '';
}

/* ------------------------- Enqueue scripts & styles --------------------- */
add_action('wp_enqueue_scripts', function () {
    $cfg = kxn_brand_config();

    wp_enqueue_style('kxn-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', [], '6.5.0');
    wp_enqueue_style('kxn-base', get_template_directory_uri() . '/assets/css/base.css', [], '1.0.2');

    $inline_css = sprintf(
        ':root{--primary:%1$s;--accent:%2$s;--light:%3$s;--ink:%4$s;--border:%5$s;}',
        esc_attr($cfg['colors']['primary']),
        esc_attr($cfg['colors']['accent']),
        esc_attr($cfg['colors']['light']),
        esc_attr($cfg['colors']['ink']),
        esc_attr($cfg['colors']['border'])
    );
    wp_add_inline_style('kxn-base', $inline_css);

    $main_js_path = get_template_directory() . '/assets/js/main.js';
    wp_enqueue_script('kxn-main', get_template_directory_uri() . '/assets/js/main.js', [], file_exists($main_js_path) ? filemtime($main_js_path) : '1.0.0', true);

    wp_localize_script('kxn-main', 'KANDA_BRAND', [
        'brand'   => $cfg['brand'],
        'country' => $cfg['country'],
        'flag'    => $cfg['flag'],
        'email'   => $cfg['email'],
        'links'   => $cfg['links'],
    ]);

    // Dashboard assets
    $is_dashboard_template = is_page_template('page-templates/user-dashboard.php') || 
                            is_page_template('user-dashboard.php') || 
                            is_page('user-dashboard');

    if ($is_dashboard_template) {
        $dash_css_path = get_template_directory() . '/assets/css/kanda-dashboard.css';
        wp_enqueue_style('kanda-dashboard-styles', get_template_directory_uri() . '/assets/css/kanda-dashboard.css', [], file_exists($dash_css_path) ? filemtime($dash_css_path) : '1.0.3');

        $dash_js_file = get_template_directory() . '/assets/js/kanda-dashboard.js';
        wp_enqueue_script(
            'kanda-dashboard-js',
            get_template_directory_uri() . '/assets/js/kanda-dashboard.js',
            ['kxn-main'],
            file_exists($dash_js_file) ? filemtime($dash_js_file) : '1.0.3',
            true
        );

        // Localize dashboard data for DUAL GATEWAYS
        $current_user = wp_get_current_user();
        $profile_data = [
            'name'     => wp_strip_all_tags($current_user->display_name ?: $current_user->user_login),
            'email'    => $current_user->user_email,
            'whatsapp' => kxn_get_user_phone($current_user->ID),
            'category' => get_user_meta($current_user->ID, 'kanda_role', true) ?: get_user_meta($current_user->ID, 'kanda_category', true),
            'org'      => get_user_meta($current_user->ID, 'kanda_org', true),
        ];

        $script_data = [
            'restBaseFLW'  => esc_url_raw(rest_url('kanda-flw/v1')),
            'restBaseDPO'  => esc_url_raw(rest_url('kanda-dpo/v1')),
            'nonce'        => wp_create_nonce('wp_rest'),
            'user_profile' => $profile_data,
            'public_key'   => defined('FLUTTERWAVE_PUBLIC_KEY') ? FLUTTERWAVE_PUBLIC_KEY : '',
        ];

        wp_localize_script('kanda-dashboard-js', 'KandaDashboard', $script_data);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            wp_add_inline_script('kanda-dashboard-js', 'console.debug("KandaDashboard:", window.KandaDashboard);');
        }
    }
});

/* ---------------------------- Admin convenience ------------------------- */
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) return;
    
    $flw_configured = defined('FLUTTERWAVE_PUBLIC_KEY') && defined('FLUTTERWAVE_SECRET_KEY') && defined('FLUTTERWAVE_WEBHOOK_SECRET');
    $dpo_configured = defined('DPO_COMPANY_TOKEN') && defined('DPO_SERVICE_TYPE');
    
    if (!$flw_configured && !$dpo_configured) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Kanda Payments:</strong> No payment gateway configured. Configure at least one:</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Flutterwave:</strong> Add <code>FLUTTERWAVE_PUBLIC_KEY</code>, <code>FLUTTERWAVE_SECRET_KEY</code>, and <code>FLUTTERWAVE_WEBHOOK_SECRET</code> to wp-config.php</li>
                <li><strong>DPO:</strong> Add <code>DPO_COMPANY_TOKEN</code> and <code>DPO_SERVICE_TYPE</code> to wp-config.php</li>
            </ul>
        </div>
        <?php
    } elseif (!$flw_configured) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><strong>Kanda Payments:</strong> Only DPO is configured. Add Flutterwave keys for redundancy.</p>
        </div>
        <?php
    } elseif (!$dpo_configured) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><strong>Kanda Payments:</strong> Only Flutterwave is configured. Add DPO credentials for redundancy.</p>
        </div>
        <?php
    }
});

/**
 * Hide the Admin Bar for all non-admin users.
 */
add_action('after_setup_theme', 'kanda_remove_admin_bar');
function kanda_remove_admin_bar() {
    if (!current_user_can('manage_options')) {
        show_admin_bar(false);
    }
}