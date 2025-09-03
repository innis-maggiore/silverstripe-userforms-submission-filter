<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class InnismaggioreUserDefinedFormControllerExtension extends Extension
{
    private static bool $isSpam = false;
    private static bool $delete = false;

    public function getIsSpam()
    {
        return self::$isSpam;
    }

    public function setIsSpam($bool)
    {
        self::$isSpam = $bool;
    }

    public function getDoDelete()
    {
        return self::$delete;
    }

    public function setDoDelete($bool)
    {
        self::$delete = $bool;
    }

    public function updateEmailData($emailData, $attachments)
    {
        $submittedFormID = $emailData['SubmittedForm']->ID;

        if ($submittedFormID && $this->getIsSpam() && $this->getDoDelete()) {
            SubmittedForm::get_by_id($submittedFormID)->delete();
        }

    }
}