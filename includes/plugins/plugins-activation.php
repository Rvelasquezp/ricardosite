<?php

if (!function_exists('utopian_set_plugins_array_to_install')) {
    function utopian_set_plugins_array_to_install()
    {
        global $default_plugins_array_to_install;

        $default_plugins_array_to_install = array('contact-form-7', 'wordpress-seo', 'advanced-custom-fields-pro', 'otgs-installer', 'svg-support');
    }
}
add_action('utopian_action_before_options_map', 'utopian_set_plugins_array_to_install');

if (!function_exists('utopian_plugins_list')) {
    function utopian_plugins_list($filter_array = array())
    {
        $plugins = array(
            array(
                'name'                     => esc_html__('Contact Form 7', 'utopian'),
                'slug'                     => 'contact-form-7',
                'source'                   => '',
                'required'                 => true,
                'version'                 => '',
                'force_activation'         => false,
                'force_deactivation'     => false,
                'external_url'             => ''
            ),
            array(
                'name'                     => esc_html__('Yoast SEO', 'utopian'),
                'slug'                     => 'wordpress-seo',
                'source'                   => '',
                'required'                 => true,
                'version'                 => '',
                'force_activation'         => false,
                'force_deactivation'     => false,
                'external_url'             => ''
            ),
            array(
                'name'                     => esc_html__('Advanced Custom Fields Pro', 'utopian'),
                'slug'                     => 'advanced-custom-fields-pro',
                'source'                => get_template_directory() . '/plugins/advanced-custom-fields-pro.zip',
                'required'                 => false,
                'version'                 => '',
                'force_activation'         => false,
                'force_deactivation'     => false,
                'external_url'             => ''
            ),
            array(
                'name'                     => esc_html__('WPML', 'utopian'),
                'slug'                     => 'otgs-installer',
                'source'                => get_template_directory() . '/plugins/otgs-installer-plugin.3.1.0.zip',
                'required'                 => false,
                'version'                 => '',
                'force_activation'         => false,
                'force_deactivation'     => false,
                'external_url'             => ''
            ),
            array(
                'name'                     => esc_html__('SVG Support', 'utopian'),
                'slug'                     => 'svg-support',
                'source'                => get_template_directory() . '/plugins/svg-support.2.5.5.zip',
                'required'                 => false,
                'version'                 => '',
                'force_activation'         => false,
                'force_deactivation'     => false,
                'external_url'             => ''
            )
        );

        if (!empty($filter_array)) {
            $filtered_plugins = array();
            foreach ($filter_array as $k1 => $val1) {
                foreach ($plugins as $k2 => $val2) {
                    if ($plugins[$k2]['slug'] == $val1) {
                        $filtered_plugins[$plugins[$k2]['slug']] = $plugins[$k2]['name'];
                    }
                }
            }
            return $filtered_plugins;
        } else {
            return $plugins;
        }
    }
}

if (!function_exists('utopian_register_theme_included_plugins')) {
    function utopian_register_theme_included_plugins()
    {
        global $default_plugins_array_to_install;
        $plugins = utopian_plugins_list();
        $plugins_to_load = array();

        // Check if the option is already set (e.g., during theme updates)
        if (!add_option("utopian_required_plugins", $default_plugins_array_to_install)) {
            $former_options = get_option("utopian_required_plugins");
            if (is_array($default_plugins_array_to_install) && count($default_plugins_array_to_install) > 0) {
                foreach ($default_plugins_array_to_install as $default_plugin) {
                    if (!in_array($default_plugin, $former_options)) {
                        array_push($former_options, $default_plugin);
                    }
                }
            }
            update_option("utopian_required_plugins", $former_options);
        }

        $utopian_required_plugins = get_option("utopian_required_plugins");
        if (empty($utopian_required_plugins)) {
            $utopian_required_plugins = array();
        }

        // Load and activate the required plugins
        foreach ($utopian_required_plugins as $required_plugin) {
            foreach ($plugins as $plugin) {
                if ($plugin['slug'] === $required_plugin) {
                    $plugins_to_load[] = $plugin;
                }
            }
        }

        $config = array(
            'id'           => 'utopian-tgmpa',
            'default_path' => '',
            'menu'         => 'install-required-plugins',
            'parent_slug'  => 'themes.php',
            'capability'   => 'manage_options', // Change this to the appropriate capability
            'has_notices'  => true,
            'dismissable'  => true,
            'is_automatic' => true,
            'message'      => '',


            // Customize these strings as needed
            'strings'      => array(
                'page_title'                      => esc_html__('Install Required Plugins', 'utopian'),
                'menu_title'                      => esc_html__('Install Plugins', 'utopian'),
                'installing'                      => esc_html__('Installing Plugin: %s', 'utopian'),
                'oops'                            => esc_html__('Something went wrong with the plugin API.', 'utopian'),
                'notice_can_install_required'      => _n_noop('This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'utopian'),
                'notice_can_activate_required'     => _n_noop('The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'utopian'),
                'return'                          => esc_html__('Return to Required Plugins Installer', 'utopian'),
                'plugin_activated'                => esc_html__('Plugin activated successfully.', 'utopian'),
                'complete'                        => esc_html__('All plugins installed and activated successfully. %s', 'utopian'),
            ),
        );

        tgmpa($plugins_to_load, $config);
    }
}

add_action('tgmpa_register', 'utopian_register_theme_included_plugins');
