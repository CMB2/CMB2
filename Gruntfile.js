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
					src: ['**/*.css'],
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
				src: ['css/cmb2.css'],
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

	grunt.registerTask('styles', ['sass', 'cmq', 'csscomb', 'cssmin']);
	grunt.registerTask('js', ['asciify', 'jshint', 'uglify']);
	grunt.registerTask('tests', ['asciify', 'jshint', 'phpunit']);
	grunt.registerTask('default', ['styles', 'js', 'tests']);
};
