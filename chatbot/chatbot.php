<?php
//test
/**
 * Moodle file tree viewer based on YUI2 Treeview
 *
 * @package    core
 * @subpackage file
 * @copyright  2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');

require_login($course, false, $cm);

$PAGE->set_title("$course->shortname: $strfiles");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

$output = $PAGE->get_renderer('core', 'files');

echo $output->header();
echo $output->box_start();

?>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="chatbot.css">
    <link rel="icon" href="icons/windesheim.png" type="image/png">
</head>
<body>
     <h1>Welkom bij de AI chatbot</h1>
   
</body>
</html>

<?php
echo $output->box_end();
echo $output->footer();
?>

