# FWieP's Download Service

A custom URL shortener and webbased download service.

## About

This project provides the ability to shorten an existing URL, or provide an
existing file under a short URL. For example, you could shorten the URL
`https://github.com/fwiep/trekisode/commit/b22e05866d28e5348bcbcf3f75b8c377e86f8b12`
to a plain and simple `https://example.com/PkMcDs`. A user visiting the short URL
will then be redirected using a HTTP-303 header.

There's a **client**-part and an **admin**-area. The client is a single page
that shows a download message with a countdown and/or starts the
download/redirect immediately. The admin page is a single form for managing all
short URLs and uploads.

This project uses *Bootstrap 5* for a responsive layout. At the moment, the admin
form is English-only. The client is locale aware: it tries to determine which
language to speak to the user. Currently the following languages are supported:

- English
- Dutch (Nederlands)
- German (Deutsch)

## Dependencies

- PHP 8.0 or newer
- [Composer][733]
- Apache 2.4 webserver
- MariaDB or MySQL database
- credentials for an SMTP server (optional for backups, recommended)
- local SMTP server (optional for debugging, for example [FakeSMTP][234])

You can choose to make a regular (cron-scheduled) request to `/admin/backup`.
This triggers an SQL-backup, encrypts it in a standard `.zip`-file and sends it
as an attachment per email to a preset email address.

## Installation

1. Clone this repository:

    ```sh
    git clone https://github.com/fwiep/fwiepdl.git;
    ```

1. Navigate into the project folder:

    ```sh
    cd fwiepdl;
    ```

1. Install dependencies via Composer:

    ```sh
    composer install;
    ```

1. In the project's root directory, copy `config.template.php` to `config.php`
  and edit that file to suit your needs. The file is commented and should explain
  each constant in some detail.

1. Set owner, group, permissions (and SELinux contexts) for all folders and files:

    ```sh
    sudo ./scripts/setpermissions.sh .;
    ```

1. Start your (local) webserver.
1. Perform one-time database setup by requesting `/admin/setup`.

## Development

You are welcome to contribute to this project!

### Translations

The client is locale aware; it tries to detect the user's preferred language and
serve the page accordingly. The project uses PHP's [gettext][442] functions and
standard `.po`/`.mo` files for translatable strings. I use [Poedit][231] under
GNU/Linux, but it's available for all major platforms.

### IDE

My development environment of choice is [Visual Studio Code][664]. This project
contains several files specifically for that IDE; all grouped in the `.vscode`
folder. If you choose to use VSCode, please make sure to edit the `pathMappings`
setting in `launch.json`. It should point to the absolute on-disk path of your
working directory.

In `tasks.json`, you'll find all custom build tasks for combining and minifying
stylesheets and JavaScript files. They make use of locally installed tools like
`uglifyjs` and `sass`. Make sure they are installed and the paths are set
accordingly.

There's a shell script called `setpermissions.sh` in the `scripts` folder; it
sets group, owner, permissions (and  SELinux contexts on Fedora) for all the
project's folders and files. It must be run with `root` privileges. Make sure
your username is set instead of `fwiep` around line 28.

If you use a (fake) local SMTP server (see [Dependencies][446]), make
sure to edit the corresponding settings in the `prepareMailer()` function in
`src/App.php`. When in debug mode, they should point to your local server.

## TODO

Things I could imagine being added to this project, if the need arises:

- Client translations in other languages beside English, Dutch and German
- Admin translation into other languages besides English
- User credential storage in the database
- Platform independent build tools and -scripts (add Windows PowerShell)

[234]: https://github.com/Nilhcem/FakeSMTP
[442]: https://www.php.net/manual/en/book.gettext.php
[446]: #dependencies
[231]: https://poedit.net/
[664]: https://code.visualstudio.com/
[733]: https://getcomposer.org/
