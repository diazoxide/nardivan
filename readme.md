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
        "directory":"public_html",
        "repos" : [
            {
                "target":"/wp-content/themes/brandlight",
                "name": "brandlight",
                "source": {
                    "url": "git@gitlab.com:brandlight/open-source/brandlight.git",
                    "type": "git",
                    "reference": "master"
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