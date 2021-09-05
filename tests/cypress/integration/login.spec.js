describe('WordPress User', () => {
	it('Can log in', () => {
		cy.visit('/wp-admin/')
		cy.location('pathname').should('contain', '/wp-login.php')
		cy.get('#rememberme')
			.should('not.be.checked')
			.click()
		cy.get('#user_login')
			.setValue(Cypress.env('wp_username'))
		cy.get('#user_pass')
			.setValue(Cypress.env('wp_password'))
			.type('{enter}')
		cy.location('pathname').should('not.contain', '/wp-login.php').and('equal', '/wp-admin/')
	});

	it('Can log out', () => {
		cy.logIn()
		cy.visit('/wp-admin/')
		cy.location('pathname').should('not.contain', '/wp-login.php').and('equal', '/wp-admin/')
		cy.get('#wp-admin-bar-logout > a').click({ force: true })
		cy.location('pathname').should('contain', '/wp-login.php')
		cy.location('search').should('contain', 'loggedout=true')
	})
})
