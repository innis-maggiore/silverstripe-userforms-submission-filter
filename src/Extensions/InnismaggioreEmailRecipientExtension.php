<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;

class InnismaggioreEmailRecipientExtension extends Extension
{
    private static array $db = [
        'SpamRecipient'         => 'Boolean',
    ];

    private static $summary_fields = [
        'EmailAddress',
        'EmailSubject',
        'EmailFrom',
        'getReadOnlySpamRecipient' => 'Spam Recipient'
    ];

    public function getReadOnlySpamRecipient()
    {
        return $this->getOwner()->SpamRecipient ? 'yes' : 'no';
    }

    public function summaryFields()
    {
        $fields = parent::summaryFields();
        if (isset($fields['EmailAddress'])) {
            $fields['EmailAddress'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILADDRESS', 'Email');
        }
        if (isset($fields['EmailSubject'])) {
            $fields['EmailSubject'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILSUBJECT', 'Subject');
        }
        if (isset($fields['EmailFrom'])) {
            $fields['EmailFrom'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILFROM', 'From');
        }
        if (isset($fields['getReadOnlySpamRecipient'])) {
            $fields['getReadOnlySpamRecipient'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SPAM', 'Spam Recipient');
        }
        return $fields;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'SpamRecipient',
        ]);

        $fields->addFieldToTab('Root.EmailDetails', CheckboxField::create('SpamRecipient')
            ->setDescription('Check box to receive spam submissions')
            ->setTitle('Enable Spam Recipient'));
    }
}