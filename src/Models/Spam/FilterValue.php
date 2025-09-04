<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\Spam;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;

class FilterValue extends DataObject
{
    private static $table_name = 'FilterValue';
    private static $singular_name = 'Filter Value';
    private static $plural_name = 'Filter Value';
    private static $description = 'You may choose a form field by name and add a list of values that will reject the form submission if any are found.';

    private static $db = [
        'Value'         => 'Varchar(100)',
    ];

    private static $has_one = [
        'FieldFilter'   => FieldFilter::class,
    ];

    private static $summary_fields = [
        'Value'         => 'Match Against',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['FieldFilterID']);

        return $fields;
    }
}
