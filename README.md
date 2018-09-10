# WP-CLI Launcher

A small wrapper around WP-CLI for your global $PATH.

## Why?

In order to avoid dependency issues, it is best to require WP-CLI on a per-project basis via Composer (`composer require wp-cli/wp-cli`). This makes WP-CLI available to your project by placing it at `vendor/bin/wp`.

However, it is inconvenient to type `vendor/bin/wp` in order to execute WP-CLI commands.  By installing the WP-CLI Launcher globally on your local machine, you can simply type `wp` on the command line, and the launcher will find and execute the project specific version of WP-CLI located in your project's `vendor` directory.

## Installation - Phar

1. Download latest stable release via CLI (code below) or browse to https://github.com/leymannx/wp-cli-launcher/releases/latest.

    OSX:
    ```Shell
    curl -OL https://github.com/leymannx/wp-cli-launcher/releases/download/0.0.16/wp-cli.phar
    ```

    Linux:

    ```Shell
    wget -O wp-cli.phar https://github.com/leymannx/wp-cli-launcher/releases/download/0.0.16/wp-cli.phar
    ```
2. Make downloaded file executable:
    ```Shell
    chmod +x wp-cli.phar
    ```
3. Move wp-cli.phar to a location listed in your `$PATH`, rename to `wp`: 

    ```Shell
    sudo mv wp-cli.phar /usr/local/bin/wp
    ```
    
4. Windows users: create a wp-cli.bat file in the same folder as wp-cli.phar with the following lines. This gets around the problem where Windows does not know that .phar files are associated with `php`:
   
    ``` Bat
    @echo off
    php "%~dp0\wp-cli.phar" %*
    ```

## Update

The WP-CLI Launcher Phar is able to self update to the latest release.

```Shell
wp self-update
```

## Alternatives

If you only have one codebase on your system (typical with VMs, Docker, etc,), you should add `/path/to/vendor/bin` to your $PATH.

## Fallback

When a site-local WP-CLI is not found, this launcher usually throws a helpful error.
You may avoid the error and instead hand off execution to a global WP-CLI (any version)
by doing *either* of:

1. Export an environment variable: `export WP_CLI_LAUNCHER_FALLBACK=/path/to/wp`
2. Specify an option: `--fallback=/path/to/wp`

## Xdebug compatibility

WP-CLI Launcher, like Composer automatically disables Xdebug by default. This improves performance substantially. You may override this feature by setting an environment variable. ``WP_CLI_ALLOW_XDEBUG=1 wp [command]``

## License

GPL-2.0+
