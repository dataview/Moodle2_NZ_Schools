<?php

/**
 * Settings for the formal_white theme
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Background colour setting
    $name = 'theme_formal_white/backgroundcolor';
    $title = get_string('backgroundcolor','theme_formal_white');
    $description = get_string('backgroundcolordesc', 'theme_formal_white');
    $default = '#F7F6F1';
    $previewconfig = array('selector'=>'.block .content', 'style'=>'backgroundColor');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Logo file setting
    $name = 'theme_formal_white/logo';
    $title = get_string('logo','theme_formal_white');
    $description = get_string('logodesc', 'theme_formal_white');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Block region width
    $name = 'theme_formal_white/regionwidth';
    $title = get_string('regionwidth','theme_formal_white');
    $description = get_string('regionwidthdesc', 'theme_formal_white');
    $default = 200;
    $choices = array(150=>'150px', 170=>'170px', 200=>'200px', 240=>'240px', 290=>'290px', 350=>'350px', 420=>'420px');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // alwayslangmenu setting
    $name = 'theme_formal_white/alwayslangmenu';
    $title = get_string('alwayslangmenu','theme_formal_white');
    $description = get_string('alwayslangmenudesc', 'theme_formal_white');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $settings->add($setting);

    // Foot note setting
    $name = 'theme_formal_white/footnote';
    $title = get_string('footnote','theme_formal_white');
    $description = get_string('footnotedesc', 'theme_formal_white');
    $setting = new admin_setting_confightmleditor($name, $title, $description, '');
    $settings->add($setting);

    // Custom CSS file
    $name = 'theme_formal_white/customcss';
    $title = get_string('customcss','theme_formal_white');
    $description = get_string('customcssdesc', 'theme_formal_white');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $settings->add($setting);
}