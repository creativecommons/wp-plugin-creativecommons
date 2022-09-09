# Development and Deployment


## WordPress.org Plugin Deployment

1. Checkout this GitHub repository
1. Checkout the WordPress.org Subversion repository for this plugin
3. From GitHub repository directory, execute the helper script with the
   following arguments
   1. WordPress.org username
   2. GitHub tag for the version
   3. Path to the Subversion repository checkout
    ```
    ./dev/prep_svn_release.sh cctimidrobot v2022.09.1 \
        ../../svn/wordpress-org-creative-commons/
    ```
4. From the Subversion repositry checkout, copy and commit Subversion tag:
    ```
    svn copy trunk/ tags/2022.09.1/
    svn commit --username=cctimidrobot -m'Tagging version 2022.09.1'
    ```

### Notes

- The GitHub tag is prefixed with "v" for compatibility with composer. The
  leading "v" **must not** used for any WordPress.org field.


### Resources

- [Using Subversion | Plugin Developer Handbook | WordPress Developer
  Resources][wordpress-svn]
- [Document process for WordPress.org plugin deployment · Issue #78][issue78]
  (Includes links to additional deployment methods and software)

[wordpress-svn]: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
[issue78]: https://github.com/creativecommons/wp-plugin-creativecommons/issues/78
