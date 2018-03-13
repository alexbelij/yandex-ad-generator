<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.04.16
 * Time: 22:52
 */

return [
    'StartDate' => date('Y-m-d'),
    'TextCampaign' => [
        'BiddingStrategy' => [
            'Search' => [
                'BiddingStrategyType' => 'LOWEST_COST'
            ],
            'Network' => [
                'BiddingStrategyType' => 'SERVING_OFF'
            ]
        ],
        'Settings' => [
            [
                'Option' => 'ENABLE_AREA_OF_INTEREST_TARGETING',
                'Value' => 'NO'
            ],
            [
                'Option' => 'ADD_METRICA_TAG',
                'Value' => 'YES'
            ],
            [
                'Option' => 'ENABLE_SITE_MONITORING',
                'Value' => 'YES'
            ],
            [
                'Option' => 'ENABLE_RELATED_KEYWORDS',
                'Value' => 'NO'
            ],
            [
                'Option' => 'EXCLUDE_PAUSED_COMPETING_ADS',
                'Value' => 'YES'
            ]
        ]
    ],
];