<?php
namespace Niteoweb\HideFooterLinks;
/**
* Plugin Name: Hide Footer Links
* Description: Hide Footer Links will try to remove site info and copyright info from your footer.
* Version:     1.0.1
* Runtime:     5.3+
* Author:      Easy Blog Networks
* Author URI:  www.easyblognetworks.com
*/

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    ?>
    <div id="error-page">
        <p>This plugin requires PHP 5.3.0 or higher. Please contact your hosting provider about upgrading your
            server software. Your PHP version is <b><?php echo PHP_VERSION; ?></b></p>
    </div>
    <?php
    die();
}

class FooterLinks
{
    const OptionName = 'Niteoweb.FooterLinks.Themes';
    const nonce = 'Niteoweb.FooterLinks.Nonce';
    const CheckHook = 'Niteoweb.FooterLinks.CheckHook';

    // Array of themes and class name to hide. Maybe extend this to hold an
    // array of classes or an override function for more complex solutions.
    private $themes = array(
        'accelerate'      => array('.footer-socket-wrapper .copyright', false),
        'accesspress-root'=> array('div.copyright', false),
        'adelle'          => array('p.footer-copy', false),
        'aldehyde'        => array('#colophon .site-info', false),
        'base-wp'         => array('#colophon .site-info', false),
        // 'brightnews'   => array('', false),   // Has empty style.css
        'bwater'          => array('#colophon #site-generator', false),
        'contango'        => array('#colophon .site-info, #colophon .copyright, #colophon .credit', true),
        'coraline'        => array('#colophon', false),
        'decode'          => array('#colophon > div', false),
        'displace'        => array('#colophon > div', false),
        'duster'          => array('#site-generator', false),
        'dw-minion'       => array('#colophon > div > div', false),
        'editor'          => array('#colophon > div', false),
        'elucidate'       => array('#colophon > div', false),
        'enough'          => array('#enough-page > footer > address small', false),
        'esquire'         => array('#credit > p', false),
        'esteem'          => array('#site-generator > div', false),
        'exray'           => array('#footer-container > footer > .copyright-container> div > p:nth-child(2)', false),
        'fictive'         => array('#colophon > div', false),
        'flat'            => array('#primary > footer', true),
        'flaton'          => array('#colophon > div > div > div > div', false),
        'forever'         => array('#site-info', false),
        'govpress'        => array('#page > footer > div', false),
        'graphy'          => array('#colophon .site-credit', false),
        'isola'           => array('#colophon > div', false),
        'minimize'        => array('#footer > p', false),
        'monaco'          => array('#colophon > div.site-info', false),
        'motif'           => array('#colophon > div', false),
        'omega'           => array('footer .credit', true),
        'origami'         => array('#footer > div.designed', false),
        'padhang'         => array('#colophon > div > div.powered', false),
        'parament'        => array('#site-generator', false),
        'pilcrow'         => array('#site-generator', false),
        'pink-touch-2'    => array('#footer > div > p', false),
        'prana'           => array('#footer .grid_credit', false),
        'quark'           => array('#footercontainer div.smallprint p', true),
        'responsivo'      => array('#footercontainer .row.smallprint  p', false),
        // 'restimpo'       => array('', false),
        'sparkling'       => array('.site-info.container .copyright', false),
        'startup'         => array('#copyright', false),
        'suffusion'       => array('#cred > table > tbody > tr > td.cred-right', false),
        'sugar-and-spice' => array('#footer > div', false),
        'syntax'          => array('#colophon > div', false),
        // 'thematic'     => array('', false),
        // 'twentyeleven'     => array('', false),
        'twentyfourteen'  => array('#colophon > div', false),
        'twentysixteen'   => array('.site-info', false),
        'twentyten'       => array('#colophon', false),
        'twentythirteen'  => array('#colophon > div.site-info', false),
        'twentytwelve'    => array('#colophon', false),
        'unite'           => array('#colophon > div > div > div', false),
        'wilson'          => array('body > div > div.content > div.credits', false),
        'zbench'          => array('#footer-inside > p', false)
    );

    private $theme = none;
    private $theme_stylesheet = '';

    function __construct()
    {
        $this->theme = wp_get_theme();
        $this->theme_stylesheet = $this->theme->get_template();

        add_action('generate_rewrite_rules', array(&$this, 'generateRewriteRules'));
        add_action('plugins_loaded', array( &$this, 'pluginsLoaded'));
    }

    /**
     * @codeCoverageIgnore
     */
    function pluginsLoaded() {
        if (is_admin()) {
            add_action('admin_notices', array(&$this, 'activatePluginNotice'));
        }
        if (current_user_can('edit_theme_options')) {
            add_action('customize_register', array(&$this, 'customizeRegister'));
        }

        add_action('wp_head', array(&$this, 'customizeCSS'));
    }

    /**
     * @codeCoverageIgnore
     */
    function generateRewriteRules($wp_rewrite)
    {
        // Protect plugin from direct access
        $wp_rewrite->add_external_rule($this->pluginURL() . 'index.php', 'index.php%{REQUEST_URI}');
        $wp_rewrite->add_external_rule($this->pluginURL() . 'readme.txt', 'index.php%{REQUEST_URI}');
        $wp_rewrite->add_external_rule($this->pluginURL(), 'index.php%{REQUEST_URI}');
    }

    /**
     * @codeCoverageIgnore
     */
    public function customizeRegister($wp_customize)
    {
        $opts =  get_option('hfl_options');
        $wp_customize->add_section(
            'hfl_settings_section',
            array(
                'title'         => 'Footprint Settings',
                'priority'      => 430,
            )
        );

        $wp_customize->add_setting(
            'hfl_options[hide-enabled]',
            array(
                'default'       => '',
                'type'          => 'option',
                'transport'     => 'refresh',
            )
        );

        $wp_customize->add_setting(
            'hfl_options[hide-selector]',
            array(
                'default'       => '',
                'type'          => 'option',
                'transport'     => 'refresh',
            )
        );

        $wp_customize->add_setting(
            'hfl_options[use-important]',
            array(
                'default'       => false,
                'type'          => 'option',
                'transport'     => 'refresh',
            )
        );

        $wp_customize->add_control(
            'hfl_enable_hide_control',
            array(
                'label'         => 'Enable hiding',
                'type'          => 'checkbox',
                'section'       => 'hfl_settings_section',
                'settings'      => 'hfl_options[hide-enabled]',
            )
        );

        if (!array_key_exists($this->theme_stylesheet, $this->themes)) {
            $wp_customize->add_control(
                'hfl_hide_css_control',
                array(
                    'label'         => 'CSS selector to hide',
                    'description'   => 'CSS selector that contains copyright info.',
                    'section'       => 'hfl_settings_section',
                    'settings'      => 'hfl_options[hide-selector]',
                    'type'          => 'text',
                )
            );
            $wp_customize->add_control(
                'hfl_use_important_control',
                array(
                    'label'         => 'Use !important when hiding',
                    'description'   => 'Force hiding by using !important in CSS.',
                    'section'       => 'hfl_settings_section',
                    'settings'      => 'hfl_options[use-important]',
                    'type'          => 'checkbox',
                )
            );
        }
    }

    /**
     * @codeCoverageIgnore
     */
    function customizeCSS() {
        $opts = get_option('hfl_options');
        $hide = $opts['hide-enabled'];
        $use_important = '';
        if (array_key_exists($this->theme_stylesheet, $this->themes)) {
            $css_selector = $this->themes[$this->theme_stylesheet][0];
            if ($this->themes[$this->theme_stylesheet][1] === true) {
                $use_important = '!important';
            }
        } else {
          $css_selector = $opts['hide-selector'];
          if ($opts['use-important'] == 1) {
              $use_important = '!important';
          }
        }

        if ($hide):
            ?>
                <!-- Footprint Customizer -->
                <style type="text/css">
                    <?php echo $css_selector; ?> { visibility: hidden <?php echo $use_important; ?>; }
                </style>
                <!-- Footprint Customizer -->
            <?php
        endif;
    }


    /**
     * @codeCoverageIgnore
     * @return string
     */
    private function pluginURL()
    {
        $url = wp_make_link_relative(plugin_dir_url(__FILE__));
        $url = ltrim($url, "/");
        return $url;
    }

    /**
     * @codeCoverageIgnore
     */
    function activatePluginNotice()
    {
        if (get_option('nfl_activation') != 'EXTERMINATE') { // Let's display the activation message only once
            add_option('nfl_activation', 'EXTERMINATE');
            ?>
            <div class="notice notice-success">
                <p>Hide Footer Links plugin has been enabled.</p>
            </div>
        <?php }
    }

    /**
     * @codeCoverageIgnore
     */
    function deactivatePlugin()
    {
        delete_option('nfl_activation');
    }

    /**
     * @codeCoverageIgnore
     */
    function activatePlugin()
    {
    }
}

// Inside WordPress
if (defined('ABSPATH')) {
    $NiteowebFooterLinks_ins = new FooterLinks;
    register_activation_hook(__FILE__, array(&$NiteowebFooterLinks_ins, 'activatePlugin'));
    register_deactivation_hook(__FILE__, array(&$NiteowebFooterLinks_ins, 'removePlugin'));
}
