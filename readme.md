# Installation

> Installation command.
 
1. Download and install package
    ```bash
    sudo wget -O nardivan.tar.gz https://github.com/diazoxide/nardivan/archive/master.tar.gz
    sudo mkdir -p /var/lib/nardivan
    sudo rm -rf /var/lib/nardivan/* 
    sudo tar -xvf nardivan.tar.gz -C /var/lib/nardivan 
    sudo chmod +x /var/lib/nardivan/nardivan-master/nardivan.sh 
    sudo mv /var/lib/nardivan/nardivan-master/nardivan.sh /usr/local/bin/nardivan
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
          "echo 'Start global\\n'"
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
              "echo 'you custom command\\n'"
            ],
            "post-update": [
              "echo 'your custom command 2\\n'"
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