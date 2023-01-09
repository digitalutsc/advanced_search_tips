<?php

namespace Drupal\advanced_search_tips\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "advanced_search_tips",
 *   admin_label = @Translation("Advanced Search: Advanced Search Tips Block")
 * )
 */
class AdvancedSearchTipsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   *
   * This method sets the block default configuration. This configuration
   * determines the block's behavior when a block is initially placed in a
   * region. Default values for the block configuration form should be added to
   * the configuration array. System default configurations are assembled in
   * BlockBase::__construct() e.g. cache setting and block title visibility.
   *
   * @see \Drupal\block\BlockBase::__construct()
   */
  public function defaultConfiguration() {
     // Clone the Site Help page from https://memory.digital.utsc.utoronto.ca/site-help
    $doc = new \DOMDocument();
    $doc->validateOnParse = true;
    $doc->loadHtml(file_get_contents('https://memory.digital.utsc.utoronto.ca/search-results'));

    // Get the html content of the page in Memory site
    $body = DOMinnerHTML($doc->getElementById('block-advancedsearchtips'));

    return [
      'advanced_search_tips_string' => $body
    ];
  }

  /**
   * {@inheritdoc}
   *
   * This method defines form elements for custom block configuration. Standard
   * block configuration fields are added by BlockBase::buildConfigurationForm()
   * (block title and title visibility) and BlockFormController::form() (block
   * visibility settings).
   *
   * @see \Drupal\block\BlockBase::buildConfigurationForm()
   * @see \Drupal\block\BlockFormController::form()
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['advanced_search_tips_string_text'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Block contents'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['advanced_search_tips_string']
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['advanced_search_tips_string']
      = $form_state->getValue('advanced_search_tips_string_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->configuration['advanced_search_tips_string'],
    ];
  }

}
