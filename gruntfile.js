'use strict';

module.exports = function (grunt) {
    // load all grunt tasks
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.initConfig({
        watch: {
            // if any .less file changes in directory "public/css/" run the "less"-task.
            files: ["less/app.less","less/bootstrap/*.less"],
            tasks: ["less:dev"]
        },
        // "less"-task configuration
        less: {
            dev: {
                options: {
                    paths: ["less/"]
                },
                files: {
                    "public/css/app.css": "less/app.less"
                }
            },
            dist:{
                options: {
                    paths: ["less/"],
                    cleancss: true
                },
                files: {
                    "public/css/app.css": "less/app.less"
                }
            }
        },
        copy: {
            less: {
                cwd: 'vendor/components/bootstrap/less',
                src: '**/*',
                dest: 'less/bootstrap',
                expand: true
            },
            js: {
                cwd: 'vendor/components/bootstrap/js',
                src: 'bootstrap.min.js',
                dest: 'public/js/vendor',
                expand: true
            },
            fonts: {
                cwd: 'vendor/components/bootstrap/fonts',
                src: '**/*',
                dest: 'public/fonts',
                expand: true
            },
            jquery: {
                cwd: 'vendor/components/jquery',
                src: 'jquery.min.js',
                dest: 'public/js/vendor',
                expand: true
            }

        },
        uglify : {
            build : {
                src : ["src/js/app.js"],
                dest : "public_html/js/app.js"
            }
        }
    });
    grunt.registerTask("js", ["uglify"]);
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('dist', ['less:dist']);
    grunt.registerTask('bootstrap',['copy:less','copy:js','copy:fonts','copy:jquery'])
};


