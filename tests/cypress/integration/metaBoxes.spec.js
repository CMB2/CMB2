describe('Meta Boxes', () => {
	beforeEach(() => {
		cy.logIn()
		cy.visit('/wp-admin/post-new.php')
		cy.location('pathname').should('equal', '/wp-admin/post-new.php')
	})

	describe('Default Closed Box', () => {
		it('Should show its title', () => {
			cy.get('#cmb2_integration_tests_default_closed')
				.as('box')
				.find('h2')
				.first()
				.should('be.visible')
			
			cy.get('#cmb2_integration_tests_field_text')
				.as('field')
				.should('not.be.visible')

			cy.get('@box')
				.find('button.handlediv')
				.as('toggle')
				.click()

			cy.get('@field')
				.should('be.visible')
				.type('Value')

			cy.get('.editor-post-title__input')
				.first()
				.type('Title')

			cy.get('.editor-post-save-draft')
				.click()
			
			cy.get('.editor-post-saved-state.is-saved')
				.should('be.visible')
			
			cy.reload()

			cy.get('@toggle')
				.click()
			
			cy.get('@field')
				.should('have.value', 'Value')

			cy.get('@toggle')
				.click()

			cy.get('@field')
				.should('be.hidden')
		})
	})
})
