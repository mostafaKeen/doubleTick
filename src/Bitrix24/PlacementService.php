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
     * Bind all CRM Detail tab placements with KEEN DoubleTick title
     */
    public function bindCrmTabs(string $tabHandlerUrl, bool $unbindFirst = true): array
    {
        $placements = [
            'CRM_LEAD_DETAIL_TAB' => 'KEEN DoubleTick',
            'CRM_DEAL_DETAIL_TAB' => 'KEEN DoubleTick',
            'CRM_CONTACT_DETAIL_TAB' => 'KEEN DoubleTick',
            'CRM_COMPANY_DETAIL_TAB' => 'KEEN DoubleTick',
        ];

        // Always unbind existing placement handlers first to ensure fresh title & clean re-install
        if ($unbindFirst) {
            $this->unbindCrmTabs();
        }

        $results = [];
        foreach ($placements as $placement => $title) {
            $res = $this->b24->call('placement.bind', [
                'PLACEMENT' => $placement,
                'HANDLER' => $tabHandlerUrl,
                'TITLE' => $title,
                'DESCRIPTION' => 'KEEN DoubleTick WhatsApp Integration Panel',
            ]);
            $results[$placement] = $res;
            Logger::info("placement.bind {$placement} as '{$title}'", ['result' => $res]);
        }

        return $results;
    }

    /**
     * Unbind CRM placements
     */
    public function unbindCrmTabs(?string $tabHandlerUrl = null): array
    {
        $placements = [
            'CRM_LEAD_DETAIL_TAB',
            'CRM_DEAL_DETAIL_TAB',
            'CRM_CONTACT_DETAIL_TAB',
            'CRM_COMPANY_DETAIL_TAB',
        ];

        $results = [];
        foreach ($placements as $placement) {
            $params = ['PLACEMENT' => $placement];
            if ($tabHandlerUrl) {
                $params['HANDLER'] = $tabHandlerUrl;
            }
            $results[$placement] = $this->b24->call('placement.unbind', $params);
            Logger::info("placement.unbind {$placement}", ['result' => $results[$placement]]);
        }

        return $results;
    }
}
