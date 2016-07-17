<?php

namespace Pages\Pagesbundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstraintsCheckUrlValidator extends ConstraintValidator {
    #code
    
    public function Validate($value, constraint $constraint)
    {
        if(1 != 0) $this->context->addViolation($constraint->message);
    }
    
    
}



?>