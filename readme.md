# Installation

> Installation command.  
 
1. Download package
    ```bash
    ```
2. Extract archive
    ```bash
    wget -O nardivan.tar.gz https://github.com/diazoxide/nardivan/archive/master.tar.gz
    sudo mkdir -p /var/lib/nardivan
    sudo rm -rf /var/lib/nardivan/* 
    sudo tar -xvf nardivan.tar.gz -C /var/lib/nardivan 
    sudo chmod +x /var/lib/nardivan/nardivan-master/nardivan.sh 
    sudo mv /var/lib/nardivan/nardivan-master/nardivan.sh /usr/local/bin/nardivan
    ```
