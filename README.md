# SilverStripe SMTP Tester

Default installation extends `SilverStripe\UserForms\Model\UserDefinedForm` and adds two new CMS Textarea fields. One for Filter Keys, and one for SPAM email recipients.
There is also a IpFormField added to the UserForm field_classes list that will retrieve the clients remote_addr after form submission to append to form email response.

## Installation

##### Using Composer

```html
$ composer require innis-maggiore/silverstripe-userforms-submission-filter ^5.0
```

* Run `/dev/build?flush=all`

## Add YML for DNADesign\ElementalUserForms\Model\ElementForm if using elemental's form panel

**app/_config/mysite.yml**
```html
DNADesign\ElementalUserForms\Model\ElementForm:
  extensions:
    - InnisMaggiore\SilverstripeUserFormsSubmissionFilter\Extensions\InnismaggioreUserFormExtension
  email_tld: '@mycompany.com'
```
## Add YML config for viewing new CMS fields - not for clients, but web admins

**app/_config/mysite.yml**
```html
InnisMaggiore\SilverstripeUserFormsSubmissionFilter\UserFormsSubmissionFilter:
  email_tld: '@mycompany.com'
```
