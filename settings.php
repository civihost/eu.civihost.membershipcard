<?php
return [
  'memberships' => [
    //'year_starts_from' => '01-01', // mm-dd
    'card' => [
      'url' => 'https://member.asus.sh/sites/member.asus.sh/wp-content/uploads/modello.png',
      'page_format' => 1028,
      'template' => 73,
      'barcode_text' => 'https://member.asus.sh/profile/?id={contact.contact_id}&{contact.checksum}',
      'custom_fields' => [
        'Sede_di_studio.Sede',
      ],
    ],
  ],
  'enable_user_dashboard' => true,
  'attach_to_templates' => [
    69,
  ],
];
