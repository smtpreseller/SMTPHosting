<?php
/**
 * Smtphosting Config Manager
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/* ==========================================================================
 * ADDON REGISTRATION
 * ======================================================================== */

function smtphosting_config()
{
    return [
        'name'        => 'Smtphosting Config Manager',
        'description' => 'Customize the branding, wording, and behavior of your SMTP Service client-area dashboard.',
        'version'     => '1.1',
        'author'      => 'Smtphosting.com',
        'fields'      => [],
    ];
}

function smtphosting_activate()
{
    return ['status' => 'success', 'description' => 'Smtphosting Config Manager activated.'];
}

function smtphosting_deactivate()
{
    return ['status' => 'success', 'description' => 'Smtphosting Config Manager deactivated.'];
}

/* ==========================================================================
 * PATHS
 * ======================================================================== */

function smtpcfg_paths()
{
    $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
    $dir = $root . '/modules/servers/Smtphosting';
    return [
        'dir'    => $dir,
        'config' => $dir . '/config.php',
        'sample' => $dir . '/config-sample.php',
        'backup' => $dir . '/config.php.bak',
    ];
}

/* ==========================================================================
 * ARRAY <-> PHP FILE HELPERS
 * ======================================================================== */

function smtpcfg_load(string $path)
{
    if (!is_readable($path)) {
        return null;
    }
    $config = include $path;
    return is_array($config) ? $config : null;
}

function smtpcfg_invalidate(string $path)
{
    clearstatcache(true, $path);
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($path, true);
    }
}

function smtpcfg_export_value($value, int $indent)
{
    $pad = str_repeat('    ', $indent);
    $padClose = str_repeat('    ', $indent - 1);

    if (is_array($value)) {
        $isList = array_keys($value) === range(0, count($value) - 1);
        $out = "[\n";
        foreach ($value as $k => $v) {
            $out .= $pad;
            if (!$isList) {
                $out .= "'" . addslashes((string) $k) . "' => ";
            }
            $out .= smtpcfg_export_value($v, $indent + 1) . ",\n";
        }
        $out .= $padClose . "]";
        return $out;
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    if ($value === null) {
        return 'null';
    }
    return "'" . addslashes((string) $value) . "'";
}

function smtpcfg_write(array $config, string $path)
{
    $php = "<?php\n/**\n * SMTP Service Dashboard - Configuration\n * Managed via Smtphosting Config Manager addon.\n * Last updated: " . date('Y-m-d H:i:s') . "\n */\n\n";
    $php .= "if (!defined('WHMCS') && php_sapi_name() !== 'cli') {\n    http_response_code(403);\n    exit('No direct access');\n}\n\n";
    $php .= "return " . smtpcfg_export_value($config, 1) . ";\n";

    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $php) === false) {
        return ['ok' => false, 'error' => 'Could not write temp file. Check folder permissions.'];
    }

    if (function_exists('shell_exec')) {
        $check = @shell_exec('php -l ' . escapeshellarg($tmp) . ' 2>&1');
        if ($check !== null && strpos($check, 'No syntax errors') === false) {
            @unlink($tmp);
            return ['ok' => false, 'error' => 'Generated file failed a syntax check: ' . htmlspecialchars((string) $check)];
        }
    } else {
        $test = include $tmp;
        if (!is_array($test)) {
            @unlink($tmp);
            return ['ok' => false, 'error' => 'Generated file did not return a valid array.'];
        }
    }

    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return ['ok' => false, 'error' => 'Could not save to ' . $path . '. Check folder permissions.'];
    }
    smtpcfg_invalidate($path);
    return ['ok' => true];
}

function smtpcfg_backup_then_write(array $config)
{
    $paths = smtpcfg_paths();
    if (is_readable($paths['config'])) {
        @copy($paths['config'], $paths['backup']); // single rolling backup
    }
    return smtpcfg_write($config, $paths['config']);
}

/* ==========================================================================
 * ICON PICKER (shared UI)
 * ======================================================================== */

function smtpcfg_icon_list()
{
    return [
        'fa-envelope','fa-envelope-open','fa-paper-plane','fa-rocket','fa-server','fa-globe','fa-globe-americas',
        'fa-plug','fa-key','fa-lock','fa-lock-open','fa-unlock','fa-chart-line','fa-chart-pie','fa-chart-bar',
        'fa-redo','fa-sync','fa-sync-alt','fa-times-circle','fa-check-circle','fa-exclamation-triangle',
        'fa-exclamation-circle','fa-info-circle','fa-question-circle','fa-shield-alt','fa-cog','fa-cogs',
        'fa-wrench','fa-database','fa-cloud','fa-cloud-upload-alt','fa-inbox','fa-id-card','fa-user','fa-users',
        'fa-user-shield','fa-star','fa-trophy','fa-flag','fa-bolt','fa-fire','fa-eye','fa-eye-slash','fa-qrcode',
        'fa-link','fa-network-wired','fa-wifi','fa-clock','fa-calendar','fa-calendar-check','fa-wallet',
        'fa-credit-card','fa-money-bill','fa-tag','fa-tags','fa-box','fa-cube','fa-list','fa-clipboard-list',
        'fa-file-alt','fa-comment','fa-comments','fa-bell','fa-bullhorn','fa-thumbs-up','fa-heart','fa-gem',
        'fa-crown','fa-award','fa-medal',
    ];
}

// $current is expected to be a full class string, e.g. "fa fa-lock" or "fas fa-lock".
function smtpcfg_icon_field(string $name, string $current, string $label = '')
{
    $id = 'icon_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $safeVal = htmlspecialchars($current, ENT_QUOTES);
    $grid = '';
    foreach (smtpcfg_icon_list() as $ic) {
        $grid .= '<button type="button" class="smtpcfg-icon-btn" title="' . $ic . '" onclick="smtpcfgPickIcon(\'' . $id . '\',\'' . $ic . '\')"><i class="fas ' . $ic . '"></i></button>';
    }
    $labelHtml = $label !== '' ? '<label>' . htmlspecialchars($label) . '</label>' : '';
    return '
    <div class="smtpcfg-icon-field">
        ' . $labelHtml . '
        <div>
            <span class="smtpcfg-icon-preview"><i class="' . $safeVal . '" id="' . $id . '_preview"></i></span>
            <input type="text" name="' . htmlspecialchars($name) . '" id="' . $id . '" value="' . $safeVal . '" class="form-control" style="max-width:220px;display:inline-block;" oninput="document.getElementById(\'' . $id . '_preview\').className=this.value;">
            <button type="button" class="btn btn-default btn-sm" onclick="document.getElementById(\'' . $id . '_grid\').classList.toggle(\'open\')">Browse Icons</button>
        </div>
        <div class="smtpcfg-icon-grid" id="' . $id . '_grid">' . $grid . '</div>
    </div>';
}

/* ==========================================================================
 * SMALL FORM HELPERS
 * ======================================================================== */

function smtpcfg_text(string $name, string $value, string $label, string $extra = '', string $help = '')
{
    $helpHtml = $help !== '' ? '<p class="help-block">' . htmlspecialchars($help) . '</p>' : '';
    return '<div class="form-group"><label>' . htmlspecialchars($label) . '</label>
        <input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="form-control" ' . $extra . '>' . $helpHtml . '</div>';
}

function smtpcfg_textarea(string $name, string $value, string $label, int $rows = 3, string $help = '')
{
    $helpHtml = $help !== '' ? '<p class="help-block">' . htmlspecialchars($help) . '</p>' : '';
    return '<div class="form-group"><label>' . htmlspecialchars($label) . '</label>
        <textarea name="' . htmlspecialchars($name) . '" rows="' . $rows . '" class="form-control">' . htmlspecialchars($value) . '</textarea>' . $helpHtml . '</div>';
}

function smtpcfg_checkbox(string $name, bool $checked, string $label)
{
    $c = $checked ? 'checked' : '';
    return '<div class="checkbox"><label><input type="checkbox" name="' . htmlspecialchars($name) . '" value="1" ' . $c . '> ' . htmlspecialchars($label) . '</label></div>';
}

function smtpcfg_color(string $name, string $value, string $label)
{
    $value = $value !== '' ? $value : '#000000';
    return '<div class="smtpcfg-color-group"><label>' . htmlspecialchars($label) . '</label><br>
        <input type="color" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '">
        <input type="text" value="' . htmlspecialchars($value) . '" class="form-control smtpcfg-color-hex" readonly></div>';
}

function smtpcfg_bool(array $arr, $key, $default = false)
{
    return isset($arr[$key]) ? (bool) $arr[$key] : $default;
}

function smtpcfg_info(string $text)
{
    return '<div class="smtpcfg-info"><i class="fas fa-info-circle"></i> ' . htmlspecialchars($text) . '</div>';
}

/* ==========================================================================
 * MAIN OUTPUT
 * ======================================================================== */

function smtphosting_output($vars)
{
    $paths = smtpcfg_paths();
    $tab = isset($_GET['tab']) ? preg_replace('/[^a-z_]/', '', $_GET['tab']) : 'general';
    $notice = '';
    $noticeType = 'info';
    $config = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['smtpcfg_action'] ?? '';

        if ($action === 'revert') {
            if (is_readable($paths['backup'])) {
                $backupConfig = smtpcfg_load($paths['backup']);
                if ($backupConfig !== null && @copy($paths['backup'], $paths['config'])) {
                    smtpcfg_invalidate($paths['config']);
                    $config = $backupConfig;
                    $notice = 'Reverted to your previous saved settings.';
                    $noticeType = 'success';
                } else {
                    $notice = 'Could not revert: the backup file could not be read or copied.';
                    $noticeType = 'danger';
                }
            } else {
                $notice = 'There is no previous version to revert to yet.';
                $noticeType = 'warning';
            }
        } elseif ($action === 'reset_default') {
            $sample = smtpcfg_load($paths['sample']);
            if ($sample === null) {
                $notice = 'The default configuration file could not be found.';
                $noticeType = 'danger';
            } else {
                $result = smtpcfg_backup_then_write($sample);
                if ($result['ok']) {
                    $config = $sample;
                    $notice = 'All settings have been reset to default. Your previous settings were backed up.';
                    $noticeType = 'success';
                } else {
                    $notice = $result['error'];
                    $noticeType = 'danger';
                }
            }
        } else {
            $diskConfig = smtpcfg_load($paths['config']);
            if ($diskConfig === null) {
                $notice = 'Could not read the current configuration file.';
                $noticeType = 'danger';
            } else {
                $updated = smtpcfg_apply_post($diskConfig, $tab, $_POST);
                if (is_string($updated)) {
                    $notice = $updated;
                    $noticeType = 'danger';
                    $config = $diskConfig;
                } else {
                    $result = smtpcfg_backup_then_write($updated);
                    if ($result['ok']) {
                        $config = $updated;
                        $notice = 'Your changes have been saved.';
                        $noticeType = 'success';
                    } else {
                        $notice = $result['error'];
                        $noticeType = 'danger';
                        $config = $diskConfig;
                    }
                }
            }
        }
    }

    if ($config === null) {
        $config = smtpcfg_load($paths['config']);
    }
    if ($config === null) {
        echo '<div class="alert alert-danger">Could not read the dashboard configuration file. Please contact support.</div>';
        return;
    }

    echo smtpcfg_styles();

    echo '<div class="smtpcfg-header">
        <h3><i class="fas fa-envelope"></i> SMTP Dashboard Settings</h3>
        <p>Control how the SMTP Service dashboard looks and behaves for your customers. Changes apply immediately after saving.</p>
    </div>';

    if ($notice !== '') {
        echo '<div class="alert alert-' . $noticeType . '">' . htmlspecialchars($notice) . '</div>';
    }

    $tabs = [
        'general'     => ['General', 'fa-sliders-h'],
        'features'    => ['Sections & Features', 'fa-th-large'],
        'branding'    => ['Colors', 'fa-palette'],
        'text'        => ['Text & Wording', 'fa-font'],
        'dnsscore'    => ['DNS Health Score', 'fa-shield-alt'],
        'icons'       => ['Icons', 'fa-icons'],
        'hideclasses' => ['Page Layout', 'fa-columns'],
    ];

    echo '<ul class="nav nav-tabs smtpcfg-tabs">';
    foreach ($tabs as $key => $t) {
        $active = $key === $tab ? 'active' : '';
        echo '<li class="' . $active . '"><a href="?module=Smtphosting&tab=' . $key . '"><i class="fas ' . $t[1] . '"></i> ' . htmlspecialchars($t[0]) . '</a></li>';
    }
    echo '</ul><div class="smtpcfg-panel">';

    switch ($tab) {
        case 'features':    smtpcfg_render_features($config); break;
        case 'branding':    smtpcfg_render_branding($config); break;
        case 'text':        smtpcfg_render_text($config); break;
        case 'dnsscore':    smtpcfg_render_dnsscore($config); break;
        case 'icons':       smtpcfg_render_icons($config); break;
        case 'hideclasses': smtpcfg_render_hideclasses($config); break;
        default:            smtpcfg_render_general($config); break;
    }

    echo '</div>';

    echo '<div class="smtpcfg-danger-zone">
        <h4>Need to undo something?</h4>
        <p>These actions affect <strong>all</strong> settings, not just the current tab.</p>
        <form method="post" style="display:inline-block;margin-right:10px;" onsubmit="return confirm(\'Undo your last save and restore the previous settings?\');">
            <input type="hidden" name="smtpcfg_action" value="revert">
            <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> Undo Last Save</button>
        </form>
        <form method="post" style="display:inline-block;" onsubmit="return confirm(\'This will erase ALL your customizations and restore the original default settings. Continue?\');">
            <input type="hidden" name="smtpcfg_action" value="reset_default">
            <button type="submit" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Reset Everything to Default</button>
        </form>
    </div>';
}

/* ==========================================================================
 * POST HANDLERS PER TAB
 * Returns updated $config array, or a string error message.
 * ======================================================================== */

function smtpcfg_apply_post(array $config, string $tab, array $post)
{
    switch ($tab) {
        case 'general':
            $groups = array_filter(array_map('trim', explode("\n", $post['group_names'] ?? '')));
            $config['product']['group_names'] = array_values($groups);
            $config['assets']['css_path'] = trim($post['css_path'] ?? $config['assets']['css_path']);
            break;

        case 'features':
            foreach (['stretch_full_width','show_service_info_card','show_quick_actions_card','show_usage_stats',
                      'show_usage_pie_chart','show_usage_progress_bars','show_week_usage','show_plan_info',
                      'show_suspended_banner','show_dns_section','show_integration_cards','show_mail_logs','show_refresh_link'] as $f) {
                $config['features'][$f] = isset($post['feat_' . $f]);
            }
            foreach (['change_password','upgrade_plan','renew_service','cancel_service'] as $f) {
                $config['features']['quick_actions'][$f] = isset($post['qa_' . $f]);
            }
            foreach (['ssl','tls','none'] as $f) {
                $config['features']['integration_protocols'][$f] = isset($post['proto_' . $f]);
            }
            foreach (['spf','dmarc','dkim'] as $f) {
                $config['features']['dns_rows'][$f] = isset($post['dns_' . $f]);
            }
            $config['features']['logs']['per_page'] = (int) ($post['logs_per_page'] ?? 25);
            $opts = array_filter(array_map('intval', array_map('trim', explode(',', $post['logs_per_page_options'] ?? ''))));
            $config['features']['logs']['per_page_options'] = array_values($opts) ?: [10, 25, 50, 100];
            $config['features']['logs']['default_status'] = $post['logs_default_status'] ?? 'all';
            foreach (['time','from','to','host','status','details'] as $f) {
                $config['features']['logs']['columns'][$f] = isset($post['col_' . $f]);
            }
            break;

        case 'branding':
            $config['branding']['active_palette'] = $post['active_palette'] ?? $config['branding']['active_palette'];
            foreach ($config['branding']['palettes'] as $key => $palette) {
                if (!isset($post['pal_' . $key . '_label'])) {
                    continue;
                }
                $config['branding']['palettes'][$key]['label'] = trim($post['pal_' . $key . '_label']);
                foreach (['primary','primary-dark','primary-light','secondary','success','info','warning','danger','light','dark'] as $colorKey) {
                    $field = 'pal_' . $key . '_' . str_replace('-', '_', $colorKey);
                    if (isset($post[$field]) && preg_match('/^#[0-9a-fA-F]{6}$/', $post[$field])) {
                        $config['branding']['palettes'][$key][$colorKey] = $post[$field];
                    }
                }
            }
            break;

        case 'text':
            foreach ($config['text']['panel_titles'] as $k => $v) {
                $config['text']['panel_titles'][$k] = trim($post['pt_' . $k] ?? $v);
            }
            foreach ($config['text']['labels'] as $k => $v) {
                $config['text']['labels'][$k] = trim($post['lb_' . $k] ?? $v);
            }
            foreach ($config['text']['dns'] as $k => $v) {
                $config['text']['dns'][$k] = trim($post['dnsmsg_' . $k] ?? $v);
            }
            foreach (['transactional', 'dedicated'] as $type) {
                if (!isset($config['text']['plan_info'][$type])) continue;
                $config['text']['plan_info'][$type]['icon'] = trim($post['plan_' . $type . '_icon'] ?? '');
                $config['text']['plan_info'][$type]['badge'] = trim($post['plan_' . $type . '_badge'] ?? '');
                $config['text']['plan_info'][$type]['title'] = trim($post['plan_' . $type . '_title'] ?? '');
                $config['text']['plan_info'][$type]['description'] = trim($post['plan_' . $type . '_desc'] ?? '');
            }
            break;

        case 'dnsscore':
            $spf = (int) ($post['w_spf'] ?? 0);
            $dmarc = (int) ($post['w_dmarc'] ?? 0);
            $dkim = (int) ($post['w_dkim'] ?? 0);
            if ($spf + $dmarc + $dkim !== 100) {
                return 'The three weight values must add up to exactly 100. Yours currently add up to ' . ($spf + $dmarc + $dkim) . '.';
            }
            $config['dns_score']['weights'] = ['spf' => $spf, 'dmarc' => $dmarc, 'dkim' => $dkim];
            foreach ($config['dns_score']['icons'] as $k => $v) {
                $config['dns_score']['icons'][$k] = trim($post['dsicon_' . $k] ?? $v);
            }
            foreach ($config['dns_score']['tips'] as $k => $v) {
                $config['dns_score']['tips'][$k] = trim($post['dstip_' . $k] ?? $v);
            }
            break;

        case 'icons':
            foreach ($config['icons'] as $k => $v) {
                $config['icons'][$k] = trim($post['ic_' . $k] ?? $v);
            }
            break;

        case 'hideclasses':
            $selectors = $post['hc_selector'] ?? [];
            $rules = $post['hc_rule'] ?? [];
            $out = [];
            foreach ($selectors as $i => $sel) {
                $sel = trim($sel);
                $rule = trim($rules[$i] ?? '');
                if ($sel !== '' && $rule !== '') {
                    $out[$sel] = $rule;
                }
            }
            $config['hide_classes']['default'] = $out;
            break;
    }
    return $config;
}

/* ==========================================================================
 * RENDER: GENERAL
 * ======================================================================== */

function smtpcfg_render_general(array $config)
{
    echo smtpcfg_info('These settings control which of your hosting products show the SMTP dashboard, and where its stylesheet loads from. Most resellers only need to check the product names.');
    echo '<form method="post">';
    echo smtpcfg_textarea('group_names', implode("\n", $config['product']['group_names'] ?? []), 'Product Group Name(s)', 4,
        'Enter the exact Product Group name(s) from WHMCS that should show this dashboard, one per line.');
    echo smtpcfg_text('css_path', $config['assets']['css_path'] ?? '', 'Stylesheet Path',
        '', 'Only change this if instructed by support - it points to the dashboard\'s styling file.');
    echo '<button type="submit" class="btn btn-primary">Save General Settings</button></form>';
}

/* ==========================================================================
 * RENDER: FEATURES
 * ======================================================================== */

function smtpcfg_render_features(array $config)
{
    $f = $config['features'];
    echo smtpcfg_info('Turn dashboard sections on or off, and control what your customers can do from it.');
    echo '<form method="post">';

    echo '<div class="smtpcfg-section"><h4>Dashboard Sections</h4>';
    $sections = [
        'stretch_full_width' => 'Stretch dashboard to full page width',
        'show_service_info_card' => 'Show "Service Information" card',
        'show_quick_actions_card' => 'Show "Quick Actions" card',
        'show_usage_stats' => 'Show usage statistics',
        'show_usage_pie_chart' => 'Show usage pie chart',
        'show_usage_progress_bars' => 'Show usage progress bars',
        'show_week_usage' => 'Show weekly usage row',
        'show_plan_info' => 'Show the plan-type info box',
        'show_suspended_banner' => 'Show a banner when sending is suspended',
        'show_dns_section' => 'Show DNS configuration checks',
        'show_integration_cards' => 'Show SMTP connection details',
        'show_mail_logs' => 'Show sent mail logs',
        'show_refresh_link' => 'Allow customers to manually refresh data',
    ];
    foreach ($sections as $key => $label) {
        echo smtpcfg_checkbox('feat_' . $key, smtpcfg_bool($f, $key), $label);
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Quick Actions Available to Customers</h4>';
    foreach (['change_password' => 'Change Password', 'upgrade_plan' => 'Upgrade Plan', 'renew_service' => 'Renew Service', 'cancel_service' => 'Cancel Service'] as $key => $label) {
        echo smtpcfg_checkbox('qa_' . $key, smtpcfg_bool($f['quick_actions'] ?? [], $key), $label);
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Connection Methods Shown</h4>';
    foreach (['ssl' => 'SSL (port 465)', 'tls' => 'TLS (port 587)', 'none' => 'Unencrypted (port 25)'] as $key => $label) {
        echo smtpcfg_checkbox('proto_' . $key, smtpcfg_bool($f['integration_protocols'] ?? [], $key), $label);
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>DNS Checks Shown</h4>';
    foreach (['spf' => 'SPF', 'dmarc' => 'DMARC', 'dkim' => 'DKIM'] as $key => $label) {
        echo smtpcfg_checkbox('dns_' . $key, smtpcfg_bool($f['dns_rows'] ?? [], $key), $label);
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Mail Log Settings</h4>';
    echo smtpcfg_text('logs_per_page', (string) ($f['logs']['per_page'] ?? 25), 'Default entries per page');
    echo smtpcfg_text('logs_per_page_options', implode(', ', $f['logs']['per_page_options'] ?? []), 'Entries-per-page choices (comma separated)');
    echo '<div class="form-group"><label>Default status filter shown first</label><select name="logs_default_status" class="form-control" style="max-width:220px;">';
    foreach (['all', 'success', 'failure', 'defer', 'inprogress'] as $opt) {
        $sel = ($f['logs']['default_status'] ?? 'all') === $opt ? 'selected' : '';
        echo '<option value="' . $opt . '" ' . $sel . '>' . ucfirst($opt) . '</option>';
    }
    echo '</select></div>';
    echo '<label>Columns to display</label>';
    foreach (['time' => 'Time', 'from' => 'From', 'to' => 'To', 'host' => 'Host', 'status' => 'Status', 'details' => 'Details'] as $key => $label) {
        echo smtpcfg_checkbox('col_' . $key, smtpcfg_bool($f['logs']['columns'] ?? [], $key), $label);
    }
    echo '</div>';

    echo '<button type="submit" class="btn btn-primary">Save Features</button></form>';
}

/* ==========================================================================
 * RENDER: BRANDING
 * ======================================================================== */

function smtpcfg_render_branding(array $config)
{
    $palettes = $config['branding']['palettes'] ?? [];
    echo smtpcfg_info('Pick a color theme for the dashboard, and fine-tune any of its colors below. Changes apply to all customers immediately.');
    echo '<form method="post">';

    echo '<div class="smtpcfg-section"><h4>Active Theme</h4>';
    echo '<div class="form-group"><select name="active_palette" class="form-control" style="max-width:280px;">';
    foreach ($palettes as $key => $p) {
        $sel = $key === ($config['branding']['active_palette'] ?? '') ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($key) . '" ' . $sel . '>' . htmlspecialchars($p['label'] ?? $key) . '</option>';
    }
    echo '</select></div></div>';

    echo '<div class="smtpcfg-section"><h4>Theme Colors</h4><p class="help-block">Click any swatch below to change that color. Each theme can be customized independently.</p>';
    foreach ($palettes as $key => $p) {
        $isActive = $key === ($config['branding']['active_palette'] ?? '') ? ' smtpcfg-palette-active' : '';
        echo '<div class="smtpcfg-palette-block' . $isActive . '"><h5>' . htmlspecialchars($p['label'] ?? $key)
            . ($isActive ? ' <span class="smtpcfg-badge">Currently Active</span>' : '') . '</h5>';
        echo smtpcfg_text('pal_' . $key . '_label', $p['label'] ?? '', 'Theme Name');
        echo '<div class="smtpcfg-color-row">';
        foreach (['primary','primary-dark','primary-light','secondary','success','info','warning','danger','light','dark'] as $colorKey) {
            $field = 'pal_' . $key . '_' . str_replace('-', '_', $colorKey);
            echo smtpcfg_color($field, $p[$colorKey] ?? '#000000', ucwords(str_replace('-', ' ', $colorKey)));
        }
        echo '</div></div>';
    }
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Save Colors</button></form>';
}

/* ==========================================================================
 * RENDER: TEXT & LABELS
 * ======================================================================== */

function smtpcfg_render_text(array $config)
{
    echo smtpcfg_info('Change any wording shown to customers - headings, button labels, and the messages shown for DNS checks.');
    echo '<form method="post">';

    echo '<div class="smtpcfg-section"><h4>Section Titles</h4>';
    foreach ($config['text']['panel_titles'] ?? [] as $k => $v) {
        echo smtpcfg_text('pt_' . $k, $v, ucwords(str_replace('_', ' ', $k)));
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Labels & Buttons</h4>';
    foreach ($config['text']['labels'] ?? [] as $k => $v) {
        echo smtpcfg_text('lb_' . $k, $v, ucwords(str_replace('_', ' ', $k)));
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>DNS Check Messages</h4><p class="help-block">Shown to customers depending on whether their SPF, DMARC, and DKIM records are set up correctly.</p>';
    foreach ($config['text']['dns'] ?? [] as $k => $v) {
        echo smtpcfg_textarea('dnsmsg_' . $k, $v, ucwords(str_replace('_', ' ', $k)), 2);
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Plan Descriptions</h4><p class="help-block">Shown to customers to explain what their plan is allowed to be used for.</p>';
    foreach (['transactional' => 'Transactional Plan', 'dedicated' => 'Dedicated Plan'] as $type => $label) {
        $p = $config['text']['plan_info'][$type] ?? ['icon' => '', 'badge' => '', 'title' => '', 'description' => ''];
        echo '<div class="smtpcfg-palette-block"><h5>' . $label . '</h5>';
        echo smtpcfg_icon_field('plan_' . $type . '_icon', $p['icon'] ?? '', 'Icon');
        echo smtpcfg_text('plan_' . $type . '_badge', $p['badge'] ?? '', 'Small Badge Text');
        echo smtpcfg_text('plan_' . $type . '_title', $p['title'] ?? '', 'Title');
        echo smtpcfg_textarea('plan_' . $type . '_desc', $p['description'] ?? '', 'Description', 3);
        echo '</div>';
    }
    echo '</div>';

    echo '<button type="submit" class="btn btn-primary">Save Text & Wording</button></form>';
}

/* ==========================================================================
 * RENDER: DNS SCORE
 * ======================================================================== */

function smtpcfg_render_dnsscore(array $config)
{
    $ds = $config['dns_score'] ?? [];
    echo smtpcfg_info('Customers see a "DNS Health Score" out of 100 based on their SPF, DMARC, and DKIM setup. Adjust how much each one counts, and what advice is shown.');
    echo '<form method="post">';

    echo '<div class="smtpcfg-section"><h4>Scoring Weights</h4><p class="help-block">These three numbers must always add up to 100.</p>';
    echo smtpcfg_text('w_spf', (string) ($ds['weights']['spf'] ?? 0), 'SPF Weight');
    echo smtpcfg_text('w_dmarc', (string) ($ds['weights']['dmarc'] ?? 0), 'DMARC Weight');
    echo smtpcfg_text('w_dkim', (string) ($ds['weights']['dkim'] ?? 0), 'DKIM Weight');
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Status Icons</h4>';
    $iconLabels = ['none' => 'No records set up', 'spf_only' => 'Only SPF set up', 'spf_dmarc' => 'SPF + DMARC set up', 'all_valid' => 'Everything set up'];
    foreach ($ds['icons'] ?? [] as $k => $v) {
        echo smtpcfg_icon_field('dsicon_' . $k, $v, $iconLabels[$k] ?? ucwords(str_replace('_', ' ', $k)));
    }
    echo '</div>';

    echo '<div class="smtpcfg-section"><h4>Advice Messages</h4>';
    foreach ($ds['tips'] ?? [] as $k => $v) {
        echo smtpcfg_textarea('dstip_' . $k, $v, $iconLabels[$k] ?? ucwords(str_replace('_', ' ', $k)), 2);
    }
    echo '</div>';

    echo '<button type="submit" class="btn btn-primary">Save DNS Score Settings</button></form>';
}

/* ==========================================================================
 * RENDER: ICONS
 * ======================================================================== */

function smtpcfg_render_icons(array $config)
{
    echo smtpcfg_info('Change the small icons shown next to each section and button throughout the dashboard.');
    echo '<form method="post">';
    echo '<div class="smtpcfg-section">';
    foreach ($config['icons'] ?? [] as $k => $v) {
        echo smtpcfg_icon_field('ic_' . $k, $v, ucwords(str_replace('_', ' ', $k)));
    }
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Save Icons</button></form>';
}

/* ==========================================================================
 * RENDER: PAGE LAYOUT (hide_classes)
 * ======================================================================== */

function smtpcfg_render_hideclasses(array $config)
{
    $rules = $config['hide_classes']['default'] ?? [];

    echo smtpcfg_info('This is an advanced setting used to hide parts of your client area template so the dashboard has more room. The default values work for most templates - only change this if you know CSS or support has asked you to.');

    echo '<form method="post" id="smtpcfg-hc-form">';
    echo '<div class="smtpcfg-section">';
    echo '<table class="table smtpcfg-hc-table" id="smtpcfg-hc-table">
        <thead><tr><th style="width:50%">Element to Hide (CSS selector)</th><th style="width:45%">Rule</th><th></th></tr></thead><tbody>';

    if (empty($rules)) {
        $rules = ['' => ''];
    }
    foreach ($rules as $selector => $rule) {
        echo '<tr>
            <td><input type="text" name="hc_selector[]" value="' . htmlspecialchars($selector) . '" class="form-control"></td>
            <td><input type="text" name="hc_rule[]" value="' . htmlspecialchars($rule) . '" class="form-control"></td>
            <td><button type="button" class="btn btn-default btn-sm" onclick="this.closest(\'tr\').remove()"><i class="fas fa-times"></i></button></td>
        </tr>';
    }
    echo '</tbody></table>';
    echo '<button type="button" class="btn btn-default btn-sm" onclick="smtpcfgAddHcRow()"><i class="fas fa-plus"></i> Add Row</button>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Save Page Layout</button></form>';

    echo '<script>
        function smtpcfgAddHcRow(){
            var tbody = document.querySelector("#smtpcfg-hc-table tbody");
            var tr = document.createElement("tr");
            tr.innerHTML = \'<td><input type="text" name="hc_selector[]" class="form-control"></td>\'
                + \'<td><input type="text" name="hc_rule[]" class="form-control"></td>\'
                + \'<td><button type="button" class="btn btn-default btn-sm" onclick="this.closest(\\\'tr\\\').remove()"><i class="fas fa-times"></i></button></td>\';
            tbody.appendChild(tr);
        }
    </script>';
}

/* ==========================================================================
 * STYLES
 * ======================================================================== */

function smtpcfg_styles()
{
    return '<style>
        .smtpcfg-header{margin-bottom:15px;}
        .smtpcfg-header h3{margin:0 0 4px;color:#1c2b33;}
        .smtpcfg-header p{color:#6c757d;margin:0;}
        .smtpcfg-info{background:#e6f7ff;border:1px solid #b8e2f7;color:#0c5a7a;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:13px;}
        .smtpcfg-tabs{margin-bottom:0;border-bottom:2px solid #0077b6;}
        .smtpcfg-tabs > li > a{color:#495057;}
        .smtpcfg-tabs > li.active > a{color:#0077b6;font-weight:600;}
        .smtpcfg-panel{background:#fff;border:1px solid #e1e5e8;border-top:none;padding:20px;border-radius:0 0 6px 6px;}
        .smtpcfg-section{border:1px solid #eaeaea;border-radius:6px;padding:16px 18px;margin-bottom:18px;background:#fcfcfd;}
        .smtpcfg-section h4{margin-top:0;color:#1c2b33;border-bottom:1px solid #eee;padding-bottom:8px;margin-bottom:14px;}
        .smtpcfg-palette-block{border:1px solid #ddd;border-radius:6px;padding:15px;margin-bottom:15px;background:#fff;}
        .smtpcfg-palette-block.smtpcfg-palette-active{border-color:#0077b6;box-shadow:0 0 0 1px #0077b6;}
        .smtpcfg-badge{background:#0077b6;color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;vertical-align:middle;}
        .smtpcfg-color-row{display:flex;flex-wrap:wrap;gap:14px;margin-top:10px;}
        .smtpcfg-color-group{margin-bottom:0;}
        .smtpcfg-color-group input[type=color]{width:46px;height:32px;padding:2px;vertical-align:middle;border-radius:4px;}
        .smtpcfg-color-hex{width:85px;display:inline-block;margin-left:4px;}
        .smtpcfg-icon-field{margin-bottom:12px;}
        .smtpcfg-icon-preview{display:inline-block;width:34px;text-align:center;font-size:16px;color:#0077b6;}
        .smtpcfg-icon-grid{display:none;flex-wrap:wrap;gap:4px;max-width:500px;border:1px solid #ddd;padding:8px;margin-top:6px;background:#fff;border-radius:6px;max-height:220px;overflow-y:auto;}
        .smtpcfg-icon-grid.open{display:flex;}
        .smtpcfg-icon-btn{width:34px;height:34px;border:1px solid #eee;background:#fff;border-radius:4px;cursor:pointer;}
        .smtpcfg-icon-btn:hover{background:#e6f7ff;border-color:#0077b6;}
        .smtpcfg-hc-table td{vertical-align:middle;}
        .smtpcfg-danger-zone{margin-top:24px;padding:16px 18px;border:1px dashed #e0a02e;border-radius:6px;background:#fff9ec;}
        .smtpcfg-danger-zone h4{margin-top:0;}
        .help-block{color:#868e96;font-size:12px;margin-top:4px;}
    </style>
    <script>
        function smtpcfgPickIcon(fieldId, iconClass){
            var input = document.getElementById(fieldId);
            var preview = document.getElementById(fieldId + "_preview");
            var full = "fa " + iconClass;
            input.value = full;
            preview.className = full;
            document.getElementById(fieldId + "_grid").classList.remove("open");
        }
        document.addEventListener("input", function(e){
            if (e.target.type === "color") {
                var hex = e.target.nextElementSibling;
                if (hex) hex.value = e.target.value;
            }
        });
    </script>';
}
