# Nardivan local environment creation tool

## Installation

> Installation command.
 
1. Download and install package
    ```bash
   composer global require diazoxide/nardivan:dev-master
    ```
> If you need to use nardivan as global bash command then you should add composer/vendor/bin directory to your user PATH
   ```bash
   export PATH="$PATH:$HOME/.config/composer/vendor/bin"
   ```
2. Create `nardivan.json` file on your project root directory
    ```json
    {
      "directory": "public_html",
      "use-symlink-method":true,
      "scripts": {
        "pre-update": [],
        "post-update": [],
        "pre-install": [],
        "post-install": []
      },
      "environments-scripts": {
        "pre-update": [
          "echo 'Start global'"
        ],
        "post-update": [
          "if [ -f \"composer.json\" ]; then composer update; fi;",
          "if [ -f \"package.json\" ]; then npm install; fi;"
        ]
      },
      "environments": [
        {
          "target": "wp-content/themes/my-theme",
          "name": "my-repo-name",
          "git": {
            "url": "git@gitlab.com:my/custom/repo.git",
            "branch": "master"
          },
          "scripts": {
            "pre-update": [
              "echo 'you custom command'"
            ],
            "post-update": [
              "echo 'your custom command'"
            ]
          }
        }
     ]
    }
    ```
3. Run installation command
    ```bash
    nardivan install
    ```
4. Run update command
    ```bash
    nardivan update
    ```