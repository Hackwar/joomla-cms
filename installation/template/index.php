<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/** @var JDocumentHtml $this */

// Add required assets
$this->getWebAssetManager()
	->registerAndUseStyle('template.installation', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css')
	->useScript('core')
	->useScript('keepalive')
	->useScript('form.validate')
	->registerAndUseScript('template.installation', 'installation/template/js/template.js', [], [], ['core', 'form.validate']);

$this->getWebAssetManager()
	->useStyle('webcomponent.joomla-alert')
	->useScript('webcomponent.joomla-alert')
	->useScript('webcomponent.core-loader');

HTMLHelper::_('bootstrap.framework');

// Add script options
$this->addScriptOptions('system.installation', ['url' => Route::_('index.php')]);

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Add strings for JavaScript error translations.
Text::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
Text::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');
Text::script('JLIB_JS_AJAX_ERROR_TIMEOUT');
Text::script('INSTL_DATABASE_RESPONSE_ERROR');

// Load the JavaScript translated messages
Text::script('INSTL_PROCESS_BUSY');
Text::script('INSTL_FTP_SETTINGS_CORRECT');

// Load strings for translated messages (directory removal)
Text::script('INSTL_REMOVE_INST_FOLDER');
Text::script('INSTL_COMPLETE_REMOVE_FOLDER');

$steps = 6;

if (is_file('sql/mysql/localise.sql'))
{
	$steps++;
}

if (is_file('sql/mysql/custom.sql'))
{
	$steps++;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<jdoc:include type="metas" />
		<jdoc:include type="styles" />
	</head>
	<body data-basepath="<?php echo Uri::root(true); ?>">
		<div class="j-install">
			<?php // Header ?>
			<header id="header" class="header">
				<div class="d-flex ">
					<div class="logo d-none d-md-block col">
						<img src="<?php echo $this->baseurl; ?>/template/images/Joomla-logo-monochrome-horizontal-white.svg" alt="">
					</div>
					<div class="mx-2 my-3 d-flex d-md-none">
						<img class="logo-small d-flex d-md-none" src="<?php echo $this->baseurl; ?>/template/images/Joomla-brandmark-monochrome-white-RGB.svg" alt="">
					</div>
					<div class="d-flex flex-wrap align-items-center mx-auto col-md-auto justify-content-center">
						<h1 class="h2 mx-1 d-flex align-items-baseline">
							<span class="icon-cogs d-none d-md-block mx-2 align-items-center" aria-hidden="true"></span>
							<?php echo Text::_('INSTL_PAGE_TITLE'); ?>
						</h1>
						<span class="small mx-1">
							Joomla! <?php echo (new Version)->getShortVersion(); ?>
						</span>
					</div>
					<div class="m-2 d-flex align-items-center col justify-content-end">
						<a href="https://docs.joomla.org/Special:MyLanguage/J4.x:Installing_Joomla" target="_blank">
							<span class="icon-question" aria-hidden="true"></span>
							<span class="sr-only"><?php echo Text::_('INSTL_HELP_LINK'); ?></span>
						</a>
					</div>
				</div>
			</header>
			<?php // Container ?>
			<div id="wrapper" class="d-flex wrapper flex-wrap">
				<div class="container-fluid container-main">
					<div id="content" class="content h-100">
						<main class="d-flex justify-content-center align-items-center h-100">
							<div class="j-container">
								<jdoc:include type="message" />
								<div id="javascript-warning">
									<noscript>
										<?php echo Text::_('INSTL_WARNJAVASCRIPT'); ?>
									</noscript>
								</div>
								<div id="container-installation" class="container-installation flex no-js hidden" data-base-url="<?php echo Uri::root(); ?>">
									<jdoc:include type="component" />
								</div>
							</div>
						</main>
						<footer class="footer text-center small w-100 p-3">
							<?php echo Version::URL; ?>
						</footer>
					</div>
				</div>
			</div>
			<div id="installationProgress" class="modal" tabindex="-1" aria-live="polite" role="region" style="z-index:10050;">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h2 class="modal-title h5"><?php echo Text::_('INSTL_PROGRESS_MODAL'); ?></h2>
						</div>
						<div class="modal-body">
							<div class="progress">
								<div class="progress-bar" role="progressbar" id="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="<?php echo $steps; ?>"></div>
							</div>
							<ul class="list-unstyled">
								<li id="progressdbcheck" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_DBCHECK'); ?>
								</li>
								<li id="progresscreate" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_CREATE'); ?>
								</li>
								<li id="progresspopulate1" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE1'); ?>
								</li>
								<li id="progresspopulate2" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE2'); ?>
								</li>
								<li id="progresspopulate3" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE3'); ?>
								</li>
								<?php if (is_file('sql/mysql/localise.sql')) : ?>
									<li id="progresscustom1" aria-hidden="true">
										<span class="fa fa-spinner fa-spin text-white"></span>
										<?php echo Text::_('INSTL_PROGRESS_STEP_CUSTOM1'); ?>
									</li>
								<?php endif; ?>
								<?php if (is_file('sql/mysql/custom.sql')) : ?>
									<li id="progresscustom2" aria-hidden="true">
										<span class="fa fa-spinner fa-spin text-white"></span>
										<?php echo Text::_('INSTL_PROGRESS_STEP_CUSTOM2'); ?>
									</li>
								<?php endif; ?>
								<li id="progressconfig" aria-hidden="true">
									<span class="fa fa-spinner fa-spin text-white"></span>
									<?php echo Text::_('INSTL_PROGRESS_STEP_CONFIG'); ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<jdoc:include type="scripts" />
		</div>
	</body>
</html>
