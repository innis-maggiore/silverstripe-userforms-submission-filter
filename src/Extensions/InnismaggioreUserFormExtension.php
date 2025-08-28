<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\SpamEmailRecipient;
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

    private static array $many_many = [
        'SpamEmailRecipients'   => SpamEmailRecipient::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'FilterList',
            'SpamEmailRecipients',
        ]);

        $emailTLD = Config::inst()->get(InnismaggioreUserFormExtension::class,'email_tld') ?? '@innismaggiore.com';

        if (!$this->getOwner()->SpamEmailRecipients()->exists()) {
            $firstTLDAdmin = Member::get()->filter('Groups.Code', 'administrators')->filter('Email:PartialMatch:nocase', $emailTLD)->first();
            $defaultEmail = $firstTLDAdmin ? $firstTLDAdmin->Email : 'web-dev@innismaggiore.com';
            $spamRecip = new SpamEmailRecipient();
            $spamRecip->EmailAddress = $defaultEmail;
            $spamRecip->FormID = $this->getOwner()->ID;
            $spamRecip->write();
            $this->getOwner()->SpamEmailRecipients()->Add($spamRecip);
            $this->getOwner()->write();
        }

        if (str_contains(Security::getCurrentUser()->Email, $emailTLD)) {
            $fields->addFieldsToTab(
                'Root.Spam',
                [
                    TextareaField::create('FilterList', 'Filter List')
                        ->setDescription('Separate filter keys by spaces or commas, wrap key phrases in "" (double quotes) this will ignore the space comma delineation rule. Be as specific as possible and remember if the key is too generic it might throw false positives and good submissions may be marked as spam. There is no partial matching, it is all explicit case sensitive matching.'),
                    GridField::create('SpamEmailRecipients',
                        'Spam Recipients',
                        $this->getOwner()->SpamEmailRecipients(),
                        GridFieldConfig_RecordEditor::create()
                    )
                ]
            );
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
        $no_matches_list = array_diff($data, $list);
        $match = ( count( $no_matches_list ) !== count( $list ) );

        if ($match) {
            foreach ($recipients as $recipient) {
                $recipients->remove($recipient);
            }
            foreach ($this->getOwner()->SpamEmailRecipients() as $spamRecip) {
                $recipients->push($spamRecip);
            }
        }
    }
}