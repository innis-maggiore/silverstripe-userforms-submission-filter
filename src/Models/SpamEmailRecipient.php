<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models;

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;

class SpamEmailRecipient extends EmailRecipient
{
    public function populateDefaults()
    {
        $this->EmailSubject = 'SPAM: ' . SiteConfig::current_site_config()->Title . ' | Form Submission';

        parent::populateDefaults();
    }
}