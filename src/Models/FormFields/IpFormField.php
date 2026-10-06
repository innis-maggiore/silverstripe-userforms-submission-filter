<?php
namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\FormFields;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\UserForms\Model\EditableFormField;

class IpFormField extends EditableFormField
{
    private static $table_name = 'IpFormField';

    private static $singular_name = 'Hidden IP Field';

    private static $plural_name = 'Hidden IP Fields';

    private static $description = 'A hidden field that keeps track of submitters remote IP.';

    private static $has_placeholder = true;

    private static $db = [

    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        return $result;
    }

    /**
     * @return FieldList
     */
    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();

        return $fields;
    }

    /**
     * @return HiddenField
     */
    public function getFormField()
    {
        $field = HiddenField::create($this->Name, $this->Title ?: false)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate('UserFormsField');

        $this->doUpdateFormField($field);

        return $field;
    }

    // this runs in process() | UserDefinedFormController:299
    public function getValueFromData($data)
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        parent::updateFormField($field);
    }

    public function onBeforeWrite()
    {
        $array = explode('\\', $this->ClassName);
        if ( !str_contains( $this->Name, array_pop($array) ) )
            $this->Name = null;

        parent::onBeforeWrite();
    }
}
