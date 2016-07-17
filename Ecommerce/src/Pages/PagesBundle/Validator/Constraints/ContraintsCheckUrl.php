<?php

namespace Pages\Pagesbundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 *@Annotation
 */

class ConstraintsCheckUrl extends Constraint {
    #code
    
    public $message = "le champ contient des liens non valides";
    
    public function validatedBy()
    {
        return 'validatorCheckUrl';
    }
}

