<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Registry;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 *
 * @var   Query     $idxQuery     TODO: Add description...
 * @var   Registry  $options      TODO: Add description...
 * @var   string    $classSuffix  The class suffix
 * @var   boolean   $loadMedia    TODO: Add description...
 */

// Build the date operators options.

$operators   = [];
$operators[] = HTMLHelper::_('select.option', 'before', Text::_('COM_FINDER_FILTER_DATE_BEFORE'));
$operators[] = HTMLHelper::_('select.option', 'exact', Text::_('COM_FINDER_FILTER_DATE_EXACTLY'));
$operators[] = HTMLHelper::_('select.option', 'after', Text::_('COM_FINDER_FILTER_DATE_AFTER'));

// Load the CSS/JS resources.
if ($loadMedia) {
    /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->useStyle('com_finder.dates');
}

$attribs['class'] = 'input-medium'
?>
        // Open the widget.
<ul id="finder-filter-select-dates">

    <li class="filter-date float-start' . $classSuffix . '">
        <label for="filter_date1" class="hasTooltip" title ="<?= Text::_('COM_FINDER_FILTER_DATE1_DESC'); ?>">
            <?php Text::_('COM_FINDER_FILTER_DATE1'); ?>
        </label>
        <br>
        <?= HTMLHelper::_(
                'select.genericlist',
                $operators,
                'w1',
                'class="inputbox filter-date-operator advancedSelect form-select w-auto mb-2"',
                'value',
                'text',
                $idxQuery->when1,
                'finder-filter-w1'
            ); ?>
        <?= HTMLHelper::_('calendar', $idxQuery->date1, 'd1', 'filter_date1', '%Y-%m-%d', $attribs); ?>
    </li>

    <li class="filter-date float-end' . $classSuffix . '">
        <label for="filter_date2" class="hasTooltip" title ="<?= Text::_('COM_FINDER_FILTER_DATE2_DESC'); ?>">
            <?= Text::_('COM_FINDER_FILTER_DATE2'); ?>
        </label>
        <br>
        <?= HTMLHelper::_(
            'select.genericlist',
            $operators,
            'w2',
            'class="inputbox filter-date-operator advancedSelect form-select w-auto mb-2"',
            'value',
            'text',
            $idxQuery->when2,
            'finder-filter-w2'
        ); ?>
        <?= HTMLHelper::_('calendar', $idxQuery->date2, 'd2', 'filter_date2', '%Y-%m-%d', $attribs); ?>
    </li>
</ul>
