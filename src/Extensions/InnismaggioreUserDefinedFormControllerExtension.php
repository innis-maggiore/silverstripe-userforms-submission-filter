<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Extension;

class InnismaggioreUserDefinedFormControllerExtension extends Extension
{
    private static ?int $submittedFormID = null;

    public function getSubFormID()
    {
        return self::$submittedFormID;
    }

    public function setSubFormID($id)
    {
        self::$submittedFormID = $id;
    }

    public function updateEmailData($emailData, $attachments)
    {
        $this->setSubFormID($emailData['SubmittedForm']->ID);
    }
}