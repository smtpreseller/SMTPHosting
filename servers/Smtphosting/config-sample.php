<?php
/**
 * SMTP Service Dashboard - Configuration
 * Author @ Smtphosting.com
 * Updated - 17th July, 2026
 */

if (!defined('WHMCS') && php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('No direct access');
}

return [

    /* ---------------------------------------------------------------
     * PRODUCT MATCHING
     * ------------------------------------------------------------- */
    'product' => [
        'group_names' => ['SMTP Mail Service'],
    ],

    /* ---------------------------------------------------------------
     * FEATURE TOGGLES
     * ------------------------------------------------------------- */
    'features' => [
        'stretch_full_width'       => true,
        'show_service_info_card'   => true,
        'show_quick_actions_card'  => true,
        'show_usage_stats'         => true,
        'show_usage_pie_chart'     => true,
        'show_usage_progress_bars' => true,
        'show_week_usage'          => true,
        'show_plan_info'           => true,
        'show_suspended_banner'    => true,
        'show_dns_section'         => true,
        'show_integration_cards'   => true,
        'show_mail_logs'           => true,
        'show_refresh_link'        => true,

        'quick_actions' => [
            'change_password' => true,
            'upgrade_plan'    => true,
            'renew_service'   => true,
            'cancel_service'  => true,
        ],

        'integration_protocols' => [
            'ssl'  => true,
            'tls'  => true,
            'none' => true,
        ],

        'dns_rows' => [
            'spf'   => true,
            'dmarc' => true,
            'dkim'  => true,
        ],

        'logs' => [
            'per_page'         => 10,
            'per_page_options' => [10, 25, 50, 100],
            'default_status'   => 'all', // all, success, failure, defer, inprogress
            'columns' => [
                'time'    => true,
                'from'    => true,
                'to'      => true,
                'host'    => true,
                'status'  => true,
                'details' => true,
            ],
        ],
    ],

    /* ---------------------------------------------------------------
     * COLOUR PALETTES
     * ------------------------------------------------------------- */
    'branding' => [

        'active_palette' => 'default',

        'palettes' => [
            'default'  => ['label' => 'SMTP Default', 'primary' => '#0077b6', 'primary-dark' => '#005885', 'primary-light' => '#e6f7ff', 'secondary' => '#5c7c8a', 'success' => '#2a9d6f', 'info' => '#17a2b8', 'warning' => '#f0a202', 'danger' => '#d64550', 'light' => '#f8f9fa', 'dark' => '#1c2b33'],
            'ocean'    => ['label' => 'Ocean Blue', 'primary' => '#006494', 'primary-dark' => '#003554', 'primary-light' => '#e3f2fd', 'secondary' => '#5c7a89', 'success' => '#2a9d8f', 'info' => '#48cae4', 'warning' => '#ee9b00', 'danger' => '#c94277', 'light' => '#f4faff', 'dark' => '#012a4a'],
            'sunset'   => ['label' => 'Sunset Orange', 'primary' => '#e85d04', 'primary-dark' => '#9d0208', 'primary-light' => '#fff3e0', 'secondary' => '#a86b4c', 'success' => '#588b3f', 'info' => '#e09f3e', 'warning' => '#ffba08', 'danger' => '#9d0208', 'light' => '#fff8f0', 'dark' => '#370617'],
            'forest'   => ['label' => 'Forest Green', 'primary' => '#2d6a4f', 'primary-dark' => '#1b4332', 'primary-light' => '#e9f5ec', 'secondary' => '#6b8a75', 'success' => '#40916c', 'info' => '#48cae4', 'warning' => '#d4a12b', 'danger' => '#b5484d', 'light' => '#f1faee', 'dark' => '#081c15'],
            'lime'     => ['label' => 'Lime Green', 'primary' => '#55AA3D', 'primary-dark' => '#1b4332', 'primary-light' => '#e9f5ec', 'secondary' => '#6b8a75', 'success' => '#40916c', 'info' => '#48cae4', 'warning' => '#d4a12b', 'danger' => '#b5484d', 'light' => '#f1faee', 'dark' => '#081c15'],
            'royal'    => ['label' => 'Royal Purple', 'primary' => '#5a189a', 'primary-dark' => '#3c096c', 'primary-light' => '#f3e8ff', 'secondary' => '#8a6ba8', 'success' => '#3d8a63', 'info' => '#7209b7', 'warning' => '#e0a02e', 'danger' => '#c9184a', 'light' => '#f8f5ff', 'dark' => '#240046'],
            'crimson'  => ['label' => 'Crimson Red', 'primary' => '#c1121f', 'primary-dark' => '#780000', 'primary-light' => '#fdecea', 'secondary' => '#a3696e', 'success' => '#4c8c4a', 'info' => '#b56576', 'warning' => '#e08c1e', 'danger' => '#9d0208', 'light' => '#fff5f5', 'dark' => '#370617'],
            'midnight' => ['label' => 'Midnight Slate', 'primary' => '#3a506b', 'primary-dark' => '#1c2541', 'primary-light' => '#eef1f6', 'secondary' => '#5c677d', 'success' => '#43aa8b', 'info' => '#5bc0be', 'warning' => '#d99a3d', 'danger' => '#c8524f', 'light' => '#f7f9fb', 'dark' => '#0b132b'],
            'coral'    => ['label' => 'Coral Reef', 'primary' => '#ff6b6b', 'primary-dark' => '#c92a2a', 'primary-light' => '#fff0f0', 'secondary' => '#c98a7d', 'success' => '#2fa88f', 'info' => '#22b8cf', 'warning' => '#fcc419', 'danger' => '#c1121f', 'light' => '#fff9f9', 'dark' => '#3d2a2a'],
            'slate'    => ['label' => 'Corporate Slate', 'primary' => '#495057', 'primary-dark' => '#212529', 'primary-light' => '#f1f3f5', 'secondary' => '#868e96', 'success' => '#2f9e44', 'info' => '#1971c2', 'warning' => '#e8890c', 'danger' => '#c92a2a', 'light' => '#f8f9fa', 'dark' => '#212529'],
            'emerald'  => ['label' => 'Emerald', 'primary' => '#0ca678', 'primary-dark' => '#087f5b', 'primary-light' => '#e6fcf5', 'secondary' => '#5c9e88', 'success' => '#2b8a3e', 'info' => '#0c8599', 'warning' => '#d99424', 'danger' => '#c94277', 'light' => '#f4fdf9', 'dark' => '#04331f'],
            'violet'   => ['label' => 'Electric Violet', 'primary' => '#7048e8', 'primary-dark' => '#4c2889', 'primary-light' => '#f3f0ff', 'secondary' => '#8b7bb8', 'success' => '#37b24d', 'info' => '#5c7cfa', 'warning' => '#e0932e', 'danger' => '#e64980', 'light' => '#faf9ff', 'dark' => '#25133f'],
        ],

        'theme_palette_overrides' => [
            // 'custom-theme-name' => 'ocean',
        ],
    ],

    /* ---------------------------------------------------------------
     * TEXT / LABELS
     * ------------------------------------------------------------- */
    'text' => [
        'panel_titles' => [
            'service_info'  => 'Service Information',
            'quick_actions' => 'Quick Actions',
            'usage_stats'   => 'SMTP Analytics',
            'dns_config'    => 'DNS Configuration',
            'integration'   => 'Integration Settings',
            'mail_logs'     => 'Sent Mail Logs',
        ],
        'labels' => [
            'package'             => 'Package',
            'server'              => 'Server',
            'domain'              => 'Domain',
            'prepay_balance'      => 'Prepay Balance',
            'change_password'     => 'Change Password',
            'change_password_sub' => 'Update SMTP credentials',
            'upgrade_plan'        => 'Upgrade Plan',
            'upgrade_plan_sub'    => 'Increase your limits',
            'renew_service'       => 'Renew Service',
            'renew_service_sub'   => 'Extend subscription',
            'cancel_service'      => 'Cancel Service',
            'cancel_service_sub'  => 'Terminate account',
            'sent_this_hour'      => 'Sent This Hour',
            'max_per_hour'        => 'Max Per Hour',
            'hourly_usage'        => 'Hourly Usage',
            'weekly_usage'        => 'Weekly Usage',
            'monthly_usage'       => 'Monthly Usage',
            'refresh'             => 'Refresh',
            'copy_hint'           => 'All records use the TXT type. Click any value to copy it.',
            'pie_success'         => 'Delivered',
            'pie_fail'            => 'Failed',
            'pie_defer'           => 'Deferred',
            'pie_inprogress'      => 'In Progress',
            'suspended_banner'    => 'Outgoing mail is currently suspended on this account. Contact support to resolve this before sending more mail.',
        ],
        'dns' => [
            'spf_valid'     => 'Your SPF record already authorizes our servers.',
            'spf_invalid'   => "Your SPF record doesn't include our sending host yet. Add the include snippet to your existing SPF record, don't create a second SPF record.",
            'dmarc_valid'   => 'DMARC record found and valid.',
            'dmarc_invalid' => 'No valid DMARC record was found. We recommend publishing the mentioned value.',
            'dkim_valid'    => 'DKIM is correctly configured and verified.',
            'dkim_invalid'  => 'DKIM does not match. Please publish the exact record at the host shown.',
        ],
        'plan_info' => [
            'transactional' => [
                'icon'        => 'fa fa-paper-plane',
                'badge'       => 'Triggered Email',
                'title'       => 'Transactional SMTP',
                'description' => 'Use this for order confirmations, OTPs, password resets, and other account-triggered emails only. Bulk or marketing email is not permitted on this plan. Never send unsolicited, spam, or phishing content.',
            ],
            'dedicated' => [
                'icon'        => 'fa fa-rocket',
                'badge'       => 'Bulk Marketing',
                'title'       => 'Dedicated SMTP',
                'description' => 'This plan supports both transactional emails and permission-based marketing or newsletter sends. Never send unsolicited, spam, or phishing content, accounts found in violation may be suspended.',
            ],
        ],
    ],

    /* ---------------------------------------------------------------
     * DNS HEALTH SCORE
     * ------------------------------------------------------------- */
    'dns_score' => [
        'weights' => ['spf' => 60, 'dmarc' => 25, 'dkim' => 15],

        'icons' => [
            'none' => 'fa fa-exclamation-triangle',
            'spf_only' => 'fa fa-info-circle',
            'spf_dmarc' => 'fa fa-check-circle',
            'all_valid' => 'fa fa-trophy',
        ],

        'tips' => [
            'none'      => 'No valid SPF record found. Add one first to authorize our servers to send on your behalf.',
            'spf_only'  => 'SPF is active, so you can start sending. We recommend also publishing a DMARC record for stronger deliverability.',
            'spf_dmarc' => 'SPF and DMARC are both valid, your domain is well configured for sending. Add DKIM if you notice occasional delivery issues.',
            'all_valid' => 'SPF, DMARC, and DKIM are all correctly configured. Your domain is fully optimized for email deliverability.',
        ],
    ],

    /* ---------------------------------------------------------------
     * ICONS (FontAwesome classes)
     * ------------------------------------------------------------- */
    'icons' => [
        'service_info'    => 'fa fa-id-card',
        'quick_actions'   => 'fa fa-rocket',
        'usage_stats'     => 'fa fa-chart-pie',
        'dns_config'      => 'fa fa-globe',
        'integration'     => 'fa fa-plug',
        'mail_logs'       => 'fa fa-envelope',
        'refresh'         => 'fa fa-sync-alt',
        'change_password' => 'fa fa-key',
        'upgrade_plan'    => 'fa fa-chart-line',
        'renew_service'   => 'fa fa-redo',
        'cancel_service'  => 'fa fa-times-circle',
        'ssl'             => 'fas fa-lock',
        'tls'             => 'fas fa-lock',
        'none'            => 'fas fa-lock-open',
        'copy'            => 'fa fa-qrcode',
        'valid'           => 'fas fa-check-circle',
        'invalid'         => 'fas fa-times-circle',
        'warning'         => 'fas fa-exclamation-triangle',
    ],

    /* ---------------------------------------------------------------
     * ASSETS
     * ------------------------------------------------------------- */
    'assets' => [
        'css_path' => '/modules/servers/Smtphosting/templates/assets/css/smtp.css?v=1',
    ],

    /* ---------------------------------------------------------------
     * DEFAULT SECTION HIDING
     * ------------------------------------------------------------- */
    'hide_classes' => [
        'default' => [
            '.col-md-3.pull-md-left.sidebar' => 'display:none!important;',
            '.col-lg-4.col-xl-3' => 'display:none!important;',
            '.col-md-9.pull-md-right' => 'width:100%!important;',
            '.primary-content' => 'max-width:100%!important;width:100%!important;',
            '.panel.panel-default.card.mb-3' => 'display:none!important;',
            '.sidebar.sidebar-primary,.main-sidebar,.panel.panel-default,.product-details.clearfix,.panel.panel-default.panel-product-details' => 'display:none!important;',
            '.main-content' => 'width: 100% !important; max-width: 100% !important',
            '.card-body,.nav.nav-tabs.nav-tabs-overflow,.tab-content.product-details-tab-container,.nav.nav-tabs.responsive-tabs-sm' => 'display:none!important;',
            '.col-lg-8.col-xl-9.primary-content' => 'flex: 0 0 100% !important;',
        ],
    ],

];