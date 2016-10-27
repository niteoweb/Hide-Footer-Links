<?php

use Niteoweb\HideFooterLinks\FooterLinks;

class TestFooterLinks extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        \WP_Mock::setUsePatchwork(true);
        \WP_Mock::setUp();
    }

    function tearDown()
    {
        \WP_Mock::tearDown();
    }

    public function test_init_admin()
    {
        \WP_Mock::wpFunction('is_admin', array(
                'return' => true,
            )
        );

        \WP_Mock::wpFunction('current_user_can', array(
                'return' => true,
            )
        );

        $plugin = new FooterLinks;

        \WP_Mock::expectActionAdded('generate_rewrite_rules', array($plugin, 'generateRewriteRules'));
        \WP_Mock::expectActionAdded('plugins_loaded', array($plugin, 'pluginsLoaded'));
        \WP_Mock::expectActionAdded('admin_notices', array($plugin, 'activatePluginNotice'));
        \WP_Mock::expectActionAdded('customize_register', array($plugin, 'customizeRegister'));
        \WP_Mock::expectActionAdded('wp_head', array($plugin, 'customizeCSS'));

        $plugin->__construct();
        \WP_Mock::assertHooksAdded();
    }

    public function test_generate_rewrite_rules()
    {
        global $wp_rewrite;
        \WP_Mock::wpFunction('wp_make_link_relative', array(
                'return' => '/wp-content/plugins/footer-links/',
            )
        );
        \WP_Mock::wpFunction('plugin_dir_url', array(
                'return' => 'http://localhost/wp-content/plugins/footer-links/',
            )
        );
        $wp_rewrite = \Mockery::mock();
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/",
                "index.php%{REQUEST_URI}"
            )
        );
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/index.php",
                "index.php%{REQUEST_URI}"
            )
        );
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/readme.txt",
                "index.php%{REQUEST_URI}"
            )
        );
        $plugin = new FooterLinks;
        $plugin->generateRewriteRules($wp_rewrite);
    }

}
