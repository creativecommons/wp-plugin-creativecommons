# Contributing and development

Thank you for your interest in contributing to CC WordPress Plugin! This
document is a set of guidelines to help you contribute to this project.


## Code of conduct

[`CODE_OF_CONDUCT.md`][org-coc]:
> The Creative Commons team is committed to fostering a welcoming community.
> This project and all other Creative Commons open source projects are governed
> by our [Code of Conduct][code_of_conduct]. Please report unacceptable
> behavior to [conduct@creativecommons.org](mailto:conduct@creativecommons.org)
> per our [reporting guidelines][reporting_guide].

[org-coc]: https://github.com/creativecommons/.github/blob/main/CODE_OF_CONDUCT.md
[code_of_conduct]: https://opensource.creativecommons.org/community/code-of-conduct/
[reporting_guide]: https://opensource.creativecommons.org/community/code-of-conduct/enforcement/


## Contributing

See [`CONTRIBUTING.md`][org-contrib].

[org-contrib]: https://github.com/creativecommons/.github/blob/main/CONTRIBUTING.md


## WordPress Coding Standards

Creative Commons plugin for WordPress follows [WordPress Coding
Standards][standards] and [WordPress Documentation Standards][inline].  Before
pushing your work/contribution, make sure it closely follows these standards
otherwise it will not be accepted. We use a PHP_CodeSniffer setup with
`'WordPress'` sniff to check the code against the standards.

[standards]: https://make.wordpress.org/core/handbook/best-practices/coding-standards/
[inline]: https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/


### Recommended Setup for WordPress Coding Standards

If you are not setup to detect WPCS errors, consider the following steps.

1. **Install Composer**

   Make sure that you have the current version of PHP installed. Then the first
   step is to install [Composer](https://getcomposer.org/). Install it Globally
   by following its [documentation](https://getcomposer.org/doc/00-intro.md)
   for your particular OS.

2. **Install PHP_CodeSniffer**

   Install [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer) by running the
   following command in your terminal:
   ```shell
   composer global require squizlabs/php_codesniffer
   ```

3. **Confirm Installation**

   Check your installation by `which phpcs`, You should get the path to the
   phpcs executable. If you don't get anything for `which phpcs`, you need to
   add this to your .zshrc or .bash_profile (or your shell’s own profile file)
   so it will make terminal look in that folder too:
   ```shell
   export PATH="$HOME/.composer/vendor/bin:$PATH"
   ```

4. **Setup WPCS**

   Clone the official [WordPress Coding Standards repository][wpcs-repo] in
   your home folder and ensure you are using its `master` branch:
   ```shell
   git clone https://github.com/WordPress/WordPress-Coding-Standards.git wpcs
   ```
   ```shell
   cd wpcs
   ```
   ```shell
   git checkout master
   ```

5. **Tell PHPCS about this directory**

   We need to add the ~/wpcs folder, where we cloned wpcs, to the installed
   paths of phpcs. Replace the path with the path of your wpcs directory:
   ```shell
   phpcs --config-set installed_paths /Users/your-username/wpcs
   ```

6. **Check Installation**

   Confirm that it is working by running the following command:
   ```shell
   phpcs -i
   ```
   The output should be:
   ```
   The installed coding standards are PEAR, Zend, PSR2, MySource, Squiz, PSR1,
   PSR12, WordPress-VIP, WordPress, WordPress-Extra, WordPress-Docs and
   WordPress-Core
   ```
   If it does not include the WordPress Standards, most probably the
   installed_paths config option is wrong. Make sure that it points to the
   right directory.

7. **Visual Studio Code Workflow**

   To configure VSCode so that it may report errors right in the editor,
   install [phpcs extension][phpcs]. Open User Settings and add the following
   settings:

   ```shell
   "phpcs.executablePath": "/usr/local/bin/phpcs",
   "phpcs.standard": "WordPress"
   ```

   Now, phpcs will report errors inside VSCode. If you are using some other
   editor, consult its documentation. Once there are no reported errors in your
   fix, you are good to go.

[wpcs-repo]: https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
[phpcs]: https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs


## Contributing to Gutenberg Blocks

CC plugin for WordPress uses Gutenberg blocks built by **create-guten-block**
tool.  If you are interested, you can read its detailed and well-written
[documentation](https://github.com/ahmadawais/create-guten-block). If you want
to test/make changes to these blocks, follow the following steps.

1. **Setup npm**

   First off, make sure you have Node version 8+ and npm 5.3 or more. Clone the
   repository and move to the branch which houses the blocks. In that
    directory, open your terminal and run:

   ```shell
   npm install
   ```

2. **Start Development**

   After the install is completed run the following command:

   ```shell
   npm start
   ```

   This will compile and run the block in development mode. It also watches for
   any changes and reports back any errors in your code. Now, you can make
   changes and test them.


3. **Build the Blocks**

   Once your development is done, make sure to run this:

   ```shell
   npm run build
   ```

   It optimizes and builds production code for your block inside `dist` folder.


## Using @wp-env for our local development environment

1. **Start the @wp-env envrionment using the command**

   Still in the directory
   of the project run the following command to start the development enviroment

    ```shell
    wp-env start
    ```

2. **Check that the development environment is running**

   @wp-env requires Docker to run, ensure you have Docker running and open
   localhost:8888 to go into the dashboard localhost:8888/wp-admin

3. **To stop @wp-env**

    ```shell
    wp-env stop
    ```


## Using a localized Docker Setup

A local `docker-compose.yml` file is included in the `./dev/` directory. It
includes an Apache webserver, the latest WordPress installation files, and a
mySQL db server utilizing MariaDB.

It is modelled after the official example: [wordpress - Official Image | Docker
Hub](https://hub.docker.com/_/wordpress/).

To run a local development environment for building and testing contributions
you can run the following pattern from the root directory of this repository
after cloning it.

```shell
docker compose -f ./dev/docker-compose.yml [command]
```

Be sure to substitute `[command]` for a valid docker compose command, such as the following to build and start containers:

````shell
docker compose -f ./dev/docker-compose.yml up

Or to stop containers:
```shell
docker compose -f ./dev/docker-compose.yml down
```

The first time the build process is run via `docker compose -f
./dev/docker-compose.yml up`, docker will create two directories within your
local repository clone:
- `./dev/db` where the database and relevant config will be stored
- `./dev/wordpress` where the WordPress files will be stored

It will then mount this plugin's root directory into the `/wp-content/plugins/`
directory of the WordPress installation. Edits made to your local plugin clone
will reflect within the build.

You can then navigate to `http://localhost:8080/` and proceed with a manual
WordPress installation. After the initial installation the WordPress install
will persisist between docker sessions.

If you need to reset the WordPress install to a "clean slate" you can simply
delete the `db` and `wordpress` directories respectively, and then run `docker
compose -f ./dev/docker-compose.yml up` again to initialize a clean install
build.
