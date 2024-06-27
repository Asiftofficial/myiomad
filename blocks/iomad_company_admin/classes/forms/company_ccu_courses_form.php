<?php

namespace block_iomad_company_admin\forms;

use \company_moodleform;
use \company;

class company_ccu_courses_form extends company_moodleform {
    protected $context = null;
    protected $selectedcompany = 0;
    protected $potentialcourses = null;
    protected $currentcourses = null;
    protected $departmentid = 0;
    protected $subhierarchieslist = null;
    protected $companydepartment = 0;
    protected $selectedcourses = 0;
    protected $company = null;
    protected $courses = array();
    protected $companycourses = array();
    //menambahkan variable roles untuk menampung list of role
    protected $roles = array();


    public function __construct($actionurl, $context, $companyid, $departmentid, $selectedcourses, $parentlevel) {
        global $DB, $USER;
        $this->selectedcompany = $companyid;
        $this->company = new company($companyid);
        $this->context = $context;
        $this->departmentid = $departmentid;
        $this->selectedcourses = $selectedcourses;

        $options = array('context' => $this->context,
                         'multiselect' => false,
                         'companyid' => $this->selectedcompany,
                         'departmentid' => $departmentid,
                         'subdepartments' => $this->subhierarchieslist,
                         'parentdepartmentid' => $parentlevel,
                         'licenses' => false,
                         'shared' => false);
        $this->companycourses = $this->company->get_menu_courses(true, true);
        $this->companycourses[0] = get_string('all');

        //start: inisialisasi nilai role 
        $this->roles[0] = get_string('none');
        $this->roles[5] = get_string('role_student', 'block_iomad_company_admin');
        $this->roles[3] = get_string('role_teacher', 'block_iomad_company_admin');
        $this->roles[4] = get_string('role_non_editing_teacher', 'block_iomad_company_admin');
        //end: inisialisasi nilai role
        
       

        parent::__construct($actionurl);
    }


    public function definition() {
        $this->_form->addElement('hidden', 'companyid', $this->selectedcompany);
        $this->_form->setType('companyid', PARAM_INT);
        $this->_form->addElement('hidden', 'deptid', $this->departmentid);
        $this->_form->setType('deptid', PARAM_INT);
    }


    public function definition_after_data() {
        $mform =& $this->_form;
       
        if ($this->companycourses) {

            $autooptions = array('multiple' => true,
                                 'noselectionstring' => get_string('none'),
                                 'onchange' => 'this.form.submit()');
            $mform->addElement('autocomplete', 'selectedcourses', get_string('selectenrolmentcourse', 'block_iomad_company_admin'), $this->companycourses, $autooptions);

            //start: menambahkan element select role ke form untuk ditampilkan ke UI
            $roleoptions = array(
                'onchange' => 'this.form.submit()');
            $mform->addElement('select', 'selectedrole', get_string('select_role', 'block_iomad_company_admin'), 
            $this->roles,
            $roleoptions
            );
            //end: menambahkan element select role ke form untuk ditampilkan ke UI
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">' . get_string('nocourses', 'block_iomad_company_admin') . '</div>');
        }


        $mform->disable_form_change_checker();
    }
}