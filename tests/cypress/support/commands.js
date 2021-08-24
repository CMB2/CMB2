// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add('logIn', () => {
	cy.request({
		url: '/wp-login.php',
		method: 'POST',
		form: true,
		body: {
			log: Cypress.env('wp_username'),
			pwd: Cypress.env('wp_password'),
			rememberme: 'forever',
			testcookie: 1,
		}
	})
	window.localStorage.setItem('WP_DATA_USER_1',
		JSON.stringify({
			"core/edit-post": {
				"preferences": {
					"features": {
						"welcomeGuide": false,
					},
				},
			},
		})
	)
})

Cypress.Commands.add('wpCli', (command, options = {}) => {
	// There once was an Cypress command
	// That made a wp-env demand
	// Docker was run, and before all was done
	// WP-CLI was in hand
	cy.exec(`npm run env run tests-cli wp ${command}`, options)
})

// Set default typing delay to 0.
Cypress.Commands.overwrite(
	'type',
	(originalFn, subject, string, options) => originalFn(
		subject,
		string,
		Object.assign({ delay: 0 }, options)
	)
)

Cypress.Commands.add('setValue', { prevSubject: true }, (subject, value) => {
	subject[0].setAttribute('value', value)
	return subject
})

Cypress.Commands.add('saveDraft', () => {
	cy.window().then(w => w.stillOnCurrentPage = true)
	cy.get('#save-post').should('not.have.class', 'disabled').click()
})

Cypress.Commands.add('publishPost', () => {
	cy.window().then(w => w.stillOnCurrentPage = true)
	cy.get('#publish').should('not.have.class', 'disabled').click()
})

Cypress.Commands.add('waitForPageLoad', () => {
	cy.window().its('stillOnCurrentPage').should('be.undefined')
	cy.get('#message .notice-dismiss').click()
})

Cypress.Commands.add('blockAutosaves', () => {
	cy.intercept('/wp-admin/admin-ajax.php', req => {
		if (req.body.includes('wp_autosave')) {
			req.reply({
				status: 400,
			})
		}
	}).as('adminAjax')
})
