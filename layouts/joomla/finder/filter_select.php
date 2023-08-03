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
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 *
 * @var   array     $showDates    Optional parameters
 * @var   Query     $idxQuery     The id of the input this label is for
 * @var   string    $options      The name of the input this label is for
 * @var   string    $classSuffix  The html code for the label
 * @var   object[]  $branches     The input field html code
 */

// Add the dates if enabled.
if ($showDates) {
echo HTMLHelper::_('filter.dates', $idxQuery, $options);
}
?>
<div class="filter-branch <?= $classSuffix; ?>">

    <?php
        // Iterate through all branches and build code.
        foreach ($branches as $bk => $bv) :
            // If the multi-lang plugin is enabled then drop the language branch.
            if ($bv->title === 'Language' && Multilanguage::isEnabled()) {
            continue;
            }

            $active = null;

            // Check if the branch is in the filter.
            if (array_key_exists($bv->title, $idxQuery->filters)) {
                // Get the request filters.
                $temp   = Factory::getApplication()->input->request->get('t', array(), 'array');

                // Search for active nodes in the branch and get the active node.
                $active = array_intersect($temp, $idxQuery->filters[$bv->title]);
                $active = count($active) === 1 ? array_shift($active) : null;
            }
    ?>

        <div class="control-group">
            <div class="control-label">
                <label for="tax-<?= OutputFilter::stringURLSafe($bv->title) ?>">
                    <?= Text::sprintf('COM_FINDER_FILTER_BRANCH_LABEL', Text::_(LanguageHelper::branchSingular($bv->title))); ?>wewerwerwer
                </label>
            </div>
            <div class="controls">
                <?= HTMLHelper::_(
                    'select.genericlist',
                    $branches[$bk]->nodes,
                    't[]',
                    'class="form-select advancedSelect"',
                    'id',
                    'title',
                    $active,
                    'tax-' . OutputFilter::stringURLSafe($bv->title)
                    ); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
