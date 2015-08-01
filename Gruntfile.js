module.exports = function(grunt) {

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		phpunit: {
			classes: {}
		},

		githooks: {
			all: {
				'pre-commit': 'tests'
			}
		},

		dirs: {
			lang: 'languages'
		},

		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					potComments: '',
					potFilename: 'cmb2.pot',
					type: 'wp-plugin',
					updateTimestamp: true,
					potHeaders: {
						poedit: true,
						'language': 'en_US',
						'x-poedit-keywordslist': true
					},
					processPot: function( pot, options ) {
						pot.headers['report-msgid-bugs-to'] = 'http://wordpress.org/support/plugin/cmb2';
						pot.headers['last-translator'] = 'WebDevStudios contact@webdevstudios.com';
						pot.headers['language-team'] = 'WebDevStudios contact@webdevstudios.com';
						var today = new Date();
						pot.headers['po-revision-date'] = today.getFullYear() +'-'+ ( today.getMonth() + 1 ) +'-'+ today.getDate() +' '+ today.getUTCHours() +':'+ today.getUTCMinutes() +'+'+ today.getTimezoneOffset();
						return pot;
					}
				}
			}
		},

		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.lang %>/',
					src: ['*.po'],
					dest: '<%= dirs.lang %>/',
					ext: '.mo',
					nonull: true
				}]
			}
		},

		checktextdomain: {
			options: {
				text_domain: 'cmb2',
				create_report_file: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
					]
				},
				files: {
					src: [
						'**/*.php', // Include all files
						'!node_modules/**', // Exclude node_modules/
						],
					expand: true
				}
			},

		exec: {
			txpull: { // Pull Transifex translation - grunt exec:txpull
				cmd: 'tx pull -a  -f' // Change the percentage with --minimum-perc=yourvalue
			},
			txpush_s: { // Push pot to Transifex - grunt exec:txpush_s
				cmd: 'tx push -s'
			},
		},

		// concat: {
		// 	options: {
		// 		stripBanners: true,
		// 		banner: '/**\n' +
		// 		' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.homepage %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv2+\n' +
		// 		' */\n',
		// 	},
		// 	CMB2 : {
		// 		src: [
		// 			'js/cmb2.min.js',
		// 			'js/jquery.timePicker.min.js',
		// 		],
		// 		dest: 'assets/js/combined.js'
		// 	}
		// },

		csscomb: {
			dist: {
				files: [{
					expand: false,
					cwd: 'css/',
					src: ['css/cmb2.css'],
					dest: 'css/',
				}]
			}
		},

		sass: {
			dist: {
				options: {
					style: 'expanded',
					lineNumbers: true
				},
				files: {
				  'css/cmb2.css': 'css/sass/cmb2.scss',
				  'css/cmb2-front.css': 'css/sass/cmb2-front.scss'
				}
			}
		},

		cmq: {
			options: {
				log: false
			},
			dist: {
				files: {
					'css/cmb2.css': 'css/cmb2.css'
				}
			}
		},

		cssmin: {
			options: {
				// banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
				// 	' * <%= pkg.homepage %>\n' +
				// 	' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
				// 	' * Licensed GPLv2+' +
				// 	' */\n'
			},
			minify: {
				expand: true,
				src: ['css/cmb2.css','css/cmb2-front.css'],
				// dest: '',
				ext: '.min.css'
			}
		},

		jshint: {
			all: [
				'js/cmb2.js'
			],
			options: {
				curly   : true,
				eqeqeq  : true,
				immed   : true,
				latedef : true,
				newcap  : true,
				noarg   : true,
				sub     : true,
				unused  : true,
				undef   : true,
				boss    : true,
				eqnull  : true,
				globals : {
					exports : true,
					module  : false
				},
				predef  :['document','window','jQuery','cmb2_l10','wp','tinyMCEPreInit','tinyMCE','console','postboxes','pagenow']
			}
		},

		asciify: {
			banner: {
				text    : 'CMB!',
				options : {
					font : 'isometric2',
					log  : true
				}
			}
		},

		uglify: {
			all: {
				files: {
					'js/cmb2.min.js': ['js/cmb2.js']
				},
				options: {
					// banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
					// 	' * <%= pkg.homepage %>\n' +
					// 	' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					// 	' * Licensed GPLv2+' +
					// 	' */\n',
					mangle: false
				}
			}
		},

		watch: {

			css: {
				files: ['css/sass/partials/*.scss'],
				tasks: ['styles'],
				options: {
					spawn: false,
				},
			},

			scripts: {
				files: ['js/cmb2.js'],
				tasks: ['js'],
				options: {
					debounceDelay: 500
				}
			},

			other: {
				files: [ '*.php', '**/*.php', '!node_modules/**', '!tests/**' ],
				tasks: [ 'makepot' ]
			}

		},

		// make a zipfile
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: 'cmb2.zip'
				},
				files: [ {
						expand: true,
						// cwd: '/',
						src: [
							'**',
							'!node_modules/**',
							'!css/sass/**',
							'!**.zip',
							'!Gruntfile.js',
							'!package.json',
							'!phpunit.xml',
							'!tests/**'
						],
						dest: '/'
				} ]
			}
		}

	});

	grunt.registerTask('styles', ['sass', 'csscomb', 'cmq', 'cssmin']);
	grunt.registerTask('js', ['asciify', 'jshint', 'uglify']);
	grunt.registerTask('tests', ['asciify', 'jshint', 'phpunit']);
	grunt.registerTask('default', ['styles', 'js', 'tests']);

	// Checktextdomain and makepot task(s)
	grunt.registerTask('build:i18n', ['checktextdomain', 'makepot', 'newer:potomo']);

	// Makepot and push it on Transifex task(s).
	grunt.registerTask('tx-push', ['makepot', 'exec:txpush_s']);

	// Pull from Transifex and create .mo task(s).
	grunt.registerTask('tx-pull', ['exec:txpull', 'newer:potomo']);
};
