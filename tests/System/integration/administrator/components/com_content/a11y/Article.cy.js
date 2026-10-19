
describe('Test accessibility on article create and edit', () => {
    const createTabs = [
        'Images and Links',
        'Options',
        'The Author',
        'Schema',
        'Publishing',
        'Configure Edit Screen',
        'Permissions',
    ];

    const toolbarButtons = [
        'toolbar-versions',
        'toolbar-preview',
        'toolbar-jooa11y-preview',
        'toolbar-inlinehelp',
    ];
    beforeEach(() => {
        cy.doAdministratorLogin();
	});

    after(() => {
        cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
    });

    // Create
	it('has no detectable a11y violations on create article initial view', () => {
		cy.visit('/administrator/index.php?option=com_content&task=article.add');
		cy.runAccessibilityScan();
	});

    // Tabs in create view
    createTabs.forEach((tab) => {
        it(`has no detectable a11y violations on create article view when "${tab}" is clicked`, () => {
            cy.visit('/administrator/index.php?option=com_content&task=article.add');
            cy.contains('#myTab [role="tab"]', tab).should('be.visible').click();
            cy.runAccessibilityScan();
        });
    });

    // Edit
    it('has no detectable a11y violations on edit article initial view', () => {
        cy.db_createArticle({ title: 'Test article' }).then((article) => {
            cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
            cy.runAccessibilityScan();
        });
    });

    // Toolbar buttons
    toolbarButtons.forEach((toolbarButton) => {
        it(`has no detectable a11y violations on edit article view when "${toolbarButton}" is clicked`, () => {
            cy.db_createArticle({ title: 'Test article' }).then((article) => {
                cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
                cy.get(`#${toolbarButton}`).should('be.visible').click();
                cy.runAccessibilityScan();
            });
        });
    });
});
