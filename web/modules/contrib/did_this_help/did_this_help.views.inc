<?php

/**
 * @file
 * Provide views data for did_this_help.module.
 */

/**
 * Implements hook_views_data().
 */
function did_this_help_views_data() {
  $data = [];

  $data['did_this_help'] = [
    'table' => [
      'group' => t('Did this help?'),
      'base' => [
        'field' => 'id',
        'title' => t('Did this help? entries'),
        'help' => t('Contains a list of Did this help? entries.'),
      ],
    ],
    'id' => [
      'real field' => 'id',
      'title' => t('Did this help? record ID'),
      'help' => t('Did this help? record.'),
      'field' => [
        'id' => 'standard',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
    ],
  ];


  $data['did_this_help']['path'] = [
    'title' => t('Page URL for Did this help? record'),
    'help' => t('Page URL for Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['did_this_help']['title'] = [
    'title' => t('Page Title for Did this help? record'),
    'help' => t('Page Title for Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['did_this_help']['uid'] = [
    'title' => t('User ID for Did this help? record'),
    'help' => t('User ID for Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'title' => t('User'),
      'help' => t('The user on which the log entry as written.'),
      'base' => 'users_field_data',
      'base field' => 'uid',
      'id' => 'standard',
    ],
  ];

  $data['did_this_help']['choice'] = [
    'title' => t('Yes/No choice for Did this help? record'),
    'help' => t('Yes/No choice for Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'did_this_help',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['did_this_help']['choice_no'] = [
    'title' => t('Answer for No choice'),
    'help' => t('Answer for No choice in Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['did_this_help']['message'] = [
    'title' => t('Message for Did this help? record'),
    'help' => t('Message for Did this help? record'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['did_this_help']['created'] = [
    'title' => t('Created date for Did this help? record'),
    'help' => t('Created date for Did this help? record'),
    'field' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
  ];

  return $data;
}
