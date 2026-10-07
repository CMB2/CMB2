import globals from 'globals';

/**
 * ESLint flat config for CMB2's hand-authored browser scripts.
 *
 * Ported 1:1 from the legacy `.jshintrc` so the swap from JSHint to ESLint is
 * behavior-preserving: same rules, same globals, same ES5 target (CMB2 still
 * supports old browser environments). Only the three unminified source files
 * are linted — generated `*.min.js` is excluded.
 */
export default [
	{
		files: [ 'js/cmb2.js', 'js/cmb2-wysiwyg.js', 'js/cmb2-char-counter.js' ],
		languageOptions: {
			// ES5 to match JSHint's default (no esversion was set) and to keep
			// the source compatible with the old browsers CMB2 targets.
			ecmaVersion: 5,
			sourceType: 'script',
			globals: {
				...globals.browser,
				// Project / library globals (was `predef` in .jshintrc).
				jQuery: 'readonly',
				cmb2_l10: 'readonly',
				wp: 'readonly',
				tinyMCEPreInit: 'readonly',
				tinyMCE: 'readonly',
				postboxes: 'readonly',
				pagenow: 'readonly',
				QTags: 'readonly',
				quicktags: 'readonly',
				_: 'readonly',
				// Was `globals` in .jshintrc: exports writable, module read-only.
				exports: 'writable',
				module: 'readonly',
			},
		},
		rules: {
			// .jshintrc: curly
			curly: [ 'error', 'all' ],
			// .jshintrc: eqeqeq + eqnull (allow == / != against null)
			eqeqeq: [ 'error', 'always', { null: 'ignore' } ],
			// .jshintrc: newcap
			'new-cap': 'error',
			// .jshintrc: noarg (no arguments.caller/callee)
			'no-caller': 'error',
			// .jshintrc: latedef
			'no-use-before-define': 'error',
			// .jshintrc: immed (wrap immediately-invoked function expressions)
			'wrap-iife': [ 'error', 'any' ],
			// .jshintrc: unused. argsIgnorePattern preserves the IIFE idiom
			// `(function(window, document, $, cmb, undefined){ … })` — the
			// trailing `undefined` param is intentionally never used; its only
			// job is to guarantee a real `undefined` binding inside the closure.
			// JSHint's `unused` did not flag it either.
			'no-unused-vars': [ 'error', { argsIgnorePattern: '^undefined$' } ],
			// .jshintrc: undef
			'no-undef': 'error',
			// .jshintrc: boss (allow assignments in conditionals)
			'no-cond-assign': 'off',
			// .jshintrc: sub — JSHint suppressed []-vs-dot warnings; the
			// equivalent ESLint rule (dot-notation) is off by default, so
			// nothing to declare.
		},
	},
];
