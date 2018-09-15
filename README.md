# Simple Dividend JSON-RPC client based on GuzzleHttp

[![Latest Stable Version](https://poser.pugx.org/denpa/php-dividendrpc/v/stable)](https://packagist.org/packages/denpa/php-dividendrpc)
[![License](https://poser.pugx.org/denpa/php-dividendrpc/license)](https://packagist.org/packages/denpa/php-dividendrpc)
[![Build Status](https://travis-ci.org/denpamusic/php-dividendrpc.svg)](https://travis-ci.org/denpamusic/php-dividendrpc)
[![Code Climate](https://codeclimate.com/github/denpamusic/php-dividendrpc/badges/gpa.svg)](https://codeclimate.com/github/denpamusic/php-dividendrpc)
[![Code Coverage](https://codeclimate.com/github/denpamusic/php-dividendrpc/badges/coverage.svg)](https://codeclimate.com/github/denpamusic/php-dividendrpc/coverage)
[![Join the chat at https://gitter.im/php-dividendrpc/Lobby](https://badges.gitter.im/php-dividendrpc/Lobby.svg)](https://gitter.im/php-dividendrpc/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Installation
Run ```php composer.phar require denpa/php-dividendrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
    "denpa/php-dividendrpc": "^2.0"
}
```
and run ```php composer.phar install```.

## Requirements
PHP 7.0 or higher

## Usage
Create new object with url as parameter
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use Cppdevcrypto\Dividend\Client as DividendClient;

$Dividendd = new DividendClient('http://rpcuser:rpcpassword@localhost:8332/');
```
or use array to define your Dividendd settings
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use Cppdevcrypto\Dividend\Client as DividendClient;

$Dividendd = new DividendClient([
    'scheme'   => 'http',                 // optional, default http
    'host'     => 'localhost',            // optional, default localhost
    'port'     => 8332,                   // optional, default 8332
    'user'     => 'rpcuser',              // required
    'password' => 'rpcpassword',          // required
    'ca'       => '/etc/ssl/ca-cert.pem'  // optional, for use with https scheme
]);
```
Then call methods defined in [Dividend Core API Documentation](https://dividend.org/en/developer-reference#dividend-core-apis) with magic:
```php
/**
 * Get block info.
 */
$block = $Dividendd->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash')->get();     // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // array of values
$block->keys();            // array of keys
$block->random(1, 'tx');   // random block txid
$block('tx')->random(2);   // two random block txid's
$block('tx')->first();     // txid of first transaction
$block('tx')->last();      // txid of last transaction

/**
 * Send transaction.
 */
$result = $Dividendd->sendToAddress('mmXgiR6KAhZCyQ8ndr2BCfEq1wNG2UnyG6', 0.1);
$txid = $result->get();

/**
 * Get transaction amount.
 */
$result = $Dividendd->listSinceBlock();
$dividend = $result->sum('transactions.*.amount');
$divtoshi = \Cppdevcrypto\Dividend\to_divtoshi($dividend);
```
To send asynchronous request, add Async to method name:
```php
$Dividendd->getBlockAsync(
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

You can also send requests using request method:
```php
/**
 * Get block info.
 */
$block = $Dividendd->request('getBlock', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash');            // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // get response values
$block->keys();            // get response keys
$block->first('tx');       // get txid of the first transaction
$block->last('tx');        // get txid of the last transaction
$block->random(1, 'tx');   // get random txid

/**
 * Send transaction.
 */
$result = $Dividendd->request('sendtoaddress', 'mmXgiR6KAhZCyQ8ndr2BCfEq1wNG2UnyG6', 0.06);
$txid = $result->get();

```
or requestAsync method for asynchronous calls:
```php
$Dividendd->requestAsync(
    'getBlock',
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

## Multi-Wallet RPC
You can use `wallet($name)` function to do a [Multi-Wallet RPC call](https://en.dividend.it/wiki/API_reference_(JSON-RPC)#Multi-wallet_RPC_calls):
```php
/**
 * Get wallet2.dat balance.
 */
$balance = $Dividendd->wallet('wallet2.dat')->getbalance();

echo $balance->get(); // 0.10000000
```


## Helpers
Package provides following helpers to assist with value handling.
### `to_dividend()`
Converts value in divtoshi to dividend.
```php
echo Cppdevcrypto\Dividend\to_dividend(100000); // 0.00100000
```
### `to_divtoshi()`
Converts value in dividend to divtoshi.
```php
echo Cppdevcrypto\Dividend\to_divtoshi(0.001); // 100000
```
### `to_udvd()`
Converts value in dividend to udvd/bits.
```php
echo Cppdevcrypto\Dividend\to_udvd(0.001); // 1000.0000
```
### `to_mdvd()`
Converts value in dividend to mdvd.
```php
echo Cppdevcrypto\Dividend\to_mdvd(0.001); // 1.0000
```
### `to_fixed()`
Trims float value to precision without rounding.
```php
echo Cppdevcrypto\Dividend\to_fixed(0.1236, 3); // 0.123
```

## License

This product is distributed under MIT license.

## Donations

If you like this project, please consider donating:<br>
**DVD**: 3L6dqSBNgdpZan78KJtzoXEk9DN3sgEQJu<br>
**Bech32**: bc1qyj8v6l70c4mjgq7hujywlg6le09kx09nq8d350

❤Thanks for your support!❤
