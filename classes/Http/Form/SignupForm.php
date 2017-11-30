<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Form;

/**
 * Form object for our signup & profile pages, handles validation of form data
 */
class SignupForm extends Form
{
    protected $fieldList = [
        'email',
        'password',
        'password2',
        'first_name',
        'last_name',
        'company',
        'twitter',
        'speaker_info',
        'speaker_bio',
        'transportation',
        'hotel',
        'speaker_photo',
        'agree_coc',
        'url',
    ];

    /**
     * Validate all methods by calling all our validation methods
     *
     * @param string $action
     *
     * @return bool
     */
    public function validateAll($action = 'create')
    {
        $this->sanitize();
        $validPasswords = true;
        $agreeCoc       = true;

        if ($action == 'create') {
            $validPasswords = $this->validatePasswords();
            $agreeCoc       = $this->validateAgreeCoc();
        }

        $validEmail         = $this->validateEmail();
        $validFirstName     = $this->validateFirstName();
        $validLastName      = $this->validateLastName();
        $validCompany       = $this->validateCompany();
        $validTwitter       = $this->validateTwitter();
        $validUrl           = $this->validateUrl();
        $validSpeakerPhoto  = $this->validateSpeakerPhoto();
        $validSpeakerInfo   = true;
        $validSpeakerBio    = true;

        if (!empty($this->taintedData['speaker_info'])) {
            $validSpeakerInfo = $this->validateSpeakerInfo();
        }

        if (!empty($this->taintedData['speaker_bio'])) {
            $validSpeakerBio = $this->validateSpeakerBio();
        }

        return
            $validEmail         &&
            $validPasswords     &&
            $validFirstName     &&
            $validLastName      &&
            $validCompany       &&
            $validTwitter       &&
            $validUrl           &&
            $validSpeakerInfo   &&
            $validSpeakerBio    &&
            $validSpeakerPhoto  &&
            $agreeCoc;
    }

    public function validateSpeakerPhoto(): bool
    {
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        // Speaker Photo is not required, only validate if it exists
        if (!isset($this->taintedData['speaker_photo'])) {
            return true;
        }

        // Check if the file was uploaded OK, display any error that may have occurred
        if (!$this->taintedData['speaker_photo']->isValid()) {
            $this->addErrorMessage($this->taintedData['speaker_photo']->getErrorMessage());

            return false;
        }

        // Check if uploaded file is greater than 5MB
        if ($this->taintedData['speaker_photo']->getClientSize() > (5 * 1048576)) {
            $this->addErrorMessage('Speaker photo can not be larger than 5MB');

            return false;
        }

        // Check if photo is in the mime-type white list
        if (!\in_array($this->taintedData['speaker_photo']->getMimeType(), $allowedMimeTypes)) {
            $this->addErrorMessage('Speaker photo must be a jpg or png');

            return false;
        }

        return true;
    }

    /**
     * Method that applies validation rules to email
     *
     * @return bool
     *
     * @internal param string $email
     */
    public function validateEmail(): bool
    {
        if (!isset($this->taintedData['email']) || $this->taintedData['email'] == '') {
            $this->addErrorMessage('Missing email');

            return false;
        }

        $response = \filter_var($this->taintedData['email'], FILTER_VALIDATE_EMAIL);

        if (!$response) {
            $this->addErrorMessage('Invalid email address format');

            return false;
        }

        return true;
    }

    /**
     * Method that applies validation rules to user-submitted passwords
     *
     * @return bool|string
     */
    public function validatePasswords(): bool
    {
        $passwd  = $this->cleanData['password'];
        $passwd2 = $this->cleanData['password2'];

        if ($passwd == '' || $passwd2 == '') {
            $this->addErrorMessage('Missing passwords');

            return false;
        }

        if ($passwd !== $passwd2) {
            $this->addErrorMessage('The submitted passwords do not match');

            return false;
        }

        if (\strlen($passwd) < 5 && \strlen($passwd2) < 5) {
            $this->addErrorMessage('The submitted password must be at least 5 characters long');

            return false;
        }

        if ($passwd !== \str_replace(' ', '', $passwd)) {
            $this->addErrorMessage('The submitted password contains invalid characters');

            return false;
        }

        return true;
    }

    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @return bool
     */
    public function validateFirstName(): bool
    {
        $firstName          = $this->cleanData['first_name'];
        $validationResponse = true;

        if (empty($firstName)) {
            $this->addErrorMessage('First name cannot be blank');
            $validationResponse = false;
        }

        if (\strlen($firstName) > 255) {
            $this->addErrorMessage('First name cannot exceed 255 characters');
            $validationResponse = false;
        }

        if ($firstName !== $this->taintedData['first_name']) {
            $this->addErrorMessage('First name contains unwanted characters');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @return bool
     */
    public function validateLastName(): bool
    {
        $lastName           = $this->cleanData['last_name'];
        $validationResponse = true;

        if (empty($lastName)) {
            $this->addErrorMessage('Last name cannot be blank');
            $validationResponse = false;
        }

        if (\strlen($lastName) > 255) {
            $this->addErrorMessage('Last name cannot exceed 255 characters');
            $validationResponse = false;
        }

        if ($lastName !== $this->taintedData['last_name']) {
            $this->addErrorMessage('Last name contains unwanted characters');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    public function validateCompany(): bool
    {
        // $company = $this->_cleanData['company'];
        return true;
    }

    public function validateTwitter(): bool
    {
        // $twitter = $this->_cleanData['twitter'];
        return true;
    }

    public function validateUrl(): bool
    {
        if (\preg_match('/https:\/\/joind\.in\/user\/[a-zA-Z0-9]{1,25}/', $this->cleanData['url'])
            || !isset($this->cleanData['url'])
            || $this->cleanData['url'] == ''
        ) {
            return true;
        }
        $this->addErrorMessage('You did not enter a valid joind.in URL');

        return false;
    }

    /**
     * Method that applies validation rules to user-submitted speaker info
     *
     * @return bool
     */
    public function validateSpeakerInfo(): bool
    {
        $speakerInfo = \filter_var(
            $this->cleanData['speaker_info'],
            FILTER_SANITIZE_STRING
        );
        $validationResponse  = true;
        $speakerInfo         = \strip_tags($speakerInfo);
        $speakerInfo         = $this->purifier->purify($speakerInfo);

        if (empty($speakerInfo)) {
            $this->addErrorMessage('You submitted speaker info but it was empty after sanitizing');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    /**
     * Method that applies validation rules to user-submitted speaker bio
     *
     * @return bool
     */
    public function validateSpeakerBio(): bool
    {
        $speakerBio = \filter_var(
            $this->cleanData['speaker_bio'],
            FILTER_SANITIZE_STRING
        );
        $validationResponse = true;
        $speakerBio         = \strip_tags($speakerBio);
        $speakerBio         = $this->purifier->purify($speakerBio);

        if (empty($speakerBio)) {
            $this->addErrorMessage('You submitted speaker bio information but it was empty after sanitizing');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    /**
     * Santize all our fields that were submitted
     */
    public function sanitize()
    {
        parent::sanitize();

        // We shouldn't be sanitizing passwords, so reset them
        if (isset($this->taintedData['password'])) {
            $this->cleanData['password'] = $this->taintedData['password'];
        }

        if (isset($this->taintedData['password2'])) {
            $this->cleanData['password2'] = $this->taintedData['password2'];
        }

        // Remove leading @ for twitter
        if (isset($this->taintedData['twitter'])) {
            $this->cleanData['twitter'] = \preg_replace(
                '/^@/',
                '',
                $this->taintedData['twitter']
            );
        }
    }

    private function validateAgreeCoc(): bool
    {
        if (!$this->getOption('has_coc')) {
            return true;
        }

        if ($this->cleanData['agree_coc'] === 'agreed') {
            return true;
        }

        $this->addErrorMessage('You must agree to abide by our code of conduct in order to submit');

        return false;
    }
}
