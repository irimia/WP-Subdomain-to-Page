<?php
/**
Plugin Name: WP Subdomains to Pages
Version: v1.0.0
Plugin URI: 
Author: Irimia Suleapa
Author URI: http://irimia.suleapa.name
Description: Setup your pages as subdomains.
**/

define("WP_RED_FILE", __FILE__);

if (!class_exists('wp_redirect_subdomain')):
    class wp_redirect_subdomain {

        var $wpredirect_subdomain;

        function __construct() {
            $this->wpredirect_subdomain = false;

            add_action('init', array(&$this, 'wp_redirect_subdomain'));
            add_action('template_redirect', array(&$this, 'wp_check_redirect'));
        }

        function wp_redirect_subdomain() {
            $root = get_option('siteurl');
            $root = str_replace(array("http://", "https://", "www."), "", $root);
            
            $httphost = strtolower($_SERVER['HTTP_HOST']);
            $httphost = str_replace(array("http://", "https://", "www."), "", $httphost);
            
            if(strstr($httphost, "/") !== false) {
                $httphost = explode("/", $httphost, 2);
                $httphost = $httphost[0];
            }

            if(strstr($httphost, "." . $root) !== false)
                $httphost = str_replace("." . $root, "", $httphost);
            
            if(strcmp($httphost, $root) == 0)
                return;
            
            $_SERVER['REQUEST_URI'] = "/" . $httphost . $_SERVER['REQUEST_URI'];

            $this->wpredirect_subdomain = true;
            return;
        }

        function wp_check_redirect() {
            global $wp_query;

            $custom_keys = get_post_custom($wp_query->post->ID);     
            $is_sub = (isset($custom_keys['makesubdomain'][0]) && $custom_keys['makesubdomain'][0] == 1) ? 1 : 0;

            $root = get_option('siteurl');
            $root = str_replace(array("http://", "https://", "www."), "", $root);

            $pagename = $wp_query->query['pagename'];
            $pagenames = explode("/", $pagename, 2);
            $pagenames[1] = ( $pagenames[1] ? "/" . trim($pagenames[1], "/") . "/" : "" );
            
            $url = explode('.', $_SERVER['SERVER_NAME']);
                    
            if(is_page() && !is_404() && $this->wpredirect_subdomain == false) {
                $final = "http://" . $pagenames[0] . "." . $root . $pagenames[1];
                if($custom_keys['makesubdomain'][0] == 1) {
                    wp_redirect($final, 301);
                    exit;
                }
            }
        }
    }
endif;

global $wp_redirect_subdomain;

if (empty($wp_redirect_subdomain))
    $wp_redirect_subdomain = & new wp_redirect_subdomain();
