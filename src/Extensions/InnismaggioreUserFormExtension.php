<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Code\FormSubmissionFilter;
use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\Spam\FieldFilter;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Security\Security;
use SilverStripe\Core\Injector\Injector;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;


class InnismaggioreUserFormExtension extends Extension
{
    private static array $db = [
        'FilterList'            => 'Varchar(510)',
        'CountList'             => 'Varchar(510)',
        'KeyCount'              => 'Int',
        'DisDupeCheck'          => 'Boolean',
        'DeleteSpamSubmission'  => 'Boolean',
    ];

    private static array $has_many = [
        'FieldFilters'          => FieldFilter::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'FilterList',
            'KeyCount',
            'DisDupeCheck',
            'DeleteSpamSubmission',
            'CountList',
            'FieldFilters',
        ]);


        if ($emailTLD = Config::inst()->get(InnismaggioreUserFormExtension::class,'email_tld')) {
            if (str_contains(Security::getCurrentUser()->Email, $emailTLD)) {
                $fields->addFieldsToTab(
                    'Root.Spam',
                    [
                        TabSet::create('MinaTabSet',
                            Tab::create('SpamFilterList', 'Filter List',
                                TextareaField::create('FilterList', 'Field Values Trigger List')->setDescription('Compares all form field values against this list. A match between a field value and a list item will auto mark submission as spam. Separate filter keys by spaces or commas, wrap key phrases in "" (double quotes) this will ignore the space comma delineation rule. Be as specific as possible and remember if the key is too generic it might throw false positives and good submissions may be marked as spam. There is no partial matching, it is all explicit case sensitive matching.'),
                                TextareaField::create('CountList', 'Message Body Trigger List')->setDescription('Counts all occurrence of the items in this list against each textarea field in the form. Control amount of times a trigger word must appear before being considered spam with `Message Body Trigger Limit` below.'),
                                NumericField::create('KeyCount', 'Message Body Trigger Limit')
                                    ->setDescription('Set number of times a trigger word must be present in textarea before being considered spam. For Key Counter Filter. Set to 0 to turn off.'),
                                CheckboxField::create('DisDupeCheck', 'Disable Duplicate Key Filter'),
                                CheckboxField::create('DeleteSpamSubmission', 'Delete Spam Submissions from Database'),
                            ),
                            Tab::create('SpamFilterFieldMappingList', 'Map Fields',
                                GridField::create('FieldFilters', 'Field Filters',
                                    $this->getOwner()->FieldFilters(),
                                    GridFieldConfig_RecordEditor::create()
                                )
                            )
                        )
                    ]
                );
            }
        }
    }

    // todo: tie in cache for list
    public function getFilterListArray($filterList, $key)
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.innismaggioreUserFormExtensionCache');
        $cache_key = $key . '_' . preg_replace('/:+/', '', $this->getOwner()->LastEdited);

        if ($cache->has($cache_key)) {
            return $cache->get($cache_key);
        } else {
            $list = preg_split('/,\s*(?=(?:[^"]*"[^"]*")*[^"]*$)/', $filterList, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($list, function($value, $key) use(&$list) {$list[$key] = str_replace('"', '', $value);});
            $cache->set($cache_key, $list, 604800);
            return $list;
        }
    }

    public function getMappedFilterList(): array
    {
        $list = [];
        if ( $this->getOwner()->FieldFilters()->count() > 0 ) {
            foreach ($this->getOwner()->FieldFilters() as $fieldFilter) {
                $list[$fieldFilter->FormFieldName] = $fieldFilter->FilterValues()->column('Value');
            }
        }

        return $list;
    }

    public function getExplicitFieldList()
    {
        if ($this->getOwner()->FilterList)
            return $this->getFilterListArray($this->getOwner()->FilterList, 'explicit_field_list');

        return [];
    }

    public function getCountMessageList()
    {
        if ($this->getOwner()->CountList)
            return $this->getFilterListArray($this->getOwner()->CountList, 'count_list');

        return [];
    }

    public function updateFilteredEmailRecipients($recipients, $data, $form)
    {
        $ex_list = $this->getExplicitFieldList();
        $count_list = $this->getCountMessageList();
        $mapped_list = $this->getMappedFilterList();
        $formSubFilter = new FormSubmissionFilter($data, $form);

        if ($formSubFilter->matchesSpam($ex_list, $count_list, $mapped_list)) {
            foreach ($recipients as $recipient) {
                if (!$recipient->SpamRecipient)
                    $recipients->remove($recipient);
            }

            if ($subFormID = Controller::curr()->getSubFormID()) {
                if ($this->getOwner()->DeleteSpamSubmission) {
                    SubmittedForm::get_by_id($subFormID)->delete();
                } else {
                    $submittedForm = SubmittedForm::get_by_id($subFormID);
                    $submittedForm->SuspectedSpam = true;
                    $submittedForm->write();
                }
            }
        } else {
            foreach ($recipients as $recipient) {
                if ($recipient->SpamRecipient)
                    $recipients->remove($recipient);
            }
        }
    }
}
