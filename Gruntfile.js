/* global module, require */
module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration
	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),

		jshint: {
			browser: {
				files: {
					src: [ 'assets/js/src/*.js' ]
				},
				options: {
					jshintrc: '.jshintrc'
				}
			},
			grunt: {
				files:{
					src: [ 'Gruntfile.js' ]
				},
				options: {
					jshintrc: '.jshintrc'
				}
			}
		},

		concat: {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %> %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n',
				sourceMap: true
			},
			css: {
				files: {
					'assets/css/bulk-move.css': ['assets/css/src/**/*.css']
				}
			},
			scripts: {
				files: {
					'assets/js/bulk-move.js': ['assets/js/src/**/*.js']
				}
			}
		},

		uglify: {
			all: {
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %> \n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n',
					mangle: {
						reserved: ['jQuery', 'BULK_MOVE']
					}
				},
				files: {
					'assets/js/bulk-move.min.js': ['assets/js/bulk-move.js']
				}
			}
		},

		cssmin: {
			minify: {
				src: 'assets/css/bulk-move.css',
				dest: 'assets/css/bulk-move.min.css'
			}
		},

		watch:  {
			scripts: {
				files: ['assets/js/src/**/*.js'],
				tasks: ['jshint:browser', 'concat', 'uglify'],
				options: {
					debounceDelay: 500
				}
			},
			css: {
				files: ['assets/css/src/**/*.css'],
				tasks: ['concat', 'cssmin'],
				options: {
					debounceDelay: 500
				}
			},
			build: {
				files: ['**', '!dist/**'],
				tasks: ['build'],
				options: {
					debounceDelay: 500
				}
			}
		},

		clean   : {
			dist: ['dist/']
		},

		copy: {
			select2: {
				files: [{
					src : 'node_modules/select2/dist/js/select2.min.js',
					dest: 'assets/js/select2.min.js'
				},
				{
					src : 'node_modules/select2/dist/css/select2.min.css',
					dest: 'assets/css/select2.min.css'
				}]
			},
			dist: {
				files : [
					{
						expand: true,
						src: [
							'**',
							'!dist/**',
							'!AUTHORS.md',
							'!docs/**',
							'!assets-wp-repo/**',
							'!code-coverage/**',
							'!codeception.yml',
							'!node_modules/**',
							'!assets/vendor/**',
							'!assets/js/src/**',
							'!assets/css/src/**',
							'!Gruntfile.js',
							'!package.json',
							'!composer.json',
							'!composer.lock',
							'!phpcs.xml',
							'!phpdoc.dist.xml',
							'!phpunit.xml.dist',
							'!bin/**',
							'!tests/**',
							'!.idea/**',
							'!tags',
							'!vendor/**'
						],
						dest: 'dist/'
					}
				]
			}
		}
	} );

	require('time-grunt')(grunt);

	grunt.registerTask('default', ['jshint:browser', 'concat', 'uglify', 'cssmin']);
	grunt.registerTask('vendor', ['copy:select2']);
	grunt.registerTask('build', [ 'default', 'vendor', 'clean', 'copy:dist']);

	grunt.util.linefeed = '\n';
};
