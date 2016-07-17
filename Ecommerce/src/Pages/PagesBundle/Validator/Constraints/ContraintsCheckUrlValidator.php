<?php

namespace Pages\Pagesbundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Pages\Pagesbundle\Services\CurlUrl;

class ConstraintsCheckUrlValidator extends ConstraintValidator {
    #code
    private $curl;
    public function __construct(CurlUrl $curl)
    {
        $this->curl = $curl;
    }
    public function Validate($value, Constraint $constraint)
    {
        $this->curl->findUrl($value);
        $this->context->addViolation($constraint->message);
    }
    
    
}



?>