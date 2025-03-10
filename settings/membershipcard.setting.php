<?php

use CRM_Membershipcard_ExtensionUtil as E;

// Build a list of enabled locales if multi-lingual
$is_multilingual = CRM_Core_I18n::isMultilingual();
$domain = new CRM_Core_DAO_Domain();
$domain->find(TRUE);

if ($domain->locales) {
  $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);
}
else {
  $locales[] = CRM_Core_I18n::getLocale();
}

foreach ($locales as $locale) {
  $settings['membershipcard_pass_logo_text_' . $locale] = [
    'name' => 'membershipcard_pass_logo_text_' . $locale,
    'type' => 'String',
    'default' => '',
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Card Logo Text') . ($is_multilingual ? ' - ' . $locale : ''),
    'description' => E::ts("Apple - displayed in the header, next to the logo. Ex: Your Org Name"),
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 5,
      ],
    ],
  ];
  $settings['membershipcard_pass_title_' . $locale] = [
    'name' => 'membershipcard_pass_title_' . $locale,
    'type' => 'String',
    'default' => '',
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Card Title') . ($is_multilingual ? ' - ' . $locale : ''),
    'description' => E::ts("Apple - displayed in the View Details. Ex: Your Org Name - Member Card"),
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 7,
      ],
    ],
  ];
}

$settings += [
  'membershipcard_contact_dashboard' => [
    'name' => 'membershipcard_contact_dashboard',
    'type' => 'Boolean',
    'default' => 0,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Display on the Contact Dashboard'),
    'description' => E::ts('If enabled, "Download" button will be displayed at the bottom of the Contact Dashboard.'),
    'quick_form_type' => 'YesNo',
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 8,
      ],
    ],
  ],
  'membershipcard_google_issuer_id' => [
    'name' => 'membershipcard_google_issuer_id',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Google Issuer ID'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('20 digit ID displayed on the Google Wallet API dashboard of the Google business console.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 10,
      ],
    ],
  ],
  'membershipcard_google_class_id' => [
    'name' => 'membershipcard_google_class_id',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Google Wallet Class ID'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('The name of the class in Google Wallet (assuming it was created manually from the Google Wallet dashboard).'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 15,
      ],
    ],
  ],
  'membershipcard_google_object_prefix' => [
    'name' => 'membershipcard_google_object_prefix',
    'type' => 'String',
    'default' => 'emember',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Google Wallet Object Prefix'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('When creating passes, the pass (object) will have a machine-name such as [issuerID].[prefix][contact_id]. Only visible to administrators, not to members.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 20,
      ],
    ],
  ],
  'membershipcard_google_json' => [
    'name' => 'membershipcard_google_json',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'textarea',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Google Wallet JSON settings'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('JSON blob with various Google Wallet settings and private key.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 25,
      ],
    ],
  ],
  'membershipcard_apple_team_identifier' => [
    'name' => 'membershipcard_apple_team_identifier',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple Team Identifier'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('10 character ID, displayed on developer.apple.com/account, under the Membership Details.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 30,
      ],
    ],
  ],
  'membershipcard_apple_passtype_identifier' => [
    'name' => 'membershipcard_apple_passtype_identifier',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple PassType Identifier'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Pass Type ID. Ex: pass.org.example.member-card'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 35,
      ],
    ],
  ],
  'membershipcard_apple_org_name' => [
    'name' => 'membershipcard_apple_org_name',
    'type' => 'String',
    'default' => NULL,
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple Organization Name'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Organization Name linked to the pass/certificate.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 40,
      ],
    ],
  ],
  'membershipcard_apple_bg_color' => [
    'name' => 'membershipcard_apple_bg_color',
    'type' => 'String',
    'default' => 'rgb(255,255,255)',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple Pass Background Color'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Background color in RGB format. Ex: rgb(255, 255, 255)'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 45,
      ],
    ],
  ],
  'membershipcard_apple_fg_color' => [
    'name' => 'membershipcard_apple_fg_color',
    'type' => 'String',
    'default' => 'rgb(0,0,0)',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple Pass Foreground Color'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Foreground color in RGB format. Mostly for text, but not labels. Ex: rgb(0, 0, 0)'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 50,
      ],
    ],
  ],
  'membershipcard_apple_label_color' => [
    'name' => 'membershipcard_apple_label_color',
    'type' => 'String',
    'default' => 'rgb(0,0,0)',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Apple Pass Label Color'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Label color in RGB format. Ex: rgb(0, 0, 0)'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 55,
      ],
    ],
  ],
  'membershipcard_member_id' => [
    'name' => 'membershipcard_member_id',
    'type' => 'String',
    'default' => 'id',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge',
    ],
    'add' => '1.0',
    'title' => E::ts('Member ID field'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Member ID to display on the card. Defaults to the Contact ID. Only change this if you have a custom field with a different ID. The first must use the Api4 syntax.'),
    'settings_pages' => [
      'membershipcard' => [
        'weight' => 90,
      ],
    ],
  ],
];

return $settings;
