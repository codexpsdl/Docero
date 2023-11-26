<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}

require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

redirect_if_major_upgrade_required();

$urlparams = array();
if (!empty($CFG->defaulthomepage) &&
        ($CFG->defaulthomepage == HOMEPAGE_MY || $CFG->defaulthomepage == HOMEPAGE_MYCOURSES) &&
        optional_param('redirect', 1, PARAM_BOOL) === 0
) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_pagelayout('frontpage');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

require_course_login($SITE);

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// If site registration needs updating, redirect.
\core\hub\registration::registration_reminder('/index.php');

if (get_home_page() != HOMEPAGE_SITE) {
    // Redirect logged-in users to My Moodle overview if required.
    $redirect = optional_param('redirect', 1, PARAM_BOOL);
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        // At this point, dashboard is enabled so we don't need to check for it (otherwise, get_home_page() won't return it).
        redirect($CFG->wwwroot .'/my/');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MYCOURSES) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/courses.php');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

// Trigger event.
course_view(context_course::instance(SITEID));

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$editing = $PAGE->user_is_editing();
$PAGE->set_title(get_string('home'));
$PAGE->set_secondary_active_tab('coursehome');

$courserenderer = $PAGE->get_renderer('core', 'course');

if ($hassiteconfig) {
    $editurl = new moodle_url('/course/view.php', ['id' => SITEID, 'sesskey' => sesskey()]);
    $editbutton = $OUTPUT->edit_button($editurl);
    $PAGE->set_button($editbutton);
}

echo $OUTPUT->header();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <style>
        /* Start Global rules */
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        
        /* Start body rules */
       
        
        /* Start form attributes */
        form {
            width: 450px;
            min-height: 460px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 9px 50px hsl(218deg 51% 90%);
            padding: 2%;
            background-color: hsl(231deg 62% 94%);
            justify-content: center;
            align-items: center;
        }
        
        /* form Container */
        form .con {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 auto;
        }
        
        /* the header form form */
        header {
            margin: 2% auto 10% auto;
            text-align: center;
            margin-top: 8%;
        }
        
        /* Login title form form */
        header h2 {
            font-size: 250%;
            font-family: 'Playfair Display', serif;
            color: #3e403f;
            
        }
        
        .input-item {
            background: #fff;
            color: #333;
            padding: 14.5px 0px 15px 9px;
            border-radius: 5px 0px 0px 5px;
        }
        
        /* inputs form */
        input[class="form-input"] {
            width: 250px;
            height: 50px;
            margin-top: 4%;
            padding: 15px;
            font-size: 16px;
            font-family: 'Abel', sans-serif;
            color: #5E6472;
            outline: none;
            border: none;
            border-radius: 5px; /* Change this to 5px to make the password input line the same length */
            transition: 0.2s linear;
        }
        
        input[id="txt-input"] {
            width: 250px;
        }
        
        /* focus */
        input:focus {
            transform: translateX(-2px);
            border-radius: 5px;
        }
        
        /* buttons */
        button {
            color: #fff;
            width: 100%;
            height: 50px;
            padding: 0 20px;
            border-radius: 5px;
            outline: none;
            border: none;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s linear;
            margin: 7% auto;
            letter-spacing: 0.05em;
        }
        
        /* Submits */
        .submits {
            width: 100%;
        }

        /* LogIn button */
        .log-in {
            background: hsl(233deg 36% 38%);
        }

        /* Move "Wachtwoord vergeten" button */
        .frgt-pass {
            background: none;
            border: none;
            color: #5E6472;
            cursor: pointer;
            margin: 0;
            padding: 0;
            text-decoration: underline;
            text-align: left;
        }


        /* buttons hover */
        button:hover {
            transform: translateY(3px);
            box-shadow: none;
        }
        
        /* buttons hover Animation */
        button:hover {
            animation: ani9 0.4s ease-in-out infinite alternate;
        }
        
        @keyframes ani9 {
            0% {
                transform: translateY(3px);
            }
            100% {
                transform: translateY(5px);
            }
        }

    </style>
</head>
<body>
    <div class="overlay">
        <form>       
            <div class="con">                
                <header class="head-form">
                    <h2>Docero</h2>                                 
                </header>
                <br>
                <div class="field-set">
                    <!-- User name -->
                    <span class="input-item">
                        <i class="fa fa-user-circle"></i>
                    </span>
                    <!-- User name Input -->
                    <input class="form-input" id="txt-input" type="text" placeholder="Email" required>
                    <br>
                    <!-- Password -->
                    <span class="input-item">
                        <i class="fa fa-key"></i>
                    </span>
                    <!-- Password Input -->
                    <input class="form-input" type="password" placeholder="Wachtwoord" id="pwd" name="password" required>
                    <br>
                    <!-- Buttons -->
                    <!-- LogIn button -->
                    <button class="log-in"> inloggen </button>
                    <!-- Forgot Password button -->
                    <button class="btn submits frgt-pass">Wachtwoord vergeten</button>
                </div>
                <!-- End Container -->
            </div>
            <!-- End Form -->
        </form>
    </div>
</body>
</html>

<?php
$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnamesused = $modinfo->get_used_module_names();

// Print Section or custom info.
if (!empty($CFG->customfrontpageinclude)) {
    // Pre-fill some variables that custom front page might use.
    $modnames = get_module_types_names();
    $modnamesplural = get_module_types_names(true);
    $mods = $modinfo->get_cms();

    include($CFG->customfrontpageinclude);

} else if ($siteformatoptions['numsections'] > 0) {
    echo $courserenderer->frontpage_section1();
}
// Include course AJAX.
include_course_ajax($SITE, $modnamesused);

echo $courserenderer->frontpage();

if ($editing && has_capability('moodle/course:create', context_system::instance())) {
    echo $courserenderer->add_new_course_button();
}
echo $OUTPUT->footer();
