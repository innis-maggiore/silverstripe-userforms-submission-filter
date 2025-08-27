<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Security\Security;

class InnismaggioreUserFormExtension extends Extension
{
    private static array $db = [
        'FilterList'            => 'Varchar(510)',
        'FilterEmailRecipients' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $emailTLD = Config::inst()->get(InnismaggioreUserFormExtension::class,'email_tld') ?? '@innismaggiore.com';
        if (str_contains(Security::getCurrentUser()->Email, $emailTLD)) {
            $fields->addFieldToTab(
                'Root.FormOptions',
                TextareaField::create('FilterList', 'Filter List')
                    ->setDescription('Separate filter keys by spaces or commas, wrap key phrases in "" (double quotes) this will ignore the space comma delineation rule. Be as specific as possible and remember if the key is too generic it might throw false positives and good submissions may be marked as spam. There is no partial matching, it is all explicit case sensitive matching.')
            );
            $fields->addFieldToTab(
                'Root.FormOptions',
                TextareaField::create('FilterEmailRecipients', 'Alert Recipients')
                    ->setDescription('Separate emails by new line, defaults to `web-dev@innismaggiore.com`')
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

    public function getFilterRecipientList()
    {
        if ($this->getOwner()->FilterList)
            return preg_split('/\R/', $this->getOwner()->FilterEmailRecipients, -1, PREG_SPLIT_NO_EMPTY);

        return ['web-dev@innismaggiore.com'];
    }
}