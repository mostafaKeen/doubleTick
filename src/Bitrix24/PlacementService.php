<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Logger;

class PlacementService
{
    private BitrixClient $b24;

    public function __construct(BitrixClient $b24)
    {
        $this->b24 = $b24;
    }

    /**
     * Bind all CRM Detail tab placements
     */
    public function bindCrmTabs(string $tabHandlerUrl): array
    {
        $placements = [
            'CRM_LEAD_DETAIL_TAB' => 'DoubleTick WhatsApp',
            'CRM_DEAL_DETAIL_TAB' => 'DoubleTick WhatsApp',
            'CRM_CONTACT_DETAIL_TAB' => 'DoubleTick WhatsApp',
            'CRM_COMPANY_DETAIL_TAB' => 'DoubleTick WhatsApp',
        ];

        $results = [];
        foreach ($placements as $placement => $title) {
            $res = $this->b24->call('placement.bind', [
                'PLACEMENT' => $placement,
                'HANDLER' => $tabHandlerUrl,
                'TITLE' => $title,
                'DESCRIPTION' => 'DoubleTick WhatsApp Integration Panel',
            ]);
            $results[$placement] = $res;
            Logger::info("placement.bind {$placement}", ['result' => $res]);
        }

        return $results;
    }

    /**
     * Unbind CRM placements
     */
    public function unbindCrmTabs(): array
    {
        $placements = [
            'CRM_LEAD_DETAIL_TAB',
            'CRM_DEAL_DETAIL_TAB',
            'CRM_CONTACT_DETAIL_TAB',
            'CRM_COMPANY_DETAIL_TAB',
        ];

        $results = [];
        foreach ($placements as $placement) {
            $results[$placement] = $this->b24->call('placement.unbind', [
                'PLACEMENT' => $placement,
            ]);
        }

        return $results;
    }
}
