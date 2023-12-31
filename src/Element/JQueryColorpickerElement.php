<?php

namespace Drupal\jquery_colorpicker\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a jQuery colorpicker form element.
 *
 * @FormElement("jquery_colorpicker")
 */
class JQueryColorpickerElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#maxlength' => 7,
      '#size' => 7,
      '#element_validate' => [
        [$class, 'validateElement'],
      ],
      '#jquery_colorpicker_background' => 'select.png',
      '#pre_render' => [
        [$class, 'preRenderjQueryColorpicker'],
      ],
      '#process' => [
        'Drupal\Core\Render\Element\RenderElement::processAjaxForm',
        [$class, 'processElement'],
      ],
      '#theme' => 'jquery_colorpicker',
      '#theme_wrappers' => ['form_element'],
      '#attached' => [
        'library' => [
          'jquery_colorpicker/element',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(&$element, FormStateInterface $form_state) {
    if (strlen($element['#value'])) {
      $valid_color = \Drupal::service('colorapi.service')->isValidHexadecimalColorString($element['#value']);
      if (!$valid_color) {
        $form_state->setError($element, t('@value is not a valid hexidecimal color.', ['@value' => $element['#value']]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE && $input !== NULL && is_scalar($input)) {
      if (\Drupal::service('colorapi.service')->isValidHexadecimalColorString($input)) {
        return strtoupper($input);
      }
    }

    return NULL;
  }

  /**
   * Prepares a #type 'jquery_colorpicker' render element a template.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for
   *   jquery-colorpicker.html.twig.
   */
  public static function preRenderjQueryColorpicker(array $element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, [
      'id',
      'name',
      'value',
      'size',
      'maxlength',
    ]);
    static::setAttributes($element, ['form-jquery_colorpicker']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processElement(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#id'] = Html::getUniqueID('edit-' . implode('-', $element['#parents']));

    // Decide what background to use to render the element. In order to ensure
    // the background exists, we create an array of the two possibilities, that
    // we will use to compare the value submitted in the Form API definition.
    $backgrounds = ['select.png', 'select2.png'];

    // Now we check to see if the value in the Form API definition is valid.
    // If it is, we use it, if it's not, we use a default value.
    $background = isset($element['#jquery_colorpicker_background']) && in_array($element['#jquery_colorpicker_background'], $backgrounds) ? $element['#jquery_colorpicker_background'] : 'select.png';

    // Since we know the background, we can then get the URL of it to pass to
    // the javascript function.
    $background_url = \Drupal::service('file_url_generator')->generateAbsoluteString('vendor://jaypan/jquery-colorpicker/images/' . $background);

    // Next we determine what the default value for the form element is. This
    // will also be passed to the javascript function.
    $element['#attached']['drupalSettings']['jqueryColorpicker']['elements'][$element['#id']]['background'] = $background_url;

    return $element;
  }

}
