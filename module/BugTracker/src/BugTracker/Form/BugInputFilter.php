<?php

namespace BugTracker\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class BugInputFilter extends InputFilter{
    public function __construct()
    {
        $this->add(array(
            'name' => 'title',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 3,
                        'max' => 100,
                    ),
                ),
            ),
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        return true;
    }

}