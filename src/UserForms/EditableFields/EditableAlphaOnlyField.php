<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\UserForms\EditableFields;

use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\UserForms\FormFields\CustomAlphaOnlyField;
use SilverStripe\Forms\FieldList;

class EditableAlphaOnlyField extends EditableTextField
{
    private static $table_name = 'EditableAlphaOnlyField';

    private static $singular_name = 'Alpha Only Field';
    private static $plural_name = 'Alpha Only Fields';

    public function getFormField()
    {
        $field = CustomAlphaOnlyField::create($this->Name, $this->Title, $this->Default)
            ->setFieldHolderTemplate('SilverStripe\UserForms\Model\FieldHolder');

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        // Add custom config options in the CMS for this field if needed
        return $fields;
    }
}