<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;

$config = require dirname(__DIR__) . '/config/config.php';

// Check if this is an AJAX request to fetch the embed URL
if (isset($_GET['action']) && $_GET['action'] === 'get_embed_url') {
    header('Content-Type: application/json');
    $phone = (string)($_GET['phone'] ?? '');
    $memberId = (string)($_GET['member_id'] ?? '');
    $userId = (string)($_GET['user_id'] ?? '1');

    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];
    $customCrmId = $b24 ? $b24->getCustomCrmIdentifier() : ($config['doubletick']['custom_crm_identifier'] ?? null);

    if (!$apiKey) {
        echo json_encode(['success' => false, 'error' => 'DoubleTick API Key not configured.']);
        exit;
    }

    if (!$phone) {
        echo json_encode(['success' => false, 'error' => 'No phone number associated with this CRM record.']);
        exit;
    }

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $embedUrl = $dt->getEmbedUrl($phone, $waba);
        $ssoTokenAcquired = false;

        // If Custom CRM integration is configured in DoubleTick, request single-use SSO token
        if ($customCrmId) {
            $sessionToken = $userId . ':' . ($memberId ?: 'default');
            $embedToken = $dt->getCustomCrmEmbedToken($customCrmId, $sessionToken);
            if ($embedToken) {
                $separator = str_contains($embedUrl, '?') ? '&' : '?';
                $embedUrl .= $separator . 'embedToken=' . urlencode($embedToken);
                $ssoTokenAcquired = true;
            }
        }

        echo json_encode([
            'success' => true,
            'url' => $embedUrl,
            'sso_enabled' => $ssoTokenAcquired,
            'has_custom_crm_id' => !empty($customCrmId)
        ]);
    } catch (\Throwable $e) {
        Logger::error("Failed to generate embed URL: " . $e->getMessage(), ['phone' => $phone]);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Check if this is an AJAX request to fetch chat AI summary
if (isset($_GET['action']) && $_GET['action'] === 'get_ai_summary') {
    header('Content-Type: application/json');
    $phone = (string)($_GET['phone'] ?? '');
    $memberId = (string)($_GET['member_id'] ?? '');

    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $summary = $dt->getChatAiSummary($phone);
        echo json_encode(['success' => true, 'data' => $summary]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Fetch approved templates for CRM modal
if (isset($_GET['action']) && $_GET['action'] === 'get_templates') {
    header('Content-Type: application/json');
    $memberId = (string)($_GET['member_id'] ?? '');
    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $tplRes = $dt->getTemplates('APPROVED');
        $templates = isset($tplRes['templates']) && is_array($tplRes['templates']) ? $tplRes['templates'] : (is_array($tplRes) ? $tplRes : []);
        echo json_encode(['success' => true, 'templates' => $templates]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Send template directly from CRM tab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_template') {
    header('Content-Type: application/json');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $templateName = trim((string)($_POST['template_name'] ?? ''));
    $language = trim((string)($_POST['language'] ?? 'en'));
    $memberId = (string)($_POST['member_id'] ?? '');

    $params = [];
    if (!empty($_POST['param1'])) $params[] = trim((string)$_POST['param1']);
    if (!empty($_POST['param2'])) $params[] = trim((string)$_POST['param2']);
    if (!empty($_POST['param3'])) $params[] = trim((string)$_POST['param3']);

    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey || !$phone || !$templateName) {
        echo json_encode(['success' => false, 'error' => 'API Key, phone and template name are required.']);
        exit;
    }

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $res = $dt->sendTemplateMessage($phone, $templateName, $language, $params, $waba);
        if (!empty($res['error']) || (!empty($res['status_code']) && $res['status_code'] >= 400)) {
            $errDetail = is_array($res['error']) ? json_encode($res['error']) : (string)$res['error'];
            if (!empty($res['raw']['message'])) {
                $msgDetail = is_array($res['raw']['message']) ? implode(', ', $res['raw']['message']) : (string)$res['raw']['message'];
                $errDetail .= " ({$msgDetail})";
            }
            echo json_encode(['success' => false, 'error' => $errDetail]);
        } else {
            $msgId = $res['messageId'] ?? ($res['messages'][0]['messageId'] ?? 'OK');
            echo json_encode(['success' => true, 'message_id' => $msgId]);
        }
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

header_remove('X-Frame-Options');
header('Content-Security-Policy: frame-ancestors *');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEEN DoubleTick</title>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <style>
        :root {
            --primary: #3F906D; --primary-hover: #2e6e52; --primary-glow: rgba(63, 144, 109, 0.25);
            --bg-dark: #0f172a; --card-bg: #1e293b; --card-border: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { padding: 12px; background: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; overflow: hidden; height: 100vh; }
        .tab-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            padding: 10px 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1px solid #334155;
        }
        .customer-info { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .toolbar-actions { display: flex; gap: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .code-badge { background: #0f172a; padding: 4px 8px; border-radius: 6px; font-family: monospace; color: #38bdf8; font-size: 12px; }
        .iframe-container { width: 100%; height: calc(100vh - 80px); border: none; border-radius: 12px; background: #fff; }
        #ai-modal {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
            background: #1e293b;
            border: 1px solid #38bdf8;
            border-radius: 12px;
            padding: 20px;
            z-index: 100;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>

<div class="tab-toolbar">
    <div class="customer-info">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="28" height="28">
            <rect width="48" height="48" rx="8" fill="#3F906D"/>
            <path d="M24 8C15.16 8 8 15.16 8 24C8 26.83 8.74 29.58 10.14 32L7.9 40.2L16.32 38C18.66 39.28 21.3 40 24 40C32.84 40 40 32.84 40 24C40 15.16 32.84 8 24 8ZM24 37.3C21.6 37.3 19.26 36.66 17.22 35.46L16.74 35.18L11.72 36.5L13.06 31.62L12.74 31.12C11.42 29 10.72 26.54 10.72 24C10.72 16.68 16.68 10.72 24 10.72C31.32 10.72 37.28 16.68 37.28 24C37.28 31.32 31.32 37.3 24 37.3Z" fill="#FFFFFF"/>
            <path d="M29.5 25.5C29 25.25 26.5 24.02 26.04 23.85C25.58 23.68 25.24 23.6 24.9 24.1C24.56 24.6 23.6 25.75 23.3 26.1C23.02 26.45 22.72 26.49 22.22 26.24C21.72 25.99 20.12 25.46 18.22 23.77C16.74 22.45 15.74 20.82 15.46 20.32C15.18 19.82 15.42 19.55 15.68 19.3C15.9 19.08 16.18 18.72 16.42 18.44C16.68 18.16 16.76 17.96 16.92 17.62C17.08 17.28 17 16.98 16.88 16.74C16.76 16.5 15.74 14 15.32 12.98C14.9 12 14.5 12.14 14.2 12.12H13.24C12.9 12.12 12.36 12.24 11.9 12.74C11.44 13.24 10.16 14.44 10.16 16.88C10.16 19.32 11.94 21.68 12.18 22C12.44 22.34 15.68 27.3 20.62 29.42C21.8 29.92 22.72 30.22 23.42 30.46C24.6 30.82 25.68 30.78 26.54 30.64C27.5 30.5 29.5 29.42 29.92 28.24C30.34 27.06 30.34 26.04 30.22 25.82C30.08 25.62 29.98 25.74 29.5 25.5Z" fill="#FFFFFF"/>
        </svg>
        <div>
            <strong id="contact-name">Loading contact...</strong>
            <span id="contact-phone" class="code-badge" style="margin-left: 8px;">---</span>
        </div>
    </div>
    <div class="toolbar-actions">
        <button class="btn" style="padding: 6px 12px; font-size: 12px; background: #2563eb; color: #fff;" onclick="openTemplateModal()">
            📢 Send Template
        </button>
        <button class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="loadAiSummary()">
            ✨ AI Summary
        </button>
        <button class="btn" style="padding: 6px 12px; font-size: 12px; background: #334155; color: #fff;" onclick="reloadIframe()">
            🔄 Refresh
        </button>
    </div>
</div>

<div id="template-modal" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 92%; max-width: 500px; background: #1e293b; border: 1px solid #3b82f6; border-radius: 12px; padding: 20px; z-index: 101; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h3 style="color: #60a5fa; font-size: 15px;">📢 Send WhatsApp Template</h3>
        <button style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px;" onclick="closeTemplateModal()">✕</button>
    </div>
    <p style="font-size: 11px; color: #94a3b8; margin-bottom: 12px;">
        Templates initiate WhatsApp conversations even when the 24-hour service window is closed.
    </p>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Choose Approved Template</label>
        <select id="crm-template-select" style="width: 100%; padding: 8px 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 13px;" onchange="onCrmTemplateChange(this)">
            <option value="">-- Choose template or type name below --</option>
        </select>
    </div>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Template Name</label>
        <input type="text" id="crm-template-name" style="width: 100%; padding: 8px 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 13px;" placeholder="e.g. welcome_message">
    </div>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Language</label>
        <input type="text" id="crm-template-lang" value="en" style="width: 100%; padding: 8px 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 13px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Placeholder 1 ({{1}} - optional)</label>
        <input type="text" id="crm-param1" style="width: 100%; padding: 8px 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 13px;" placeholder="e.g. Customer Name">
    </div>
    <div style="margin-bottom: 12px;">
        <label style="display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Placeholder 2 ({{2}} - optional)</label>
        <input type="text" id="crm-param2" style="width: 100%; padding: 8px 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 13px;" placeholder="e.g. Order # or Date">
    </div>
    <div id="crm-template-status" style="display: none; margin-bottom: 10px; font-size: 12px; padding: 8px; border-radius: 6px;"></div>
    <button class="btn btn-primary" id="btn-send-crm-tpl" style="width: 100%; padding: 9px; background: #2563eb;" onclick="submitCrmTemplate()">
        🚀 Send Template
    </button>
</div>

<div id="ai-modal">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3 style="color: #38bdf8; font-size: 15px;">🤖 DoubleTick AI Conversation Summary</h3>
        <button style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px;" onclick="closeAiModal()">✕</button>
    </div>
    <div id="ai-content" style="font-size: 13px; color: #e2e8f0; max-height: 300px; overflow-y: auto; line-height: 1.5;">
        Generating conversation summary...
    </div>
</div>

<div id="sso-tip" style="display: none; background: #1e293b; border-left: 4px solid var(--primary); padding: 8px 14px; margin-bottom: 8px; border-radius: 6px; font-size: 12px; color: #cbd5e1; justify-content: space-between; align-items: center;">
    <span>⚡ <strong>1-Click Auto-Login</strong>: To skip login prompts, configure your DoubleTick Custom CRM Identifier in Connector Settings. Or log in once at <a href="https://web.doubletick.io" target="_blank" style="color: #34d399; text-decoration: underline;">web.doubletick.io</a> with your registered agent phone number.</span>
    <button onclick="document.getElementById('sso-tip').style.display='none'" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;padding:0 4px;">✕</button>
</div>

<div id="chat-container" style="height: calc(100vh - 80px); width: 100%;">
    <div id="loading" style="text-align: center; padding: 60px; color: var(--text-muted);">
        <p>Connecting to KEEN DoubleTick WhatsApp session...</p>
    </div>
    <iframe id="chat-frame" class="iframe-container" style="display: none;"></iframe>
</div>

<script>
    let currentPhone = '';
    let currentMemberId = '';
    let currentUserId = '1';

    BX24.init(function() {
        const info = BX24.placement.info();
        const placement = info.placement;
        const entityId = info.options && info.options.ID ? info.options.ID : null;
        
        const auth = BX24.getAuth();
        currentMemberId = auth ? auth.member_id : '';
        if (auth && auth.user_id) {
            currentUserId = auth.user_id;
        }

        if (!entityId) {
            document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">No CRM entity ID found.</p>';
            return;
        }

        // Determine entity type
        let entityType = 'lead';
        if (placement.indexOf('DEAL') !== -1) entityType = 'deal';
        else if (placement.indexOf('CONTACT') !== -1) entityType = 'contact';
        else if (placement.indexOf('COMPANY') !== -1) entityType = 'company';

        // Load entity details to get phone
        BX24.callMethod('crm.' + entityType + '.get', { id: entityId }, function(res) {
            if (res.error()) {
                document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">Failed to load CRM record: ' + res.error() + '</p>';
                return;
            }

            const data = res.data();
            let phone = '';
            let name = data.TITLE || data.NAME || 'Customer';

            if (data.PHONE && data.PHONE.length > 0) {
                phone = data.PHONE[0].VALUE;
            }

            if (!phone && data.CONTACT_ID) {
                BX24.callMethod('crm.contact.get', { id: data.CONTACT_ID }, function(contactRes) {
                    if (!contactRes.error()) {
                        const contactData = contactRes.data();
                        name = contactData.NAME + ' ' + (contactData.LAST_NAME || '');
                        if (contactData.PHONE && contactData.PHONE.length > 0) {
                            initChat(contactData.PHONE[0].VALUE, name);
                        } else {
                            showNoPhone();
                        }
                    }
                });
            } else if (phone) {
                initChat(phone, name);
            } else {
                showNoPhone();
            }
        });
    });

    function showNoPhone() {
        document.getElementById('contact-name').innerText = 'No Phone Number';
        document.getElementById('loading').innerHTML = '<p style="color: var(--warning)">This CRM record does not have a phone number. Add a phone number to start a WhatsApp chat.</p>';
    }

    function initChat(phone, name) {
        currentPhone = phone;
        document.getElementById('contact-name').innerText = name;
        document.getElementById('contact-phone').innerText = phone;

        // Fetch pre-authenticated Single-Sign-On Embed URL from backend
        const queryParams = new URLSearchParams({
            action: 'get_embed_url',
            phone: phone,
            member_id: currentMemberId || '',
            user_id: currentUserId || '1'
        });

        fetch('placement_tab.php?' + queryParams.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success && data.url) {
                    const iframe = document.getElementById('chat-frame');
                    iframe.src = data.url;
                    iframe.style.display = 'block';
                    document.getElementById('loading').style.display = 'none';

                    if (!data.has_custom_crm_id) {
                        const tip = document.getElementById('sso-tip');
                        if (tip) tip.style.display = 'flex';
                    }
                } else {
                    document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">Error loading chat: ' + (data.error || 'Unknown error') + '</p>';
                }
            })
            .catch(err => {
                document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">Network error while requesting DoubleTick embed URL.</p>';
            });
    }

    function reloadIframe() {
        if (currentPhone) {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('chat-frame').style.display = 'none';
            initChat(currentPhone, document.getElementById('contact-name').innerText);
        }
    }

    function loadAiSummary() {
        if (!currentPhone) return;
        document.getElementById('ai-modal').style.display = 'block';
        document.getElementById('ai-content').innerText = 'Analyzing conversation with DoubleTick AI...';

        fetch('placement_tab.php?action=get_ai_summary&phone=' + encodeURIComponent(currentPhone) + '&member_id=' + encodeURIComponent(currentMemberId))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const summaryText = data.data.summary || JSON.stringify(data.data, null, 2);
                    document.getElementById('ai-content').innerHTML = '<div style="white-space: pre-wrap;">' + summaryText + '</div>';
                } else {
                    document.getElementById('ai-content').innerText = 'No AI summary available or feature not enabled: ' + (data.error || 'Empty');
                }
            })
            .catch(err => {
                document.getElementById('ai-content').innerText = 'Failed to fetch AI summary.';
            });
    }

    function closeAiModal() {
        document.getElementById('ai-modal').style.display = 'none';
    }

    let crmTemplatesLoaded = false;
    function openTemplateModal() {
        if (!currentPhone) {
            alert('Please wait until contact phone number is loaded.');
            return;
        }
        document.getElementById('template-modal').style.display = 'block';
        document.getElementById('crm-template-status').style.display = 'none';

        if (!crmTemplatesLoaded) {
            fetch('placement_tab.php?action=get_templates&member_id=' + encodeURIComponent(currentMemberId))
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.templates && Array.isArray(data.templates)) {
                        const sel = document.getElementById('crm-template-select');
                        sel.innerHTML = '<option value="">-- Choose from approved templates --</option>';
                        data.templates.forEach(tpl => {
                            const opt = document.createElement('option');
                            opt.value = tpl.name || '';
                            opt.setAttribute('data-lang', tpl.language || 'en');
                            opt.textContent = (tpl.name || '') + ' (' + (tpl.language || 'en') + ') - ' + (tpl.category || 'TEMPLATE');
                            sel.appendChild(opt);
                        });
                        crmTemplatesLoaded = true;
                    }
                })
                .catch(e => console.warn('Could not load templates', e));
        }
    }

    function closeTemplateModal() {
        document.getElementById('template-modal').style.display = 'none';
    }

    function onCrmTemplateChange(sel) {
        if (sel.value) {
            document.getElementById('crm-template-name').value = sel.value;
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.getAttribute('data-lang')) {
                document.getElementById('crm-template-lang').value = opt.getAttribute('data-lang');
            }
        }
    }

    function submitCrmTemplate() {
        const tplName = document.getElementById('crm-template-name').value.trim();
        const tplLang = document.getElementById('crm-template-lang').value.trim() || 'en';
        const p1 = document.getElementById('crm-param1').value.trim();
        const p2 = document.getElementById('crm-param2').value.trim();
        const statusEl = document.getElementById('crm-template-status');
        const btn = document.getElementById('btn-send-crm-tpl');

        if (!tplName) {
            alert('Please specify a template name.');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Sending...';
        statusEl.style.display = 'block';
        statusEl.style.background = 'rgba(59, 130, 246, 0.2)';
        statusEl.style.color = '#93c5fd';
        statusEl.innerText = 'Sending WhatsApp template...';

        const formData = new FormData();
        formData.append('action', 'send_template');
        formData.append('phone', currentPhone);
        formData.append('template_name', tplName);
        formData.append('language', tplLang);
        formData.append('param1', p1);
        formData.append('param2', p2);
        formData.append('member_id', currentMemberId);

        fetch('placement_tab.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = '🚀 Send Template';
            if (data.success) {
                statusEl.style.background = 'rgba(16, 185, 129, 0.2)';
                statusEl.style.color = '#10b981';
                statusEl.innerText = '✓ Template sent successfully! Message ID: ' + (data.message_id || 'OK');
                setTimeout(function() {
                    closeTemplateModal();
                    reloadIframe();
                }, 2000);
            } else {
                statusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                statusEl.style.color = '#ef4444';
                statusEl.innerText = '✕ Error: ' + (data.error || 'Failed to send template');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = '🚀 Send Template';
            statusEl.style.background = 'rgba(239, 68, 68, 0.2)';
            statusEl.style.color = '#ef4444';
            statusEl.innerText = '✕ Network error while sending template.';
        });
    }
</script>

</body>
</html>
