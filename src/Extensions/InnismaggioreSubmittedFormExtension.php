<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class InnismaggioreSubmittedFormExtension extends Extension
{
    private static array $db = [
        'SuspectedSpam'             => 'Boolean',
    ];

    private static $summary_fields = [
        'ID',
        'Created.Nice'              => 'Created',
        'getReadOnlySuspectedSpam'  => 'Suspected Spam'
    ];

    public function getReadOnlySuspectedSpam()
    {
        return $this->getOwner()->SuspectedSpam ? 'yes' : 'no';
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'SuspectedSpam',
        ]);
    }
}