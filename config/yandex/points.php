<?php

return [
    'adgroups' => [
        'add' => [
            'per_request' => 20,
            'per_unit' => 20
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 0
        ],
        'get' => [
            'per_request' => 15,
            'per_unit' => 1
        ],
        'update' => [
            'per_request' => 20,
            'per_unit' => 20
        ]
    ],
    'ads' => [
        'add' => [
            'per_request' => 20,
            'per_unit' => 20
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 0
        ],
        'get' => [
            'per_request' => 15,
            'per_unit' => 1
        ],
        'update' => [
            'per_request' => 20,
            'per_unit' => 20
        ],
        'archive' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'moderate' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'resume' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'suspend' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'unarchive' => [
            'per_request' => 40,
            'per_unit' => 0
        ]
    ],
    'campaigns' => [
        'add' => [
            'per_request' => 10,
            'per_unit' => 5
        ],
        'archive' => [
            'per_request' => 10,
            'per_unit' => 5
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 2
        ],
        'get' => [
            'per_request' => 10,
            'per_unit' => 1
        ],
        'resume' => [
            'per_request' => 10,
            'per_unit' => 5
        ],
        'suspend' => [
            'per_request' => 10,
            'per_unit' => 5
        ],
        'unarchive' => [
            'per_request' => 10,
            'per_unit' => 5
        ],
        'update' => [
            'per_request' => 10,
            'per_unit' => 3
        ]
    ],
    'keywords' => [
        'add' => [
            'per_request' => 20,
            'per_unit' => 2
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 1
        ],
        'get' => [
            'per_request' => 15,
            'per_unit' => 1
        ],
        'resume' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'suspend' => [
            'per_request' => 15,
            'per_unit' => 0
        ],
        'update' => [
            'per_request' => 20,
            'per_unit' => 2
        ]
    ],
    'sitelinks' => [
        'add' => [
            'per_request' => 20,
            'per_unit' => 20
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 0
        ],
        'get' => [
            'per_request' => 15,
            'per_unit' => 1
        ]
    ],
    'vcards' => [
        'add' => [
            'per_request' => 20,
            'per_unit' => 20
        ],
        'delete' => [
            'per_request' => 10,
            'per_unit' => 0
        ],
        'get' => [
            'per_request' => 15,
            'per_unit' => 1
        ]
    ],
    'dictionaries' => [
        'get' => [
            'per_request' => 1,
            'per_unit' => 1
        ]
    ],
    'bids' => [
        'get' => [
            'per_request' => 15,
            'per_unit' => 0.0005
        ],
        'set' => [
            'per_request' => 25,
            'per_unit' => 0
        ]
    ],
    'changes' => [
        'check' => [
            'per_request' => 10,
            'per_unit' => 0
        ],
    ],
];