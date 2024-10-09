<?php

namespace Drupal\coderz\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ContactForm extends FormBase {

  /**
   *
   */
  public function getFormId() {
    return 'coderz_contact_form';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['full_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Your Full Name'),
      ],
    ];

    $form['phone_number'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Phone Number'),
      ],
    ];

    $form['email_address'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter Email Address'),
      ],
    ];

    $form['address_location'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Address Location'),
      ],
    ];

    $form['select_time'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Select Time (HH:MM)'),
      ],
    ];

    $form['date'] = [
      '#type' => 'date',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Date (mm/dd/yyyy)'),
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Request'),
    ];

    return $form;
  }

  /**
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate phone number (must contain exactly 10 digits).
    $phone_number = $form_state->getValue('phone_number');
    if (!preg_match('/^\d{10,}$/', $phone_number)) {
      $form_state->setErrorByName('phone_number', $this->t('Enter a valid 10-digit phone number.'));
    }

    // Validate email address.
    $email = $form_state->getValue('email_address');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email_address', $this->t('Enter a valid email address.'));
    }

    // Validate time (HH:MM format).
    $time = $form_state->getValue('select_time');
    if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
      $form_state->setErrorByName('select_time', $this->t('Enter a valid time in HH:MM format.'));
    }

    // Validate that the selected date is not earlier than the current date.
    $submitted_date = $form_state->getValue('date');
    // Get the current date in the 'Y-m-d' format.
    $current_date = date('Y-m-d');

    if ($submitted_date < $current_date) {
      $form_state->setErrorByName('date', $this->t('The date cannot be earlier than today.'));
    }
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the form values.
    $full_name = $form_state->getValue('full_name');
    $phone_number = $form_state->getValue('phone_number');
    $email_address = $form_state->getValue('email_address');
    $address_location = $form_state->getValue('address_location');
    $select_time = $form_state->getValue('select_time');
    $date = $form_state->getValue('date');

    // Insert the data into the 'request_form_submissions' table.
    \Drupal::database()->insert('request_form_submissions')
      ->fields([
        'full_name' => $full_name,
        'phone_number' => $phone_number,
        'email_address' => $email_address,
        'address_location' => $address_location,
        'select_time' => $select_time,
        'date' => $date,
    // Save the current timestamp.
        'submitted' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    // Display a success message.
    \Drupal::messenger()->addMessage($this->t('Thank you! Your request has been submitted.'));
  }

}
