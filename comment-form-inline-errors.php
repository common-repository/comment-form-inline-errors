<?php
/*
    Plugin Name: Comment Form Inline Errors
    Plugin URI: http://latorante.name
    Description: Shows comment form errors nicely above the form instead of redirecting to wordpress error page. It also remembers post fields and fills them in so you don't have to retype all the fields again after comment error occurs.
    Author: latorante
    Author URI: http://latorante.name
    Author Email: martin@latorante.name
    Version: 1.0.2
    License: GPLv2
*/
/*
    Copyright 2013  Martin Picha  (email : martin@latorante.name)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) { exit; }

if (!class_exists('wpCommentFormInlineErrors')){
    class wpCommentFormInlineErrors
    {
        /* minimum required wp version */
        public $wpVer = "3.0";
        /* minimum required php version */
        public $phpVer = "5.3";

        public function __construct() { add_action('init', array($this, 'init')); }

        /**
         * Hook me up, buttercup
         */

        public function init()
        {
            if(!$this->checkRequirements()){ return; }
            session_start();
            /* all these hooks are in wp since version 3.0, that's where we aim. */
            add_filter('wp_die_handler', array($this, 'getWpDieHandler'));
            add_action('comment_form_before_fields', array($this, 'displayFormError'));
            add_action('comment_form_logged_in_after', array($this, 'displayFormError'));
            add_filter('comment_form_default_fields',array($this, 'formDefaults'));
            add_filter('comment_form_field_comment',array($this, 'formCommentDefault'));
        }


        /**
         * Let's check Wordpress version, and PHP version and tell those
         * guys whats needed to upgrade, if anything.
         *
         * @return bool
         */

        private function checkRequirements()
        {
            global $wp_version;
            if (!version_compare($wp_version, $this->wpVer, '>=')){
                $this->pluginDeactivate();
                add_action('admin_notices', array($this, 'displayVersionNotice'));
                return FALSE;
            } elseif (!version_compare(PHP_VERSION, $this->phpVer, '>=')){
                $this->pluginDeactivate();
                add_action('admin_notices', array($this, 'displayPHPNotice'));
                return FALSE;
            }
            return TRUE;
        }


        /**
         * Deactivates our plugin if anything goes wrong. Also, removes the
         * "Plugin activated" message, if we don't pass requriments check.
         */

        private function pluginDeactivate()
        {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            deactivate_plugins(plugin_basename(__FILE__));
            unset($_GET['activate']);
        }


        /**
         * Displays outdated wordpress messsage.
         */

        public function displayVersionNotice()
        {
            global $wp_version;
            $this->displayAdminError(
                'Sorry mate, this plugin requires at least WordPress varsion ' . $this->wpVer . ' or higher.
                You are currently using ' . $wp_version . '. Please upgrade your WordPress.');
        }


        /**
         * Displays outdated php message.
         */

        public function displayPHPNotice()
        {
            $this->displayAdminError(
                'You need PHP version at least '. $this->phpVer .' to run this plugin. You are currently using PHP version ' . PHP_VERSION . '.');
        }


        /**
         * Admin error helper
         *
         * @param $error
         */

        private function displayAdminError($error) { echo '<div id="message" class="error"><p><strong>' . $error . '</strong></p></div>';  }


        /************************ Let's do this. ************************/

        /**
         * Overwrites wordpress error handeling.
         *
         * @param $handler
         * @return array
         */

        function getWpDieHandler($handler){ return array($this, 'handleWpError'); }


        /**
         * Now this sounds great does it not? :) After refresh, we can
         * display that message. Easy peasy my man. Of course, only if
         * it's not admin error.
         *
         * @param $message
         * @param string $title
         * @param array $args
         */

        function handleWpError($message, $title='', $args=array())
        {
            // this is simple, if it's not admin error, and we simply continue
            // and sort it our way. Meaning, send errors to form itself and display them thru $_SESSION.
            // and yes, we test if comment id is present, not sure how else to test if commenting featured is being used :)
            if(!is_admin() && !empty($_POST['comment_post_ID']) && is_numeric($_POST['comment_post_ID'])){
                $_SESSION['formError'] = $message;
                // let's save those form fields in session as well hey? bit annoying
                // filling everything again and again. might work
                $denied = array('submit', 'comment_post_ID', 'comment_parent');
                foreach($_POST as $key => $value){
                    if(!in_array($key, $denied)){
                        $_SESSION['formFields'][$key] = stripslashes($value);
                    }
                }
                // write, redirect, go
                session_write_close();
                wp_safe_redirect(get_permalink($_POST['comment_post_ID']) . '#formError', 302);
                exit;
            } else {
                _default_wp_die_handler($message, $title, $args);   // this is for the other errors
            }
        }


        /**
         * Display inline form error.
         */

        public function displayFormError()
        {
            $formError = $_SESSION['formError'];
            unset($_SESSION['formError']);
            if(!empty($formError)){
                echo '<div id="formError" class="formError" style="color:red;">';
                echo '<p>'. $formError .'</p>';
                echo '</div><div class="clear clearfix"></div>';
            }
        }


        /**
         * Reset form defaults to value sent, it's nice when form remebers
         * stuff and doesn't force you to fill in shit again and again.
         *
         * @param $fields
         * @return mixed
         */

        function formDefaults($fields)
        {
            $formFields = $_SESSION['formFields'];
            foreach($fields as $key => $field){
                if($this->stringContains('input', $field)){
                    if($this->stringContains('type="text"', $field)){
                        $fields[$key] = str_replace('value=""', 'value="'. stripslashes($formFields[$key]) .'"', $field);
                    }
                } elseif ($this->stringContains('</textarea>', $field)){
                    $fields[$key] = str_replace('</textarea>', stripslashes($formFields[$key]) .'</textarea>', $field);
                }
            }
            return $fields;
        }


        /**
         * Of course comment field is special :) needs special
         * hook for defaults.
         *
         * @param $comment_field
         * @return mixed
         */

        function formCommentDefault($comment_field)
        {
            $formFields = $_SESSION['formFields'];
            unset($_SESSION['formFields']);
            return str_replace('</textarea>', $formFields['comment'] . '</textarea>', $comment_field);
        }


        /**
         * Just little helper for filling the form again.
         *
         * @param $haystack
         * @param $needle
         * @return bool
         */

        public function stringContains($needle, $haystack){ return strpos($haystack, $needle) !== FALSE; }

    }

}

new wpCommentFormInlineErrors();