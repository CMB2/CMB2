module.exports = function(grunt) {

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		phpunit: {
			classes: {}
		},

		githooks: {
			all: {
				'pre-commit': 'default'
			}
		},
		// concat: {
		// 	options: {
		// 		stripBanners: true,
		// 		// banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
		// 		// 	' * <%= pkg.homepage %>\n' +
		// 		// 	' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
		// 		// 	' * Licensed GPLv2+' +
		// 		// 	' */\n'
		// 	},
		// 	'': {
		// 		src: [
		// 			'js/cmb2.js',
		// 			'js/cmb2.js',
		// 		],
		// 		dest: 'assets/js/{%= dir_name %}.js'
		// 	}
		// },

		csscomb: {
			dist: {
				files: [{
					expand: true,
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
				predef  :['document','window','jQuery','cmb2_l10','wp','tinyMCEPreInit','tinyMCE','console']
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
			}
		},

		update_submodules: {

			default: {
				options: {
					// default command line parameters will be used: --init --recursive
				}
			},
			withCustomParameters: {
				options: {
					params: '--force' // specifies your own command-line parameters
				}
			},

		}

	});

	grunt.registerTask('styles', ['sass', 'cmq', 'csscomb', 'cssmin']);
	grunt.registerTask('js', ['asciify', 'jshint', 'uglify']);
	grunt.registerTask('tests', ['asciify', 'jshint', 'phpunit']);
	grunt.registerTask('default', ['update_submodules', 'styles', 'js', 'tests']);
};
