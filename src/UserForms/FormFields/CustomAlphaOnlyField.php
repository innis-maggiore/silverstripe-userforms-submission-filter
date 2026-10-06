<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\UserForms\FormFields;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\TextField;

class CustomAlphaOnlyField extends TextField
{
    public function validate($validator)
    {
        $valid = parent::validate($validator);

        if (preg_match('/[^a-zA-Z]/', $this->value())) {
            // silent error so bot isn't sure how to fix
            $validator->validationError($this->Name, '');
            $valid = false;
        }

        return $valid;
    }
}