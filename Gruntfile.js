module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		phpunit: {
			classes: {}
		},
		githooks: {
			all: {
				'pre-commit': 'phpunit'
			}
		},
	});

	grunt.loadNpmTasks('grunt-phpunit');
	grunt.loadNpmTasks('grunt-githooks');

	grunt.registerTask('default', ['phpunit']);
};
