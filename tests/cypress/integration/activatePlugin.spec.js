describe('Plugin', () => {
	before(() => {
		// cy.wpCli('plugin deactivate cmb2', { failOnNonZeroExit: false })
	})

	beforeEach(() => {
		cy.logIn()
		cy.visit('/wp-admin/plugins.php')
		cy.location('pathname').should('equal', '/wp-admin/plugins.php')
	})

	it('Can be deactivated', () => {
		cy.get('#deactivate-cmb2').click()
		cy.get('#activate-cmb2').should('be.visible')
	})

	it('Can be activated', () => {
		cy.get('#activate-cmb2').click()
		cy.get('#deactivate-cmb2').should('be.visible')
	})
})
