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

    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

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
        echo json_encode(['success' => true, 'url' => $embedUrl]);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoubleTick WhatsApp CRM Integration</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="//api.bitrix24.com/api/v1/"></script>
    <style>
        body { padding: 12px; background: #0f172a; overflow: hidden; height: 100vh; }
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
        <img src="assets/icon.svg" width="28" height="28" alt="DoubleTick">
        <div>
            <strong id="contact-name">Loading contact...</strong>
            <span id="contact-phone" class="code-badge" style="margin-left: 8px;">---</span>
        </div>
    </div>
    <div class="toolbar-actions">
        <button class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="loadAiSummary()">
            ✨ AI Summary
        </button>
        <button class="btn" style="padding: 6px 12px; font-size: 12px; background: #334155; color: #fff;" onclick="reloadIframe()">
            🔄 Refresh
        </button>
    </div>
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

<div id="chat-container" style="height: calc(100vh - 80px); width: 100%;">
    <div id="loading" style="text-align: center; padding: 60px; color: var(--text-muted);">
        <p>Connecting to DoubleTick WhatsApp session...</p>
    </div>
    <iframe id="chat-frame" class="iframe-container" style="display: none;"></iframe>
</div>

<script>
    let currentPhone = '';
    let currentMemberId = '';

    BX24.init(function() {
        const info = BX24.placement.info();
        const placement = info.placement;
        const entityId = info.options && info.options.ID ? info.options.ID : null;
        
        const auth = BX24.getAuth();
        currentMemberId = auth ? auth.member_id : '';

        if (!entityId) {
            document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">No CRM entity ID found in context.</p>';
            return;
        }

        // Determine method based on placement
        let method = 'crm.lead.get';
        if (placement.indexOf('DEAL') !== -1) {
            method = 'crm.deal.get';
        } else if (placement.indexOf('CONTACT') !== -1) {
            method = 'crm.contact.get';
        } else if (placement.indexOf('COMPANY') !== -1) {
            method = 'crm.company.get';
        }

        BX24.callMethod(method, { id: entityId }, function(res) {
            if (res.error()) {
                document.getElementById('loading').innerHTML = '<p style="color: var(--danger)">Error loading CRM record: ' + res.error() + '</p>';
                return;
            }

            const data = res.data();
            let phone = '';
            let name = data.TITLE || data.NAME || 'Customer';

            if (data.PHONE && data.PHONE.length > 0) {
                phone = data.PHONE[0].VALUE;
            }

            // If deal without direct phone, fetch linked contact phone
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
        fetch('placement_tab.php?action=get_embed_url&phone=' + encodeURIComponent(phone) + '&member_id=' + encodeURIComponent(currentMemberId))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.url) {
                    const iframe = document.getElementById('chat-frame');
                    iframe.src = data.url;
                    iframe.style.display = 'block';
                    document.getElementById('loading').style.display = 'none';
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
</script>

</body>
</html>
