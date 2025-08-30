<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Code\FormSubmissionFilter;
use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\SpamEmailRecipient;
use SilverStripe\Forms\TabSet;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class InnismaggioreUserFormExtension extends Extension
{
    private static array $db = [
        'FilterList'            => 'Varchar(510)',
    ];

    private static array $has_many = [
        'SpamEmailRecipients'   => SpamEmailRecipient::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'FilterList',
            'SpamEmailRecipients',
        ]);

        $fields->addFieldToTab('Root.Spam',
            TabSet::create('SpamTabSet',
                Tab::create('SpamRecipientTab', 'Spam Recipients',
                    GridField::create('SpamEmailRecipients',
                        'Spam Recipients',
                        $this->getOwner()->SpamEmailRecipients(),
                        GridFieldConfig_RecordEditor::create(),
                    )
                )
            )
        );

        if ($emailTLD = Config::inst()->get(InnismaggioreUserFormExtension::class,'email_tld')) {
            if (str_contains(Security::getCurrentUser()->Email, $emailTLD)) {
                $fields->addFieldsToTab(
                    'Root.Spam.SpamTabSet',
                    [
                        Tab::create('SpamFilterList', 'Filter List',
                            TextareaField::create('FilterList', 'Filter List')->setDescription('Separate filter keys by spaces or commas, wrap key phrases in "" (double quotes) this will ignore the space comma delineation rule. Be as specific as possible and remember if the key is too generic it might throw false positives and good submissions may be marked as spam. There is no partial matching, it is all explicit case sensitive matching.'),
                        )
                    ]
                );
            }
        }
    }

    public function getFilterListArray()
    {
        if ($this->getOwner()->FilterList) {
            $list = preg_split('/,\s*(?=(?:[^"]*"[^"]*")*[^"]*$)/', $this->getOwner()->FilterList, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($list, function($value, $key) use(&$list) {$list[$key] = str_replace('"', '', $value);});
            return $list;
        }

        return null;
    }

    public function updateFilteredEmailRecipients($recipients, $data, $form)
    {
        $list = $this->getOwner()->getFilterListArray();
        $formSubFilter = new FormSubmissionFilter($data);

        // todo: Make submissionScrubber() class
        // check if first and last name match
        // check for Unicode character properties
        // check message field against a list of "trigger" words or phrases, and set a range for deciding how many times a trigger is found in the message before being marked spam
        // check global field trigger keyword bank. 1:1 match and case sensitive, auto mark as spam

        if ($formSubFilter->matchesSpam($list)) {
            foreach ($recipients as $recipient) {
                $recipients->remove($recipient);
            }
            if ($this->getOwner()->SpamEmailRecipients()->count() >= 1) {
                foreach ($this->getOwner()->SpamEmailRecipients() as $spamRecip) {
                    $recipients->push($spamRecip);
                }
            }
        }
    }
}