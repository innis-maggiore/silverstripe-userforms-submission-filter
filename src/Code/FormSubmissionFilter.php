<?php

namespace InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Code;

use InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Models\FormFields\IpFormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;

class FormSubmissionFilter
{
    private static array $data = [];
    private static array $messages = [];
    private static bool $keyCount = true;
    private static bool $dupeCheck = true;

    private function getData(): array
    {
        return self::$data;
    }

    private function setData($data): void
    {
        self::$data = $data;
    }

    private function getKeyCount(): bool
    {
        return self::$keyCount;
    }

    private function setKeyCount($val): void
    {
        self::$keyCount = $val;
    }

    private function getDupeCheck(): bool
    {
        return self::$dupeCheck;
    }

    private function setDupeCheck($val): void
    {
        self::$dupeCheck = $val;
    }

    private function getMessages(): array
    {
        return self::$messages;
    }

    private function setMessages($messages): void
    {
        self::$messages = $messages;
    }

    public function __construct($data, $form)
    {
        $fields = $form->getController()->data()->Fields();
        $messageFields = [];

        $this->setDupeCheck(!$form->getController()->DisDupeCheck);
        $this->setKeyCount($form->getController()->KeyCount ? 1 : 0);

        foreach ($fields as $field) {
            if (!$field->showInReports()
                && !in_array(IpFormField::class, $field->getClassAncestry() ?? [])
                && !$field->Rows > 1)
            {
                continue;
            }
            // make 100% sure setting correct field
            if ($field->ClassName === IpFormField::class)
                $data[$field->Name] = $_SERVER['REMOTE_ADDR'];

            if ($field->Rows > 1)
                $messageFields[] = $data[$field->Name];
        }

        $this->setData($data);
        $this->setMessages($messageFields);
    }

    public function matchesSpam($filterList, $countList): bool
    {
        return $this->checkForTriggerVals($filterList) ||
            $this->checkDuplicateVals() ||
            $this->countFlagWordsInMessageField($countList); // check global field trigger keyword bank. 1:1 match and case sensitive, auto mark as spam
    }

    private function checkForTriggerVals($filterList): bool
    {
        return ( count( array_diff( $filterList, $this->getData() ) ) !== count( $filterList ) );
    }

    // check if two fields have identical values
    private function checkDuplicateVals(): bool
    {
        if (!$this->getDupeCheck())
            return false;

        $data = array_values($this->getData());
        $dataIndexes = count($data) - 1;
        for ($i = 0; $i < $dataIndexes; $i++) {
            $match = $data[$i];
            for ($j = ($i + 1); $j < $dataIndexes; $j++) {
                if ($match === $data[$j]) {
                    return true;
                }
            }
        }

        return false;
    }

    // check message field against a list of "trigger" words or phrases, and set a range for deciding how many times a trigger is found in the message before being marked spam
    private function countFlagWordsInMessageField($countList): bool
    {
        if (!$limit = $this->getKeyCount() || !$countList)
            return false;

        $messages = $this->getMessages();
        $count = 0;

        foreach ($messages as $message) {
            foreach ($countList as $key) {
                $count += substr_count($message, $key);
            }
            if ($count >= $limit)
                return true;

            $count = 0;
        }

        return false;
    }
}