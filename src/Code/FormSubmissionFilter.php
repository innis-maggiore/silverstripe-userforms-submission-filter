<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Code;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\FormFields\IpFormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;

class FormSubmissionFilter
{
    private static array $data;

    private function getData(): array
    {
        return self::$data;
    }

    private function setData($data): void
    {
        self::$data = $data;
    }

    public function __construct($data)
    {
        $fields = $this->getOwner()->data()->Fields();

        foreach ($fields as $field) {
            if (!$field->showInReports() || !in_array(IpFormField::class, $field->getClassAncestry() ?? [])) {
                continue;
            }
            // make 100% sure setting correct field
            if ($field->ClassName === IpFormField::class) {
                $data[$field->Name] = $_SERVER['REMOTE_ADDR'];
            }
        }

        $this->setData($data);
    }

    public function matchesSpam($filterList): bool
    {

        return $this->checkDuplicateVals() ||
            $this->pregMatchOnMessageField() ||
            $this->countFlagWordsInMessageField($filterList) ||
            in_array($this->getData(), $filterList); // check global field trigger keyword bank. 1:1 match and case sensitive, auto mark as spam
    }

    // check if two fields have identical values
    private function checkDuplicateVals(): bool
    {
        $data = $this->getData();

        for ($i = 0; $i < count($data); $i++) {
            $match = $data[$i];
            for ($j = $i + 1; $j < count($data) - $i; $j++) {
                if ($match === $data[$j]) {
                    return true;
                }
            }
        }

        return false;
    }

    // check for Unicode character properties
    private function pregMatchOnMessageField(): bool
    {
        $data = $this->getData();
        $pattern = "[^ -~\r\n]"; // will match any character that is not an ASCII printable character and is also not a carriage return or a line feed. This effectively targets non-ASCII characters and other non-printable control characters that are not newlines.

        return preg_match($pattern, $data['MessageField']);
    }

    // check message field against a list of "trigger" words or phrases, and set a range for deciding how many times a trigger is found in the message before being marked spam
    private function countFlagWordsInMessageField($filterList): bool
    {
        $data = $this->getData();
        $count = 0;
        $limit = 5;

        foreach ($filterList as $key) {
            $count += substr_count($key, $data['MessageField']);
        }

        return $count > $limit;
    }

}