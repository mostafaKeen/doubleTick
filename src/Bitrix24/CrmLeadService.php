<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;

class CrmLeadService
{
    private BitrixClient $b24;

    public function __construct(BitrixClient $b24)
    {
        $this->b24 = $b24;
    }

    /**
     * Create or update Bitrix24 CRM Lead with Meta CTWA ad attribution
     */
    public function createOrUpdateFromWhatsApp(
        string $phone,
        string $customerName,
        bool $isCtwa = false,
        ?array $referral = null,
        ?string $firstMessageText = null
    ): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Check if lead or contact with this phone already exists
        $searchRes = $this->b24->call('crm.lead.list', [
            'filter' => ['PHONE' => $cleanPhone],
            'select' => ['ID', 'TITLE', 'STATUS_ID'],
            'limit' => 1,
        ]);

        $existingLeadId = $searchRes['result'][0]['ID'] ?? null;

        $comments = "Source: DoubleTick WhatsApp\nReceived at: " . date('Y-m-d H:i:s');
        if ($firstMessageText) {
            $comments .= "\nFirst Message: " . $firstMessageText;
        }

        $fields = [
            'NAME' => $customerName ?: 'WhatsApp User',
            'PHONE' => [
                ['VALUE' => $cleanPhone, 'VALUE_TYPE' => 'WORK']
            ],
            'COMMENTS' => $comments,
        ];

        if ($isCtwa && !empty($referral)) {
            $sourceUrl = (string)($referral['source_url'] ?? '');
            $sourceId = (string)($referral['source_id'] ?? '');
            $headline = (string)($referral['headline'] ?? '');
            $ctwaClid = (string)($referral['ctwa_clid'] ?? '');

            $fields['SOURCE_ID'] = 'ADVERTISING';
            $fields['SOURCE_DESCRIPTION'] = "Meta WhatsApp Ad: {$headline}";
            $fields['UTM_SOURCE'] = 'meta_whatsapp';
            $fields['UTM_MEDIUM'] = 'cpc';
            $fields['UTM_CAMPAIGN'] = $sourceId;
            $fields['UTM_CONTENT'] = $headline;

            $comments .= "\n\n--- Meta Click-To-WhatsApp Ad Attribution ---";
            $comments .= "\nAd ID: " . $sourceId;
            $comments .= "\nHeadline: " . $headline;
            if (!empty($referral['body'])) {
                $comments .= "\nAd Body: " . $referral['body'];
            }
            if (!empty($ctwaClid)) {
                $comments .= "\nCTWA Click ID: " . $ctwaClid;
            }
            if (!empty($sourceUrl)) {
                $comments .= "\nAd Source URL: " . $sourceUrl;
            }
            $fields['COMMENTS'] = $comments;
        } else {
            $fields['SOURCE_ID'] = 'CALL';
            $fields['SOURCE_DESCRIPTION'] = 'Inbound WhatsApp Message';
        }

        if ($existingLeadId) {
            // Update existing lead with attribution if missing
            $leadId = (int)$existingLeadId;
            $res = $this->b24->call('crm.lead.update', [
                'ID' => $leadId,
                'fields' => $fields,
            ]);
            Logger::info("Updated existing Bitrix24 Lead #{$leadId}", ['phone' => $cleanPhone]);
        } else {
            // Create new lead
            $fields['TITLE'] = "WhatsApp Lead: " . ($customerName ?: "+{$cleanPhone}");
            $res = $this->b24->call('crm.lead.add', [
                'fields' => $fields,
                'params' => ['REGISTER_SONET_EVENT' => 'Y'],
            ]);
            $leadId = (int)($res['result'] ?? 0);
            Logger::info("Created new Bitrix24 Lead #{$leadId}", ['phone' => $cleanPhone]);
        }

        // Record in lead_attributions table
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO lead_attributions (
                portal_id, lead_id, customer_phone, customer_name, is_ctwa,
                source_url, source_id, headline, ctwa_clid, created_at
            ) VALUES (
                :portal_id, :lead_id, :phone, :name, :is_ctwa,
                :source_url, :source_id, :headline, :ctwa_clid, datetime('now')
            )
        ");
        $stmt->execute([
            'portal_id' => $this->b24->getPortalId(),
            'lead_id' => $leadId,
            'phone' => $cleanPhone,
            'name' => $customerName,
            'is_ctwa' => $isCtwa ? 1 : 0,
            'source_url' => $referral['source_url'] ?? null,
            'source_id' => $referral['source_id'] ?? null,
            'headline' => $referral['headline'] ?? null,
            'ctwa_clid' => $referral['ctwa_clid'] ?? null,
        ]);

        return [
            'lead_id' => $leadId,
            'is_new' => empty($existingLeadId),
            'attribution' => $isCtwa,
        ];
    }
}
