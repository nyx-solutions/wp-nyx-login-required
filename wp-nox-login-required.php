<?php

    /**
     * Plugin Name: Login Required
     * Description: Lock down the whole WordPress site to prevent public access. Only logged-in users can view the site or the content of the REST API if this plugin is activated.
     * Plugin URI:  https://github.com/nox-it/wp-nox-login-required
     * Author:      NOX IT
     * Author URI:  https://github.com/nox-it
     * License:     GNU General Public License v2 or later
     * License URI: http://www.gnu.org/licenses/gpl-2.0.html
     * Version:     1.0.1
     */

    defined('ABSPATH') or die();

    defined('NOX_LR_ENABLE_REST')        or define('NOX_LR_ENABLE_REST',        false);
    defined('NOX_LR_USE_LOGIN_REDIRECT') or define('NOX_LR_USE_LOGIN_REDIRECT', true);
    defined('NOX_LR_HTML_FILE_PATH')     or define('NOX_LR_HTML_FILE_PATH',     null);


    add_action(
        'template_redirect',
        static function () {
            if (!is_user_logged_in()) {
                $canRedirect = NOX_LR_USE_LOGIN_REDIRECT;

                if (!$canRedirect) {
                    $htmlFilePath = NOX_LR_HTML_FILE_PATH;

                    if (is_file($htmlFilePath)) {
                        $html = file_get_contents(NOX_LR_HTML_FILE_PATH);

                        echo $html;

                        http_response_code(200);

                        exit;
                    } else {
                        $canRedirect = true;
                    }
                }

                if ($canRedirect) {
                    auth_redirect();
                }
            }
        }
    );

    add_action(
        'plugins_loaded',
        static function () {
            remove_filter('lostpassword_url', 'wc_lostpassword_url');
        }
    );


    if (!NOX_LR_ENABLE_REST) {
        add_filter(
            'rest_authentication_errors',
            function ($result) {
                if (!empty($result)) {
                    return $result;
                }

                if (!is_user_logged_in()) {
                    return new WP_Error('rest_not_logged_in', 'API Requests are only supported for authenticated requests.', ['status' => 401]);
                }

                return $result;
            }
        );
    }
