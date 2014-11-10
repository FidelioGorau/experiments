<?php
namespace BugTracker\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

/**
 * Class BugForm
 * @package BugTracker\Form
 */
class BugForm extends Form
{
    protected $userList;

    public function __construct(array $users)
    {
        $this->userList = $users;
        parent::__construct('bugpost');
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new \BugTracker\Form\BugInputFilter());
        $this->add(array(
            'name' => 'security',
            'type' => 'Zend\Form\Element\Csrf',
        ));
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'created',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'userId',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'options' => array(
                'min' => 3,
                'max' => 25,
                'label' => 'Title',
            ),
        ));
        $this->add(array(
            'name' => 'userId',
            'type' => 'select',
            'options' => array(
                'label' => 'Asign to:',
                'options'=>$this->userList
            ),
        ));
        $this->add(array(
            'name' => 'text',
            'type' => 'Textarea',
            'options' => array(
                'label' => 'Text',
            ),
        ));
        $this->add(array(
            'name' => 'state',
            'type' => 'select',
            'options' => array(
                'label' => 'status:',
                'options'=>array(
                    '1'=>'active',
                    '2'=>'resolved',
                    '3'=>'closed'
                )
            ),

        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }

}
