<?php

//$ADMIN->add('localplugins', new admin_externalpage('nzmoodlesettings', get_string('nzschoolssettings', 'local_nzschools'), "$CFG->wwwroot/local/nzschools/settings_page.php"));
$ADMIN->add('root', new admin_externalpage('nzschoolssettings', get_string('nzschoolssettings', 'local_nzschools'), "$CFG->wwwroot/local/nzschools/settings_page.php"));
