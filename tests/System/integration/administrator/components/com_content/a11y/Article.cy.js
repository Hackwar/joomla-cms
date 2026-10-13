describe('Test accessibility on article create and edit', () => {
  beforeEach(() => {
		cy.doAdministratorLogin();
		// Clear the filter
		cy.visit('/administrator/index.php?option=com_content&filter=');
	});
  afterEach(() => cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'"));
   
	it('has no detectable a11y violations on create article view', () => {
		cy.visit('/administrator/index.php?option=com_content&task=article.add');
		cy.runAccessibilityScan();
	});
});
