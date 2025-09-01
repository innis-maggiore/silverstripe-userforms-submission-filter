<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Code\FormSubmissionFilter;
use SilverStripe\Forms\TabSet;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class InnismaggioreUserFormExtension extends Extension
{
    private static array $db = [
        'FilterList'            => 'Varchar(510)',
        'KeyCount'              => 'Int',
        'DisDupeCheck'          => 'Boolean',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'FilterList',
            'KeyCount',
            'DisDupeCheck',
        ]);


        if ($emailTLD = Config::inst()->get(InnismaggioreUserFormExtension::class,'email_tld')) {
            if (str_contains(Security::getCurrentUser()->Email, $emailTLD)) {
                $fields->addFieldsToTab(
                    'Root.Spam',
                    [
                        Tab::create('SpamFilterList', 'Filter List',
                            TextareaField::create('FilterList', 'Filter List')->setDescription('Separate filter keys by spaces or commas, wrap key phrases in "" (double quotes) this will ignore the space comma delineation rule. Be as specific as possible and remember if the key is too generic it might throw false positives and good submissions may be marked as spam. There is no partial matching, it is all explicit case sensitive matching.'),
                            NumericField::create('KeyCount', 'Key Counter')
                                ->setDescription('Set number of times a trigger word must be present in text before being considered spam. For Key Counter Filter. Set to 0 to turn off.'),
                            CheckboxField::create('DisDupeCheck', 'Disable Duplicate Key Filter'),
                        )
                    ]
                );
            }
        }
    }

    // todo: tie in cache for list
    public function getFilterListArray()
    {
        if ($filterList = $this->getOwner()->FilterList) {
            $list = preg_split('/,\s*(?=(?:[^"]*"[^"]*")*[^"]*$)/', $filterList, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($list, function($value, $key) use(&$list) {$list[$key] = str_replace('"', '', $value);});

            return $list;
        }

        return null;
    }

    public function updateFilteredEmailRecipients($recipients, $data, $form)
    {
        $list = $this->getFilterListArray();
        $formSubFilter = new FormSubmissionFilter($data, $form);

        if ($formSubFilter->matchesSpam($list)) {
            foreach ($recipients as $recipient) {
                if (!$recipient->SpamRecipient)
                    $recipients->remove($recipient);
            }
        }
    }
}