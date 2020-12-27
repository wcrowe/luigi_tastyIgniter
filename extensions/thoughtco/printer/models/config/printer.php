<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
		        'create' => [
		            'label' => 'lang:admin::lang.button_new',
		            'class' => 'btn btn-primary',
		            'href' => 'thoughtco/printer/printers/create',
		        ],
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
            ],
        ],
		'filter' => [
			'scopes' => [
				'is_enabled' => [
					'label' => 'lang:admin::lang.text_filter_status',
					'type' => 'switch',
					'conditions' => 'is_enabled = :filtered',
				],
			],
		],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'thoughtco/printer/printers/edit/{id}',
                ],
            ],
            'label' => [
                'label' => 'lang:thoughtco.printer::default.column_label',
                'type' => 'text',
                'sortable' => TRUE,
            ],
			'is_enabled' => [
				'label' => 'lang:thoughtco.printer::default.column_status',
				'type' => 'switch',
				'sortable' => FALSE,
			],
            'id' => [
                'type' => 'text',
                'label' => '',
				'sortable' => FALSE,
                'formatter' => function($something, $column, $value){
					return '<a class="btn btn-primary" href="'.admin_url('thoughtco/printer/autoprint?location='.$value).'">'.lang('thoughtco.printer::default.btn_autoprint').'</a>';
                }
            ],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/printer/printers'],
                'save' => [
                    'label' => 'lang:admin::lang.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                ],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'tabs' => [
	        'fields' => [
	            'location_id' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_location',
	                'type' => 'select',
					'span' => 'left',
	            ],
				'is_enabled' => [
					'tab' => 'lang:thoughtco.printer::default.tab_connection',
					'label' => 'lang:thoughtco.printer::default.label_status',
					'type' => 'switch',
					'span' => 'right',
				],
	            'label' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_label',
	                'type' => 'text',
	            ],
	            'printer_settings[type]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'Type',
	                'type' => 'select',
	                'options' => [
		                'usb' => 'USB',
		                'ip' => 'IP/Network',
		                'ethernet' => 'Epson ePOS Webservice'
	                ]
	            ],
	            'printer_settings[usb_setup]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_usb_setup',
	                'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/usbsetup',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]',
		            ],
				],
	            'printer_settings[ip_setup]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ip_setup',
	                'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/ipsetup',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ip]',
		            ],
	                'span' => 'left',
				],
	            'printer_settings[ssl]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ssl',
	                'type' => 'switch',
	                'value' => 1,
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ip]',
		            ],
	                'span' => 'right',
	            ],
	            'printer_settings[ip_address]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ip',
	                'type' => 'text',
	                'attributes' => [
		                'pattern' => '((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$'
	                ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]',
		            ],
	                'span' => 'left',
	            ],
	            'printer_settings[port]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_port',
	                'type' => 'text',
	                'attributes' => [
		                'maxlength' => 4,
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]',
		            ],
	                'span' => 'right',
	            ],
	            'printer_settings[device_name]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.device_name',
	                'type' => 'text',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ethernet]',
		            ],
	                'span' => 'left',
	            ],
	            'printer_settings[copies]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_copies',
	                'type' => 'text',
					'default' => 1,
	                'attributes' => [
		                'maxlength' => 4,
		            ],
	                'span' => 'left',
	            ],
				'printer_settings[characters_per_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_characters_per_line',
					'type' => 'text',
					'default' => 48,
					'attributes' => [
						'maxlength' => 4,
					],
					'span' => 'right',
				],
				'printer_settings[codepage]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_codepage',
					'type' => 'text',
					'default' => 16,
					'attributes' => [
						'maxlength' => 4,
					],
					'span' => 'left',
					'comment' => 'lang:thoughtco.printer::default.comment_codepage',
				],
	            'printer_settings[categories]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_categories',
		            'type' => 'selectlist',
		            'options' => \Thoughtco\Printer\Models\Printer::getCategoryOptions(),
	                'span' => 'right',
					'comment' => 'lang:thoughtco.printer::default.comment_categories',
	            ],
	            'printer_settings[getstatus]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_getstatus',
		            'type' => 'select',
		            'options' => \Thoughtco\Printer\Models\Printer::getStatusOptions(false),
	                'span' => 'left',
	            ],
	            'printer_settings[setstatus]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_setstatus',
		            'type' => 'select',
		            'options' => \Thoughtco\Printer\Models\Printer::getStatusOptions(true),
	                'span' => 'right',
	            ],
	            'printer_settings[autocut]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_autocut',
	                'type' => 'switch',
	                'default' => 1,
	                'span' => 'left',
	            ],
	            'printer_settings[autoprint_everywhere]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_everywhere',
	                'type' => 'switch',
	                'default' => 0,
	                'span' => 'right',
	            ],
	            'printer_settings[autoprint_sameday]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_sameday',
	                'type' => 'switch',
	                'default' => 1,
	                'span' => 'left',
	            ],
	            'printer_settings[usedefault]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_default',
	                'type' => 'switch',
		            'default' => 1,
	                'span' => 'right',
	            ],
	            'printer_settings[format]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.output_format',
	                'type' => 'markdowneditor',
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
	            'printer_settings[lines_before]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.lines_before',
		            'type' => 'number',
					'default' => 0,
		            'attributes' => [
			            'step' => 1,
			            'min' => 0
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
	            'printer_settings[lines_after]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.lines_after',
		            'type' => 'number',
					'default' => 0,
		            'attributes' => [
			            'step' => 1,
			            'min' => 0
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
				'printer_settings[encoding]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_encoding',
					'type' => 'select',
					'options' => \Thoughtco\Printer\Models\Printer::getEncodingOptions(),
                    'default' => 'windows-1252',
					'trigger' => [
						'action' => 'hide',
						'field' => 'printer_settings[usedefault]',
						'condition' => 'checked',
					],
				],
		        'variables' => [
		            'tab' => 'lang:system::lang.mail_templates.text_variables',
		            'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/variables',
		            'disabled' => TRUE
		        ],
	        ]
        ]
    ],
];
