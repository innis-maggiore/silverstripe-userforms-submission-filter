<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;

class InnismaggioreUserDefinedFormControllerExtension extends Extension
{
    public function updateEmail($email, $recipient, $emailData)
    {
        $controller = $this->getOwner();
        $list = $controller->getFilterListArray();
        $match = $emailData['Fields']->filter('Value:ExactMatch:case', $list)->count() > 0;

        if ($match) {
            $email->setSubject('SPAM: ' . SiteConfig::current_site_config()->Title . ' | ' . $controller->Title . ' Submission');
            $email->setTo($controller->getFilterRecipientList());
        }
    }
}