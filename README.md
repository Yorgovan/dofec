# dofec
DeepOnion Front-End Client via SSH

Displaying DeepOnion stats with PHP via SSH using:
- https://github.com/phpseclib/phpseclib
- https://github.com/Dogfalo/materialize

Could be easily customized for other cryptocurrencies.


## Usage
- setup DeepOnion wallet on Raspberry Pi (or some other machine) or cloud (DigitalOcean etc.)
### If not on cloud
- open SSH port (22) if it is not opened (if you don't know how contact your ISP)
- get machine local IP address if you'll access it only on local network
- setup port forwarding and setup static URL (e.g noip.com) if you want global access
### In index.php change following
```php
$url = 'my_ip_or_url'; // e.g. 192.168.1.10 or myraspberrypi.ddns.net
$user = 'user'; // pi - for default raspberry pi setup
$password = 'password'; // raspberry - for default raspberry pi setup
$transactions_number = 100; // number of transactions to display (desc)
```
![screenshot](screenshot.png)
