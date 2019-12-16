<?php

$app_icon = $this->get_option('plugin_icon', 'cat');

return
[
    'title' => __('WP Petfinder Options', 'wppf'),
    'logo'  => str_replace('/inc/', '/', plugins_url('images/icon-'. $app_icon. '-128.png', __file__)),
    'menus' =>
    [
        [
            'name'  => 'wppf-general',
            'title' => __('General Options', 'wppf'),
            'icon'  => 'font-awesome:fa-cogs',
            'controls' =>
            [
                [
                    'type'   => 'section',
                    'title'  => __('Petfinder API', 'wppf'),
                    'fields' =>
                    [
                        [
                            'type'          => 'textbox',
                            'name'          => 'api_key',
                            'label'         => __('Petfinder API key', 'wppf'),
                            'description'   => __(sprintf('You can obtain one <a href="%s" target="_blank">here</a>', 'https://www.petfinder.com/developers/'), 'wppf'),
                            'default'       => ''
                        ],
                        [
                            'type'          => 'textbox',
                            'name'          => 'api_secret',
                            'label'         => __('Petfinder API secret', 'wppf'),
                            'description'   => __('To obtain one see above', 'wppf'),
                            'default'       => ''
                        ],
                        [
                            'type'          => 'toggle',
                            'name'          => 'cache',
							'label'         => __('WP Petfinder Cache', 'wppf'),
							'description'   => __('Turn it Off if you are using a caching plugin such as W3TC', 'wppf'),
							'default'       => '1',
                        ]
                    ]
                ],
                [
                    'type'   => 'section',
                    'title'  => __('WP Petfinder Plugin', 'wppf'),
                    'fields' =>
                    [
                        [
                            'type'           => 'select',
                            'name'           => 'results_page',
                            'label'          => __('Search Results page', 'wppf'),
                            'description'    => __('Will redirect search results to specified page', 'wppf'),
                            'items'          =>
                            [
                                'data'       =>
                                [
                                    [
                                        'source' => 'function',
                                        'value' => 'vp_get_pages',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type'           => 'select',
                            'name'           => 'animal_page',
                            'label'          => __('Animal Details page', 'wppf'),
                            'description'    => __('Which page should we use as detailed information for animals?', 'wppf'),
                            'items'          =>
                            [
                                'data'       =>
                                [
                                    [
                                        'source' => 'function',
                                        'value' => 'vp_get_pages',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type'           => 'select',
                            'name'           => 'adopt_page_cat',
                            'label'          => __('Cat Adoption Page', 'wppf'),
                            'description'    => __('Redirect to this page when Adopt Me button is clicked', 'wppf'),
                            'items'          =>
                            [
                                'data'       =>
                                [
                                    [
                                        'source' => 'function',
                                        'value' => 'vp_get_pages',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type'           => 'select',
                            'name'           => 'adopt_page_dog',
                            'label'          => __('Dog Adoption Page', 'wppf'),
                            'description'    => __('Redirect to this page when Adopt Me button is clicked; can  be the same as above', 'wppf'),
                            'items'          =>
                            [
                                'data'       =>
                                [
                                    [
                                        'source' => 'function',
                                        'value' => 'vp_get_pages',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type'           => 'textbox',
                            'name'           => 'page_title',
                            'label'          => __('Detail page title', 'wppf'),
                            'description'    => __('Document title for SEO', 'wppf'),
                            'default'        => '[name] - [gender] [age] [type]'
                        ],
                    ]
                ],
            ]
        ],
        [
            'name'  => 'wppf-look',
            'title' => __('Look &amp; Feel', 'wppf'),
            'icon'  => 'font-awesome:fa-eye',
            'controls' =>
            [
                [
                    'type'   => 'section',
                    'title'  => __('Icons &amp; Images', 'wppf'),
                    'fields' =>
                    [
                        [
                            'type'           => 'radioimage',
                            'name'           => 'plugin_icon',
                            'label'          => __('WP Petfinder Icon', 'wppf'),
                            'description'    => __('Menu and App icon for WP Petfinder Plugin', 'wppf'),
                            'items' =>
                            [
                                [
                                    'value'  => 'cat',
                                    'label'  => __('Cat', 'wppg'),
                                    'img'    => str_replace('/inc/', '/', plugins_url('images/icon-cat-64.png', __file__)),
                                ],
                                [
                                    'value'  => 'dog',
                                    'label'  => __('Dog', 'wppg'),
                                    'img'    => str_replace('/inc/', '/', plugins_url('images/icon-dog-64.png', __file__)),
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'type'   => 'section',
                    'title'  => __('CSS', 'wppf'),
                    'fields' =>
                    [
                        [
							'type'           => 'codeeditor',
							'name'           => 'custom_css',
							'label'          => __('Custom CSS', 'wpps'),
							'description'    => __('Put your custom CSS here', 'wpps'),
							'theme'          => 'github',
							'mode'           => 'css'
                        ]
                    ]
                ]
            ]
        ],
        /*
        [
            'name'  => 'wppf-customize',
            'title' => __('Customizer', 'wppf'),
            'icon'  => 'font-awesome:fa-css3',
            'controls' =>
            [
                [
                    'type'   => 'section',
                    'title'  => __('Use Customizer', 'wppf'),
                    'fields' =>
                    [
                        [
                            'type'          => 'toggle',
                            'name'          => 'use_customizer',
							'label'         => __('Use Customizer', 'wppf'),
							'description'   => __('Turn it Off if you want to customize plugin using CSS.<br /><b>Styles below will override CSS</b>', 'wppf'),
							'default'       => '0',
                        ]
                    ]
                ],
                [
                    'type'   => 'section',
                    'title'  => __('Buttons', 'wppf'),
                    'fields' =>
                    [
                        [
                            'type'          => 'color',
                            'name'          => 'button_color',
                            'label'         => __('Button Color', 'wppf'),
                            'description'   => __('Override default button color', 'wppf'),
                            'default'       => 'rgba(212,0,134,1)',
                            'format'        => 'rgba',
                        ],
                        [
                            'type'          => 'color',
                            'name'          => 'button_border',
                            'label'         => __('Button Border', 'wppf'),
                            'description'   => __('Button border color', 'wppf'),
                            'default'       => 'rgba(212,0,134,1)',
                            'format'        => 'rgba',
                        ],
                        [
                            'type'          => 'color',
                            'name'          => 'button_text',
                            'label'         => __('Button Text Color', 'wppf'),
                            'description'   => __('Button Text Color', 'wppf'),
                            'default'       => 'rgba(255,255,255,1)',
                            'format'        => 'rgba',
                        ],
						[
							'type'          => 'textbox',
							'name'          => 'adopt_text',
							'label'         => __('Adopt Me button text', 'wppf'),
							'description'   => __('Text that appears on Adopt Me button. You can also customize it using shortcode [pf_details]', 'wppf'),
							'default'       => __('Adopt Me', 'wppf'),
						],
                    ]
                ],
            ]
        ]
        */
    ]
];
?>