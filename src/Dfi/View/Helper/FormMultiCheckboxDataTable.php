<?php
namespace Dfi\View\Helper;
use Zend_Filter_Exception;
use Zend_Filter_PregReplace;
use Zend_View_Helper_FormElement;
/**
 * Ez_Form_Decorator_BootstrapErrors
 *
 * Wraps errors in span with class help-inline
 */
class FormMultiCheckboxDataTable extends Zend_View_Helper_FormElement
{
    /**
     * Input type to use
     * @var string
     */
    protected $_inputType = 'checkbox';

    /**
     * Whether or not this element represents an array collection by default
     * @var bool
     */
    protected $_isArray = false;

    /**
     * Generates a set of radio button elements.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The radio value to mark as 'checked'.
     *
     * @param array|string $attribs Attributes added to each radio.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param string $listsep
     * @return string The radio buttons XHTML.
     * @throws Zend_Filter_Exception
     */
    public function FormMultiCheckboxDataTable($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {

        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info);

        /**
         * @var $name
         * @var $value
         * @var $attribs
         * @var $options
         * @var $listsep
         * @var $disable
         * @var $id
         * @var $escape
         */


        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $label_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        $labelPlacement = 'append';
        foreach ($label_attribs as $key => $val) {
            switch (strtolower($key)) {
                case 'placement':
                    unset($label_attribs[$key]);
                    $val = strtolower($val);
                    if (in_array($val, array('prepend', 'append'))) {
                        $labelPlacement = $val;
                    }
                    break;
            }
        }

        // the radio button values and labels
        $options = (array)$options;

        $columnFilter = array(
            "sPlaceHolder" => "head:after",
            "aoColumns" => [
                null,
                array("type" => "text"),
                array("type" => "text"),
                array("type" => "text"),
                array("type" => "text")
            ]
        );

        $columnFilterJson = json_encode($columnFilter);

        // build the element
        $xhtml = '<table class="table table-striped table-condensed table-hover table-columnfilter table-checkable datatable"
        data-widget="false"
       data-paging="false"
       data-columnFilter=\'' . $columnFilterJson . '\'
       data-columnFilter-select2="true"
       data-searching="true"
       width="100%">';
        $xhtml .= '<thead><tr>';
        $firstRow = $options[key($options)];
        $xhtml .= '<th class="checkbox-column"><input type="checkbox" class="uniform"></th>';
        foreach ($firstRow as $collName => $valll) {
            $xhtml .= '<th>' . $collName . '</th>';
        }

        $xhtml .= '</tr></thead><tbody>';
        $list = array();

        // should the name affect an array collection?
        $name = $this->view->escape($name);
        if ($this->_isArray && ('[]' != substr($name, -2))) {
            $name .= '[]';
        }

        // ensure value is an array to allow matching multiple times
        $value = (array)$value;

        // Set up the filter - Alnum + hyphen + underscore
        require_once 'Zend/Filter/PregReplace.php';
        $pattern = @preg_match('/\pL/u', 'a')
            ? '/[^\p{L}\p{N}\-\_]/u'    // Unicode
            : '/[^a-zA-Z0-9\-\_]/';     // No Unicode
        $filter = new Zend_Filter_PregReplace($pattern, "");

        // add radio buttons to the list.
        foreach ($options as $opt_value => $fields) {


            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($opt_value, $disable)) {
                $disabled = ' disabled="disabled"';
            }

            // is it checked?
            $checked = '';
            if (in_array($opt_value, $value)) {
                $checked = ' checked="checked"';
            }

            // generate ID
            $optId = $id . '-' . $filter->filter($opt_value);

            // Wrap the radios in labels
            $radio = '<label'
                . $this->_htmlAttribs($label_attribs) . '>'
                . '<input type="' . $this->_inputType . '"'
                . ' name="' . $name . '"'
                . ' id="' . $optId . '"'
                . ' value="' . $this->view->escape($opt_value) . '"'
                . $checked
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $this->getClosingBracket()
                . '</label>';

            // add to the array of radio buttons
            $line = '<tr><td class="checkbox-column">' . $radio . '</td>';
            foreach ($fields as $labels) {
                $line .= '<td>' . $labels . '</td>';
            }
            $list[] = $line . '</tr>';
        }

        // done!
        $xhtml .= implode("\n", $list);
        $xhtml .= '</tbody></table>';
        return $xhtml;
    }
}