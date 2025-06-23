<?php

return [
    'versions' => [
        '1.0' => [
            'guidelines' => [
                [
                    'id' => '1',
                    'title' => 'Provide equivalent alternatives',
                    'checkpoints' => [
                        [
                            'number' => '1.1',
                            'priority' => 'A',
                            'method' => 'checkTextAlternatives'
                        ],
                        // ... add all 65 checkpoints
                    ]
                ],
                // ... all 14 guidelines
            ]
        ],
        '2.0' => [
            'principles' => [
                [
                    'id' => '1',
                    'title' => 'Perceivable',
                    'guidelines' => [
                        [
                            'id' => '1.1',
                            'title' => 'Text Alternatives',
                            'success_criteria' => [
                                [
                                    'number' => '1.1.1',
                                    'level' => 'A',
                                    'method' => 'checkNonTextContent'
                                ],
                                // ... all 2.0 criteria
                            ]
                        ]
                    ]
                ]
            ]
        ],
        // Similar structures for 2.1 and 2.2
    ]
];
