<?php

class block_extsearch_edit_form extends block_edit_form {

    /**
     * Adds form fields specific to this block
     *
     * @param moodleform $mform
     */
    protected function specific_definition($mform){
        global $CFG;

        // Figure out the options for the search provider dropdown menu
        $options = array();
        $options['google'] = get_string('google', 'block_extsearch');
        $default = 'google';

        $digitalnzapikey = get_config(NULL, 'block_extsearch_digitalnz_api_key');
        if (!empty($digitalnzapikey)) {
            $options['digitalnz'] = get_string('digitalnz', 'block_extsearch');
            $options['edna'] = get_string('edna', 'block_extsearch');
            $default = 'digitalnz';
        }

        $mform->addElement('select', 'config_search_provider', get_string('searchprovider_label','block_extsearch'), $options);
        $mform->setDefault('config_search_provider', $default);

        // Figure out the options for the Google SafeSearch dropdown menu
        $safesearch = array();
        $safesearch['active'] = get_string('googlesafesearch_active', 'block_extsearch');
        $safesearch['moderate'] = get_string('googlesafesearch_moderate', 'block_extsearch');
        $safesearch['off'] = get_string('googlesafesearch_off', 'block_extsearch');
        $mform->addElement('select', 'config_google_safesearch', get_string('googlesafesearch_label','block_extsearch'), $safesearch);
        $mform->setDefault('config_google_safesearch','moderate');

        $mform->addElement('checkbox', 'config_popup_links', get_string('popuplinks', 'block_extsearch'));

        $mform->addElement('html', '<i>'.get_string('noteaboutsitewideconfig', 'block_extsearch', $CFG->wwwroot.'/admin/settings.php?section=blocksettingextsearch').'<i>');
    }

}