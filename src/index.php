<?php

namespace Niteoweb\HideFooterLinks;

/**
 * Plugin Name: Hide Footer Links
 * Description: Hide Footer Links will try to remove site info and copyright info from your footer.
 * Version:     1.0.3
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
    const OPTION_NAME = 'Niteoweb.FooterLinks.Themes';
    const NONCE = 'Niteoweb.FooterLinks.Nonce';
    const CHECK_HOOK = 'Niteoweb.FooterLinks.CheckHook';

    // Array of themes and class name to hide. Maybe extend this to hold an
    // array of classes or an override function for more complex solutions.
    private $themes = array(
        'accelerate' => array('.footer-socket-wrapper .copyright', false),
        'accesspress-root' => array('div.copyright', false),
        'adelle' => array('p.footer-copy', false),
        'aldehyde' => array('#colophon .site-info', false),
        'astrid' => array('div.site-copyright', false),
        'avenue' => array('#colophon .site-info .row > div:last-child', false),
        'base-wp' => array('#colophon .site-info', false),
        'basic' => array('div.themeby', false),
        // 'brightnews' => array('', false),   // No copyright info
        'bwater' => array('#colophon #site-generator', false),
        'chinese-restaurant' => array('section#kt-copyright', false),
        'colormag' => array('footer .copyright', false),
        'contango' => array('#colophon .site-info, #colophon .copyright, #colophon .credit', true),
        'coraline' => array('#colophon', false),
        'decode' => array('#colophon > div', false),
        'displace' => array('#colophon > div', false),
        'duster' => array('#site-generator', false),
        'dw-minion' => array('#colophon > div > div', false),
        'maxstore' => array('#colophon.rsrc-footer', false),
        'editor' => array('#colophon > div', false),
        'elucidate' => array('#colophon > div', false),
        'enough' => array('#enough-page > footer > address small', false),
        'esquire' => array('#credit > p', false),
        'esteem' => array('#site-generator > div', false),
        'exray' => array('#footer-container > footer > .copyright-container> div > p:nth-child(2)', false),
        'fictive' => array('#colophon > div', false),
        'flat' => array('#primary > footer', true),
        'flaton' => array('#colophon > div > div > div > div', false),
        'forever' => array('#site-info', false),
        'govpress' => array('#page > footer > div', false),
        'graphy' => array('#colophon .site-credit', false),
        'iconic-one' => array('div.site-wordpress', false),
        'illdy' => array('footer > div > div > div:last-child', false),
        'isola' => array('#colophon > div', false),
        'minimize' => array('#footer > p', false),
        'modernize' => array('.footer__info', false),
        'monaco' => array('#colophon > div.site-info', false),
        'motif' => array('#colophon > div', false),
        'olsen-light' => array('#footer > div p.tagline', false),
        'omega' => array('footer .credit', true),
        'onepress' => array('#colophon .site-info .container', false),
        'origami' => array('#footer > div.designed', false),
        'padhang' => array('#colophon > div > div.powered', false),
        'parament' => array('#site-generator', false),
        'pilcrow' => array('#site-generator', false),
        'pink-touch-2' => array('#footer > div > p', false),
        'prana' => array('#footer .grid_credit', false),
        'quark' => array('#footercontainer div.smallprint p', true),
        'responsivo' => array('#footercontainer .row.smallprint  p', false),
        // 'restimpo' => array('', false), // No copyright info
        'shop-isle' => array('p.shop-isle-poweredby-box', false),
        'spacious' => array('#colophon .copyright', false),
        'sparkling' => array('.site-info.container .copyright', false),
        'startup' => array('#copyright', false),
        'storefront' => array('#colophon div.site-info', false),
        'sydney' => array('#colophon .site-info', false),
        'suffusion' => array('#cred > table > tbody > tr > td.cred-right', false),
        'sugar-and-spice' => array('#footer > div', false),
        'syntax' => array('#colophon > div', false),
        // 'thematic' => array('', false),  // No copyright info
        'twentyeleven' => array('#colophon #site-generator', false),
        'total'  => array('#ht-colophon .ht-site-info', false),
        'twentyfourteen' => array('#colophon > div', false),
        'twentysixteen' => array('.site-info', false),
        'twentyten' => array('#colophon', false),
        'twentythirteen' => array('#colophon > div.site-info', false),
        'twentyseventeen' => array('#colophon > div > div > a', false),
        'twentytwelve' => array('#colophon', false),
        'vega' => array('div.footer .copyright', false),
        'unite' => array('#colophon > div > div > div', false),
        'wilson' => array('body > div > div.content > div.credits', false),
        'zbench' => array('#footer-inside > p', false)
    );

    public function __construct()
    {
        add_action('generate_rewrite_rules', array(&$this, 'generateRewriteRules'));
        add_action('init', array(&$this, 'wpAdminInit'));
    }

    public function wpAdminInit()
    {
        if (current_user_can('edit_theme_options')) {
            add_action('customize_register', array(&$this, 'customizeRegister'));
        }

        add_action('wp_head', array(&$this, 'customizeCSS'));
    }

    public function generateRewriteRules($wp_rewrite)
    {
        // Protect plugin from direct access
        $wp_rewrite->add_external_rule($this->pluginURL() . 'index.php', 'index.php%{REQUEST_URI}');
        $wp_rewrite->add_external_rule($this->pluginURL() . 'readme.txt', 'index.php%{REQUEST_URI}');
        $wp_rewrite->add_external_rule($this->pluginURL(), 'index.php%{REQUEST_URI}');
    }


    public function customizeRegister($wp_customize)
    {
        $wp_customize->add_section(
            'hfl_settings_section',
            array(
                'title' => __('Footer Links Settings', 'hide-footer-links'),
                'description' => __('Enables hiding of footer copyright info by searching your theme or giving you an option to specify a custom CSS selector.', 'hide-footer-links'),
                'priority' => 430,
            )
        );

        $wp_customize->add_setting(
            'hfl_options[hide-enabled]',
            array(
                'default' => '',
                'type' => 'option',
                'transport' => 'refresh',
            )
        );

        $wp_customize->add_setting(
            'hfl_options[hide-selector]',
            array(
                'default' => '',
                'type' => 'option',
                'transport' => 'refresh',
            )
        );

        $wp_customize->add_setting(
            'hfl_options[use-important]',
            array(
                'default' => false,
                'type' => 'option',
                'transport' => 'refresh',
            )
        );

        $wp_customize->add_control(
            'hfl_enable_hide_control',
            array(
                'label' => __('Enable hiding', 'hide-footer-links'),
                'type' => 'checkbox',
                'section' => 'hfl_settings_section',
                'settings' => 'hfl_options[hide-enabled]',
            )
        );

        $theme_stylesheet = wp_get_theme()->get_template();

        if (!array_key_exists($theme_stylesheet, $this->themes)) {
            $wp_customize->add_control(
                'hfl_hide_css_control',
                array(
                    'label' => __('CSS selector to hide', 'hide-footer-links'),
                    'description' => __('CSS selector that contains copyright info. <a href="https://blog.easyblognetworks.com/2017/free-hide-footer-links-plugin/">Help</a> ', 'hide-footer-links'),
                    'section' => 'hfl_settings_section',
                    'settings' => 'hfl_options[hide-selector]',
                    'type' => 'text',
                )
            );
            $wp_customize->add_control(
                'hfl_use_important_control',
                array(
                    'label' => __('Use !important when hiding', 'hide-footer-links'),
                    'description' => __('Force hiding by using !important in CSS.', 'hide-footer-link'),
                    'section' => 'hfl_settings_section',
                    'settings' => 'hfl_options[use-important]',
                    'type' => 'checkbox',
                )
            );
        }
    }


    public function customizeCSS()
    {
        $opts = get_option('hfl_options', array('hide-enabled' => true));
        $hide = $opts['hide-enabled'];
        $use_important = '';
        $theme_stylesheet = wp_get_theme()->get_template();

        if (array_key_exists($theme_stylesheet, $this->themes)) {
            $css_selector = $this->themes[$theme_stylesheet][0];
            if ($this->themes[$theme_stylesheet][1] === true) {
                $use_important = '!important';
            }
        } else {
            $css_selector = $opts['hide-selector'];
            if ($opts['use-important'] == 1) {
                $use_important = '!important';
            }
        }

        if ($hide) {
            ?>
            <style type="text/css">
                <?php echo $css_selector; ?>
                {
                    visibility: hidden
                <?php echo $use_important; ?>
                ;
                }
            </style>
            <?php
        }
    }


    /**
     * @return string
     */
    private function pluginURL()
    {
        $url = wp_make_link_relative(plugin_dir_url(__FILE__));
        $url = ltrim($url, "/");
        return $url;
    }
}

// Inside WordPress
if (defined('ABSPATH')) {
    new FooterLinks;
}

