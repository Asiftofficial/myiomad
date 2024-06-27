<?php


require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/local/email/lib.php');

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$companyid = optional_param('companyid', 0, PARAM_INTEGER);
$courses = optional_param_array('courses', array(), PARAM_INTEGER);
$departmentid = optional_param('deptid', 0, PARAM_INTEGER);
$selectedcourses = optional_param_array('selectedcourses', array('-1'), PARAM_INTEGER);
//mendapatkan nilai param selectedrole
$selectedrole = optional_param('selectedrole', 0, PARAM_INTEGER);
$groupid = optional_param('groupid', 0, PARAM_INTEGER);

if (empty($courses) && !empty($selectedcourses)) {
    $courses = $selectedcourses;
}

$context = context_system::instance();
require_login();

$params = array('companyid' => $companyid,
                'courses' => $courses,
                'deptid' => $departmentid,
                'selectedcourses' => $selectedcourses,
                'rid' => $selectedrole,
                'groupid' => $groupid);

$urlparams = array('companyid' => $companyid, 'rid' => $selectedrole);
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
if (!empty($courses)) {
    foreach ($courses as $a => $b)
    $urlparams["courses[$a]"] = $b;
}
if (!empty($selectedcourses)) {
    foreach ($selectedcourses as $a => $b)
    $urlparams["selectedcourses[$a]"] = $b;
}

$linktext = get_string('company_course_users_title', 'block_iomad_company_admin');

$linkurl = new moodle_url('/blocks/iomad_company_admin/company_course_users_form.php');


$PAGE->set_context($context);
$PAGE->set_url($linkurl);
$PAGE->set_pagelayout('base');
$PAGE->set_title($linktext);

$PAGE->set_heading($linktext);


$output = $PAGE->get_renderer('block_iomad_company_admin');


$PAGE->requires->js_call_amd('block_iomad_company_admin/department_select', 'init', array('deptid', 1, optional_param('deptid', 0, PARAM_INT)));
$PAGE->navbar->add($linktext, $linkurl);

require_login(null, false);
iomad::require_capability('block/iomad_company_admin:company_course_users', $context);


$companyid = iomad::get_my_companyid($context);
$parentlevel = company::get_company_parentnode($companyid);
$companydepartment = $parentlevel->id;
$syscontext = context_system::instance();
$company = new company($companyid);

$coursesform = new \block_iomad_company_admin\forms\company_ccu_courses_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourses, $parentlevel);
$coursesform->set_data(array('selectedcourses' => $selectedcourses, 'courses' => $courses));
//passing nilai selectedrole ketika pembuatan object company_course_users_form
$usersform = new \block_iomad_company_admin\forms\company_course_users_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourses, $selectedrole);




if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
    throw new moodle_exception('invaliddepartment', 'block_iomad_company_admin');
}

if ($coursesform->is_cancelled() || $usersform->is_cancelled() ||
     optional_param('cancel', false, PARAM_BOOL) ) {
    if ($returnurl) {
        redirect($returnurl);
    } else {
        redirect(new moodle_url($CFG->wwwroot .'/blocks/iomad_company_admin/index.php'));
    }
} else {
    echo $output->header();

 
    echo $output->display_tree_selector($company, $parentlevel, $linkurl, $urlparams, $departmentid);

    echo html_writer::start_tag('div', array('class' => 'iomadclear'));
    if ($companyid > 0) {
        $coursesform->set_data($params);
        echo $coursesform->display();
        if (!in_array('-1', $selectedcourses, true)) {
            if ($data = $coursesform->get_data() || empty($selectedcourses)) {
                 if (count($courses) > 0) {
                    $usersform->set_course(array($courses));
                    $usersform->process();
                    $usersform = new \block_iomad_company_admin\forms\company_course_users_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourses,$selectedrole );
                    $usersform->set_course(array($courses));
                    $usersform->set_data(array('groupid' => $groupid, 'rid' => $selectedrole));
                    
                } else if (!empty($selectedcourses)) {
                    $usersform->set_course($selectedcourses);
                }
                echo $usersform->display();
            } else if (count($courses) > 0) {
                $usersform->set_course(array($courses));
                $usersform->process();
                $usersform = new \block_iomad_company_admin\forms\company_course_users_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourses, $selectedrole);
                $usersform->set_course(array($courses));
                $usersform->set_data(array('groupid' => $groupid,  'rid' => $selectedrole));
                
                echo $usersform->display();
            }
        }
    }
    echo html_writer::end_tag('div');
    echo $output->footer();
}
