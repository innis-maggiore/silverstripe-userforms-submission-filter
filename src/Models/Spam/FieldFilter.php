<?php
namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\Spam;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
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
        'Title'                 => 'Varchar(255)',
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
        'Title'                 => 'Field Title',
        'FilterValues.Count'    => 'Filter Count'
    ];

    public function getFormFieldsMap()
    {
        return $this->Form()->Fields()->map('Name', 'Title');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
           'Title',
           'FormID',
           'FormFieldName',
           'FilterValues',
        ]);

        $fields->findOrMakeTab('Root.Main')->setTitle($this->i18n_singular_name());
        $fields->addFieldToTab('Root.Main',
            DropdownField::create('FormFieldName', 'Form Field Name', $this->getFormFieldsMap())
        );
        if ($this->isInDB()) {
            $fields->addFieldsToTab('Root.Main', [
                GridField::create('FilterValues',
                    'Filter Values',
                    $this->FilterValues(),
                    GridFieldConfig_RecordEditor::create()),
            ]);
        }

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        if ($filter = $this->Form()->FieldFilters()->filter('FormFieldName', $this->FormFieldName)->first())
            if ($filter->ID != $this->ID)
                $result->addError('Filter already exists for this field');

        return $result;
    }

    public function onBeforeWrite()
    {
        if (empty($this->Title))
            $this->Title = $this->getFormFieldsMap()->toArray()[$this->FormFieldName];

        parent::onBeforeWrite();
    }
}
