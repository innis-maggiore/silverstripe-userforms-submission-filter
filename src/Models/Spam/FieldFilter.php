<?php
namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\Spam;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\ORM\DataObject;

class FieldFilter extends DataObject
{
    private static $table_name = 'FieldFilter';
    private static $singular_name = 'Field Filter';
    private static $plural_name = 'Field Filters';
    private static $description = 'You may choose a form field by name and add a list of values that will reject the form submission if any are found.';

    private static $db = [
        'FormFieldName'         => 'Varchar(255)',
    ];

    private static $has_one = [
        'Form'                  => DataObject::class,
    ];

    private static $has_many = [
        'FilterValues'          => FilterValue::class
    ];

    private static $summary_fields = [
        'FormFieldName',
        'FilterValues.Count'    => 'Filter Count'
    ];

    public function getFormFieldsMap()
    {
        return $this->Form()->Fields()->map();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
           'FormID',
           'FormFieldName',
           'FilterValues',
        ]);

        $fields->findOrMakeTab('Root.Main')->setTitle($this->i18n_singular_name());

        $fields->addFieldToTab('Root.Main', [

        ]);

        return $fields;
    }
}
